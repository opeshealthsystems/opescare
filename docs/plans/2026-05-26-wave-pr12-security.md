# Wave PR-12: Security Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add HSM/KMS encryption key management, secrets rotation runbook, CI dependency vulnerability scanning, WAF configuration documentation, and a formal threat model — all without modifying any existing module, controller, model, route, or migration.

**Architecture:** All five items are additive-only: new config keys, new Artisan commands, new CI files, new documentation files, and optional infrastructure-level WAF configuration. No existing files are deleted, renamed, or structurally modified. Migrations are guarded with `if (!Schema::hasColumn(...))`. Tests use SQLite + `RefreshDatabase`.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL (prod), SQLite (tests), AWS KMS SDK (optional), GitHub Actions CI, Cloudflare/AWS WAF (infrastructure layer, documented not coded).

---

## Canonical Rules (repeat in every wave)

1. **Never** delete, rename, or truncate any existing migration file.
2. **Never** remove a column from any existing `$fillable` or `$casts` array.
3. **Never** drop a table or column in a new migration — only ADD.
4. All new migrations guarded: `if (!Schema::hasTable('x'))` / `if (!Schema::hasColumn('x', 'y'))`.
5. Run `php artisan test` after every task — zero regressions allowed.
6. All new models use `HasUuids` trait.
7. `is_demo` must NOT appear in any model's `$fillable`.
8. Do not expose patient data publicly.

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `app/Services/Security/KmsEncryptionService.php` | Facade wrapper around AWS KMS (or local fallback) |
| Create | `app/Console/Commands/RotateSecretsCommand.php` | Artisan command + runbook scaffold |
| Create | `config/kms.php` | KMS driver config |
| Modify | `config/services.php` | Add `kms` block (additive only) |
| Modify | `.env.example` | Add KMS + rotation env vars (additive only) |
| Create | `tests/Feature/Security/KmsEncryptionServiceTest.php` | Unit tests for KMS wrapper |
| Create | `tests/Feature/Security/RotateSecretsCommandTest.php` | Command smoke tests |
| Create | `.github/workflows/security-scan.yml` | `composer audit` + `npm audit` in CI |
| Create | `.github/dependabot.yml` | Dependabot config for composer + npm + GitHub Actions |
| Create | `docs/waf-configuration.md` | Cloudflare/AWS WAF rule documentation |
| Create | `docs/secrets-rotation-runbook.md` | Step-by-step manual rotation guide |
| Create | `docs/threat-model.md` | STRIDE-based threat model for OpesCare |

---

### Task 1: KMS / HSM Encryption Key Management Wrapper

**Purpose:** Provide a Laravel service that encrypts/decrypts sensitive fields (e.g., SSNs, CNAMGS numbers, API secrets) via AWS KMS — with a local AES-256-GCM fallback for local/test environments. Existing encrypted fields remain untouched; this service is opt-in for new fields only.

**Files:**
- Create: `config/kms.php`
- Modify: `config/services.php` (additive only)
- Modify: `.env.example` (additive only)
- Create: `app/Services/Security/KmsEncryptionService.php`
- Create: `tests/Feature/Security/KmsEncryptionServiceTest.php`

---

- [ ] **Step 1.1: Write the failing test**

```php
<?php
// tests/Feature/Security/KmsEncryptionServiceTest.php
namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Services\Security\KmsEncryptionService;

class KmsEncryptionServiceTest extends TestCase
{
    private KmsEncryptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Force local driver for all tests
        config(['kms.driver' => 'local']);
        $this->service = app(KmsEncryptionService::class);
    }

    public function test_encrypt_returns_non_empty_string(): void
    {
        $plaintext = 'CNAMGS-123456789';
        $ciphertext = $this->service->encrypt($plaintext);

        $this->assertNotEmpty($ciphertext);
        $this->assertNotSame($plaintext, $ciphertext);
    }

    public function test_decrypt_roundtrip(): void
    {
        $plaintext = 'sensitive-patient-data';
        $ciphertext = $this->service->encrypt($plaintext);
        $decrypted  = $this->service->decrypt($ciphertext);

        $this->assertSame($plaintext, $decrypted);
    }

    public function test_encrypt_produces_different_ciphertext_each_call(): void
    {
        // Local driver uses random IV — each call differs
        $plaintext = 'same-input';
        $first  = $this->service->encrypt($plaintext);
        $second = $this->service->encrypt($plaintext);

        $this->assertNotSame($first, $second);
    }

    public function test_decrypt_throws_on_tampered_ciphertext(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->service->decrypt('not-valid-base64-ciphertext!!');
    }

    public function test_re_encrypt_changes_ciphertext(): void
    {
        $plaintext  = 'rotate-me';
        $old        = $this->service->encrypt($plaintext);
        $new        = $this->service->reEncrypt($old);

        $this->assertSame($plaintext, $this->service->decrypt($new));
        $this->assertNotSame($old, $new);
    }
}
```

- [ ] **Step 1.2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/Security/KmsEncryptionServiceTest.php --no-coverage
```

Expected: FAIL — `App\Services\Security\KmsEncryptionService` not found.

- [ ] **Step 1.3: Create `config/kms.php`**

```php
<?php
// config/kms.php
return [
    /*
    |--------------------------------------------------------------------------
    | KMS Driver
    |--------------------------------------------------------------------------
    | Supported: "local" (AES-256-GCM, uses APP_KEY), "aws" (AWS KMS)
    |
    | Set KMS_DRIVER=aws in production and configure the key below.
    */
    'driver' => env('KMS_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | AWS KMS Configuration
    |--------------------------------------------------------------------------
    */
    'aws' => [
        'key_id'  => env('KMS_AWS_KEY_ID'),   // arn:aws:kms:... or alias/opescare
        'region'  => env('KMS_AWS_REGION', 'eu-west-1'),
        'version' => 'latest',
    ],
];
```

- [ ] **Step 1.4: Add KMS block to `config/services.php` (additive)**

Open `config/services.php` and append the following **before** the closing `];`:

```php
    /*
    |--------------------------------------------------------------------------
    | KMS / HSM (see config/kms.php for full config)
    |--------------------------------------------------------------------------
    */
    'kms' => [
        'driver' => env('KMS_DRIVER', 'local'),
    ],
```

- [ ] **Step 1.5: Add env vars to `.env.example` (additive)**

Append at the bottom of `.env.example`:

```dotenv
# ── KMS / HSM encryption ─────────────────────────────────────────────────────
KMS_DRIVER=local
KMS_AWS_KEY_ID=
KMS_AWS_REGION=eu-west-1
```

- [ ] **Step 1.6: Create `KmsEncryptionService`**

```php
<?php
// app/Services/Security/KmsEncryptionService.php
namespace App\Services\Security;

use RuntimeException;

/**
 * Thin wrapper for field-level encryption.
 *
 * Driver "local"  → AES-256-GCM using APP_KEY (for dev / test).
 * Driver "aws"    → AWS KMS GenerateDataKey envelope encryption (production).
 *
 * This service is opt-in; no existing model or migration is touched.
 * Call encrypt() / decrypt() only on new fields added after this wave.
 */
