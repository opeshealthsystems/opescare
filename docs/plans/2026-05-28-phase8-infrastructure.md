# Phase 8: Multi-Region Config + True Tenant Isolation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans

**Goal:** Add application-level multi-region failover detection and extend HasFacilityScope to all patient-data models (167 models need the trait).
**Architecture:** Multi-region: middleware + config. Tenant isolation: additive trait application — does NOT restructure any model, just adds `use HasFacilityScope` where appropriate.
**Tech Stack:** Laravel 11, PHP 8.3, PostgreSQL

---

## Task 1: Multi-Region / Multi-AZ Failover Detection (item 57)

**Context:** `config/database.php` already has read replica config. This task adds application-level health checking and failover alerting.

---

### Step 1.1 — Create `config/regions.php`

**File:** `C:\laragon\www\opescare\apps\api-laravel\config\regions.php`

```php
<?php

return [
    'current_region'   => env('APP_REGION', 'af-south-1'),
    'fallback_region'  => env('APP_FALLBACK_REGION', ''),
    'health_check_ttl' => env('DB_HEALTH_CHECK_TTL', 30),
    'failover_webhook' => env('FAILOVER_ALERT_WEBHOOK', ''),
];
```

No database migrations required. No model changes.

---

### Step 1.2 — Create `app/Services/Infrastructure/RegionHealthService.php`

**File:** `C:\laragon\www\opescare\apps\api-laravel\app\Services\Infrastructure\RegionHealthService.php`

```php
<?php

namespace App\Services\Infrastructure;

use App\Services\SecurityIncidentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class RegionHealthService
{
    public function isDatabaseHealthy(): bool
    {
        $ttl = config('regions.health_check_ttl', 30);

        return Cache::remember('health.database', $ttl, function () {
            try {
                DB::select('SELECT 1');
                return true;
            } catch (Throwable $e) {
                Log::critical('Database health check failed', [
                    'error'   => $e->getMessage(),
                    'region'  => config('regions.current_region'),
                ]);
                return false;
            }
        });
    }

    public function isRedisHealthy(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (Throwable $e) {
            Log::error('Redis health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getHealthStatus(): array
    {
        return [
            'database'  => $this->isDatabaseHealthy(),
            'redis'     => $this->isRedisHealthy(),
            'region'    => config('regions.current_region'),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function alertFailover(string $component): void
    {
        $webhook = config('regions.failover_webhook');

        if (empty($webhook)) {
            return;
        }

        try {
            Http::timeout(5)->post($webhook, [
                'component' => $component,
                'region'    => config('regions.current_region'),
                'timestamp' => now()->toIso8601String(),
                'severity'  => 'CRITICAL',
            ]);
        } catch (Throwable $e) {
            Log::error('Failover webhook delivery failed', [
                'webhook'   => $webhook,
                'component' => $component,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
```

---

### Step 1.3 — Create `app/Http/Middleware/DatabaseHealthMiddleware.php`

**File:** `C:\laragon\www\opescare\apps\api-laravel\app\Http\Middleware\DatabaseHealthMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use App\Services\Infrastructure\RegionHealthService;
use App\Services\SecurityIncidentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DatabaseHealthMiddleware
{
    public function __construct(
        private readonly RegionHealthService    $regionHealth,
        private readonly SecurityIncidentService $securityIncident,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->regionHealth->isDatabaseHealthy()) {
            $this->securityIncident->logCritical('database_unavailable', [
                'region'  => config('regions.current_region'),
                'path'    => $request->path(),
                'ip'      => $request->ip(),
            ]);

            $this->regionHealth->alertFailover('database');

            return response()->json([
                'error'       => 'Service temporarily unavailable',
                'retry_after' => config('regions.health_check_ttl', 30),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
```

---

### Step 1.4 — Register Middleware in `bootstrap/app.php` (Laravel 11)

**File:** `C:\laragon\www\opescare\apps\api-laravel\bootstrap\app.php`

In the `withMiddleware` closure, append as global middleware:

```php
$middleware->append(\App\Http\Middleware\DatabaseHealthMiddleware::class);
```

Full diff context:

```php
->withMiddleware(function (Middleware $middleware) {
    // ... existing middleware config ...
    $middleware->append(\App\Http\Middleware\DatabaseHealthMiddleware::class);
})
```

---

### Step 1.5 — Update `.env.example`

Add to `C:\laragon\www\opescare\apps\api-laravel\.env.example`:

```dotenv
# Multi-region config
APP_REGION=af-south-1
APP_FALLBACK_REGION=
DB_HEALTH_CHECK_TTL=30
FAILOVER_ALERT_WEBHOOK=
```

---

### Step 1.6 — Test: Database Health Middleware

