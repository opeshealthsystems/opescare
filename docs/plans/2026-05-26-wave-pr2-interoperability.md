# Wave PR-2: Interoperability Completions

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete 6 interoperability gaps — LOINC on lab results, SNOMED on diagnoses, CNAMGS national patient ID, cross-facility record exchange, DHIS2 push for MINSANTE, and HL7 v2 ADT parsing.

**Architecture:** All additions are purely additive. LOINC/SNOMED add nullable columns to existing tables via migrations. CNAMGS adds to patient identity. Cross-facility exchange is a new service. DHIS2 push is a queued job. HL7 v2 ADT is a new parser service consumed by BridgeConnector.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, queued jobs (Laravel Queue), PHPUnit

---

## File Map

```
database/migrations/
  2026_05_26_200001_add_loinc_code_to_lab_results.php
  2026_05_26_200002_add_snomed_code_to_problem_lists.php
  2026_05_26_200003_add_cnamgs_fields_to_patients.php
  2026_05_26_200004_create_cross_facility_record_requests_table.php
app/Jobs/
  PushPublicHealthToDhis2Job.php
app/Services/Interoperability/
  Dhis2PushService.php
  Hl7AdtParser.php
  CrossFacilityRecordService.php
app/Models/
  CrossFacilityRecordRequest.php
tests/Feature/Interoperability/
  LoincSnomedTest.php
  CnamgsTest.php
  CrossFacilityRecordTest.php
  Dhis2PushTest.php
  Hl7AdtTest.php
```

---

### Task 1: LOINC Codes on Lab Results + SNOMED on Diagnoses

**Files:**
- Create: `database/migrations/2026_05_26_200001_add_loinc_code_to_lab_results.php`
- Create: `database/migrations/2026_05_26_200002_add_snomed_code_to_problem_lists.php`
- Test: `tests/Feature/Interoperability/LoincSnomedTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Interoperability/LoincSnomedTest.php
namespace Tests\Feature\Interoperability;

use App\Models\LabResult;
use App\Models\ProblemList;
use App\Models\Patient;
use App\Models\User;
use App\Models\LabOrder;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoincSnomedTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_result_can_store_loinc_code(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $order = LabOrder::create([
            'patient_id'  => $patient->id,
            'ordered_by'  => $provider->id,
            'facility_id' => $facility->id,
            'test_name'   => 'Fasting Blood Glucose',
            'urgency'     => 'routine',
            'status'      => 'pending',
        ]);

        $result = LabResult::create([
            'lab_order_id'  => $order->id,
            'patient_id'    => $patient->id,
            'test_name'     => 'Fasting Blood Glucose',
            'result_value'  => '5.6',
            'result_unit'   => 'mmol/L',
            'loinc_code'    => '1556-0',
            'loinc_display' => 'Fasting glucose [Mass/volume] in Capillary blood',
            'status'        => 'final',
        ]);

        $this->assertEquals('1556-0', $result->fresh()->loinc_code);
    }

    public function test_problem_can_store_snomed_code(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();

        $problem = ProblemList::create([
            'patient_id'     => $patient->id,
            'provider_id'    => $provider->id,
            'icd_code'       => 'E11.9',
            'icd_version'    => '10',
            'description'    => 'Type 2 diabetes mellitus',
            'snomed_code'    => '44054006',
            'snomed_display' => 'Diabetes mellitus type 2',
            'status'         => 'active',
            'priority'       => 'high',
        ]);

        $this->assertEquals('44054006', $problem->fresh()->snomed_code);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Interoperability/LoincSnomedTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_200001_add_loinc_code_to_lab_results.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_results', 'loinc_code')) {
                $table->string('loinc_code', 30)->nullable()->after('test_name');
                $table->string('loinc_display')->nullable()->after('loinc_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropColumnIfExists(['loinc_code', 'loinc_display']);
        });
    }
};
```

