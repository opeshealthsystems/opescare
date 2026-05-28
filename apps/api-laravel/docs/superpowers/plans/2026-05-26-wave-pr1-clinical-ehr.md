# Wave PR-1: Clinical EHR Completion

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete four EHR gaps — medication reconciliation + drug interaction checking, problem list / diagnosis lifecycle, maternity/ANC module, and pharmacy routing for the existing e-prescribing stub.

**Architecture:** All new features follow the existing Module pattern under `app/Modules/`. Each module has a Service class, model(s), migration(s), controller(s), and feature tests. No existing models, routes, or controllers are modified except to extend `$fillable` with new fields. All tests use `RefreshDatabase`.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL (prod), SQLite (tests), PHPUnit

---

## File Map

```
app/Models/MedicationReconciliation.php      (new)
app/Models/DrugInteractionAlert.php          (new)
app/Models/ProblemList.php                   (new)
app/Models/AntenatalRecord.php               (new)
app/Models/AntenatalVisit.php                (new)
app/Models/PharmacyRoute.php                 (new — pharmacy routing for prescriptions)
app/Modules/ClinicalDecisionSupport/
    Services/DrugInteractionService.php       (new)
app/Modules/OperationalFlow/
    Services/MedicationReconciliationService.php (new)
app/Modules/Maternity/                        (new module)
    Services/AntenatalCareService.php         (new)
app/Http/Controllers/Api/V1/
    Clinical/MedicationReconciliationController.php (new)
    Clinical/ProblemListController.php         (new)
    Clinical/AntenatalController.php           (new)
    Clinical/PharmacyRouteController.php       (new)
database/migrations/
    2026_05_26_100001_create_medication_reconciliations_table.php
    2026_05_26_100002_create_drug_interaction_alerts_table.php
    2026_05_26_100003_create_problem_lists_table.php
    2026_05_26_100004_create_antenatal_records_table.php
    2026_05_26_100005_create_antenatal_visits_table.php
    2026_05_26_100006_create_pharmacy_routes_table.php
    2026_05_26_100007_add_pharmacy_route_id_to_prescriptions.php
tests/Feature/Clinical/
    MedicationReconciliationTest.php
    ProblemListTest.php
    AntenatalCareTest.php
    PharmacyRoutingTest.php
routes/api.php                               (extend — add new route groups)
```

---

### Task 1: Medication Reconciliation + Drug Interaction

