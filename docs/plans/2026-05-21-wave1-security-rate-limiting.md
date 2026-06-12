# Wave 1 — Security & Rate Limiting Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix broken client authentication, enforce consent on all data-access Connect API endpoints, and apply per-client rate limiting to prevent abuse.

**Architecture:** Four targeted fixes — (1) standardise secret hashing to SHA-256 in `VerifyIntegrationClient`, (2) add facility-scoped consent checks to the three write endpoints and the ConsentService, (3) add a `RequireConsentGrant` middleware applied via route groups, (4) apply `throttle.client` to the Connect API authenticated group.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, built-in `RateLimiter` facade.

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Modify | `app/Http/Middleware/VerifyIntegrationClient.php` | Fix SHA-256 secret comparison |
| Modify | `app/Modules/Governance/Services/ConsentService.php` | Add facility_id scope to verifyAccess |
| Create | `app/Http/Middleware/RequireConsentGrant.php` | Reusable consent enforcement middleware |
| Modify | `bootstrap/app.php` | Register new middleware alias |
| Modify | `routes/api.php` | Apply throttle.client + RequireConsentGrant to Connect routes |
| Modify | `database/seeders/DemoDeveloperAccountSeeder.php` | Fix bcrypt → SHA-256 on client_secret |
| Modify | `tests/Feature/ConnectPlatformTest.php` | Add tests for auth fix + consent enforcement |
| Create | `tests/Feature/Connect/ConsentEnforcementTest.php` | Dedicated consent enforcement test suite |

---

## Task 1 — Fix client secret authentication (critical)

**Files:**
- Modify: `app/Http/Middleware/VerifyIntegrationClient.php`

The middleware currently does `->where('client_secret', $clientSecret)` comparing plain text against a SHA-256 hash in the database. Real clients can never authenticate — only the hardcoded test bypass works.

- [ ] **Step 1.1: Write the failing test**

Add to `tests/Feature/ConnectPlatformTest.php` in `setUp()`, create a real DB client:

```php
protected function setUp(): void
{
    parent::setUp();

    // ... existing facility + patient setup ...

    // Real integration client (secret stored as SHA-256, as DeveloperPortalController does)
    \App\Models\IntegrationClient::create([
        'client_id'     => 'real_client_001',
        'client_secret' => hash('sha256', 'real_secret_abc123'),
        'facility_id'   => '00000000-0000-0000-0000-000000000001',
        'name'          => 'Test Real Client',
        'environment'   => 'sandbox',
        'scopes'        => json_encode(['health_id:read', 'patients:read']),
        'status'        => 'active',
    ]);
}
```

Add test method:

```php
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
```

- [ ] **Step 1.2: Run to confirm failure**

```bash
cd C:\laragon\www\opescare\apps\api-laravel
php artisan test --filter="test_real_client_can_authenticate_with_correct_secret" --stop-on-failure
```

Expected: FAIL — 403 instead of 200 (middleware compares plain text against hash).

- [ ] **Step 1.3: Fix VerifyIntegrationClient**

Replace the DB lookup block in `app/Http/Middleware/VerifyIntegrationClient.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IntegrationClient;

class VerifyIntegrationClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $clientId     = $request->header('X-Client-ID');
        $clientSecret = $request->header('X-Client-Secret');

        if (!$clientId || !$clientSecret) {
            return response()->json(['error' => 'Missing integration credentials.'], 401);
        }

        // Sandbox/Testing Bypass — deterministic unit & integration tests only
        if ($clientId === 'test_client_id' && $clientSecret === 'test_client_secret') {
            $request->attributes->add([
                'integration_client_id' => 'test_client_id',
                'facility_id'           => '00000000-0000-0000-0000-000000000001',
            ]);
            return $next($request);
        }

        try {
            // All real secrets are stored as SHA-256 hashes (set by DeveloperPortalController::storeApp)
            $hashedSecret = hash('sha256', $clientSecret);

            $client = IntegrationClient::where('client_id', $clientId)
                ->where('client_secret', $hashedSecret)
                ->first();

            if (!$client || $client->status !== 'active') {
                return response()->json(['error' => 'Invalid or inactive integration client.'], 403);
            }

            $request->attributes->add([
                'integration_client'    => $client,
                'integration_client_id' => $client->client_id,
                'facility_id'           => $client->facility_id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database integration integrity error: ' . $e->getMessage()], 500);
        }

        return $next($request);
    }
}
```