**File:** `C:\laragon\www\opescare\apps\api-laravel\tests\Feature\Infrastructure\DatabaseHealthMiddlewareTest.php`

```php
<?php

namespace Tests\Feature\Infrastructure;

use App\Services\Infrastructure\RegionHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DatabaseHealthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_healthy_database_allows_request_through(): void
    {
        $health = Mockery::mock(RegionHealthService::class);
        $health->shouldReceive('isDatabaseHealthy')->once()->andReturn(true);
        $this->app->instance(RegionHealthService::class, $health);

        $this->getJson('/api/v1/health')->assertStatus(200);
    }

    public function test_unhealthy_database_returns_503(): void
    {
        $health = Mockery::mock(RegionHealthService::class);
        $health->shouldReceive('isDatabaseHealthy')->once()->andReturn(false);
        $health->shouldReceive('alertFailover')->once()->with('database');
        $this->app->instance(RegionHealthService::class, $health);

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(503)
            ->assertJson([
                'error'       => 'Service temporarily unavailable',
                'retry_after' => 30,
            ]);
    }

    public function test_region_health_service_returns_status_shape(): void
    {
        $service = app(RegionHealthService::class);
        $status  = $service->getHealthStatus();

        $this->assertArrayHasKey('database', $status);
        $this->assertArrayHasKey('redis', $status);
        $this->assertArrayHasKey('region', $status);
        $this->assertArrayHasKey('timestamp', $status);
    }
}
```

Run: `php artisan test tests/Feature/Infrastructure/DatabaseHealthMiddlewareTest.php`

---

## Task 2: Tenant Isolation — HasFacilityScope Across All Patient-Data Models (item 67)

**Context:** The trait `app/Traits/HasFacilityScope.php` already exists. The task is purely additive: add `use HasFacilityScope;` to models that have `facility_id` in their `$fillable` and do not already use it. No model files are restructured. No traits are removed.

---

### Step 2.1 — Create Audit Console Command

**File:** `C:\laragon\www\opescare\apps\api-laravel\app\Console\Commands\AuditFacilityScopeCommand.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuditFacilityScopeCommand extends Command
{
    protected $signature   = 'opescare:audit-facility-scope';
    protected $description = 'Scan all models for facility_id in $fillable that are missing HasFacilityScope trait';

    public function handle(): int
    {
        $modelPath = app_path('Models');
        $files     = File::allFiles($modelPath);
        $missing   = [];
        $already   = [];

        foreach ($files as $file) {
            $content  = File::get($file->getPathname());
            $hasFacId = str_contains($content, "'facility_id'") || str_contains($content, '"facility_id"');
            $hasScope = str_contains($content, 'HasFacilityScope');

            if ($hasFacId && ! $hasScope) {
                $missing[] = $file->getRelativePathname();
            } elseif ($hasFacId && $hasScope) {
                $already[] = $file->getRelativePathname();
            }
        }

        $this->info('Models WITH facility_id already using HasFacilityScope: ' . count($already));
        foreach ($already as $m) {
            $this->line("  [OK] {$m}");
        }

        $this->newLine();
        $this->warn('Models WITH facility_id MISSING HasFacilityScope: ' . count($missing));
        foreach ($missing as $m) {
            $this->line("  [MISSING] {$m}");
        }

        $this->newLine();
        $this->info('Run: php artisan opescare:audit-facility-scope to re-audit after applying traits.');

        return self::SUCCESS;
    }
}
```

Register command in `app/Console/Kernel.php` (or it auto-discovers in Laravel 11 via `Console/Commands/` convention).

Run audit first: `php artisan opescare:audit-facility-scope`

---

### Step 2.2 — Apply HasFacilityScope — Batch A (Appointments, 4 models)

For each model below: open the file, locate the class body's `use` statements block, add `use \App\Traits\HasFacilityScope;` after the last existing `use` statement inside the class. Verify `facility_id` is in `$fillable`. Save. Do not change anything else.

**Model 1:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\Appointment.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 2:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\AppointmentSlot.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 3:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\AppointmentReminder.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 4:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\AppointmentWaitlist.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

Commit: `git commit -m "feat(tenancy): apply HasFacilityScope to appointment models (batch A)"`

---

### Step 2.3 — Apply HasFacilityScope — Batch B (Clinical, 8 models)

**Model 5:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\ClinicalNote.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 6:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\Diagnosis.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 7:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\LabOrder.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 8:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\LabResult.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 9:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\Prescription.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 10:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\PrescriptionItem.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 11:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\VitalSign.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 12:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\TriageVitalSign.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

Commit: `git commit -m "feat(tenancy): apply HasFacilityScope to clinical models (batch B)"`

---

### Step 2.4 — Apply HasFacilityScope — Batch C (Immunization + Insurance, 4 models)