```php
<?php
// database/migrations/2026_05_26_200002_add_snomed_code_to_problem_lists.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('problem_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('problem_lists', 'snomed_code')) {
                $table->string('snomed_code', 30)->nullable()->after('icd_version');
                $table->string('snomed_display')->nullable()->after('snomed_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('problem_lists', function (Blueprint $table) {
            $table->dropColumnIfExists(['snomed_code', 'snomed_display']);
        });
    }
};
```

- [ ] **Step 4: Add fields to model `$fillable`**

In `app/Models/LabResult.php` add `'loinc_code'` and `'loinc_display'` to `$fillable`.
In `app/Models/ProblemList.php` add `'snomed_code'` and `'snomed_display'` to `$fillable`.

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Interoperability/LoincSnomedTest.php
```
Expected: Both PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_200001_* database/migrations/2026_05_26_200002_* \
  app/Models/LabResult.php app/Models/ProblemList.php \
  tests/Feature/Interoperability/LoincSnomedTest.php
git commit -m "feat(interop): add loinc_code to lab_results and snomed_code to problem_lists"
```

---

### Task 2: CNAMGS National Patient ID

**Files:**
- Create: `database/migrations/2026_05_26_200003_add_cnamgs_fields_to_patients.php`
- Test: `tests/Feature/Interoperability/CnamgsTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Interoperability/CnamgsTest.php
namespace Tests\Feature\Interoperability;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CnamgsTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_store_cnamgs_number(): void
    {
        $patient = Patient::factory()->create([
            'cnamgs_number'        => 'CM-1234-5678',
            'cnamgs_verified_at'   => now(),
            'national_id_number'   => '123456789',
            'national_id_type'     => 'cni',
        ]);

        $this->assertEquals('CM-1234-5678', $patient->fresh()->cnamgs_number);
        $this->assertEquals('cni', $patient->fresh()->national_id_type);
    }

    public function test_cnamgs_number_is_unique(): void
    {
        Patient::factory()->create(['cnamgs_number' => 'CM-0001-0001']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Patient::factory()->create(['cnamgs_number' => 'CM-0001-0001']);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Interoperability/CnamgsTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_200003_add_cnamgs_fields_to_patients.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'cnamgs_number')) {
                $table->string('cnamgs_number', 50)->nullable()->unique()->after('id');
                $table->timestamp('cnamgs_verified_at')->nullable()->after('cnamgs_number');
                $table->string('national_id_number', 50)->nullable()->after('cnamgs_verified_at');
                $table->enum('national_id_type', ['cni', 'passport', 'residence_permit', 'other'])
                    ->nullable()->after('national_id_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumnIfExists([
                'cnamgs_number','cnamgs_verified_at','national_id_number','national_id_type',
            ]);
        });
    }
};
```

- [ ] **Step 4: Add to Patient `$fillable`**

In `app/Models/Patient.php` add to `$fillable`:
```php
'cnamgs_number', 'cnamgs_verified_at', 'national_id_number', 'national_id_type',
```

Add to `$casts`:
```php
'cnamgs_verified_at' => 'datetime',
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Interoperability/CnamgsTest.php
```
Expected: Both PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_200003_* app/Models/Patient.php \
  tests/Feature/Interoperability/CnamgsTest.php
git commit -m "feat(interop): CNAMGS national patient ID fields on patients table"
```

---

### Task 3: Cross-Facility Record Exchange

**Files:**
- Create: `database/migrations/2026_05_26_200004_create_cross_facility_record_requests_table.php`
- Create: `app/Models/CrossFacilityRecordRequest.php`
- Create: `app/Services/Interoperability/CrossFacilityRecordService.php`
- Test: `tests/Feature/Interoperability/CrossFacilityRecordTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Interoperability/CrossFacilityRecordTest.php
namespace Tests\Feature\Interoperability;