**Files:**
- Create: `database/migrations/2026_05_26_100001_create_medication_reconciliations_table.php`
- Create: `database/migrations/2026_05_26_100002_create_drug_interaction_alerts_table.php`
- Create: `app/Models/MedicationReconciliation.php`
- Create: `app/Models/DrugInteractionAlert.php`
- Create: `app/Modules/OperationalFlow/Services/MedicationReconciliationService.php`
- Create: `app/Modules/ClinicalDecisionSupport/Services/DrugInteractionService.php`
- Create: `app/Http/Controllers/Api/V1/Clinical/MedicationReconciliationController.php`
- Test: `tests/Feature/Clinical/MedicationReconciliationTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/Clinical/MedicationReconciliationTest.php
namespace Tests\Feature\Clinical;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\MedicationReconciliation;
use App\Models\DrugInteractionAlert;
use App\Modules\OperationalFlow\Services\MedicationReconciliationService;
use App\Modules\ClinicalDecisionSupport\Services\DrugInteractionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicationReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_reconciliation_record(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new MedicationReconciliationService();
        $rec = $service->createReconciliation(
            patientId:   $patient->id,
            providerId:  $provider->id,
            facilityId:  $facility->id,
            medications: [
                ['name' => 'Metformin', 'dose' => '500mg', 'frequency' => 'BD', 'source' => 'patient_reported'],
                ['name' => 'Atorvastatin', 'dose' => '20mg', 'frequency' => 'OD', 'source' => 'pharmacy_record'],
            ],
            notes: 'Admission reconciliation'
        );

        $this->assertInstanceOf(MedicationReconciliation::class, $rec);
        $this->assertEquals($patient->id, $rec->patient_id);
        $this->assertCount(2, $rec->medications);
        $this->assertEquals('pending_review', $rec->status);
    }

    public function test_drug_interaction_check_detects_known_pair(): void
    {
        $service = new DrugInteractionService();

        $alerts = $service->checkInteractions([
            ['name' => 'Warfarin', 'dose' => '5mg'],
            ['name' => 'Aspirin', 'dose' => '100mg'],
        ]);

        $this->assertNotEmpty($alerts);
        $this->assertEquals('major', $alerts[0]['severity']);
    }

    public function test_drug_interaction_no_alerts_for_safe_pair(): void
    {
        $service = new DrugInteractionService();
        $alerts = $service->checkInteractions([
            ['name' => 'Metformin', 'dose' => '500mg'],
            ['name' => 'Vitamin C', 'dose' => '500mg'],
        ]);
        $this->assertEmpty($alerts);
    }

    public function test_reconciliation_hard_stop_blocks_contraindicated_drug(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new MedicationReconciliationService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('HARD_STOP_CONTRAINDICATION');

        $service->createReconciliation(
            patientId:   $patient->id,
            providerId:  $provider->id,
            facilityId:  $facility->id,
            medications: [
                ['name' => 'Warfarin', 'dose' => '5mg', 'frequency' => 'OD', 'source' => 'current'],
                ['name' => 'Aspirin', 'dose' => '100mg', 'frequency' => 'OD', 'source' => 'new', 'flag_hard_stop' => true],
            ],
        );
    }
}
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
php artisan test tests/Feature/Clinical/MedicationReconciliationTest.php
```
Expected: FAIL — class not found errors.

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_100001_create_medication_reconciliations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medication_reconciliations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->json('medications');           // array of {name, dose, frequency, source}
            $table->text('notes')->nullable();
            $table->enum('status', ['pending_review', 'reviewed', 'completed'])->default('pending_review');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_reconciliations');
    }
};
```

```php
<?php
// database/migrations/2026_05_26_100002_create_drug_interaction_alerts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drug_interaction_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('reconciliation_id')
                ->constrained('medication_reconciliations')->cascadeOnDelete();
            $table->string('drug_a');
            $table->string('drug_b');
            $table->enum('severity', ['minor', 'moderate', 'major', 'contraindicated']);
            $table->text('description');
            $table->boolean('is_hard_stop')->default(false);
            $table->boolean('acknowledged')->default(false);
            $table->foreignUuid('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_interaction_alerts');
    }
};
```

- [ ] **Step 4: Create models**

```php
<?php
// app/Models/MedicationReconciliation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MedicationReconciliation extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id', 'provider_id', 'facility_id',
        'medications', 'notes', 'status', 'reviewed_at',
    ];

    protected $casts = [
        'medications'  => 'array',
        'reviewed_at'  => 'datetime',
    ];

    public function patient()   { return $this->belongsTo(Patient::class); }
    public function provider()  { return $this->belongsTo(User::class, 'provider_id'); }
    public function facility()  { return $this->belongsTo(Facility::class); }
    public function alerts()    { return $this->hasMany(DrugInteractionAlert::class, 'reconciliation_id'); }
}
```

```php
<?php
// app/Models/DrugInteractionAlert.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DrugInteractionAlert extends Model
{
    use HasUuids;

    protected $fillable = [
        'reconciliation_id', 'drug_a', 'drug_b', 'severity',
        'description', 'is_hard_stop', 'acknowledged',
        'acknowledged_by', 'acknowledged_at',
    ];

    protected $casts = [
        'is_hard_stop'    => 'boolean',
        'acknowledged'    => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function reconciliation() { return $this->belongsTo(MedicationReconciliation::class); }
    public function acknowledgedBy() { return $this->belongsTo(User::class, 'acknowledged_by'); }
}
```

- [ ] **Step 5: Create DrugInteractionService**

```php
<?php
// app/Modules/ClinicalDecisionSupport/Services/DrugInteractionService.php
namespace App\Modules\ClinicalDecisionSupport\Services;

class DrugInteractionService
{
    /**
     * Known interaction pairs — keyed as sorted pair "DRUG_A|DRUG_B".
     * Production: replace with RxNorm API call or local drug DB.
     */
    private array $knownInteractions = [
        'aspirin|warfarin' => [
            'severity'    => 'major',
            'description' => 'Concurrent use significantly increases bleeding risk.',
            'is_hard_stop'=> false,
        ],
        'clopidogrel|warfarin' => [
            'severity'    => 'major',
            'description' => 'Combined anticoagulation/antiplatelet therapy increases haemorrhage risk.',
            'is_hard_stop'=> false,
        ],
        'simvastatin|clarithromycin' => [
            'severity'    => 'major',
            'description' => 'CYP3A4 inhibition raises simvastatin plasma levels — myopathy risk.',
            'is_hard_stop'=> false,
        ],
        'methotrexate|nsaids' => [
            'severity'    => 'contraindicated',
            'description' => 'NSAIDs reduce methotrexate clearance — severe toxicity risk.',
            'is_hard_stop'=> true,
        ],
    ];

    /**
     * @param  array $medications  Each element: ['name' => string, 'dose' => string, ...]
     * @return array               Alert records (empty if none found)
     */
    public function checkInteractions(array $medications): array
    {
        $names  = array_map(fn($m) => strtolower(trim($m['name'])), $medications);
        $alerts = [];

        for ($i = 0; $i < count($names); $i++) {
            for ($j = $i + 1; $j < count($names); $j++) {
                $pair = implode('|', array_values(array_sort([$names[$i], $names[$j]])));
                if (isset($this->knownInteractions[$pair])) {
                    $interaction = $this->knownInteractions[$pair];
                    $alerts[] = array_merge($interaction, [
                        'drug_a' => $names[$i],
                        'drug_b' => $names[$j],
                    ]);
                }
            }
        }

        return $alerts;
    }
}
```

- [ ] **Step 6: Create MedicationReconciliationService**

```php
<?php
// app/Modules/OperationalFlow/Services/MedicationReconciliationService.php
namespace App\Modules\OperationalFlow\Services;

use App\Models\DrugInteractionAlert;
use App\Models\MedicationReconciliation;
use App\Modules\ClinicalDecisionSupport\Services\DrugInteractionService;
use Illuminate\Support\Facades\DB;

class MedicationReconciliationService
{
    public function __construct(
        private DrugInteractionService $interactionService = new DrugInteractionService()
    ) {}

    /**
     * Create a reconciliation record. Throws HARD_STOP_CONTRAINDICATION if any
     * medication marked flag_hard_stop=true has a contraindicated interaction.
     */
    public function createReconciliation(
        string  $patientId,
        string  $providerId,
        string  $facilityId,
        array   $medications,
        ?string $notes = null
    ): MedicationReconciliation {
        $alerts = $this->interactionService->checkInteractions($medications);

        // Hard-stop: any new medication (flag_hard_stop=true) with a contraindicated interaction
        foreach ($medications as $med) {
            if (!empty($med['flag_hard_stop'])) {
                foreach ($alerts as $alert) {
                    if (
                        ($alert['drug_a'] === strtolower($med['name']) ||
                         $alert['drug_b'] === strtolower($med['name'])) &&
                        $alert['is_hard_stop']
                    ) {
                        throw new \Exception('HARD_STOP_CONTRAINDICATION: ' . $alert['description']);
                    }
                }
            }
        }

        return DB::transaction(function () use (
            $patientId, $providerId, $facilityId, $medications, $notes, $alerts
        ) {
            $rec = MedicationReconciliation::create([
                'patient_id'  => $patientId,
                'provider_id' => $providerId,
                'facility_id' => $facilityId,
                'medications' => $medications,
                'notes'       => $notes,
                'status'      => 'pending_review',
            ]);

            foreach ($alerts as $alert) {
                DrugInteractionAlert::create(array_merge($alert, [
                    'reconciliation_id' => $rec->id,
                ]));
            }

            return $rec;
        });
    }

    public function acknowledge(string $alertId, string $userId): DrugInteractionAlert
    {
        $alert = DrugInteractionAlert::findOrFail($alertId);
        $alert->update([
            'acknowledged'    => true,
            'acknowledged_by' => $userId,
            'acknowledged_at' => now(),
        ]);
        return $alert;
    }
}
```

- [ ] **Step 7: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Clinical/MedicationReconciliationTest.php
```
Expected: All 4 tests PASS.

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_05_26_100001_* database/migrations/2026_05_26_100002_* \
  app/Models/MedicationReconciliation.php app/Models/DrugInteractionAlert.php \
  app/Modules/OperationalFlow/Services/MedicationReconciliationService.php \
  app/Modules/ClinicalDecisionSupport/Services/DrugInteractionService.php \
  tests/Feature/Clinical/MedicationReconciliationTest.php
git commit -m "feat(clinical): medication reconciliation + drug interaction checking"
```

---

### Task 2: Problem List / Diagnosis Lifecycle (ICD-10/11)

**Files:**
- Create: `database/migrations/2026_05_26_100003_create_problem_lists_table.php`
- Create: `app/Models/ProblemList.php`
- Create: `app/Http/Controllers/Api/V1/Clinical/ProblemListController.php`
- Test: `tests/Feature/Clinical/ProblemListTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Clinical/ProblemListTest.php
namespace Tests\Feature\Clinical;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\ProblemList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProblemListTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_active_problem(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();

        $problem = ProblemList::create([
            'patient_id'   => $patient->id,
            'provider_id'  => $provider->id,
            'icd_code'     => 'E11.9',
            'icd_version'  => '10',
            'description'  => 'Type 2 diabetes mellitus without complications',
            'onset_date'   => '2020-01-15',
            'status'       => 'active',
            'priority'     => 'high',
        ]);

        $this->assertEquals('E11.9', $problem->icd_code);
        $this->assertEquals('active', $problem->status);
    }

    public function test_can_resolve_problem(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();

        $problem = ProblemList::create([
            'patient_id'  => $patient->id,
            'provider_id' => $provider->id,
            'icd_code'    => 'J06.9',
            'icd_version' => '10',
            'description' => 'Acute upper respiratory infection',
            'status'      => 'active',
            'priority'    => 'low',
        ]);

        $problem->update(['status' => 'resolved', 'resolved_date' => now()->toDateString()]);
        $this->assertEquals('resolved', $problem->fresh()->status);
    }

    public function test_problem_list_scoped_to_patient(): void
    {
        $p1 = Patient::factory()->create();
        $p2 = Patient::factory()->create();
        $provider = User::factory()->create();

        ProblemList::create(['patient_id'=>$p1->id,'provider_id'=>$provider->id,'icd_code'=>'E11.9','icd_version'=>'10','description'=>'Diabetes','status'=>'active','priority'=>'high']);
        ProblemList::create(['patient_id'=>$p2->id,'provider_id'=>$provider->id,'icd_code'=>'I10','icd_version'=>'10','description'=>'Hypertension','status'=>'active','priority'=>'high']);

        $this->assertCount(1, ProblemList::where('patient_id', $p1->id)->get());
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Clinical/ProblemListTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_100003_create_problem_lists_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('problem_lists', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->string('icd_code', 20);
            $table->enum('icd_version', ['10', '11'])->default('10');
            $table->text('description');
            $table->date('onset_date')->nullable();
            $table->date('resolved_date')->nullable();
            $table->enum('status', ['active', 'resolved', 'inactive', 'entered_in_error'])->default('active');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('notes')->nullable();
            $table->index(['patient_id', 'status']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_lists');
    }
};
```

- [ ] **Step 4: Create model**

```php
<?php
// app/Models/ProblemList.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProblemList extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id', 'provider_id', 'icd_code', 'icd_version',
        'description', 'onset_date', 'resolved_date', 'status',
        'priority', 'notes',
    ];

    protected $casts = [
        'onset_date'    => 'date',
        'resolved_date' => 'date',
    ];

    public function patient()  { return $this->belongsTo(Patient::class); }
    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }

    public function scopeActive($query)   { return $query->where('status', 'active'); }
    public function scopeResolved($query) { return $query->where('status', 'resolved'); }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Clinical/ProblemListTest.php
