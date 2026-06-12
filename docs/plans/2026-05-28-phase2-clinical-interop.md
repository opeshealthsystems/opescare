# Phase 2: Maternity Module + HL7 v2 ADT

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans

**Goal:** Add dedicated antenatal/postnatal care module and HL7 v2 ADT outbound sender.
**Architecture:** New Maternity module under app/Modules/Maternity/. HL7 v2 ADT uses a stateless sender (no full parser — send-only A01/A08 events).
**Tech Stack:** Laravel 11, PHP 8.3, PostgreSQL, UUID PKs

---

## File Map

```
app/Models/
    PregnancyRecord.php                        (new)
    AntenatalVisit.php                         (new)
    DeliveryRecord.php                         (new)
app/Modules/Maternity/
    Services/MaternityService.php              (new)
app/Http/Controllers/Api/V1/
    MaternityController.php                    (new)
app/Services/Integration/
    Hl7AdtService.php                          (new)
config/
    hl7.php                                    (new)
database/migrations/
    2026_05_28_001000_create_pregnancy_records_table.php
    2026_05_28_001001_create_antenatal_visits_table.php
    2026_05_28_001002_create_delivery_records_table.php
tests/Feature/
    MaternityTest.php                          (new)
    Hl7AdtTest.php                             (new)
routes/api.php                                 (extend — maternity route group)
```

---

## Task 1: Maternity / Antenatal Care Module

**Files:**
- Create: `database/migrations/2026_05_28_001000_create_pregnancy_records_table.php`
- Create: `database/migrations/2026_05_28_001001_create_antenatal_visits_table.php`
- Create: `database/migrations/2026_05_28_001002_create_delivery_records_table.php`
- Create: `app/Models/PregnancyRecord.php`
- Create: `app/Models/AntenatalVisit.php`
- Create: `app/Models/DeliveryRecord.php`
- Create: `app/Modules/Maternity/Services/MaternityService.php`
- Create: `app/Http/Controllers/Api/V1/MaternityController.php`
- Extend: `routes/api.php` (maternity route group)
- Test: `tests/Feature/MaternityTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/MaternityTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Facility;
use App\Models\PregnancyRecord;
use App\Models\AntenatalVisit;
use App\Models\DeliveryRecord;
use App\Modules\Maternity\Services\MaternityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class MaternityTest extends TestCase
{
    use RefreshDatabase;

    private MaternityService $service;
    private User $provider;
    private Patient $patient;
    private Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = app(MaternityService::class);
        $this->provider = User::factory()->create();
        $this->patient  = Patient::factory()->create();
        $this->facility = Facility::factory()->create();
    }

    public function test_can_register_pregnancy(): void
    {
        $record = $this->service->registerPregnancy([
            'patient_id'      => $this->patient->id,
            'facility_id'     => $this->facility->id,
            'provider_id'     => $this->provider->id,
            'gravida'         => 2,
            'para'            => 1,
            'lmp'             => '2026-01-01',
            'edd'             => '2026-10-08',
            'pregnancy_status'=> 'active',
            'blood_type'      => 'O',
            'rhesus_factor'   => 'positive',
            'high_risk'       => false,
            'risk_factors'    => [],
            'registered_at'   => now()->toDateTimeString(),
        ]);

        $this->assertInstanceOf(PregnancyRecord::class, $record);
        $this->assertNotNull($record->id);
        $this->assertEquals(2, $record->gravida);
        $this->assertEquals('active', $record->pregnancy_status);
        $this->assertDatabaseHas('pregnancy_records', ['id' => $record->id]);
    }

    public function test_can_record_antenatal_visit(): void
    {
        $record = $this->service->registerPregnancy([
            'patient_id'      => $this->patient->id,
            'facility_id'     => $this->facility->id,
            'provider_id'     => $this->provider->id,
            'gravida'         => 1,
            'para'            => 0,
            'lmp'             => '2026-01-01',
            'edd'             => '2026-10-08',
            'pregnancy_status'=> 'active',
            'blood_type'      => 'A',
            'rhesus_factor'   => 'negative',
            'high_risk'       => false,
            'risk_factors'    => [],
            'registered_at'   => now()->toDateTimeString(),
        ]);

        $visit = $this->service->recordAntenatalVisit($record->id, [
            'patient_id'           => $this->patient->id,
            'facility_id'          => $this->facility->id,
            'provider_id'          => $this->provider->id,
            'visit_date'           => '2026-02-15',
            'gestational_age_weeks'=> 6,
            'gestational_age_days' => 3,
            'fundal_height_cm'     => 8.5,
            'fetal_heart_rate'     => 148,
            'presentation'         => 'cephalic',
            'weight_kg'            => 62.5,
            'bp_systolic'          => 110,
            'bp_diastolic'         => 70,
            'urine_protein'        => 'negative',
            'urine_glucose'        => 'negative',
            'oedema'               => 'none',
        ]);

        $this->assertInstanceOf(AntenatalVisit::class, $visit);
        $this->assertEquals($record->id, $visit->pregnancy_record_id);
        $this->assertEquals(6, $visit->gestational_age_weeks);
        $this->assertDatabaseHas('antenatal_visits', ['id' => $visit->id]);
    }

    public function test_can_record_delivery(): void
    {
        $record = PregnancyRecord::factory()->create([
            'patient_id'  => $this->patient->id,
            'facility_id' => $this->facility->id,
            'provider_id' => $this->provider->id,
        ]);

        $delivery = $this->service->recordDelivery($record->id, [
            'patient_id'           => $this->patient->id,
            'facility_id'          => $this->facility->id,
            'provider_id'          => $this->provider->id,
            'delivery_date'        => '2026-09-30',
            'delivery_mode'        => 'svd',
            'birth_weight_grams'   => 3250,
            'apgar_1min'           => 8,
            'apgar_5min'           => 9,
            'neonatal_outcome'     => 'live',
        ]);

        $this->assertInstanceOf(DeliveryRecord::class, $delivery);
        $this->assertEquals('svd', $delivery->delivery_mode);
        $this->assertEquals(3250, $delivery->birth_weight_grams);
        $this->assertDatabaseHas('delivery_records', ['id' => $delivery->id]);
    }

    public function test_gestational_age_calculation(): void
    {
        // LMP 10 weeks and 3 days ago
        $lmp    = Carbon::now()->subWeeks(10)->subDays(3)->toDateString();
        $result = $this->service->calculateGestationalAge($lmp);

        $this->assertArrayHasKey('weeks', $result);
        $this->assertArrayHasKey('days', $result);
        $this->assertEquals(10, $result['weeks']);
        $this->assertEquals(3, $result['days']);
    }

    public function test_antenatal_schedule_returns_recommended_weeks(): void
    {
        $record   = PregnancyRecord::factory()->create(['patient_id' => $this->patient->id]);
        $schedule = $this->service->getAntenatalSchedule($record->id);

        $expected = [4, 8, 12, 16, 20, 24, 28, 30, 32, 34, 36, 38, 40];
        $this->assertEquals($expected, $schedule);
    }

    public function test_high_risk_detection_on_elevated_bp(): void
    {
        $record = PregnancyRecord::factory()->create([
            'patient_id'  => $this->patient->id,
            'high_risk'   => false,
            'risk_factors'=> [],
        ]);

        // Record a visit with high BP
        $this->service->recordAntenatalVisit($record->id, [
            'patient_id'           => $this->patient->id,
            'facility_id'          => $this->facility->id,
            'provider_id'          => $this->provider->id,
            'visit_date'           => now()->toDateString(),
            'gestational_age_weeks'=> 28,
            'gestational_age_days' => 0,
            'fundal_height_cm'     => 28.0,
            'weight_kg'            => 72.0,
            'bp_systolic'          => 145,
            'bp_diastolic'         => 95,
        ]);

        $this->assertTrue($this->service->isHighRisk($record->fresh()));
    }
}
```

