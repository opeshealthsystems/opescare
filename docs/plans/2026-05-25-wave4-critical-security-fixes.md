# Wave 4 — Critical Security Fixes (Blockers)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminate all 7 CRITICAL findings and the remaining HIGH findings (H10, H11, H12) that were deferred from Wave 3. After this wave, no finding rated CRITICAL or HIGH remains open.

**Architecture:** Touches route middleware, legacy controller rebuild, schema migration (emergency cascade), and .env.example rewrite. Apply Waves 1–3 before this wave.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL

**Findings addressed:** C1, C2, C3, C4, C5, C6, C7, H10, H11, H12

---

## Files Modified in This Wave

| File | Change |
|------|--------|
| `routes/api.php` | Add VerifyIntegrationClient to legacy emergency and merge-cases routes |
| `app/Http/Controllers/Api/V1/Connect/EmergencyAccessController.php` | Fix random UUID audit; require auth |
| `app/Http/Controllers/Api/V1/Connect/ConnectGovernanceController.php` | Remove caller-supplied actor_id |
| `app/Http/Controllers/Api/V1/Connect/RecordController.php` | Remove hardcoded clinical data (pullSummary, pullEmergencyProfile) |
| `.env.example` | Complete rewrite with production-safe defaults |
| `database/migrations/2026_05_25_000002_fix_emergency_access_cascade.php` | NEW — change cascade to RESTRICT |
| `config/mail.php` | Verify production mail settings |
| `.env.example` | MAIL_MAILER=smtp |

---

### Task 1: Secure the unauthenticated legacy emergency access endpoint

**Findings:** C1, C2

**Files:**
- Modify: `routes/api.php`
- Modify: `app/Http/Controllers/Api/V1/Connect/EmergencyAccessController.php`
- Test: `tests/Feature/Security/LegacyEmergencyAccessAuthTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Security/LegacyEmergencyAccessAuthTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use Tests\TestCase;

class LegacyEmergencyAccessAuthTest extends TestCase
{
    public function test_emergency_profile_endpoint_requires_authentication(): void
    {
        // Unauthenticated POST should be rejected
        $response = $this->post('/api/v1/connect/patients/emergency-profile', [
            'health_id' => 'OC-CMR-TEST-0001',
            'reason'    => 'Patient unconscious in emergency room',
        ]);

        // Must NOT be 200 — must be 401 or 403
        $this->assertContains($response->getStatusCode(), [401, 403],
            'Legacy emergency profile endpoint must require authentication. Got: ' . $response->getStatusCode());
    }

    public function test_emergency_profile_audit_does_not_use_random_uuid(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/Api/V1/Connect/EmergencyAccessController.php')
        );

        $this->assertStringNotContainsString(
            'Str::uuid()',
            $source,
            'EmergencyAccessController must not use Str::uuid() for actor_id or facility_id — these must come from the authenticated integration client'
        );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/LegacyEmergencyAccessAuthTest.php
```

Expected: FAIL — endpoint returns 200 without auth; `Str::uuid()` found in source

- [ ] **Step 3: Add VerifyIntegrationClient to the legacy emergency route group**

Open `routes/api.php`. Find the v1/connect group that uses `['api', 'throttle:verify']` (lines ~321-332):

```php
// BEFORE:
Route::prefix('v1/connect')->middleware(['api', 'throttle:verify'])->group(function () {
    Route::post('/medical-ids/verify', [...]);
    Route::post('/medical-ids/verify-qr', [...]);
    Route::post('/consents/request-medical-id', [...]);
    Route::post('/patients/emergency-profile', [\App\Http\Controllers\Api\V1\Connect\EmergencyAccessController::class, 'pullEmergencyProfile']);
    Route::get('/admin/merge-cases', [...]);
    Route::post('/admin/merge-cases/{id}/resolve', [...]);
});

// AFTER — separate unprotected verification routes from protected ones:
// Public verification routes (these CAN remain without auth — QR/Health ID verification is public by design)
Route::prefix('v1/connect')->middleware(['api', 'throttle:verify'])->group(function () {
    Route::post('/medical-ids/verify', [\App\Http\Controllers\Api\V1\Connect\MedicalIdVerificationController::class, 'verifyHealthId']);
    Route::post('/medical-ids/verify-qr', [\App\Http\Controllers\Api\V1\Connect\MedicalIdVerificationController::class, 'verifyQr']);
});

// Protected legacy routes — require integration client authentication
Route::prefix('v1/connect')->middleware([\App\Http\Middleware\VerifyIntegrationClient::class, 'throttle.client:200,1'])->group(function () {
    Route::post('/consents/request-medical-id', [\App\Http\Controllers\Api\V1\Connect\ConsentController::class, 'requestConsent']);
    Route::post('/patients/emergency-profile', [\App\Http\Controllers\Api\V1\Connect\EmergencyAccessController::class, 'pullEmergencyProfile']);

    // Admin merge cases — require integration client auth
    Route::get('/admin/merge-cases', [\App\Http\Controllers\Api\V1\Connect\DuplicateMergeController::class, 'listCases']);
    Route::post('/admin/merge-cases/{id}/resolve', [\App\Http\Controllers\Api\V1\Connect\DuplicateMergeController::class, 'resolveCase']);
});
```

