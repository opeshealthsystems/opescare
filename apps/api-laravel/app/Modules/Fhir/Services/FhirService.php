<?php

namespace App\Modules\Fhir\Services;

use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Visit;
use App\Models\VitalSign;
use App\Modules\Fhir\Mappers\FhirDiagnosticReportMapper;
use App\Modules\Fhir\Mappers\FhirEncounterMapper;
use App\Modules\Fhir\Mappers\FhirMedicationRequestMapper;
use App\Modules\Fhir\Mappers\FhirObservationMapper;
use App\Modules\Fhir\Mappers\FhirPatientMapper;

/**
 * FHIR R4 Service
 *
 * Central service for generating FHIR R4-compliant JSON resources and bundles
 * from OpesCare internal models.
 *
 * This is a READ-ONLY mapping layer. It does not accept FHIR writes.
 * Supported resources: Patient, Encounter, Observation, MedicationRequest, DiagnosticReport
 */
class FhirService
{
    public function __construct(
        private readonly FhirPatientMapper            $patientMapper,
        private readonly FhirEncounterMapper          $encounterMapper,
        private readonly FhirObservationMapper        $observationMapper,
        private readonly FhirMedicationRequestMapper  $medicationMapper,
        private readonly FhirDiagnosticReportMapper   $diagnosticMapper,
    ) {}

    // -------------------------------------------------------------------------
    // Single Resource
    // -------------------------------------------------------------------------

    public function patient(Patient $patient): array
    {
        return $this->patientMapper->toFhir($patient);
    }

    public function encounter(Visit $visit): array
    {
        return $this->encounterMapper->toFhir($visit);
    }

    public function diagnosticReport(LabOrder $order): array
    {
        $order->loadMissing('results');
        return $this->diagnosticMapper->toFhir($order);
    }

    public function medicationRequestBundle(Prescription $prescription): array
    {
        $prescription->loadMissing('items');
        return $this->wrapBundle($this->medicationMapper->prescriptionToBundle($prescription));
    }

    public function observationBundle(VitalSign $vital): array
    {
        $vital->loadMissing('triageRecord.visit');
        return $this->wrapBundle($this->observationMapper->toFhirBundle($vital));
    }

    // -------------------------------------------------------------------------
    // Patient-Centric Bundles
    // -------------------------------------------------------------------------

    /**
     * Build a FHIR Bundle with all available resources for a patient.
     * Includes: Patient, Encounters, DiagnosticReports, MedicationRequests, Observations
     */
    public function patientBundle(Patient $patient, int $limit = 50): array
    {
        $entries = [];

        // Patient
        $entries[] = $this->bundleEntry($this->patient($patient));

        // Encounters (visits)
        $visits = Visit::where('patient_id', $patient->id)->latest()->take($limit)->get();
        foreach ($visits as $visit) {
            $entries[] = $this->bundleEntry($this->encounter($visit));
        }

        // DiagnosticReports (lab orders with results)
        $labOrders = LabOrder::where('patient_id', $patient->id)
            ->with('results')
            ->latest('ordered_at')
            ->take($limit)
            ->get();
        foreach ($labOrders as $order) {
            $entries[] = $this->bundleEntry($this->diagnosticReport($order));
        }

        // MedicationRequests (prescriptions)
        $prescriptions = Prescription::where('patient_id', $patient->id)
            ->with('items')
            ->latest('prescribed_at')
            ->take($limit)
            ->get();
        foreach ($prescriptions as $prescription) {
            foreach ($this->medicationMapper->prescriptionToBundle($prescription) as $med) {
                $entries[] = $this->bundleEntry($med);
            }
        }

        return [
            'resourceType' => 'Bundle',
            'id'           => 'bundle-patient-' . $patient->id,
            'meta'         => ['lastUpdated' => now()->toIso8601String()],
            'type'         => 'searchset',
            'total'        => count($entries),
            'entry'        => $entries,
        ];
    }

    // -------------------------------------------------------------------------
    // FHIR CapabilityStatement
    // -------------------------------------------------------------------------

    public function capabilityStatement(): array
    {
        return [
            'resourceType' => 'CapabilityStatement',
            'id'           => 'opescare-fhir-capability',
            'url'          => url('/api/fhir/R4/metadata'),
            'version'      => '1.0',
            'name'         => 'OpesCareR4',
            'title'        => 'OpesCare FHIR R4 Capability Statement',
            'status'       => 'active',
            'date'         => now()->toDateString(),
            'publisher'    => 'OpesCare',
            'kind'         => 'instance',
            'fhirVersion'  => '4.0.1',
            'format'       => ['json'],
            'rest'         => [
                [
                    'mode'     => 'server',
                    'resource' => [
                        ['type' => 'Patient',           'interaction' => [['code' => 'read'], ['code' => 'search-type']]],
                        ['type' => 'Encounter',         'interaction' => [['code' => 'read'], ['code' => 'search-type']]],
                        ['type' => 'Observation',       'interaction' => [['code' => 'read'], ['code' => 'search-type']]],
                        ['type' => 'MedicationRequest', 'interaction' => [['code' => 'read'], ['code' => 'search-type']]],
                        ['type' => 'DiagnosticReport',  'interaction' => [['code' => 'read'], ['code' => 'search-type']]],
                    ],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function wrapBundle(array $resources): array
    {
        return [
            'resourceType' => 'Bundle',
            'type'         => 'collection',
            'total'        => count($resources),
            'entry'        => array_map(fn ($r) => $this->bundleEntry($r), $resources),
        ];
    }

    private function bundleEntry(array $resource): array
    {
        $type = $resource['resourceType'];
        $id   = $resource['id'] ?? null;

        return [
            'fullUrl'  => $id ? url("/api/fhir/R4/{$type}/{$id}") : null,
            'resource' => $resource,
        ];
    }
}