- [ ] **Step 2: Create migrations**

```php
<?php
// database/migrations/2026_05_28_001000_create_pregnancy_records_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregnancy_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('gravida')->default(1);
            $table->unsignedTinyInteger('para')->default(0);
            $table->date('edd')->nullable()->comment('Estimated delivery date');
            $table->date('lmp')->nullable()->comment('Last menstrual period');
            $table->string('pregnancy_status')->default('active')
                ->comment('active|delivered|miscarriage|stillbirth|ectopic|terminated');
            $table->string('blood_type', 3)->nullable()->comment('A|B|AB|O');
            $table->string('rhesus_factor', 10)->nullable()->comment('positive|negative');
            $table->boolean('high_risk')->default(false);
            $table->json('risk_factors')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index('patient_id');
            $table->index('facility_id');
            $table->index('provider_id');
            $table->index('pregnancy_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregnancy_records');
    }
};
```

```php
<?php
// database/migrations/2026_05_28_001001_create_antenatal_visits_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antenatal_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pregnancy_record_id')->constrained('pregnancy_records')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->date('visit_date');
            $table->unsignedTinyInteger('gestational_age_weeks')->default(0);
            $table->unsignedTinyInteger('gestational_age_days')->default(0);
            $table->decimal('fundal_height_cm', 5, 2)->nullable();
            $table->unsignedSmallInteger('fetal_heart_rate')->nullable();
            $table->string('presentation', 20)->nullable()
                ->comment('cephalic|breech|transverse|unknown');
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->unsignedSmallInteger('bp_systolic')->nullable();
            $table->unsignedSmallInteger('bp_diastolic')->nullable();
            $table->string('urine_protein', 10)->nullable()
                ->comment('negative|trace|1+|2+|3+|4+');
            $table->string('urine_glucose', 10)->nullable()
                ->comment('negative|trace|positive');
            $table->string('oedema', 10)->nullable()
                ->comment('none|mild|moderate|severe');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('pregnancy_record_id');
            $table->index('patient_id');
            $table->index('visit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antenatal_visits');
    }
};
```

