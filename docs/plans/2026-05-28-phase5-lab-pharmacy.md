# Phase 5: Radiology Reports + Drug Formulary + Controlled Substances

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans

**Goal:** Add radiology report distribution, drug formulary management, and controlled substance tracking.
**Architecture:** New models + services. No existing lab/pharmacy code modified.
**Tech Stack:** Laravel 11, PHP 8.3, PostgreSQL, UUID PKs

---

## Task 1: Radiology Report Distribution (item 34)

### 1.1 Migration: `database/migrations/2026_05_28_005000_create_radiology_reports_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            // imaging_orders table is created by Phase 1; nullable FK avoids hard dependency
            $table->uuid('imaging_order_id')->nullable()->index();
            $table->foreignUuid('ordered_by')->constrained('users');
            $table->foreignUuid('reported_by')->constrained('users');
            $table->enum('modality', [
                'xray', 'ct', 'mri', 'ultrasound', 'echo', 'nuclear', 'pet', 'other',
            ]);
            $table->string('body_part', 150);
            $table->dateTime('study_date');
            $table->text('clinical_indication');
            $table->text('findings');
            $table->text('impression');
            $table->text('recommendation')->nullable();
            $table->enum('status', ['draft', 'preliminary', 'final', 'amended', 'corrected'])
                ->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('amended_at')->nullable();
            $table->text('amendment_reason')->nullable();
            $table->json('distributed_to')->nullable()->comment('Array of user UUIDs notified');
            $table->timestamp('distributed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['facility_id', 'status']);
            $table->index(['patient_id', 'study_date']);
            $table->index('reported_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_reports');
    }
};
```

---

### 1.2 Model: `app/Models/RadiologyReport.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RadiologyReport extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'imaging_order_id',
        'ordered_by',
        'reported_by',
        'modality',
        'body_part',
        'study_date',
        'clinical_indication',
        'findings',
        'impression',
        'recommendation',
        'status',
        'finalized_at',
        'amended_at',
        'amendment_reason',
        'distributed_to',
        'distributed_at',
    ];

    protected $casts = [
        'study_date'     => 'datetime',
        'finalized_at'   => 'datetime',
        'amended_at'     => 'datetime',
        'distributed_at' => 'datetime',
        'distributed_to' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
```

---

### 1.3 Service: `app/Services/Lab/RadiologyReportService.php`

```php
<?php

namespace App\Services\Lab;

use App\Models\RadiologyReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use RuntimeException;

class RadiologyReportService
{
    /**
     * Create a new draft radiology report.
     *
     * @param  array{
     *     patient_id: string,
     *     facility_id: string,
     *     imaging_order_id?: string|null,
     *     ordered_by: string,
     *     reported_by: string,
     *     modality: string,
     *     body_part: string,
     *     study_date: string,
     *     clinical_indication: string,
     *     findings: string,
     *     impression: string,
     *     recommendation?: string|null,
     * }  $data
     */
    public function createDraft(array $data): RadiologyReport
    {
        return RadiologyReport::create(array_merge($data, [
            'status'       => 'draft',
            'distributed_to' => [],
        ]));
    }

    /**
     * Finalize a radiology report (transitions draft/preliminary → final).
     *
     * @throws RuntimeException if report is already final/amended/corrected.
     */
    public function finalize(string $reportId, string $radiologistId): RadiologyReport
    {
        /** @var RadiologyReport $report */
        $report = RadiologyReport::findOrFail($reportId);

        if (in_array($report->status, ['final', 'amended', 'corrected'])) {
            throw new RuntimeException(
                "Report {$reportId} cannot be finalized from status '{$report->status}'."
            );
        }

        $report->update([
            'status'       => 'final',
            'reported_by'  => $radiologistId,
            'finalized_at' => now(),
        ]);

        return $report->fresh();
    }

    /**
     * Amend a finalized report (final → amended).
     *
     * @param  array<string, mixed>  $changes  Fields to update (findings, impression, recommendation, etc.)
     * @throws RuntimeException if report is not in a final state.
     */
    public function amend(string $reportId, string $reason, array $changes): RadiologyReport
    {
        /** @var RadiologyReport $report */
        $report = RadiologyReport::findOrFail($reportId);

        if (!in_array($report->status, ['final', 'corrected'])) {
            throw new RuntimeException(
                "Only finalized reports can be amended. Current status: '{$report->status}'."
            );
        }

        $allowedFields = ['findings', 'impression', 'recommendation', 'clinical_indication'];
        $safeChanges   = array_intersect_key($changes, array_flip($allowedFields));

        $report->update(array_merge($safeChanges, [
            'status'           => 'amended',
            'amended_at'       => now(),
            'amendment_reason' => $reason,
        ]));

        return $report->fresh();
    }

    /**
     * Distribute a report to a list of users (providers, care team members).
     *
     * Sets distributed_to and distributed_at, then fires a notification event
     * via Laravel's Event system if a RadiologyReportDistributed listener is registered.
     *
     * @param  string[]  $userIds  UUIDs of users to notify
     * @throws RuntimeException if report is not yet finalized.
     */
    public function distribute(string $reportId, array $userIds): RadiologyReport
    {
        /** @var RadiologyReport $report */
        $report = RadiologyReport::findOrFail($reportId);

        if (!in_array($report->status, ['final', 'amended', 'corrected'])) {
            throw new RuntimeException(
                "Only finalized reports can be distributed. Current status: '{$report->status}'."
            );
        }

        // Merge with any previously distributed recipients (idempotent)
        $existing    = $report->distributed_to ?? [];
        $merged      = array_values(array_unique(array_merge($existing, $userIds)));

        $report->update([
            'distributed_to' => $merged,
            'distributed_at' => now(),
        ]);

        // Fire event — caught by notification listener if registered
        if (class_exists(\App\Events\RadiologyReportDistributed::class)) {
            Event::dispatch(new \App\Events\RadiologyReportDistributed($report, $userIds));
        }

        return $report->fresh();
    }

    /**
     * Return all non-final reports for a facility (draft + preliminary).
     */
    public function getPendingForFacility(string $facilityId): Collection
    {
        return RadiologyReport::where('facility_id', $facilityId)
            ->whereIn('status', ['draft', 'preliminary'])
            ->with(['patient', 'reportedBy'])
            ->orderBy('study_date')
            ->get();
    }
}
```

---

### 1.4 Controller: `app/Http/Controllers/Api/V1/RadiologyReportController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Lab\RadiologyReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RadiologyReportController extends Controller
{
    public function __construct(private readonly RadiologyReportService $service) {}

    /**
     * POST /api/radiology/reports
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'          => ['required', 'uuid', 'exists:patients,id'],
            'facility_id'         => ['required', 'uuid', 'exists:facilities,id'],
            'imaging_order_id'    => ['sometimes', 'nullable', 'uuid'],
            'ordered_by'          => ['required', 'uuid', 'exists:users,id'],
            'reported_by'         => ['required', 'uuid', 'exists:users,id'],
            'modality'            => ['required', 'in:xray,ct,mri,ultrasound,echo,nuclear,pet,other'],
            'body_part'           => ['required', 'string', 'max:150'],
            'study_date'          => ['required', 'date'],
            'clinical_indication' => ['required', 'string'],
            'findings'            => ['required', 'string'],
            'impression'          => ['required', 'string'],
            'recommendation'      => ['sometimes', 'nullable', 'string'],
        ]);

        $report = $this->service->createDraft($validated);

        return response()->json(['data' => $report], Response::HTTP_CREATED);
    }

    /**
     * GET /api/radiology/reports/{id}
     */
    public function show(string $id): JsonResponse
    {
        $report = \App\Models\RadiologyReport::with(['patient', 'orderedBy', 'reportedBy', 'facility'])
            ->findOrFail($id);

        return response()->json(['data' => $report]);
    }

    /**
     * PATCH /api/radiology/reports/{id}/finalize
     */
    public function finalize(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'radiologist_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $report = $this->service->finalize($id, $validated['radiologist_id']);

        return response()->json(['data' => $report]);
    }

    /**
     * PATCH /api/radiology/reports/{id}/amend
     */
    public function amend(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason'              => ['required', 'string', 'max:1000'],
            'findings'            => ['sometimes', 'string'],
            'impression'          => ['sometimes', 'string'],
            'recommendation'      => ['sometimes', 'nullable', 'string'],
            'clinical_indication' => ['sometimes', 'string'],
        ]);

        $reason  = $validated['reason'];
        $changes = array_except($validated, ['reason']);

        $report = $this->service->amend($id, $reason, $changes);

        return response()->json(['data' => $report]);
    }

    /**
     * POST /api/radiology/reports/{id}/distribute
     */
    public function distribute(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $report = $this->service->distribute($id, $validated['user_ids']);

        return response()->json(['data' => $report]);
    }

    /**
     * GET /api/facilities/{facilityId}/radiology/reports/pending
     */
    public function pending(string $facilityId): JsonResponse
    {
        $reports = $this->service->getPendingForFacility($facilityId);

        return response()->json(['data' => $reports]);
    }
}
```

---

### 1.5 Routes (add to `routes/api.php`)

```php
// Radiology Reports
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('radiology/reports',                                                [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'store']);
    Route::get('radiology/reports/{id}',                                            [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'show']);
    Route::patch('radiology/reports/{id}/finalize',                                 [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'finalize']);
    Route::patch('radiology/reports/{id}/amend',                                    [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'amend']);
    Route::post('radiology/reports/{id}/distribute',                                [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'distribute']);
    Route::get('facilities/{facilityId}/radiology/reports/pending',                 [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'pending']);
});
```

---

### 1.6 Test: `tests/Feature/RadiologyReportTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Patient;
use App\Models\RadiologyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiologyReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Facility $facility;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->facility = Facility::factory()->create();
        $this->patient  = Patient::factory()->create();
    }

    private function draftPayload(): array
    {
        return [
            'patient_id'          => $this->patient->id,
            'facility_id'         => $this->facility->id,
            'ordered_by'          => $this->user->id,
            'reported_by'         => $this->user->id,
            'modality'            => 'ct',
            'body_part'           => 'Chest',
            'study_date'          => now()->format('Y-m-d H:i:s'),
            'clinical_indication' => 'Chest pain evaluation',
            'findings'            => 'No acute findings.',
            'impression'          => 'Normal CT chest.',
        ];
    }

    public function test_create_draft_report(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/radiology/reports', $this->draftPayload());

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'draft');
        $this->assertDatabaseHas('radiology_reports', ['modality' => 'ct', 'status' => 'draft']);
    }

    public function test_finalize_report(): void
    {
        $report = RadiologyReport::factory()->create(array_merge($this->draftPayload(), [
            'status' => 'draft',
        ]));

        $response = $this->actingAs($this->user)
            ->patchJson("/api/radiology/reports/{$report->id}/finalize", [
                'radiologist_id' => $this->user->id,
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'final');
        $this->assertNotNull($response->json('data.finalized_at'));
    }

    public function test_amend_finalized_report(): void
    {
        $report = RadiologyReport::factory()->create(array_merge($this->draftPayload(), [
            'status'       => 'final',
            'finalized_at' => now(),
        ]));

        $response = $this->actingAs($this->user)
            ->patchJson("/api/radiology/reports/{$report->id}/amend", [
                'reason'   => 'Measurement correction',
                'findings' => 'Revised findings with corrected measurements.',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'amended');
        $response->assertJsonPath('data.amendment_reason', 'Measurement correction');
    }

    public function test_distribute_report_sets_distributed_to_and_at(): void
    {
        $recipient = User::factory()->create();

        $report = RadiologyReport::factory()->create(array_merge($this->draftPayload(), [
            'status'       => 'final',
            'finalized_at' => now(),
        ]));

        $response = $this->actingAs($this->user)
            ->postJson("/api/radiology/reports/{$report->id}/distribute", [
                'user_ids' => [$recipient->id],
            ]);

        $response->assertOk();
        $this->assertContains($recipient->id, $response->json('data.distributed_to'));
        $this->assertNotNull($response->json('data.distributed_at'));
    }

    public function test_cannot_distribute_draft_report(): void
    {
        $report = RadiologyReport::factory()->create(array_merge($this->draftPayload(), [
            'status' => 'draft',
        ]));

        $response = $this->actingAs($this->user)
            ->postJson("/api/radiology/reports/{$report->id}/distribute", [
                'user_ids' => [$this->user->id],
            ]);

        $response->assertStatus(500); // RuntimeException mapped by handler
    }
}
```

---

## Task 2: Drug Formulary Management (item 36)

### 2.1 Migration: `database/migrations/2026_05_28_005001_create_drug_formularies_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drug_formularies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // null = national formulary entry, non-null = facility-specific entry
            $table->foreignUuid('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->string('generic_name', 255);
            $table->json('brand_names')->nullable()->comment('Array of brand name strings');
            $table->string('drug_code', 50)->index();
            $table->string('drug_class', 100);
            $table->enum('form', ['tablet', 'capsule', 'liquid', 'injection', 'topical', 'inhaler', 'other']);
            $table->string('strength', 50);
            $table->string('unit', 30);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_controlled')->default(false);
            $table->boolean('requires_prior_auth')->default(false);
            $table->json('restricted_to')->nullable()->comment('Array of specialty slugs');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['drug_code', 'facility_id']);
            $table->index(['is_controlled', 'facility_id']);
            $table->index('generic_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_formularies');
    }
};
```

---

### 2.2 Model: `app/Models/DrugFormulary.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrugFormulary extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_id',
        'generic_name',
        'brand_names',
        'drug_code',
        'drug_class',
        'form',
        'strength',
        'unit',
        'is_available',
        'is_controlled',
        'requires_prior_auth',
        'restricted_to',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'brand_names'         => 'array',
        'restricted_to'       => 'array',
        'is_available'        => 'boolean',
        'is_controlled'       => 'boolean',
        'requires_prior_auth' => 'boolean',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

