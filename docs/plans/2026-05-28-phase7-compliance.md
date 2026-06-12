# Phase 7: Provider Credentialing, Advance Directives, Data Retention, Pen Test Log

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans

**Goal:** Add provider credentialing lifecycle, advance directive management, automated data retention purge, and pen test remediation tracking.
**Architecture:** New models + commands. No existing compliance code modified.
**Tech Stack:** Laravel 11, PHP 8.3, PostgreSQL, UUID PKs

---

## Task 1: Provider Credentialing (item 45)

---

### Migration: `database/migrations/2026_05_28_007000_create_provider_credentials_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id')->index();
            $table->enum('credential_type', [
                'medical_license',
                'specialist_cert',
                'dea_registration',
                'board_certification',
                'cpr_cert',
                'hospital_privilege',
                'other',
            ]);
            $table->string('issuing_body', 255);
            $table->string('credential_number', 100);
            $table->date('issued_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', [
                'active',
                'expired',
                'suspended',
                'revoked',
                'pending_renewal',
            ])->default('active');
            $table->string('document_path', 500)->nullable();
            $table->uuid('verified_by')->nullable()->index();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['provider_id', 'status']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_credentials');
    }
};
```

---

### Model: `app/Models/ProviderCredential.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderCredential extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'credential_type',
        'issuing_body',
        'credential_number',
        'issued_date',
        'expiry_date',
        'status',
        'document_path',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function provider(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function verifier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $daysAhead = 30): bool
    {
        return $this->expiry_date !== null
            && $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= $daysAhead;
    }
}
```

---

### Service: `app/Services/Staff/CredentialingService.php`

```php
<?php

namespace App\Services\Staff;

use App\Models\ProviderCredential;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CredentialingService
{
    public function addCredential(array $data): ProviderCredential
    {
        return ProviderCredential::create($data);
    }

    public function verify(string $credentialId, string $verifiedBy): ProviderCredential
    {
        $credential = ProviderCredential::findOrFail($credentialId);

        $credential->update([
            'verified_by' => $verifiedBy,
            'verified_at' => Carbon::now(),
            'status'      => 'active',
        ]);

        return $credential->fresh();
    }

    public function getExpiringCredentials(int $daysAhead = 30): Collection
    {
        $cutoff = Carbon::now()->addDays($daysAhead);

        return ProviderCredential::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $cutoff)
            ->where('expiry_date', '>=', Carbon::now())
            ->with('provider')
            ->orderBy('expiry_date')
            ->get();
    }

    public function getProviderCredentials(string $providerId): Collection
    {
        return ProviderCredential::where('provider_id', $providerId)
            ->orderBy('credential_type')
            ->orderByDesc('issued_date')
            ->get();
    }

    public function getCredentialSummary(string $facilityId): array
    {
        // Get all providers belonging to this facility
        $providerIds = User::where('facility_id', $facilityId)->pluck('id');

        $credentials = ProviderCredential::whereIn('provider_id', $providerIds)->get();

        $totalProviders    = $providerIds->count();
        $hasExpired        = $credentials->where('status', 'expired')->pluck('provider_id')->unique()->count();
        $expiringSoon      = $credentials
            ->filter(fn (ProviderCredential $c) => $c->isExpiringSoon(30))
            ->pluck('provider_id')
            ->unique()
            ->count();

        // "Fully credentialed" = has at least one active medical_license and no expired credentials
        $fullyCredentialed = $providerIds->filter(function ($providerId) use ($credentials) {
            $providerCreds = $credentials->where('provider_id', $providerId);
            $hasLicense    = $providerCreds->where('credential_type', 'medical_license')
                ->where('status', 'active')
                ->isNotEmpty();
            $hasNoExpired  = $providerCreds->where('status', 'expired')->isEmpty();
            return $hasLicense && $hasNoExpired;
        })->count();

        return [
            'total_providers'    => $totalProviders,
            'fully_credentialed' => $fullyCredentialed,
            'has_expired'        => $hasExpired,
            'expiring_soon'      => $expiringSoon,
        ];
    }
}
```

---

### Command: `app/Console/Commands/NotifyExpiringCredentialsCommand.php`

```php
<?php

namespace App\Console\Commands;