class KmsEncryptionService
{
    private string $driver;

    public function __construct()
    {
        $this->driver = config('kms.driver', 'local');
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Encrypt plaintext.  Returns a base64-encoded string safe for storage.
     */
    public function encrypt(string $plaintext): string
    {
        return match ($this->driver) {
            'aws'   => $this->encryptAws($plaintext),
            default => $this->encryptLocal($plaintext),
        };
    }

    /**
     * Decrypt a ciphertext produced by encrypt().
     *
     * @throws RuntimeException on failure
     */
    public function decrypt(string $ciphertext): string
    {
        return match ($this->driver) {
            'aws'   => $this->decryptAws($ciphertext),
            default => $this->decryptLocal($ciphertext),
        };
    }

    /**
     * Re-encrypt — useful for key rotation: decrypt with old key, re-encrypt.
     * Under local driver this just produces a new IV (ciphertext changes).
     * Under AWS driver this generates a fresh data key.
     */
    public function reEncrypt(string $ciphertext): string
    {
        return $this->encrypt($this->decrypt($ciphertext));
    }

    // ─── Local Driver (AES-256-GCM) ──────────────────────────────────────────

    private function encryptLocal(string $plaintext): string
    {
        $key    = $this->localKey();
        $iv     = random_bytes(12); // 96-bit IV for GCM
        $tag    = '';

        $cipher = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($cipher === false) {
            throw new RuntimeException('Local encryption failed: ' . openssl_error_string());
        }

        // Pack: iv(12) + tag(16) + ciphertext
        return base64_encode($iv . $tag . $cipher);
    }

    private function decryptLocal(string $ciphertext): string
    {
        $raw = base64_decode($ciphertext, strict: true);

        if ($raw === false || strlen($raw) < 28) {
            throw new RuntimeException('KmsEncryptionService: invalid ciphertext format.');
        }

        $key    = $this->localKey();
        $iv     = substr($raw, 0, 12);
        $tag    = substr($raw, 12, 16);
        $data   = substr($raw, 28);

        $plain = openssl_decrypt(
            $data,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plain === false) {
            throw new RuntimeException('KmsEncryptionService: decryption failed — wrong key or tampered data.');
        }

        return $plain;
    }

    private function localKey(): string
    {
        // Derive a 32-byte key from APP_KEY
        $appKey = config('app.key');
        $raw    = base64_decode(str_replace('base64:', '', $appKey));
        return hash('sha256', $raw, true);
    }

    // ─── AWS Driver (envelope encryption) ────────────────────────────────────

    /**
     * AWS KMS envelope encryption:
     * 1. GenerateDataKey → (plaintext_key, ciphertext_key_blob)
     * 2. Encrypt payload with plaintext_key (AES-256-GCM, local)
     * 3. Store base64(ciphertext_key_blob + iv + tag + payload)
     *
     * Requires: aws/aws-sdk-php installed and KMS_AWS_KEY_ID configured.
     */
    private function encryptAws(string $plaintext): string
    {
        $kms = $this->awsClient();

        $result = $kms->generateDataKey([
            'KeyId'   => config('kms.aws.key_id'),
            'KeySpec' => 'AES_256',
        ]);

        $dataKey    = (string) $result['Plaintext'];
        $encDataKey = (string) $result['CiphertextBlob'];

        $iv  = random_bytes(12);
        $tag = '';

        $cipher = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $dataKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        sodium_memzero($dataKey);

        // Pack: len(4) + encDataKey + iv(12) + tag(16) + cipher
        $encKeyLen = strlen($encDataKey);
        $packed    = pack('N', $encKeyLen) . $encDataKey . $iv . $tag . $cipher;

        return base64_encode($packed);
    }

    private function decryptAws(string $ciphertext): string
    {
        $raw = base64_decode($ciphertext, strict: true);

        if ($raw === false || strlen($raw) < 32) {
            throw new RuntimeException('KmsEncryptionService: invalid AWS ciphertext format.');
        }

        $encKeyLen = unpack('N', substr($raw, 0, 4))[1];
        $offset    = 4;
        $encDataKey = substr($raw, $offset, $encKeyLen);
        $offset    += $encKeyLen;
        $iv         = substr($raw, $offset, 12);
        $offset    += 12;
        $tag        = substr($raw, $offset, 16);
        $offset    += 16;
        $data       = substr($raw, $offset);

        $kms    = $this->awsClient();
        $result = $kms->decrypt(['CiphertextBlob' => $encDataKey]);
        $dataKey = (string) $result['Plaintext'];

        $plain = openssl_decrypt(
            $data,
            'aes-256-gcm',
            $dataKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        sodium_memzero($dataKey);

        if ($plain === false) {
            throw new RuntimeException('KmsEncryptionService: AWS decryption failed.');
        }

        return $plain;
    }

    private function awsClient(): \Aws\Kms\KmsClient
    {
        if (!class_exists(\Aws\Kms\KmsClient::class)) {
            throw new RuntimeException(
                'AWS SDK not installed. Run: composer require aws/aws-sdk-php'
            );
        }

        return new \Aws\Kms\KmsClient([
            'version' => config('kms.aws.version', 'latest'),
            'region'  => config('kms.aws.region', 'eu-west-1'),
        ]);
    }
}
```

- [ ] **Step 1.7: Run tests to confirm they pass**

```bash
php artisan test tests/Feature/Security/KmsEncryptionServiceTest.php --no-coverage
```

Expected: 5 tests, 5 assertions — all PASS.

- [ ] **Step 1.8: Run full suite to confirm no regressions**

```bash
php artisan test --no-coverage
```

Expected: all existing tests pass.

- [ ] **Step 1.9: Commit**

```bash
git add config/kms.php config/services.php .env.example \
        app/Services/Security/KmsEncryptionService.php \
        tests/Feature/Security/KmsEncryptionServiceTest.php
git commit -m "feat(security): add KMS/HSM encryption service with local+AWS drivers"
```

---

### Task 2: Secrets Rotation Artisan Command + Runbook

**Purpose:** Provide `php artisan opescare:rotate-secrets --check` (dry-run, reports which secrets are nearing expiry) and a written runbook that operations staff can follow for manual/automated rotation of DB passwords, API keys, and the APP_KEY.

**Files:**
- Create: `app/Console/Commands/RotateSecretsCommand.php`
- Create: `docs/secrets-rotation-runbook.md`
- Create: `tests/Feature/Security/RotateSecretsCommandTest.php`

---

- [ ] **Step 2.1: Write the failing test**

```php
<?php
// tests/Feature/Security/RotateSecretsCommandTest.php
namespace Tests\Feature\Security;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class RotateSecretsCommandTest extends TestCase
{
    public function test_check_flag_reports_secrets_status(): void
    {
        // Set a known "last rotated" timestamp far in the past
        Cache::put('secrets.last_rotated.app_key', now()->subDays(100)->toIso8601String());
        Cache::put('secrets.last_rotated.db_password', now()->subDays(45)->toIso8601String());

        $this->artisan('opescare:rotate-secrets --check')
             ->assertExitCode(0);
    }

    public function test_check_flag_exits_without_modifying_secrets(): void
    {
        $originalAppKey = config('app.key');

        $this->artisan('opescare:rotate-secrets --check')
             ->assertExitCode(0);

        // APP_KEY must not change during --check
        $this->assertSame($originalAppKey, config('app.key'));
    }

    public function test_command_outputs_secret_names(): void
    {
        $this->artisan('opescare:rotate-secrets --check')
             ->expectsOutputToContain('app_key')
             ->assertExitCode(0);
    }

    public function test_status_command_without_flag_shows_instructions(): void
    {
        $this->artisan('opescare:rotate-secrets')
             ->expectsOutputToContain('--check')
             ->assertExitCode(0);
    }
}
```

- [ ] **Step 2.2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/Security/RotateSecretsCommandTest.php --no-coverage
```

Expected: FAIL — command not found.

- [ ] **Step 2.3: Create `RotateSecretsCommand`**

```php
<?php
// app/Console/Commands/RotateSecretsCommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Secrets Rotation Command
 *
 * Usage:
 *   php artisan opescare:rotate-secrets --check   # Report status, no changes
 *
 * Actual rotation steps are documented in docs/secrets-rotation-runbook.md.
 * This command NEVER auto-rotates — it only validates and reports.
 * Rotation requires deliberate human action per the runbook.
 */
class RotateSecretsCommand extends Command
{
    protected $signature = 'opescare:rotate-secrets
                            {--check : Report rotation status without making any changes}';

    protected $description = 'Report secrets rotation status. Use --check to see which secrets need rotation.';

    /** Rotation interval thresholds (days) */
    private array $thresholds = [
        'app_key'           => 90,
        'db_password'       => 60,
        'api_key_mtn_momo'  => 180,
        'api_key_orange'    => 180,
        'kms_data_keys'     => 365,
    ];

    public function handle(): int
    {
        if (!$this->option('check')) {
            $this->warn('No action taken.');
            $this->line('');
            $this->line('This command does NOT auto-rotate secrets.');
            $this->line('To check rotation status: php artisan opescare:rotate-secrets <fg=yellow>--check</>');
            $this->line('For rotation steps:       see <fg=cyan>docs/secrets-rotation-runbook.md</>');
            return self::SUCCESS;
        }

        $this->info('Checking secrets rotation status...');
        $this->line('');

        $rows         = [];
        $overdueCount = 0;

        foreach ($this->thresholds as $secret => $maxDays) {
            $lastRotatedKey = "secrets.last_rotated.{$secret}";
            $lastRotated    = Cache::get($lastRotatedKey);

            if ($lastRotated === null) {
                $status   = '<fg=yellow>UNKNOWN — never recorded</>';
                $daysSince = '?';
            } else {
                $dt        = \Carbon\Carbon::parse($lastRotated);
                $daysSince = (int) $dt->diffInDays(now());
                $overdue   = $daysSince >= $maxDays;

                if ($overdue) {
                    $status = "<fg=red>OVERDUE ({$daysSince} / {$maxDays} days)</>";
                    $overdueCount++;
                } else {
                    $remaining = $maxDays - $daysSince;
                    $status    = "<fg=green>OK — rotates in {$remaining} days</>";
                }
            }

            $rows[] = [$secret, $maxDays . 'd', $daysSince, $status];
        }

        $this->table(
            ['Secret', 'Max Age', 'Days Since', 'Status'],
            $rows
        );

        $this->line('');

        if ($overdueCount > 0) {
            $this->error("{$overdueCount} secret(s) need rotation. See docs/secrets-rotation-runbook.md.");
            Log::warning('opescare:rotate-secrets: overdue secrets detected', ['count' => $overdueCount]);
        } else {
            $this->info('All tracked secrets are within rotation policy.');
        }

        $this->line('');
        $this->line('To record a completed rotation:');
        $this->line('  Cache::put(\'secrets.last_rotated.<name>\', now()->toIso8601String(), 400 * 24 * 60);');

        return self::SUCCESS;
    }
}
```

- [ ] **Step 2.4: Register the command in `app/Console/Kernel.php`**

Open `app/Console/Kernel.php`. In the `$commands` array (or the `commands()` method), add:

```php
\App\Console\Commands\RotateSecretsCommand::class,
```

> If the project uses auto-discovery (Laravel 13 default), skip this step — the command is auto-registered.

- [ ] **Step 2.5: Run tests**

```bash
php artisan test tests/Feature/Security/RotateSecretsCommandTest.php --no-coverage
```

Expected: 4 tests, all PASS.

- [ ] **Step 2.6: Create `docs/secrets-rotation-runbook.md`**

```markdown
# OpesCare Secrets Rotation Runbook

**Version:** 1.0  
**Owner:** Platform Engineering  
**Review cadence:** Quarterly  

---

## Overview

This runbook defines the step-by-step procedure for rotating all secrets
managed by the OpesCare platform.  Rotation is triggered by:

- Routine schedule (see rotation intervals below)
- Suspected compromise
- Staff departure with secret access
- Vendor credential revocation

**Never auto-rotate** without a change-control record.  Every rotation
must be logged in the audit trail.

---

## Rotation Intervals

| Secret | Max Age | Command to Check |
|--------|---------|-----------------|
| `APP_KEY` | 90 days | `php artisan opescare:rotate-secrets --check` |
| DB password | 60 days | `php artisan opescare:rotate-secrets --check` |
| MTN MoMo API key | 180 days | `php artisan opescare:rotate-secrets --check` |
| Orange Money API key | 180 days | `php artisan opescare:rotate-secrets --check` |
| KMS data keys (AWS) | 365 days | AWS Console → KMS → Enable automatic rotation |

---

## Pre-Rotation Checklist

- [ ] Open a change-control ticket referencing this runbook
- [ ] Notify on-call engineer
- [ ] Verify DB read replica is in sync before rotating DB credentials
- [ ] Schedule rotation during low-traffic window (02:00–04:00 WAT)

---

## Procedure 1: Rotate APP_KEY

> Rotating APP_KEY invalidates all existing encrypted cookies and sessions.
> All users will be logged out.

1. Generate a new key:
   ```bash
   php artisan key:generate --show
   # Copy the output: base64:xxxx...
   ```

2. Update the secret in your secrets manager (AWS Secrets Manager / HashiCorp Vault):
   ```bash
   aws secretsmanager update-secret \
     --secret-id opescare/app_key \
     --secret-string "base64:xxxx..."
   ```

3. Deploy the new key to all application servers (rolling deployment preferred).

4. Verify the application boots:
   ```bash
   php artisan config:cache && php artisan route:cache
   curl -sf https://your-domain.com/api/health | jq .
   ```

5. Record the rotation:
   ```php
   php artisan tinker
   Cache::put('secrets.last_rotated.app_key', now()->toIso8601String(), 400 * 24 * 60);
   ```

6. Close change-control ticket.

---

## Procedure 2: Rotate DB Password

1. Generate a new strong password (≥ 32 characters, all character classes):
   ```bash
   openssl rand -base64 32
   ```

2. Update the password in PostgreSQL (do NOT drop old user):
   ```sql
   ALTER USER opescare_app PASSWORD 'new_password_here';
   ```

3. Update the secret in AWS Secrets Manager:
   ```bash
   aws secretsmanager update-secret \
     --secret-id opescare/db_password \
     --secret-string "new_password_here"
   ```

4. Cycle the application connection (zero-downtime):
   - Deploy the new `DB_PASSWORD` env var via a rolling restart.
   - Verify health: `curl https://your-domain.com/api/health | jq .checks.database`

5. Record the rotation:
   ```php
   Cache::put('secrets.last_rotated.db_password', now()->toIso8601String(), 400 * 24 * 60);
   ```

---

## Procedure 3: Rotate Mobile Money API Keys

### MTN MoMo

1. Log in to MTN MoMo Developer Portal.
2. Regenerate the API user credentials.
3. Update `.env` / AWS Secrets Manager:
   - `MTN_MOMO_API_USER`
   - `MTN_MOMO_API_KEY`
4. Deploy → verify a test payment completes in staging.
5. Record: `Cache::put('secrets.last_rotated.api_key_mtn_momo', now()->toIso8601String(), 400 * 24 * 60);`

### Orange Money

1. Log in to Orange Money partner portal.
2. Regenerate API secret.
3. Update `ORANGE_MONEY_SECRET` in secrets manager.
4. Deploy → verify in staging.
5. Record: `Cache::put('secrets.last_rotated.api_key_orange', now()->toIso8601String(), 400 * 24 * 60);`

---

## Procedure 4: Rotate AWS KMS Data Keys

Enable AWS KMS automatic annual rotation:

```bash
aws kms enable-key-rotation \
  --key-id $(aws kms describe-key --key-id alias/opescare --query 'KeyMetadata.KeyId' --output text)
```

AWS will rotate the backing key material annually.  Existing ciphertexts
remain decryptable.  No application restart is needed.

Record: `Cache::put('secrets.last_rotated.kms_data_keys', now()->toIso8601String(), 400 * 24 * 60);`

---

## Post-Rotation Verification

- [ ] `curl https://your-domain.com/api/health` → `{"status":"ok"}`
- [ ] Run smoke tests: `php artisan test --filter=HealthCheck`
- [ ] Verify monitoring dashboards show no DB connection errors
- [ ] Check application logs for auth failures in the 10 minutes after rotation
- [ ] Run `php artisan opescare:rotate-secrets --check` and confirm all secrets show OK

---

## Incident: Suspected Compromise

If a secret is suspected to be compromised, rotate IMMEDIATELY regardless
of schedule.  Then:

1. Revoke the old credential at the source (MTN portal, AWS IAM, Postgres, etc.)
2. Review `security_audit_logs` table for suspicious activity in the past 30 days
3. Notify the Data Protection Officer per the Incident Response Plan
4. File a post-incident review within 72 hours
```

- [ ] **Step 2.7: Run full test suite**

```bash
php artisan test --no-coverage
```

Expected: all tests pass.

- [ ] **Step 2.8: Commit**

```bash
git add app/Console/Commands/RotateSecretsCommand.php \
        docs/secrets-rotation-runbook.md \
        tests/Feature/Security/RotateSecretsCommandTest.php
git commit -m "feat(security): add secrets rotation command and runbook"
```

---

### Task 3: CI Dependency Vulnerability Scanning

**Purpose:** Add `composer audit` + `npm audit` to GitHub Actions CI, and configure Dependabot to automatically open PRs for outdated composer, npm, and GitHub Actions dependencies. Zero existing code modified.

**Files:**
- Create: `.github/workflows/security-scan.yml`
- Create: `.github/dependabot.yml`
- Create: `tests/Feature/Security/ComposerAuditTest.php`

---

- [ ] **Step 3.1: Write a smoke test that verifies the composer.lock exists**

```php
<?php
// tests/Feature/Security/ComposerAuditTest.php
namespace Tests\Feature\Security;

use Tests\TestCase;

class ComposerAuditTest extends TestCase
{
    /**
     * composer.lock must exist for `composer audit` to work.
     * This test catches accidental deletion of the lock file.
     */
    public function test_composer_lock_exists(): void
    {
        $this->assertFileExists(base_path('composer.lock'));
    }

    /**
     * composer.json must declare a minimum PHP version.
     * This prevents accidental removal of the platform requirement.
     */
    public function test_composer_json_declares_php_constraint(): void
    {
        $json = json_decode(file_get_contents(base_path('composer.json')), true);

        $this->assertArrayHasKey('require', $json);
        $this->assertArrayHasKey('php', $json['require']);
    }
}
```

- [ ] **Step 3.2: Run test to confirm it passes already (no FAIL expected)**

```bash
php artisan test tests/Feature/Security/ComposerAuditTest.php --no-coverage
```

Expected: 2 tests PASS (composer.lock and composer.json already exist).

- [ ] **Step 3.3: Create `.github/workflows/security-scan.yml`**

```yaml
# .github/workflows/security-scan.yml
name: Security Scan

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]
  schedule:
    # Run every Monday at 07:00 UTC
    - cron: '0 7 * * 1'

jobs:
  composer-audit:
    name: Composer Vulnerability Audit
    runs-on: ubuntu-24.04

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          tools: composer:v2

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader

      - name: Run composer audit
        # Exit code 1 if any vulnerability found at HIGH or CRITICAL level
        run: composer audit --format=json --no-interaction 2>&1 | tee /tmp/composer-audit.json
        continue-on-error: false

      - name: Upload audit report
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: composer-audit-report
          path: /tmp/composer-audit.json
          retention-days: 30

  npm-audit:
    name: NPM Vulnerability Audit
    runs-on: ubuntu-24.04
    # Only runs if package.json exists (Blade/API project may not have one)
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Check for package.json
        id: check_npm
        run: |
          if [ -f package.json ]; then
            echo "has_npm=true" >> $GITHUB_OUTPUT
          else
            echo "has_npm=false" >> $GITHUB_OUTPUT
          fi

      - name: Set up Node
        if: steps.check_npm.outputs.has_npm == 'true'
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: NPM install
        if: steps.check_npm.outputs.has_npm == 'true'
        run: npm ci --ignore-scripts

      - name: NPM audit
        if: steps.check_npm.outputs.has_npm == 'true'
        run: npm audit --audit-level=high

  laravel-tests:
    name: Laravel Test Suite (with security)
    runs-on: ubuntu-24.04
    needs: [composer-audit]

    services:
      pgsql:
        image: postgres:16
        env:
          POSTGRES_DB: opescare_test
          POSTGRES_USER: opescare
          POSTGRES_PASSWORD: secret
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo_pgsql, pdo_sqlite, redis
          tools: composer:v2