```
Expected: All 3 tests PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_100003_* app/Models/ProblemList.php \
  tests/Feature/Clinical/ProblemListTest.php
git commit -m "feat(clinical): problem list with ICD-10/11 lifecycle management"
```

---

### Task 3: Maternity / Antenatal Care Module

**Files:**
- Create: `database/migrations/2026_05_26_100004_create_antenatal_records_table.php`
- Create: `database/migrations/2026_05_26_100005_create_antenatal_visits_table.php`
- Create: `app/Models/AntenatalRecord.php`
- Create: `app/Models/AntenatalVisit.php`
- Create: `app/Modules/Maternity/Services/AntenatalCareService.php`
- Test: `tests/Feature/Clinical/AntenatalCareTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Clinical/AntenatalCareTest.php
namespace Tests\Feature\Clinical;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\AntenatalRecord;
use App\Models\AntenatalVisit;
use App\Modules\Maternity\Services\AntenatalCareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntenatalCareTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_open_antenatal_record(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new AntenatalCareService();
        $record  = $service->openRecord(
            patientId:      $patient->id,
            providerId:     $provider->id,
            facilityId:     $facility->id,
            lmpDate:        '2026-01-01',
            gravida:        2,
            para:           1,
        );

        $this->assertInstanceOf(AntenatalRecord::class, $record);
        $this->assertEquals($patient->id, $record->patient_id);
        $this->assertEquals(2, $record->gravida);
        $this->assertNotNull($record->estimated_delivery_date);
    }

    public function test_edd_calculated_from_lmp(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new AntenatalCareService();
        $record  = $service->openRecord($patient->id, $provider->id, $facility->id, '2026-01-01', 1, 0);

        // EDD = LMP + 280 days (Naegele's rule)
        $expectedEdd = \Carbon\Carbon::parse('2026-01-01')->addDays(280)->toDateString();
        $this->assertEquals($expectedEdd, $record->estimated_delivery_date->toDateString());
    }

    public function test_can_record_antenatal_visit(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new AntenatalCareService();
        $record  = $service->openRecord($patient->id, $provider->id, $facility->id, '2026-01-01', 1, 0);

        $visit = $service->recordVisit(
            recordId:      $record->id,
            providerId:    $provider->id,
            visitDate:     '2026-02-15',
            gestationalAge:  6,
            bloodPressure:   '110/70',
            fetalHeartRate:  148,
            notes:           'All normal',
        );

        $this->assertInstanceOf(AntenatalVisit::class, $visit);
        $this->assertEquals(6, $visit->gestational_age_weeks);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Clinical/AntenatalCareTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_100004_create_antenatal_records_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('antenatal_records', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->date('lmp_date');
            $table->date('estimated_delivery_date');
            $table->unsignedTinyInteger('gravida')->default(1);
            $table->unsignedTinyInteger('para')->default(0);
            $table->text('risk_factors')->nullable();
            $table->enum('status', ['active', 'delivered', 'closed'])->default('active');
            $table->date('delivery_date')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('antenatal_records'); }
};
```

