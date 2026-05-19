<?php

namespace App\Http\Controllers\Api\Fhir;

use App\Http\Controllers\Controller;
use App\Models\ConsentGrant;
use App\Models\Facility;
use App\Models\LabOrder;
use App\Models\OfficialDocument;
use App\Models\Patient;
use App\Models\PatientInsurancePolicy;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Visit;
use App\Modules\Fhir\Mappers\FhirDiagnosticReportMapper;
use App\Modules\Fhir\Mappers\FhirEncounterMapper;
use App\Modules\Fhir\Mappers\FhirMedicationRequestMapper;
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
    // Practitioner (Phase 30)
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/Practitioner/{id}
     */
    public function practitioner(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return $this->fhirResponse($this->fhirService->practitioner($user));
    }

    /**
     * GET /api/fhir/R4/Practitioner?facility={facilityId}
     */
    public function searchPractitioner(Request $request): JsonResponse
    {
        $query = User::query()->limit(50);

        if ($facilityId = $request->query('facility')) {
            $query->where('primary_facility_id', $facilityId);
        }

        return $this->fhirResponse($this->fhirService->practitionerBundle($query->get()));
    }

    // -------------------------------------------------------------------------
    // Organization (Phase 30)
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/Organization/{id}
     */
    public function organization(string $id): JsonResponse
    {
        $facility = Facility::findOrFail($id);
        return $this->fhirResponse($this->fhirService->organization($facility));
    }

    /**
     * GET /api/fhir/R4/Organization?active={true|false}
     */
    public function searchOrganization(Request $request): JsonResponse
    {
        $query = Facility::query()->limit(50);

        if ($request->query('active') !== null) {
            $status = filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN) ? 'active' : 'inactive';
            $query->where('status', $status);
        }

        return $this->fhirResponse($this->fhirService->organizationBundle($query->get()));
    }

    // -------------------------------------------------------------------------
    // DocumentReference (Phase 30)
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/DocumentReference/{id}
     */
    public function documentReference(string $id): JsonResponse
    {
        $doc = OfficialDocument::findOrFail($id);
        return $this->fhirResponse($this->fhirService->documentReference($doc));
    }

    /**
     * GET /api/fhir/R4/DocumentReference?patient={patientId}&status={status}
     */
    public function searchDocumentReference(Request $request): JsonResponse
    {
        $query = OfficialDocument::query()->latest('issued_at')->limit(50);

        if ($patientId = $request->query('patient')) {
            $patientId = str_replace('Patient/', '', $patientId);
            $query->where('patient_id', $patientId);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return $this->fhirResponse($this->fhirService->documentReferenceBundle($query->get()));
    }

    // -------------------------------------------------------------------------
    // Consent (Phase 30)
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/Consent/{id}
     */
    public function consent(string $id): JsonResponse
    {
        $grant = ConsentGrant::findOrFail($id);
        return $this->fhirResponse($this->fhirService->consent($grant));
    }

    /**
     * GET /api/fhir/R4/Consent?patient={patientId}&status={status}
     */
    public function searchConsent(Request $request): JsonResponse
    {
        $query = ConsentGrant::query()->latest()->limit(50);

        if ($patientId = $request->query('patient')) {
            $patientId = str_replace('Patient/', '', $patientId);
            $query->where('patient_id', $patientId);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return $this->fhirResponse($this->fhirService->consentBundle($query->get()));
    }

    // -------------------------------------------------------------------------
    // Coverage (Phase 30)
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/Coverage/{id}
     */
    public function coverage(string $id): JsonResponse
    {
        $policy = PatientInsurancePolicy::findOrFail($id);
        return $this->fhirResponse($this->fhirService->coverage($policy));
    }

    /**
     * GET /api/fhir/R4/Coverage?patient={patientId}&status={status}
     */
    public function searchCoverage(Request $request): JsonResponse
    {
        $query = PatientInsurancePolicy::query()->latest()->limit(50);

        if ($patientId = $request->query('patient')) {
            $patientId = str_replace('Patient/', '', $patientId);
            $query->where('patient_id', $patientId);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return $this->fhirResponse($this->fhirService->coverageBundle($query->get()));
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