      - name: Copy env
        run: cp .env.example .env.testing

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader

      - name: Generate key
        run: php artisan key:generate --env=testing

      - name: Run tests
        env:
          DB_CONNECTION: sqlite
          DB_DATABASE: ':memory:'
        run: php artisan test --no-coverage --parallel
```

- [ ] **Step 3.4: Create `.github/dependabot.yml`**

```yaml
# .github/dependabot.yml
version: 2

updates:
  # PHP / Composer dependencies
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
      day: "monday"
      time: "07:00"
      timezone: "Africa/Libreville"
    open-pull-requests-limit: 10
    labels:
      - "dependencies"
      - "php"
    commit-message:
      prefix: "chore(deps)"
    ignore:
      # Ignore major version bumps on Laravel core — handle manually
      - dependency-name: "laravel/framework"
        update-types: ["version-update:semver-major"]

  # GitHub Actions
  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "weekly"
      day: "monday"
      time: "07:00"
      timezone: "Africa/Libreville"
    open-pull-requests-limit: 5
    labels:
      - "dependencies"
      - "github-actions"
    commit-message:
      prefix: "chore(ci)"

  # NPM (if applicable)
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
      day: "monday"
      time: "07:00"
      timezone: "Africa/Libreville"
    open-pull-requests-limit: 10
    labels:
      - "dependencies"
      - "javascript"
    commit-message:
      prefix: "chore(deps)"
    ignore:
      # Ignore minor/patch updates for stable frontend packages
      - dependency-name: "vite"
        update-types: ["version-update:semver-minor", "version-update:semver-patch"]
```

- [ ] **Step 3.5: Run full test suite**

```bash
php artisan test --no-coverage
```

Expected: all tests pass.

- [ ] **Step 3.6: Commit**

```bash
git add .github/workflows/security-scan.yml \
        .github/dependabot.yml \
        tests/Feature/Security/ComposerAuditTest.php
git commit -m "feat(security): add CI composer/npm audit and Dependabot config"
```

---

### Task 4: WAF Configuration Documentation

**Purpose:** Document the recommended Cloudflare / AWS WAF rule sets, rate-limiting rules, bot management settings, and IP allowlisting procedure for the OpesCare API. This is a documentation-only task — no code is deployed (WAF sits at the infrastructure layer). A smoke test verifies the doc file exists.

**Files:**
- Create: `docs/waf-configuration.md`
- Create: `tests/Feature/Security/WafDocumentationTest.php`

---

- [ ] **Step 4.1: Write the test**

```php
<?php
// tests/Feature/Security/WafDocumentationTest.php
namespace Tests\Feature\Security;

use Tests\TestCase;

class WafDocumentationTest extends TestCase
{
    public function test_waf_configuration_doc_exists(): void
    {
        $this->assertFileExists(base_path('docs/waf-configuration.md'));
    }

