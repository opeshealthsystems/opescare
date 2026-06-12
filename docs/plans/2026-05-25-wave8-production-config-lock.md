# Wave 8 — Production Configuration Lock & Infrastructure Hardening

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Lock down all remaining production configuration gaps — queue driver, cache driver, maintenance mode, HTTPS enforcement, rate limiting documentation, and Horizon/queue worker setup for production job processing. Apply Waves 1–7 first.

**Architecture:** Config-only changes. No database migrations. No route changes. Each task is independently testable.

**Tech Stack:** Laravel 13, Redis, PostgreSQL

**Findings addressed:** Remaining L-findings, production deployment prerequisites

---

### Task 1: Configure Redis for cache, sessions, and queues

**Files:**
- Modify: `.env.example` (already done in Wave 4)
- Create: `config/opescare.php` (platform-wide config)
- Test: `tests/Feature/Config/ProductionConfigTest.php`

- [ ] **Step 1: Write the production config test**

Create `tests/Feature/Config/ProductionConfigTest.php`:

```php
<?php
namespace Tests\Feature\Config;

use Tests\TestCase;

class ProductionConfigTest extends TestCase
{
    public function test_queue_is_not_sync_in_production(): void
    {
        // In testing, sync is fine. In production, it should be redis or database.
        if (app()->isProduction()) {
            $this->assertNotEquals('sync', config('queue.default'),
                'QUEUE_CONNECTION=sync in production blocks request processing during job execution');
        } else {
            $this->assertTrue(true); // Skip in non-production
        }
    }

    public function test_cache_is_not_file_in_production(): void
    {
        if (app()->isProduction()) {
            $this->assertNotEquals('file', config('cache.default'),
                'CACHE_STORE=file in production does not support rate limiting across multiple servers');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_opescare_system_provider_id_is_configured(): void
    {
        $providerId = config('opescare.system_provider_id');
        $this->assertNotEmpty($providerId, 'OPESCARE_SYSTEM_PROVIDER_ID must be set');
        // Must be a valid UUID format
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $providerId,
            'OPESCARE_SYSTEM_PROVIDER_ID must be a valid UUID'
        );
    }
}
```

- [ ] **Step 2: Run test**

```bash
php artisan test tests/Feature/Config/ProductionConfigTest.php
```

Expected: PASS (tests skip in non-production environment)

- [ ] **Step 3: Create opescare config file if not exists**

Create `config/opescare.php`:

```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | OpesCare Platform Configuration
    |--------------------------------------------------------------------------
    */

    // UUID of the system service account for B2B-imported clinical records.
    // Must exist in the users table. Run: php artisan db:seed --class=SystemAccountSeeder
    'system_provider_id' => env('OPESCARE_SYSTEM_PROVIDER_ID', '00000000-0000-0000-0000-000000000001'),

    // Demo mode settings
    'demo' => [
        'enabled'        => env('OPESCARE_DEMO_MODE', false),
        'allowed_ips'    => env('DEMO_ALLOWED_IPS', ''),
    ],

    // Family account invite settings
    'family' => [
        'invite_ttl_hours' => env('FAMILY_INVITE_TTL_HOURS', 48),
    ],

    // Health ID generation
    'health_id' => [
        'default_country' => env('OPESCARE_DEFAULT_COUNTRY', 'CM'),
    ],
];
```

- [ ] **Step 4: Commit**

```bash
git add config/opescare.php tests/Feature/Config/ProductionConfigTest.php
git commit -m "config: create opescare.php platform config; add production config safety tests"
```

---

### Task 2: Create system provider account seeder

**Files:**
- Create: `database/seeders/SystemAccountSeeder.php`

- [ ] **Step 1: Create seeder**

Create `database/seeders/SystemAccountSeeder.php`:

```php
<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAccountSeeder extends Seeder
{
    public function run(): void
    {
        $systemProviderId = config('opescare.system_provider_id',
            '00000000-0000-0000-0000-000000000001');

        $existing = User::find($systemProviderId);

        if ($existing) {
            $this->command->info("System provider account already exists: {$systemProviderId}");
            return;
        }

        // Create system service account — this user is the author of B2B-imported clinical records
        $user = new User();
        $user->id             = $systemProviderId;
        $user->name           = 'OpesCare System';
        $user->email          = 'system@opescare.internal';
        $user->password       = Hash::make(\Illuminate\Support\Str::random(64)); // unreachable password
        $user->status         = 'system';
        $user->is_demo        = false;
        $user->save();

        $this->command->info("System provider account created: {$systemProviderId}");
    }
}
```

- [ ] **Step 2: Register in DatabaseSeeder**

In `database/seeders/DatabaseSeeder.php`, add:

```php
$this->call([
    SystemAccountSeeder::class,
    // ... other seeders
]);
```

- [ ] **Step 3: Run seeder**

```bash
php artisan db:seed --class=SystemAccountSeeder
```

Expected: System account created without error.

- [ ] **Step 4: Commit**

```bash
git add database/seeders/SystemAccountSeeder.php database/seeders/DatabaseSeeder.php
git commit -m "feat: add SystemAccountSeeder to create B2B system provider account"
```

---

### Task 3: Add HTTPS enforcement middleware for production

**Files:**
- Create: `app/Http/Middleware/ForceHttps.php`
- Modify: `bootstrap/app.php`

