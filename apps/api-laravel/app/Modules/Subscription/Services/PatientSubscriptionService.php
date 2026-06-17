<?php

namespace App\Modules\Subscription\Services;

use App\Models\FamilyLink;
use App\Models\OrganizationSubscription;
use App\Models\Patient;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * B2C (patient) subscription flow on the unified subscription engine.
 *
 * Patients have no organization, so feature entitlements are resolved directly
 * from the active subscription's plan rather than materialized ModuleEntitlement
 * rows (which are organization-scoped). One active subscription per patient.
 *
 * See docs/superpowers/specs/2026-06-17-subscription-billing-design.md (Phase 2).
 */
class PatientSubscriptionService
{
    /** The single active (or trialing) subscription for a patient, if any. */
    public function activeSubscription(Patient $patient): ?OrganizationSubscription
    {
        return OrganizationSubscription::with('plan.planFeatures')
            ->where('subscriber_type', 'patient')
            ->where('subscriber_id', $patient->id)
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->orderByDesc('created_at')
            ->first();
    }

    /** The patient's current plan, or the Free plan when unsubscribed. */
    public function currentPlan(Patient $patient): ?SubscriptionPlan
    {
        return $this->activeSubscription($patient)?->plan ?? $this->freePlan();
    }

    public function freePlan(): ?SubscriptionPlan
    {
        return SubscriptionPlan::forAudience('patient')->where('slug', 'patient-free')->first();
    }

    /**
     * Ensure the patient has at least the Free plan (default state on signup).
     * Idempotent — returns the existing active subscription if one exists.
     */
    public function ensureFreePlan(Patient $patient, ?string $actorId = null): OrganizationSubscription
    {
        if ($existing = $this->activeSubscription($patient)) {
            return $existing;
        }

        $free = $this->freePlan();
        abort_if($free === null, 500, 'Patient Free plan is not seeded.');

        return $this->open($patient, $free, 'monthly', [], $actorId);
    }

    /**
     * Activate a paid (or free) plan for a patient. For paid plans this is called
     * by the checkout flow AFTER payment is confirmed. Upgrading/switching from an
     * existing active subscription cancels the old one first (single active sub).
     */
    public function startSubscription(
        Patient $patient,
        SubscriptionPlan $plan,
        string $interval = 'monthly',
        array $billing = [],
        ?string $actorId = null
    ): OrganizationSubscription {
        return DB::transaction(function () use ($patient, $plan, $interval, $billing, $actorId) {
            // Close any current subscription so there is exactly one active row.
            $current = $this->activeSubscription($patient);
            if ($current) {
                $current->update([
                    'status'       => 'cancelled',
                    'cancelled_at' => now()->toDateString(),
                    'auto_renew'   => false,
                    'updated_by'   => $actorId,
                ]);
            }

            return $this->open($patient, $plan, $interval, $billing, $actorId);
        });
    }

    /** Cancel at period end (stays active until current_period_end, then lapses). */
    public function cancel(Patient $patient, string $reason = '', ?string $actorId = null): ?OrganizationSubscription
    {
        $sub = $this->activeSubscription($patient);
        if (!$sub) {
            return null;
        }

        $sub->update([
            'auto_renew' => false,
            'notes'      => trim(($sub->notes ?? '') . "\nCancellation requested: {$reason}"),
            'updated_by' => $actorId,
        ]);

        return $sub->fresh();
    }

    /**
     * Does the patient's current plan include a feature key? Free-plan features
     * count too. A dependent also inherits features from a guardian's Premium
     * family plan (within the family_sharing seat limit), so callers get a single
     * source of truth for gating.
     */
    public function hasFeature(Patient $patient, string $featureKey): bool
    {
        $plan = $this->currentPlan($patient);
        if ($plan !== null && $plan->hasFeature($featureKey)) {
            return true;
        }

        $covering = $this->coveringSubscription($patient);
        return $covering !== null && $covering->plan->hasFeature($featureKey);
    }

    // ── Family sharing ─────────────────────────────────────────────────────────

