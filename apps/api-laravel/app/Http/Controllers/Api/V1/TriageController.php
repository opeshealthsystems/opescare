<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Triage\Services\TriageService;
use App\Modules\Triage\Services\TriageScoringService;
use App\Modules\Triage\Services\EmergencyWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TriageController — Triage & Emergency Workflow API.
 *
 * CDSS SAFETY RULE: Triage scores are advisory only.
 * The system assists the triage nurse/clinician — it does not replace their judgment.
 * No automated clinical decision may be taken solely on a computed triage score.
 */
class TriageController extends Controller
{
    public function __construct(
        private TriageService          $triage,
        private TriageScoringService   $scoring,
        private EmergencyWorkflowService $emergency
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'       => ['required', 'uuid'],
            'facility_id'      => ['required', 'uuid'],
            'chief_complaint'  => ['required', 'string', 'max:1000'],
            'arrival_mode'     => ['required', 'in:walk_in,ambulance,referral,emergency'],
            'vitals'           => ['nullable', 'array'],
            'vitals.bp_systolic'   => ['nullable', 'integer'],
            'vitals.bp_diastolic'  => ['nullable', 'integer'],
            'vitals.heart_rate'    => ['nullable', 'integer'],
            'vitals.temperature'   => ['nullable', 'numeric'],
            'vitals.spo2'          => ['nullable', 'integer'],
            'vitals.respiratory_rate' => ['nullable', 'integer'],
            'vitals.weight_kg'     => ['nullable', 'numeric'],
        ]);

        $record = $this->triage->recordTriage($validated, $request->user()->id);

        return response()->json($record, 201);
    }

    public function score(Request $request, string $triageId): JsonResponse
    {
        $score = $this->scoring->computeScore($triageId);

        return response()->json([
            'triage_id'   => $triageId,
            'score'       => $score,
            'disclaimer'  => 'Advisory score only. Clinical judgment of the assessing clinician takes precedence.',
        ]);
    }

    public function reassess(Request $request, string $triageId): JsonResponse
    {
        $validated = $request->validate([
            'reason'  => ['required', 'string'],
            'vitals'  => ['nullable', 'array'],
            'new_priority' => ['nullable', 'in:immediate,urgent,less_urgent,non_urgent'],
        ]);

        return response()->json(
            $this->triage->reassess($triageId, $validated, $request->user()->id)
        );
    }

    public function escalateToEmergency(Request $request, string $triageId): JsonResponse
    {
        $validated = $request->validate([
            'reason'   => ['required', 'string'],
            'location' => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->emergency->escalateFromTriage($triageId, $validated, $request->user()->id)
        );
    }

    public function listActive(Request $request): JsonResponse
    {
        $facilityId = $request->attributes->get('facility_id');
        if (!$facilityId) {
            return response()->json(['message' => 'Facility could not be resolved.', 'error_code' => 'FACILITY_UNRESOLVABLE'], 403);
        }

        return response()->json(
            $this->triage->listActiveForFacility($facilityId)
        );
    }
}
