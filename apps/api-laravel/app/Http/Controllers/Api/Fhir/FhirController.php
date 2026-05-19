<?php

namespace App\Http\Controllers\Api\Fhir;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Visit;
use App\Modules\Fhir\Mappers\FhirDiagnosticReportMapper;
use App\Modules\Fhir\Mappers\FhirEncounterMapper;
use App\Modules\Fhir\Mappers\FhirMedicationRequestMapper;
use App\Modules\Fhir\Mappers\FhirPatientMapper;
use App\Modules\Fhir\Services\FhirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * FHIR R4 REST API Controller
 *
 * Read-only FHIR R4-compliant endpoints for healthcare data interoperability.
 * All responses are FHIR JSON (application/fhir+json).
 *
 * SECURITY NOTE: In production, these endpoints MUST be protected by OAuth2
 * scopes (SMART on FHIR) or API key authorization before exposing patient data.
 * Currently accepts any authenticated API request.
 */
class FhirController extends Controller
{
    public function __construct(private readonly FhirService $fhirService) {}

    /**
     * FHIR CapabilityStatement (server metadata).
     *
     * GET /api/fhir/R4/metadata
     */
    public function metadata(): JsonResponse
    {
        return $this->fhirResponse($this->fhirService->capabilityStatement());
    }

    // -------------------------------------------------------------------------
    // Patient
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/Patient/{id}
     */
    public function patient(string $id): JsonResponse
    {
        $patient = Patient::where('id', $id)
            ->orWhere('health_id', $id)
            ->firstOrFail();

        return $this->fhirResponse($this->fhirService->patient($patient));
    }

    /**
     * GET /api/fhir/R4/Patient
     * Search params: identifier (health_id), family, given
     */
    public function searchPatient(Request $request): JsonResponse
    {
        $query = Patient::query()->limit(20);

        if ($identifier = $request->query('identifier')) {
            $query->where('health_id', $identifier);
        }
        if ($family = $request->query('family')) {
            $query->where('last_name', 'like', "%{$family}%");
        }
        if ($given = $request->query('given')) {
            $query->where('first_name', 'like', "%{$given}%");
        }

        $patients = $query->get();
        $entries  = $patients->map(fn ($p) => [
            'fullUrl'  => url('/api/fhir/R4/Patient/' . $p->id),
            'resource' => $this->fhirService->patient($p),
        ])->values()->all();

        return $this->fhirResponse([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => count($entries),
            'entry'        => $entries,
        ]);
    }

    /**
     * Full patient bundle (all resources for one patient).
     *
     * GET /api/fhir/R4/Patient/{id}/$everything
     */
    public function patientEverything(string $id): JsonResponse
    {
        $patient = Patient::where('id', $id)
            ->orWhere('health_id', $id)
            ->firstOrFail();

        return $this->fhirResponse($this->fhirService->patientBundle($patient));
    }

    // -------------------------------------------------------------------------
    // Encounter
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/Encounter/{id}
     */
    public function encounter(string $id): JsonResponse
    {
        $visit = Visit::with(['facility', 'provider'])->findOrFail($id);
        return $this->fhirResponse($this->fhirService->encounter($visit));
    }

    /**
     * GET /api/fhir/R4/Encounter?patient={patientId}
     */
    public function searchEncounter(Request $request): JsonResponse
    {
        $query = Visit::query()->with(['facility', 'provider'])->latest()->limit(50);

        if ($patientId = $request->query('patient')) {
            $patientId = str_replace('Patient/', '', $patientId);
            $query->where('patient_id', $patientId);
        }

        $encounters = $query->get();
        $mapper     = app(FhirEncounterMapper::class);
        $entries    = $encounters->map(fn ($v) => [
            'fullUrl'  => url('/api/fhir/R4/Encounter/' . $v->id),
            'resource' => $mapper->toFhir($v),
        ])->values()->all();

        return $this->fhirResponse([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => count($entries),
            'entry'        => $entries,
        ]);
    }

    // -------------------------------------------------------------------------
    // DiagnosticReport
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/DiagnosticReport/{id}
     */
    public function diagnosticReport(string $id): JsonResponse
    {
        $order = LabOrder::with('results')->findOrFail($id);
        return $this->fhirResponse($this->fhirService->diagnosticReport($order));
    }

    /**
     * GET /api/fhir/R4/DiagnosticReport?patient={patientId}&status={status}
     */
    public function searchDiagnosticReport(Request $request): JsonResponse
    {
        $query = LabOrder::with('results')->latest('ordered_at')->limit(50);

        if ($patientId = $request->query('patient')) {
            $patientId = str_replace('Patient/', '', $patientId);
            $query->where('patient_id', $patientId);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $orders  = $query->get();
        $mapper  = app(FhirDiagnosticReportMapper::class);
        $entries = $orders->map(fn ($o) => [
            'fullUrl'  => url('/api/fhir/R4/DiagnosticReport/' . $o->id),
            'resource' => $mapper->toFhir($o),
        ])->values()->all();

        return $this->fhirResponse([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => count($entries),
            'entry'        => $entries,
        ]);
    }

    // -------------------------------------------------------------------------
    // MedicationRequest
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/MedicationRequest/{id}
     * ID is a PrescriptionItem ID.
     */
    public function medicationRequest(string $id): JsonResponse
    {
        $item = \App\Models\PrescriptionItem::with('prescription')->findOrFail($id);
        $mapper = app(FhirMedicationRequestMapper::class);
        return $this->fhirResponse($mapper->itemToFhir($item, $item->prescription));
    }

    /**
     * GET /api/fhir/R4/MedicationRequest?patient={patientId}
     */
    public function searchMedicationRequest(Request $request): JsonResponse
    {
        $query = Prescription::with('items')->latest('prescribed_at')->limit(50);

        if ($patientId = $request->query('patient')) {
            $patientId = str_replace('Patient/', '', $patientId);
            $query->where('patient_id', $patientId);
        }

        $prescriptions = $query->get();
        $mapper        = app(FhirMedicationRequestMapper::class);

        $entries = [];
        foreach ($prescriptions as $prescription) {
            foreach ($mapper->prescriptionToBundle($prescription) as $med) {
                $entries[] = [
                    'fullUrl'  => url('/api/fhir/R4/MedicationRequest/' . $med['id']),
                    'resource' => $med,
                ];
            }
        }

        return $this->fhirResponse([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => count($entries),
            'entry'        => $entries,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function fhirResponse(array $resource, int $status = 200): JsonResponse
    {
        return response()->json($resource, $status, [
            'Content-Type' => 'application/fhir+json; fhirVersion=4.0',
        ]);
    }
}
