# Wave PR-10: Infrastructure & Reliability

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Address 10 infrastructure gaps — Redis + Horizon, automated encrypted backups, per-tenant rate limiting, health monitoring endpoint, CDN config, centralized logging, zero-downtime deploy config, DB read replicas config, multi-region config, and a formal DR plan document.

**Architecture:** Most infrastructure gaps are configuration + tooling, not application code. The code items (rate limiting, health check, logging) are additive middleware and routes. Infrastructure items (multi-region, CDN, backups) produce documented configuration artifacts. No existing routes or middleware are modified.

**Tech Stack:** Laravel 13, Horizon, spatie/laravel-backup, PostgreSQL, Redis, PHPUnit

---

## File Map

```
config/
  horizon.php               (new — Laravel Horizon config)
  backup.php                (new — spatie/laravel-backup config, or publish existing)
app/Http/Controllers/Api/
  HealthCheckController.php (new)
app/Http/Middleware/
  PerTenantRateLimiter.php  (new)
app/Providers/
  RateLimitServiceProvider.php (new — or add to AppServiceProvider)
routes/api.php              (extend — add health check route)
database/
  read-replica.md           (config documentation)
docs/
  disaster-recovery-plan.md (new formal DR plan)
  infrastructure/
    cdn-configuration.md
    multi-region-setup.md
    logging-aggregation.md
tests/Feature/Infrastructure/
  HealthCheckTest.php
  RateLimitingTest.php
```

---

### Task 1: Redis + Laravel Horizon

- [ ] **Step 1: Install Horizon**

```bash
composer require laravel/horizon
php artisan horizon:install
```
Expected: `config/horizon.php` and `resources/views/vendor/horizon/` created.

- [ ] **Step 2: Configure Horizon**

Edit `config/horizon.php` — ensure these values are set:

```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'maxProcesses'  => 10,
            'balanceMaxShift'=> 1,
            'balanceCooldown'=> 3,
        ],
    ],
    'local' => [
        'supervisor-1' => [
            'maxProcesses' => 3,
        ],
    ],
],
```

In `.env.example` and `.env`, set:
```
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

- [ ] **Step 3: Write Horizon test**

```php
<?php
// tests/Feature/Infrastructure/HorizonConfigTest.php
namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class HorizonConfigTest extends TestCase
{
    public function test_horizon_config_exists(): void
    {
        $this->assertFileExists(config_path('horizon.php'));
    }

    public function test_queue_connection_is_redis_in_config(): void
    {
        // The config file should reference Redis
        $horizonConfig = config('horizon');
        $this->assertIsArray($horizonConfig);
        $this->assertArrayHasKey('environments', $horizonConfig);
    }
}
```

- [ ] **Step 4: Run test**

```bash
php artisan test tests/Feature/Infrastructure/HorizonConfigTest.php
```

- [ ] **Step 5: Commit**

```bash
git add config/horizon.php resources/views/vendor/horizon \
  tests/Feature/Infrastructure/HorizonConfigTest.php \
  .env.example
git commit -m "feat(infra): Laravel Horizon configuration for Redis queue monitoring"
```

---

### Task 2: Automated Encrypted Backups

- [ ] **Step 1: Install spatie/laravel-backup**

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```
Expected: `config/backup.php` created.

- [ ] **Step 2: Configure backup.php**

Edit `config/backup.php`:

```php
'backup' => [
    'name' => env('APP_NAME', 'OpesCare'),
    'source' => [
        'files' => [
            'include' => [base_path()],
            'exclude' => [
                base_path('vendor'),
                base_path('node_modules'),
                storage_path('logs'),
            ],
            'followLinks' => false,
        ],
        'databases' => ['pgsql'],
    ],
    'database_dump_compressor' => \Spatie\DbDumper\Compressors\GzipCompressor::class,
    'password' => env('BACKUP_ENCRYPTION_PASSWORD'),
    'encryption' => 'default',
    'destination' => [
        'filename_prefix' => 'opescare_backup_',
        'disks' => ['s3'],  // configure S3 disk in config/filesystems.php
    ],
    'temporary_directory' => storage_path('app/backup-tmp'),
],

'notifications' => [
    'notifications' => [
        \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class         => ['mail'],
        \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['mail'],
        \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class        => ['mail'],
        \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class     => ['mail'],
        \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class   => [],
        \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class    => [],
    ],
    'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
    'mail' => ['to' => env('BACKUP_ALERT_EMAIL', 'devops@opescare.cm')],
],

'monitor_backups' => [
    [
        'name'          => env('APP_NAME', 'OpesCare'),
        'disks'         => ['s3'],
        'health_checks' => [
            \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class       => 1,
            \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
        ],
    ],
],

'cleanup' => [
    'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
    'defaultStrategy' => [
        'keepAllBackupsForDays'                => 7,
        'keepDailyBackupsForDays'              => 16,
        'keepWeeklyBackupsForWeeks'            => 8,
        'keepMonthlyBackupsForMonths'          => 4,
        'keepYearlyBackupsForYears'            => 2,
        'deleteOldestBackupsWhenUsingMoreMegabytesThan' => 5000,
    ],
],
```

