<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PregnancyRecord;
use App\Modules\Maternity\Services\MaternityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaternityController extends Controller
{
    public function __construct(private readonly MaternityService $service) {}

    public function index(string $patientId): JsonResponse
    {
        $records = PregnancyRecord::where('patient_id', $patientId)
            ->with(['facility', 'provider'])
            ->orderByDesc('registered_at')
            ->get();

        return response()->json(['data' => $records]);
    }

    public function store(Request $request, string $patientId): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'      => ['required', 'uuid', 'exists:facilities,id'],
            'provider_id'      => ['required', 'uuid', 'exists:users,id'],
            'gravida'          => ['required', 'integer', 'min:1'],
            'para'             => ['required', 'integer', 'min:0'],
            'lmp'              => ['nullable', 'date'],
            'edd'              => ['nullable', 'date'],
            'pregnancy_status' => ['required', 'in:active,delivered,miscarriage,stillbirth,ectopic,terminated'],
            'blood_type'       => ['nullable', 'in:A,B,AB,O'],
            'rhesus_factor'    => ['nullable', 'in:positive,negative'],
            'high_risk'        => ['boolean'],
            'risk_factors'     => ['nullable', 'array'],
            'notes'            => ['nullable', 'string'],
            'registered_at'    => ['nullable', 'date'],
        ]);

        $validated['patient_id']    = $patientId;
        $validated['registered_at'] = $validated['registered_at'] ?? now();

        $record = $this->service->registerPregnancy($validated);

        return response()->json(['data' => $record], 201);
    }

    public function show(string $id): JsonResponse
    {
        $record = PregnancyRecord::with(['patient', 'facility', 'provider'])->findOrFail($id);
        return response()->json(['data' => $record]);
    }

    public function antenatalVisits(string $id): JsonResponse
    {
        $record = PregnancyRecord::findOrFail($id);
        $visits = $record->antenatalVisits()->with('provider')->orderBy('visit_date')->get();
        return response()->json(['data' => $visits]);
    }

    public function storeAntenatalVisit(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'            => ['required', 'uuid', 'exists:patients,id'],
            'facility_id'           => ['required', 'uuid', 'exists:facilities,id'],
            'provider_id'           => ['required', 'uuid', 'exists:users,id'],
            'visit_date'            => ['required', 'date'],
            'gestational_age_weeks' => ['required', 'integer', 'min:0', 'max:45'],
            'gestational_age_days'  => ['required', 'integer', 'min:0', 'max:6'],
            'fundal_height_cm'      => ['nullable', 'numeric', 'min:0'],
            'fetal_heart_rate'      => ['nullable', 'integer', 'min:60', 'max:200'],
            'presentation'          => ['nullable', 'in:cephalic,breech,transverse,unknown'],
            'weight_kg'             => ['nullable', 'numeric', 'min:20', 'max:200'],
            'bp_systolic'           => ['nullable', 'integer', 'min:60', 'max:250'],
            'bp_diastolic'          => ['nullable', 'integer', 'min:40', 'max:150'],
            'urine_protein'         => ['nullable', 'in:negative,trace,1+,2+,3+,4+'],
            'urine_glucose'         => ['nullable', 'in:negative,trace,positive'],
            'oedema'                => ['nullable', 'in:none,mild,moderate,severe'],
            'notes'                 => ['nullable', 'string'],
        ]);

        $visit = $this->service->recordAntenatalVisit($id, $validated);
        return response()->json(['data' => $visit], 201);
    }

    public function deliveries(string $id): JsonResponse
    {
        $record     = PregnancyRecord::findOrFail($id);
        $deliveries = $record->deliveryRecords()->with('provider')->get();
        return response()->json(['data' => $deliveries]);
    }

    public function storeDelivery(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'            => ['required', 'uuid', 'exists:patients,id'],
            'facility_id'           => ['required', 'uuid', 'exists:facilities,id'],
            'provider_id'           => ['required', 'uuid', 'exists:users,id'],
            'delivery_date'         => ['required', 'date'],
            'delivery_mode'         => ['required', 'in:svd,assisted_vaginal,caesarean,other'],
            'indication'            => ['nullable', 'string'],
            'duration_labour_hours' => ['nullable', 'numeric', 'min:0'],
            'birth_weight_grams'    => ['required', 'integer', 'min:300', 'max:7000'],
            'apgar_1min'            => ['nullable', 'integer', 'min:0', 'max:10'],
            'apgar_5min'            => ['nullable', 'integer', 'min:0', 'max:10'],
            'neonatal_outcome'      => ['required', 'in:live,stillbirth,early_neonatal_death'],
            'complications'         => ['nullable', 'string'],
            'notes'                 => ['nullable', 'string'],
        ]);

        $delivery = $this->service->recordDelivery($id, $validated);
        return response()->json(['data' => $delivery], 201);
    }
}
