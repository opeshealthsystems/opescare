<?php

namespace OpesCare\Modules;

use OpesCare\Http\ApiClient;

/**
 * Patient record reads — requires valid consent grant.
 */
class Patients
{
    public function __construct(private readonly ApiClient $client) {}

    /**
     * Retrieve consented patient summary: demographics, allergies, medications, labs, visits.
     *
     * Requires consent scope: patients:read
     */
    public function getSummary(string $healthId): array
    {
        return $this->client->get("api/v1/connect/patients/{$healthId}/summary");
    }

    /**
     * Emergency profile access. Requires emergency:access scope and is fully audited.
     */
    public function getEmergencyProfile(string $healthId): array
    {
        return $this->client->get("api/v1/connect/patients/{$healthId}/emergency-profile");
    }

    /**
     * Search patients by Health ID, demographics, national ID, or phone hash.
     */
    public function search(array $params): array
    {
        return $this->client->post('api/v1/connect/patients/search', $params);
    }
}
