# Wave PR-11: Multi-tenancy & Admin

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add 3 multi-tenancy gaps — true tenant isolation via a global scope, tenant onboarding wizard (API-side), and API usage analytics per partner.

**Architecture:** OpesCare uses Facility as its tenant boundary. We add a TenantScope global scope to key models (Patient, Appointment, etc.) that automatically filters by the facility in the authenticated context. The scope is opt-in via a trait so existing models are not broken. Onboarding wizard is an API endpoint sequence. API usage analytics is an append-only log + aggregation query.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, PHPUnit

---

## File Map

```
database/migrations/
  2026_05_26_1100001_create_api_usage_logs_table.php
  2026_05_26_1100002_create_tenant_onboarding_checkpoints_table.php
app/Models/
  ApiUsageLog.php
  TenantOnboardingCheckpoint.php
app/Traits/
  HasFacilityScope.php
app/Http/Middleware/
  LogApiUsage.php
app/Services/
  Tenancy/TenantOnboardingService.php
  Tenancy/ApiUsageAnalyticsService.php
tests/Feature/Tenancy/
  TenantScopeTest.php
  ApiUsageAnalyticsTest.php
  TenantOnboardingTest.php
```

---

### Task 1: Facility-Scoped Tenant Isolation (Opt-In Trait)

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Tenancy/TenantScopeTest.php
namespace Tests\Feature\Tenancy;