    public function test_waf_doc_contains_required_sections(): void
    {
        $content = file_get_contents(base_path('docs/waf-configuration.md'));

        $this->assertStringContainsString('Rate Limiting', $content);
        $this->assertStringContainsString('Bot Management', $content);
        $this->assertStringContainsString('IP Allowlist', $content);
        $this->assertStringContainsString('OWASP', $content);
    }
}
```

- [ ] **Step 4.2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/Security/WafDocumentationTest.php --no-coverage
```

Expected: FAIL — file not found.

- [ ] **Step 4.3: Create `docs/waf-configuration.md`**

```markdown
# OpesCare WAF Configuration Guide

**Version:** 1.0  
**Owner:** Platform Engineering  
**Review cadence:** Quarterly or after any security incident  

---

## Overview

OpesCare uses a Web Application Firewall (WAF) in front of all public API
endpoints and the patient portal.  This guide covers both Cloudflare WAF
(primary recommendation) and AWS WAF (alternative for AWS-hosted deployments).

---

## Architecture

```
Internet → Cloudflare WAF → Load Balancer → Laravel API (Nginx/FPM)
```

All traffic to `api.opescare.com` and `portal.opescare.com` passes through
the WAF.  Internal service-to-service calls bypass the WAF via VPC peering.

---

## 1. OWASP Core Rule Set

Enable the OWASP ModSecurity Core Rule Set (CRS) managed rules.

### Cloudflare

1. Dashboard → Security → WAF → Managed Rules
2. Enable **Cloudflare OWASP Core Ruleset** (version 3.3+)
3. Set **Paranoia Level: 1** (start here; increase after 30-day observation)
4. Set **Anomaly Score Threshold: 25** (default)
5. Deploy in **Log** mode for 72 hours → review false positives → switch to **Block**

### AWS WAF (alternative)

```hcl
resource "aws_wafv2_web_acl" "opescare" {
  name  = "opescare-waf"
  scope = "REGIONAL"

  default_action { allow {} }

  rule {
    name     = "AWSManagedRulesCommonRuleSet"
    priority = 10
    override_action { none {} }
    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesCommonRuleSet"
        vendor_name = "AWS"
      }
    }
    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "CommonRuleSet"
      sampled_requests_enabled   = true
    }
  }
}
```

---

## 2. Rate Limiting

Rate limiting is applied at two layers:
1. **WAF layer** — coarse, IP-based (this document)
2. **Application layer** — fine-grained, per-user/partner (see `wave-pr10-infrastructure`)

### Cloudflare Rate Limiting Rules

| Rule Name | Match | Threshold | Period | Action |
|-----------|-------|-----------|--------|--------|
| `api-global` | `hostname eq api.opescare.com` | 1000 req | 1 min | Block 60s |
| `api-auth-endpoints` | `uri_path contains "/api/auth"` | 20 req | 1 min | Block 300s |
| `api-password-reset` | `uri_path contains "/password/reset"` | 5 req | 5 min | Block 3600s |
| `portal-login` | `uri_path contains "/login"` | 10 req | 1 min | Block 300s |
| `api-public-health` | `uri_path eq "/api/health"` | 100 req | 1 min | Log only |

Configuration (Cloudflare Terraform):

```hcl
resource "cloudflare_ruleset" "rate_limits" {
  zone_id     = var.cloudflare_zone_id
  name        = "OpesCare Rate Limits"
  description = "Per-endpoint rate limiting"
  kind        = "zone"
  phase       = "http_ratelimit"

  rules {
    action = "block"
    ratelimit {
      characteristics        = ["ip.src"]
      period                 = 60
      requests_per_period    = 1000
      mitigation_timeout     = 60
    }
    expression  = "(http.host eq \"api.opescare.com\")"
    description = "api-global"
    enabled     = true
  }

  rules {
    action = "block"
    ratelimit {
      characteristics        = ["ip.src"]
      period                 = 60
      requests_per_period    = 20
      mitigation_timeout     = 300
    }
    expression  = "(http.host eq \"api.opescare.com\" and http.request.uri.path contains \"/api/auth\")"
    description = "api-auth-endpoints"
    enabled     = true
  }
}
```

---

## 3. Bot Management

### Cloudflare Bot Fight Mode

1. Dashboard → Security → Bots
2. Enable **Bot Fight Mode** (free) or **Super Bot Fight Mode** (Pro+)
3. Configure:
   - **Definitely Automated**: Block
   - **Likely Automated**: Challenge (JS challenge for portal, block for API)
   - **Verified Bots** (Google, Bing): Allow

### Allowed Bot Patterns

The following are explicitly allowed by User-Agent and/or IP:

| Bot | Reason | Allowlist method |
|-----|--------|-----------------|
| Dependabot | CI/CD dependency scanning | GitHub IP ranges |
| UptimeRobot | Health monitoring | Static IP allowlist |
| Internal health checks | Load balancer probes | VPC IP allowlist |

---

## 4. IP Allowlist / Blocklist

### Allowlist (Bypass WAF)

These CIDRs bypass WAF inspection (still logged):

- Internal VPC CIDR: `10.0.0.0/8`
- Office NAT: configure per deployment in Cloudflare → Security → WAF → Custom Rules
- CI/CD runners: GitHub Actions IP ranges (see https://api.github.com/meta)

### Blocklist (Auto-block)

Cloudflare Managed IP Threat Lists:
- Enable **Cloudflare IP Reputation** → Block Score ≥ 75
- Subscribe to **Cloudflare Radar** threat feed (Enterprise)

### Emergency Block Procedure

To block a specific IP immediately:

```bash
# Cloudflare API
curl -X POST "https://api.cloudflare.com/client/v4/zones/{ZONE_ID}/firewall/rules" \
  -H "Authorization: Bearer ${CF_API_TOKEN}" \
  -H "Content-Type: application/json" \
  --data '[{
    "action": "block",
    "filter": {
      "expression": "(ip.src eq 1.2.3.4)",
      "description": "Emergency block - incident #NNN"
    }
  }]'