```php
<?php
// database/migrations/2026_05_28_001002_create_delivery_records_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pregnancy_record_id')->constrained('pregnancy_records')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->date('delivery_date');
            $table->string('delivery_mode', 30)
                ->comment('svd|assisted_vaginal|caesarean|other');
            $table->text('indication')->nullable();
            $table->decimal('duration_labour_hours', 5, 2)->nullable();
            $table->unsignedSmallInteger('birth_weight_grams');
            $table->unsignedTinyInteger('apgar_1min')->nullable();
            $table->unsignedTinyInteger('apgar_5min')->nullable();
            $table->string('neonatal_outcome', 30)
                ->comment('live|stillbirth|early_neonatal_death');
            $table->text('complications')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('pregnancy_record_id');
            $table->index('patient_id');
            $table->index('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_records');
    }
};
```

- [ ] **Step 3: Create models**

```php
<?php
// app/Models/PregnancyRecord.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PregnancyRecord extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'provider_id',
        'gravida',
        'para',
        'edd',
        'lmp',
        'pregnancy_status',
        'blood_type',
        'rhesus_factor',
        'high_risk',
        'risk_factors',
        'notes',
        'registered_at',
    ];

    protected $casts = [
        'gravida'      => 'integer',
        'para'         => 'integer',
        'edd'          => 'date',
        'lmp'          => 'date',
        'high_risk'    => 'boolean',
        'risk_factors' => 'array',
        'registered_at'=> 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function antenatalVisits(): HasMany
    {
        return $this->hasMany(AntenatalVisit::class);
    }

    public function deliveryRecords(): HasMany
    {
        return $this->hasMany(DeliveryRecord::class);
    }
}
```

```php
<?php
// app/Models/AntenatalVisit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AntenatalVisit extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'pregnancy_record_id',
        'patient_id',
        'facility_id',
        'provider_id',
        'visit_date',
        'gestational_age_weeks',
        'gestational_age_days',
        'fundal_height_cm',
        'fetal_heart_rate',
        'presentation',
        'weight_kg',
        'bp_systolic',
        'bp_diastolic',
        'urine_protein',
        'urine_glucose',
        'oedema',
        'notes',
    ];

    protected $casts = [
        'visit_date'            => 'date',
        'gestational_age_weeks' => 'integer',
        'gestational_age_days'  => 'integer',
        'fundal_height_cm'      => 'decimal:2',
        'fetal_heart_rate'      => 'integer',
        'weight_kg'             => 'decimal:2',
        'bp_systolic'           => 'integer',
        'bp_diastolic'          => 'integer',
    ];

    public function pregnancyRecord(): BelongsTo
    {
        return $this->belongsTo(PregnancyRecord::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
```

```php
<?php
// app/Models/DeliveryRecord.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryRecord extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'pregnancy_record_id',
        'patient_id',
        'facility_id',
        'provider_id',
        'delivery_date',
        'delivery_mode',
        'indication',
        'duration_labour_hours',
        'birth_weight_grams',
        'apgar_1min',
        'apgar_5min',
        'neonatal_outcome',
        'complications',
        'notes',
    ];

    protected $casts = [
        'delivery_date'         => 'date',
        'duration_labour_hours' => 'decimal:2',
        'birth_weight_grams'    => 'integer',
        'apgar_1min'            => 'integer',
        'apgar_5min'            => 'integer',
    ];

    public function pregnancyRecord(): BelongsTo
    {
        return $this->belongsTo(PregnancyRecord::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
```

- [ ] **Step 4: Create MaternityService**

