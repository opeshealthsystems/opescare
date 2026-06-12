<?php

namespace OpesCare\Modules;

use OpesCare\Http\ApiClient;

/**
 * Patient consent request and verification.
 *
 * Consent is MANDATORY before accessing clinical data.
 * Always verify consent status before calling Patients::getSummary() or Records writes.
 */
class Consents
{
    public function __construct(private readonly ApiClient $client) {}

    /**
     * Request consent from a patient for specified scopes.
     *
     * @param  string  $healthId
     * @param  array{
     *     purpose: string,
     *     requested_scopes: string[],
     *     validity_period_days?: int,
     *     system_name?: string
     * }  $params
     */
    public function request(string $healthId, array $params): array
    {
        return $this->client->post('api/v1/connect/consents/request', array_merge(
            ['health_id' => $healthId],
            $params
        ));
    }

    /**
     * Verify whether a specific scope has been granted for a patient.
     *
     * @param  string  $healthId
     * @param  string  $scope   e.g. "patients:read"
     */
    public function verify(string $healthId, string $scope): array
    {
        return $this->client->post('api/v1/connect/consents/verify', [
            'health_id' => $healthId,
            'scope'     => $scope,
        ]);
    }

    /**
     * Request emergency access override (fully audited, use only in genuine emergencies).
     *
     * @param  string  $healthId
     * @param  string  $reason    Clinical justification for audit record
     * @param  string  $emergencyType  e.g. "clinical_emergency"
     */
    public function requestEmergencyAccess(string $healthId, string $reason, string $emergencyType = 'clinical_emergency'): array
    {
        return $this->client->post('api/v1/connect/emergency-access/request', [
            'health_id'      => $healthId,
            'reason'         => $reason,
            'emergency_type' => $emergencyType,
        ]);
    }
}