    /**
     * The guardian's Premium subscription that covers this dependent, if any.
     * Coverage goes to the earliest-linked dependents up to the family_sharing
     * seat limit on the guardian's plan.
     */
    public function coveringSubscription(Patient $dependent): ?OrganizationSubscription
    {
        $links = FamilyLink::where('dependent_patient_id', $dependent->id)
            ->where('status', 'active')
            ->get();

        foreach ($links as $link) {
            $guardianPatient = User::find($link->guardian_user_id)?->patient;
            if (!$guardianPatient) {
                continue;
            }

            $sub = $this->activeSubscription($guardianPatient);
            if (!$sub || !$sub->plan || !$sub->plan->hasFeature('family_sharing')) {
                continue;
            }

            $limit   = $this->featureLimit($guardianPatient, 'family_sharing');
            $covered = $this->coveredDependentIds($guardianPatient, $limit);
            if (in_array($dependent->id, $covered, true)) {
                return $sub;
            }
        }

        return null;
    }

    /**
     * IDs of the guardian's dependents currently covered by their family plan,
     * capped at the seat limit (null = uncapped). Earliest links win the seats.
     *
     * @return array<int, string>
     */
    public function coveredDependentIds(Patient $guardianPatient, ?int $limit): array
    {
        $guardianUserId = User::where('patient_id', $guardianPatient->id)->value('id');
        if (!$guardianUserId) {
            return [];
        }

        $ids = FamilyLink::where('guardian_user_id', $guardianUserId)
            ->where('status', 'active')
            ->orderBy('created_at')
            ->pluck('dependent_patient_id');

        return ($limit !== null ? $ids->take($limit) : $ids)->values()->all();
    }

    /** Family seat usage for a guardian: [used, total] (total null = uncapped / no plan). */
    public function familySeats(Patient $guardianPatient): array
    {
        $sub = $this->activeSubscription($guardianPatient);
        if (!$sub || !$sub->plan || !$sub->plan->hasFeature('family_sharing')) {
            return ['used' => 0, 'total' => 0];
        }
        $limit = $this->featureLimit($guardianPatient, 'family_sharing');
        return ['used' => count($this->coveredDependentIds($guardianPatient, $limit)), 'total' => $limit];
    }

    /** Numeric limit for a metered feature (e.g. family_sharing => 5), or null. */
    public function featureLimit(Patient $patient, string $featureKey): ?int
    {
        $plan = $this->currentPlan($patient);
        $feature = $plan?->planFeatures()->where('feature_key', $featureKey)->first();
        return $feature?->limit_value !== null ? (int) $feature->limit_value : null;
    }

    // ── internals ────────────────────────────────────────────────────────────

    private function open(
        Patient $patient,
        SubscriptionPlan $plan,
        string $interval,
        array $billing,
        ?string $actorId
    ): OrganizationSubscription {
        $isTrialing  = $plan->trial_days > 0 && !$plan->isFree();
        $periodStart = now();
        $periodEnd   = $interval === 'annual'
            ? $periodStart->copy()->addYear()
            : $periodStart->copy()->addMonth();

        return OrganizationSubscription::create([
            'subscriber_type'      => 'patient',
            'subscriber_id'        => $patient->id,
            'interval'             => $interval,
            'plan_id'              => $plan->id,
            'status'               => $isTrialing ? 'trialing' : 'active',
            'trial_starts_at'      => $isTrialing ? $periodStart->toDateString() : null,
            'trial_ends_at'        => $isTrialing ? $periodStart->copy()->addDays($plan->trial_days)->toDateString() : null,
            'current_period_start' => $periodStart->toDateString(),
            'current_period_end'   => $periodEnd->toDateString(),
            'billing_email'        => $billing['email'] ?? null,
            'billing_name'         => $billing['name'] ?? null,
            'payment_method'       => $billing['payment_method'] ?? null,
            'auto_renew'           => $billing['auto_renew'] ?? !$plan->isFree(),
            'created_by'           => $actorId,
        ])->load('plan.planFeatures');
    }
}