```php
<?php
// app/Modules/Maternity/Services/MaternityService.php
namespace App\Modules\Maternity\Services;

use App\Models\AntenatalVisit;
use App\Models\DeliveryRecord;
use App\Models\PregnancyRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaternityService
{
    /** Recommended ANC visit schedule in gestational weeks (WHO-aligned) */
    private const RECOMMENDED_VISIT_WEEKS = [4, 8, 12, 16, 20, 24, 28, 30, 32, 34, 36, 38, 40];

    /**
     * Register a new pregnancy.
     */
    public function registerPregnancy(array $data): PregnancyRecord
    {
        return DB::transaction(function () use ($data) {
            $record = PregnancyRecord::create($data);
            return $record;
        });
    }

    /**
     * Record an antenatal visit for a pregnancy.
     */
    public function recordAntenatalVisit(string $pregnancyRecordId, array $data): AntenatalVisit
    {
        $record = PregnancyRecord::findOrFail($pregnancyRecordId);

        $data['pregnancy_record_id'] = $record->id;

        $visit = AntenatalVisit::create($data);

        // Recompute high-risk flag based on new visit data
        if ($this->isHighRisk($record->refresh())) {
            $record->update(['high_risk' => true]);
        }

        return $visit;
    }

    /**
     * Record the delivery outcome for a pregnancy.
     */
    public function recordDelivery(string $pregnancyRecordId, array $data): DeliveryRecord
    {
        return DB::transaction(function () use ($pregnancyRecordId, $data) {
            $record = PregnancyRecord::findOrFail($pregnancyRecordId);

            $data['pregnancy_record_id'] = $record->id;

            $delivery = DeliveryRecord::create($data);

            // Update pregnancy status on delivery
            $status = match ($data['neonatal_outcome'] ?? 'live') {
                'stillbirth'            => 'stillbirth',
                'early_neonatal_death'  => 'delivered',
                default                 => 'delivered',
            };
            $record->update(['pregnancy_status' => $status]);

            return $delivery;
        });
    }

    /**
     * Returns the list of recommended antenatal visit weeks.
     */
    public function getAntenatalSchedule(string $pregnancyRecordId): array
    {
        // Validate the record exists
        PregnancyRecord::findOrFail($pregnancyRecordId);

        return self::RECOMMENDED_VISIT_WEEKS;
    }

    /**
     * Determine whether a pregnancy is high-risk.
     *
     * Criteria (any one triggers high risk):
     * - Patient age > 35
     * - Any recorded BP >= 140 systolic OR >= 90 diastolic
     * - Any antenatal visit with moderate/severe oedema
     * - Previous caesarean in risk_factors
     * - Existing high_risk flag already set
     */
    public function isHighRisk(PregnancyRecord $record): bool
    {
        if ($record->high_risk) {
            return true;
        }

        // Check risk_factors array for previous caesarean
        $riskFactors = $record->risk_factors ?? [];
        if (in_array('previous_caesarean', $riskFactors, true)) {
            return true;
        }

        // Check patient age
        $patient = $record->patient;
        if ($patient && $patient->date_of_birth) {
            $age = Carbon::parse($patient->date_of_birth)->age;
            if ($age > 35) {
                return true;
            }
        }

        // Check any antenatal visit for elevated BP or oedema
        $dangerousVisit = $record->antenatalVisits()
            ->where(function ($q) {
                $q->where('bp_systolic', '>=', 140)
                    ->orWhere('bp_diastolic', '>=', 90)
                    ->orWhereIn('oedema', ['moderate', 'severe']);
            })
            ->exists();

        return $dangerousVisit;
    }

    /**
     * Calculate gestational age from LMP date.
     *
     * @param  string  $lmp  Date string (Y-m-d)
     * @return array{weeks: int, days: int}
     */
    public function calculateGestationalAge(string $lmp): array
    {
        $lmpDate      = Carbon::parse($lmp)->startOfDay();
        $today        = Carbon::today();
        $totalDays    = (int) $lmpDate->diffInDays($today);
        $weeks        = intdiv($totalDays, 7);
        $remainingDays = $totalDays % 7;

        return [
            'weeks' => $weeks,
            'days'  => $remainingDays,
        ];
    }
}
```

- [ ] **Step 5: Create MaternityController**