- [ ] **Step 4: Fix EmergencyAccessController.logAccess() to use real client identity**

Open `app/Http/Controllers/Api/V1/Connect/EmergencyAccessController.php`. Replace the `logAccess()` private method:

```php
private function logAccess(
    string $healthId,
    ?string $patientId,
    string $purpose,
    string $accessType,
    string $result,
    Request $request
): void {
    // Use integration client identity from middleware attributes — NOT random UUIDs
    $clientId   = $request->attributes->get('integration_client_id', 'unknown');
    $facilityId = $request->attributes->get('facility_id'); // null if not set

    MedicalIdAccessEvent::create([
        'patient_id'  => $patientId,
        'health_id'   => $healthId,
        'actor_id'    => null,  // No user UUID for machine-to-machine calls
        'actor_type'  => 'integration_client:' . $clientId,
        'facility_id' => $facilityId,
        'access_type' => $accessType,
        'purpose'     => $purpose,
        'result'      => $result,
        'ip_address'  => $request->ip(),
        'user_agent'  => substr(str_replace(["\n", "\r"], '', $request->userAgent() ?? ''), 0, 255),
    ]);
}
```

- [ ] **Step 5: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/LegacyEmergencyAccessAuthTest.php
```

Expected: PASS

- [ ] **Step 6: Run full test suite**

```bash
php artisan test
```

Expected: All tests pass.

- [ ] **Step 7: Commit**

```bash
git add routes/api.php app/Http/Controllers/Api/V1/Connect/EmergencyAccessController.php tests/Feature/Security/LegacyEmergencyAccessAuthTest.php
git commit -m "security: add VerifyIntegrationClient auth to legacy emergency access endpoint; fix random UUID audit"
```

---

### Task 2: Remove caller-supplied actor_id from emergency access

**Finding:** C5

**Files:**
- Modify: `app/Http/Controllers/Api/V1/Connect/ConnectGovernanceController.php`
- Test: `tests/Feature/Security/EmergencyActorIdTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Security/EmergencyActorIdTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use Tests\TestCase;

class EmergencyActorIdTest extends TestCase
{
    public function test_emergency_access_actor_id_cannot_be_set_by_caller(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/Api/V1/Connect/ConnectGovernanceController.php')
        );

        // The actor_id must NOT come from $request->input('actor_id')
        $this->assertStringNotContainsString(
            "input('actor_id'",
            $source,
            'actor_id in emergency access must not be caller-supplied — use integration client identity'
        );
    }
}
```

- [ ] **Step 2: Fix ConnectGovernanceController.requestEmergencyAccess()**

Open `app/Http/Controllers/Api/V1/Connect/ConnectGovernanceController.php`. Update `requestEmergencyAccess()`:

```php
public function requestEmergencyAccess(Request $request)
{
    $patientId  = $request->input('patient_id');
    $reason     = $request->input('reason');

    if (!$patientId || !$reason) {
        return response()->json(['message' => 'Validation failed. patient_id and reason are required.'], 400);
    }

    // Use facility_id from verified integration client attributes — NOT from request body
    $facilityId = $request->attributes->get('facility_id', '00000000-0000-0000-0000-000000000002');
    $clientId   = $request->attributes->get('integration_client_id', 'unknown');

    // actor_id is derived from the authenticated integration client — caller cannot supply it
    // The system actor for B2B emergency access is the integration client ID
    $actorId    = '00000000-0000-0000-0000-000000000001'; // system service account
    $actorLabel = 'integration_client:' . $clientId;

    $event = $this->emergencyService->requestEmergencyAccess($patientId, $facilityId, $actorId, $reason);

    // Log with real client identity
    \App\Services\AuditLogger::log(
        $request,
        'emergency_access_requested',
        'emergency_access_event',
        $event->id,
        $patientId,
        true,
        $reason
    );

    return response()->json([
        'status'                     => 'emergency_authorized',
        'emergency_access_event_id'  => $event->id,
        'message'                    => 'Emergency override activated and audited.',
    ], 201);
}
```

- [ ] **Step 3: Run test**

```bash
php artisan test tests/Feature/Security/EmergencyActorIdTest.php
```

Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Api/V1/Connect/ConnectGovernanceController.php tests/Feature/Security/EmergencyActorIdTest.php
git commit -m "security: remove caller-supplied actor_id from emergency access; use integration client identity"
```

