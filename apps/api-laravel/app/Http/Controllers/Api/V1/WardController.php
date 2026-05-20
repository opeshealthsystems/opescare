<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\WardManagement\Services\AdmissionService;
use App\Modules\WardManagement\Services\WardService;
use App\Modules\WardManagement\Services\DischargePlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WardController — Ward, Admission & Bed Management API.
 *
 * Covers patient admission, bed assignment, ward transfers,
 * nursing rounds, inpatient medication administration, and discharge planning.
 *
 * CDSS SAFETY RULE: Medication administration records are advisory tools.
 * Clinical staff retain full responsibility for all administration decisions.
 */
class WardController extends Controller
{
    public function __construct(
        private AdmissionService        $admissions,
        private WardService             $wards,
        private DischargePlanningService $discharge
    ) {}

    // ── Admissions ─────────────────────────────────────────────────────────

    public function admit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'       => ['required', 'uuid'],
            'facility_id'      => ['required', 'uuid'],
            'ward_id'          => ['nullable', 'uuid'],
            'bed_id'           => ['nullable', 'uuid'],
            'admission_reason' => ['required', 'string'],
            'admitting_doctor' => ['required', 'uuid'],
            'admission_type'   => ['required', 'in:emergency,elective,transfer'],
        ]);

        return response()->json(
            $this->admissions->admit($validated, $request->user()->id),
            201
        );
    }

    public function assignBed(Request $request, string $admissionId): JsonResponse
    {
        $validated = $request->validate([
            'bed_id'  => ['required', 'uuid'],
            'notes'   => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->wards->assignBed($admissionId, $validated['bed_id'], $request->user()->id, $validated['notes'] ?? null)
        );
    }

    public function transferBed(Request $request, string $admissionId): JsonResponse
    {
        $validated = $request->validate([
            'new_bed_id' => ['required', 'uuid'],
            'reason'     => ['required', 'string'],
        ]);

        return response()->json(
            $this->wards->transferBed($admissionId, $validated, $request->user()->id)
        );
    }

    // ── Bed Availability ───────────────────────────────────────────────────

    public function getBedAvailability(Request $request): JsonResponse
    {
        return response()->json(
            $this->wards->getBedAvailability($request->input('facility_id'), $request->input('ward_id'))
        );
    }

    // ── Nursing Rounds ─────────────────────────────────────────────────────

    public function recordNursingRound(Request $request, string $admissionId): JsonResponse
    {
        $validated = $request->validate([
            'notes'      => ['required', 'string'],
            'vitals'     => ['nullable', 'array'],
            'concerns'   => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->wards->recordNursingRound($admissionId, $validated, $request->user()->id),
            201
        );
    }

    // ── Discharge Planning ─────────────────────────────────────────────────

    public function initiateDischargePlan(Request $request, string $admissionId): JsonResponse
    {
        $validated = $request->validate([
            'planned_discharge_date' => ['required', 'date', 'after:today'],
            'discharge_notes'        => ['nullable', 'string'],
            'follow_up_required'     => ['required', 'boolean'],
        ]);

        return response()->json(
            $this->discharge->initiatePlan($admissionId, $validated, $request->user()->id),
            201
        );
    }

    public function discharge(Request $request, string $admissionId): JsonResponse
    {
        $validated = $request->validate([
            'discharge_summary' => ['required', 'string'],
            'discharge_type'    => ['required', 'in:home,transfer,against_advice,deceased'],
            'follow_up_date'    => ['nullable', 'date', 'after:today'],
        ]);

        return response()->json(
            $this->admissions->discharge($admissionId, $validated, $request->user()->id)
        );
    }
}