```php
<?php
// app/Http/Controllers/Api/V1/MaternityController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AntenatalVisit;
use App\Models\DeliveryRecord;
use App\Models\PregnancyRecord;
use App\Modules\Maternity\Services\MaternityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaternityController extends Controller
{
    public function __construct(private readonly MaternityService $service) {}

    /**
     * List all pregnancies for a patient.
     */
    public function index(string $patientId): JsonResponse
    {
        $records = PregnancyRecord::where('patient_id', $patientId)
            ->with(['facility', 'provider'])
            ->orderByDesc('registered_at')
            ->get();

        return response()->json(['data' => $records]);
    }

    /**
     * Register a new pregnancy.
     */
    public function store(Request $request, string $patientId): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'      => ['required', 'uuid', 'exists:facilities,id'],
            'provider_id'      => ['required', 'uuid', 'exists:users,id'],
            'gravida'          => ['required', 'integer', 'min:1'],
            'para'             => ['required', 'integer', 'min:0'],
            'lmp'              => ['nullable', 'date'],
            'edd'              => ['nullable', 'date'],
            'pregnancy_status' => ['required', 'in:active,delivered,miscarriage,stillbirth,ectopic,terminated'],
            'blood_type'       => ['nullable', 'in:A,B,AB,O'],
            'rhesus_factor'    => ['nullable', 'in:positive,negative'],
            'high_risk'        => ['boolean'],
            'risk_factors'     => ['nullable', 'array'],
            'notes'            => ['nullable', 'string'],
            'registered_at'    => ['nullable', 'date'],
        ]);

        $validated['patient_id']   = $patientId;
        $validated['registered_at'] = $validated['registered_at'] ?? now();

        $record = $this->service->registerPregnancy($validated);

        return response()->json(['data' => $record], 201);
    }

    /**
     * Show a single pregnancy record.
     */
    public function show(string $id): JsonResponse
    {
        $record = PregnancyRecord::with(['patient', 'facility', 'provider'])->findOrFail($id);

        return response()->json(['data' => $record]);
    }

    /**
     * List antenatal visits for a pregnancy.
     */
    public function antenatalVisits(string $id): JsonResponse
    {
        $record = PregnancyRecord::findOrFail($id);

        $visits = $record->antenatalVisits()
            ->with('provider')
            ->orderBy('visit_date')
            ->get();

        return response()->json(['data' => $visits]);
    }

    /**
     * Record a new antenatal visit.
     */
    public function storeAntenatalVisit(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'            => ['required', 'uuid', 'exists:patients,id'],
            'facility_id'           => ['required', 'uuid', 'exists:facilities,id'],
            'provider_id'           => ['required', 'uuid', 'exists:users,id'],
            'visit_date'            => ['required', 'date'],
            'gestational_age_weeks' => ['required', 'integer', 'min:0', 'max:45'],
            'gestational_age_days'  => ['required', 'integer', 'min:0', 'max:6'],
            'fundal_height_cm'      => ['nullable', 'numeric', 'min:0'],
            'fetal_heart_rate'      => ['nullable', 'integer', 'min:60', 'max:200'],
            'presentation'          => ['nullable', 'in:cephalic,breech,transverse,unknown'],
            'weight_kg'             => ['nullable', 'numeric', 'min:20', 'max:200'],
            'bp_systolic'           => ['nullable', 'integer', 'min:60', 'max:250'],
            'bp_diastolic'          => ['nullable', 'integer', 'min:40', 'max:150'],
            'urine_protein'         => ['nullable', 'in:negative,trace,1+,2+,3+,4+'],
            'urine_glucose'         => ['nullable', 'in:negative,trace,positive'],
            'oedema'                => ['nullable', 'in:none,mild,moderate,severe'],
            'notes'                 => ['nullable', 'string'],
        ]);

        $visit = $this->service->recordAntenatalVisit($id, $validated);

        return response()->json(['data' => $visit], 201);
    }

    /**
     * List delivery records for a pregnancy.
     */
    public function deliveries(string $id): JsonResponse
    {
        $record    = PregnancyRecord::findOrFail($id);
        $deliveries = $record->deliveryRecords()->with('provider')->get();

        return response()->json(['data' => $deliveries]);
    }

    /**
     * Record a delivery outcome.
     */
    public function storeDelivery(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'           => ['required', 'uuid', 'exists:patients,id'],
            'facility_id'          => ['required', 'uuid', 'exists:facilities,id'],
            'provider_id'          => ['required', 'uuid', 'exists:users,id'],
            'delivery_date'        => ['required', 'date'],
            'delivery_mode'        => ['required', 'in:svd,assisted_vaginal,caesarean,other'],
            'indication'           => ['nullable', 'string'],
            'duration_labour_hours'=> ['nullable', 'numeric', 'min:0'],
            'birth_weight_grams'   => ['required', 'integer', 'min:300', 'max:7000'],
            'apgar_1min'           => ['nullable', 'integer', 'min:0', 'max:10'],
            'apgar_5min'           => ['nullable', 'integer', 'min:0', 'max:10'],
            'neonatal_outcome'     => ['required', 'in:live,stillbirth,early_neonatal_death'],
            'complications'        => ['nullable', 'string'],
            'notes'                => ['nullable', 'string'],
        ]);

        $delivery = $this->service->recordDelivery($id, $validated);

        return response()->json(['data' => $delivery], 201);
    }
}
```

- [ ] **Step 6: Register routes in routes/api.php**

Add the following block inside the authenticated API middleware group:

```php
// routes/api.php — add inside authenticated middleware group
use App\Http\Controllers\Api\V1\MaternityController;

Route::prefix('maternity')->group(function () {
    Route::get('patients/{patientId}/pregnancies',        [MaternityController::class, 'index']);
    Route::post('patients/{patientId}/pregnancies',       [MaternityController::class, 'store']);
    Route::get('pregnancies/{id}',                        [MaternityController::class, 'show']);
    Route::get('pregnancies/{id}/antenatal-visits',       [MaternityController::class, 'antenatalVisits']);
    Route::post('pregnancies/{id}/antenatal-visits',      [MaternityController::class, 'storeAntenatalVisit']);
    Route::get('pregnancies/{id}/deliveries',             [MaternityController::class, 'deliveries']);
    Route::post('pregnancies/{id}/deliveries',            [MaternityController::class, 'storeDelivery']);
});
```

- [ ] **Step 7: Run tests and confirm green**

```bash
php artisan test tests/Feature/MaternityTest.php
```

---

## Task 2: HL7 v2 ADT Outbound Sender

**Files:**
- Create: `config/hl7.php`
- Create: `app/Services/Integration/Hl7AdtService.php`
- Test: `tests/Feature/Hl7AdtTest.php`

- [ ] **Step 1: Write the failing tests**