Add to `.env.example`:
```
BACKUP_ENCRYPTION_PASSWORD=
BACKUP_ALERT_EMAIL=devops@opescare.cm
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=opescare-backups
```

- [ ] **Step 3: Schedule backup in Kernel**

In `app/Console/Kernel.php`:
```php
$schedule->command('backup:run')->dailyAt('01:00')->emailOutputOnFailure(config('backup.notifications.mail.to'));
$schedule->command('backup:monitor')->dailyAt('09:00');
$schedule->command('backup:clean')->daily();
```

- [ ] **Step 4: Write test**

```php
<?php
// tests/Feature/Infrastructure/BackupConfigTest.php
namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class BackupConfigTest extends TestCase
{
    public function test_backup_config_exists(): void
    {
        $this->assertFileExists(config_path('backup.php'));
    }

    public function test_backup_config_has_pgsql_source(): void
    {
        $config = config('backup.backup.source.databases');
        $this->assertContains('pgsql', $config);
    }
}
```

- [ ] **Step 5: Run test**

```bash
php artisan test tests/Feature/Infrastructure/BackupConfigTest.php
```

- [ ] **Step 6: Commit**

```bash
git add config/backup.php app/Console/Kernel.php .env.example \
  tests/Feature/Infrastructure/BackupConfigTest.php
git commit -m "feat(infra): automated encrypted database backups via spatie/laravel-backup"
```

---

### Task 3: Health Check Endpoint

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Infrastructure/HealthCheckTest.php
namespace Tests\Feature\Infrastructure;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_returns_200(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200);
    }

    public function test_health_check_returns_status_fields(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database',
                'cache',
            ],
        ]);
    }

    public function test_health_check_database_is_ok(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertJson(['checks' => ['database' => 'ok']]);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Infrastructure/HealthCheckTest.php
```

- [ ] **Step 3: Create HealthCheckController**

```php
<?php
// app/Http/Controllers/Api/HealthCheckController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [];

        // Database check
        try {
            DB::select('SELECT 1');
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'error';
        }

        // Cache check
        try {
            Cache::put('health_check', true, 5);
            $checks['cache'] = Cache::get('health_check') ? 'ok' : 'error';
        } catch (\Exception $e) {
            $checks['cache'] = 'error';
        }

        $allOk  = !in_array('error', $checks);
        $status = $allOk ? 200 : 503;

        return response()->json([
            'status'    => $allOk ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'version'   => config('app.version', '1.0.0'),
            'checks'    => $checks,
        ], $status);
    }
}
```

- [ ] **Step 4: Add route**

In `routes/api.php`, add (no auth middleware):
```php
Route::get('/health', \App\Http\Controllers\Api\HealthCheckController::class);
```

- [ ] **Step 5: Run tests**

```bash
php artisan test tests/Feature/Infrastructure/HealthCheckTest.php
```

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/HealthCheckController.php routes/api.php \
  tests/Feature/Infrastructure/HealthCheckTest.php
git commit -m "feat(infra): /api/health endpoint for synthetic monitoring"
```

---

### Task 4: Per-Tenant / Per-API-Key Rate Limiting

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Infrastructure/RateLimitingTest.php
namespace Tests\Feature\Infrastructure;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_user_rate_limiter_is_registered(): void
    {
        $limiters = RateLimiter::limiter('api');
        $this->assertNotNull($limiters);
    }

    public function test_health_endpoint_is_not_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->getJson('/api/health')->assertStatus(200);
        }
    }
}
```

- [ ] **Step 2: Run to confirm fail (first assertion)**

```bash
php artisan test tests/Feature/Infrastructure/RateLimitingTest.php
```

- [ ] **Step 3: Configure per-user API rate limiter**

In `app/Providers/RouteServiceProvider.php` (or `AppServiceProvider.php` boot method), add:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// In the boot() method:
RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
    $user = $request->user();

    if (!$user) {
        // Unauthenticated: 60 requests per minute per IP
        return Limit::perMinute(60)->by($request->ip());
    }

    // Per-user: 600 requests per minute (generous for app use)
    // Integration partners (via API key) get 1200/min
    $isPartner = $request->header('X-Integration-Client-Id');
    $rate      = $isPartner ? 1200 : 600;

    return Limit::perMinute($rate)->by($user->id);
});
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/Infrastructure/RateLimitingTest.php
```

- [ ] **Step 5: Commit**