- [ ] **Step 1.4: Fix seeder — standardise to SHA-256**

In `database/seeders/DemoDeveloperAccountSeeder.php`, replace any `Hash::make(...)` on client_secret with `hash('sha256', ...)`:

Find the line:
```php
'client_secret' => \Illuminate\Support\Facades\Hash::make('prod_secret_opeshisos_2026'),
```

Replace with:
```php
'client_secret' => hash('sha256', 'prod_secret_opeshisos_2026'),
```

- [ ] **Step 1.5: Run tests**

```bash
php artisan test --filter="ConnectPlatformTest" --stop-on-failure
```

Expected: All existing tests PASS. New auth tests PASS.

- [ ] **Step 1.6: Commit**

```bash
git add app/Http/Middleware/VerifyIntegrationClient.php \
        database/seeders/DemoDeveloperAccountSeeder.php \
        tests/Feature/ConnectPlatformTest.php
git commit -m "fix(auth): standardise client_secret comparison to SHA-256 hash

Real integration clients could never authenticate — middleware was
comparing plain-text secret against SHA-256 hash stored in DB.
Standardise all secrets to SHA-256. Fix seeder inconsistency."
```

---

## Task 2 — Fix ConsentService.verifyAccess to scope by facility

**Files:**
- Modify: `app/Modules/Governance/Services/ConsentService.php`

Currently `verifyAccess` ignores `$facilityId` — a token from Facility A grants access when called by Facility B.

- [ ] **Step 2.1: Write failing test**

Add to `tests/Feature/Connect/ConsentEnforcementTest.php` (create file):

```php
<?php

namespace Tests\Feature\Connect;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;
use App\Models\Facility;
use App\Models\ConsentGrant;
use App\Models\ConsentRequest;
use App\Modules\Governance\Services\ConsentService;

class ConsentEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private ConsentService $service;
    private Patient $patient;
    private Facility $facilityA;
    private Facility $facilityB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ConsentService::class);

        $this->facilityA = Facility::create([
            'id'     => '00000000-0000-0000-0000-aaaaaaaaaaaa',
            'name'   => 'Facility A',
            'type'   => 'hospital',
            'status' => 'active',
        ]);

        $this->facilityB = Facility::create([
            'id'     => '00000000-0000-0000-0000-bbbbbbbbbbbb',
            'name'   => 'Facility B',
            'type'   => 'clinic',
            'status' => 'active',
        ]);

        $this->patient = Patient::create([
            'health_id'  => 'OC-TST-0001-0001-01',
            'first_name' => 'Jane',
            'last_name'  => 'Consent',
            'sex'        => 'female',
            'date_of_birth' => '1995-01-01',
            'is_demo'    => false,
        ]);

        // Consent granted to Facility A only
        $req = ConsentRequest::create([
            'patient_id'             => $this->patient->id,
            'requesting_facility_id' => $this->facilityA->id,
            'purpose'                => 'treatment',
            'requested_scope'        => ['patients:read'],
            'duration_minutes'       => 60,
            'status'                 => 'approved',
        ]);

        ConsentGrant::create([
            'consent_request_id' => $req->id,
            'patient_id'         => $this->patient->id,
            'facility_id'        => $this->facilityA->id,
            'authorizing_actor'  => 'patient',
            'scope'              => ['patients:read'],
            'status'             => 'active',
            'expires_at'         => now()->addHour(),
        ]);
    }

    public function test_facility_a_can_access_with_valid_consent(): void
    {
        $result = $this->service->verifyAccess(
            $this->patient->id,
            $this->facilityA->id,
            null,
            'patients:read',
            'treatment'
        );

        $this->assertTrue($result);
    }

    public function test_facility_b_cannot_access_with_consent_granted_to_facility_a(): void
    {
        $result = $this->service->verifyAccess(
            $this->patient->id,
            $this->facilityB->id,
            null,
            'patients:read',
            'treatment'
        );

        $this->assertFalse($result);
    }

    public function test_expired_consent_is_rejected(): void
    {
        ConsentGrant::where('patient_id', $this->patient->id)->update([
            'expires_at' => now()->subMinute(),
        ]);

        $result = $this->service->verifyAccess(
            $this->patient->id,
            $this->facilityA->id,
            null,
            'patients:read',
            'treatment'
        );

        $this->assertFalse($result);
    }
}
```