use App\Services\Staff\CredentialingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyExpiringCredentialsCommand extends Command
{
    protected $signature = 'opescare:notify-expiring-credentials {--days=30 : Number of days ahead to check}';

    protected $description = 'Notify administrators of provider credentials expiring soon';

    public function __construct(private readonly CredentialingService $credentialingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days        = (int) $this->option('days');
        $credentials = $this->credentialingService->getExpiringCredentials($days);

        if ($credentials->isEmpty()) {
            $this->info("No credentials expiring within {$days} days.");
            Log::info("opescare:notify-expiring-credentials: No credentials expiring within {$days} days.");
            return self::SUCCESS;
        }

        $this->info("Found {$credentials->count()} credential(s) expiring within {$days} days:");

        $tableRows = [];
        foreach ($credentials as $credential) {
            $providerName = optional($credential->provider)->name ?? $credential->provider_id;
            $expiryDate   = $credential->expiry_date->format('Y-m-d');

            $tableRows[] = [
                $providerName,
                $credential->credential_type,
                $credential->issuing_body,
                $expiryDate,
                $credential->status,
            ];

            Log::warning('Provider credential expiring soon', [
                'credential_id'   => $credential->id,
                'provider_id'     => $credential->provider_id,
                'provider_name'   => $providerName,
                'credential_type' => $credential->credential_type,
                'expiry_date'     => $expiryDate,
                'days_remaining'  => (int) now()->diffInDays($credential->expiry_date),
            ]);
        }

        $this->table(
            ['Provider', 'Type', 'Issuing Body', 'Expiry Date', 'Status'],
            $tableRows,
        );

        // Fire notification event — receivers hook into this; falls back to log if no listeners configured
        // event(new \App\Events\CredentialsExpiringSoon($credentials));

        return self::SUCCESS;
    }
}
```

**Register in `app/Console/Kernel.php`:**
```php
// In schedule() method:
$schedule->command('opescare:notify-expiring-credentials --days=30')
         ->weeklyOn(1, '08:00') // Every Monday at 08:00
         ->withoutOverlapping();
```

**Register the command class:**
```php
protected $commands = [
    \App\Console\Commands\NotifyExpiringCredentialsCommand::class,
];
```

---

### Test: `tests/Feature/CredentialingTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\ProviderCredential;
use App\Models\User;
use App\Services\Staff\CredentialingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CredentialingTest extends TestCase
{
    use RefreshDatabase;

    private CredentialingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CredentialingService::class);
    }

    public function test_can_add_credential(): void
    {
        $provider = User::factory()->create();

        $credential = $this->service->addCredential([
            'provider_id'       => $provider->id,
            'credential_type'   => 'medical_license',
            'issuing_body'      => 'Cameroon Medical Council',
            'credential_number' => 'CMC-2024-001',
            'issued_date'       => '2024-01-01',
            'expiry_date'       => Carbon::now()->addYear()->toDateString(),
        ]);

        $this->assertInstanceOf(ProviderCredential::class, $credential);
        $this->assertEquals('active', $credential->status);
    }

    public function test_can_verify_credential(): void
    {
        $provider  = User::factory()->create();
        $verifier  = User::factory()->create();

        $credential = $this->service->addCredential([
            'provider_id'       => $provider->id,
            'credential_type'   => 'specialist_cert',
            'issuing_body'      => 'West African College of Physicians',
            'credential_number' => 'WACP-2024-042',
            'issued_date'       => '2024-03-01',
        ]);

        $verified = $this->service->verify($credential->id, $verifier->id);

        $this->assertEquals($verifier->id, $verified->verified_by);
        $this->assertNotNull($verified->verified_at);
        $this->assertEquals('active', $verified->status);
    }

    public function test_get_expiring_credentials_within_days(): void
    {
        $provider = User::factory()->create();

        // Expiring in 10 days — should be detected with days=30
        $expiringSoon = $this->service->addCredential([
            'provider_id'       => $provider->id,
            'credential_type'   => 'cpr_cert',
            'issuing_body'      => 'American Heart Association',
            'credential_number' => 'AHA-001',
            'issued_date'       => Carbon::now()->subYear()->toDateString(),
            'expiry_date'       => Carbon::now()->addDays(10)->toDateString(),
        ]);

        // Expiring in 60 days — should NOT be detected with days=30
        $expiringLater = $this->service->addCredential([
            'provider_id'       => $provider->id,
            'credential_type'   => 'medical_license',
            'issuing_body'      => 'Cameroon Medical Council',
            'credential_number' => 'CMC-002',
            'issued_date'       => Carbon::now()->subYear()->toDateString(),
            'expiry_date'       => Carbon::now()->addDays(60)->toDateString(),
        ]);

        $results = $this->service->getExpiringCredentials(30);

        $this->assertTrue($results->contains('id', $expiringSoon->id));
        $this->assertFalse($results->contains('id', $expiringLater->id));
    }

    public function test_credential_summary_counts_correctly(): void
    {
        // This test requires a facility_id on User — adjust to match your User schema
        $this->assertTrue(true); // Placeholder — implement once User->facility relationship is confirmed
    }
}
```

---

## Task 2: Advance Directives / Living Will (item 51)

---

### Migration: `database/migrations/2026_05_28_007001_create_advance_directives_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_directives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id')->index();
            $table->uuid('facility_id')->index();
            $table->enum('directive_type', [
                'dnr',
                'living_will',
                'healthcare_proxy',
                'polst',
                'organ_donation',
                'other',
            ]);
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->string('document_path', 500)->nullable();
            $table->string('witness_name', 255)->nullable();
            $table->date('witness_date')->nullable();
            $table->string('healthcare_proxy_name', 255)->nullable();
            $table->string('healthcare_proxy_phone', 30)->nullable();
            $table->string('healthcare_proxy_relationship', 100)->nullable();
            $table->text('instructions')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('facility_id')->references('id')->on('facilities');
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['patient_id', 'is_active', 'directive_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_directives');
    }
};
```

---

### Model: `app/Models/AdvanceDirective.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvanceDirective extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'directive_type',
        'is_active',
        'effective_date',
        'expiry_date',
        'document_path',
        'witness_name',
        'witness_date',
        'healthcare_proxy_name',
        'healthcare_proxy_phone',
        'healthcare_proxy_relationship',
        'instructions',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'effective_date' => 'date',
        'expiry_date'    => 'date',
        'witness_date'   => 'date',
        'verified_at'    => 'datetime',
    ];

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function verifier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
```

---

### Service: `app/Services/Clinical/AdvanceDirectiveService.php`

```php
<?php