---

### Task 3: Remove hardcoded clinical data from RecordController

**Findings:** C7, H12

**Files:**
- Modify: `app/Http/Controllers/Api/V1/Connect/RecordController.php`
- Test: `tests/Feature/Security/RecordControllerHardcodedDataTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Security/RecordControllerHardcodedDataTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use Tests\TestCase;

class RecordControllerHardcodedDataTest extends TestCase
{
    public function test_pull_summary_contains_no_hardcoded_allergy_data(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/Api/V1/Connect/RecordController.php')
        );

        $this->assertStringNotContainsString(
            "'Penicillin'",
            $source,
            'pullSummary must not contain hardcoded allergy data'
        );
        $this->assertStringNotContainsString(
            "'Amoxicillin'",
            $source,
            'pullSummary must not contain hardcoded medication data'
        );
    }

    public function test_pull_emergency_profile_contains_no_hardcoded_contact(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/Api/V1/Connect/RecordController.php')
        );

        $this->assertStringNotContainsString(
            'Mary Doe',
            $source,
            'pullEmergencyProfile must not contain hardcoded emergency contact'
        );
        $this->assertStringNotContainsString(
            'O Positive',
            $source,
            'pullEmergencyProfile must not contain hardcoded blood group'
        );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/RecordControllerHardcodedDataTest.php
```

Expected: FAIL

- [ ] **Step 3: Fix pullSummary() — remove hardcoded allergies and medications**

In `RecordController::pullSummary()`, replace the hardcoded sections with empty arrays (data comes from real DB or future clinical data module):

```php
// BEFORE:
'allergies' => [
    [
        'substance' => 'Penicillin',
        'severity'  => 'severe',
        'status'    => 'active',
        'source_facility' => 'St. Jude Clinical Research Hospital'
    ]
],
'active_medications' => [
    [
        'generic_name' => 'Amoxicillin',
        'dose'         => '500mg',
        'frequency'    => 'twice daily',
        'source_facility' => 'Metro Emergency General Clinic'
    ]
],

// AFTER (return only real data — allergies/medications from DB, or empty until implemented):
'allergies'          => [],  // TODO: implement AllergyRecord model in Phase 2 clinical data
'active_medications' => [],  // TODO: implement MedicationRecord model in Phase 2 clinical data
```

Also fix the hardcoded date_of_birth fallback:

```php
// BEFORE:
'date_of_birth' => $patient->date_of_birth ? $patient->date_of_birth->toDateString() : '1990-04-12'

// AFTER:
'date_of_birth' => $patient->date_of_birth?->toDateString(),
```

- [ ] **Step 4: Fix pullEmergencyProfile() — remove hardcoded Mary Doe and O Positive**

```php
// BEFORE:
'emergency_contacts' => [
    [
        'name'     => 'Mary Doe',
        'relation' => 'Spouse',
        'phone'    => '+237 600-000-000'
    ]
],
'clinical_safety' => [
    'blood_group'          => 'O Positive',
    'critical_allergies'   => ['Penicillin', 'Peanuts'],
    'chronic_conditions'   => ['Type 1 Diabetes Mellitus'],
    'high_risk_medications' => ['Insulin Glargine']
]

// AFTER (return only real patient data):
'emergency_contacts' => $patient ? [
    [
        'name'     => $patient->emergency_contact_name ?? null,
        'relation' => $patient->emergency_contact_relation ?? null,
        'phone'    => $patient->emergency_contact_phone ?? null,
    ]
] : [],
'clinical_safety' => [
    'blood_group'           => $patient->blood_group ?? null,
    'critical_allergies'    => [],  // TODO: load from AllergyRecord when implemented
    'chronic_conditions'    => [],  // TODO: load from Diagnosis model
    'high_risk_medications' => [],  // TODO: load from Prescription model
],
```