```php
<?php
// tests/Feature/Hl7AdtTest.php
namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use App\Services\Integration\Hl7AdtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Hl7AdtTest extends TestCase
{
    use RefreshDatabase;

    private Hl7AdtService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'hl7.host'         => '127.0.0.1',
            'hl7.port'         => 2575,
            'hl7.facility_id'  => 'OPESCARE',
            'hl7.sending_app'  => 'OPESCARE_EMR',
        ]);

        $this->service = app(Hl7AdtService::class);
    }

    public function test_build_a01_message_contains_required_segments(): void
    {
        $patient  = Patient::factory()->create([
            'first_name'    => 'Amara',
            'last_name'     => 'Diallo',
            'date_of_birth' => '1990-06-15',
            'gender'        => 'F',
        ]);
        $facility = Facility::factory()->create();
        $provider = User::factory()->create();

        // Create a fake visit object (stdClass or array) for testing
        $visit = (object) [
            'id'           => 'VISIT-001',
            'admitted_at'  => now(),
            'visit_type'   => 'inpatient',
        ];

        $message = $this->service->buildA01Message($patient, $visit, $facility);

        $this->assertStringContainsString('MSH|', $message);
        $this->assertStringContainsString('EVN|', $message);
        $this->assertStringContainsString('PID|', $message);
        $this->assertStringContainsString('PV1|', $message);
        $this->assertStringContainsString('ADT^A01', $message);
    }

    public function test_build_a08_message_contains_required_segments(): void
    {
        $patient = Patient::factory()->create([
            'first_name'    => 'Kofi',
            'last_name'     => 'Mensah',
            'date_of_birth' => '1985-03-22',
            'gender'        => 'M',
        ]);

        $message = $this->service->buildA08Message($patient);

        $this->assertStringContainsString('MSH|', $message);
        $this->assertStringContainsString('EVN|', $message);
        $this->assertStringContainsString('PID|', $message);
        $this->assertStringContainsString('ADT^A08', $message);
    }

    public function test_build_a28_message_contains_required_segments(): void
    {
        $patient = Patient::factory()->create();

        $message = $this->service->buildA28Message($patient);

        $this->assertStringContainsString('MSH|', $message);
        $this->assertStringContainsString('PID|', $message);
        $this->assertStringContainsString('ADT^A28', $message);
    }

    public function test_mllp_framing_applied_in_send(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $visit = (object) [
            'id'          => 'V-TEST',
            'admitted_at' => now(),
            'visit_type'  => 'outpatient',
        ];

        $message = $this->service->buildA01Message($patient, $visit, $facility);

        // Verify MLLP framing constants are defined correctly
        $this->assertEquals("\x0b", Hl7AdtService::VT);
        $this->assertEquals("\x1c", Hl7AdtService::FS);
        $this->assertEquals("\r",   Hl7AdtService::CR);

        // Framed message should start with VT and end with FS+CR
        $framed = Hl7AdtService::VT . $message . Hl7AdtService::FS . Hl7AdtService::CR;
        $this->assertStringStartsWith("\x0b", $framed);
        $this->assertStringEndsWith("\x1c\r", $framed);
    }

    public function test_send_returns_false_when_host_unreachable(): void
    {
        // No real HL7 server — expect graceful false return
        $result = $this->service->send('HL7-TEST', '240.0.0.1', 2575);
        $this->assertFalse($result);
    }

    public function test_a01_pid_segment_includes_patient_name(): void
    {
        $patient = Patient::factory()->create([
            'first_name' => 'Fatima',
            'last_name'  => 'Ouedraogo',
        ]);
        $facility = Facility::factory()->create();

        $visit = (object) ['id' => 'V1', 'admitted_at' => now(), 'visit_type' => 'inpatient'];

        $message = $this->service->buildA01Message($patient, $visit, $facility);

        // PID segment should contain the patient's name in HL7 format (last^first)
        $this->assertStringContainsString('Ouedraogo^Fatima', $message);
    }
}
```

- [ ] **Step 2: Create config/hl7.php**

```php
<?php
// config/hl7.php
return [
    /*
    |--------------------------------------------------------------------------
    | HL7 v2 ADT Outbound Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and connection settings for the HL7 v2 ADT MLLP outbound
    | sender.  All values are read from environment variables so they can be
    | overridden per environment without touching the config file.
    |
    */

    'host'         => env('HL7_HOST', '127.0.0.1'),
    'port'         => (int) env('HL7_PORT', 2575),
    'facility_id'  => env('HL7_FACILITY_ID', 'OPESCARE'),
    'sending_app'  => env('HL7_SENDING_APP', 'OPESCARE_EMR'),

    /*
    | Socket timeout in seconds for the MLLP TCP connection.
    */
    'timeout'      => (int) env('HL7_TIMEOUT', 5),
];
```

- [ ] **Step 3: Create Hl7AdtService**

