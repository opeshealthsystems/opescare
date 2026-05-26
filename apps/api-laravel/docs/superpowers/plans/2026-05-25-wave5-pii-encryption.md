# Wave 5 — PII Column-Level Encryption

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Encrypt all PII fields in the `patients` table at column level using Laravel's `encrypted` cast. Fields: `date_of_birth`, `phone_number`, `address`. All existing data migrated. Apply Waves 1–4 first.

**Architecture:** Use Laravel's built-in `Crypt::encrypt()` / `encrypted` cast. Add a data migration that re-saves all existing patients through the model (triggering encryption). Keep `health_id` unencrypted (it is a lookup key, not sensitive in isolation). The migration is reversible.

**Tech Stack:** Laravel 13, AES-256-CBC (APP_KEY), PostgreSQL

**Findings addressed:** H9

**⚠️ CRITICAL PREREQUISITE:** `APP_KEY` must be set and stable before running this migration. Changing APP_KEY after encryption destroys all encrypted data. Run `php artisan key:generate` and commit the key to a secrets manager BEFORE running this wave's migration.

---

## Files Modified in This Wave

| File | Change |
|------|--------|
| `app/Models/Patient.php` | Add `encrypted` cast for PII fields |
| `database/migrations/2026_05_25_000003_encrypt_patient_pii_fields.php` | NEW — data migration |
| `app/Console/Commands/EncryptExistingPatientPii.php` | NEW — Artisan command for data migration |
| `tests/Feature/Security/PatientPiiEncryptionTest.php` | NEW |

---

### Task 1: Add encrypted casts to Patient model

**Finding:** H9

**Files:**
- Modify: `app/Models/Patient.php`
- Test: `tests/Feature/Security/PatientPiiEncryptionTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Security/PatientPiiEncryptionTest.php`:

```php
<?php
namespace Tests\Feature\Security;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientPiiEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_date_of_birth_is_stored_encrypted_in_database(): void
    {
        $patient = Patient::factory()->create([
            'date_of_birth' => '1990-04-15',
            'is_demo'       => false,
        ]);

        // Read raw value from DB (bypassing model cast)
        $raw = DB::table('patients')->where('id', $patient->id)->value('date_of_birth');

        // Raw value must NOT be the plain date string
        $this->assertNotEquals('1990-04-15', $raw,
            'date_of_birth must be stored encrypted, not as plain text');

        // But reading through model must return the decrypted value
        $decrypted = $patient->fresh()->date_of_birth;
        $this->assertEquals('1990-04-15', $decrypted instanceof \Carbon\Carbon
            ? $decrypted->format('Y-m-d')
            : (string) $decrypted
        );
    }

    public function test_phone_number_is_stored_encrypted(): void
    {
        $patient = Patient::factory()->create([
            'phone_number' => '+237600123456',
            'is_demo'      => false,
        ]);

        $raw = DB::table('patients')->where('id', $patient->id)->value('phone_number');
        $this->assertNotEquals('+237600123456', $raw,
            'phone_number must be stored encrypted');

        $this->assertEquals('+237600123456', $patient->fresh()->phone_number);
    }

    public function test_address_is_stored_encrypted(): void
    {
        $patient = Patient::factory()->create([
            'address'  => '123 Main Street, Yaoundé',
            'is_demo'  => false,
        ]);

        $raw = DB::table('patients')->where('id', $patient->id)->value('address');
        $this->assertNotEquals('123 Main Street, Yaoundé', $raw,
            'address must be stored encrypted');

        $this->assertEquals('123 Main Street, Yaoundé', $patient->fresh()->address);
    }

    public function test_health_id_is_not_encrypted_it_is_a_lookup_key(): void
    {
        $patient = Patient::factory()->create([
            'health_id' => 'OC-CMR-TEST-XXXX',
            'is_demo'   => false,
        ]);

        // health_id must be searchable — must NOT be encrypted
        $raw = DB::table('patients')->where('id', $patient->id)->value('health_id');
        $this->assertEquals('OC-CMR-TEST-XXXX', $raw,
            'health_id must remain unencrypted (it is a lookup key)');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Security/PatientPiiEncryptionTest.php
```

Expected: FAIL — date_of_birth stored as plain text

- [ ] **Step 3: Add encrypted casts to Patient model**

Open `app/Models/Patient.php`. Update the `$casts` array:

```php
protected $casts = [
    // PII fields — encrypted at rest using APP_KEY (AES-256-CBC)
    'date_of_birth' => 'encrypted',
    'phone_number'  => 'encrypted',
    'address'       => 'encrypted',

    // Non-PII fields — standard casts
    'is_demo'       => 'boolean',
    'created_at'    => 'datetime',
    'updated_at'    => 'datetime',
    // ... any other existing casts
];
```

**Note:** The `encrypted` cast uses Laravel's `Crypt::encrypt()` / `Crypt::decrypt()` which uses AES-256-CBC with the APP_KEY. Values stored in DB will be base64-encoded encrypted strings.

- [ ] **Step 4: Increase column sizes in migration to accommodate encrypted values**

Encrypted values are significantly longer than plain text. A DOB "1990-04-15" (10 chars) becomes ~200 chars encrypted. Create a migration:

