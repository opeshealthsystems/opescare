# Wave PR-5: Laboratory & Imaging

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete 4 lab/imaging gaps — critical value alerting with acknowledgement, DICOM/PACS integration stub, radiology report distribution, and reference range management.

**Architecture:** Critical value alerting extends LabResult with an observer. DICOM uses DICOMweb (WADO-RS/STOW-RS) as the integration standard — OpesCare acts as the broker, not the viewer. Radiology reports are a new model linked to LabOrders. Reference ranges are a lookup table scoped to test_code + demographics.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, Model Observers, PHPUnit

---

## File Map

```
database/migrations/
  2026_05_26_500001_create_critical_value_alerts_table.php
  2026_05_26_500002_create_dicom_studies_table.php
  2026_05_26_500003_create_radiology_reports_table.php
  2026_05_26_500004_create_lab_reference_ranges_table.php
app/Models/
  CriticalValueAlert.php
  DicomStudy.php
  RadiologyReport.php
  LabReferenceRange.php
app/Observers/
  LabResultObserver.php
app/Services/Lab/
  CriticalValueService.php
  DicomWebService.php
tests/Feature/Lab/
  CriticalValueAlertTest.php
  DicomStudyTest.php
  RadiologyReportTest.php
  LabReferenceRangeTest.php
```

---