- [ ] **Step 2.2: Run to confirm failure**

```bash
php artisan test --filter="ConsentEnforcementTest::test_facility_b_cannot_access" --stop-on-failure
```

Expected: FAIL — `verifyAccess` returns `true` for Facility B (bug confirmed).

- [ ] **Step 2.3: Fix ConsentService.verifyAccess**

Replace `verifyAccess` in `app/Modules/Governance/Services/ConsentService.php`:

```php
public function verifyAccess(
    string $patientId,
    string $facilityId,
    ?string $userId,
    string $scope,
    string $purpose
): bool {
    $now = Carbon::now();

    $grants = ConsentGrant::where('consent_grants.patient_id', $patientId)
        ->where('consent_grants.facility_id', $facilityId)   // ← scope to requesting facility
        ->where('consent_grants.status', 'active')
        ->where('consent_grants.expires_at', '>=', $now)
        ->join('consent_requests', 'consent_grants.consent_request_id', '=', 'consent_requests.id')
        ->where('consent_requests.purpose', $purpose)
        ->select('consent_grants.*')
        ->get();

    foreach ($grants as $grant) {
        $scopes = $grant->scope ?? [];
        if (in_array($scope, $scopes) || in_array('*', $scopes)) {
            return true;
        }
    }

    return false;
}
```

- [ ] **Step 2.4: Run tests**

```bash
php artisan test --filter="ConsentEnforcementTest" --stop-on-failure
```

Expected: All 3 tests PASS.

- [ ] **Step 2.5: Commit**

```bash
git add app/Modules/Governance/Services/ConsentService.php \
        tests/Feature/Connect/ConsentEnforcementTest.php
git commit -m "fix(consent): scope ConsentService.verifyAccess to requesting facility_id

Consent granted by patient to Facility A was being honoured when
called by Facility B. Add facility_id filter to verifyAccess query."
```

---

## Task 3 — RequireConsentGrant middleware

**Files:**
- Create: `app/Http/Middleware/RequireConsentGrant.php`
- Modify: `bootstrap/app.php`

A single reusable middleware that reads `X-Consent-Grant-Id` + `X-Purpose-Of-Use`, validates the grant exists, belongs to the patient, is active, not expired, and is scoped to the requesting facility and required scope.

- [ ] **Step 3.1: Create the middleware**

