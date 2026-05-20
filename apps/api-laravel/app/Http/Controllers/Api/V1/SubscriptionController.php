<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Services\SubscriptionService;
use App\Modules\Subscription\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SubscriptionController — SaaS Subscription & Billing API.
 *
 * IMPORTANT: This is SaaS subscription billing (organization plans).
 * This is NOT patient billing. Patient billing uses BillingController.
 * Do NOT mix these two billing domains.
 *
 * Covers: plan management, organization subscriptions, trials, usage limits,
 * module entitlements, upgrade/downgrade, cancellation.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptions,
        private PlanLimitService    $limits
    ) {}

    // ── Plans ──────────────────────────────────────────────────────────────

    public function listPlans(): JsonResponse
    {
        return response()->json($this->subscriptions->listActivePlans());
    }

    public function showPlan(string $planId): JsonResponse
    {
        return response()->json($this->subscriptions->getPlan($planId));
    }

    // ── Organization Subscriptions ─────────────────────────────────────────

    public function getMySubscription(Request $request): JsonResponse
    {
        return response()->json(
            $this->subscriptions->getForOrganization($request->user()->organization_id)
        );
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id'         => ['required', 'uuid'],
            'organization_id' => ['required', 'uuid'],
            'billing_cycle'   => ['required', 'in:monthly,annual'],
        ]);

        return response()->json(
            $this->subscriptions->subscribe($validated, $request->user()->id),
            201
        );
    }

    public function upgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'new_plan_id'     => ['required', 'uuid'],
            'organization_id' => ['required', 'uuid'],
        ]);

        return response()->json(
            $this->subscriptions->changePlan($validated['organization_id'], $validated['new_plan_id'], $request->user()->id)
        );
    }

    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'uuid'],
            'reason'          => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->subscriptions->cancel($validated['organization_id'], $validated['reason'] ?? null, $request->user()->id)
        );
    }

    // ── Usage & Limits ─────────────────────────────────────────────────────

    public function getUsage(Request $request): JsonResponse
    {
        return response()->json(
            $this->limits->getCurrentUsage($request->user()->organization_id)
        );
    }

    public function checkLimit(Request $request, string $featureKey): JsonResponse
    {
        $allowed = $this->limits->isWithinLimit($request->user()->organization_id, $featureKey);
        return response()->json(['feature' => $featureKey, 'allowed' => $allowed]);
    }
}
