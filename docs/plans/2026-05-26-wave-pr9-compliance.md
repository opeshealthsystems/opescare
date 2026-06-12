# Wave PR-9: Compliance & Governance

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete 4 compliance gaps — Cameroon Law 2010/012 compliance checklist, advance directives / living will, automated data retention + purge, and third-party pen test log.

**Architecture:** Compliance checklist is a seeded reference table. Advance directives are a patient-linked document model. Data retention is an Artisan command + policy model. Pen test log is an append-only record table. All additive.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, Artisan console commands, PHPUnit

---

## File Map

```
database/migrations/
  2026_05_26_900001_create_advance_directives_table.php
  2026_05_26_900002_create_data_retention_policies_table.php
  2026_05_26_900003_create_security_audit_logs_table.php
app/Models/
  AdvanceDirective.php
  DataRetentionPolicy.php
  SecurityAuditLog.php
app/Console/Commands/
  EnforceDataRetentionCommand.php
app/Services/Compliance/
  DataRetentionService.php
  ComplianceChecklistService.php
database/seeders/
  CameroonComplianceChecklistSeeder.php
tests/Feature/Compliance/
  AdvanceDirectiveTest.php
  DataRetentionTest.php
  PenTestLogTest.php
  ComplianceChecklistTest.php
```

---

### Task 1: Advance Directives / Living Will

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Compliance/AdvanceDirectiveTest.php
namespace Tests\Feature\Compliance;

