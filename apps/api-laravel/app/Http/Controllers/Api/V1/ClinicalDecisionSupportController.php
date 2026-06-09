<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAlert;
use App\Modules\ClinicalDecisionSupport\Services\AlertOverrideService;
use App\Modules\ClinicalDecisionSupport\Services\ClinicalDecisionSupportService;
use App\Modules\ClinicalDecisionSupport\Services\RuleEvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ClinicalDecisionSupportController
 *
 * REST JSON API surface for the Clinical Decision Support System (CDSS).
 * All routes protected by VerifyIntegrationClient middleware.
 *
 * CDSS SAFETY RULE — encoded in every response:
 * "Alerts are advisory only. They assist the clinician's review.
 *  They do NOT prevent prescribing or replace clinical judgment."
 *
 * Endpoints:
 *  POST   /v1/cdss/run                            — run all checks for a visit
 *  GET    /v1/cdss/visits/{visitId}/alerts         — active alerts for a visit
 *  GET    /v1/cdss/patients/{patientId}/alerts     — alert history for a patient
 *  POST   /v1/cdss/alerts/{alertId}/acknowledge    — acknowledge an alert
 *  POST   /v1/cdss/alerts/{alertId}/override       — override with documented reason
 *  POST   /v1/cdss/alerts/{alertId}/dismiss        — dismiss info-level alert
 *  GET    /v1/cdss/facilities/{facilityId}/summary — critical alert count for dashboard
 */
class ClinicalDecisionSupportController extends Controller
{
    public function __construct(
        private readonly ClinicalDecisionSupportService $cdss,
        private readonly RuleEvaluationService          $rules,
        private readonly AlertOverrideService           $overrides
    ) {}