use App\Models\Patient;
use App\Models\Facility;
use App\Models\User;
use App\Models\CrossFacilityRecordRequest;
use App\Services\Interoperability\CrossFacilityRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossFacilityRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_request_record_transfer(): void
    {
        $patient         = Patient::factory()->create();
        $requestingFac   = Facility::factory()->create();
        $sourceFac       = Facility::factory()->create();
        $requestingUser  = User::factory()->create();

        $service = new CrossFacilityRecordService();
        $request = $service->requestRecords(
            patientId:          $patient->id,
            requestingFacility: $requestingFac->id,
            sourceFacility:     $sourceFac->id,
            requestedBy:        $requestingUser->id,
            purpose:            'Referral continuity',
            recordTypes:        ['lab_results', 'vital_signs'],
        );

        $this->assertInstanceOf(CrossFacilityRecordRequest::class, $request);
        $this->assertEquals('pending', $request->status);
        $this->assertContains('lab_results', $request->record_types);
    }

    public function test_request_requires_patient_consent(): void
    {
        $patient         = Patient::factory()->create();
        $requestingFac   = Facility::factory()->create();
        $sourceFac       = Facility::factory()->create();
        $requestingUser  = User::factory()->create();

        $service = new CrossFacilityRecordService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PATIENT_CONSENT_REQUIRED');

        $service->requestRecords(
            patientId:          $patient->id,
            requestingFacility: $requestingFac->id,
            sourceFacility:     $sourceFac->id,
            requestedBy:        $requestingUser->id,
            purpose:            'Marketing',
            recordTypes:        ['clinical_notes'],
            requireConsent:     true,
            hasConsent:         false,
        );
    }

    public function test_can_approve_record_request(): void
    {
        $patient        = Patient::factory()->create();
        $requestingFac  = Facility::factory()->create();
        $sourceFac      = Facility::factory()->create();
        $requestingUser = User::factory()->create();
        $approver       = User::factory()->create();

        $service = new CrossFacilityRecordService();
        $request = $service->requestRecords($patient->id, $requestingFac->id, $sourceFac->id, $requestingUser->id, 'Referral', ['lab_results']);

        $approved = $service->approveRequest($request->id, $approver->id);
        $this->assertEquals('approved', $approved->status);
        $this->assertNotNull($approved->approved_at);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Interoperability/CrossFacilityRecordTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_200004_create_cross_facility_record_requests_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cross_facility_record_requests', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('requesting_facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('source_facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('purpose');
            $table->json('record_types');   // array: lab_results, vital_signs, clinical_notes, etc.
            $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled', 'expired'])
                ->default('pending');
            $table->boolean('consent_obtained')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('cross_facility_record_requests'); }
};
```

- [ ] **Step 4: Create model**

```php
<?php
// app/Models/CrossFacilityRecordRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CrossFacilityRecordRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','requesting_facility_id','source_facility_id',
        'requested_by','approved_by','purpose','record_types','status',
        'consent_obtained','approved_at','fulfilled_at','expires_at','rejection_reason',
    ];

    protected $casts = [
        'record_types'    => 'array',
        'consent_obtained'=> 'boolean',
        'approved_at'     => 'datetime',
        'fulfilled_at'    => 'datetime',
        'expires_at'      => 'datetime',
    ];

    public function patient()             { return $this->belongsTo(Patient::class); }
    public function requestingFacility()  { return $this->belongsTo(Facility::class, 'requesting_facility_id'); }
    public function sourceFacility()      { return $this->belongsTo(Facility::class, 'source_facility_id'); }
    public function requestedBy()         { return $this->belongsTo(User::class, 'requested_by'); }
    public function approvedBy()          { return $this->belongsTo(User::class, 'approved_by'); }
}
```

- [ ] **Step 5: Create CrossFacilityRecordService**

```php
<?php
// app/Services/Interoperability/CrossFacilityRecordService.php
namespace App\Services\Interoperability;