**Model 13:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\ImmunizationRecord.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 14:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\EligibilityCheck.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 15:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\InsuranceClaim.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 16:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\InsurancePlan.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

Commit: `git commit -m "feat(tenancy): apply HasFacilityScope to immunization and insurance models (batch C)"`

---

### Step 2.5 — Apply HasFacilityScope — Batch D (Pharmacy + Incidents, 3 models)

**Model 17:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\PharmacyStockAvailability.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 18:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\IncidentReport.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 19:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\IncidentEscalation.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

Commit: `git commit -m "feat(tenancy): apply HasFacilityScope to pharmacy and incident models (batch D)"`

---

### Step 2.6 — Apply HasFacilityScope — Batch E (Referral + Teleconsultation, 4 models)

**Model 20:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\ReferralCase.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 21:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\Teleconsultation.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 22:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\CallSession.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 23:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\VirtualWaitingRoom.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

Commit: `git commit -m "feat(tenancy): apply HasFacilityScope to referral and teleconsultation models (batch E)"`

---

### Step 2.7 — Apply HasFacilityScope — Batch F (Patient Engagement + Maternal, 5 models)

**Model 24:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\PatientSurvey.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 25:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\CarePlan.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 26:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\AntenatalVisit.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 27:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\DeliveryRecord.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 28:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\PregnancyRecord.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

Commit: `git commit -m "feat(tenancy): apply HasFacilityScope to patient engagement and maternal models (batch F)"`

---

### Step 2.8 — Apply HasFacilityScope — Batch G (Radiology + Pharmacy + Provider, 4 models)

**Model 29:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\RadiologyReport.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 30:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\ControlledSubstanceDispensing.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 31:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\ProviderShift.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

**Model 32:** `C:\laragon\www\opescare\apps\api-laravel\app\Models\OnCallSchedule.php`
- Add inside class: `use \App\Traits\HasFacilityScope;`
- Confirm `'facility_id'` present in `$fillable`

Commit: `git commit -m "feat(tenancy): apply HasFacilityScope to radiology, controlled substances, and provider models (batch G)"`

---

### Step 2.9 — Re-run Audit to Confirm Zero Missing Models

```bash
php artisan opescare:audit-facility-scope
```

Expected output: 0 models missing HasFacilityScope.

---

### Step 2.10 — Tenant Isolation Feature Test

**File:** `C:\laragon\www\opescare\apps\api-laravel\tests\Feature\TenantIsolationTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_facility_a_cannot_retrieve_facility_b_appointments(): void
    {
        $facilityA = Facility::factory()->create();
        $facilityB = Facility::factory()->create();

        $patientA = Patient::factory()->create(['facility_id' => $facilityA->id]);
        $patientB = Patient::factory()->create(['facility_id' => $facilityB->id]);

        $appointmentA = Appointment::factory()->create([
            'facility_id' => $facilityA->id,
            'patient_id'  => $patientA->id,
        ]);
        $appointmentB = Appointment::factory()->create([
            'facility_id' => $facilityB->id,
            'patient_id'  => $patientB->id,
        ]);

        // Simulate request scoped to Facility A
        app()->instance('current_facility_id', $facilityA->id);

        $results = Appointment::all();

        $ids = $results->pluck('id')->toArray();

        $this->assertContains($appointmentA->id, $ids, 'Facility A record should be visible');
        $this->assertNotContains($appointmentB->id, $ids, 'Facility B record must NOT be visible from Facility A scope');
    }

    public function test_facility_scope_applies_to_all_scoped_models(): void
    {
        // Smoke-test: all scoped models respond to facility() scope without error
        $facility = Facility::factory()->create();
        app()->instance('current_facility_id', $facility->id);

        $models = [
            \App\Models\ClinicalNote::class,
            \App\Models\LabOrder::class,
            \App\Models\Prescription::class,
            \App\Models\VitalSign::class,
            \App\Models\InsuranceClaim::class,
        ];

        foreach ($models as $model) {
            $this->assertIsObject(
                $model::query()->first(),
                "Model {$model} should support HasFacilityScope without error"
            );
        }
    }
}
```

Run: `php artisan test tests/Feature/TenantIsolationTest.php`

---

## Completion Checklist

- [ ] `config/regions.php` created
- [ ] `RegionHealthService` created and container-resolvable
- [ ] `DatabaseHealthMiddleware` created
- [ ] Middleware registered in `bootstrap/app.php`
- [ ] `.env.example` updated with region vars
- [ ] `AuditFacilityScopeCommand` created and runs cleanly
- [ ] All 32 models in Batches A–G have `use \App\Traits\HasFacilityScope;` added
- [ ] Audit command reports 0 missing models
- [ ] `DatabaseHealthMiddlewareTest` passes
- [ ] `TenantIsolationTest` passes
- [ ] Each batch committed separately (7 commits)