    /**
     * Run all CDSS checks for a patient in a visit context.
     *
     * Body:
     * {
     *   facility_id:  uuid,
     *   patient_id:   uuid,
     *   visit_id:     uuid,
     *   drug_codes:   [string, ...],
     *   lab_results:  [{test_code, value, unit}, ...],
     *   allergies:    [string, ...],       // allergen codes
     *   is_pregnant:  bool,
     *   triggered_by: string              // staff_id or system
     * }
     *
     * Returns list of newly created alert IDs.
     */
    public function run(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'              => ['required', 'uuid'],
            'patient_id'               => ['required', 'uuid'],
            'visit_id'                 => ['required', 'uuid'],
            'drug_codes'               => ['nullable', 'array'],
            'drug_codes.*'             => ['string'],
            'lab_results'              => ['nullable', 'array'],
            'lab_results.*.test_code'  => ['required_with:lab_results', 'string'],
            'lab_results.*.value'      => ['required_with:lab_results', 'numeric'],
            'lab_results.*.unit'       => ['nullable', 'string'],
            'allergies'                => ['nullable', 'array'],
            'allergies.*'              => ['string'],
            'is_pregnant'              => ['nullable', 'boolean'],
            'triggered_by'             => ['nullable', 'string', 'max:255'],
        ]);

        // facility_id must come from middleware attributes when present
        $middlewareFacilityId = $request->attributes->get('facility_id');
        if ($middlewareFacilityId && $middlewareFacilityId !== $validated['facility_id']) {
            return response()->json([
                'error'   => 'forbidden',
                'message' => 'facility_id does not match your authenticated facility.',
            ], 403);
        }

        $firedAlertIds = $this->cdss->runChecksForVisit(
            $validated['facility_id'],
            $validated['patient_id'],
            $validated['visit_id'],
            [
                'drug_codes'  => $validated['drug_codes']  ?? [],
                'lab_results' => $validated['lab_results'] ?? [],
                'allergies'   => $validated['allergies']   ?? [],
                'is_pregnant' => $validated['is_pregnant'] ?? false,
            ],
            $validated['triggered_by'] ?? 'api'
        );

        $alerts = ClinicalAlert::whereIn('id', $firedAlertIds)
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
            ->get();

        return response()->json([
            'advisory_notice'  => 'Alerts are decision-support only and do not replace clinical judgment.',
            'alerts_fired'     => $alerts->count(),
            'alerts'           => $alerts->map(fn ($a) => $this->serializeAlert($a)),
        ]);
    }

    /**
     * Get active alerts for a visit — used by clinical UI on visit open.
     */
    public function visitAlerts(string $visitId): JsonResponse
    {
        $alerts = $this->cdss->getActiveAlertsForVisit($visitId);

        return response()->json([
            'advisory_notice' => 'Alerts are decision-support only and do not replace clinical judgment.',
            'visit_id'        => $visitId,
            'count'           => $alerts->count(),
            'alerts'          => $alerts->map(fn ($a) => $this->serializeAlert($a)),
        ]);
    }

    /**
     * Get alert history for a patient.
     */
    public function patientAlerts(string $patientId, Request $request): JsonResponse
    {
        $limit  = min((int) $request->input('limit', 50), 200);
        $alerts = $this->cdss->getAlertsForPatient($patientId, $limit);

        return response()->json([
            'patient_id' => $patientId,
            'count'      => $alerts->count(),
            'alerts'     => $alerts->map(fn ($a) => $this->serializeAlert($a)),
        ]);
    }

    /**
     * Acknowledge an alert — clinician confirms they have reviewed it.
     * Body: { staff_id: uuid }
     */
    public function acknowledge(string $alertId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'string'],
        ]);

        $alert = $this->cdss->acknowledgeAlert($alertId, $validated['staff_id']);

        return response()->json([
            'message' => 'Alert acknowledged.',
            'alert'   => $this->serializeAlert($alert),
        ]);
    }

    /**
     * Override an alert with a mandatory documented reason.
     * Body: { staff_id: uuid, reason: string, category?: string }
     * category: clinically_appropriate | patient_specific | risk_accepted | other
     */
    public function override(string $alertId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'string'],
            'reason'   => ['required', 'string', 'min:10', 'max:1000'],
            'category' => ['nullable', 'string', 'in:clinically_appropriate,patient_specific,risk_accepted,other'],
        ]);

        $override = $this->cdss->overrideAlert(
            $alertId,
            $validated['staff_id'],
            $validated['reason'],
            $validated['category'] ?? 'other'
        );

        return response()->json([
            'message'   => 'Alert overridden with documented reason.',
            'override'  => [
                'id'                => $override->id,
                'alert_id'          => $override->alert_id,
                'overridden_by'     => $override->overridden_by,
                'override_reason'   => $override->override_reason,
                'override_category' => $override->override_category,
                'overridden_at'     => $override->overridden_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Dismiss an info-level alert.
     * Body: { staff_id: uuid }
     */
    public function dismiss(string $alertId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'string'],
        ]);

        $this->cdss->dismissAlert($alertId, $validated['staff_id']);

        return response()->json(['message' => 'Alert dismissed.']);
    }

    /**
     * Critical unacknowledged alert summary for facility dashboard.
     */
    public function facilitySummary(string $facilityId, Request $request): JsonResponse
    {
        $middlewareFacilityId = $request->attributes->get('facility_id');
        if ($middlewareFacilityId && $middlewareFacilityId !== $facilityId) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        return response()->json([
            'facility_id'             => $facilityId,
            'critical_unacknowledged' => $this->cdss->getCriticalUnacknowledgedCount($facilityId),
        ]);
    }

    // ── Alert Overrides ───────────────────────────────────────────────────

    /**
     * Record a clinician's decision to proceed despite an alert.
     * High-risk alert types (allergy, critical drug interaction, etc.)
     * require a non-empty reason. All overrides are audited.
     *
     * CDSS SAFETY RULE: This records the override — it does NOT prevent
     * prescribing. The clinician retains full clinical responsibility.
     *
     * Body: { alert_id, overridden_by, reason, clinical_justification? }
     */
    public function recordOverride(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'alert_id'               => ['required', 'uuid'],
            'overridden_by'          => ['required', 'uuid'],
            'reason'                 => ['required', 'string', 'min:10', 'max:1000'],
            'clinical_justification' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $override = $this->overrides->recordOverride(
                $validated['alert_id'],
                $validated['overridden_by'],
                $validated['reason'],
                $validated['clinical_justification'] ?? null
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => 'Alert not found.'], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'advisory_notice' => 'Override recorded. Clinician retains full responsibility for this decision.',
            'message'         => $override->is_high_risk_override
                ? 'High-risk override recorded — flagged for QA review.'
                : 'Override recorded.',
            'data'            => $override,
        ], 201);
    }

    /**
     * List high-risk overrides pending QA review.
     * Used by clinical governance / QA dashboards.
     */
    public function highRiskOverridesPendingReview(): JsonResponse
    {
        $overrides = $this->overrides->getHighRiskOverridesPendingReview();
        return response()->json([
            'count' => $overrides->count(),
            'data'  => $overrides,
        ]);
    }

    /**
     * Mark a high-risk override as QA reviewed.
     * Body: { reviewed_by: uuid }
     */
    public function qaReviewOverride(string $overrideId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reviewed_by' => ['required', 'uuid'],
        ]);

        try {
            $override = $this->overrides->markQaReviewed($overrideId, $validated['reviewed_by']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => 'Override not found.'], 404);
        }

        return response()->json(['message' => 'Override marked as QA reviewed.', 'data' => $override]);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function serializeAlert(ClinicalAlert $a): array
    {
        return [
            'id'             => $a->id,
            'alert_type'     => $a->alert_type,
            'severity'       => $a->severity,
            'status'         => $a->status,
            'alert_message'  => $a->alert_message,
            'recommendation' => $a->recommendation,
            'context_data'   => $a->context_data,
            'patient_id'     => $a->patient_id,
            'visit_id'       => $a->visit_id,
            'triggered_by'   => $a->triggered_by,
            'triggered_at'   => $a->triggered_at?->toISOString(),
        ];
    }
}