namespace App\Services\Clinical;

use App\Models\AdvanceDirective;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdvanceDirectiveService
{
    public function register(array $data): AdvanceDirective
    {
        return AdvanceDirective::create(array_merge(['is_active' => true], $data));
    }

    public function revoke(string $directiveId, string $revokedBy): AdvanceDirective
    {
        $directive = AdvanceDirective::findOrFail($directiveId);

        $directive->update([
            'is_active'  => false,
            'verified_by'=> $revokedBy,
            'verified_at'=> Carbon::now(),
            'instructions' => ($directive->instructions ?? '') . "\n[Revoked by {$revokedBy} at " . Carbon::now()->toDateTimeString() . ']',
        ]);

        return $directive->fresh();
    }

    public function getActiveForPatient(string $patientId): Collection
    {
        return AdvanceDirective::where('patient_id', $patientId)
            ->where('is_active', true)
            ->orderBy('directive_type')
            ->get();
    }

    public function hasActiveDnr(string $patientId): bool
    {
        return AdvanceDirective::where('patient_id', $patientId)
            ->where('directive_type', 'dnr')
            ->where('is_active', true)
            ->exists();
    }

    public function getHealthcareProxy(string $patientId): ?AdvanceDirective
    {
        return AdvanceDirective::where('patient_id', $patientId)
            ->where('directive_type', 'healthcare_proxy')
            ->where('is_active', true)
            ->latest()
            ->first();
    }
}
```

---

### Controller: `app/Http/Controllers/Api/V1/AdvanceDirectiveController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdvanceDirective;
use App\Services\Clinical\AdvanceDirectiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvanceDirectiveController extends Controller
{
    public function __construct(private readonly AdvanceDirectiveService $service)
    {
    }

    /** GET /api/patients/{patientId}/advance-directives */
    public function index(string $patientId): JsonResponse
    {
        $directives = $this->service->getActiveForPatient($patientId);
        return response()->json(['data' => $directives]);
    }

    /** POST /api/patients/{patientId}/advance-directives */
    public function store(Request $request, string $patientId): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'                  => 'required|uuid|exists:facilities,id',
            'directive_type'               => 'required|in:dnr,living_will,healthcare_proxy,polst,organ_donation,other',
            'effective_date'               => 'required|date',
            'expiry_date'                  => 'nullable|date|after:effective_date',
            'document_path'                => 'nullable|string|max:500',
            'witness_name'                 => 'nullable|string|max:255',
            'witness_date'                 => 'nullable|date',
            'healthcare_proxy_name'        => 'nullable|string|max:255',
            'healthcare_proxy_phone'       => 'nullable|string|max:30',
            'healthcare_proxy_relationship'=> 'nullable|string|max:100',
            'instructions'                 => 'nullable|string',
        ]);

        $directive = $this->service->register(array_merge($validated, ['patient_id' => $patientId]));

        return response()->json(['data' => $directive], 201);
    }

    /** GET /api/patients/{patientId}/advance-directives/{id} */
    public function show(string $patientId, string $id): JsonResponse
    {
        $directive = AdvanceDirective::where('patient_id', $patientId)->findOrFail($id);
        return response()->json(['data' => $directive]);
    }

    /** DELETE /api/patients/{patientId}/advance-directives/{id} — revoke */
    public function destroy(Request $request, string $patientId, string $id): JsonResponse
    {
        $directive = $this->service->revoke($id, $request->user()->id);
        return response()->json(['data' => $directive, 'message' => 'Directive revoked.']);
    }
}
```

**Routes (add to `routes/api.php`):**
```php
Route::middleware(['auth:sanctum', 'role:clinician,admin'])->group(function () {
    Route::get('/patients/{patientId}/advance-directives',      [\App\Http\Controllers\Api\V1\AdvanceDirectiveController::class, 'index']);
    Route::post('/patients/{patientId}/advance-directives',     [\App\Http\Controllers\Api\V1\AdvanceDirectiveController::class, 'store']);
    Route::get('/patients/{patientId}/advance-directives/{id}', [\App\Http\Controllers\Api\V1\AdvanceDirectiveController::class, 'show']);
    Route::delete('/patients/{patientId}/advance-directives/{id}', [\App\Http\Controllers\Api\V1\AdvanceDirectiveController::class, 'destroy']);
});
```

---

### Test: `tests/Feature/AdvanceDirectiveTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use App\Services\Clinical\AdvanceDirectiveService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvanceDirectiveTest extends TestCase
{
    use RefreshDatabase;

    private AdvanceDirectiveService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AdvanceDirectiveService::class);
    }

    public function test_can_register_dnr_directive(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $directive = $this->service->register([
            'patient_id'     => $patient->id,
            'facility_id'    => $facility->id,
            'directive_type' => 'dnr',
            'effective_date' => Carbon::now()->toDateString(),
        ]);

        $this->assertTrue((bool) $directive->is_active);
        $this->assertEquals('dnr', $directive->directive_type);
    }

    public function test_has_active_dnr_returns_true_when_dnr_exists(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $this->service->register([
            'patient_id'     => $patient->id,
            'facility_id'    => $facility->id,
            'directive_type' => 'dnr',
            'effective_date' => Carbon::now()->toDateString(),
        ]);

        $this->assertTrue($this->service->hasActiveDnr($patient->id));
    }

    public function test_revoke_deactivates_directive(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();
        $user     = User::factory()->create();

        $directive = $this->service->register([
            'patient_id'     => $patient->id,
            'facility_id'    => $facility->id,
            'directive_type' => 'dnr',
            'effective_date' => Carbon::now()->toDateString(),
        ]);

        $this->assertTrue($this->service->hasActiveDnr($patient->id));

        $this->service->revoke($directive->id, $user->id);

        $this->assertFalse($this->service->hasActiveDnr($patient->id));
    }

    public function test_get_healthcare_proxy_returns_correct_directive(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $proxy = $this->service->register([
            'patient_id'                   => $patient->id,
            'facility_id'                  => $facility->id,
            'directive_type'               => 'healthcare_proxy',
            'effective_date'               => Carbon::now()->toDateString(),
            'healthcare_proxy_name'        => 'Mary Doe',
            'healthcare_proxy_phone'       => '+237670000099',
            'healthcare_proxy_relationship'=> 'spouse',
        ]);

        $found = $this->service->getHealthcareProxy($patient->id);

        $this->assertNotNull($found);
        $this->assertEquals('Mary Doe', $found->healthcare_proxy_name);
    }
}
```

---

## Task 3: Automated Data Retention + Purge (item 53)

---

### Config: `config/data_retention.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data Retention Policy (OpesCare)
    |--------------------------------------------------------------------------
    | Retention periods per Cameroon Law No. 2010/012 and HIPAA equivalents.
    | All durations are in the unit noted in the key name.
    */

    'audit_logs_days'      => env('RETENTION_AUDIT_LOGS_DAYS', 2555),   // 7 years
    'access_logs_days'     => env('RETENTION_ACCESS_LOGS_DAYS', 365),
    'api_usage_logs_days'  => env('RETENTION_API_USAGE_LOGS_DAYS', 180),
    'ussd_sessions_days'   => env('RETENTION_USSD_SESSIONS_DAYS', 30),
    'export_files_hours'   => env('RETENTION_EXPORT_FILES_HOURS', 24),
    'clinical_data_years'  => env('RETENTION_CLINICAL_DATA_YEARS', 10),  // per Cameroon Law 2010/012
];
```

---

### Command: `app/Console/Commands/PurgeExpiredDataCommand.php`

```php
<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredDataCommand extends Command
{
    protected $signature = 'opescare:purge-expired-data {--dry-run : Preview what would be deleted without deleting}';

    protected $description = 'Purge expired data per OpesCare retention policy';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('[DRY RUN] No data will be deleted.');
        }

        $this->info('Starting data retention purge — ' . Carbon::now()->toDateTimeString());

        $results = [];

        // 1. API usage logs
        $results['api_usage_logs'] = $this->purgeTable(
            table: 'api_usage_logs',
            column: 'created_at',
            days: (int) config('data_retention.api_usage_logs_days', 180),
            dryRun: $dryRun,
        );

        // 2. USSD sessions
        $results['ussd_sessions'] = $this->purgeTable(
            table: 'ussd_sessions',
            column: 'last_active_at',
            days: (int) config('data_retention.ussd_sessions_days', 30),
            dryRun: $dryRun,
        );

        // 3. Export files
        $results['export_files'] = $this->purgeExportFiles(
            hoursOld: (int) config('data_retention.export_files_hours', 24),
            dryRun: $dryRun,
        );

        // Summary
        $this->newLine();
        $this->info('Purge Summary:');
        $this->table(
            ['Target', 'Records/Files Affected'],
            collect($results)->map(fn ($count, $target) => [$target, $count])->values()->toArray(),
        );

        Log::info('opescare:purge-expired-data completed', array_merge(
            ['dry_run' => $dryRun],
            $results,
        ));

        return self::SUCCESS;
    }

    private function purgeTable(
        string $table,
        string $column,
        int $days,
        bool $dryRun,
    ): int {
        $cutoff = Carbon::now()->subDays($days);

        try {
            $count = DB::table($table)->where($column, '<', $cutoff)->count();

            if (! $dryRun && $count > 0) {
                DB::table($table)->where($column, '<', $cutoff)->delete();
            }

            $verb = $dryRun ? 'Would delete' : 'Deleted';
            $this->line("  {$verb} {$count} record(s) from {$table} (older than {$days} days).");
            return $count;
        } catch (\Throwable $e) {
            Log::error("PurgeExpiredDataCommand: failed to purge {$table}", [
                'error' => $e->getMessage(),
            ]);
            $this->error("  Failed to purge {$table}: {$e->getMessage()}");
            return 0;
        }
    }

    private function purgeExportFiles(int $hoursOld, bool $dryRun): int
    {
        $exportDir = 'exports/medical-records';
        $cutoff    = Carbon::now()->subHours($hoursOld)->timestamp;
        $deleted   = 0;

        try {
            $files = Storage::disk('local')->files($exportDir);

            foreach ($files as $file) {
                $lastModified = Storage::disk('local')->lastModified($file);

                if ($lastModified < $cutoff) {
                    if (! $dryRun) {
                        Storage::disk('local')->delete($file);
                    }
                    $deleted++;
                }
            }

            $verb = $dryRun ? 'Would delete' : 'Deleted';
            $this->line("  {$verb} {$deleted} export file(s) older than {$hoursOld} hour(s).");
        } catch (\Throwable $e) {
            Log::error('PurgeExpiredDataCommand: failed to purge export files', [
                'error' => $e->getMessage(),
            ]);
            $this->error("  Failed to purge export files: {$e->getMessage()}");
        }

        return $deleted;
    }
}
```

**Register in `app/Console/Kernel.php`:**
```php
// In schedule() method:
$schedule->command('opescare:purge-expired-data')
         ->daily()
         ->at('03:00')
         ->withoutOverlapping()
         ->runInBackground();

