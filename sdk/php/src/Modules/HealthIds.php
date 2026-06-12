<?php

namespace OpesCare\Modules;

use OpesCare\Http\ApiClient;

/**
 * Health ID resolution and verification.
 *
 * OpesCare Health ID format: CM-HID-XXXX-XXXX-XXXX
 */
class HealthIds
{
    public function __construct(private readonly ApiClient $client) {}

    /**
     * Resolve a patient to their canonical Health ID.
     *
     * Pass health_id for direct lookup, or first_name + last_name + date_of_birth
     * for demographic resolution. Returns status: found|created|not_found.
     *
     * @param  array{health_id?: string, first_name?: string, last_name?: string, date_of_birth?: string} $params
     */
    public function resolve(array $params): array
    {
        return $this->client->post('api/v1/connect/patients/resolve', $params);
    }

    /**
     * Verify a Health ID's format and existence without resolving full demographics.
     *
     * @param  string  $healthId  e.g. CM-HID-7KQ9-MP42-X8D1
     */
    public function verify(string $healthId): array
    {
        return $this->client->get("api/v1/connect/patients/verify/{$healthId}");
    }
}
