<?php

namespace OpesCare\Modules;

use OpesCare\Http\ApiClient;

/**
 * Clinical record writes — push encounters, lab results, prescriptions to OpesCare.
 *
 * All write operations require:
 *  1. A valid consent grant for the relevant scope
 *  2. An Idempotency-Key (auto-generated if not provided via idempotencyKey param)
 */
class Records
{
    public function __construct(private readonly ApiClient $client) {}

    /**
     * Push a clinical encounter (note, alert, recommendation) to OpesCare.
     *
     * Requires consent scope: patients:write
     *
     * @param  array{
     *     health_id: string,
     *     encounter_type: string,
     *     clinical_note: string,
     *     severity?: string,
     *     alert_type?: string,
     *     cdss_rule_id?: string,
     *     confidence_score?: float,
     *     occurred_at?: string,
     *     source_system?: string
     * }  $data
     * @param  string|null  $idempotencyKey  Unique key for safe retries; auto-generated if null
     */
    public function pushEncounter(array $data, ?string $idempotencyKey = null): array
    {
        return $this->client->post(
            'api/v1/connect/records/encounters',
            $data,
            $idempotencyKey ?? $this->generateKey('enc')
        );
    }

    /**
     * Push a lab result or interpretation to OpesCare.
     *
     * Requires consent scope: labs:write
     *
     * @param  array{
     *     health_id: string,
     *     test_name: string,
     *     result_value: string,
     *     reference_range?: string,
     *     interpretation?: string,
     *     flagged?: bool,
     *     flag_level?: string,
     *     occurred_at?: string,
     *     source_system?: string
     * }  $data
     */
    public function pushLabResult(array $data, ?string $idempotencyKey = null): array
    {
        return $this->client->post(
            'api/v1/connect/records/lab-results',
            $data,
            $idempotencyKey ?? $this->generateKey('lab')
        );
    }

    /**
     * Push a prescription or drug safety alert to OpesCare.
     *
     * Requires consent scope: prescriptions:write
     *
     * @param  array{
     *     health_id: string,
     *     alert_type?: string,
     *     medication_name?: string,
     *     contraindication_reason?: string,
     *     severity?: string,
     *     recommendation?: string,
     *     occurred_at?: string,
     *     source_system?: string
     * }  $data
     */
    public function pushPrescription(array $data, ?string $idempotencyKey = null): array
    {
        return $this->client->post(
            'api/v1/connect/records/prescriptions',
            $data,
            $idempotencyKey ?? $this->generateKey('rx')
        );
    }

    private function generateKey(string $prefix): string
    {
        return $prefix . '-' . bin2hex(random_bytes(16));
    }
}
