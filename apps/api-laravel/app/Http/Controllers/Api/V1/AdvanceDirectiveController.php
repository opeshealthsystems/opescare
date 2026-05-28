<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdvanceDirective;
use App\Services\Clinical\AdvanceDirectiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvanceDirectiveController extends Controller
{
    public function __construct(private readonly AdvanceDirectiveService $service)
    {
    }

    /** GET /api/v1/patients/{patientId}/advance-directives */
    public function index(string $patientId): JsonResponse
    {
        $directives = $this->service->getActiveForPatient($patientId);
        return response()->json(['data' => $directives]);
    }

    /** POST /api/v1/patients/{patientId}/advance-directives */
    public function store(Request $request, string $patientId): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'                   => 'required|uuid|exists:facilities,id',
            'directive_type'                => 'required|in:dnr,living_will,healthcare_proxy,polst,organ_donation,other',
            'effective_date'                => 'required|date',
            'expiry_date'                   => 'nullable|date|after:effective_date',
            'document_path'                 => 'nullable|string|max:500',
            'witness_name'                  => 'nullable|string|max:255',
            'witness_date'                  => 'nullable|date',
            'healthcare_proxy_name'         => 'nullable|string|max:255',
            'healthcare_proxy_phone'        => 'nullable|string|max:30',
            'healthcare_proxy_relationship' => 'nullable|string|max:100',
            'instructions'                  => 'nullable|string',
        ]);

        $directive = $this->service->register(array_merge($validated, ['patient_id' => $patientId]));

        return response()->json(['data' => $directive], 201);
    }

    /** GET /api/v1/patients/{patientId}/advance-directives/{id} */
    public function show(string $patientId, string $id): JsonResponse
    {
        $directive = AdvanceDirective::where('patient_id', $patientId)->findOrFail($id);
        return response()->json(['data' => $directive]);
    }

    /** DELETE /api/v1/patients/{patientId}/advance-directives/{id} — revoke */
    public function destroy(Request $request, string $patientId, string $id): JsonResponse
    {
        $directive = $this->service->revoke($id, $request->user()->id);
        return response()->json(['data' => $directive, 'message' => 'Directive revoked.']);
    }
}