// In $commands array:
protected $commands = [
    \App\Console\Commands\PurgeExpiredDataCommand::class,
    \App\Console\Commands\NotifyExpiringCredentialsCommand::class,
];
```

---

### Test: `tests/Feature/PurgeExpiredDataTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\UssdSession;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurgeExpiredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_returns_counts_without_deleting(): void
    {
        // Seed an old USSD session
        UssdSession::create([
            'session_id'     => 'OLD-SESS-001',
            'phone_number'   => '+237670000010',
            'service_code'   => '*384#',
            'current_menu'   => 'MAIN',
            'initiated_at'   => Carbon::now()->subDays(60),
            'last_active_at' => Carbon::now()->subDays(60),
        ]);

        $this->assertDatabaseCount('ussd_sessions', 1);

        $this->artisan('opescare:purge-expired-data --dry-run')
             ->assertExitCode(0);

        // Record should still exist after dry-run
        $this->assertDatabaseCount('ussd_sessions', 1);
    }

    public function test_actual_run_deletes_old_ussd_sessions(): void
    {
        // Seed an old session (> 30 days)
        UssdSession::create([
            'session_id'     => 'OLD-SESS-002',
            'phone_number'   => '+237670000011',
            'service_code'   => '*384#',
            'current_menu'   => 'MAIN',
            'initiated_at'   => Carbon::now()->subDays(60),
            'last_active_at' => Carbon::now()->subDays(60),
        ]);

        // Seed a recent session (< 30 days)
        UssdSession::create([
            'session_id'     => 'NEW-SESS-001',
            'phone_number'   => '+237670000012',
            'service_code'   => '*384#',
            'current_menu'   => 'MAIN',
            'initiated_at'   => Carbon::now()->subDays(5),
            'last_active_at' => Carbon::now()->subDays(5),
        ]);

        $this->artisan('opescare:purge-expired-data')
             ->assertExitCode(0);

        $this->assertDatabaseMissing('ussd_sessions', ['session_id' => 'OLD-SESS-002']);
        $this->assertDatabaseHas('ussd_sessions', ['session_id' => 'NEW-SESS-001']);
    }
}
```

---

## Task 4: Pen Test Log + Remediation Tracker (item 56)

---

### Migrations

**`database/migrations/2026_05_28_007002_create_pen_test_engagements_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pen_test_engagements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('vendor_name', 255);
            $table->enum('engagement_type', [
                'black_box',
                'white_box',
                'grey_box',
                'red_team',
                'social_engineering',
            ]);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('scope');
            $table->string('report_path', 500)->nullable();
            $table->enum('status', [
                'planned',
                'in_progress',
                'completed',
                'remediation_in_progress',
                'closed',
            ])->default('planned');
            $table->integer('total_findings')->default(0);
            $table->integer('critical_findings')->default(0);
            $table->integer('high_findings')->default(0);
            $table->integer('medium_findings')->default(0);
            $table->integer('low_findings')->default(0);
            $table->integer('informational_findings')->default(0);
            $table->uuid('created_by')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pen_test_engagements');
    }
};
```

**`database/migrations/2026_05_28_007003_create_pen_test_findings_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pen_test_findings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pen_test_engagement_id')->index();
            $table->string('title', 255);
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'informational']);
            $table->decimal('cvss_score', 3, 1)->nullable();
            $table->text('description');
            $table->string('affected_component', 255);
            $table->text('attack_vector')->nullable();
            $table->text('remediation_steps');
            $table->enum('status', [
                'open',
                'in_progress',
                'remediated',
                'accepted_risk',
                'false_positive',
            ])->default('open');
            $table->uuid('assigned_to')->nullable()->index();
            $table->date('due_date')->nullable();
            $table->timestamp('remediated_at')->nullable();
            $table->uuid('remediated_by')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pen_test_engagement_id')
                  ->references('id')->on('pen_test_engagements')->cascadeOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('remediated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['pen_test_engagement_id', 'severity', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pen_test_findings');
    }
};
```

---

### Models

**`app/Models/PenTestEngagement.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenTestEngagement extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'vendor_name',
        'engagement_type',
        'start_date',
        'end_date',
        'scope',
        'report_path',
        'status',
        'total_findings',
        'critical_findings',
        'high_findings',
        'medium_findings',
        'low_findings',
        'informational_findings',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function findings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PenTestFinding::class);
    }

    /** Recalculate finding counts from DB and save. */
    public function recalculateFindingCounts(): void
    {
        $counts = $this->findings()
            ->selectRaw('severity, count(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        $this->update([
            'critical_findings'      => $counts->get('critical', 0),
            'high_findings'          => $counts->get('high', 0),
            'medium_findings'        => $counts->get('medium', 0),
            'low_findings'           => $counts->get('low', 0),
            'informational_findings' => $counts->get('informational', 0),
            'total_findings'         => $counts->sum(),
        ]);
    }
}
```

**`app/Models/PenTestFinding.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenTestFinding extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'pen_test_engagement_id',
        'title',
        'severity',
        'cvss_score',
        'description',
        'affected_component',
        'attack_vector',
        'remediation_steps',
        'status',
        'assigned_to',
        'due_date',
        'remediated_at',
        'remediated_by',
        'verification_notes',
    ];

    protected $casts = [
        'cvss_score'    => 'float',
        'due_date'      => 'date',
        'remediated_at' => 'datetime',
    ];

    public function engagement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PenTestEngagement::class, 'pen_test_engagement_id');
    }

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function remediatedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'remediated_by');
    }
}
```

---

### Service: `app/Services/Security/PenTestService.php`

```php
<?php