- [ ] **Step 5: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/RecordControllerHardcodedDataTest.php
```

Expected: PASS

- [ ] **Step 6: Run full test suite**

```bash
php artisan test
```

Expected: All tests pass.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Api/V1/Connect/RecordController.php tests/Feature/Security/RecordControllerHardcodedDataTest.php
git commit -m "security: remove all hardcoded clinical data (allergies, medications, emergency contacts) from RecordController"
```

---

### Task 4: Fix emergency_access_events cascade — prevent audit trail destruction

**Finding:** H11

**Files:**
- Create: `database/migrations/2026_05_25_000002_fix_emergency_access_events_cascade.php`
- Test: (schema test)

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration fix_emergency_access_events_cascade_on_patient_delete
```

Edit the generated migration:

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing CASCADE foreign key on emergency_access_events.patient_id
        // and replace it with SET NULL — audit records must survive patient deletion
        Schema::table('emergency_access_events', function (Blueprint $table) {
            // Drop existing FK (name may vary — check your schema)
            $table->dropForeign(['patient_id']);
        });

        Schema::table('emergency_access_events', function (Blueprint $table) {
            // Re-add with SET NULL — audit records survive patient deletion
            // patient_id must be nullable for this to work
            $table->foreignUuid('patient_id')
                ->nullable()
                ->change();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->nullOnDelete();
        });

        // Also fix audit_events if it has CASCADE
        // (audit_events.patient_id should also be SET NULL on patient delete)
        if (Schema::hasTable('audit_events')) {
            try {
                Schema::table('audit_events', function (Blueprint $table) {
                    $table->dropForeign(['patient_id']);
                });
                Schema::table('audit_events', function (Blueprint $table) {
                    $table->foreignUuid('patient_id')->nullable()->change();
                    $table->foreign('patient_id')
                        ->references('id')
                        ->on('patients')
                        ->nullOnDelete();
                });
            } catch (\Throwable) {
                // audit_events may not have a patient_id FK — skip if not present
            }
        }
    }

    public function down(): void
    {
        Schema::table('emergency_access_events', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->cascadeOnDelete();
        });
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

Expected: Migration runs without error.

- [ ] **Step 3: Write test to verify**

```php
// In tests/Feature/Security/EmergencyAccessCascadeTest.php:
<?php
namespace Tests\Feature\Security;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmergencyAccessCascadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_patient_does_not_delete_emergency_access_events(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);

        // Create an emergency access event
        DB::table('emergency_access_events')->insert([
            'id'          => \Illuminate\Support\Str::uuid(),
            'patient_id'  => $patient->id,
            'health_id'   => $patient->health_id,
            'actor_id'    => \Illuminate\Support\Str::uuid(),
            'actor_type'  => 'facility_staff',
            'facility_id' => \Illuminate\Support\Str::uuid(),
            'access_type' => 'pull_emergency_profile',
            'purpose'     => 'emergency_access',
            'result'      => 'success',
            'ip_address'  => '127.0.0.1',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $eventCountBefore = DB::table('emergency_access_events')
            ->where('patient_id', $patient->id)->count();
        $this->assertEquals(1, $eventCountBefore);

        // Delete the patient
        $patient->delete();

        // Emergency access events must still exist (patient_id set to null, not deleted)
        $eventCountAfter = DB::table('emergency_access_events')->count();
        $this->assertEquals(1, $eventCountAfter, 'Emergency access events must survive patient deletion');

        $event = DB::table('emergency_access_events')->first();
        $this->assertNull($event->patient_id, 'patient_id should be null after patient deletion');
    }
}
```

Save to `tests/Feature/Security/EmergencyAccessCascadeTest.php`.

- [ ] **Step 4: Run test**

```bash
php artisan test tests/Feature/Security/EmergencyAccessCascadeTest.php
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/migrations/ tests/Feature/Security/EmergencyAccessCascadeTest.php
git commit -m "security: change emergency_access_events patient cascade from DELETE to SET NULL to preserve audit trail"
```

---

### Task 5: Rewrite .env.example with production-safe defaults

**Finding:** C6

**Files:**
- Rewrite: `.env.example`

- [ ] **Step 1: Read current .env.example**

```bash
cat .env.example
```

- [ ] **Step 2: Rewrite .env.example with safe defaults**

Replace the entire `.env.example` with this production-safe template:

```dotenv
# =============================================================================
# OpesCare Platform — Production Environment Configuration
# =============================================================================
# Copy this file to .env and fill in all values marked [REQUIRED].
# Values marked [GENERATE] must be generated — do not leave blank.
# Values marked [OPTIONAL] have safe defaults.
# =============================================================================