```php
<?php
// database/migrations/2026_05_26_100005_create_antenatal_visits_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('antenatal_visits', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('antenatal_record_id')
                ->constrained('antenatal_records')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->date('visit_date');
            $table->unsignedTinyInteger('gestational_age_weeks');
            $table->string('blood_pressure', 20)->nullable();
            $table->unsignedSmallInteger('fetal_heart_rate')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('fundal_height_cm', 5, 1)->nullable();
            $table->string('fetal_presentation', 50)->nullable();
            $table->text('notes')->nullable();
            $table->text('next_visit_plan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('antenatal_visits'); }
};
```

- [ ] **Step 4: Create models**

```php
<?php
// app/Models/AntenatalRecord.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AntenatalRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','provider_id','facility_id','lmp_date',
        'estimated_delivery_date','gravida','para','risk_factors',
        'status','delivery_date','delivery_notes',
    ];

    protected $casts = [
        'lmp_date'               => 'date',
        'estimated_delivery_date'=> 'date',
        'delivery_date'          => 'date',
    ];

    public function patient()  { return $this->belongsTo(Patient::class); }
    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }
    public function facility() { return $this->belongsTo(Facility::class); }
    public function visits()   { return $this->hasMany(AntenatalVisit::class); }
}
```

```php
<?php
// app/Models/AntenatalVisit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AntenatalVisit extends Model
{
    use HasUuids;

    protected $fillable = [
        'antenatal_record_id','provider_id','visit_date',
        'gestational_age_weeks','blood_pressure','fetal_heart_rate',
        'weight_kg','fundal_height_cm','fetal_presentation','notes','next_visit_plan',
    ];

    protected $casts = ['visit_date' => 'date'];

    public function record()   { return $this->belongsTo(AntenatalRecord::class, 'antenatal_record_id'); }
    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }
}
```

