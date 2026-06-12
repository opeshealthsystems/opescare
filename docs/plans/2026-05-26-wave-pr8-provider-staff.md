# Wave PR-8: Provider & Staff

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add 4 missing provider/staff features — provider credentialing + license verification, care team collaboration with handoff notes, on-call scheduling, and provider performance metrics.

**Architecture:** Credentialing is a new module with license expiry alerting. Handoff notes extend the existing clinical note pattern. On-call scheduling reuses the ProviderShift model from Wave PR-3 (is_on_call flag) with a dedicated query service. Provider performance is a read-only aggregation service over existing appointment/encounter data.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, PHPUnit

---

## File Map

```
database/migrations/
  2026_05_26_800001_create_provider_credentials_table.php
  2026_05_26_800002_create_handoff_notes_table.php
  2026_05_26_800003_create_care_team_members_table.php
app/Models/
  ProviderCredential.php
  HandoffNote.php
  CareTeamMember.php
app/Services/
  Staff/CredentialingService.php
  Staff/ProviderPerformanceService.php
  Staff/OnCallService.php
tests/Feature/Staff/
  CredentialingTest.php
  HandoffNoteTest.php
  OnCallTest.php
  ProviderPerformanceTest.php
```

---

### Task 1: Provider Credentialing + License Verification

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Staff/CredentialingTest.php
namespace Tests\Feature\Staff;

use App\Models\User;
use App\Models\Facility;
use App\Models\ProviderCredential;
use App\Services\Staff\CredentialingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CredentialingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_provider_credential(): void
    {
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $credential = ProviderCredential::create([
            'provider_id'     => $provider->id,
            'facility_id'     => $facility->id,
            'credential_type' => 'medical_license',
            'license_number'  => 'CM-MED-2024-001',
            'issuing_body'    => 'Ordre National des Médecins du Cameroun',
            'issue_date'      => '2024-01-01',
            'expiry_date'     => '2027-01-01',
            'status'          => 'active',
        ]);

        $this->assertEquals('active', $credential->status);
        $this->assertEquals('CM-MED-2024-001', $credential->license_number);
    }

    public function test_expired_credential_is_flagged(): void
    {
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $credential = ProviderCredential::create([
            'provider_id'     => $provider->id,
            'facility_id'     => $facility->id,
            'credential_type' => 'medical_license',
            'license_number'  => 'CM-MED-2020-001',
            'issuing_body'    => 'Ordre National des Médecins du Cameroun',
            'issue_date'      => '2020-01-01',
            'expiry_date'     => '2023-01-01', // expired
            'status'          => 'active',
        ]);

        $service  = new CredentialingService();
        $expired  = $service->getExpiredCredentials();

        $this->assertTrue($expired->contains('id', $credential->id));
    }

    public function test_credentials_expiring_soon_are_identified(): void
    {
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        ProviderCredential::create([
            'provider_id'     => $provider->id,
            'facility_id'     => $facility->id,
            'credential_type' => 'medical_license',
            'license_number'  => 'CM-MED-2024-099',
            'issuing_body'    => 'Ordre National',
            'issue_date'      => now()->subYear()->toDateString(),
            'expiry_date'     => now()->addDays(25)->toDateString(), // expiring in 25 days
            'status'          => 'active',
        ]);

        $service  = new CredentialingService();
        $expiring = $service->getExpiringWithin(30); // expiring in 30 days

        $this->assertGreaterThan(0, $expiring->count());
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Staff/CredentialingTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_800001_create_provider_credentials_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->enum('credential_type', [
                'medical_license','nursing_license','specialist_certificate',
                'dea_number','board_certification','cpr_certification','other',
            ]);
            $table->string('license_number');
            $table->string('issuing_body');
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active','expired','suspended','pending_renewal'])->default('active');
            $table->string('document_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('provider_credentials'); }
};
```

- [ ] **Step 4: Create ProviderCredential model**

```php
<?php
// app/Models/ProviderCredential.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProviderCredential extends Model
{
    use HasUuids;

    protected $fillable = [
        'provider_id','facility_id','credential_type','license_number',
        'issuing_body','issue_date','expiry_date','status','document_url','notes',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
    ];

    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }
    public function facility() { return $this->belongsTo(Facility::class); }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString());
    }

    public function scopeExpiringWithin($query, int $days)
    {
        return $query->where('expiry_date', '>=', now()->toDateString())
            ->where('expiry_date', '<=', now()->addDays($days)->toDateString());
    }
}
```

- [ ] **Step 5: Create CredentialingService**

```php
<?php
// app/Services/Staff/CredentialingService.php
namespace App\Services\Staff;