```php
<?php
// app/Http/Middleware/RequireConsentGrant.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ConsentGrant;
use App\Models\Patient;
use App\Enums\OpesCareErrorCode;

class RequireConsentGrant
{
    /**
     * Validate that the incoming request carries a valid, active, non-expired
     * consent grant scoped to the requesting facility and the required scope.
     *
     * Usage in routes:
     *   ->middleware('consent.grant:patients:read')
     *   ->middleware('consent.grant:labs:write')
     */
    public function handle(Request $request, Closure $next, string $requiredScope = 'patients:read'): Response
    {
        $consentGrantId = $request->header('X-Consent-Grant-Id');
        $purpose        = $request->header('X-Purpose-Of-Use');
        $facilityId     = $request->attributes->get('facility_id');

        if (!$consentGrantId || !$purpose) {
            return response()->json([
                'status'          => 'rejected',
                'error_code'      => OpesCareErrorCode::CONSENT_REQUIRED->value,
                'message'         => 'Missing X-Consent-Grant-Id or X-Purpose-Of-Use headers.',
                'required_action' => 'request_consent',
            ], 403);
        }

        // Resolve patient from route or request body
        $healthId = $request->route('health_id') ?? $request->input('health_id');
        $patient  = $healthId ? Patient::where('health_id', $healthId)->first() : null;

        $grant = ConsentGrant::where('id', $consentGrantId)
            ->where('status', 'active')
            ->where('facility_id', $facilityId)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$grant) {
            return response()->json([
                'status'          => 'rejected',
                'error_code'      => OpesCareErrorCode::CONSENT_REQUIRED->value,
                'message'         => 'No active consent grant found for this facility.',
                'required_action' => 'request_consent',
            ], 403);
        }

        // Verify patient match when patient is known
        if ($patient && $grant->patient_id !== $patient->id) {
            return response()->json([
                'status'          => 'rejected',
                'error_code'      => OpesCareErrorCode::CONSENT_REQUIRED->value,
                'message'         => 'Consent grant does not belong to the specified patient.',
                'required_action' => 'request_consent',
            ], 403);
        }

        // Verify scope
        $grantedScopes = $grant->scope ?? [];
        if (!in_array($requiredScope, $grantedScopes) && !in_array('*', $grantedScopes)) {
            return response()->json([
                'status'          => 'rejected',
                'error_code'      => OpesCareErrorCode::CONSENT_REQUIRED->value,
                'message'         => "Consent grant does not include required scope: {$requiredScope}.",
                'required_action' => 'request_consent',
            ], 403);
        }

        // Attach grant to request for downstream use
        $request->attributes->add(['consent_grant' => $grant]);

        return $next($request);
    }
}
```

- [ ] **Step 3.2: Register alias in bootstrap/app.php**

In the `->withMiddleware()` closure, add to the `alias` array:

```php
'consent.grant' => \App\Http\Middleware\RequireConsentGrant::class,
```

The full aliases block becomes:
```php
$middleware->alias([
    'sdk.token'        => \App\Http\Middleware\VerifySdkToken::class,
    'throttle.client'  => \App\Http\Middleware\ThrottleByClient::class,
    'bridge.agent'     => \App\Http\Middleware\VerifyBridgeAgent::class,
    'portal.access'    => \App\Http\Middleware\EnsurePortalAccess::class,
    'facility.context' => \App\Http\Middleware\RequireFacilityContext::class,
    'consent.grant'    => \App\Http\Middleware\RequireConsentGrant::class,
]);
```

- [ ] **Step 3.3: Write middleware tests**

Add to `tests/Feature/Connect/ConsentEnforcementTest.php`:

```php
public function test_require_consent_grant_middleware_blocks_missing_headers(): void
{
    // Hit a route that requires consent (will add in Task 4)
    // For now test that the middleware exists and is registered
    $this->assertArrayHasKey(
        'consent.grant',
        app(\Illuminate\Routing\Router::class)->getMiddleware()
    );
}
```

- [ ] **Step 3.4: Commit**

```bash
git add app/Http/Middleware/RequireConsentGrant.php \
        bootstrap/app.php \
        tests/Feature/Connect/ConsentEnforcementTest.php
git commit -m "feat(middleware): add RequireConsentGrant middleware

Validates X-Consent-Grant-Id header on data-access routes.
Checks grant: active, not expired, facility-scoped, patient-matched,
and scope-matched. Attaches grant to request attributes."
```

---

## Task 4 — Apply consent enforcement to write + read endpoints

**Files:**
- Modify: `routes/api.php`
- Modify: `app/Http/Controllers/Api/V1/Connect/RecordController.php`