- [ ] **Step 5: Create AntenatalCareService**

```php
<?php
// app/Modules/Maternity/Services/AntenatalCareService.php
namespace App\Modules\Maternity\Services;

use App\Models\AntenatalRecord;
use App\Models\AntenatalVisit;
use Carbon\Carbon;

class AntenatalCareService
{
    public function openRecord(
        string $patientId,
        string $providerId,
        string $facilityId,
        string $lmpDate,
        int    $gravida,
        int    $para,
        ?string $riskFactors = null
    ): AntenatalRecord {
        $edd = Carbon::parse($lmpDate)->addDays(280)->toDateString(); // Naegele's rule

        return AntenatalRecord::create([
            'patient_id'              => $patientId,
            'provider_id'             => $providerId,
            'facility_id'             => $facilityId,
            'lmp_date'                => $lmpDate,
            'estimated_delivery_date' => $edd,
            'gravida'                 => $gravida,
            'para'                    => $para,
            'risk_factors'            => $riskFactors,
            'status'                  => 'active',
        ]);
    }

    public function recordVisit(
        string  $recordId,
        string  $providerId,
        string  $visitDate,
        int     $gestationalAge,
        ?string $bloodPressure   = null,
        ?int    $fetalHeartRate  = null,
        ?float  $weightKg        = null,
        ?float  $fundalHeight    = null,
        ?string $presentation    = null,
        ?string $notes           = null,
        ?string $nextVisitPlan   = null,
    ): AntenatalVisit {
        return AntenatalVisit::create([
            'antenatal_record_id'   => $recordId,
            'provider_id'           => $providerId,
            'visit_date'            => $visitDate,
            'gestational_age_weeks' => $gestationalAge,
            'blood_pressure'        => $bloodPressure,
            'fetal_heart_rate'      => $fetalHeartRate,
            'weight_kg'             => $weightKg,
            'fundal_height_cm'      => $fundalHeight,
            'fetal_presentation'    => $presentation,
            'notes'                 => $notes,
            'next_visit_plan'       => $nextVisitPlan,
        ]);
    }
}
```