### Task 1: Critical Value Alerting with Acknowledgement

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Lab/CriticalValueAlertTest.php
namespace Tests\Feature\Lab;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\CriticalValueAlert;
use App\Services\Lab\CriticalValueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriticalValueAlertTest extends TestCase
{
    use RefreshDatabase;

    private function makeLabResult(float $value, string $unit = 'mmol/L'): LabResult
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $order = LabOrder::create([
            'patient_id'  => $patient->id,
            'ordered_by'  => $provider->id,
            'facility_id' => $facility->id,
            'test_name'   => 'Serum Potassium',
            'urgency'     => 'routine',
            'status'      => 'resulted',
        ]);

        return LabResult::create([
            'lab_order_id'  => $order->id,
            'patient_id'    => $patient->id,
            'test_name'     => 'Serum Potassium',
            'result_value'  => (string) $value,
            'result_unit'   => $unit,
            'loinc_code'    => '2823-3',
            'status'        => 'final',
        ]);
    }

    public function test_critical_low_potassium_generates_alert(): void
    {
        $service = new CriticalValueService();
        $result  = $this->makeLabResult(2.5); // < 3.0 is critical low

        $alert = $service->evaluateResult($result);

        $this->assertNotNull($alert);
        $this->assertEquals('critical_low', $alert->alert_type);
        $this->assertFalse($alert->acknowledged);
    }

    public function test_normal_value_generates_no_alert(): void
    {
        $service = new CriticalValueService();
        $result  = $this->makeLabResult(4.2); // normal

        $alert = $service->evaluateResult($result);

        $this->assertNull($alert);
    }

    public function test_critical_alert_can_be_acknowledged(): void
    {
        $provider = User::factory()->create();
        $service  = new CriticalValueService();
        $result   = $this->makeLabResult(2.5);

        $alert = $service->evaluateResult($result);
        $this->assertNotNull($alert);

        $service->acknowledge($alert->id, $provider->id, 'Patient notified and IV potassium ordered');

        $alert->refresh();
        $this->assertTrue($alert->acknowledged);
        $this->assertEquals($provider->id, $alert->acknowledged_by);
        $this->assertNotNull($alert->acknowledged_at);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Lab/CriticalValueAlertTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_500001_create_critical_value_alerts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('critical_value_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('lab_result_id')->constrained('lab_results')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->enum('alert_type', ['critical_high','critical_low','panic_high','panic_low']);
            $table->string('test_name');
            $table->string('result_value');
            $table->string('critical_threshold');
            $table->boolean('acknowledged')->default(false);
            $table->foreignUuid('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('acknowledgement_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('critical_value_alerts'); }
};
```

- [ ] **Step 4: Create CriticalValueAlert model**

```php
<?php
// app/Models/CriticalValueAlert.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CriticalValueAlert extends Model
{
    use HasUuids;

    protected $fillable = [
        'lab_result_id','patient_id','alert_type','test_name',
        'result_value','critical_threshold','acknowledged',
        'acknowledged_by','acknowledged_at','acknowledgement_note',
    ];

    protected $casts = [
        'acknowledged'    => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function labResult()      { return $this->belongsTo(LabResult::class); }
    public function patient()        { return $this->belongsTo(Patient::class); }
    public function acknowledgedBy() { return $this->belongsTo(User::class, 'acknowledged_by'); }
}
```

- [ ] **Step 5: Create CriticalValueService**

```php
<?php
// app/Services/Lab/CriticalValueService.php
namespace App\Services\Lab;

use App\Models\CriticalValueAlert;
use App\Models\LabResult;

class CriticalValueService
{
    /**
     * Critical thresholds keyed by LOINC code.
     * Format: ['critical_low' => float|null, 'critical_high' => float|null]
     * Production: load from lab_reference_ranges table.
     */
    private array $thresholds = [
        '2823-3' => ['critical_low' => 3.0,  'critical_high' => 6.5,  'unit' => 'mmol/L'], // K+
        '2951-2' => ['critical_low' => 120.0, 'critical_high' => 160.0,'unit' => 'mmol/L'], // Na+
        '2345-7' => ['critical_low' => 2.2,   'critical_high' => 22.2, 'unit' => 'mmol/L'], // Glucose
        '718-7'  => ['critical_low' => 70.0,  'critical_high' => null, 'unit' => 'g/L'],    // Haemoglobin
    ];

    public function evaluateResult(LabResult $result): ?CriticalValueAlert
    {
        $threshold = $this->thresholds[$result->loinc_code ?? ''] ?? null;
        if (!$threshold) {
            return null;
        }

        $value     = (float) $result->result_value;
        $alertType = null;
        $threshold_value = null;

        if ($threshold['critical_low'] !== null && $value < $threshold['critical_low']) {
            $alertType       = 'critical_low';
            $threshold_value = $threshold['critical_low'];
        } elseif ($threshold['critical_high'] !== null && $value > $threshold['critical_high']) {
            $alertType       = 'critical_high';
            $threshold_value = $threshold['critical_high'];
        }

        if (!$alertType) {
            return null;
        }

        return CriticalValueAlert::create([
            'lab_result_id'      => $result->id,
            'patient_id'         => $result->patient_id,
            'alert_type'         => $alertType,
            'test_name'          => $result->test_name,
            'result_value'       => $result->result_value . ' ' . $result->result_unit,
            'critical_threshold' => $threshold_value . ' ' . $threshold['unit'],
            'acknowledged'       => false,
        ]);
    }

    public function acknowledge(string $alertId, string $providerId, string $note = ''): CriticalValueAlert
    {
        $alert = CriticalValueAlert::findOrFail($alertId);
        $alert->update([
            'acknowledged'         => true,
            'acknowledged_by'      => $providerId,
            'acknowledged_at'      => now(),
            'acknowledgement_note' => $note,
        ]);
        return $alert;
    }
}
```

- [ ] **Step 6: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Lab/CriticalValueAlertTest.php
```
Expected: All 3 PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_500001_* app/Models/CriticalValueAlert.php \
  app/Services/Lab/CriticalValueService.php \
  tests/Feature/Lab/CriticalValueAlertTest.php
git commit -m "feat(lab): critical value alerting with acknowledgement trail"
```

---

### Task 2: DICOM Study Broker (PACS Integration)

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Lab/DicomStudyTest.php
namespace Tests\Feature\Lab;

use App\Models\Patient;
use App\Models\Facility;
use App\Models\DicomStudy;
use App\Services\Lab\DicomWebService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DicomStudyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_dicom_study(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $study = DicomStudy::create([
            'patient_id'   => $patient->id,
            'facility_id'  => $facility->id,
            'study_uid'    => '1.2.840.10008.5.1.4.1.1.2.1',
            'modality'     => 'CT',
            'body_part'    => 'Chest',
            'study_date'   => '2026-06-01',
            'accession_no' => 'ACC-2026-001',
            'status'       => 'available',
        ]);

        $this->assertEquals('CT', $study->modality);
        $this->assertEquals('available', $study->status);
    }

    public function test_dicomweb_service_builds_wado_url(): void
    {
        $service = new DicomWebService(
            wadoBaseUrl: 'https://pacs.hospital.cm/wado',
            stowBaseUrl: 'https://pacs.hospital.cm/stow',
        );

        $url = $service->buildWadoUrl('1.2.840.10008.5.1.4.1.1.2.1');

        $this->assertStringContainsString('1.2.840.10008.5.1.4.1.1.2.1', $url);
        $this->assertStringContainsString('wado', $url);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Lab/DicomStudyTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_500002_create_dicom_studies_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dicom_studies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('lab_order_id')->nullable()->constrained('lab_orders')->nullOnDelete();
            $table->string('study_uid')->unique();
            $table->string('modality', 20);     // CT, MR, CR, US, etc.
            $table->string('body_part')->nullable();
            $table->date('study_date');
            $table->string('accession_no')->nullable();
            $table->string('pacs_url')->nullable();
            $table->enum('status', ['pending','available','archived'])->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('dicom_studies'); }
};
```

- [ ] **Step 4: Create DicomStudy model**

```php
<?php
// app/Models/DicomStudy.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DicomStudy extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','facility_id','lab_order_id','study_uid',
        'modality','body_part','study_date','accession_no',
        'pacs_url','status','description',
    ];

    protected $casts = ['study_date' => 'date'];

    public function patient()   { return $this->belongsTo(Patient::class); }
    public function facility()  { return $this->belongsTo(Facility::class); }
    public function labOrder()  { return $this->belongsTo(LabOrder::class); }
}
```

- [ ] **Step 5: Create DicomWebService**

```php
<?php
// app/Services/Lab/DicomWebService.php
namespace App\Services\Lab;

class DicomWebService
{
    public function __construct(
        private string $wadoBaseUrl = '',
        private string $stowBaseUrl = '',
    ) {
        $this->wadoBaseUrl = $wadoBaseUrl ?: config('services.pacs.wado_url', '');
        $this->stowBaseUrl = $stowBaseUrl ?: config('services.pacs.stow_url', '');
    }

    /** Returns a DICOMweb WADO-RS URL for a study UID */
    public function buildWadoUrl(string $studyUid): string
    {
        return rtrim($this->wadoBaseUrl, '/') . '/studies/' . urlencode($studyUid);
    }

    /** Returns a DICOMweb WADO-RS URL for a specific series */
    public function buildSeriesUrl(string $studyUid, string $seriesUid): string
    {
        return $this->buildWadoUrl($studyUid) . '/series/' . urlencode($seriesUid);
    }
}
```

- [ ] **Step 6: Add PACS config to `config/services.php`**

```php
'pacs' => [
    'wado_url' => env('PACS_WADO_URL', ''),
    'stow_url' => env('PACS_STOW_URL', ''),
    'auth'     => env('PACS_AUTH_TOKEN', ''),
],
```

- [ ] **Step 7: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Lab/DicomStudyTest.php
```

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_05_26_500002_* app/Models/DicomStudy.php \
  app/Services/Lab/DicomWebService.php config/services.php \
  tests/Feature/Lab/DicomStudyTest.php
git commit -m "feat(lab): DICOM study broker with DICOMweb WADO-RS URL builder"
```

---

### Task 3: Radiology Reports + Reference Ranges

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Lab/RadiologyReportTest.php
namespace Tests\Feature\Lab;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\LabOrder;
use App\Models\DicomStudy;
use App\Models\RadiologyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiologyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_radiology_report(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $order = LabOrder::create([
            'patient_id'  => $patient->id,
            'ordered_by'  => $provider->id,
            'facility_id' => $facility->id,
            'test_name'   => 'Chest X-Ray',
            'urgency'     => 'routine',
            'status'      => 'resulted',
        ]);

        $study = DicomStudy::create([
            'patient_id'  => $patient->id,
            'facility_id' => $facility->id,
            'lab_order_id'=> $order->id,
            'study_uid'   => '1.2.3.4',
            'modality'    => 'CR',
            'study_date'  => '2026-06-01',
            'status'      => 'available',
        ]);

        $report = RadiologyReport::create([
            'dicom_study_id'  => $study->id,
            'lab_order_id'    => $order->id,
            'patient_id'      => $patient->id,
            'radiologist_id'  => $provider->id,
            'findings'        => 'No active pulmonary disease. Heart size normal.',
            'impression'      => 'Normal chest radiograph.',
            'status'          => 'final',
            'reported_at'     => now(),
        ]);

        $this->assertEquals('final', $report->status);
        $this->assertStringContainsString('Normal', $report->impression);
    }
}
```

```php
<?php
// tests/Feature/Lab/LabReferenceRangeTest.php
namespace Tests\Feature\Lab;

use App\Models\LabReferenceRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabReferenceRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_reference_range(): void
    {
        $range = LabReferenceRange::create([
            'loinc_code'  => '2823-3',
            'test_name'   => 'Serum Potassium',
            'unit'        => 'mmol/L',
            'gender'      => 'all',
            'age_min'     => 18,
            'age_max'     => 120,
            'normal_low'  => 3.5,
            'normal_high' => 5.0,
            'critical_low'=> 3.0,
            'critical_high'=> 6.5,
        ]);

        $this->assertEquals('2823-3', $range->loinc_code);
        $this->assertEquals(3.5, $range->normal_low);
    }

    public function test_lookup_range_for_demographic(): void
    {
        LabReferenceRange::create([
            'loinc_code'  => '718-7',
            'test_name'   => 'Haemoglobin',
            'unit'        => 'g/dL',
            'gender'      => 'female',
            'age_min'     => 18,
            'age_max'     => 120,
            'normal_low'  => 12.0,
            'normal_high' => 16.0,
            'critical_low'=> 7.0,
        ]);

        $range = LabReferenceRange::forLoinc('718-7', 'female', 30)->first();
        $this->assertNotNull($range);
        $this->assertEquals(12.0, $range->normal_low);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Lab/RadiologyReportTest.php tests/Feature/Lab/LabReferenceRangeTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_500003_create_radiology_reports_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('radiology_reports', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('dicom_study_id')->nullable()->constrained('dicom_studies')->nullOnDelete();
            $table->foreignUuid('lab_order_id')->nullable()->constrained('lab_orders')->nullOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('radiologist_id')->constrained('users')->cascadeOnDelete();
            $table->text('findings');
            $table->text('impression');
            $table->text('recommendations')->nullable();
            $table->enum('status', ['draft','preliminary','final','amended'])->default('draft');
            $table->timestamp('reported_at')->nullable();
            $table->boolean('distributed')->default(false);
            $table->timestamp('distributed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('radiology_reports'); }
};
```

```php
<?php
// database/migrations/2026_05_26_500004_create_lab_reference_ranges_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_reference_ranges', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('loinc_code', 30)->nullable();
            $table->string('test_name');
            $table->string('unit', 30);
            $table->enum('gender', ['male','female','all'])->default('all');
            $table->unsignedTinyInteger('age_min')->default(0);
            $table->unsignedTinyInteger('age_max')->default(120);
            $table->decimal('normal_low', 10, 3)->nullable();
            $table->decimal('normal_high', 10, 3)->nullable();
            $table->decimal('critical_low', 10, 3)->nullable();
            $table->decimal('critical_high', 10, 3)->nullable();
            $table->index(['loinc_code', 'gender']);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('lab_reference_ranges'); }
};
```

- [ ] **Step 4: Create models**

```php
<?php
// app/Models/RadiologyReport.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RadiologyReport extends Model
{
    use HasUuids;

    protected $fillable = [
        'dicom_study_id','lab_order_id','patient_id','radiologist_id',
        'findings','impression','recommendations','status',
        'reported_at','distributed','distributed_at',
    ];

    protected $casts = [
        'reported_at'    => 'datetime',
        'distributed'    => 'boolean',
        'distributed_at' => 'datetime',
    ];

    public function dicomStudy()   { return $this->belongsTo(DicomStudy::class); }
    public function labOrder()     { return $this->belongsTo(LabOrder::class); }
    public function patient()      { return $this->belongsTo(Patient::class); }
    public function radiologist()  { return $this->belongsTo(User::class, 'radiologist_id'); }
}
```

```php
<?php
// app/Models/LabReferenceRange.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LabReferenceRange extends Model
{
    use HasUuids;

    protected $fillable = [
        'loinc_code','test_name','unit','gender',
        'age_min','age_max','normal_low','normal_high',
        'critical_low','critical_high',
    ];

    public function scopeForLoinc($query, string $loinc, string $gender, int $age)
    {
        return $query->where('loinc_code', $loinc)
            ->where(fn($q) => $q->where('gender', $gender)->orWhere('gender', 'all'))
            ->where('age_min', '<=', $age)
            ->where('age_max', '>=', $age);
    }
}
```

- [ ] **Step 5: Run all lab tests + full suite**

```bash
php artisan migrate && \
php artisan test tests/Feature/Lab/RadiologyReportTest.php && \
php artisan test tests/Feature/Lab/LabReferenceRangeTest.php && \
php artisan test
```
Expected: All green.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_500003_* database/migrations/2026_05_26_500004_* \
  app/Models/RadiologyReport.php app/Models/LabReferenceRange.php \
  tests/Feature/Lab/RadiologyReportTest.php tests/Feature/Lab/LabReferenceRangeTest.php
git commit -m "feat(lab): radiology reports + reference range management"
```