---

### 2.3 Service: `app/Services/Pharmacy/FormularyService.php`

```php
<?php

namespace App\Services\Pharmacy;

use App\Models\DrugFormulary;
use Illuminate\Database\Eloquent\Collection;

class FormularyService
{
    /**
     * Search formulary by generic name, brand name, or drug code.
     *
     * Searches the national formulary (facility_id IS NULL) and the facility-specific
     * entries. Facility entries override national entries when both match.
     *
     * @param  array{
     *     is_controlled?: bool,
     *     is_available?: bool,
     *     drug_class?: string,
     *     form?: string,
     * }  $filters
     */
    public function search(string $query, ?string $facilityId, array $filters = []): Collection
    {
        $q = DrugFormulary::where(function ($builder) use ($query) {
            $like = '%' . mb_strtolower($query) . '%';
            $builder->whereRaw('LOWER(generic_name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(drug_code) LIKE ?', [$like])
                ->orWhereRaw("EXISTS (
                    SELECT 1 FROM jsonb_array_elements_text(brand_names) AS bn
                    WHERE LOWER(bn) LIKE ?
                )", [$like]);
        });

        // Scope to national + facility-specific entries
        $q->where(function ($builder) use ($facilityId) {
            $builder->whereNull('facility_id');
            if ($facilityId) {
                $builder->orWhere('facility_id', $facilityId);
            }
        });

        if (isset($filters['is_controlled'])) {
            $q->where('is_controlled', $filters['is_controlled']);
        }

        if (isset($filters['is_available'])) {
            $q->where('is_available', $filters['is_available']);
        }

        if (!empty($filters['drug_class'])) {
            $q->where('drug_class', $filters['drug_class']);
        }

        if (!empty($filters['form'])) {
            $q->where('form', $filters['form']);
        }

        return $q->orderByRaw('facility_id IS NULL')->orderBy('generic_name')->get();
    }

    /**
     * Check whether a drug is present and available in the formulary for a facility.
     */
    public function isOnFormulary(string $drugCode, ?string $facilityId): bool
    {
        return DrugFormulary::where('drug_code', $drugCode)
            ->where('is_available', true)
            ->where(function ($q) use ($facilityId) {
                $q->whereNull('facility_id');
                if ($facilityId) {
                    $q->orWhere('facility_id', $facilityId);
                }
            })
            ->exists();
    }

    /**
     * Add a new drug to the formulary.
     *
     * @param  array{
     *     facility_id?: string|null,
     *     generic_name: string,
     *     brand_names?: string[],
     *     drug_code: string,
     *     drug_class: string,
     *     form: string,
     *     strength: string,
     *     unit: string,
     *     is_available?: bool,
     *     is_controlled?: bool,
     *     requires_prior_auth?: bool,
     *     restricted_to?: string[]|null,
     *     notes?: string|null,
     *     created_by: string,
     * }  $data
     */
    public function add(array $data): DrugFormulary
    {
        return DrugFormulary::create($data);
    }

    /**
     * Toggle the availability flag of a formulary entry.
     */
    public function toggleAvailability(string $id, bool $available): DrugFormulary
    {
        /** @var DrugFormulary $entry */
        $entry = DrugFormulary::findOrFail($id);
        $entry->update(['is_available' => $available]);

        return $entry->fresh();
    }

    /**
     * Return all controlled substances visible to a facility.
     */
    public function getControlledSubstances(?string $facilityId): Collection
    {
        return DrugFormulary::where('is_controlled', true)
            ->where(function ($q) use ($facilityId) {
                $q->whereNull('facility_id');
                if ($facilityId) {
                    $q->orWhere('facility_id', $facilityId);
                }
            })
            ->orderBy('generic_name')
            ->get();
    }

    /**
     * Return drugs restricted to a specific medical specialty.
     *
     * A drug is considered specialty-restricted when its restricted_to array is non-null
     * and non-empty. This method returns only drugs that include the given specialty.
     */
    public function getRestrictedDrugs(string $specialty, ?string $facilityId): Collection
    {
        return DrugFormulary::whereNotNull('restricted_to')
            ->whereRaw("restricted_to::jsonb @> ?::jsonb", [json_encode([$specialty])])
            ->where(function ($q) use ($facilityId) {
                $q->whereNull('facility_id');
                if ($facilityId) {
                    $q->orWhere('facility_id', $facilityId);
                }
            })
            ->orderBy('generic_name')
            ->get();
    }
}
```