```bash
php artisan make:migration expand_patient_pii_column_sizes_for_encryption
```

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Encrypted values need TEXT columns (unlimited size)
            // PostgreSQL TEXT handles any length
            $table->text('date_of_birth')->nullable()->change();
            $table->text('phone_number')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('date_of_birth', 20)->nullable()->change();
            $table->string('phone_number', 30)->nullable()->change();
            $table->string('address', 500)->nullable()->change();
        });
    }
};
```

- [ ] **Step 5: Run migration**

```bash
php artisan migrate
```

Expected: Migration runs without error.

- [ ] **Step 6: Run test to verify it passes**

```bash
php artisan test tests/Feature/Security/PatientPiiEncryptionTest.php
```

Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Models/Patient.php database/migrations/ tests/Feature/Security/PatientPiiEncryptionTest.php
git commit -m "security: add encrypted cast to Patient PII fields (date_of_birth, phone_number, address)"
```

---

### Task 2: Data migration — encrypt existing patient records

**Files:**
- Create: `app/Console/Commands/EncryptExistingPatientPii.php`

- [ ] **Step 1: Create the Artisan command**

```bash
php artisan make:command EncryptExistingPatientPii
```

Edit `app/Console/Commands/EncryptExistingPatientPii.php`:

```php
<?php
namespace App\Console\Commands;

use App\Models\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EncryptExistingPatientPii extends Command
{
    protected $signature = 'opescare:encrypt-patient-pii
                            {--dry-run : Show what would be encrypted without making changes}
                            {--batch=100 : Process N patients at a time}';

    protected $description = 'Encrypt existing plain-text PII fields in the patients table';

    public function handle(): int
    {
        $dryRun   = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');

        $this->info($dryRun ? '[DRY RUN] Scanning patients...' : 'Encrypting patient PII...');

        $total     = 0;
        $encrypted = 0;
        $skipped   = 0;
        $errors    = 0;

        // Process in chunks to avoid memory exhaustion
        Patient::withoutGlobalScopes()  // include demo patients
            ->chunkById($batchSize, function ($patients) use ($dryRun, &$total, &$encrypted, &$skipped, &$errors) {
                foreach ($patients as $patient) {
                    $total++;
                    try {
                        if ($dryRun) {
                            $this->line("Would encrypt patient: {$patient->health_id}");
                            $encrypted++;
                            continue;
                        }

                        // Re-save through model — the encrypted cast will encrypt the values
                        // The model reads the current plain value, then saves it encrypted
                        $patient->touch(); // triggers save with current cast values
                        $encrypted++;

                    } catch (\Throwable $e) {
                        $errors++;
                        $this->error("Failed to encrypt patient {$patient->health_id}: {$e->getMessage()}");
                    }
                }
            });

        $this->info("Done. Total: {$total} | Encrypted: {$encrypted} | Skipped: {$skipped} | Errors: {$errors}");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
```

- [ ] **Step 2: Test the command (dry run)**

```bash
php artisan opescare:encrypt-patient-pii --dry-run
```

Expected: Shows list of patients that would be encrypted.

- [ ] **Step 3: Run in production (after Wave 4 is deployed)**

```bash
php artisan opescare:encrypt-patient-pii --batch=50
```

Expected: All patients processed without errors.

- [ ] **Step 4: Verify spot-check**

```bash
php artisan tinker --execute="
\$p = App\Models\Patient::withoutGlobalScopes()->whereNotNull('phone_number')->first();
echo 'Raw DB: ' . DB::table('patients')->where('id', \$p->id)->value('phone_number') . PHP_EOL;
echo 'Model: ' . \$p->phone_number . PHP_EOL;
"
```

Expected: Raw DB shows encrypted string (starts with `eyJ`), Model shows plain phone number.

- [ ] **Step 5: Commit**

```bash
git add app/Console/Commands/EncryptExistingPatientPii.php
git commit -m "feat: add EncryptExistingPatientPii artisan command for data migration"
```

---

### Task 3: Add pin_hash verification (already hashed — document the pattern)

- [ ] **Step 1: Verify pin_hash is not double-encrypted**

The `pin_hash` field is an existing bcrypt hash stored in the database. It should NOT get the `encrypted` cast (that would double-encode it). Verify:

```bash
grep -n "pin_hash" app/Models/Patient.php
```

Expected: `pin_hash` should NOT appear in the `$casts` encrypted list.

- [ ] **Step 2: Add comment documenting the distinction**

In `Patient.php` above the `$casts` array:

```php
// PII ENCRYPTION NOTE:
// - date_of_birth, phone_number, address → encrypted cast (AES-256-CBC via APP_KEY)
// - pin_hash → bcrypt hash, NOT encrypted cast (already a one-way hash, not recoverable)
// - health_id → NOT encrypted (it is a searchable lookup key, not sensitive in isolation)
// - first_name, last_name → NOT encrypted in this wave (searched by admin; encrypt in Wave 7 if needed)
```

- [ ] **Step 3: Commit**

```bash
git add app/Models/Patient.php
git commit -m "docs: document PII encryption strategy in Patient model (encrypted vs hashed vs plain)"
```

---

### Task 4: Wave 5 final verification

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --parallel
```

Expected: All tests pass.

- [ ] **Step 2: Verify encrypted casts are present**

```bash
grep -A10 "casts" app/Models/Patient.php | grep "encrypted"
```

Expected: Shows `date_of_birth`, `phone_number`, `address` with `encrypted` cast.

- [ ] **Step 3: Run encryption command in test mode**

```bash
php artisan opescare:encrypt-patient-pii --dry-run
```

Expected: Runs without error.

- [ ] **Step 4: Check no raw PII in test DB**

```bash
php artisan test --filter=PatientPiiEncryptionTest
```

Expected: All 4 assertions pass.
