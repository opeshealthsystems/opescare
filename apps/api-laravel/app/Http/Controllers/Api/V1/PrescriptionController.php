<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Services\Documents\DocumentIssuanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function __construct(private readonly DocumentIssuanceService $issuance) {}

    public function store(Request $request): JsonResponse
    {
        $facilityId = $request->attributes->get('facility_id');
        if (! $facilityId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'patient_id'                   => 'required|uuid',
            'prescriber_id'                => 'required|uuid',
            'visit_id'                     => 'nullable|uuid',
            'items'                        => 'required|array|min:1',
            'items.*.drug_name'            => 'required|string|max:200',
            'items.*.drug_code'            => 'nullable|string|max:50',
            'items.*.dosage'               => 'required|string|max:100',
            'items.*.frequency'            => 'required|string|max:100',
            'items.*.duration'             => 'nullable|string|max:100',
            'items.*.quantity'             => 'nullable|integer|min:1',
            'items.*.route'                => 'nullable|string|max:50',
            'items.*.notes'                => 'nullable|string',
            'notes'                        => 'nullable|string',
            'is_discharge_prescription'    => 'nullable|boolean',
        ]);

        $prescription = Prescription::create([
            'patient_id'                => $validated['patient_id'],
            'prescriber_id'             => $validated['prescriber_id'],
            'facility_id'               => $facilityId,
            'visit_id'                  => $validated['visit_id'] ?? null,
            'notes'                     => $validated['notes'] ?? null,
            'is_discharge_prescription' => $validated['is_discharge_prescription'] ?? false,
            'status'                    => 'active',
        ]);

        foreach ($validated['items'] as $item) {
            PrescriptionItem::create(array_merge($item, ['prescription_id' => $prescription->id]));
        }

        try {
            $this->issuance->issueFromModel(
                'RX',
                'Prescription — ' . count($validated['items']) . ' item(s)',
                [
                    'prescription_id' => $prescription->id,
                    'patient_id'      => $validated['patient_id'],
                    'items'           => $validated['items'],
                ],
                $facilityId,
                $validated['patient_id'],
                null,
                $validated['prescriber_id'],
            );
        } catch (\Throwable $e) {
            // Document issuance failure is non-fatal
        }

        return response()->json(['data' => $prescription->load('items')], 201);
    }

    public function show(Prescription $prescription): JsonResponse
    {
        return response()->json(['data' => $prescription->load(['items', 'prescriber', 'patient'])]);
    }

    public function index(Request $request): JsonResponse
    {
        $facilityId = $request->attributes->get('facility_id');
        if (! $facilityId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $prescriptions = Prescription::where('facility_id', $facilityId)
            ->with(['patient', 'prescriber'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $prescriptions]);
    }
}