- [ ] **Step 6: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Clinical/AntenatalCareTest.php
```
Expected: All 3 tests PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_100004_* database/migrations/2026_05_26_100005_* \
  app/Models/AntenatalRecord.php app/Models/AntenatalVisit.php \
  app/Modules/Maternity/Services/AntenatalCareService.php \
  tests/Feature/Clinical/AntenatalCareTest.php
git commit -m "feat(maternity): antenatal care module with EDD calculation and visit tracking"
```

---

### Task 4: Pharmacy Routing for E-Prescribing

**Files:**
- Create: `database/migrations/2026_05_26_100006_create_pharmacy_routes_table.php`
- Create: `database/migrations/2026_05_26_100007_add_pharmacy_route_id_to_prescriptions.php`
- Create: `app/Models/PharmacyRoute.php`
- Test: `tests/Feature/Clinical/PharmacyRoutingTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Clinical/PharmacyRoutingTest.php
namespace Tests\Feature\Clinical;

use App\Models\Facility;
use App\Models\Prescription;
use App\Models\PharmacyRoute;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_pharmacy_route(): void
    {
        $facility = Facility::factory()->create();

        $route = PharmacyRoute::create([
            'facility_id'         => $facility->id,
            'pharmacy_name'       => 'Pharmacie Centrale Yaoundé',
            'pharmacy_type'       => 'in_facility',
            'contact_email'       => 'pharm@centraleyaounde.cm',
            'contact_phone'       => '+237 222 223 344',
            'routing_method'      => 'fax',
            'is_active'           => true,
        ]);

        $this->assertEquals('in_facility', $route->pharmacy_type);
        $this->assertTrue($route->is_active);
    }

    public function test_prescription_can_be_routed(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $route = PharmacyRoute::create([
            'facility_id'    => $facility->id,
            'pharmacy_name'  => 'External Pharmacy',
            'pharmacy_type'  => 'external',
            'routing_method' => 'api',
            'is_active'      => true,
        ]);

        // Existing Prescription model — extend with pharmacy_route_id
        $prescription = Prescription::create([
            'patient_id'        => $patient->id,
            'prescribed_by'     => $provider->id,
            'facility_id'       => $facility->id,
            'pharmacy_route_id' => $route->id,
            'status'            => 'pending',
        ]);

        $this->assertEquals($route->id, $prescription->pharmacy_route_id);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Clinical/PharmacyRoutingTest.php
```

