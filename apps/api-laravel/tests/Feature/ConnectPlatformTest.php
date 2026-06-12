<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\WithMobileAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConnectPlatformTest extends TestCase
{
    use RefreshDatabase, WithMobileAuth;

    protected function setUp(): void
    {
        parent::setUp();

        $facility = new \App\Models\Facility();
        $facility->id = '00000000-0000-0000-0000-000000000001';
        $facility->name = 'Metro Emergency General Clinic';
        $facility->type = 'clinic';
        $facility->status = 'active';
        $facility->save();

        $patient = new \App\Models\Patient();
        $patient->id = '00000000-0000-0000-0000-000000000003';
        $patient->health_id = 'OC-CMR-7KQ9-MP42-X8D1';
        $patient->first_name = 'John';
        $patient->last_name = 'Doe';
        $patient->sex = 'male';
        $patient->date_of_birth = '1990-04-12';
        $patient->identity_status = 'verified_by_facility';
        $patient->emergency_contact = [
            'name' => 'Mary Doe',
            'relation' => 'Spouse',
            'phone' => '+237 600-000-000'
        ];
        $patient->save();

        // Real integration client — secret stored as SHA-256 (as DeveloperPortalController::storeApp does)
        \App\Models\IntegrationClient::create([
            'client_id'     => 'real_client_001',
            'client_secret' => hash('sha256', 'real_secret_abc123'),
            'facility_id'   => '00000000-0000-0000-0000-000000000001',
            'name'          => 'Test Real Client',
            'environment'   => 'sandbox',
            'scopes'        => json_encode(['health_id:read', 'patients:read']),
            'status'        => 'active',
        ]);

        // Active consent grant for the test patient at the test facility
        $consentReq = \App\Models\ConsentRequest::create([
            'patient_id'             => '00000000-0000-0000-0000-000000000003',
            'requesting_facility_id' => '00000000-0000-0000-0000-000000000001',
            'purpose'                => 'treatment',
            'requested_scope'        => ['patients:read', 'patients:write', 'labs:write', 'prescriptions:write'],
            'duration_minutes'       => 1440,
            'status'                 => 'approved',
        ]);

        // forceCreate bypasses mass-assignment so the explicit id is stored
        // (HasUuids only auto-generates when id is empty, so our value is kept)
        \App\Models\ConsentGrant::forceCreate([
            'id'                 => '0c000000-0000-4000-8000-0000000cc001', // consent_grants.id is a Postgres uuid column
            'consent_request_id' => $consentReq->id,
            'patient_id'         => '00000000-0000-0000-0000-000000000003',
            'facility_id'        => '00000000-0000-0000-0000-000000000001',
            'authorizing_actor'  => 'patient',
            'scope'              => ['patients:read', 'patients:write', 'labs:write', 'prescriptions:write'],
            'status'             => 'active',
            'expires_at'         => now()->addDay(),
        ]);
    }

    /**
     * Test B2B OAuth token issuing endpoint.
     */
    public function test_auth_token_issuance_succeeds_with_sandbox_credentials()
    {
        $response = $this->postJson('/api/v1/connect/auth/token', [
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'grant_type' => 'client_credentials'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'expires_in',
                     'scope'
                 ]);
    }

    /**
     * Test client verification middleware blocks unauthorized requests.
     */
    public function test_b2b_routes_block_unauthorized_clients()
    {
        // Missing X-Client-ID and X-Client-Secret headers
        $response = $this->postJson('/api/v1/connect/patients/search', [
            'search_type' => 'health_id',
            'query' => 'OC-CMR-7KQ9-MP42-X8D1',
            'purpose' => 'treatment'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'error' => 'Missing integration credentials.'
                 ]);
    }

    /**
     * Test secure patient search exact match.
     */
    public function test_patient_search_succeeds_with_valid_sandbox_client()
    {
        $response = $this->withHeaders([
            'X-Client-ID' => 'test_client_id',
            'X-Client-Secret' => 'test_client_secret'
        ])->postJson('/api/v1/connect/patients/search', [
            'search_type' => 'health_id',
            'query' => 'OC-CMR-7KQ9-MP42-X8D1',
            'purpose' => 'treatment'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => 'matched',
                     'match_type' => 'exact'
                 ]);
    }

    /**
     * Test idempotency key enforcement on writes.
     */
    public function test_writes_require_idempotency_key_header()
    {
        $response = $this->withHeaders([
            'X-Client-ID' => 'test_client_id',
            'X-Client-Secret' => 'test_client_secret'
        ])->postJson('/api/v1/connect/records/encounters', [
            'health_id' => 'OC-CMR-7KQ9-MP42-X8D1',
            'external_encounter_id' => 'ENC-9001'
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment([
                     'error_code' => 'IDEMPOTENCY_KEY_REQUIRED'
                 ]);
    }

    /**
     * Test idempotency key conflict check using real database caching.
     */
    public function test_writes_block_duplicate_idempotency_keys_and_retrieve_cache_hits()
    {
        // 1. Initial write — consent grant required on write routes
        $response = $this->withHeaders([
            'X-Client-ID'       => 'test_client_id',
            'X-Client-Secret'   => 'test_client_secret',
            'Idempotency-Key'   => 'idm_key_test_1002',
            'X-Consent-Grant-Id' => '0c000000-0000-4000-8000-0000000cc001',
        ])->postJson('/api/v1/connect/records/encounters', [
            'health_id' => 'OC-CMR-7KQ9-MP42-X8D1',
            'external_encounter_id' => 'ENC-9001'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'status' => 'accepted'
                 ]);

        // Assert row is cached in database
        $this->assertDatabaseHas('idempotency_records', [
            'idempotency_key' => 'idm_key_test_1002',
            'client_id' => 'test_client_id'
        ]);

        // 2. Duplicate request with EXACT same key and body returns CACHE HIT response
        $response2 = $this->withHeaders([
            'X-Client-ID'       => 'test_client_id',
            'X-Client-Secret'   => 'test_client_secret',
            'Idempotency-Key'   => 'idm_key_test_1002',
            'X-Consent-Grant-Id' => '0c000000-0000-4000-8000-0000000cc001',
        ])->postJson('/api/v1/connect/records/encounters', [
            'health_id' => 'OC-CMR-7KQ9-MP42-X8D1',
            'external_encounter_id' => 'ENC-9001'
        ]);

        $response2->assertStatus(200);
        $response2->assertHeader('X-Cache-Idempotency', 'HIT');

        // 3. Request with same key but DIFFERENT body throws 409 conflict
        $response3 = $this->withHeaders([
            'X-Client-ID'       => 'test_client_id',
            'X-Client-Secret'   => 'test_client_secret',
            'Idempotency-Key'   => 'idm_key_test_1002',
            'X-Consent-Grant-Id' => '0c000000-0000-4000-8000-0000000cc001',
        ])->postJson('/api/v1/connect/records/encounters', [
            'health_id' => 'OC-CMR-7KQ9-MP42-X8D1',
            'external_encounter_id' => 'ENC-9002_DIFFERENT'
        ]);

        $response3->assertStatus(409)
                 ->assertJsonFragment([
                     'error_code' => 'IDEMPOTENCY_CONFLICT'
                 ]);
    }

    /**
     * Test B2B pull requires active consent header.
     */
    public function test_pull_summary_fails_without_consent_grant_token()
    {
        $response = $this->withHeaders([
            'X-Client-ID' => 'test_client_id',
            'X-Client-Secret' => 'test_client_secret',
            'X-Purpose-Of-Use' => 'treatment',
            'X-Consent-Grant-Id' => 'invalid_grant'
        ])->getJson('/api/v1/connect/patients/OC-CMR-7KQ9-MP42-X8D1/summary');

        $response->assertStatus(403)
                 ->assertJsonFragment([
                     'error_code' => 'CONSENT_REQUIRED'
                 ]);
    }

    /**
     * Test audited emergency override pull and verify PostgreSQL audit_events row creation.
     */
    public function test_emergency_override_pull_bypasses_standard_consent_and_logs_audit_events()
    {
        $response = $this->withHeaders([
            'X-Client-ID' => 'test_client_id',
            'X-Client-Secret' => 'test_client_secret',
            'X-Purpose-Of-Use' => 'emergency',
            'X-Emergency-Reason' => 'Patient is unconscious in ICU'
        ])->getJson('/api/v1/connect/patients/OC-CMR-7KQ9-MP42-X8D1/emergency-profile');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'emergency_status' => 'consent_bypassed_audited'
                 ]);

        // Assert database audit trail was persisted in PostgreSQL
        $this->assertDatabaseHas('audit_events', [
            'emergency_override' => true,
            'reason' => 'Patient is unconscious in ICU'
        ]);
    }

    /**
     * Test matching reconciliation triggers.
     */
    public function test_reconciliation_case_creation_on_matching_conflicts()
    {
        $response = $this->withHeaders([
            'X-Client-ID'       => 'test_client_id',
            'X-Client-Secret'   => 'test_client_secret',
            'Idempotency-Key'   => 'idm_recon_key_01',
            'X-Consent-Grant-Id' => '0c000000-0000-4000-8000-0000000cc001',
        ])->postJson('/api/v1/connect/records/encounters', [
            'health_id' => 'OC-CMR-RECON-REQUIRED',
            'external_encounter_id' => 'ENC-RECON-001'
        ]);

        $response->assertStatus(202)
                 ->assertJsonFragment([
                     'status' => 'pending_reconciliation',
                     'error_code' => 'RECONCILIATION_REQUIRED'
                 ]);

        // Verify row was inserted into reconciliation_cases table
        $this->assertDatabaseHas('reconciliation_cases', [
            'mismatch_reason' => 'unresolved_health_id',
            'status' => 'pending'
        ]);
    }

    /**
     * Test B2C Mobile endpoints.
     */
    public function test_mobile_me_endpoint_returns_demographic_profiles()
    {
        $patient = \App\Models\Patient::find('00000000-0000-0000-0000-000000000003');
        $response = $this->withHeaders($this->mobileAuthHeaders($patient))
                         ->getJson('/api/mobile/me');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'health_id' => 'OC-CMR-7KQ9-MP42-X8D1',
                     'display_name' => 'John D.'
                 ]);
    }

    /**
     * Real integration client authenticates correctly via SHA-256 secret.
     */
    public function test_real_client_can_authenticate_with_correct_secret(): void
    {
        $response = $this->withHeaders([
            'X-Client-ID'     => 'real_client_001',
            'X-Client-Secret' => 'real_secret_abc123',
        ])->postJson('/api/v1/connect/patients/search', [
            'search_type' => 'health_id',
            'query'       => 'OC-CMR-7KQ9-MP42-X8D1',
            'purpose'     => 'treatment',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Real integration client is blocked with wrong secret.
     */
    public function test_real_client_blocked_with_wrong_secret(): void
    {
        $response = $this->withHeaders([
            'X-Client-ID'     => 'real_client_001',
            'X-Client-Secret' => 'wrong_secret',
        ])->postJson('/api/v1/connect/patients/search', [
            'search_type' => 'health_id',
            'query'       => 'OC-CMR-7KQ9-MP42-X8D1',
            'purpose'     => 'treatment',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Rate-limit headers are present on Connect API responses.
     */
    public function test_rate_limit_headers_present_on_connect_response(): void
    {
        $response = $this->withHeaders([
            'X-Client-ID'     => 'test_client_id',
            'X-Client-Secret' => 'test_client_secret',
        ])->postJson('/api/v1/connect/patients/search', [
            'search_type' => 'health_id',
            'query'       => 'OC-CMR-7KQ9-MP42-X8D1',
            'purpose'     => 'treatment',
        ]);

        $response->assertStatus(200)
                 ->assertHeader('X-RateLimit-Limit')
                 ->assertHeader('X-RateLimit-Remaining');
    }
}