use App\Models\CrossFacilityRecordRequest;

class CrossFacilityRecordService
{
    public function requestRecords(
        string  $patientId,
        string  $requestingFacility,
        string  $sourceFacility,
        string  $requestedBy,
        string  $purpose,
        array   $recordTypes,
        bool    $requireConsent = false,
        bool    $hasConsent     = true,
    ): CrossFacilityRecordRequest {
        if ($requireConsent && !$hasConsent) {
            throw new \Exception('PATIENT_CONSENT_REQUIRED');
        }

        return CrossFacilityRecordRequest::create([
            'patient_id'             => $patientId,
            'requesting_facility_id' => $requestingFacility,
            'source_facility_id'     => $sourceFacility,
            'requested_by'           => $requestedBy,
            'purpose'                => $purpose,
            'record_types'           => $recordTypes,
            'status'                 => 'pending',
            'consent_obtained'       => $hasConsent,
            'expires_at'             => now()->addDays(30),
        ]);
    }

    public function approveRequest(string $requestId, string $approverId): CrossFacilityRecordRequest
    {
        $request = CrossFacilityRecordRequest::findOrFail($requestId);
        $request->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
        return $request;
    }

    public function rejectRequest(string $requestId, string $reason): CrossFacilityRecordRequest
    {
        $request = CrossFacilityRecordRequest::findOrFail($requestId);
        $request->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        return $request;
    }
}
```

- [ ] **Step 6: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Interoperability/CrossFacilityRecordTest.php
```
Expected: All 3 PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_200004_* app/Models/CrossFacilityRecordRequest.php \
  app/Services/Interoperability/CrossFacilityRecordService.php \
  tests/Feature/Interoperability/CrossFacilityRecordTest.php
git commit -m "feat(interop): cross-facility record exchange with consent enforcement"
```

---

### Task 4: DHIS2 Push Service for MINSANTE

**Files:**
- Create: `app/Services/Interoperability/Dhis2PushService.php`
- Create: `app/Jobs/PushPublicHealthToDhis2Job.php`
- Test: `tests/Feature/Interoperability/Dhis2PushTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Interoperability/Dhis2PushTest.php
namespace Tests\Feature\Interoperability;