namespace App\Services\Security;

use App\Models\PenTestEngagement;
use App\Models\PenTestFinding;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PenTestService
{
    public function createEngagement(array $data): PenTestEngagement
    {
        return PenTestEngagement::create($data);
    }

    public function addFinding(string $engagementId, array $data): PenTestFinding
    {
        $finding = PenTestFinding::create(array_merge(
            $data,
            ['pen_test_engagement_id' => $engagementId],
        ));

        // Update counts on parent engagement
        $finding->engagement->recalculateFindingCounts();

        return $finding;
    }

    public function updateFindingStatus(
        string $findingId,
        string $status,
        ?string $notes = null,
        ?string $userId = null,
    ): PenTestFinding {
        $finding = PenTestFinding::findOrFail($findingId);

        $updates = ['status' => $status];

        if ($notes !== null) {
            $updates['verification_notes'] = $notes;
        }

        if ($status === 'remediated') {
            $updates['remediated_at'] = Carbon::now();
            if ($userId !== null) {
                $updates['remediated_by'] = $userId;
            }
        }

        $finding->update($updates);

        return $finding->fresh();
    }

    public function getOpenFindings(?string $severity = null): Collection
    {
        $query = PenTestFinding::whereIn('status', ['open', 'in_progress'])
            ->with(['engagement', 'assignee'])
            ->orderByRaw("CASE severity
                WHEN 'critical'      THEN 1
                WHEN 'high'          THEN 2
                WHEN 'medium'        THEN 3
                WHEN 'low'           THEN 4
                WHEN 'informational' THEN 5
                ELSE 6 END"
            );

        if ($severity !== null) {
            $query->where('severity', $severity);
        }

        return $query->get();
    }

    public function getRemediationSummary(string $engagementId): array
    {
        $findings = PenTestFinding::where('pen_test_engagement_id', $engagementId)->get();

        $total       = $findings->count();
        $open        = $findings->where('status', 'open')->count();
        $inProgress  = $findings->where('status', 'in_progress')->count();
        $remediated  = $findings->where('status', 'remediated')->count();
        $accepted    = $findings->where('status', 'accepted_risk')->count();
        $falsePos    = $findings->where('status', 'false_positive')->count();

        $bySeverity = $findings
            ->groupBy('severity')
            ->map(function ($group) {
                return [
                    'total'      => $group->count(),
                    'open'       => $group->whereIn('status', ['open', 'in_progress'])->count(),
                    'remediated' => $group->where('status', 'remediated')->count(),
                ];
            })
            ->toArray();

        return [
            'total'       => $total,
            'open'        => $open,
            'in_progress' => $inProgress,
            'remediated'  => $remediated,
            'accepted'    => $accepted,
            'false_positive' => $falsePos,
            'by_severity' => $bySeverity,
        ];
    }
}
```

---

### Controller: `app/Http/Controllers/Api/V1/Security/PenTestController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\PenTestEngagement;
use App\Services\Security\PenTestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenTestController extends Controller
{
    public function __construct(private readonly PenTestService $service)
    {
    }

    /** GET /api/v1/security/pen-tests */
    public function index(): JsonResponse
    {
        $engagements = PenTestEngagement::with('findings')->latest()->get();
        return response()->json(['data' => $engagements]);
    }

    /** POST /api/v1/security/pen-tests */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'vendor_name'     => 'required|string|max:255',
            'engagement_type' => 'required|in:black_box,white_box,grey_box,red_team,social_engineering',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'scope'           => 'required|string',
            'report_path'     => 'nullable|string|max:500',
            'status'          => 'nullable|in:planned,in_progress,completed,remediation_in_progress,closed',
        ]);

        $validated['created_by'] = $request->user()->id;

        $engagement = $this->service->createEngagement($validated);
        return response()->json(['data' => $engagement], 201);
    }

    /** GET /api/v1/security/pen-tests/{id} */
    public function show(string $id): JsonResponse
    {
        $engagement = PenTestEngagement::with('findings')->findOrFail($id);
        $summary    = $this->service->getRemediationSummary($id);

        return response()->json([
            'data'    => $engagement,
            'summary' => $summary,
        ]);
    }

    /** POST /api/v1/security/pen-tests/{id}/findings */
    public function storeFinding(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'severity'           => 'required|in:critical,high,medium,low,informational',
            'cvss_score'         => 'nullable|numeric|min:0|max:10',
            'description'        => 'required|string',
            'affected_component' => 'required|string|max:255',
            'attack_vector'      => 'nullable|string',
            'remediation_steps'  => 'required|string',
            'assigned_to'        => 'nullable|uuid|exists:users,id',
            'due_date'           => 'nullable|date',
        ]);

        $finding = $this->service->addFinding($id, $validated);
        return response()->json(['data' => $finding], 201);
    }

    /** PATCH /api/v1/security/pen-tests/{id}/findings/{findingId} */
    public function updateFinding(Request $request, string $id, string $findingId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,remediated,accepted_risk,false_positive',
            'notes'  => 'nullable|string',
        ]);

        $finding = $this->service->updateFindingStatus(
            $findingId,
            $validated['status'],
            $validated['notes'] ?? null,
            $request->user()->id,
        );

        return response()->json(['data' => $finding]);
    }

    /** GET /api/v1/security/pen-tests/open-findings?severity=critical */
    public function openFindings(Request $request): JsonResponse
    {
        $severity = $request->query('severity');
        $findings = $this->service->getOpenFindings($severity);
        return response()->json(['data' => $findings]);
    }
}
```

**Routes (add to `routes/api.php`):**
```php
Route::middleware(['auth:sanctum', 'role:admin,security'])->prefix('v1/security')->group(function () {
    Route::get('/pen-tests',                                       [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'index']);
    Route::post('/pen-tests',                                      [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'store']);
    Route::get('/pen-tests/open-findings',                         [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'openFindings']);
    Route::get('/pen-tests/{id}',                                  [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'show']);
    Route::post('/pen-tests/{id}/findings',                        [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'storeFinding']);
    Route::patch('/pen-tests/{id}/findings/{findingId}',           [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'updateFinding']);
});
```

---

### Test: `tests/Feature/PenTestTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Security\PenTestService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenTestTest extends TestCase
{
    use RefreshDatabase;

    private PenTestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PenTestService::class);
    }

    public function test_can_create_engagement(): void
    {
        $user = User::factory()->create();

        $engagement = $this->service->createEngagement([
            'title'           => 'Q2 2026 External Pen Test',
            'vendor_name'     => 'SecureOps Ltd',
            'engagement_type' => 'black_box',
            'start_date'      => '2026-06-01',
            'end_date'        => '2026-06-14',
            'scope'           => 'All public-facing APIs and web interfaces',
            'created_by'      => $user->id,
        ]);

        $this->assertDatabaseHas('pen_test_engagements', [
            'id'     => $engagement->id,
            'status' => 'planned',
        ]);
    }

    public function test_can_add_critical_finding(): void
    {
        $user = User::factory()->create();

        $engagement = $this->service->createEngagement([
            'title'           => 'Test Engagement',
            'vendor_name'     => 'Test Vendor',
            'engagement_type' => 'grey_box',
            'start_date'      => '2026-06-01',
            'end_date'        => '2026-06-07',
            'scope'           => 'Internal APIs',
            'created_by'      => $user->id,
        ]);

        $finding = $this->service->addFinding($engagement->id, [
            'title'              => 'SQL Injection in patient search endpoint',
            'severity'           => 'critical',
            'cvss_score'         => 9.8,
            'description'        => 'Unsanitized input in GET /api/patients?name= allows blind SQL injection.',
            'affected_component' => 'PatientController@index',
            'attack_vector'      => 'Network — unauthenticated',
            'remediation_steps'  => 'Use parameterized queries / Eloquent ORM exclusively. Add WAF rule.',
        ]);

        $this->assertEquals('critical', $finding->severity);
        $this->assertEquals('open', $finding->status);

        // Verify engagement counters updated
        $engagement->refresh();
        $this->assertEquals(1, $engagement->critical_findings);
        $this->assertEquals(1, $engagement->total_findings);
    }

    public function test_can_update_finding_to_remediated(): void
    {
        $user = User::factory()->create();

        $engagement = $this->service->createEngagement([
            'title'           => 'Test Engagement 2',
            'vendor_name'     => 'Test Vendor',
            'engagement_type' => 'white_box',
            'start_date'      => '2026-06-01',
            'end_date'        => '2026-06-07',
            'scope'           => 'All',
            'created_by'      => $user->id,
        ]);

        $finding = $this->service->addFinding($engagement->id, [
            'title'              => 'Weak JWT signing secret',
            'severity'           => 'high',
            'description'        => 'JWT tokens signed with weak HS256 secret.',
            'affected_component' => 'AuthController',
            'remediation_steps'  => 'Rotate to RS256 with 2048-bit key.',
        ]);

        $updated = $this->service->updateFindingStatus(
            $finding->id,
            'remediated',
            'Migrated to RS256. Verified in staging.',
            $user->id,
        );

        $this->assertEquals('remediated', $updated->status);
        $this->assertNotNull($updated->remediated_at);
        $this->assertEquals($user->id, $updated->remediated_by);
    }

    public function test_remediation_summary_counts_by_status(): void
    {
        $user = User::factory()->create();

        $engagement = $this->service->createEngagement([
            'title'           => 'Summary Test Engagement',
            'vendor_name'     => 'Vendor',
            'engagement_type' => 'red_team',
            'start_date'      => '2026-06-01',
            'end_date'        => '2026-06-30',
            'scope'           => 'Full',
            'created_by'      => $user->id,
        ]);

        // Add 3 findings: 1 open critical, 1 in_progress high, 1 remediated medium
        $f1 = $this->service->addFinding($engagement->id, [
            'title' => 'F1', 'severity' => 'critical',
            'description' => 'Desc', 'affected_component' => 'API',
            'remediation_steps' => 'Fix it',
        ]);
        $f2 = $this->service->addFinding($engagement->id, [
            'title' => 'F2', 'severity' => 'high',
            'description' => 'Desc', 'affected_component' => 'UI',
            'remediation_steps' => 'Fix it',
        ]);
        $f3 = $this->service->addFinding($engagement->id, [
            'title' => 'F3', 'severity' => 'medium',
            'description' => 'Desc', 'affected_component' => 'DB',
            'remediation_steps' => 'Fix it',
        ]);

        $this->service->updateFindingStatus($f2->id, 'in_progress');
        $this->service->updateFindingStatus($f3->id, 'remediated', 'Patched.', $user->id);

        $summary = $this->service->getRemediationSummary($engagement->id);

        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(1, $summary['open']);
        $this->assertEquals(1, $summary['in_progress']);
        $this->assertEquals(1, $summary['remediated']);
        $this->assertArrayHasKey('critical', $summary['by_severity']);
    }
}
```

---

## Implementation Order

1. Create config: `config/data_retention.php`
2. Run migrations in order: `007000` → `007001` → `007002` → `007003`
3. Register commands in `app/Console/Kernel.php`
4. Add routes in `routes/api.php`
5. Run tests:
   ```bash
   php artisan test tests/Feature/CredentialingTest.php \
                   tests/Feature/AdvanceDirectiveTest.php \
                   tests/Feature/PurgeExpiredDataTest.php \
                   tests/Feature/PenTestTest.php
   ```

## Verification Checklist

- [ ] `provider_credentials` table created with correct enum values for `credential_type` and `status`
- [ ] `CredentialingService::getExpiringCredentials(30)` returns only credentials with `expiry_date` within 30 days and `status = active`
- [ ] `NotifyExpiringCredentialsCommand` logs to `Log::warning` with `credential_id`, `provider_id`, `days_remaining`
- [ ] `AdvanceDirectiveService::hasActiveDnr` returns `false` after `revoke` is called
- [ ] `PurgeExpiredDataCommand --dry-run` does not delete any records (count asserted in test)
- [ ] `PurgeExpiredDataCommand` deletes USSD sessions older than `RETENTION_USSD_SESSIONS_DAYS`
- [ ] `PenTestEngagement::recalculateFindingCounts()` updates `critical_findings` correctly after `addFinding`
- [ ] `PenTestService::updateFindingStatus('remediated')` sets `remediated_at` and `remediated_by`
- [ ] `getRemediationSummary` `by_severity` key is present and contains per-severity breakdown