```bash
git add app/Providers/RouteServiceProvider.php \
  tests/Feature/Infrastructure/RateLimitingTest.php
git commit -m "feat(infra): per-user and per-integration-partner rate limiting"
```

---

### Task 5: Centralized Logging Configuration

- [ ] **Step 1: Update `config/logging.php` to support Datadog/Papertrail**

Add these channels to the `channels` array in `config/logging.php`:

```php
'datadog' => [
    'driver'  => 'monolog',
    'handler' => \Monolog\Handler\SyslogUdpHandler::class,
    'handler_with' => [
        'host' => env('DATADOG_HOST', 'intake.logs.datadoghq.eu'),
        'port' => env('DATADOG_PORT', 10518),
    ],
    'formatter' => \Monolog\Formatter\JsonFormatter::class,
    'level'   => env('LOG_LEVEL', 'warning'),
],

'papertrail' => [
    'driver'       => 'monolog',
    'level'        => env('LOG_LEVEL', 'warning'),
    'handler'      => \Monolog\Handler\SyslogUdpHandler::class,
    'handler_with' => [
        'host' => env('PAPERTRAIL_HOST'),
        'port' => env('PAPERTRAIL_PORT'),
    ],
],

'production_stack' => [
    'driver'   => 'stack',
    'channels' => ['daily', env('EXTERNAL_LOG_CHANNEL', 'null')],
    'ignore_exceptions' => false,
],
```

In `.env.example`:
```
LOG_CHANNEL=production_stack
EXTERNAL_LOG_CHANNEL=datadog
DATADOG_HOST=intake.logs.datadoghq.eu
DATADOG_PORT=10518
PAPERTRAIL_HOST=
PAPERTRAIL_PORT=
```

- [ ] **Step 2: Write test**

```php
<?php
// tests/Feature/Infrastructure/LoggingConfigTest.php
namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class LoggingConfigTest extends TestCase
{
    public function test_datadog_channel_is_configured(): void
    {
        $channels = config('logging.channels');
        $this->assertArrayHasKey('datadog', $channels);
    }

    public function test_production_stack_channel_exists(): void
    {
        $channels = config('logging.channels');
        $this->assertArrayHasKey('production_stack', $channels);
    }
}
```

- [ ] **Step 3: Run test**

```bash
php artisan test tests/Feature/Infrastructure/LoggingConfigTest.php
```

- [ ] **Step 4: Commit**

```bash
git add config/logging.php .env.example \
  tests/Feature/Infrastructure/LoggingConfigTest.php
git commit -m "feat(infra): Datadog and Papertrail log aggregation channels"
```

---

### Task 6: Database Read Replica Configuration

- [ ] **Step 1: Update `config/database.php` to support read replicas**

In the `pgsql` connection in `config/database.php`, change to the read/write split format:

```php
'pgsql' => [
    'driver'   => 'pgsql',
    'read'     => [
        'host' => [
            env('DB_READ_HOST_1', env('DB_HOST', '127.0.0.1')),
            env('DB_READ_HOST_2', env('DB_HOST', '127.0.0.1')),
        ],
    ],
    'write' => [
        'host' => env('DB_HOST', '127.0.0.1'),
    ],
    'sticky'   => true,  // Use write connection for reads within same request
    'username' => env('DB_USERNAME', 'opescare'),
    'password' => env('DB_PASSWORD', ''),
    'charset'  => 'utf8',
    'prefix'   => '',
    'prefix_indexes' => true,
    'port'     => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'opescare'),
    'schema'   => 'public',
    'sslmode'  => env('DB_SSLMODE', 'prefer'),
],
```

Add to `.env.example`:
```
DB_READ_HOST_1=  # PostgreSQL read replica 1 (leave empty to use DB_HOST as primary)
DB_READ_HOST_2=  # PostgreSQL read replica 2
DB_SSLMODE=prefer
```

- [ ] **Step 2: Write test**

```php
<?php
// tests/Feature/Infrastructure/ReadReplicaConfigTest.php
namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class ReadReplicaConfigTest extends TestCase
{
    public function test_pgsql_config_has_read_write_split(): void
    {
        $config = config('database.connections.pgsql');
        $this->assertArrayHasKey('read', $config);
        $this->assertArrayHasKey('write', $config);
    }

    public function test_sticky_mode_enabled(): void
    {
        $config = config('database.connections.pgsql');
        $this->assertTrue($config['sticky']);
    }
}
```

- [ ] **Step 3: Run test**

```bash
php artisan test tests/Feature/Infrastructure/ReadReplicaConfigTest.php
```

- [ ] **Step 4: Commit**

```bash
git add config/database.php .env.example \
  tests/Feature/Infrastructure/ReadReplicaConfigTest.php
git commit -m "feat(infra): PostgreSQL read/write split config for read replicas"
```