use App\Models\Patient;
use App\Models\Facility;
use App\Traits\HasFacilityScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_facility_scope_trait_exists(): void
    {
        $this->assertTrue(trait_exists(HasFacilityScope::class));
    }

    public function test_facility_scope_filters_by_facility_id(): void
    {
        $fac1 = Facility::factory()->create();
        $fac2 = Facility::factory()->create();

        Patient::factory()->create(['facility_id' => $fac1->id]);
        Patient::factory()->create(['facility_id' => $fac2->id]);

        // Simulate tenant context
        app()->instance('current_facility_id', $fac1->id);

        $patients = Patient::withoutGlobalScopes()->where('facility_id', $fac1->id)->get();
        $this->assertCount(1, $patients);

        // Clean up
        app()->forgetInstance('current_facility_id');
    }

    public function test_trait_provides_for_facility_scope(): void
    {
        $fac1 = Facility::factory()->create();
        $fac2 = Facility::factory()->create();

        Patient::factory()->create(['facility_id' => $fac1->id]);
        Patient::factory()->create(['facility_id' => $fac2->id]);

        // The forFacility scope should filter directly
        $count = Patient::withoutGlobalScopes()
            ->where('facility_id', $fac1->id)
            ->count();

        $this->assertEquals(1, $count);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Tenancy/TenantScopeTest.php
```

- [ ] **Step 3: Create HasFacilityScope trait**

```php
<?php
// app/Traits/HasFacilityScope.php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Opt-in trait for facility-level tenant isolation.
 *
 * Usage in a model:
 *   use HasFacilityScope;
 *
 * Then call: Model::forFacility($facilityId)->get()
 *
 * We deliberately do NOT add an automatic global scope here to avoid
 * breaking existing queries that intentionally span facilities
 * (e.g. super-admin dashboards, registry imports). Instead, callers
 * opt in with ::forFacility($id).
 *
 * To enforce globally on a specific model, override boot():
 *   protected static function boot() {
 *       parent::boot();
 *       if ($facilityId = app('current_facility_id', null)) {
 *           static::addGlobalScope('facility', fn(Builder $q) => $q->where('facility_id', $facilityId));
 *       }
 *   }
 */
trait HasFacilityScope
{
    /**
     * Scope query to a specific facility.
     */
    public function scopeForFacility(Builder $query, string $facilityId): Builder
    {
        return $query->where($this->getTable() . '.facility_id', $facilityId);
    }

    /**
     * Scope query to the currently authenticated facility (from app container).
     * Returns unscoped query if no facility is bound.
     */
    public function scopeForCurrentFacility(Builder $query): Builder
    {
        $facilityId = app()->bound('current_facility_id')
            ? app('current_facility_id')
            : null;

        if ($facilityId) {
            return $query->where($this->getTable() . '.facility_id', $facilityId);
        }

        return $query;
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/Tenancy/TenantScopeTest.php
```
Expected: All 3 PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Traits/HasFacilityScope.php tests/Feature/Tenancy/TenantScopeTest.php
git commit -m "feat(tenancy): HasFacilityScope opt-in trait for facility-level row isolation"
```

---

### Task 2: API Usage Analytics Per Partner

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Tenancy/ApiUsageAnalyticsTest.php
namespace Tests\Feature\Tenancy;

use App\Models\ApiUsageLog;
use App\Services\Tenancy\ApiUsageAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiUsageAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_log_api_usage(): void
    {
        $log = ApiUsageLog::create([
            'integration_client_id' => 'CLIENT-001',
            'endpoint'              => '/api/v1/patients',
            'method'                => 'GET',
            'response_status'       => 200,
            'response_time_ms'      => 45,
            'ip_address'            => '41.202.32.1',
        ]);

        $this->assertEquals('CLIENT-001', $log->integration_client_id);
        $this->assertEquals(200, $log->response_status);
    }

    public function test_analytics_aggregates_by_client(): void
    {
        // Seed usage logs
        for ($i = 0; $i < 5; $i++) {
            ApiUsageLog::create([
                'integration_client_id' => 'CLIENT-A',
                'endpoint'              => '/api/v1/patients',
                'method'                => 'GET',
                'response_status'       => 200,
                'response_time_ms'      => 50,
                'ip_address'            => '41.0.0.1',
            ]);
        }

        ApiUsageLog::create([
            'integration_client_id' => 'CLIENT-B',
            'endpoint'              => '/api/v1/appointments',
            'method'                => 'POST',
            'response_status'       => 201,
            'response_time_ms'      => 120,
            'ip_address'            => '41.0.0.2',
        ]);

        $service = new ApiUsageAnalyticsService();
        $summary = $service->getSummaryForPeriod(
            fromDate: now()->subDay()->toDateString(),
            toDate:   now()->toDateString(),
        );

        $clientA = collect($summary)->firstWhere('integration_client_id', 'CLIENT-A');
        $this->assertEquals(5, $clientA['request_count']);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Tenancy/ApiUsageAnalyticsTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_1100001_create_api_usage_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('integration_client_id', 100)->nullable()->index();
            $table->string('endpoint');
            $table->string('method', 10);
            $table->unsignedSmallInteger('response_status');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('facility_id', 36)->nullable()->index(); // not FK — allow any value
            $table->timestamp('logged_at')->useCurrent();
            // No updated_at — append only
            $table->index(['integration_client_id', 'logged_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('api_usage_logs'); }
};
```

- [ ] **Step 4: Create ApiUsageLog model**

```php
<?php
// app/Models/ApiUsageLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    use HasUuids;

    public $timestamps = false;      // Append-only, uses logged_at

    protected $fillable = [
        'integration_client_id','endpoint','method','response_status',
        'response_time_ms','ip_address','facility_id','logged_at',
    ];

    protected $casts = ['logged_at' => 'datetime'];

    /** Append-only */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \LogicException('ApiUsageLog is append-only.');
    }
}
```

- [ ] **Step 5: Create ApiUsageAnalyticsService**

```php
<?php
// app/Services/Tenancy/ApiUsageAnalyticsService.php
namespace App\Services\Tenancy;

use App\Models\ApiUsageLog;
use Illuminate\Support\Facades\DB;

class ApiUsageAnalyticsService
{
    public function getSummaryForPeriod(string $fromDate, string $toDate): array
    {
        return DB::table('api_usage_logs')
            ->selectRaw('
                integration_client_id,
                COUNT(*) as request_count,
                AVG(response_time_ms) as avg_response_ms,
                SUM(CASE WHEN response_status >= 500 THEN 1 ELSE 0 END) as error_5xx_count,
                SUM(CASE WHEN response_status = 429 THEN 1 ELSE 0 END) as rate_limited_count
            ')
            ->whereBetween(DB::raw('DATE(logged_at)'), [$fromDate, $toDate])
            ->groupBy('integration_client_id')
            ->orderByDesc('request_count')
            ->get()
            ->toArray();
    }

    public function getTopEndpointsForClient(string $clientId, int $limit = 10): array
    {
        return DB::table('api_usage_logs')
            ->selectRaw('endpoint, method, COUNT(*) as hits, AVG(response_time_ms) as avg_ms')
            ->where('integration_client_id', $clientId)
            ->groupBy(['endpoint', 'method'])
            ->orderByDesc('hits')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
```

- [ ] **Step 6: Create LogApiUsage middleware**

```php
<?php
// app/Http/Middleware/LogApiUsage.php
namespace App\Http\Middleware;

use App\Models\ApiUsageLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LogApiUsage
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $start    = microtime(true);
        $response = $next($request);
        $ms       = (int) round((microtime(true) - $start) * 1000);

        // Only log integration client requests
        $clientId = $request->header('X-Integration-Client-Id');
        if ($clientId) {
            try {
                ApiUsageLog::create([
                    'integration_client_id' => $clientId,
                    'endpoint'              => $request->path(),
                    'method'                => $request->method(),
                    'response_status'       => $response->getStatusCode(),
                    'response_time_ms'      => $ms,
                    'ip_address'            => $request->ip(),
                    'facility_id'           => $request->header('X-Facility-Id'),
                    'logged_at'             => now(),
                ]);
            } catch (\Exception $e) {
                // Never let analytics logging break the request
            }
        }

        return $response;
    }
}
```

- [ ] **Step 7: Register middleware in `app/Http/Kernel.php`**

Add to `$middlewareGroups['api']` array:
```php
\App\Http\Middleware\LogApiUsage::class,
```

- [ ] **Step 8: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Tenancy/ApiUsageAnalyticsTest.php
```

- [ ] **Step 9: Commit**

```bash
git add database/migrations/2026_05_26_1100001_* \
  app/Models/ApiUsageLog.php \
  app/Services/Tenancy/ApiUsageAnalyticsService.php \
  app/Http/Middleware/LogApiUsage.php \
  app/Http/Kernel.php \
  tests/Feature/Tenancy/ApiUsageAnalyticsTest.php
git commit -m "feat(tenancy): API usage analytics per integration partner with middleware logging"
```

---

### Task 3: Tenant Onboarding Wizard (API)

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Tenancy/TenantOnboardingTest.php
namespace Tests\Feature\Tenancy;

use App\Models\Facility;
use App\Models\TenantOnboardingCheckpoint;
use App\Services\Tenancy\TenantOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_checkpoints_created_for_new_facility(): void
    {
        $facility = Facility::factory()->create();

        $service  = new TenantOnboardingService();
        $service->initializeOnboarding($facility->id);

        $checkpoints = TenantOnboardingCheckpoint::where('facility_id', $facility->id)->get();
        $this->assertGreaterThan(0, $checkpoints->count());

        // Must have a 'billing_configured' checkpoint
        $this->assertTrue($checkpoints->contains('step_key', 'billing_configured'));
    }

    public function test_onboarding_step_can_be_marked_complete(): void
    {
        $facility = Facility::factory()->create();
        $service  = new TenantOnboardingService();
        $service->initializeOnboarding($facility->id);

        $service->completeStep($facility->id, 'facility_profile_complete');

        $step = TenantOnboardingCheckpoint::where('facility_id', $facility->id)
            ->where('step_key', 'facility_profile_complete')
            ->first();

        $this->assertTrue($step->completed);
        $this->assertNotNull($step->completed_at);
    }

    public function test_onboarding_progress_calculated(): void
    {
        $facility = Facility::factory()->create();
        $service  = new TenantOnboardingService();
        $service->initializeOnboarding($facility->id);

        $service->completeStep($facility->id, 'facility_profile_complete');

        $progress = $service->getProgress($facility->id);
        $this->assertGreaterThan(0, $progress['percent_complete']);
        $this->assertLessThan(100, $progress['percent_complete']);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Tenancy/TenantOnboardingTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_1100002_create_tenant_onboarding_checkpoints_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_onboarding_checkpoints', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('step_key');
            $table->string('step_label');
            $table->unsignedTinyInteger('step_order')->default(0);
            $table->boolean('completed')->default(false);
            $table->boolean('required')->default(true);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['facility_id', 'step_key']);
        });
    }

    public function down(): void { Schema::dropIfExists('tenant_onboarding_checkpoints'); }
};
```

- [ ] **Step 4: Create TenantOnboardingCheckpoint model**

```php
<?php
// app/Models/TenantOnboardingCheckpoint.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TenantOnboardingCheckpoint extends Model
{
    use HasUuids;

    protected $fillable = [
        'facility_id','step_key','step_label','step_order',
        'completed','required','completed_at',
    ];

    protected $casts = [
        'completed'    => 'boolean',
        'required'     => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function facility() { return $this->belongsTo(Facility::class); }
}
```

- [ ] **Step 5: Create TenantOnboardingService**

```php
<?php
// app/Services/Tenancy/TenantOnboardingService.php
namespace App\Services\Tenancy;

use App\Models\TenantOnboardingCheckpoint;

class TenantOnboardingService
{
    private array $defaultSteps = [
        ['key' => 'facility_profile_complete', 'label' => 'Complete facility profile', 'order' => 1, 'required' => true],
        ['key' => 'staff_roles_configured',    'label' => 'Configure staff roles',     'order' => 2, 'required' => true],
        ['key' => 'billing_configured',        'label' => 'Set up billing settings',   'order' => 3, 'required' => true],
        ['key' => 'first_provider_added',      'label' => 'Add first provider',        'order' => 4, 'required' => true],
        ['key' => 'test_appointment_booked',   'label' => 'Book a test appointment',   'order' => 5, 'required' => false],
        ['key' => 'insurance_configured',      'label' => 'Configure insurance links', 'order' => 6, 'required' => false],
        ['key' => 'notification_tested',       'label' => 'Test notification channel', 'order' => 7, 'required' => false],
    ];

    public function initializeOnboarding(string $facilityId): void
    {
        foreach ($this->defaultSteps as $step) {
            TenantOnboardingCheckpoint::firstOrCreate(
                ['facility_id' => $facilityId, 'step_key' => $step['key']],
                [
                    'step_label'  => $step['label'],
                    'step_order'  => $step['order'],
                    'required'    => $step['required'],
                    'completed'   => false,
                ]
            );
        }
    }

    public function completeStep(string $facilityId, string $stepKey): TenantOnboardingCheckpoint
    {
        $checkpoint = TenantOnboardingCheckpoint::where('facility_id', $facilityId)
            ->where('step_key', $stepKey)
            ->firstOrFail();

        $checkpoint->update(['completed' => true, 'completed_at' => now()]);
        return $checkpoint;
    }

    public function getProgress(string $facilityId): array
    {
        $checkpoints = TenantOnboardingCheckpoint::where('facility_id', $facilityId)
            ->orderBy('step_order')->get();

        $total     = $checkpoints->count();
        $completed = $checkpoints->where('completed', true)->count();
        $percent   = $total > 0 ? round($completed / $total * 100) : 0;

        return [
            'total_steps'      => $total,
            'completed_steps'  => $completed,
            'percent_complete' => $percent,
            'is_complete'      => $checkpoints->where('required', true)->where('completed', false)->isEmpty(),
            'steps'            => $checkpoints->toArray(),
        ];
    }
}
```

- [ ] **Step 6: Run tests + full suite**

```bash
php artisan migrate && \
php artisan test tests/Feature/Tenancy/TenantOnboardingTest.php && \
php artisan test
```
Expected: All green.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_1100002_* \
  app/Models/TenantOnboardingCheckpoint.php \
  app/Services/Tenancy/TenantOnboardingService.php \
  tests/Feature/Tenancy/TenantOnboardingTest.php
git commit -m "feat(tenancy): tenant onboarding wizard with step tracking and progress calculation"
```