```

---

## 5. TLS / HTTPS Configuration

- Minimum TLS version: **TLS 1.2** (TLS 1.3 preferred)
- Cipher suites: Follow Mozilla Modern compatibility profile
- HSTS header: `max-age=31536000; includeSubDomains; preload`
- Certificate type: Cloudflare-managed (auto-renewal) or ACM (AWS)

Cloudflare SSL/TLS settings:
1. Dashboard → SSL/TLS → Overview → **Full (strict)**
2. Dashboard → SSL/TLS → Edge Certificates → **Always Use HTTPS**: ON
3. Dashboard → SSL/TLS → Edge Certificates → **Minimum TLS Version**: TLS 1.2
4. Dashboard → SSL/TLS → Edge Certificates → **HSTS**: Enable, 12 months, includeSubDomains

---

## 6. Monitoring and Alerting

### Metrics to Monitor

| Metric | Threshold | Alert |
|--------|-----------|-------|
| WAF blocks per minute | > 100 | PagerDuty P2 |
| 401/403 rate | > 5% of requests | Slack #security |
| Rate-limit triggers | > 50 in 5 min | Slack #security |
| New country accessing API | Any new country | Slack #security (review) |

### Cloudflare Analytics Dashboard

1. Dashboard → Analytics & Logs → Security Events
2. Filter by action: **Block**, **Challenge**
3. Set up email digest: Weekly summary of blocked requests

---

## 7. Incident Response (WAF)

### WAF False Positive

1. Identify the request in Cloudflare Security Events log
2. Note the Ray ID and the triggered rule ID
3. Add a WAF exception (Skip Rule) scoped to the specific URI path
4. Document the exception in this file under "Known Exceptions"
5. Create a ticket to review the exception after 30 days

### Under Active Attack

1. Enable **Under Attack Mode** (Cloudflare): Dashboard → Overview → Under Attack Mode
2. Alert the on-call engineer via PagerDuty
3. Increase rate-limit strictness (lower thresholds by 50%)
4. Review `security_audit_logs` table for correlated application events
5. If attack persists > 30 minutes, engage Cloudflare Support

---

## Known WAF Exceptions

| Date | URI Pattern | Rule Bypassed | Reason | Review Date |
|------|-------------|--------------|--------|-------------|
| (none yet) | | | | |

---

## Review History

| Date | Reviewer | Changes |
|------|----------|---------|
| 2026-05-26 | Platform Engineering | Initial version |
```

- [ ] **Step 4.4: Run tests**

```bash
php artisan test tests/Feature/Security/WafDocumentationTest.php --no-coverage
```

Expected: 2 tests, all PASS.

- [ ] **Step 4.5: Run full suite**

```bash
php artisan test --no-coverage
```

Expected: all existing tests pass.

- [ ] **Step 4.6: Commit**

```bash
git add docs/waf-configuration.md \
        tests/Feature/Security/WafDocumentationTest.php
git commit -m "docs(security): add WAF configuration guide (Cloudflare + AWS WAF)"
```

---

### Task 5: Formal Threat Model Document (STRIDE)

**Purpose:** Create a formal STRIDE-based threat model for OpesCare covering all data flows, trust boundaries, assets, and mitigations. This is a documentation-only task. A smoke test verifies the document exists and contains the required sections.

**Files:**
- Create: `docs/threat-model.md`
- Create: `tests/Feature/Security/ThreatModelTest.php`

---

- [ ] **Step 5.1: Write the test**

```php
<?php
// tests/Feature/Security/ThreatModelTest.php
namespace Tests\Feature\Security;

use Tests\TestCase;

class ThreatModelTest extends TestCase
{
    public function test_threat_model_doc_exists(): void
    {
        $this->assertFileExists(base_path('docs/threat-model.md'));
    }

    public function test_threat_model_contains_stride_categories(): void
    {
        $content = file_get_contents(base_path('docs/threat-model.md'));

        foreach (['Spoofing', 'Tampering', 'Repudiation', 'Information Disclosure', 'Denial of Service', 'Elevation of Privilege'] as $category) {
            $this->assertStringContainsString($category, $content, "Missing STRIDE category: {$category}");
        }
    }

    public function test_threat_model_contains_data_flow_section(): void
    {
        $content = file_get_contents(base_path('docs/threat-model.md'));
        $this->assertStringContainsString('Data Flow', $content);
        $this->assertStringContainsString('Trust Boundary', $content);
    }

    public function test_threat_model_contains_mitigations(): void
    {
        $content = file_get_contents(base_path('docs/threat-model.md'));
        $this->assertStringContainsString('Mitigation', $content);
    }
}
```

- [ ] **Step 5.2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/Security/ThreatModelTest.php --no-coverage
```

Expected: FAIL — file not found.

- [ ] **Step 5.3: Create `docs/threat-model.md`**

```markdown
# OpesCare Threat Model