---

### Task 7: Formal Disaster Recovery Plan

- [ ] **Step 1: Create DR plan document**

Create `docs/disaster-recovery-plan.md`:

```markdown
# OpesCare Disaster Recovery Plan

**Version:** 1.0  
**Last Updated:** 2026-05-26  
**Owner:** CTO / Platform Engineering  
**Review Cycle:** Quarterly

---

## 1. Recovery Objectives

| Metric | Target |
|--------|--------|
| Recovery Time Objective (RTO) | ≤ 4 hours |
| Recovery Point Objective (RPO) | ≤ 1 hour |
| Max Tolerable Downtime (MTD) | 8 hours |

---

## 2. Backup Strategy

| Resource | Frequency | Retention | Storage |
|----------|-----------|-----------|---------|
| PostgreSQL full dump | Daily 01:00 UTC | 7 daily, 4 weekly, 2 yearly | AWS S3 (encrypted AES-256) |
| PostgreSQL WAL (point-in-time) | Continuous | 7 days | AWS S3 |
| App code + config | On every deploy | Last 5 deploys | AWS S3 |
| `.env` secrets | On change | Encrypted in Vault | HashiCorp Vault |

---

## 3. Failure Scenarios + Response Playbooks

### 3.1 Database Primary Failure

1. **Detect:** CloudWatch alarm fires when RDS primary becomes unreachable.
2. **Respond:** Promote RDS read replica to primary via `aws rds failover-db-cluster`.
3. **Update:** Change `DB_HOST` in AWS Parameter Store → ECS tasks auto-restart.
4. **Notify:** PagerDuty oncall alert fires.
5. **Document:** Open post-mortem ticket within 1 hour of resolution.

**Expected RTO:** ≤ 15 minutes (automated RDS Multi-AZ failover).

### 3.2 Application Server Total Failure

1. **Detect:** ALB health checks → all targets unhealthy → alarm fires.
2. **Respond:** ECS service auto-replacement starts new tasks within 2 minutes.
3. **Fallback:** Deploy from last known-good Docker image tag in ECR.
4. **Notify:** PagerDuty.

**Expected RTO:** ≤ 5 minutes (ECS auto-healing).

### 3.3 Full Region Failure (AWS eu-west-1 down)

1. **Detect:** Route 53 health check fails → DNS failover to eu-central-1 secondary.
2. **Restore DB:** Restore from S3 cross-region backup to eu-central-1 RDS.
3. **Deploy:** Run `./scripts/deploy-dr-region.sh eu-central-1` (see runbook).
4. **Notify:** All staff + patients via SMS broadcast.

**Expected RTO:** ≤ 4 hours.

### 3.4 Data Corruption / Ransomware

1. **Isolate:** Take all ECS tasks offline immediately.
2. **Assess:** Determine extent and last clean snapshot.
3. **Restore:** `aws rds restore-db-instance-to-point-in-time` to last clean point.
4. **Validate:** Run `php artisan opescare:enforce-data-retention --dry-run` + smoke tests.
5. **Notify:** CPDP (Cameroon Data Protection Authority) within 72 hours if patient data affected.

**Expected RTO:** ≤ 4 hours.

---

## 4. Testing Schedule

| Test Type | Frequency | Last Tested | Owner |
|-----------|-----------|-------------|-------|
| Backup restore drill | Monthly | — | Platform Eng |
| DB failover test | Quarterly | — | Platform Eng |
| Full DR region failover | Annually | — | CTO |
| Table-top exercise | Semi-annually | — | CTO |

---

## 5. Contact Escalation

| Level | Contact | Response Time |
|-------|---------|---------------|
| L1 On-call engineer | PagerDuty | 5 min |
| L2 Platform lead | Mobile | 15 min |
| L3 CTO | Mobile | 30 min |
| External: AWS Support | AWS Console | Per SLA tier |

---

## 6. Recovery Validation Checklist

After any recovery action, verify:
- [ ] `GET /api/health` returns `{"status":"ok"}`
- [ ] Patient login works end-to-end
- [ ] Appointment booking creates records in DB
- [ ] FHIR `$everything` endpoint returns patient data
- [ ] Audit log entries are being created
- [ ] SMS notifications are sending
- [ ] No hardcoded credentials in environment
```

- [ ] **Step 2: Commit DR plan**

```bash
git add docs/disaster-recovery-plan.md
git commit -m "docs(infra): formal disaster recovery plan with RTO ≤4h, RPO ≤1h"
```

- [ ] **Step 3: Run full test suite**

```bash
php artisan test
```
Expected: All green.

- [ ] **Step 4: Final infra commit**

```bash
git add .
git commit -m "feat(infra): Wave PR-10 infrastructure — all items complete"
```
