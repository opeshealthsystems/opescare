<?php

namespace OpesCare;

use OpesCare\Auth\TokenManager;
use OpesCare\Http\ApiClient;
use OpesCare\Modules\Consents;
use OpesCare\Modules\Fhir;
use OpesCare\Modules\HealthIds;
use OpesCare\Modules\Patients;
use OpesCare\Modules\Records;
use OpesCare\Modules\Webhooks;

/**
 * OpesCare Connect Suite PHP SDK
 *
 * Quick start:
 *
 *   $client = new OpesCareClient(
 *       clientId:     'sandbox_xxxxxxxxxxxx',
 *       clientSecret: 'sk_sandbox_xxxxxxxxxxxx',
 *       environment:  'sandbox',   // 'sandbox' | 'production'
 *   );
 *
 *   // Resolve a patient
 *   $result = $client->healthIds->resolve(['health_id' => 'CM-HID-7KQ9-MP42-X8D1']);
 *
 *   // Read allergies (FHIR R4)
 *   $allergies = $client->fhir->allergyIntolerances('CM-HID-7KQ9-MP42-X8D1');
 *
 *   // Push a CDSS recommendation
 *   $client->records->pushEncounter([
 *       'health_id'      => 'CM-HID-7KQ9-MP42-X8D1',
 *       'encounter_type' => 'cdss_alert',
 *       'clinical_note'  => 'Drug interaction detected: Warfarin + Aspirin',
 *       'severity'       => 'high',
 *   ]);
 */
class OpesCareClient
{
    private const BASE_URLS = [
        'sandbox'    => 'http://opescare.test',
        'production' => 'https://api.opescare.com',
    ];

    public readonly HealthIds $healthIds;
    public readonly Patients  $patients;
    public readonly Consents  $consents;
    public readonly Records   $records;
    public readonly Fhir      $fhir;
    public readonly Webhooks  $webhooks;

    private TokenManager $tokenManager;
    private ApiClient    $http;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $environment = 'sandbox',
        ?string $baseUrl = null,
        private readonly float $timeout = 30.0
    ) {
        $resolvedUrl = $baseUrl ?? (self::BASE_URLS[$environment]
            ?? throw new \InvalidArgumentException("Unknown environment '{$environment}'. Use 'sandbox' or 'production'."));

        $this->tokenManager = new TokenManager($resolvedUrl, $clientId, $clientSecret);

        // Fetch token once eagerly so any credential errors surface at construction time
        $token = $this->tokenManager->getToken();

        $this->http = new ApiClient($resolvedUrl, $token, $timeout);

        $this->healthIds = new HealthIds($this->http);
        $this->patients  = new Patients($this->http);
        $this->consents  = new Consents($this->http);
        $this->records   = new Records($this->http);
        $this->fhir      = new Fhir($this->http);
        $this->webhooks  = new Webhooks($this->http);
    }

    /**
     * Returns a fresh client with a new Bearer token.
     * Call this if you receive a 401 mid-session (token has expired).
     */
    public function withRefreshedToken(): static
    {
        return new static(
            $this->clientId,
            $this->clientSecret,
            $this->environment,
            null,
            $this->timeout
        );
    }
}
