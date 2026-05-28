# Wave PR-6: Pharmacy Completions

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add 2 missing pharmacy features — drug formulary management per facility, and controlled substance tracking with dispense audit trail.

**Architecture:** DrugFormulary is a new table linking facilities to approved drugs. ControlledSubstanceRecord tracks every dispense of a scheduled drug with a mandatory audit trail. Both are additive — no existing pharmacy or inventory models are changed.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, PHPUnit

---

## File Map

```
database/migrations/
  2026_05_26_600001_create_drug_formularies_table.php
  2026_05_26_600002_create_controlled_substance_records_table.php
app/Models/
  DrugFormulary.php
  ControlledSubstanceRecord.php
tests/Feature/Pharmacy/
  DrugFormularyTest.php
  ControlledSubstanceTest.php
```

---

### Task 1: Drug Formulary Management

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Pharmacy/DrugFormularyTest.php
namespace Tests\Feature\Pharmacy;

use App\Models\Facility;
use App\Models\DrugFormulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrugFormularyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_drug_to_facility_formulary(): void
    {
        $facility = Facility::factory()->create();

        $entry = DrugFormulary::create([
            'facility_id'     => $facility->id,
            'drug_name'       => 'Metformin 500mg',
            'generic_name'    => 'Metformin hydrochloride',
            'drug_class'      => 'Biguanide antidiabetic',
            'dosage_form'     => 'tablet',
            'strength'        => '500mg',
            'route'           => 'oral',
            'is_approved'     => true,
            'requires_preauth'=> false,
        ]);

        $this->assertTrue($entry->is_approved);
        $this->assertEquals('tablet', $entry->dosage_form);
    }

    public function test_can_filter_approved_drugs_for_facility(): void
    {
        $facility = Facility::factory()->create();

        DrugFormulary::create(['facility_id'=>$facility->id,'drug_name'=>'Metformin 500mg','generic_name'=>'Metformin','drug_class'=>'Antidiabetic','dosage_form'=>'tablet','strength'=>'500mg','route'=>'oral','is_approved'=>true,'requires_preauth'=>false]);
        DrugFormulary::create(['facility_id'=>$facility->id,'drug_name'=>'Insulin Glargine','generic_name'=>'Insulin glargine','drug_class'=>'Insulin analogue','dosage_form'=>'injection','strength'=>'100IU/mL','route'=>'subcutaneous','is_approved'=>false,'requires_preauth'=>true]);

        $approved = DrugFormulary::where('facility_id', $facility->id)
            ->where('is_approved', true)->get();

        $this->assertCount(1, $approved);
        $this->assertEquals('Metformin 500mg', $approved->first()->drug_name);
    }

    public function test_formulary_entry_can_require_preauth(): void
    {
        $facility = Facility::factory()->create();

        $entry = DrugFormulary::create([
            'facility_id'     => $facility->id,
            'drug_name'       => 'Erythropoietin',
            'generic_name'    => 'Epoetin alfa',
            'drug_class'      => 'Haematopoietic agent',
            'dosage_form'     => 'injection',
            'strength'        => '4000IU/mL',
            'route'           => 'subcutaneous',
            'is_approved'     => true,
            'requires_preauth'=> true,
        ]);

        $this->assertTrue($entry->requires_preauth);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Pharmacy/DrugFormularyTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_600001_create_drug_formularies_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drug_formularies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('drug_name');
            $table->string('generic_name');
            $table->string('drug_class')->nullable();
            $table->enum('dosage_form', ['tablet','capsule','injection','syrup','cream','inhaler','patch','drops','other'])
                ->default('tablet');
            $table->string('strength', 50)->nullable();
            $table->enum('route', ['oral','intravenous','intramuscular','subcutaneous','topical','inhaled','rectal','other'])
                ->default('oral');
            $table->string('atc_code', 20)->nullable();     // WHO ATC code
            $table->boolean('is_approved')->default(true);
            $table->boolean('requires_preauth')->default(false);
            $table->boolean('is_controlled')->default(false);
            $table->text('restrictions')->nullable();
            $table->unique(['facility_id', 'drug_name', 'strength', 'route']);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('drug_formularies'); }
};
```

- [ ] **Step 4: Create DrugFormulary model**

```php
<?php
// app/Models/DrugFormulary.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DrugFormulary extends Model
{
    use HasUuids;

    protected $fillable = [
        'facility_id','drug_name','generic_name','drug_class',
        'dosage_form','strength','route','atc_code',
        'is_approved','requires_preauth','is_controlled','restrictions',
    ];

    protected $casts = [
        'is_approved'      => 'boolean',
        'requires_preauth' => 'boolean',
        'is_controlled'    => 'boolean',
    ];

    public function facility() { return $this->belongsTo(Facility::class); }

    public function scopeApproved($query) { return $query->where('is_approved', true); }
    public function scopeControlled($query) { return $query->where('is_controlled', true); }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Pharmacy/DrugFormularyTest.php
```
Expected: All 3 PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_600001_* app/Models/DrugFormulary.php \
  tests/Feature/Pharmacy/DrugFormularyTest.php
git commit -m "feat(pharmacy): drug formulary management per facility"
```

---

### Task 2: Controlled Substance Tracking

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Pharmacy/ControlledSubstanceTest.php
namespace Tests\Feature\Pharmacy;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\Prescription;
use App\Models\ControlledSubstanceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlledSubstanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_record_controlled_substance_dispense(): void
    {
        $patient     = Patient::factory()->create();
        $prescriber  = User::factory()->create();
        $dispenser   = User::factory()->create();
        $facility    = Facility::factory()->create();

        $prescription = Prescription::create([
            'patient_id'    => $patient->id,
            'prescribed_by' => $prescriber->id,
            'facility_id'   => $facility->id,
            'status'        => 'active',
        ]);

        $record = ControlledSubstanceRecord::create([
            'prescription_id'   => $prescription->id,
            'patient_id'        => $patient->id,
            'facility_id'       => $facility->id,
            'prescribed_by'     => $prescriber->id,
            'dispensed_by'      => $dispenser->id,
            'drug_name'         => 'Morphine Sulfate',
            'drug_schedule'     => 'schedule_2',
            'quantity_dispensed'=> 30,
            'unit'              => 'tablet',
            'dispensed_at'      => now(),
            'batch_number'      => 'BATCH-2026-001',
        ]);

        $this->assertEquals('Morphine Sulfate', $record->drug_name);
        $this->assertEquals('schedule_2', $record->drug_schedule);
        $this->assertEquals(30, $record->quantity_dispensed);
    }

    public function test_cannot_dispense_without_valid_prescription(): void
    {
        $patient  = Patient::factory()->create();
        $dispenser = User::factory()->create();
        $facility = Facility::factory()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Attempt to create a record with a non-existent prescription_id
        ControlledSubstanceRecord::create([
            'prescription_id'   => '00000000-0000-0000-0000-000000000000',
            'patient_id'        => $patient->id,
            'facility_id'       => $facility->id,
            'prescribed_by'     => $dispenser->id,
            'dispensed_by'      => $dispenser->id,
            'drug_name'         => 'Tramadol',
            'drug_schedule'     => 'schedule_4',
            'quantity_dispensed'=> 20,
            'unit'              => 'tablet',
            'dispensed_at'      => now(),
        ]);
    }

    public function test_controlled_substance_audit_trail_is_immutable(): void
    {
        $patient    = Patient::factory()->create();
        $prescriber = User::factory()->create();
        $dispenser  = User::factory()->create();
        $facility   = Facility::factory()->create();

        $prescription = Prescription::create([
            'patient_id'    => $patient->id,
            'prescribed_by' => $prescriber->id,
            'facility_id'   => $facility->id,
            'status'        => 'active',
        ]);

        $record = ControlledSubstanceRecord::create([
            'prescription_id'   => $prescription->id,
            'patient_id'        => $patient->id,
            'facility_id'       => $facility->id,
            'prescribed_by'     => $prescriber->id,
            'dispensed_by'      => $dispenser->id,
            'drug_name'         => 'Diazepam',
            'drug_schedule'     => 'schedule_4',
            'quantity_dispensed'=> 10,
            'unit'              => 'tablet',
            'dispensed_at'      => now(),
        ]);

        // Immutability guard: model does not allow updates
        $this->expectException(\LogicException::class);
        $record->update(['quantity_dispensed' => 5]);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Pharmacy/ControlledSubstanceTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_600002_create_controlled_substance_records_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('controlled_substance_records', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('prescribed_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('dispensed_by')->constrained('users')->cascadeOnDelete();
            $table->string('drug_name');
            $table->enum('drug_schedule', ['schedule_1','schedule_2','schedule_3','schedule_4','schedule_5']);
            $table->unsignedInteger('quantity_dispensed');
            $table->string('unit', 30);
            $table->timestamp('dispensed_at');
            $table->string('batch_number', 50)->nullable();
            $table->string('witness_id')->nullable(); // optional second provider witness
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('controlled_substance_records'); }
};
```

- [ ] **Step 4: Create ControlledSubstanceRecord model with immutability guard**

```php
<?php
// app/Models/ControlledSubstanceRecord.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ControlledSubstanceRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'prescription_id','patient_id','facility_id',
        'prescribed_by','dispensed_by','drug_name','drug_schedule',
        'quantity_dispensed','unit','dispensed_at','batch_number',
        'witness_id','notes',
    ];

    protected $casts = ['dispensed_at' => 'datetime'];

    /**
     * Controlled substance records are an immutable audit trail.
     * Updates are not permitted after creation.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \LogicException('ControlledSubstanceRecord is immutable. Corrections require a new record.');
    }

    public function prescription() { return $this->belongsTo(Prescription::class); }
    public function patient()      { return $this->belongsTo(Patient::class); }
    public function facility()     { return $this->belongsTo(Facility::class); }
    public function prescriber()   { return $this->belongsTo(User::class, 'prescribed_by'); }
    public function dispenser()    { return $this->belongsTo(User::class, 'dispensed_by'); }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Pharmacy/ControlledSubstanceTest.php
```
Expected: All 3 PASS.

- [ ] **Step 6: Run full test suite**

```bash
php artisan test
```
Expected: All green.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_600002_* app/Models/ControlledSubstanceRecord.php \
  tests/Feature/Pharmacy/ControlledSubstanceTest.php
git commit -m "feat(pharmacy): controlled substance tracking with immutable audit trail"
```