**Version:** 1.0  
**Classification:** Internal — Confidential  
**Owner:** CISO / Platform Engineering  
**Review cadence:** Annually and after major architecture changes  
**Methodology:** STRIDE (Spoofing, Tampering, Repudiation, Information Disclosure, Denial of Service, Elevation of Privilege)

---

## 1. System Overview

OpesCare is a multi-tenant Electronic Health Records (EHR) platform serving
healthcare facilities in Gabon and the CEMAC region.  The platform handles:

- Protected Health Information (PHI) for patients
- Clinical workflows (appointments, prescriptions, lab results, imaging)
- Billing and insurance claims including mobile money transactions
- Integration with DHIS2, HL7 feeds, CNAMGS national insurance, and PACS

**Regulatory context:** Gabon Law No. 025/2019 on personal data protection;
HIPAA-aligned best practices (not legally required in Gabon but adopted as
a quality standard).

---

## 2. Assets

| Asset | Sensitivity | Location |
|-------|-------------|----------|
| Patient PHI (demographics, diagnoses, medications) | Critical | PostgreSQL `patients`, `medical_records`, `prescriptions` |
| CNAMGS / National ID numbers | Critical | PostgreSQL `patients.cnamgs_number` (encrypted at rest) |
| Lab and imaging results | High | PostgreSQL `lab_results`, `dicom_studies` |
| Authentication credentials | Critical | PostgreSQL `users.password` (bcrypt), sessions |
| API keys (mobile money, DHIS2) | Critical | `.env` / AWS Secrets Manager |
| APP_KEY (Laravel encryption) | Critical | `.env` / AWS Secrets Manager |
| Audit logs | High | PostgreSQL `security_audit_logs` (immutable) |
| Medical record PDFs | High | Local filesystem / S3 (encrypted) |

---

## 3. Data Flow Diagram (Textual)

### Trust Boundaries

```
[Internet] ──► [Cloudflare WAF] ──► [Load Balancer] ──┐
                                                        │
[DHIS2 Server] ──────────────────────────────────────  │
[HL7 Sender]   ──────────────────────────────────────  │
[CNAMGS API]   ──────────────────────────────────────  ├─► [Laravel API (App Server)]
[MTN MoMo API] ──────────────────────────────────────  │        │
[Orange Money] ──────────────────────────────────────  │        ├─► [PostgreSQL Primary]
                                                        │        │        └─► [PostgreSQL Replica]
[Patient Browser / Mobile App] ──────────────────────  │        ├─► [Redis (Queue + Cache)]
[Provider Web App] ──────────────────────────────────  │        ├─► [S3 / Local Storage]
[Admin Dashboard] ───────────────────────────────────  │        └─► [SMTP / SMS (Vonage)]
```

**Trust Boundaries:**
1. **Public Internet → WAF**: Untrusted; all traffic inspected
2. **WAF → App Server**: Semi-trusted; rate-limited and filtered
3. **App Server → DB**: Trusted internal; TLS in transit
4. **App Server → External APIs**: Semi-trusted; credentials required
5. **Admin Network → App Server**: Privileged; restricted by IP allowlist

---

## 4. STRIDE Threat Analysis

### 4.1 Spoofing

| ID | Threat | Asset | Mitigation | Status |
|----|--------|-------|-----------|--------|
| S-01 | Attacker impersonates a patient to access their records | Patient portal session | JWT/session auth + CSRF tokens; account lockout after 5 failures | ✅ Mitigated |
| S-02 | Attacker spoofs Integration Client ID header | API rate limiter | `X-Integration-Client-Id` is validated against DB on each request | ✅ Mitigated |
| S-03 | Forged HL7 ADT message with fake patient data | HL7 ingest endpoint | Endpoint is internal-only (IP restricted); messages are validated and logged | ✅ Mitigated |
| S-04 | Attacker spoofs DHIS2 callback | DHIS2 push queue | Outbound only — OpesCare pushes to DHIS2, no inbound callback accepted | ✅ Mitigated |
| S-05 | Phishing attack against provider login | Provider credentials | 2FA recommended for all provider accounts (enforced for Admin role) | ⚠️ Partial — 2FA optional for non-admin providers |

**Action required for S-05:** Enforce 2FA for all provider roles in a future wave.

### 4.2 Tampering

| ID | Threat | Asset | Mitigation | Status |
|----|--------|-------|-----------|--------|
| T-01 | SQL injection via API input | All DB tables | Eloquent parameterized queries; no raw SQL with user input | ✅ Mitigated |
| T-02 | Modification of audit log records | `security_audit_logs` | Model overrides `update()` to throw `\LogicException` (immutable) | ✅ Mitigated |
| T-03 | Modification of controlled substance records | `controlled_substance_records` | Same immutable pattern as audit logs | ✅ Mitigated |
| T-04 | Mass assignment on sensitive models | Patient PHI | `$guarded = ['*']` or explicit `$fillable`; `is_demo` excluded | ✅ Mitigated |
| T-05 | Tampering with PDF medical records in transit | Generated PDFs | HTTPS enforced end-to-end; S3 bucket versioning enabled | ✅ Mitigated |
| T-06 | Migration rollback deletes clinical data | All tables | All migrations additive-only; no `dropColumn` / `dropTable` in codebase | ✅ Mitigated |

### 4.3 Repudiation

| ID | Threat | Asset | Mitigation | Status |
|----|--------|-------|-----------|--------|
| R-01 | Provider denies placing a prescription | `prescriptions` | `security_audit_logs` records all create/update with user ID and timestamp | ✅ Mitigated |
| R-02 | Patient denies consenting to data sharing | Consent records | `patient_consents` table with `consented_at`, `revoked_at`, and signed token hash | ✅ Mitigated |
| R-03 | Admin denies changing user permissions | `users.roles` | Audit log captures all role changes | ✅ Mitigated |
| R-04 | System clock manipulation to backdate records | All timestamp fields | DB timestamps are server-side (`now()` in PostgreSQL); clients cannot set `created_at` | ✅ Mitigated |

### 4.4 Information Disclosure

| ID | Threat | Asset | Mitigation | Status |
|----|--------|-------|-----------|--------|
| I-01 | API response leaks PHI of other patients | All patient endpoints | Facility-scoped queries + authorization policies; no cross-tenant data leak | ✅ Mitigated |
| I-02 | CNAMGS numbers exposed in API response | `patients.cnamgs_number` | Field excluded from default serialization; returned only on explicit request with elevated scope | ⚠️ Partial — exclusion not yet enforced at serialization layer |
| I-03 | Error messages reveal stack traces | All endpoints | `APP_DEBUG=false` in production; custom exception handler returns generic messages | ✅ Mitigated |
| I-04 | Log files contain PHI | Application logs | Logging sanitized — patient IDs logged, not names/data | ⚠️ Partial — full PHI scrub of logs not yet audited |
| I-05 | Mobile money API keys in source code | Source repository | Keys stored in `.env` / Secrets Manager; `.env` in `.gitignore`; Dependabot scans for hardcoded secrets | ✅ Mitigated |
| I-06 | PDF medical records accessible without auth | Generated PDFs | PDFs served through authenticated download endpoint; no public S3 URLs | ✅ Mitigated |
| I-07 | DICOM images accessible via guessable Study UID | `dicom_studies` | WADO-RS URLs require auth bearer token; Study UIDs are UUIDs (unguessable) | ✅ Mitigated |