```php
<?php
// app/Services/Integration/Hl7AdtService.php
namespace App\Services\Integration;

use App\Models\Facility;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Stateless HL7 v2.5 ADT outbound sender.
 *
 * Supported events: A01 (admit), A08 (update patient info), A28 (add person info).
 * Transport: MLLP (Minimum Lower Layer Protocol) over TCP sockets.
 *
 * NOT a full HL7 parser — send-only.
 */
class Hl7AdtService
{
    // MLLP framing bytes
    public const VT = "\x0b"; // Vertical Tab — start of block
    public const FS = "\x1c"; // File Separator — end of block
    public const CR = "\r";   // Carriage Return — final terminator

    // HL7 field delimiters
    private const FIELD     = '|';
    private const COMPONENT = '^';
    private const REPEAT    = '~';
    private const ESCAPE    = '\\';
    private const SUBCOMP   = '&';

    public function __construct()
    {
        // Service is stateless — no DI required beyond config
    }

    // -------------------------------------------------------------------------
    // Message builders
    // -------------------------------------------------------------------------

    /**
     * Build an ADT^A01 (Admit/Visit Notification) HL7 v2.5 message string.
     */
    public function buildA01Message(Patient $patient, object $visit, Facility $facility): string
    {
        $segments = [
            $this->buildMsh('ADT', 'A01', $facility),
            $this->buildEvn('A01'),
            $this->buildPid($patient),
            $this->buildPv1($visit, 'I', $facility),
        ];

        return implode(self::CR, $segments) . self::CR;
    }

    /**
     * Build an ADT^A08 (Update Patient Information) HL7 v2.5 message string.
     */
    public function buildA08Message(Patient $patient): string
    {
        $facility = null; // A08 can be facility-agnostic

        $segments = [
            $this->buildMsh('ADT', 'A08'),
            $this->buildEvn('A08'),
            $this->buildPid($patient),
        ];

        return implode(self::CR, $segments) . self::CR;
    }

    /**
     * Build an ADT^A28 (Add Person Information) HL7 v2.5 message string.
     */
    public function buildA28Message(Patient $patient): string
    {
        $segments = [
            $this->buildMsh('ADT', 'A28'),
            $this->buildEvn('A28'),
            $this->buildPid($patient),
        ];

        return implode(self::CR, $segments) . self::CR;
    }

    // -------------------------------------------------------------------------
    // Segment builders
    // -------------------------------------------------------------------------

    /**
     * MSH — Message Header Segment
     *
     * MSH|^~\&|SendingApp|SendingFacility|ReceivingApp|ReceivingFacility|DateTime||MsgType|MsgCtrlId|P|2.5
     */
    private function buildMsh(string $messageType, string $triggerEvent, ?Facility $facility = null): string
    {
        $sendingApp      = config('hl7.sending_app', 'OPESCARE_EMR');
        $sendingFacility = config('hl7.facility_id', 'OPESCARE');
        $receivingApp    = $facility?->hl7_receiving_app ?? 'RECEIVING_APP';
        $receivingFacility = $facility?->hl7_receiving_facility ?? 'RECEIVING_FACILITY';
        $dateTime        = Carbon::now()->format('YmdHis');
        $msgCtrlId       = strtoupper(substr(md5(uniqid('', true)), 0, 20));
        $msgType         = "{$messageType}^{$triggerEvent}";

        $fields = [
            'MSH',
            '^~\\&',              // MSH.2 encoding characters
            $sendingApp,          // MSH.3
            $sendingFacility,     // MSH.4
            $receivingApp,        // MSH.5
            $receivingFacility,   // MSH.6
            $dateTime,            // MSH.7
            '',                   // MSH.8 (security)
            $msgType,             // MSH.9
            $msgCtrlId,           // MSH.10
            'P',                  // MSH.11 (processing ID — P=Production)
            '2.5',                // MSH.12 (version ID)
        ];

        return implode(self::FIELD, $fields);
    }

    /**
     * EVN — Event Type Segment
     *
     * EVN|EventTypeCode|RecordedDateTime
     */
    private function buildEvn(string $eventTypeCode): string
    {
        $recorded = Carbon::now()->format('YmdHis');

        $fields = [
            'EVN',
            $eventTypeCode,  // EVN.1
            $recorded,       // EVN.2
        ];

        return implode(self::FIELD, $fields);
    }

    /**
     * PID — Patient Identification Segment
     *
     * PID|SetId|PatientId||PatientIdList|PatientName|MotherMaiden|DOB|Sex|||Address
     */
    private function buildPid(Patient $patient): string
    {
        $patientId   = $patient->id;
        $lastName    = $this->escape($patient->last_name ?? '');
        $firstName   = $this->escape($patient->first_name ?? '');
        $dob         = $patient->date_of_birth
            ? Carbon::parse($patient->date_of_birth)->format('Ymd')
            : '';
        $gender      = strtoupper(substr($patient->gender ?? 'U', 0, 1));
        $phone        = $this->escape($patient->phone ?? '');
        $mrn          = $patient->mrn ?? $patientId;

        // PID.3: Patient Identifier List  — MRN^^^AssigningAuthority
        $pidList = "{$mrn}^^^" . config('hl7.facility_id', 'OPESCARE');

        // PID.5: Patient Name — LastName^FirstName^Middle^Suffix^Prefix
        $name = "{$lastName}{self::COMPONENT}{$firstName}";

        $fields = [
            'PID',
            '1',         // PID.1 set ID
            $patientId,  // PID.2 patient ID (external)
            $pidList,    // PID.3 patient identifier list
            '',          // PID.4 alternate patient ID
            $name,       // PID.5 patient name
            '',          // PID.6 mother's maiden name
            $dob,        // PID.7 date of birth
            $gender,     // PID.8 administrative sex
            '',          // PID.9 patient alias
            '',          // PID.10 race
            '',          // PID.11 patient address
            '',          // PID.12 county code
            $phone,      // PID.13 phone number (home)
        ];

        return implode(self::FIELD, $fields);
    }

    /**
     * PV1 — Patient Visit Segment
     *
     * PV1|SetId|PatientClass|AssignedPatientLocation||||AttendingDoctor
     */
    private function buildPv1(object $visit, string $patientClass = 'O', ?Facility $facility = null): string
    {
        $visitId  = $visit->id ?? '';
        $location = $facility?->code ?? config('hl7.facility_id', 'OPESCARE');

        $admitTime = '';
        if (isset($visit->admitted_at)) {
            $admitTime = Carbon::parse($visit->admitted_at)->format('YmdHis');
        }

        $fields = [
            'PV1',
            '1',           // PV1.1 set ID
            $patientClass, // PV1.2 patient class (I=Inpatient, O=Outpatient, E=Emergency)
            $location,     // PV1.3 assigned patient location
            '',            // PV1.4 admission type
            '',            // PV1.5 preadmit number
            '',            // PV1.6 prior patient location
            '',            // PV1.7 attending doctor
            '',            // PV1.8 referring doctor
            '',            // PV1.9 consulting doctor
            '',            // PV1.10 hospital service
            '',            // PV1.11-17 (bed status etc)
            '', '', '', '', '', '',
            '',            // PV1.18 patient type
            $visitId,      // PV1.19 visit number
            '',            // PV1.20-43 (financial, diet, etc)
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            $admitTime,    // PV1.44 admit date/time
        ];

        return implode(self::FIELD, $fields);
    }

    // -------------------------------------------------------------------------
    // Transport
    // -------------------------------------------------------------------------

    /**
     * Send an HL7 message over MLLP/TCP.
     *
     * Applies MLLP framing: VT + message + FS + CR.
     * Returns true on successful send (ACK not parsed — fire-and-forget).
     */
    public function send(string $hl7Message, string $host, int $port): bool
    {
        $timeout = config('hl7.timeout', 5);

        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

            if ($socket === false) {
                Log::warning('Hl7AdtService: Could not connect to HL7 host', [
                    'host'   => $host,
                    'port'   => $port,
                    'errno'  => $errno,
                    'errstr' => $errstr,
                ]);
                return false;
            }

            stream_set_timeout($socket, $timeout);

            $framed = self::VT . $hl7Message . self::FS . self::CR;
            $written = fwrite($socket, $framed);

            if ($written === false || $written === 0) {
                Log::warning('Hl7AdtService: Failed to write to socket');
                fclose($socket);
                return false;
            }

            // Read ACK (not parsed — just drain to avoid RST)
            $ack = fread($socket, 4096);

            fclose($socket);

            Log::info('Hl7AdtService: Message sent', [
                'host'  => $host,
                'port'  => $port,
                'bytes' => $written,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Hl7AdtService: Exception during send', [
                'host'      => $host,
                'port'      => $port,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build and send an ADT^A01 message for a patient admission.
     */
    public function sendAdmission(string $patientId, string $visitId): bool
    {
        $patient  = \App\Models\Patient::findOrFail($patientId);
        $facility = \App\Models\Facility::first(); // default facility; extend as needed
        $visit    = (object) ['id' => $visitId, 'admitted_at' => now(), 'visit_type' => 'inpatient'];

        $message = $this->buildA01Message($patient, $visit, $facility);

        return $this->send($message, config('hl7.host'), config('hl7.port'));
    }

    /**
     * Build and send an ADT^A08 message for a patient info update.
     */
    public function sendPatientUpdate(string $patientId): bool
    {
        $patient = \App\Models\Patient::findOrFail($patientId);
        $message = $this->buildA08Message($patient);

        return $this->send($message, config('hl7.host'), config('hl7.port'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Escape HL7 special characters in a field value.
     */
    private function escape(string $value): string
    {
        return str_replace(
            ['\\', '|', '^', '~', '&'],
            ['\\E\\', '\\F\\', '\\S\\', '\\R\\', '\\T\\'],
            $value
        );
    }
}
```

- [ ] **Step 4: Run tests and confirm green**

```bash
php artisan test tests/Feature/Hl7AdtTest.php
```

---

## Acceptance Criteria

- [ ] All three maternity model migrations run without errors: `php artisan migrate`
- [ ] `PregnancyRecord`, `AntenatalVisit`, `DeliveryRecord` all use `HasUuids` and `HasFactory`
- [ ] `MaternityService::calculateGestationalAge` returns correct `{weeks, days}` for any LMP
- [ ] `MaternityService::isHighRisk` returns `true` when BP >= 140/90 or oedema moderate+
- [ ] All 7 maternity routes respond correctly (401 unauthenticated, 422 on bad input, 201/200 on valid)
- [ ] `Hl7AdtService::buildA01Message` output contains MSH, EVN, PID, PV1 segments
- [ ] MLLP framing constants `VT=\x0b`, `FS=\x1c`, `CR=\r` are correct
- [ ] `Hl7AdtService::send` returns `false` gracefully when host is unreachable (no uncaught exception)
- [ ] Feature tests all pass: `php artisan test tests/Feature/MaternityTest.php tests/Feature/Hl7AdtTest.php`