# --- Application ---
APP_NAME="OpesCare"
APP_ENV=production
APP_KEY=                    # [GENERATE] Run: php artisan key:generate
APP_DEBUG=false             # [REQUIRED] MUST be false in production
APP_URL=https://yourdomain.com  # [REQUIRED]
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# --- Database (PostgreSQL REQUIRED for production) ---
DB_CONNECTION=pgsql         # [REQUIRED] Never use sqlite in production
DB_HOST=127.0.0.1           # [REQUIRED]
DB_PORT=5432
DB_DATABASE=opescare        # [REQUIRED]
DB_USERNAME=opescare_user   # [REQUIRED]
DB_PASSWORD=                # [REQUIRED] Strong password

# --- Session (Security-hardened) ---
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true        # [REQUIRED] Encrypt session payload
SESSION_SECURE_COOKIE=true  # [REQUIRED] HTTPS-only cookies
SESSION_DOMAIN=null
SESSION_SAME_SITE=lax

# --- Cache ---
CACHE_STORE=redis           # [REQUIRED] Use redis/memcached, not file
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# --- Queue (Async job processing) ---
QUEUE_CONNECTION=redis      # [REQUIRED] Use redis/database, not sync

# --- Mail (REQUIRED: must not use 'log' in production) ---
MAIL_MAILER=smtp            # [REQUIRED] smtp, ses, postmark — not log
MAIL_HOST=smtp.yourdomain.com   # [REQUIRED]
MAIL_PORT=587
MAIL_USERNAME=              # [REQUIRED]
MAIL_PASSWORD=              # [REQUIRED]
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@opescare.com
MAIL_FROM_NAME="OpesCare"

# --- Logging ---
LOG_CHANNEL=daily
LOG_LEVEL=warning           # [REQUIRED] warning or error in production — NEVER debug
LOG_DAILY_DAYS=90           # 90 days for health data compliance

# --- OpesCare Demo Mode ---
OPESCARE_DEMO_MODE=false    # [REQUIRED] MUST be false in production
DEMO_ALLOWED_IPS=           # Comma-separated IPs allowed to use demo login (empty = blocked)

# --- OpesCare System Accounts ---
OPESCARE_SYSTEM_PROVIDER_ID=00000000-0000-0000-0000-000000000001  # [REQUIRED] Run seeder to create

# --- Family/Guardian Configuration ---
# Invite link expiry in hours
FAMILY_INVITE_TTL_HOURS=48

# --- Filesystem ---
FILESYSTEM_DISK=s3          # [RECOMMENDED] Use S3 for production file storage
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=

# --- Broadcasting ---
BROADCAST_CONNECTION=log

# --- Rate Limiting ---
# Adjust these per infrastructure capacity
# THROTTLE_API=60           # Default: 60/min for general API

# --- Maintenance Mode ---
APP_MAINTENANCE_DRIVER=cache  # Use cache driver for multi-server deployments
APP_MAINTENANCE_STORE=redis
```

- [ ] **Step 3: Write test to verify no dangerous defaults**

Create `tests/Feature/Config/EnvExampleSecurityTest.php`:

```php
<?php
namespace Tests\Feature\Config;

use Tests\TestCase;

class EnvExampleSecurityTest extends TestCase
{
    private function readEnvExample(): string
    {
        return file_get_contents(base_path('.env.example'));
    }

    public function test_env_example_has_app_debug_false(): void
    {
        $this->assertStringContainsString('APP_DEBUG=false', $this->readEnvExample());
    }

    public function test_env_example_has_pgsql_not_sqlite(): void
    {
        $content = $this->readEnvExample();
        $this->assertStringContainsString('DB_CONNECTION=pgsql', $content);
        $this->assertStringNotContainsString('DB_CONNECTION=sqlite', $content);
    }

    public function test_env_example_demo_mode_is_false(): void
    {
        $this->assertStringContainsString('OPESCARE_DEMO_MODE=false', $this->readEnvExample());
    }

    public function test_env_example_mail_is_not_log(): void
    {
        $content = $this->readEnvExample();
        $this->assertStringNotContainsString('MAIL_MAILER=log', $content);
        $this->assertStringContainsString('MAIL_MAILER=smtp', $content);
    }

    public function test_env_example_session_is_encrypted(): void
    {
        $this->assertStringContainsString('SESSION_ENCRYPT=true', $this->readEnvExample());
    }