**Action required for I-02:** Add `makeHidden(['cnamgs_number', 'national_id_number'])` to `Patient` resource and only reveal via a dedicated endpoint with `can:view-cnamgs` permission.
**Action required for I-04:** Add a log sanitization middleware that replaces patient names with `[PATIENT]` in all log output.

### 4.5 Denial of Service

| ID | Threat | Asset | Mitigation | Status |
|----|--------|-------|-----------|--------|
| D-01 | Volumetric DDoS against API | All endpoints | Cloudflare WAF + rate limiting (WAF layer + application layer) | ✅ Mitigated |
| D-02 | Slow HTTP (Slowloris) against Nginx | App server | Nginx `client_body_timeout 10s; client_header_timeout 10s;` | ✅ Mitigated |
| D-03 | Queue flooding via crafted API calls | Redis queue | Job max attempts = 3; failed jobs logged; Horizon monitors queue depth | ✅ Mitigated |
| D-04 | Large file upload causing disk exhaustion | S3 / local storage | File size limit enforced in validation (max:10240 for lab files) | ⚠️ Partial — max size not consistently enforced on all upload endpoints |
| D-05 | N+1 query exhaustion on large datasets | PostgreSQL | Eager loading in controllers; `EXPLAIN ANALYZE` in development | ⚠️ Partial — not all endpoints have been N+1 audited |

**Action required for D-04:** Audit all file upload validators and enforce `max:10240` (10 MB) globally.

### 4.6 Elevation of Privilege

| ID | Threat | Asset | Mitigation | Status |
|----|--------|-------|-----------|--------|
| E-01 | Patient role accessing provider-only endpoints | Provider workflows | `Gate::authorize` + role-based middleware on all provider routes | ✅ Mitigated |
| E-02 | Facility staff accessing another facility's data | Multi-tenant data | `HasFacilityScope` trait; policies check `facility_id` | ✅ Mitigated |
| E-03 | IDOR — patient accesses another patient's record by guessing ID | Patient records | UUIDs as primary keys (unguessable); ownership check in policy | ✅ Mitigated |
| E-04 | JWT token forging | All authenticated endpoints | Laravel Sanctum / Passport; tokens stored hashed; short expiry | ✅ Mitigated |
| E-05 | CSRF on state-changing portal endpoints | Patient portal | Laravel CSRF middleware on all web routes | ✅ Mitigated |
| E-06 | Insecure direct object reference in file downloads | PDFs, reports | Resource policy checks ownership before serving file | ✅ Mitigated |

---

## 5. Risk Register

| Threat ID | Likelihood | Impact | Risk Score | Priority |
|-----------|------------|--------|------------|----------|
| S-05 | Medium | High | **High** | P1 |
| I-02 | Low | High | **Medium** | P2 |
| I-04 | Low | Medium | **Medium** | P2 |
| D-04 | Low | Medium | **Low** | P3 |
| D-05 | Medium | Low | **Low** | P3 |

---

## 6. Out-of-Scope Threats

The following are acknowledged but not mitigated at the application layer:

| Threat | Reason |
|--------|--------|
| Physical server access | Handled by AWS / hosting provider SLAs |
| Insider threat by DBA with direct DB access | Addressed by DB audit logging at PostgreSQL level (pgaudit) |
| Side-channel attacks on TLS | Handled by Cloudflare TLS termination |
| Zero-day in PHP / Laravel | Mitigated by automated dependency scanning (Dependabot, Wave PR-12 Task 3) |

---

## 7. Security Architecture Decisions

| Decision | Rationale |
|----------|-----------|
| UUIDs as primary keys | Prevents IDOR enumeration attacks |
| Immutable audit log models | Tamper-proof audit trail; cannot be silently deleted |
| Additive-only migrations | No accidental data deletion; rollback-safe |
| Opt-in facility scope (not global) | Super-admin cross-facility queries work correctly |
| Field-level encryption for CNAMGS | Regulatory compliance for national ID data |
| Invitation tokens stored as SHA-256 hash | Raw token never persisted; one-way lookup only |
| Redis queue with Horizon monitoring | Prevents silent job failures and queue poisoning |

---

## 8. Review History

| Date | Reviewer | Changes |
|------|----------|---------|
| 2026-05-26 | Platform Engineering | Initial STRIDE analysis |

---

## 9. Next Review Triggers

- Major Laravel framework version upgrade
- Addition of a new external integration (payment gateway, lab system, etc.)
- Any security incident rated P1 or P2
- Annual review regardless of changes
```

- [ ] **Step 5.4: Run tests**

```bash
php artisan test tests/Feature/Security/ThreatModelTest.php --no-coverage
```

Expected: 4 tests, all PASS.

- [ ] **Step 5.5: Run full suite**

```bash
php artisan test --no-coverage
```

Expected: all tests pass, zero regressions.

- [ ] **Step 5.6: Commit**

```bash
git add docs/threat-model.md \
        tests/Feature/Security/ThreatModelTest.php
git commit -m "docs(security): add formal STRIDE threat model"
```

---

## Final Verification

- [ ] Run full test suite one final time:

```bash
php artisan test --no-coverage
```

Expected output: all tests pass.

- [ ] Verify all Wave PR-12 files exist:

```bash
ls -1 \
  config/kms.php \
  app/Services/Security/KmsEncryptionService.php \
  app/Console/Commands/RotateSecretsCommand.php \
  .github/workflows/security-scan.yml \
  .github/dependabot.yml \
  docs/waf-configuration.md \
  docs/secrets-rotation-runbook.md \
  docs/threat-model.md
```

Expected: all 8 files listed without errors.

- [ ] Final commit:

```bash
git add .
git commit -m "feat(security): complete Wave PR-12 — KMS, secrets rotation, CI scanning, WAF, threat model"
```

---

## Wave PR-12 Summary

| Item | Feature | Status after this wave |
|------|---------|----------------------|
| 71 | HSM/KMS encryption key management | ✅ `KmsEncryptionService` (local AES-256-GCM + AWS KMS envelope encryption) |
| 72 | Secrets rotation automation | ✅ `RotateSecretsCommand` + `docs/secrets-rotation-runbook.md` |
| 73 | Dependency vulnerability scanning | ✅ `.github/workflows/security-scan.yml` + `.github/dependabot.yml` |
| 74 | WAF configuration | ✅ `docs/waf-configuration.md` (Cloudflare + AWS WAF rules) |
| 75 | Threat model document | ✅ `docs/threat-model.md` (STRIDE analysis, risk register, architecture decisions) |

**No existing module, controller, model, route, migration, or test was modified.**
