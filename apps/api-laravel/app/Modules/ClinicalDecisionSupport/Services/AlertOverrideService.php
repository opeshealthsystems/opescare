<?php

namespace App\Modules\ClinicalDecisionSupport\Services;

use App\Models\AlertOverride;
use App\Models\ClinicalAlert;
use App\Models\AuditEvent;

/**
 * AlertOverrideService — Records and audits clinical alert overrides.
 *
 * CDSS SAFETY RULE: Alert overrides are not blocks — they are advisory dismissals.
 * The system records the override and the reason. The clinician retains full
 * responsibility for their decision. Overrides must never be silent.
 *
 * High-risk overrides (e.g. allergy, critical drug interaction) require
 * a reason and are flagged for QA review.
 */
class AlertOverrideService
{
    private const HIGH_RISK_ALERT_TYPES = [
        'allergy',
        'critical_drug_interaction',
        'critical_lab_result',
        'duplicate_controlled_substance',
    ];

    /**
     * Record a clinician's decision to proceed despite an active alert.
     *
     * @throws \InvalidArgumentException if high-risk override lacks a reason
     */
    public function recordOverride(
        string $alertId,
        string $overriddenBy,
        string $reason,
        string $clinicalJustification = null
    ): AlertOverride {
        $alert = ClinicalAlert::findOrFail($alertId);

        if (in_array($alert->alert_type, self::HIGH_RISK_ALERT_TYPES) && empty($reason)) {
            throw new \InvalidArgumentException(
                "A stated reason is required to override a {$alert->alert_type} alert."
            );
        }

        $override = AlertOverride::create([
            'clinical_alert_id'       => $alertId,
            'overridden_by'           => $overriddenBy,
            'reason'                  => $reason,
            'clinical_justification'  => $clinicalJustification,
            'is_high_risk_override'   => in_array($alert->alert_type, self::HIGH_RISK_ALERT_TYPES),
            'overridden_at'           => now(),
        ]);

        // Mark the alert as overridden (not dismissed — the alert record persists)
        $alert->update(['status' => 'overridden', 'overridden_at' => now()]);

        AuditEvent::create([
            'actor_id'  => $overriddenBy,
            'action'    => 'clinical_alert.overridden',
            'module'    => 'clinical_decision_support',
            'metadata'  => [
                'alert_id'   => $alertId,
                'alert_type' => $alert->alert_type,
                'reason'     => $reason,
                'high_risk'  => $override->is_high_risk_override,
            ],
        ]);

        return $override;
    }

    /** Returns all high-risk overrides pending QA review. */
    public function getHighRiskOverridesPendingReview(): \Illuminate\Database\Eloquent\Collection
    {
        return AlertOverride::where('is_high_risk_override', true)
            ->whereNull('qa_reviewed_at')
            ->orderByDesc('overridden_at')
            ->get();
    }

    public function markQaReviewed(string $overrideId, string $reviewedBy): AlertOverride
    {
        $override = AlertOverride::findOrFail($overrideId);
        $override->update([
            'qa_reviewed_by' => $reviewedBy,
            'qa_reviewed_at' => now(),
        ]);
        return $override->fresh();
    }
}