---

### 2.4 Controller: `app/Http/Controllers/Api/V1/DrugFormularyController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\FormularyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DrugFormularyController extends Controller
{
    public function __construct(private readonly FormularyService $service) {}

    /**
     * GET /api/pharmacy/formulary/search
     *
     * Query params: q (required), facility_id, is_controlled, is_available, drug_class, form
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'            => ['required', 'string', 'min:2', 'max:100'],
            'facility_id'  => ['sometimes', 'nullable', 'uuid'],
            'is_controlled'=> ['sometimes', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'drug_class'   => ['sometimes', 'string', 'max:100'],
            'form'         => ['sometimes', 'in:tablet,capsule,liquid,injection,topical,inhaler,other'],
        ]);

        $filters = array_filter(
            array_intersect_key($validated, array_flip(['is_controlled', 'is_available', 'drug_class', 'form'])),
            fn ($v) => $v !== null,
        );

        $results = $this->service->search(
            $validated['q'],
            $validated['facility_id'] ?? null,
            $filters,
        );

        return response()->json(['data' => $results]);
    }

    /**
     * POST /api/pharmacy/formulary
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'         => ['sometimes', 'nullable', 'uuid', 'exists:facilities,id'],
            'generic_name'        => ['required', 'string', 'max:255'],
            'brand_names'         => ['sometimes', 'array'],
            'brand_names.*'       => ['string', 'max:255'],
            'drug_code'           => ['required', 'string', 'max:50'],
            'drug_class'          => ['required', 'string', 'max:100'],
            'form'                => ['required', 'in:tablet,capsule,liquid,injection,topical,inhaler,other'],
            'strength'            => ['required', 'string', 'max:50'],
            'unit'                => ['required', 'string', 'max:30'],
            'is_available'        => ['sometimes', 'boolean'],
            'is_controlled'       => ['sometimes', 'boolean'],
            'requires_prior_auth' => ['sometimes', 'boolean'],
            'restricted_to'       => ['sometimes', 'nullable', 'array'],
            'restricted_to.*'     => ['string', 'max:100'],
            'notes'               => ['sometimes', 'nullable', 'string'],
            'created_by'          => ['required', 'uuid', 'exists:users,id'],
        ]);

        $entry = $this->service->add($validated);

        return response()->json(['data' => $entry], Response::HTTP_CREATED);
    }

    /**
     * PATCH /api/pharmacy/formulary/{id}/availability
     */
    public function toggleAvailability(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'is_available' => ['required', 'boolean'],
        ]);

        $entry = $this->service->toggleAvailability($id, $validated['is_available']);

        return response()->json(['data' => $entry]);
    }

    /**
     * GET /api/pharmacy/formulary/controlled
     *
     * Query params: facility_id (optional)
     */
    public function controlled(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['sometimes', 'nullable', 'uuid'],
        ]);

        $entries = $this->service->getControlledSubstances($validated['facility_id'] ?? null);

        return response()->json(['data' => $entries]);
    }
}
```

---

### 2.5 Routes (add to `routes/api.php`)

```php
// Drug Formulary
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('pharmacy/formulary/search',                  [\App\Http\Controllers\Api\V1\DrugFormularyController::class, 'search']);
    Route::get('pharmacy/formulary/controlled',              [\App\Http\Controllers\Api\V1\DrugFormularyController::class, 'controlled']);
    Route::post('pharmacy/formulary',                        [\App\Http\Controllers\Api\V1\DrugFormularyController::class, 'store']);
    Route::patch('pharmacy/formulary/{id}/availability',     [\App\Http\Controllers\Api\V1\DrugFormularyController::class, 'toggleAvailability']);
});
```

---

### 2.6 Test: `tests/Feature/DrugFormularyTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\DrugFormulary;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrugFormularyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->facility = Facility::factory()->create();
    }

    private function baseEntry(): array
    {
        return [
            'generic_name' => 'Metformin',
            'brand_names'  => ['Glucophage', 'Fortamet'],
            'drug_code'    => 'MET500',
            'drug_class'   => 'Biguanides',
            'form'         => 'tablet',
            'strength'     => '500mg',
            'unit'         => 'mg',
            'created_by'   => $this->user->id,
        ];
    }

    public function test_add_drug_to_formulary(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/pharmacy/formulary', $this->baseEntry());

        $response->assertCreated();
        $this->assertDatabaseHas('drug_formularies', ['drug_code' => 'MET500', 'is_available' => true]);
    }

    public function test_search_by_generic_name(): void
    {
        DrugFormulary::factory()->create(array_merge($this->baseEntry(), ['created_by' => $this->user->id]));

        $response = $this->actingAs($this->user)
            ->getJson('/api/pharmacy/formulary/search?q=Metformin');

        $response->assertOk();
        $this->assertGreaterThan(0, count($response->json('data')));
        $this->assertEquals('Metformin', $response->json('data.0.generic_name'));
    }

    public function test_search_by_brand_name(): void
    {
        DrugFormulary::factory()->create(array_merge($this->baseEntry(), ['created_by' => $this->user->id]));

        $response = $this->actingAs($this->user)
            ->getJson('/api/pharmacy/formulary/search?q=Glucophage');

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_toggle_availability_to_false(): void
    {
        $entry = DrugFormulary::factory()->create(
            array_merge($this->baseEntry(), ['is_available' => true, 'created_by' => $this->user->id])
        );

        $response = $this->actingAs($this->user)
            ->patchJson("/api/pharmacy/formulary/{$entry->id}/availability", [
                'is_available' => false,
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.is_available', false);
        $this->assertDatabaseHas('drug_formularies', ['id' => $entry->id, 'is_available' => false]);
    }

    public function test_controlled_substances_endpoint(): void
    {
        DrugFormulary::factory()->create(array_merge($this->baseEntry(), [
            'is_controlled' => true,
            'drug_code'     => 'OXY10',
            'generic_name'  => 'Oxycodone',
            'created_by'    => $this->user->id,
        ]));

        $response = $this->actingAs($this->user)
            ->getJson('/api/pharmacy/formulary/controlled');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->every(fn ($d) => $d['is_controlled'] === true));
    }
}
```

---

## Task 3: Controlled Substance Tracking (item 37)

### 3.1 Migration: `database/migrations/2026_05_28_005002_create_controlled_substance_dispensings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlled_substance_dispensings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            // prescription_id and prescription_item_id reference tables from the pharmacy domain
            $table->uuid('prescription_id')->index();
            $table->uuid('prescription_item_id')->index();
            $table->string('drug_code', 50);
            $table->string('drug_name', 255);
            $table->enum('schedule', [
                'schedule_i', 'schedule_ii', 'schedule_iii', 'schedule_iv', 'schedule_v',
            ]);
            $table->decimal('quantity_dispensed', 10, 2);
            $table->string('unit', 30);
            $table->foreignUuid('dispensed_by')->constrained('users');
            $table->timestamp('dispensed_at');
            // Witness required for Schedule II
            $table->uuid('witness_id')->nullable()->index();
            $table->timestamp('witness_confirmed_at')->nullable();
            $table->decimal('stock_balance_before', 10, 2);
            $table->decimal('stock_balance_after', 10, 2);
            $table->string('lot_number', 50)->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'dispensed_at']);
            $table->index(['drug_code', 'facility_id']);
            $table->index('schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlled_substance_dispensings');
    }
};
```

---

### 3.2 Migration: `database/migrations/2026_05_28_005003_create_controlled_substance_inventories_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlled_substance_inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('drug_code', 50);
            $table->string('drug_name', 255);
            $table->enum('schedule', [
                'schedule_i', 'schedule_ii', 'schedule_iii', 'schedule_iv', 'schedule_v',
            ]);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->string('unit', 30);
            $table->timestamp('last_reconciled_at')->nullable();
            $table->uuid('last_reconciled_by')->nullable()->index();
            $table->timestamps();

            $table->unique(['facility_id', 'drug_code'], 'cs_inventory_facility_drug_unique');
            $table->index(['facility_id', 'schedule']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlled_substance_inventories');
    }
};
```

---

### 3.3 Model: `app/Models/ControlledSubstanceDispensing.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlledSubstanceDispensing extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'facility_id',
        'patient_id',
        'prescription_id',
        'prescription_item_id',
        'drug_code',
        'drug_name',
        'schedule',
        'quantity_dispensed',
        'unit',
        'dispensed_by',
        'dispensed_at',
        'witness_id',
        'witness_confirmed_at',
        'stock_balance_before',
        'stock_balance_after',
        'lot_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'quantity_dispensed'   => 'decimal:2',
        'stock_balance_before' => 'decimal:2',
        'stock_balance_after'  => 'decimal:2',
        'dispensed_at'         => 'datetime',
        'witness_confirmed_at' => 'datetime',
        'expiry_date'          => 'date',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function witness(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_id');
    }
}
```

---

### 3.4 Model: `app/Models/ControlledSubstanceInventory.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlledSubstanceInventory extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'facility_id',
        'drug_code',
        'drug_name',
        'schedule',
        'current_balance',
        'unit',
        'last_reconciled_at',
        'last_reconciled_by',
    ];

    protected $casts = [
        'current_balance'    => 'decimal:2',
        'last_reconciled_at' => 'datetime',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function lastReconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reconciled_by');
    }
}
```

---

### 3.5 Service: `app/Services/Pharmacy/ControlledSubstanceService.php`

```php
<?php