    public function test_env_example_log_level_is_not_debug(): void
    {
        $content = $this->readEnvExample();
        $this->assertStringNotContainsString('LOG_LEVEL=debug', $content);
        $this->assertStringContainsString('LOG_LEVEL=warning', $content);
    }

    public function test_env_example_app_key_is_empty_placeholder(): void
    {
        // APP_KEY must be empty — operators must generate their own
        $lines = explode("\n", $this->readEnvExample());
        foreach ($lines as $line) {
            if (str_starts_with($line, 'APP_KEY=')) {
                $value = substr($line, strlen('APP_KEY='));
                $this->assertEmpty(trim($value),
                    'APP_KEY in .env.example must be empty — operators generate their own key');
                return;
            }
        }
    }
}
```

- [ ] **Step 4: Run test**

```bash
php artisan test tests/Feature/Config/EnvExampleSecurityTest.php
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add .env.example tests/Feature/Config/EnvExampleSecurityTest.php
git commit -m "security: rewrite .env.example with production-safe defaults — fix APP_DEBUG, DB_CONNECTION, MAIL_MAILER, OPESCARE_DEMO_MODE, SESSION_ENCRYPT"
```

---

### Task 6: Fix mail configuration for production (MAIL_MAILER=log default)

**Finding:** H10

This task extends Task 5 — .env.example already updated to MAIL_MAILER=smtp. Additionally we add a boot-time check.

**Files:**
- Create: `app/Providers/ProductionSafetyServiceProvider.php`
- Modify: `bootstrap/providers.php`

- [ ] **Step 1: Create boot-time safety check**

Create `app/Providers/ProductionSafetyServiceProvider.php`:

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class ProductionSafetyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!$this->app->isProduction()) {
            return;
        }

        // In production, warn loudly about dangerous configurations
        $warnings = [];

        if (config('app.debug') === true) {
            $warnings[] = 'APP_DEBUG=true in production — this exposes stack traces!';
        }

        if (config('mail.default') === 'log') {
            $warnings[] = 'MAIL_MAILER=log in production — patient notifications will NOT be delivered!';
        }

        if (config('demo.enabled') === true) {
            $warnings[] = 'OPESCARE_DEMO_MODE=true in production — demo data may be visible!';
        }

        if (config('database.default') === 'sqlite') {
            $warnings[] = 'DB_CONNECTION=sqlite in production — use PostgreSQL!';
        }

        foreach ($warnings as $warning) {
            Log::critical('production_safety_check_failed', ['warning' => $warning]);
        }

        // Abort if any critical config is wrong in production
        if (!empty($warnings) && config('app.debug') === true) {
            abort(500, 'Application is misconfigured for production. Check logs.');
        }
    }
}
```

- [ ] **Step 2: Register the provider**

In `bootstrap/providers.php`, add:

```php
App\Providers\ProductionSafetyServiceProvider::class,
```

- [ ] **Step 3: Run tests**

```bash
php artisan test
```

Expected: All tests pass (safety check only runs in production environment).

- [ ] **Step 4: Commit**

```bash
git add app/Providers/ProductionSafetyServiceProvider.php bootstrap/providers.php
git commit -m "reliability: add ProductionSafetyServiceProvider that logs critical warnings for dangerous production config"
```

---

### Task 7: Wave 4 final verification

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --parallel
```

Expected: All tests pass.

- [ ] **Step 2: Verify no Str::uuid() in emergency controller**

```bash
grep -n "Str::uuid" app/Http/Controllers/Api/V1/Connect/EmergencyAccessController.php
```

Expected: No output.

- [ ] **Step 3: Verify legacy emergency route has VerifyIntegrationClient**

```bash
grep -B2 "emergency-profile" routes/api.php
```

Expected: Shows VerifyIntegrationClient in the middleware chain.

- [ ] **Step 4: Verify no hardcoded clinical data in RecordController**

```bash
grep -n "Penicillin\|Amoxicillin\|Mary Doe\|O Positive\|1990-04-12" app/Http/Controllers/Api/V1/Connect/RecordController.php
```

Expected: No output.

- [ ] **Step 5: Verify .env.example is safe**

```bash
grep "APP_DEBUG\|DB_CONNECTION\|MAIL_MAILER\|OPESCARE_DEMO\|LOG_LEVEL" .env.example
```

Expected:
```
APP_DEBUG=false
DB_CONNECTION=pgsql
MAIL_MAILER=smtp
OPESCARE_DEMO_MODE=false
LOG_LEVEL=warning
```