use App\Services\Interoperability\Dhis2PushService;
use App\Jobs\PushPublicHealthToDhis2Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class Dhis2PushTest extends TestCase
{
    use RefreshDatabase;

    public function test_dhis2_push_service_formats_payload(): void
    {
        $service = new Dhis2PushService(
            baseUrl: 'https://dhis2.minsante.cm',
            username: 'test',
            password: 'test'
        );

        $payload = $service->buildPayload([
            'org_unit'   => 'Hôpital Central Yaoundé',
            'period'     => '202601',
            'data_element' => 'malaria_confirmed',
            'value'      => 42,
        ]);

        $this->assertArrayHasKey('dataValues', $payload);
        $this->assertEquals('malaria_confirmed', $payload['dataValues'][0]['dataElement']);
        $this->assertEquals(42, $payload['dataValues'][0]['value']);
    }

    public function test_dhis2_job_is_queued_with_correct_data(): void
    {
        Queue::fake();

        PushPublicHealthToDhis2Job::dispatch([
            'org_unit'     => 'OU-001',
            'period'       => '202601',
            'data_element' => 'cholera_suspected',
            'value'        => 5,
        ]);

        Queue::assertPushed(PushPublicHealthToDhis2Job::class, function ($job) {
            return $job->dataPoint['data_element'] === 'cholera_suspected';
        });
    }

    public function test_dhis2_push_fails_gracefully_on_error(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ERROR'], 400)]);

        $service = new Dhis2PushService('https://dhis2.minsante.cm', 'user', 'pass');

        $result = $service->push([
            'org_unit' => 'OU-001', 'period' => '202601',
            'data_element' => 'malaria_confirmed', 'value' => 10,
        ]);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Interoperability/Dhis2PushTest.php
```

- [ ] **Step 3: Create Dhis2PushService**

```php
<?php
// app/Services/Interoperability/Dhis2PushService.php
namespace App\Services\Interoperability;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Dhis2PushService
{
    public function __construct(
        private string $baseUrl,
        private string $username,
        private string $password,
    ) {}

    public function buildPayload(array $dataPoint): array
    {
        return [
            'dataValues' => [[
                'dataElement' => $dataPoint['data_element'],
                'period'      => $dataPoint['period'],
                'orgUnit'     => $dataPoint['org_unit'],
                'value'       => $dataPoint['value'],
            ]],
        ];
    }

    public function push(array $dataPoint): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->post("{$this->baseUrl}/api/dataValueSets", $this->buildPayload($dataPoint));

            if ($response->successful() && ($response->json('status') !== 'ERROR')) {
                return ['success' => true, 'response' => $response->json()];
            }

            return ['success' => false, 'error' => $response->json('description', 'Unknown DHIS2 error')];
        } catch (\Exception $e) {
            Log::error('DHIS2 push failed', ['error' => $e->getMessage(), 'data' => $dataPoint]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

- [ ] **Step 4: Create queued job**

```php
<?php
// app/Jobs/PushPublicHealthToDhis2Job.php
namespace App\Jobs;

use App\Services\Interoperability\Dhis2PushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushPublicHealthToDhis2Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public array $dataPoint) {}

    public function handle(): void
    {
        $service = new Dhis2PushService(
            baseUrl:  config('services.dhis2.url', 'https://dhis2.minsante.cm'),
            username: config('services.dhis2.username'),
            password: config('services.dhis2.password'),
        );

        $result = $service->push($this->dataPoint);

        if (!$result['success']) {
            Log::warning('DHIS2 push unsuccessful', ['result' => $result, 'data' => $this->dataPoint]);
        }
    }
}
```

- [ ] **Step 5: Add DHIS2 config to `config/services.php`**

```php
// Add inside the return array of config/services.php:
'dhis2' => [
    'url'      => env('DHIS2_URL', 'https://dhis2.minsante.cm'),
    'username' => env('DHIS2_USERNAME'),
    'password' => env('DHIS2_PASSWORD'),
],
```

Also add to `.env.example`:
```
DHIS2_URL=https://dhis2.minsante.cm
DHIS2_USERNAME=
DHIS2_PASSWORD=
```

- [ ] **Step 6: Run tests**

```bash
php artisan test tests/Feature/Interoperability/Dhis2PushTest.php
```
Expected: All 3 PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Services/Interoperability/Dhis2PushService.php \
  app/Jobs/PushPublicHealthToDhis2Job.php \
  config/services.php .env.example \
  tests/Feature/Interoperability/Dhis2PushTest.php
git commit -m "feat(interop): DHIS2 push service for MINSANTE public health reporting"
```

---

### Task 5: HL7 v2 ADT Parser

**Files:**
- Create: `app/Services/Interoperability/Hl7AdtParser.php`
- Test: `tests/Feature/Interoperability/Hl7AdtTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Interoperability/Hl7AdtTest.php
namespace Tests\Feature\Interoperability;

use App\Services\Interoperability\Hl7AdtParser;
use Tests\TestCase;

class Hl7AdtTest extends TestCase
{
    private string $sampleAdt = "MSH|^~\\&|HOSP|YAOUNDE|OPESCARE||20260101120000||ADT^A01|MSG001|P|2.5\r\n" .
        "EVN|A01|20260101120000\r\n" .
        "PID|1||PAT-001^^^HOSP^MR||Nkemdirim^Chidi||19850312|M|||123 Rue de la Paix^^Yaounde^CM\r\n" .
        "PV1|1|I|WARD-A^BED-12^HOSP";

    public function test_parses_adt_a01_admit(): void
    {
        $parser = new Hl7AdtParser();
        $result = $parser->parse($this->sampleAdt);

        $this->assertEquals('A01', $result['event_type']);
        $this->assertEquals('PAT-001', $result['patient_id']);
        $this->assertEquals('Nkemdirim', $result['family_name']);
        $this->assertEquals('Chidi', $result['given_name']);
        $this->assertEquals('M', $result['gender']);
        $this->assertEquals('19850312', $result['dob']);
    }

    public function test_parses_patient_location(): void
    {
        $parser = new Hl7AdtParser();
        $result = $parser->parse($this->sampleAdt);

        $this->assertEquals('WARD-A', $result['ward']);
        $this->assertEquals('BED-12', $result['bed']);
    }

    public function test_throws_on_invalid_message(): void
    {
        $parser = new Hl7AdtParser();

        $this->expectException(\InvalidArgumentException::class);
        $parser->parse('NOT_AN_HL7_MESSAGE');
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Interoperability/Hl7AdtTest.php
```

- [ ] **Step 3: Create Hl7AdtParser**

```php
<?php
// app/Services/Interoperability/Hl7AdtParser.php
namespace App\Services\Interoperability;

class Hl7AdtParser
{
    /**
     * Parse an HL7 v2 ADT message into an array of structured fields.
     * Supports: A01 (Admit), A02 (Transfer), A03 (Discharge), A08 (Update).
     *
     * @throws \InvalidArgumentException for non-HL7 input
     */
    public function parse(string $message): array
    {
        $segments = preg_split('/[\r\n]+/', trim($message));

        if (!str_starts_with($segments[0] ?? '', 'MSH')) {
            throw new \InvalidArgumentException('Not a valid HL7 v2 message — MSH segment missing');
        }

        $msh = $this->parseSegment($segments[0]);
        $evn = $this->findSegment($segments, 'EVN');
        $pid = $this->findSegment($segments, 'PID');
        $pv1 = $this->findSegment($segments, 'PV1');

        $eventType = isset($msh[8]) ? explode('^', $msh[8])[1] ?? '' : '';

        // PID-3: patient ID list, PID-5: patient name (family^given), PID-7: DOB, PID-8: gender
        $patientId  = $pid ? explode('^', $pid[3] ?? '')[0] : null;
        $name       = $pid ? explode('^', $pid[5] ?? '') : [];
        $familyName = $name[0] ?? null;
        $givenName  = $name[1] ?? null;
        $dob        = $pid[7] ?? null;
        $gender     = $pid[8] ?? null;

        // PV1-3: point of care ^ room ^ facility
        $location   = $pv1 ? explode('^', $pv1[3] ?? '') : [];
        $ward       = $location[0] ?? null;
        $bed        = $location[1] ?? null;

        return [
            'event_type'  => $eventType,
            'patient_id'  => $patientId,
            'family_name' => $familyName,
            'given_name'  => $givenName,
            'dob'         => $dob,
            'gender'      => $gender,
            'ward'        => $ward,
            'bed'         => $bed,
            'raw_msh'     => $msh,
        ];
    }

    private function parseSegment(string $line): array
    {
        return explode('|', $line);
    }

    private function findSegment(array $segments, string $type): ?array
    {
        foreach ($segments as $seg) {
            if (str_starts_with($seg, $type . '|')) {
                return $this->parseSegment($seg);
            }
        }
        return null;
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/Interoperability/Hl7AdtTest.php
```
Expected: All 3 PASS.

- [ ] **Step 5: Run full suite**

```bash
php artisan test
```
Expected: All green.

- [ ] **Step 6: Commit**

```bash
git add app/Services/Interoperability/Hl7AdtParser.php \
  tests/Feature/Interoperability/Hl7AdtTest.php
git commit -m "feat(interop): HL7 v2 ADT message parser (A01/A02/A03/A08)"
```