use App\Models\Patient;
use App\Models\User;
use App\Models\AdvanceDirective;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvanceDirectiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_register_advance_directive(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();

        $directive = AdvanceDirective::create([
            'patient_id'         => $patient->id,
            'recorded_by'        => $provider->id,
            'directive_type'     => 'dnr',
            'content'            => 'Patient does not wish to be resuscitated.',
            'effective_date'     => '2026-07-01',
            'witness_name'       => 'Marie Nguembi',
            'is_active'          => true,
        ]);

        $this->assertEquals('dnr', $directive->directive_type);
        $this->assertTrue($directive->is_active);
    }

    public function test_only_one_active_dnr_per_patient(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();

        AdvanceDirective::create([
            'patient_id'     => $patient->id,
            'recorded_by'    => $provider->id,
            'directive_type' => 'dnr',
            'content'        => 'Original DNR',
            'effective_date' => '2026-01-01',
            'is_active'      => true,
        ]);

        // Second active DNR should deactivate the first
        $first = AdvanceDirective::where('patient_id', $patient->id)
            ->where('directive_type', 'dnr')
            ->where('is_active', true)->first();
        $first->update(['is_active' => false]);

        AdvanceDirective::create([
            'patient_id'     => $patient->id,
            'recorded_by'    => $provider->id,
            'directive_type' => 'dnr',
            'content'        => 'Updated DNR with new conditions',
            'effective_date' => '2026-07-01',
            'is_active'      => true,
        ]);

        $activeDnrs = AdvanceDirective::where('patient_id', $patient->id)
            ->where('directive_type', 'dnr')
            ->where('is_active', true)->count();

        $this->assertEquals(1, $activeDnrs);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Compliance/AdvanceDirectiveTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_900001_create_advance_directives_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('advance_directives', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->enum('directive_type', [
                'dnr',              // Do Not Resuscitate
                'living_will',      // Living will / advance care plan
                'poa_healthcare',   // Power of attorney for healthcare
                'organ_donation',   // Organ donation consent
                'other',
            ]);
            $table->text('content');
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->string('witness_name')->nullable();
            $table->string('document_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index(['patient_id', 'directive_type', 'is_active']);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('advance_directives'); }
};
```

- [ ] **Step 4: Create AdvanceDirective model**

```php
<?php
// app/Models/AdvanceDirective.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AdvanceDirective extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','recorded_by','directive_type','content',
        'effective_date','expiry_date','witness_name','document_url','is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date'    => 'date',
        'is_active'      => 'boolean',
    ];

    public function patient()    { return $this->belongsTo(Patient::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Compliance/AdvanceDirectiveTest.php
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_900001_* app/Models/AdvanceDirective.php \
  tests/Feature/Compliance/AdvanceDirectiveTest.php
git commit -m "feat(compliance): advance directives and living will management"
```

---

### Task 2: Automated Data Retention + Purge

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Compliance/DataRetentionTest.php
namespace Tests\Feature\Compliance;

use App\Models\DataRetentionPolicy;
use App\Services\Compliance\DataRetentionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_retention_policy(): void
    {
        $policy = DataRetentionPolicy::create([
            'table_name'           => 'audit_logs',
            'retention_days'       => 2555, // 7 years
            'purge_action'         => 'delete',
            'legal_basis'          => 'Cameroon Law 2010/012 Art. 25',
            'is_active'            => true,
        ]);

        $this->assertEquals('audit_logs', $policy->table_name);
        $this->assertEquals(2555, $policy->retention_days);
    }

    public function test_retention_service_identifies_records_for_purge(): void
    {
        DataRetentionPolicy::create([
            'table_name'     => 'ussd_sessions',
            'retention_days' => 30,
            'purge_action'   => 'delete',
            'legal_basis'    => 'Operational need',
            'is_active'      => true,
        ]);

        $service  = new DataRetentionService();
        $policies = $service->getActivePolicies();

        $this->assertTrue($policies->contains('table_name', 'ussd_sessions'));
    }

    public function test_artisan_command_runs_without_error(): void
    {
        DataRetentionPolicy::create([
            'table_name'     => 'ussd_sessions',
            'retention_days' => 30,
            'purge_action'   => 'delete',
            'legal_basis'    => 'Operational',
            'is_active'      => true,
        ]);

        $this->artisan('opescare:enforce-data-retention --dry-run')
             ->assertExitCode(0);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Compliance/DataRetentionTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_900002_create_data_retention_policies_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('table_name')->unique();
            $table->unsignedInteger('retention_days');
            $table->enum('purge_action', ['delete','anonymise','archive'])->default('delete');
            $table->string('legal_basis')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedBigInteger('last_run_purged')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('data_retention_policies'); }
};
```

- [ ] **Step 4: Create DataRetentionPolicy model**

```php
<?php
// app/Models/DataRetentionPolicy.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DataRetentionPolicy extends Model
{
    use HasUuids;

    protected $fillable = [
        'table_name','retention_days','purge_action',
        'legal_basis','is_active','last_run_at','last_run_purged',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'last_run_at' => 'datetime',
    ];
}
```

- [ ] **Step 5: Create DataRetentionService**

```php
<?php
// app/Services/Compliance/DataRetentionService.php
namespace App\Services\Compliance;

use App\Models\DataRetentionPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataRetentionService
{
    public function getActivePolicies()
    {
        return DataRetentionPolicy::where('is_active', true)->get();
    }

    /**
     * Enforce all active retention policies.
     *
     * @param  bool $dryRun  If true, log what would be deleted but don't delete
     * @return array         Summary: ['table' => count_purged]
     */
    public function enforce(bool $dryRun = false): array
    {
        $summary = [];

        foreach ($this->getActivePolicies() as $policy) {
            $cutoff = now()->subDays($policy->retention_days)->toDateTimeString();

            // Only process tables that have a created_at column
            if (!DB::getSchemaBuilder()->hasColumn($policy->table_name, 'created_at')) {
                Log::warning("DataRetention: table {$policy->table_name} has no created_at column — skipping");
                continue;
            }

            $query = DB::table($policy->table_name)->where('created_at', '<', $cutoff);
            $count = $query->count();

            if (!$dryRun && $count > 0) {
                match ($policy->purge_action) {
                    'delete'    => $query->delete(),
                    'anonymise' => $query->update(['anonymised_at' => now()]),
                    default     => null,
                };

                $policy->update([
                    'last_run_at'     => now(),
                    'last_run_purged' => $count,
                ]);
            }

            $summary[$policy->table_name] = [
                'action'     => $dryRun ? 'dry_run' : $policy->purge_action,
                'count'      => $count,
                'cutoff'     => $cutoff,
            ];

            Log::info("DataRetention [{$policy->table_name}]: {$count} records would be {$policy->purge_action}d" . ($dryRun ? ' (dry run)' : ''));
        }

        return $summary;
    }
}
```

- [ ] **Step 6: Create Artisan command**

```php
<?php
// app/Console/Commands/EnforceDataRetentionCommand.php
namespace App\Console\Commands;

use App\Services\Compliance\DataRetentionService;
use Illuminate\Console\Command;

class EnforceDataRetentionCommand extends Command
{
    protected $signature   = 'opescare:enforce-data-retention {--dry-run : Log what would be purged without deleting}';
    protected $description = 'Enforce data retention policies per Cameroon Law 2010/012 and internal data governance rules';

    public function handle(DataRetentionService $service): int
    {
        $dryRun  = $this->option('dry-run');
        $this->info('OpesCare Data Retention Enforcement' . ($dryRun ? ' [DRY RUN]' : ''));

        $summary = $service->enforce($dryRun);

        foreach ($summary as $table => $result) {
            $this->line(sprintf(
                '  %-40s  %s  %d records  cutoff: %s',
                $table,
                str_pad($result['action'], 10),
                $result['count'],
                $result['cutoff'],
            ));
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
```

- [ ] **Step 7: Register command in `app/Console/Kernel.php`**

In the `$commands` array of `Kernel.php` add:
```php
\App\Console\Commands\EnforceDataRetentionCommand::class,
```

Also add to the schedule:
```php
$schedule->command('opescare:enforce-data-retention')->daily()->at('02:00');
```

- [ ] **Step 8: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Compliance/DataRetentionTest.php
```

- [ ] **Step 9: Commit**

```bash
git add database/migrations/2026_05_26_900002_* app/Models/DataRetentionPolicy.php \
  app/Services/Compliance/DataRetentionService.php \
  app/Console/Commands/EnforceDataRetentionCommand.php \
  app/Console/Kernel.php \
  tests/Feature/Compliance/DataRetentionTest.php
git commit -m "feat(compliance): automated data retention enforcement per Law 2010/012"
```

---

### Task 3: Third-Party Pen Test Log

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Compliance/PenTestLogTest.php
namespace Tests\Feature\Compliance;

use App\Models\SecurityAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenTestLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_pen_test_log_entry(): void
    {
        $log = SecurityAuditLog::create([
            'audit_type'        => 'penetration_test',
            'vendor_name'       => 'SecureWorks Cameroon',
            'audit_date'        => '2026-06-01',
            'scope'             => 'External network + API surface',
            'findings_count'    => 12,
            'critical_count'    => 1,
            'high_count'        => 3,
            'medium_count'      => 5,
            'low_count'         => 3,
            'status'            => 'in_remediation',
            'report_url'        => 'https://secure.opescare.cm/audits/pentest-2026-06.pdf',
            'next_assessment'   => '2026-12-01',
        ]);

        $this->assertEquals('penetration_test', $log->audit_type);
        $this->assertEquals(1, $log->critical_count);
    }

    public function test_pen_test_log_is_append_only(): void
    {
        $log = SecurityAuditLog::create([
            'audit_type'     => 'vulnerability_scan',
            'vendor_name'    => 'Internal',
            'audit_date'     => '2026-05-01',
            'scope'          => 'Full API surface',
            'findings_count' => 0,
            'critical_count' => 0,
            'high_count'     => 0,
            'medium_count'   => 0,
            'low_count'      => 0,
            'status'         => 'completed',
        ]);

        // Updates are prohibited — model throws
        $this->expectException(\LogicException::class);
        $log->update(['findings_count' => 5]);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Compliance/PenTestLogTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_900003_create_security_audit_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->enum('audit_type', [
                'penetration_test','vulnerability_scan','code_review',
                'compliance_audit','soc2_assessment','iso27001_audit','other',
            ]);
            $table->string('vendor_name');
            $table->date('audit_date');
            $table->text('scope');
            $table->unsignedInteger('findings_count')->default(0);
            $table->unsignedInteger('critical_count')->default(0);
            $table->unsignedInteger('high_count')->default(0);
            $table->unsignedInteger('medium_count')->default(0);
            $table->unsignedInteger('low_count')->default(0);
            $table->enum('status', ['in_progress','in_remediation','completed','closed'])->default('in_progress');
            $table->string('report_url')->nullable();
            $table->date('next_assessment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('security_audit_logs'); }
};
```

- [ ] **Step 4: Create SecurityAuditLog model (append-only)**

```php
<?php
// app/Models/SecurityAuditLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'audit_type','vendor_name','audit_date','scope',
        'findings_count','critical_count','high_count','medium_count','low_count',
        'status','report_url','next_assessment','notes',
    ];

    protected $casts = [
        'audit_date'      => 'date',
        'next_assessment' => 'date',
    ];

    /** Append-only — no updates permitted. Add a new entry to correct. */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \LogicException('SecurityAuditLog is an append-only record. Create a new entry to make corrections.');
    }
}
```

- [ ] **Step 5: Run tests + full suite**

```bash
php artisan migrate && php artisan test tests/Feature/Compliance/PenTestLogTest.php && php artisan test
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_900003_* app/Models/SecurityAuditLog.php \
  tests/Feature/Compliance/PenTestLogTest.php
git commit -m "feat(compliance): append-only security audit log for pen test and vulnerability scan tracking"
```