namespace App\Services\Pharmacy;

use App\Models\ControlledSubstanceDispensing;
use App\Models\ControlledSubstanceInventory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ControlledSubstanceService
{
    /**
     * Discrepancy threshold (units) that triggers a security incident flag.
     */
    private const DISCREPANCY_THRESHOLD = 0.01;

    /**
     * DEA schedules that require a witness signature before dispensing.
     */
    private const WITNESS_REQUIRED_SCHEDULES = ['schedule_ii'];

    /**
     * Dispense a controlled substance, update inventory, and optionally
     * require witness confirmation for Schedule II.
     *
     * @param  array{
     *     facility_id: string,
     *     patient_id: string,
     *     prescription_id: string,
     *     prescription_item_id: string,
     *     drug_code: string,
     *     drug_name: string,
     *     schedule: string,
     *     quantity_dispensed: float,
     *     unit: string,
     *     dispensed_by: string,
     *     dispensed_at?: string,
     *     witness_id?: string|null,
     *     lot_number?: string|null,
     *     expiry_date?: string|null,
     *     notes?: string|null,
     * }  $data
     *
     * @throws RuntimeException if Schedule II and no witness_id provided.
     * @throws RuntimeException if inventory record not found.
     */
    public function dispense(array $data): ControlledSubstanceDispensing
    {
        if (
            in_array($data['schedule'], self::WITNESS_REQUIRED_SCHEDULES, true)
            && empty($data['witness_id'])
        ) {
            throw new RuntimeException(
                "Schedule II controlled substances require a witness. Provide witness_id."
            );
        }

        return DB::transaction(function () use ($data): ControlledSubstanceDispensing {
            /** @var ControlledSubstanceInventory $inventory */
            $inventory = ControlledSubstanceInventory::where('facility_id', $data['facility_id'])
                ->where('drug_code', $data['drug_code'])
                ->lockForUpdate()
                ->firstOrFail();

            $balanceBefore = (float) $inventory->current_balance;
            $qty           = (float) $data['quantity_dispensed'];

            if ($balanceBefore < $qty) {
                throw new RuntimeException(
                    "Insufficient stock: balance {$balanceBefore} {$inventory->unit}, requested {$qty}."
                );
            }

            $balanceAfter = round($balanceBefore - $qty, 2);

            $inventory->update(['current_balance' => $balanceAfter]);

            return ControlledSubstanceDispensing::create(array_merge($data, [
                'stock_balance_before' => $balanceBefore,
                'stock_balance_after'  => $balanceAfter,
                'dispensed_at'         => $data['dispensed_at'] ?? now(),
            ]));
        });
    }

    /**
     * Record witness confirmation for a Schedule II dispensing event.
     *
     * @throws RuntimeException if the dispensing does not require a witness or is already confirmed.
     */
    public function confirmWitness(string $dispensingId, string $witnessId): ControlledSubstanceDispensing
    {
        /** @var ControlledSubstanceDispensing $dispensing */
        $dispensing = ControlledSubstanceDispensing::findOrFail($dispensingId);

        if (!in_array($dispensing->schedule, self::WITNESS_REQUIRED_SCHEDULES, true)) {
            throw new RuntimeException(
                "Witness confirmation is only required for: " . implode(', ', self::WITNESS_REQUIRED_SCHEDULES)
            );
        }

        if ($dispensing->witness_confirmed_at !== null) {
            throw new RuntimeException("Dispensing {$dispensingId} has already been witnessed.");
        }

        $dispensing->update([
            'witness_id'           => $witnessId,
            'witness_confirmed_at' => now(),
        ]);

        return $dispensing->fresh();
    }

    /**
     * Reconcile the inventory for a specific drug at a facility.
     *
     * If the discrepancy between the current recorded balance and the physical
     * count exceeds the threshold, a security incident flag is raised.
     *
     * @throws RuntimeException if inventory record not found.
     */
    public function reconcileInventory(
        string $facilityId,
        string $drugCode,
        float  $actualBalance,
        string $reconcilierId,
    ): ControlledSubstanceInventory {
        /** @var ControlledSubstanceInventory $inventory */
        $inventory = ControlledSubstanceInventory::where('facility_id', $facilityId)
            ->where('drug_code', $drugCode)
            ->firstOrFail();

        $discrepancy = abs((float) $inventory->current_balance - $actualBalance);

        if ($discrepancy > self::DISCREPANCY_THRESHOLD) {
            $this->flagDiscrepancy(
                $inventory->id,
                "Reconciliation discrepancy: recorded {$inventory->current_balance}, "
                . "physical count {$actualBalance}, delta {$discrepancy} {$inventory->unit}."
            );
        }

        $inventory->update([
            'current_balance'    => $actualBalance,
            'last_reconciled_at' => now(),
            'last_reconciled_by' => $reconcilierId,
        ]);

        return $inventory->fresh();
    }

    /**
     * Return all dispensing log entries for a facility within a date range.
     */
    public function getDispenseLog(string $facilityId, Carbon $from, Carbon $to): Collection
    {
        return ControlledSubstanceDispensing::where('facility_id', $facilityId)
            ->whereBetween('dispensed_at', [$from, $to])
            ->with(['patient', 'dispensedBy', 'witness'])
            ->orderByDesc('dispensed_at')
            ->get();
    }

    /**
     * Return the current inventory of all controlled substances for a facility.
     */
    public function getInventory(string $facilityId): Collection
    {
        return ControlledSubstanceInventory::where('facility_id', $facilityId)
            ->orderBy('schedule')
            ->orderBy('drug_name')
            ->get();
    }

    /**
     * Flag a discrepancy in the controlled substance inventory.
     *
     * Logs a critical security alert. If the application has a SecurityIncident
     * event class registered, it will be dispatched for further handling (audit
     * trail, CISO notification, etc.).
     */
    public function flagDiscrepancy(string $inventoryId, string $reason): void
    {
        Log::critical('Controlled substance inventory discrepancy detected', [
            'inventory_id' => $inventoryId,
            'reason'       => $reason,
            'flagged_at'   => now()->toIso8601String(),
        ]);

        if (class_exists(\App\Events\ControlledSubstanceDiscrepancy::class)) {
            \Illuminate\Support\Facades\Event::dispatch(
                new \App\Events\ControlledSubstanceDiscrepancy($inventoryId, $reason)
            );
        }
    }
}
```

---

### 3.6 Controller: `app/Http/Controllers/Api/V1/ControlledSubstanceController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\ControlledSubstanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ControlledSubstanceController extends Controller
{
    public function __construct(private readonly ControlledSubstanceService $service) {}

    /**
     * POST /api/pharmacy/controlled-substances/dispense
     */
    public function dispense(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'          => ['required', 'uuid', 'exists:facilities,id'],
            'patient_id'           => ['required', 'uuid', 'exists:patients,id'],
            'prescription_id'      => ['required', 'uuid'],
            'prescription_item_id' => ['required', 'uuid'],
            'drug_code'            => ['required', 'string', 'max:50'],
            'drug_name'            => ['required', 'string', 'max:255'],
            'schedule'             => ['required', 'in:schedule_i,schedule_ii,schedule_iii,schedule_iv,schedule_v'],
            'quantity_dispensed'   => ['required', 'numeric', 'min:0.01'],
            'unit'                 => ['required', 'string', 'max:30'],
            'dispensed_by'         => ['required', 'uuid', 'exists:users,id'],
            'dispensed_at'         => ['sometimes', 'date'],
            'witness_id'           => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'lot_number'           => ['sometimes', 'nullable', 'string', 'max:50'],
            'expiry_date'          => ['sometimes', 'nullable', 'date'],
            'notes'                => ['sometimes', 'nullable', 'string'],
        ]);

        $dispensing = $this->service->dispense($validated);

        return response()->json(['data' => $dispensing], Response::HTTP_CREATED);
    }

    /**
     * POST /api/pharmacy/controlled-substances/{id}/witness
     */
    public function confirmWitness(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'witness_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $dispensing = $this->service->confirmWitness($id, $validated['witness_id']);

        return response()->json(['data' => $dispensing]);
    }

    /**
     * POST /api/pharmacy/controlled-substances/reconcile
     */
    public function reconcile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id'    => ['required', 'uuid', 'exists:facilities,id'],
            'drug_code'      => ['required', 'string', 'max:50'],
            'actual_balance' => ['required', 'numeric', 'min:0'],
            'reconciler_id'  => ['required', 'uuid', 'exists:users,id'],
        ]);

        $inventory = $this->service->reconcileInventory(
            $validated['facility_id'],
            $validated['drug_code'],
            (float) $validated['actual_balance'],
            $validated['reconciler_id'],
        );

        return response()->json(['data' => $inventory]);
    }

    /**
     * GET /api/pharmacy/controlled-substances/log
     *
     * Query params: facility_id (required), from (Y-m-d), to (Y-m-d)
     */
    public function log(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
            'from'        => ['sometimes', 'date_format:Y-m-d'],
            'to'          => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();
        $to   = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        $log = $this->service->getDispenseLog($validated['facility_id'], $from, $to);

        return response()->json(['data' => $log]);
    }

    /**
     * GET /api/pharmacy/controlled-substances/inventory
     *
     * Query params: facility_id (required)
     */
    public function inventory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
        ]);

        $inventory = $this->service->getInventory($validated['facility_id']);

        return response()->json(['data' => $inventory]);
    }
}
```

---

### 3.7 Routes (add to `routes/api.php`)

```php
// Controlled Substances
Route::middleware(['auth:sanctum'])->prefix('pharmacy/controlled-substances')->group(function () {
    Route::post('dispense',             [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'dispense']);
    Route::post('{id}/witness',         [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'confirmWitness']);
    Route::post('reconcile',            [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'reconcile']);
    Route::get('log',                   [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'log']);
    Route::get('inventory',             [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'inventory']);
});
```

---

### 3.8 Test: `tests/Feature/ControlledSubstanceTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\ControlledSubstanceDispensing;
use App\Models\ControlledSubstanceInventory;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlledSubstanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $witness;
    private Facility $facility;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->witness  = User::factory()->create();
        $this->facility = Facility::factory()->create();
        $this->patient  = Patient::factory()->create();
    }

    private function seedInventory(string $drugCode = 'OXY10', float $balance = 100.0): ControlledSubstanceInventory
    {
        return ControlledSubstanceInventory::factory()->create([
            'facility_id'     => $this->facility->id,
            'drug_code'       => $drugCode,
            'drug_name'       => 'Oxycodone HCl',
            'schedule'        => 'schedule_ii',
            'current_balance' => $balance,
            'unit'            => 'tablet',
        ]);
    }

    private function dispensePayload(array $overrides = []): array
    {
        return array_merge([
            'facility_id'          => $this->facility->id,
            'patient_id'           => $this->patient->id,
            'prescription_id'      => fake()->uuid(),
            'prescription_item_id' => fake()->uuid(),
            'drug_code'            => 'OXY10',
            'drug_name'            => 'Oxycodone HCl',
            'schedule'             => 'schedule_ii',
            'quantity_dispensed'   => 10,
            'unit'                 => 'tablet',
            'dispensed_by'         => $this->user->id,
            'witness_id'           => $this->witness->id,
        ], $overrides);
    }

    public function test_dispense_schedule_ii_requires_witness(): void
    {
        $this->seedInventory();

        // Omit witness_id — should fail
        $payload = $this->dispensePayload(['witness_id' => null]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pharmacy/controlled-substances/dispense', $payload);

        $response->assertStatus(500); // RuntimeException
    }

    public function test_dispense_schedule_ii_with_witness_succeeds(): void
    {
        $this->seedInventory('OXY10', 100.0);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pharmacy/controlled-substances/dispense', $this->dispensePayload());

        $response->assertCreated();
        $this->assertEquals(10.0, $response->json('data.quantity_dispensed'));
        $this->assertEquals(90.0, $response->json('data.stock_balance_after'));

        // Inventory should be decremented
        $this->assertDatabaseHas('controlled_substance_inventories', [
            'facility_id'     => $this->facility->id,
            'drug_code'       => 'OXY10',
            'current_balance' => 90.0,
        ]);
    }

    public function test_confirm_witness_sets_confirmed_at(): void
    {
        $this->seedInventory();

        $dispensing = ControlledSubstanceDispensing::factory()->create([
            'facility_id'          => $this->facility->id,
            'patient_id'           => $this->patient->id,
            'prescription_id'      => fake()->uuid(),
            'prescription_item_id' => fake()->uuid(),
            'drug_code'            => 'OXY10',
            'drug_name'            => 'Oxycodone HCl',
            'schedule'             => 'schedule_ii',
            'quantity_dispensed'   => 5,
            'unit'                 => 'tablet',
            'dispensed_by'         => $this->user->id,
            'witness_id'           => null,
            'witness_confirmed_at' => null,
            'stock_balance_before' => 100.0,
            'stock_balance_after'  => 95.0,
            'dispensed_at'         => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/pharmacy/controlled-substances/{$dispensing->id}/witness", [
                'witness_id' => $this->witness->id,
            ]);

        $response->assertOk();
        $this->assertNotNull($response->json('data.witness_confirmed_at'));
        $this->assertEquals($this->witness->id, $response->json('data.witness_id'));
    }

    public function test_inventory_balance_updated_after_dispense(): void
    {
        $this->seedInventory('MOR10', 50.0);

        $payload = $this->dispensePayload([
            'drug_code' => 'MOR10',
            'drug_name' => 'Morphine Sulfate',
            'quantity_dispensed' => 5,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/pharmacy/controlled-substances/dispense', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('controlled_substance_inventories', [
            'facility_id'     => $this->facility->id,
            'drug_code'       => 'MOR10',
            'current_balance' => 45.0,
        ]);
    }

    public function test_reconcile_inventory_updates_balance(): void
    {
        $this->seedInventory('OXY10', 80.0);

        $response = $this->actingAs($this->user)
            ->postJson('/api/pharmacy/controlled-substances/reconcile', [
                'facility_id'    => $this->facility->id,
                'drug_code'      => 'OXY10',
                'actual_balance' => 78.5,
                'reconciler_id'  => $this->user->id,
            ]);

        $response->assertOk();
        $this->assertEquals(78.5, $response->json('data.current_balance'));
        $this->assertNotNull($response->json('data.last_reconciled_at'));
    }
}
```

---

## Implementation Order

1. Run migrations in sequence (005000 → 005003)
2. Register new models in `app/Models/`
3. Create and bind services in `AppServiceProvider`:
   - `RadiologyReportService`
   - `FormularyService`
   - `ControlledSubstanceService`
4. Create controllers
5. Register routes in `routes/api.php`
6. Run `php artisan route:cache`
7. Run feature tests

## Notes

- **RadiologyReport.imaging_order_id** is a nullable UUID without a strict FK constraint so this migration runs independently of the Phase 1 imaging_orders table.
- **FormularyService.search** uses PostgreSQL's `jsonb_array_elements_text` for brand name search. If SQLite is used in tests, mock this method or replace with a Laravel Scout approach.
- **ControlledSubstanceService.flagDiscrepancy** dispatches `App\Events\ControlledSubstanceDiscrepancy` if the class exists. Create that event + listener to wire into the audit/security pipeline.
- **Schedule II witness flow:** `dispense()` records `witness_id` at dispense time. `confirmWitness()` stamps `witness_confirmed_at` later (supports two-person rule where the witness signs off after verification). Both steps are required for a fully compliant Schedule II record.
- All monetary and quantity columns use `decimal` types to prevent floating-point drift at the database level.