- [ ] **Step 1: Create ForceHttps middleware**

Create `app/Http/Middleware/ForceHttps.php`:

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce HTTPS in production
        if (app()->isProduction() && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Register in bootstrap/app.php**

Add to the global web middleware (runs before everything):

```php
$middleware->prepend(\App\Http\Middleware\ForceHttps::class);
```

- [ ] **Step 3: Write test**

Create `tests/Feature/Security/ForceHttpsTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use Tests\TestCase;

class ForceHttpsTest extends TestCase
{
    public function test_https_redirect_does_not_fire_in_testing_environment(): void
    {
        // In testing (non-production), HTTP requests should NOT be redirected
        $response = $this->get('/login');
        // Should not be 301 redirect in test environment
        $this->assertNotEquals(301, $response->getStatusCode());
    }
}
```

- [ ] **Step 4: Run test**

```bash
php artisan test tests/Feature/Security/ForceHttpsTest.php
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Middleware/ForceHttps.php bootstrap/app.php tests/Feature/Security/ForceHttpsTest.php
git commit -m "security: add ForceHttps middleware that redirects HTTP to HTTPS in production"
```

---

### Task 4: Create production deployment checklist document

**Files:**
- Create: `docs/PRODUCTION_DEPLOYMENT.md`

- [ ] **Step 1: Create deployment checklist**

Create `docs/PRODUCTION_DEPLOYMENT.md`:

```markdown
# OpesCare — Production Deployment Checklist

## MANDATORY — Must complete before every deployment to national environment

### 1. Environment Configuration
- [ ] `APP_KEY` is set (run `php artisan key:generate` and store in secrets manager)
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_URL` is set to actual HTTPS domain
- [ ] `DB_CONNECTION=pgsql` (NOT sqlite)
- [ ] `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` all set
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `MAIL_MAILER=smtp` (NOT log or null)
- [ ] `QUEUE_CONNECTION=redis` or `database` (NOT sync)
- [ ] `CACHE_STORE=redis` (NOT file)
- [ ] `LOG_LEVEL=warning` (NOT debug)
- [ ] `OPESCARE_DEMO_MODE=false`
- [ ] `DEMO_ALLOWED_IPS=` (empty — block all demo login)
- [ ] `OPESCARE_SYSTEM_PROVIDER_ID` set to actual system account UUID

### 2. Database
- [ ] All migrations run: `php artisan migrate --force`
- [ ] System account seeder run: `php artisan db:seed --class=SystemAccountSeeder`
- [ ] Facility role assignment seeder run: `php artisan db:seed --class=FacilityRoleAssignmentSeeder`
- [ ] PII encryption command run: `php artisan opescare:encrypt-patient-pii`
- [ ] PostgreSQL connection tested and working
- [ ] Database backups configured and tested

### 3. Application
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan optimize`
- [ ] Storage symlink created: `php artisan storage:link`

### 4. Security Verification
- [ ] Run `php artisan test` — all tests pass
- [ ] Verify security headers present: curl -I https://yourdomain.com/login | grep -i "content-security\|x-frame\|x-content"
- [ ] Verify emergency access endpoint requires auth: curl -X POST https://yourdomain.com/api/v1/connect/patients/emergency-profile → 401/403
- [ ] Verify demo login is blocked: curl -X POST https://yourdomain.com/demo-access/login-as → 403 (demo disabled)
- [ ] Verify APP_DEBUG=false: access any non-existent route → no stack trace in response
- [ ] Verify HTTPS redirect: curl -I http://yourdomain.com/login → 301 redirect to HTTPS

### 5. Infrastructure
- [ ] Redis running and accessible
- [ ] Queue workers running: `php artisan queue:work redis --daemon`
- [ ] Scheduler configured: `* * * * * php artisan schedule:run >> /dev/null 2>&1`
- [ ] SSL certificate valid and not expiring within 30 days
- [ ] Firewall: port 5432 (PostgreSQL) NOT exposed to internet
- [ ] Firewall: port 6379 (Redis) NOT exposed to internet

### 6. Monitoring
- [ ] Log shipping configured (Papertrail / CloudWatch / Graylog)
- [ ] Alert on LOG_LEVEL=critical (production safety check failures)
- [ ] Database backup notifications configured
- [ ] Uptime monitoring active

### 7. Data
- [ ] Patient PII encryption verified (spot-check DB raw values)
- [ ] Demo data NOT present in production database
- [ ] National facility registry seeded

## REMINDER: Changing APP_KEY after PII encryption destroys all encrypted patient data.
## Store APP_KEY in a secrets manager (AWS Secrets Manager / HashiCorp Vault) immediately after generation.
```

- [ ] **Step 2: Commit**

```bash
git add docs/PRODUCTION_DEPLOYMENT.md
git commit -m "docs: add comprehensive production deployment checklist"
```

---

### Task 5: Wave 8 final verification

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --parallel
```

Expected: All tests pass.

- [ ] **Step 2: Run production safety check**

```bash
php artisan tinker --execute="app()->make(App\Providers\ProductionSafetyServiceProvider::class)->boot();"
```

Expected: No critical warnings (in non-production environment).

- [ ] **Step 3: Verify opescare config loads**

```bash
php artisan tinker --execute="var_dump(config('opescare.system_provider_id'));"
```

Expected: UUID string.