use App\Models\ProviderCredential;
use Illuminate\Database\Eloquent\Collection;

class CredentialingService
{
    public function getExpiredCredentials(): Collection
    {
        return ProviderCredential::expired()->with('provider')->get();
    }

    public function getExpiringWithin(int $days): Collection
    {
        return ProviderCredential::expiringWithin($days)->with('provider')->get();
    }

    public function renewCredential(string $credentialId, string $newExpiry): ProviderCredential
    {
        $credential = ProviderCredential::findOrFail($credentialId);
        $credential->update([
            'expiry_date' => $newExpiry,
            'status'      => 'active',
        ]);
        return $credential;
    }
}
```

- [ ] **Step 6: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Staff/CredentialingTest.php
```

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_800001_* app/Models/ProviderCredential.php \
  app/Services/Staff/CredentialingService.php \
  tests/Feature/Staff/CredentialingTest.php
git commit -m "feat(staff): provider credentialing and license verification with expiry tracking"
```

---

### Task 2: Care Team Collaboration + Handoff Notes

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Staff/HandoffNoteTest.php
namespace Tests\Feature\Staff;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\CareTeamMember;
use App\Models\HandoffNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoffNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_care_team_member(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $member = CareTeamMember::create([
            'patient_id'  => $patient->id,
            'provider_id' => $provider->id,
            'facility_id' => $facility->id,
            'role'        => 'primary_physician',
            'is_active'   => true,
        ]);

        $this->assertEquals('primary_physician', $member->role);
        $this->assertTrue($member->is_active);
    }

    public function test_can_create_handoff_note(): void
    {
        $patient   = Patient::factory()->create();
        $fromProv  = User::factory()->create();
        $toProv    = User::factory()->create();
        $facility  = Facility::factory()->create();

        $note = HandoffNote::create([
            'patient_id'   => $patient->id,
            'from_provider'=> $fromProv->id,
            'to_provider'  => $toProv->id,
            'facility_id'  => $facility->id,
            'content'      => 'Patient stable. Continue current medications. Follow up on K+ levels.',
            'priority'     => 'routine',
            'acknowledged' => false,
        ]);

        $this->assertEquals('routine', $note->priority);
        $this->assertFalse($note->acknowledged);
    }

    public function test_handoff_note_can_be_acknowledged(): void
    {
        $patient   = Patient::factory()->create();
        $fromProv  = User::factory()->create();
        $toProv    = User::factory()->create();
        $facility  = Facility::factory()->create();

        $note = HandoffNote::create([
            'patient_id'   => $patient->id,
            'from_provider'=> $fromProv->id,
            'to_provider'  => $toProv->id,
            'facility_id'  => $facility->id,
            'content'      => 'Urgent: patient spiked fever. Watch for sepsis signs.',
            'priority'     => 'urgent',
            'acknowledged' => false,
        ]);

        $note->update(['acknowledged' => true, 'acknowledged_at' => now()]);
        $this->assertTrue($note->fresh()->acknowledged);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Staff/HandoffNoteTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_800002_create_handoff_notes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('handoff_notes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('from_provider')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('to_provider')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->text('content');
            $table->enum('priority', ['routine','urgent','critical'])->default('routine');
            $table->boolean('acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('handoff_notes'); }
};
```

```php
<?php
// database/migrations/2026_05_26_800003_create_care_team_members_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('care_team_members', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->enum('role', [
                'primary_physician','specialist','nurse','pharmacist',
                'physiotherapist','social_worker','other',
            ])->default('primary_physician');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['patient_id', 'provider_id', 'role']);
        });
    }

    public function down(): void { Schema::dropIfExists('care_team_members'); }
};
```

- [ ] **Step 4: Create models**

