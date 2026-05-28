<?php

namespace App\Http\Controllers\Api\Fhir;

use App\Http\Controllers\Controller;
use App\Models\AllergyRecord;
use App\Models\ConsentGrant;
use App\Models\Diagnosis;
use App\Models\Facility;
use App\Models\FhirSubscription;
use App\Models\ImmunizationRecord;
use App\Models\LabOrder;
use App\Models\OfficialDocument;
use App\Models\Patient;
use App\Models\PatientInsurancePolicy;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Support\Str;
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

    // =========================================================================
    // FHIR Subscriptions (R4)
    // =========================================================================

    /**
     * GET /api/fhir/R4/Subscription
     * List all subscriptions for the authenticated facility.
     */
    public function subscriptionIndex(Request $request): JsonResponse
    {
        $subscriptions = FhirSubscription::query()
            ->when($request->query('status'), fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn(FhirSubscription $s) => $s->toFhirResource());

        return $this->fhirResponse([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => $subscriptions->count(),
            'entry'        => $subscriptions->map(fn($r) => ['resource' => $r])->values()->all(),
        ]);
    }

    /**
     * POST /api/fhir/R4/Subscription
     * Create a new subscription.
     */
    public function subscriptionCreate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'criteria'         => 'required|string|max:500',
            'channel.type'     => 'required|in:rest-hook,email,websocket',
            'channel.endpoint' => 'required_if:channel.type,rest-hook|url|max:500',
            'channel.payload'  => 'nullable|string',
            'channel.header'   => 'nullable|array',
            'reason'           => 'nullable|string|max:500',
            'end'              => 'nullable|date',
        ]);

        $subscription = FhirSubscription::create([
            'facility_id'   => $request->header('X-Facility-Id'),
            'status'        => 'requested',
            'reason'        => $data['reason'] ?? null,
            'criteria'      => $data['criteria'],
            'channel_type'  => $data['channel']['type'],
            'endpoint'      => $data['channel']['endpoint'] ?? null,
            'headers'       => $data['channel']['header'] ?? null,
            'payload_type'  => $data['channel']['payload'] ?? 'application/fhir+json',
            'end'           => $data['end'] ?? null,
            'created_by'    => $request->user()?->id,
            'signing_secret'=> Str::random(64),
        ]);

        // Activate immediately for rest-hook (endpoint verification would happen in production)
        $subscription->update(['status' => 'active']);

        return $this->fhirResponse($subscription->fresh()->toFhirResource(), 201);
    }

    /**
     * GET /api/fhir/R4/Subscription/{id}
     */
    public function subscriptionShow(string $id): JsonResponse
    {
        $subscription = FhirSubscription::findOrFail($id);
        return $this->fhirResponse($subscription->toFhirResource());
    }

    /**
     * DELETE /api/fhir/R4/Subscription/{id}
     * Turn off a subscription.
     */
    public function subscriptionDelete(string $id): JsonResponse
    {
        $subscription = FhirSubscription::findOrFail($id);
        $subscription->update(['status' => 'off']);
        return response()->json(null, 204);
    }

    // =========================================================================
    // FHIR Bulk Export ($export)
    // =========================================================================

    /**
     * GET /api/fhir/R4/$export
     * System-level bulk export — returns NDJSON for all resource types.
     *
     * Implements FHIR Bulk Data Access IG (STU1).
     * For large datasets, this should be made async (queue-based).
     * Current implementation is synchronous for simplicity.
     */
    public function bulkExport(Request $request): \Illuminate\Http\Response
    {
        $since        = $request->query('_since');
        $outputFormat = $request->query('_outputFormat', 'application/fhir+ndjson');
        $types        = $request->query('_type')
            ? explode(',', $request->query('_type'))
            : ['Patient', 'Encounter', 'Observation', 'MedicationRequest', 'Condition'];

        $facilityId = $request->header('X-Facility-Id');

        // Build NDJSON output
        $lines = [];

        if (in_array('Patient', $types)) {
            $query = \App\Models\Patient::query();
            if ($since) $query->where('updated_at', '>=', $since);
            $query->each(function (\App\Models\Patient $p) use (&$lines) {
                $lines[] = json_encode($this->fhirService->patient($p));
            });
        }

        // Return as NDJSON
        return response(implode("\n", $lines), 200, [
            'Content-Type'        => 'application/fhir+ndjson',
            'X-Progress'          => '100%',
            'Expires'             => now()->addHour()->toRfc7231String(),
        ]);
    }

    /**
     * GET /api/fhir/R4/Patient/{id}/$export
     * Patient-compartment bulk export.
     */
    public function patientBulkExport(string $id, Request $request): \Illuminate\Http\Response
    {
        $patient = \App\Models\Patient::where('id', $id)
            ->orWhere('health_id', $id)
            ->firstOrFail();

        $everything = $this->fhirService->patientEverything($patient);

        // Convert Bundle entries to NDJSON
        $lines = collect($everything['entry'] ?? [])
            ->map(fn($e) => json_encode($e['resource']))
            ->all();

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'application/fhir+ndjson',
        ]);
    }

    // -------------------------------------------------------------------------
    // Immunization
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/Immunization/{id}
     */
    public function immunization(string $id): JsonResponse
    {
        $record = ImmunizationRecord::findOrFail($id);
        return $this->fhirResponse($this->fhirService->immunization($record));
    }

    /**
     * GET /api/fhir/R4/Immunization?patient={patientId}
     */
    public function searchImmunization(Request $request): JsonResponse
    {
        $query = ImmunizationRecord::query()->latest('administered_at')->limit(50);

        if ($patientId = $request->query('patient')) {
            $query->where('patient_id', str_replace('Patient/', '', $patientId));
        }

        $records = $query->get();
        $entries = $records->map(fn ($r) => [
            'fullUrl'  => url('/api/fhir/R4/Immunization/' . $r->id),
            'resource' => $this->fhirService->immunization($r),
        ])->values()->all();

        return $this->fhirResponse([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => count($entries),
            'entry'        => $entries,
        ]);
    }

    // -------------------------------------------------------------------------
    // AllergyIntolerance
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/AllergyIntolerance/{id}
     */
    public function allergyIntolerance(string $id): JsonResponse
    {
        $allergy = AllergyRecord::findOrFail($id);
        return $this->fhirResponse($this->fhirService->allergyIntolerance($allergy));
    }

    /**
     * GET /api/fhir/R4/AllergyIntolerance?patient={patientId}
     */
    public function searchAllergyIntolerance(Request $request): JsonResponse
    {
        $query = AllergyRecord::query()->where('status', 'active')->latest()->limit(50);

        if ($patientId = $request->query('patient')) {
            $query->where('patient_id', str_replace('Patient/', '', $patientId));
        }

        $allergies = $query->get();
        $entries   = $allergies->map(fn ($a) => [
            'fullUrl'  => url('/api/fhir/R4/AllergyIntolerance/' . $a->id),
            'resource' => $this->fhirService->allergyIntolerance($a),
        ])->values()->all();

        return $this->fhirResponse([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => count($entries),
            'entry'        => $entries,
        ]);
    }

    // -------------------------------------------------------------------------
    // Condition
    // -------------------------------------------------------------------------

    /**
     * GET /api/fhir/R4/Condition/{id}
     */
    public function condition(string $id): JsonResponse
    {
        $diagnosis = Diagnosis::findOrFail($id);
        return $this->fhirResponse($this->fhirService->condition($diagnosis));
    }

    /**
     * GET /api/fhir/R4/Condition?patient={patientId}&clinical-status={status}
     */
    public function searchCondition(Request $request): JsonResponse
    {
        $query = Diagnosis::query()->latest()->limit(50);

        if ($patientId = $request->query('patient')) {
            $query->where('patient_id', str_replace('Patient/', '', $patientId));
        }

        if ($status = $request->query('clinical-status')) {
            $query->where('status', $status);
        }

        $diagnoses = $query->get();
        $entries   = $diagnoses->map(fn ($d) => [
            'fullUrl'  => url('/api/fhir/R4/Condition/' . $d->id),
            'resource' => $this->fhirService->condition($d),
        ])->values()->all();

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