- [ ] **Step 3: Create migration for pharmacy_routes**

```php
<?php
// database/migrations/2026_05_26_100006_create_pharmacy_routes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pharmacy_routes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('pharmacy_name');
            $table->enum('pharmacy_type', ['in_facility', 'external', 'online'])->default('in_facility');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->enum('routing_method', ['fax', 'api', 'print', 'sms'])->default('print');
            $table->string('api_endpoint')->nullable();
            $table->string('api_key_encrypted')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('pharmacy_routes'); }
};
```

- [ ] **Step 4: Add pharmacy_route_id to prescriptions**

```php
<?php
// database/migrations/2026_05_26_100007_add_pharmacy_route_id_to_prescriptions.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('prescriptions', 'pharmacy_route_id')) {
                $table->foreignUuid('pharmacy_route_id')
                    ->nullable()
                    ->constrained('pharmacy_routes')
                    ->nullOnDelete()
                    ->after('facility_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeignIfExists(['pharmacy_route_id']);
            $table->dropColumnIfExists('pharmacy_route_id');
        });
    }
};
```

- [ ] **Step 5: Create PharmacyRoute model**

```php
<?php
// app/Models/PharmacyRoute.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PharmacyRoute extends Model
{
    use HasUuids;

    protected $fillable = [
        'facility_id','pharmacy_name','pharmacy_type',
        'contact_email','contact_phone','routing_method',
        'api_endpoint','api_key_encrypted','is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
    protected $hidden = ['api_key_encrypted'];

    public function facility()      { return $this->belongsTo(Facility::class); }
    public function prescriptions() { return $this->hasMany(Prescription::class); }
}
```

- [ ] **Step 6: Add pharmacy_route_id to Prescription `$fillable`**

Open `app/Models/Prescription.php` and add `'pharmacy_route_id'` to the `$fillable` array. Do NOT remove any existing fields.

- [ ] **Step 7: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Clinical/PharmacyRoutingTest.php
```
Expected: Both tests PASS.

- [ ] **Step 8: Run full suite**

```bash
php artisan test
```
Expected: All existing tests still pass.

- [ ] **Step 9: Commit**

```bash
git add database/migrations/2026_05_26_100006_* database/migrations/2026_05_26_100007_* \
  app/Models/PharmacyRoute.php app/Models/Prescription.php \
  tests/Feature/Clinical/PharmacyRoutingTest.php
git commit -m "feat(pharmacy): pharmacy routing for e-prescriptions"
```
