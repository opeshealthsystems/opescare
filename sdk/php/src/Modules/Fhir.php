<?php

namespace OpesCare\Modules;

use OpesCare\Http\ApiClient;

/**
 * FHIR R4 resource reads.
 *
 * All responses conform to the FHIR R4 specification (application/fhir+json).
 * Requires Bearer token; patient-specific resources also require consent:
 * patients:read scope.
 */
class Fhir
{
    public function __construct(private readonly ApiClient $client) {}

    /** FHIR CapabilityStatement — public, no auth required. */
    public function metadata(): array
    {
        return $this->client->get('api/fhir/R4/metadata');
    }

    /** GET /fhir/R4/Patient/{id} */
    public function patient(string $healthId): array
    {
        return $this->client->get("api/fhir/R4/Patient/{$healthId}");
    }

    /** GET /fhir/R4/Patient?identifier={healthId}&... */
    public function searchPatients(array $params = []): array
    {
        return $this->client->get('api/fhir/R4/Patient', $params);
    }

    /** GET /fhir/R4/Patient/{id}/$everything — full patient bundle */
    public function patientEverything(string $healthId): array
    {
        return $this->client->get("api/fhir/R4/Patient/{$healthId}/\$everything");
    }

    /** GET /fhir/R4/AllergyIntolerance?patient={healthId} */
    public function allergyIntolerances(string $healthId, array $params = []): array
    {
        return $this->client->get('api/fhir/R4/AllergyIntolerance', array_merge(['patient' => $healthId], $params));
    }

    /** GET /fhir/R4/MedicationRequest?patient={healthId}&status=active */
    public function medicationRequests(string $healthId, array $params = []): array
    {
        return $this->client->get('api/fhir/R4/MedicationRequest', array_merge(['patient' => $healthId], $params));
    }

    /** GET /fhir/R4/DiagnosticReport?patient={healthId} */
    public function diagnosticReports(string $healthId, array $params = []): array
    {
        return $this->client->get('api/fhir/R4/DiagnosticReport', array_merge(['patient' => $healthId], $params));
    }

    /** GET /fhir/R4/Condition?patient={healthId} */
    public function conditions(string $healthId, array $params = []): array
    {
        return $this->client->get('api/fhir/R4/Condition', array_merge(['patient' => $healthId], $params));
    }

    /** GET /fhir/R4/Immunization?patient={healthId} */
    public function immunizations(string $healthId, array $params = []): array
    {
        return $this->client->get('api/fhir/R4/Immunization', array_merge(['patient' => $healthId], $params));
    }

    /** GET /fhir/R4/Encounter?patient={healthId} */
    public function encounters(string $healthId, array $params = []): array
    {
        return $this->client->get('api/fhir/R4/Encounter', array_merge(['patient' => $healthId], $params));
    }
}