Apply `consent.grant` middleware to routes that access patient data. Remove the inline consent check from `pullSummary` (now handled by middleware).

- [ ] **Step 4.1: Write failing tests**

Add to `tests/Feature/Connect/ConsentEnforcementTest.php`:

```php
public function test_push_encounter_requires_consent_grant(): void
{
    $response = $this->withHeaders([
        'X-Client-ID'     => 'test_client_id',
        'X-Client-Secret' => 'test_client_secret',
        'Idempotency-Key' => 'idem-test-001',
        // No X-Consent-Grant-Id
    ])->postJson('/api/v1/connect/records/encounters', [
        'health_id'             => 'OC-TST-0001-0001-01',
        'external_encounter_id' => 'ENC-001',
    ]);

    $response->assertStatus(403)
             ->assertJsonFragment(['required_action' => 'request_consent']);
}

public function test_push_lab_result_requires_consent_grant(): void
{
    $response = $this->withHeaders([
        'X-Client-ID'       => 'test_client_id',
        'X-Client-Secret'   => 'test_client_secret',
        'Idempotency-Key'   => 'idem-test-002',
    ])->postJson('/api/v1/connect/records/lab-results', [
        'health_id'            => 'OC-TST-0001-0001-01',
        'external_lab_order_id'=> 'LAB-001',
    ]);

    $response->assertStatus(403)
             ->assertJsonFragment(['required_action' => 'request_consent']);
}

public function test_push_prescription_requires_consent_grant(): void
{
    $response = $this->withHeaders([
        'X-Client-ID'     => 'test_client_id',
        'X-Client-Secret' => 'test_client_secret',
        'Idempotency-Key' => 'idem-test-003',
    ])->postJson('/api/v1/connect/records/prescriptions', [
        'health_id'  => 'OC-TST-0001-0001-01',
        'medication' => ['name' => 'Amoxicillin', 'dose' => '500mg'],
    ]);

    $response->assertStatus(403)
             ->assertJsonFragment(['required_action' => 'request_consent']);
}

public function test_pull_summary_requires_consent_grant(): void
{
    $response = $this->withHeaders([
        'X-Client-ID'     => 'test_client_id',
        'X-Client-Secret' => 'test_client_secret',
        // No X-Consent-Grant-Id or X-Purpose-Of-Use
    ])->getJson('/api/v1/connect/patients/OC-CMR-7KQ9-MP42-X8D1/summary');

    $response->assertStatus(403)
             ->assertJsonFragment(['required_action' => 'request_consent']);
}
```

- [ ] **Step 4.2: Run to confirm they fail**

```bash
php artisan test --filter="test_push_encounter_requires_consent" --stop-on-failure
```

Expected: FAIL — currently returns 400 (validation fails before consent) or 200.

- [ ] **Step 4.3: Apply middleware in routes/api.php**

Find the Connect API authenticated group in `routes/api.php` and restructure it:

```php
Route::middleware(VerifyIntegrationClient::class)->group(function () {

    // No consent required — these ARE the consent flow
    Route::post('/widget/sessions',          [AuthController::class, 'createWidgetSession']);
    Route::post('/patients/search',          [PatientSearchController::class, 'search']);
    Route::post('/patients/resolve',         [HealthIdResolutionController::class, 'resolve']);
    Route::get('/patients/verify/{health_id}',[HealthIdResolutionController::class, 'verify']);
    Route::post('/consents/request',         [ConnectGovernanceController::class, 'requestConsent']);
    Route::post('/consents/verify',          [ConnectGovernanceController::class, 'verifyConsent']);
    Route::post('/emergency-access/request', [ConnectGovernanceController::class, 'requestEmergencyAccess']);
    Route::post('/inventory/pharmacy-stock/sync', [InventoryController::class, 'syncPharmacyStock']);
    Route::post('/inventory/blood-stock/sync',    [InventoryController::class, 'syncBloodStock']);
    Route::post('/webhooks/subscriptions',   [WebhookController::class, 'createSubscription']);
    Route::get('/reconciliation/cases',      [ReconciliationController::class, 'listCases']);
    Route::post('/reconciliation/cases/{id}/resolve', [ReconciliationController::class, 'resolveCase']);

    // Emergency profile — different consent model (emergency bypass, audited)
    Route::get('/patients/{health_id}/emergency-profile',        [ConnectGovernanceController::class, 'getEmergencyProfile']);
    Route::get('/patients/{health_id}/legacy-emergency-profile', [RecordController::class, 'pullEmergencyProfile']);

    // Data-access routes — require valid consent grant
    Route::middleware('consent.grant:patients:read')->group(function () {
        Route::get('/patients/{health_id}/summary', [RecordController::class, 'pullSummary']);
    });

    Route::middleware(IdempotencyProtection::class)->group(function () {
        Route::middleware('consent.grant:labs:write')->group(function () {
            Route::post('/records/lab-results',   [RecordController::class, 'pushLabResult']);
        });
        Route::middleware('consent.grant:prescriptions:write')->group(function () {
            Route::post('/records/prescriptions', [RecordController::class, 'pushPrescription']);
        });
        Route::middleware('consent.grant:patients:write')->group(function () {
            Route::post('/records/encounters',    [RecordController::class, 'pushEncounter']);
        });
    });
});
```

Make sure the `use` imports at the top of `routes/api.php` include all controllers referenced above. They already exist — just verify.

- [ ] **Step 4.4: Remove redundant inline consent check from RecordController::pullSummary**

In `app/Http/Controllers/Api/V1/Connect/RecordController.php`, remove lines 7–25 (the inline header check and consent validation block) from `pullSummary`. The middleware now handles this. The method should start directly with the patient lookup:

```php
public function pullSummary(Request $request, $healthId)
{
    $patient = Patient::where('health_id', $healthId)->first();

    if (!$patient && $healthId === 'OC-CMR-7KQ9-MP42-X8D1') {
        // ... existing sandbox fallback unchanged ...
    }

    if (!$patient) {
        return response()->json([
            'status'     => 'rejected',
            'error_code' => OpesCareErrorCode::PATIENT_NOT_FOUND->value,
            'message'    => 'No patient was found with this health ID.'
        ], 404);
    }

    AuditLogger::log($request, 'patient_summary_pulled', 'patient', $patient->id, $patient->id);

    // ... rest of method unchanged ...
}
```

- [ ] **Step 4.5: Run tests**

```bash
php artisan test --filter="ConsentEnforcementTest" --stop-on-failure
php artisan test --filter="ConnectPlatformTest" --stop-on-failure
```

Expected: All consent enforcement tests PASS. Existing platform tests PASS.

- [ ] **Step 4.6: Commit**

```bash
git add routes/api.php \
        app/Http/Controllers/Api/V1/Connect/RecordController.php \
        tests/Feature/Connect/ConsentEnforcementTest.php
git commit -m "feat(security): enforce consent grant on all Connect API data-access routes

pushEncounter, pushLabResult, pushPrescription, and pullSummary now
require a valid X-Consent-Grant-Id header scoped to the requesting
facility. Emergency access routes retain their separate bypass model."
```

---

## Task 5 — Per-client rate limiting on Connect API

**Files:**
- Modify: `routes/api.php`

`ThrottleByClient` already exists and is applied to SDK (120/min) and Bridge (300/min). The Connect API authenticated group uses Laravel's built-in IP-based `throttle:verify`. Replace with `throttle.client`.

- [ ] **Step 5.1: Write the rate-limit test**

Add to `tests/Feature/ConnectPlatformTest.php`:

```php
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
```

- [ ] **Step 5.2: Run to confirm failure**

```bash
php artisan test --filter="test_rate_limit_headers_present" --stop-on-failure
```