```php
<?php
// app/Models/HandoffNote.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HandoffNote extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','from_provider','to_provider','facility_id',
        'content','priority','acknowledged','acknowledged_at',
    ];

    protected $casts = [
        'acknowledged'    => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function patient()      { return $this->belongsTo(Patient::class); }
    public function fromProvider() { return $this->belongsTo(User::class, 'from_provider'); }
    public function toProvider()   { return $this->belongsTo(User::class, 'to_provider'); }
    public function facility()     { return $this->belongsTo(Facility::class); }
}
```

```php
<?php
// app/Models/CareTeamMember.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CareTeamMember extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','provider_id','facility_id','role','is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function patient()  { return $this->belongsTo(Patient::class); }
    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }
    public function facility() { return $this->belongsTo(Facility::class); }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Staff/HandoffNoteTest.php
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_800002_* database/migrations/2026_05_26_800003_* \
  app/Models/HandoffNote.php app/Models/CareTeamMember.php \
  tests/Feature/Staff/HandoffNoteTest.php
git commit -m "feat(staff): care team membership + handoff notes with acknowledgement"
```

---

### Task 3: Provider Performance Metrics

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Staff/ProviderPerformanceTest.php
namespace Tests\Feature\Staff;

use App\Models\User;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\Appointment;
use App\Services\Staff\ProviderPerformanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_count_aggregated_for_provider(): void
    {
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();
        $patient  = Patient::factory()->create();

        // Create 3 completed appointments
        for ($i = 0; $i < 3; $i++) {
            Appointment::create([
                'patient_id'       => $patient->id,
                'provider_id'      => $provider->id,
                'facility_id'      => $facility->id,
                'appointment_date' => now()->subDays($i + 1)->toDateString(),
                'appointment_time' => '09:00:00',
                'status'           => 'completed',
            ]);
        }

        $service = new ProviderPerformanceService();
        $metrics = $service->getMetrics(
            providerId: $provider->id,
            fromDate:   now()->subMonth()->toDateString(),
            toDate:     now()->toDateString(),
        );

        $this->assertEquals(3, $metrics['total_appointments']);
        $this->assertEquals(3, $metrics['completed_appointments']);
    }

    public function test_no_show_rate_calculated(): void
    {
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();
        $patient  = Patient::factory()->create();

        Appointment::create(['patient_id'=>$patient->id,'provider_id'=>$provider->id,'facility_id'=>$facility->id,'appointment_date'=>now()->subDays(3)->toDateString(),'appointment_time'=>'09:00:00','status'=>'completed']);
        Appointment::create(['patient_id'=>$patient->id,'provider_id'=>$provider->id,'facility_id'=>$facility->id,'appointment_date'=>now()->subDays(2)->toDateString(),'appointment_time'=>'09:00:00','status'=>'no_show']);

        $service = new ProviderPerformanceService();
        $metrics = $service->getMetrics($provider->id, now()->subMonth()->toDateString(), now()->toDateString());

        $this->assertEquals(50.0, $metrics['no_show_rate_pct']);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Staff/ProviderPerformanceTest.php
```

- [ ] **Step 3: Create ProviderPerformanceService**

```php
<?php
// app/Services/Staff/ProviderPerformanceService.php
namespace App\Services\Staff;

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class ProviderPerformanceService
{
    public function getMetrics(string $providerId, string $fromDate, string $toDate): array
    {
        $query = Appointment::where('provider_id', $providerId)
            ->whereBetween('appointment_date', [$fromDate, $toDate]);

        $total     = (clone $query)->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $noShow    = (clone $query)->where('status', 'no_show')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();

        $noShowRate = $total > 0 ? round($noShow / $total * 100, 1) : 0.0;

        return [
            'provider_id'            => $providerId,
            'from_date'              => $fromDate,
            'to_date'                => $toDate,
            'total_appointments'     => $total,
            'completed_appointments' => $completed,
            'no_show_appointments'   => $noShow,
            'cancelled_appointments' => $cancelled,
            'no_show_rate_pct'       => $noShowRate,
            'completion_rate_pct'    => $total > 0 ? round($completed / $total * 100, 1) : 0.0,
        ];
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/Staff/ProviderPerformanceTest.php
```

- [ ] **Step 5: Run full suite**

```bash
php artisan test
```

- [ ] **Step 6: Commit**

```bash
git add app/Services/Staff/ProviderPerformanceService.php \
  tests/Feature/Staff/ProviderPerformanceTest.php
git commit -m "feat(staff): provider performance metrics (appointment + no-show rates)"
```