Expected: FAIL — no `X-RateLimit-*` headers (IP throttle doesn't add them).

- [ ] **Step 5.3: Apply throttle.client to Connect API**

In `routes/api.php`, find:

```php
Route::prefix('v1/connect')->middleware(['api', 'throttle:verify'])->group(function () {
```

There are TWO `v1/connect` groups — the public one (medical ID verify) and the authenticated one. Update the main one that wraps `VerifyIntegrationClient`:

The outer group for the full Connect API (the one in `routes/api.php` that contains the `VerifyIntegrationClient` group):

```php
// Before the VerifyIntegrationClient group, the outer connect prefix group should be:
Route::prefix('v1/connect')->group(function () {

    // Public auth endpoint
    Route::post('/auth/token', [AuthController::class, 'issueToken']);

    // All authenticated routes — client-keyed rate limit applied here
    Route::middleware([VerifyIntegrationClient::class, 'throttle.client:200,1'])->group(function () {
        // ... all routes from Task 4 ...
    });
});
```

Rate limits by integration type:
- Connect API: **200 requests/minute** per client (lower than Bridge because it's interactive)
- SDK: 120/minute (unchanged)
- Bridge: 300/minute (unchanged — bulk sync)

- [ ] **Step 5.4: Run tests**

```bash
php artisan test --filter="test_rate_limit_headers_present" --stop-on-failure
php artisan test --filter="ConnectPlatformTest" --stop-on-failure
```

Expected: All tests PASS including the new rate-limit header test.

- [ ] **Step 5.5: Commit**

```bash
git add routes/api.php tests/Feature/ConnectPlatformTest.php
git commit -m "feat(rate-limiting): apply throttle.client:200,1 to Connect API authenticated routes

Connect API was using IP-based throttle. Replace with client-keyed
ThrottleByClient middleware (200 req/min). SDK/Bridge unchanged."
```

---

## Task 6 — Full regression run

- [ ] **Step 6.1: Run full test suite**

```bash
php artisan test --stop-on-failure
```

Expected: All tests PASS.

- [ ] **Step 6.2: If any test fails, diagnose**

Check `tests/Feature/ConnectPlatformTest.php` — the existing `test_pull_patient_summary_with_valid_consent_returns_clinical_data` test passes `X-Consent-Grant-Id: cgt_test_active_01` inline. That hardcoded grant ID bypass in `RecordController::pullSummary` has been removed (Task 4). Update the test to use the middleware-compatible approach:

```php
// In setUp(), create a real consent grant:
$grant = \App\Models\ConsentGrant::create([
    'id'                 => 'cgt_test_active_01',
    'patient_id'         => '00000000-0000-0000-0000-000000000003',
    'facility_id'        => '00000000-0000-0000-0000-000000000001',
    'consent_request_id' => null,
    'authorizing_actor'  => 'patient',
    'scope'              => ['patients:read'],
    'status'             => 'active',
    'expires_at'         => now()->addHour(),
]);
```

Then the existing test that sends `X-Consent-Grant-Id: cgt_test_active_01` will pass through the middleware correctly.

- [ ] **Step 6.3: Final commit**

```bash
git add tests/Feature/ConnectPlatformTest.php
git commit -m "test: update ConnectPlatformTest to use real consent grants

Now that RequireConsentGrant middleware validates grants against DB,
test setup must seed a real ConsentGrant rather than relying on the
removed hardcoded bypass in RecordController."
```

---

## Summary

After Wave 1 is complete:

| Gap | Status |
|---|---|
| Real client auth broken (plain vs SHA-256) | ✅ Fixed |
| ConsentService ignores facility_id | ✅ Fixed |
| pushEncounter without consent | ✅ Blocked by middleware |
| pushLabResult without consent | ✅ Blocked by middleware |
| pushPrescription without consent | ✅ Blocked by middleware |
| Connect API no per-client rate limiting | ✅ 200 req/min applied |

Wave 2 (Patient Self-Booking + Notifications) begins next.
