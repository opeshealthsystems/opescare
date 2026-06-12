# Phase 4: Revenue Cycle Dashboard + Patient Payment Plans

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans

**Goal:** Add revenue cycle reporting dashboard and patient installment payment plans.
**Architecture:** New service classes query existing Invoice/InsuranceClaim/Payment models. No existing tables modified.
**Tech Stack:** Laravel 11, PHP 8.3, PostgreSQL, UUID PKs

---

## Task 1: Revenue Cycle Dashboard (item 29)

### 1.1 Service: `app/Services/Reports/RevenueCycleService.php`

```php
<?php

namespace App\Services\Reports;

use App\Models\InsuranceClaim;
use App\Models\ClaimPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RevenueCycleService
{
    /**
     * Return a high-level revenue cycle summary for a facility within a date range.
     *
     * @param  string  $facilityId
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return array{
     *     total_billed: float,
     *     total_collected: float,
     *     collection_rate: float,
     *     total_pending: float,
     *     total_denied: float,
     *     denial_rate: float,
     *     avg_days_to_payment: float,
     *     outstanding_ar: float,
     *     claims_by_status: array,
     * }
     */
    public function getSummary(string $facilityId, Carbon $from, Carbon $to): array
    {
        $claims = InsuranceClaim::where('facility_id', $facilityId)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $totalBilled    = (float) $claims->sum('claimed_amount');
        $totalCollected = (float) $claims
            ->whereIn('status', ['paid', 'partially_paid'])
            ->sum('paid_amount');
        $totalPending   = (float) $claims
            ->whereIn('status', ['submitted', 'under_review'])
            ->sum('claimed_amount');
        $totalDenied    = (float) $claims
            ->where('status', 'rejected')
            ->sum('claimed_amount');

        $collectionRate = $totalBilled > 0
            ? round($totalCollected / $totalBilled * 100, 2)
            : 0.0;
        $denialRate = $totalBilled > 0
            ? round($totalDenied / $totalBilled * 100, 2)
            : 0.0;

        // Average days from claim submitted_at to ClaimPayment.paid_at
        $avgDays = (float) DB::table('insurance_claims as ic')
            ->join('claim_payments as cp', 'cp.insurance_claim_id', '=', 'ic.id')
            ->where('ic.facility_id', $facilityId)
            ->whereBetween('ic.created_at', [$from, $to])
            ->whereNotNull('ic.submitted_at')
            ->whereNotNull('cp.paid_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (cp.paid_at - ic.submitted_at)) / 86400) as avg_days')
            ->value('avg_days') ?? 0.0;

        // Outstanding AR: approved/partially_approved but not fully paid
        $outstandingAr = (float) $claims
            ->whereIn('status', ['approved', 'partially_approved', 'partially_paid'])
            ->sum(fn ($c) => $c->approved_amount - $c->paid_amount);

        // Claims grouped by status
        $claimsByStatus = [];
        foreach ($claims->groupBy('status') as $status => $group) {
            $claimsByStatus[$status] = [
                'count'  => $group->count(),
                'amount' => (float) $group->sum('claimed_amount'),
            ];
        }

        return [
            'total_billed'        => $totalBilled,
            'total_collected'     => $totalCollected,
            'collection_rate'     => $collectionRate,
            'total_pending'       => $totalPending,
            'total_denied'        => $totalDenied,
            'denial_rate'         => $denialRate,
            'avg_days_to_payment' => round($avgDays, 1),
            'outstanding_ar'      => $outstandingAr,
            'claims_by_status'    => $claimsByStatus,
        ];
    }

    /**
     * Return outstanding claims bucketed by age (days since created_at).
     *
     * Buckets: 0-30d, 31-60d, 61-90d, 91-120d, 120+d
     *
     * @param  string  $facilityId
     * @return array<string, array{count: int, amount: float}>
     */
    public function getAgingReport(string $facilityId): array
    {
        $buckets = [
            '0-30'   => ['count' => 0, 'amount' => 0.0],
            '31-60'  => ['count' => 0, 'amount' => 0.0],
            '61-90'  => ['count' => 0, 'amount' => 0.0],
            '91-120' => ['count' => 0, 'amount' => 0.0],
            '120+'   => ['count' => 0, 'amount' => 0.0],
        ];

        $outstanding = InsuranceClaim::where('facility_id', $facilityId)
            ->whereNotIn('status', ['paid', 'cancelled', 'rejected'])
            ->get(['id', 'created_at', 'claimed_amount', 'paid_amount']);

        $now = Carbon::now();

        foreach ($outstanding as $claim) {
            $age = (int) $now->diffInDays($claim->created_at);
            $remaining = max(0, $claim->claimed_amount - $claim->paid_amount);

            $key = match (true) {
                $age <= 30  => '0-30',
                $age <= 60  => '31-60',
                $age <= 90  => '61-90',
                $age <= 120 => '91-120',
                default     => '120+',
            };

            $buckets[$key]['count']++;
            $buckets[$key]['amount'] += $remaining;
        }

        // Round amounts
        foreach ($buckets as &$bucket) {
            $bucket['amount'] = round($bucket['amount'], 2);
        }

        return $buckets;
    }

    /**
     * Group denial reasons from ClaimDecision.decision_notes by keyword patterns.
     *
     * @param  string  $facilityId
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return array<int, array{reason: string, count: int, amount: float}>
     */
    public function getDenialReasons(string $facilityId, Carbon $from, Carbon $to): array
    {
        $deniedClaims = InsuranceClaim::with('decisions')
            ->where('facility_id', $facilityId)
            ->where('status', 'rejected')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $patterns = [
            'Not Covered'              => ['not covered', 'non-covered', 'excluded'],
            'Medical Necessity'        => ['medical necessity', 'not medically necessary', 'clinically unnecessary'],
            'Prior Authorization'      => ['prior auth', 'pre-authorization', 'prior authorization'],
            'Duplicate Claim'          => ['duplicate', 'already processed'],
            'Coding Error'             => ['invalid code', 'incorrect code', 'coding error', 'icd', 'cpt'],
            'Timely Filing'            => ['timely filing', 'filing deadline', 'late submission'],
            'Eligibility / Coverage'   => ['not eligible', 'eligibility', 'coverage terminated', 'inactive'],
            'Missing Information'      => ['missing', 'incomplete', 'not submitted'],
            'Other'                    => [],
        ];

        $tally = [];
        foreach (array_keys($patterns) as $reason) {
            $tally[$reason] = ['count' => 0, 'amount' => 0.0];
        }

        foreach ($deniedClaims as $claim) {
            $notes = '';
            foreach ($claim->decisions ?? [] as $decision) {
                $notes .= ' ' . strtolower($decision->decision_notes ?? '');
            }
            $notes = trim($notes);

            $matched = false;
            foreach ($patterns as $reason => $keywords) {
                if (empty($keywords)) {
                    continue;
                }
                foreach ($keywords as $kw) {
                    if (str_contains($notes, $kw)) {
                        $tally[$reason]['count']++;
                        $tally[$reason]['amount'] += (float) $claim->claimed_amount;
                        $matched = true;
                        break 2;
                    }
                }
            }

            if (!$matched) {
                $tally['Other']['count']++;
                $tally['Other']['amount'] += (float) $claim->claimed_amount;
            }
        }

        $results = [];
        foreach ($tally as $reason => $data) {
            if ($data['count'] > 0) {
                $results[] = [
                    'reason' => $reason,
                    'count'  => $data['count'],
                    'amount' => round($data['amount'], 2),
                ];
            }
        }

        usort($results, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $results;
    }

    /**
     * Return per-month billed / collected / denied totals for the last N months.
     *
     * @param  string  $facilityId
     * @param  int     $months
     * @return array<int, array{month: string, billed: float, collected: float, denied: float}>
     */
    public function getMonthlyTrend(string $facilityId, int $months = 6): array
    {
        $from = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $rows = DB::table('insurance_claims')
            ->where('facility_id', $facilityId)
            ->where('created_at', '>=', $from)
            ->selectRaw("
                TO_CHAR(created_at, 'YYYY-MM') AS month,
                SUM(claimed_amount) AS billed,
                SUM(CASE WHEN status IN ('paid','partially_paid') THEN paid_amount ELSE 0 END) AS collected,
                SUM(CASE WHEN status = 'rejected' THEN claimed_amount ELSE 0 END) AS denied
            ")
            ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM')")
            ->orderBy('month')
            ->get();

        return $rows->map(fn ($r) => [
            'month'     => $r->month,
            'billed'    => round((float) $r->billed, 2),
            'collected' => round((float) $r->collected, 2),
            'denied'    => round((float) $r->denied, 2),
        ])->toArray();
    }
}
```

---

### 1.2 Controller: `app/Http/Controllers/Api/V1/Reports/RevenueCycleController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\RevenueCycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RevenueCycleController extends Controller
{
    public function __construct(private readonly RevenueCycleService $service) {}

    /**
     * GET /api/reports/revenue-cycle/summary
     *
     * Query params: facility_id (required), from (Y-m-d), to (Y-m-d)
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
            'from'        => ['sometimes', 'date_format:Y-m-d'],
            'to'          => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : Carbon::now()->startOfMonth();
        $to   = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        $data = $this->service->getSummary($validated['facility_id'], $from, $to);

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/reports/revenue-cycle/aging
     *
     * Query params: facility_id (required)
     */
    public function aging(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
        ]);

        $data = $this->service->getAgingReport($validated['facility_id']);

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/reports/revenue-cycle/denials
     *
     * Query params: facility_id (required), from (Y-m-d), to (Y-m-d)
     */
    public function denials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
            'from'        => ['sometimes', 'date_format:Y-m-d'],
            'to'          => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : Carbon::now()->subMonths(3)->startOfDay();
        $to   = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        $data = $this->service->getDenialReasons($validated['facility_id'], $from, $to);

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/reports/revenue-cycle/trend
     *
     * Query params: facility_id (required), months (int 1-24, default 6)
     */
    public function trend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
            'months'      => ['sometimes', 'integer', 'min:1', 'max:24'],
        ]);

        $data = $this->service->getMonthlyTrend(
            $validated['facility_id'],
            (int) ($validated['months'] ?? 6),
        );

        return response()->json(['data' => $data]);
    }
}
```

---

### 1.3 Routes (add to `routes/api.php` under a `reports` prefix group)

```php
// Revenue Cycle Reports
Route::prefix('reports/revenue-cycle')->middleware(['auth:sanctum'])->group(function () {
    Route::get('summary', [\App\Http\Controllers\Api\V1\Reports\RevenueCycleController::class, 'summary']);
    Route::get('aging',   [\App\Http\Controllers\Api\V1\Reports\RevenueCycleController::class, 'aging']);
    Route::get('denials', [\App\Http\Controllers\Api\V1\Reports\RevenueCycleController::class, 'denials']);
    Route::get('trend',   [\App\Http\Controllers\Api\V1\Reports\RevenueCycleController::class, 'trend']);
});
```

---

### 1.4 Test: `tests/Feature/Reports/RevenueCycleTest.php`

```php
<?php

namespace Tests\Feature\Reports;

use App\Models\InsuranceClaim;
use App\Models\ClaimPayment;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RevenueCycleTest extends TestCase
{
    use RefreshDatabase;

    private Facility $facility;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->facility = Facility::factory()->create();
        $this->user     = User::factory()->create();
    }

    public function test_summary_returns_all_expected_keys(): void
    {
        InsuranceClaim::factory()->count(3)->create([
            'facility_id'    => $this->facility->id,
            'claimed_amount' => 1000.00,
            'approved_amount'=> 900.00,
            'paid_amount'    => 900.00,
            'status'         => 'paid',
        ]);

        InsuranceClaim::factory()->count(2)->create([
            'facility_id'    => $this->facility->id,
            'claimed_amount' => 500.00,
            'approved_amount'=> 0.00,
            'paid_amount'    => 0.00,
            'status'         => 'rejected',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/revenue-cycle/summary?facility_id=' . $this->facility->id
                . '&from=' . Carbon::now()->subMonth()->format('Y-m-d')
                . '&to=' . Carbon::now()->format('Y-m-d'));

        $response->assertOk();

        $data = $response->json('data');

        $expectedKeys = [
            'total_billed', 'total_collected', 'collection_rate',
            'total_pending', 'total_denied', 'denial_rate',
            'avg_days_to_payment', 'outstanding_ar', 'claims_by_status',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Missing key: {$key}");
        }
    }

    public function test_collection_rate_calculation_is_correct(): void
    {
        // 3 paid claims @ 1000 each => total_billed = 3000, total_collected = 2700
        InsuranceClaim::factory()->count(3)->create([
            'facility_id'    => $this->facility->id,
            'claimed_amount' => 1000.00,
            'paid_amount'    => 900.00,
            'status'         => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/revenue-cycle/summary?facility_id=' . $this->facility->id);

        $response->assertOk();

        $data = $response->json('data');

        $this->assertEquals(3000.00, $data['total_billed']);
        $this->assertEquals(2700.00, $data['total_collected']);
        $this->assertEquals(90.00,   $data['collection_rate']); // 2700/3000*100 = 90%
    }

    public function test_aging_report_buckets_claims_by_age(): void
    {
        // Claim created 45 days ago => '31-60' bucket
        InsuranceClaim::factory()->create([
            'facility_id'    => $this->facility->id,
            'claimed_amount' => 800.00,
            'paid_amount'    => 0.00,
            'status'         => 'submitted',
            'created_at'     => Carbon::now()->subDays(45),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/revenue-cycle/aging?facility_id=' . $this->facility->id);

        $response->assertOk();

        $data = $response->json('data');

        $this->assertArrayHasKey('31-60', $data);
        $this->assertEquals(1,      $data['31-60']['count']);
        $this->assertEquals(800.00, $data['31-60']['amount']);
    }

    public function test_monthly_trend_returns_correct_structure(): void
    {
        InsuranceClaim::factory()->create([
            'facility_id'    => $this->facility->id,
            'claimed_amount' => 1200.00,
            'paid_amount'    => 1200.00,
            'status'         => 'paid',
            'created_at'     => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/revenue-cycle/trend?facility_id=' . $this->facility->id . '&months=3');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertIsArray($data);

        if (count($data) > 0) {
            $this->assertArrayHasKey('month',     $data[0]);
            $this->assertArrayHasKey('billed',    $data[0]);
            $this->assertArrayHasKey('collected', $data[0]);
            $this->assertArrayHasKey('denied',    $data[0]);
        }
    }
}
```

---

## Task 2: Patient Payment Plans (item 30)

### 2.1 Migration: `database/migrations/2026_05_28_004000_create_patient_payment_plans_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_payment_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('down_payment', 12, 2)->default(0);
            $table->decimal('installment_amount', 12, 2);
            $table->unsignedInteger('installment_count');
            $table->unsignedInteger('paid_count')->default(0);
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly']);
            $table->enum('status', ['active', 'completed', 'defaulted', 'cancelled'])->default('active');
            $table->date('next_due_date');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'status']);
            $table->index(['facility_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_payment_plans');
    }
};
```

---

### 2.2 Migration: `database/migrations/2026_05_28_004001_create_payment_plan_installments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_plan_installments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_plan_id')
                ->constrained('patient_payment_plans')
                ->cascadeOnDelete();
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'partial', 'missed'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->timestamps();

            $table->index(['payment_plan_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plan_installments');
    }
};
```

---

### 2.3 Model: `app/Models/PatientPaymentPlan.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientPaymentPlan extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'invoice_id',
        'facility_id',
        'total_amount',
        'down_payment',
        'installment_amount',
        'installment_count',
        'paid_count',
        'frequency',
        'status',
        'next_due_date',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'total_amount'       => 'decimal:2',
        'down_payment'       => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'installment_count'  => 'integer',
        'paid_count'         => 'integer',
        'next_due_date'      => 'date',
        'started_at'         => 'datetime',
        'completed_at'       => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PaymentPlanInstallment::class, 'payment_plan_id');
    }
}
```

---

### 2.4 Model: `app/Models/PaymentPlanInstallment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentPlanInstallment extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'due_date',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
        'payment_reference',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'amount'      => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at'     => 'datetime',
    ];

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PatientPaymentPlan::class, 'payment_plan_id');
    }
}
```

---

### 2.5 Service: `app/Services/Billing/PaymentPlanService.php`

```php
<?php

namespace App\Services\Billing;

use App\Models\PatientPaymentPlan;
use App\Models\PaymentPlanInstallment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PaymentPlanService
{
    private const MISSED_INSTALLMENTS_BEFORE_DEFAULT = 2;

    /**
     * Create a new payment plan.
     *
     * Validates: total_amount == down_payment + (installment_amount × installment_count)
     *
     * @param  array{
     *     patient_id: string,
     *     invoice_id: string,
     *     facility_id: string,
     *     total_amount: float,
     *     down_payment: float,
     *     installment_amount: float,
     *     installment_count: int,
     *     frequency: string,
     *     next_due_date: string,
     *     notes?: string,
     * }  $data
     */
    public function createPlan(array $data): PatientPaymentPlan
    {
        $expected = round(
            (float) $data['down_payment'] + ((float) $data['installment_amount'] * (int) $data['installment_count']),
            2,
        );
        $actual   = round((float) $data['total_amount'], 2);

        if (abs($expected - $actual) > 0.01) {
            throw new InvalidArgumentException(
                "total_amount ({$actual}) must equal down_payment + (installment_amount × installment_count) = {$expected}."
            );
        }

        return DB::transaction(function () use ($data): PatientPaymentPlan {
            /** @var PatientPaymentPlan $plan */
            $plan = PatientPaymentPlan::create([
                'patient_id'         => $data['patient_id'],
                'invoice_id'         => $data['invoice_id'],
                'facility_id'        => $data['facility_id'],
                'total_amount'       => $data['total_amount'],
                'down_payment'       => $data['down_payment'] ?? 0,
                'installment_amount' => $data['installment_amount'],
                'installment_count'  => $data['installment_count'],
                'paid_count'         => 0,
                'frequency'          => $data['frequency'],
                'status'             => 'active',
                'next_due_date'      => $data['next_due_date'],
                'started_at'         => now(),
                'notes'              => $data['notes'] ?? null,
            ]);

            $this->generateInstallments($plan);

            return $plan->load('installments');
        });
    }

    /**
     * Generate installment records for a plan based on frequency.
     */
    public function generateInstallments(PatientPaymentPlan $plan): void
    {
        $dueDate = Carbon::parse($plan->next_due_date);

        $installments = [];
        for ($i = 0; $i < $plan->installment_count; $i++) {
            $installments[] = [
                'payment_plan_id'  => $plan->id,
                'due_date'         => $dueDate->format('Y-m-d'),
                'amount'           => $plan->installment_amount,
                'paid_amount'      => 0,
                'status'           => 'pending',
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $dueDate = match ($plan->frequency) {
                'weekly'   => $dueDate->addWeek(),
                'biweekly' => $dueDate->addWeeks(2),
                'monthly'  => $dueDate->addMonth(),
            };
        }

        PaymentPlanInstallment::insert($installments);
    }

    /**
     * Record a payment against a specific installment.
     *
     * @throws RuntimeException if the installment is already fully paid or the plan is not active.
     */
    public function recordInstallmentPayment(
        string $installmentId,
        float  $amount,
        string $reference,
    ): PaymentPlanInstallment {
        return DB::transaction(function () use ($installmentId, $amount, $reference): PaymentPlanInstallment {
            /** @var PaymentPlanInstallment $installment */
            $installment = PaymentPlanInstallment::lockForUpdate()->findOrFail($installmentId);

            /** @var PatientPaymentPlan $plan */
            $plan = PatientPaymentPlan::lockForUpdate()->findOrFail($installment->payment_plan_id);

            if ($plan->status !== 'active') {
                throw new RuntimeException("Payment plan is not active (status: {$plan->status}).");
            }

            if ($installment->status === 'paid') {
                throw new RuntimeException("Installment {$installmentId} is already fully paid.");
            }

            $newPaid = round($installment->paid_amount + $amount, 2);

            $installment->update([
                'paid_amount'       => $newPaid,
                'status'            => $newPaid >= $installment->amount ? 'paid' : 'partial',
                'paid_at'           => now(),
                'payment_reference' => $reference,
            ]);

            // Update plan paid_count and next_due_date
            if ($installment->status === 'paid') {
                $plan->increment('paid_count');
                $plan->refresh();

                if ($plan->paid_count >= $plan->installment_count) {
                    $plan->update(['status' => 'completed', 'completed_at' => now()]);
                } else {
                    // Set next_due_date to the earliest pending installment
                    $nextDue = PaymentPlanInstallment::where('payment_plan_id', $plan->id)
                        ->whereIn('status', ['pending', 'partial', 'missed'])
                        ->orderBy('due_date')
                        ->value('due_date');

                    if ($nextDue) {
                        $plan->update(['next_due_date' => $nextDue]);
                    }
                }
            }

            return $installment->fresh();
        });
    }

    /**
     * Mark overdue installments as 'missed'; default a plan if ≥2 installments are missed.
     *
     * @return int Number of installments marked as missed in this run.
     */
    public function checkForDefaults(): int
    {
        $missedCount = PaymentPlanInstallment::whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', Carbon::today())
            ->whereHas('paymentPlan', fn ($q) => $q->where('status', 'active'))
            ->update(['status' => 'missed']);

        // Default plans with too many missed installments
        $planIds = PaymentPlanInstallment::where('status', 'missed')
            ->select('payment_plan_id')
            ->groupBy('payment_plan_id')
            ->havingRaw('COUNT(*) >= ?', [self::MISSED_INSTALLMENTS_BEFORE_DEFAULT])
            ->pluck('payment_plan_id');

        if ($planIds->isNotEmpty()) {
            PatientPaymentPlan::whereIn('id', $planIds)
                ->where('status', 'active')
                ->update(['status' => 'defaulted']);
        }

        return $missedCount;
    }

    /**
     * Return a summary of a single payment plan.
     *
     * @return array{
     *     plan: PatientPaymentPlan,
     *     installments: Collection,
     *     total_paid: float,
     *     total_remaining: float,
     *     is_overdue: bool,
     * }
     */
    public function getPlanSummary(string $planId): array
    {
        /** @var PatientPaymentPlan $plan */
        $plan         = PatientPaymentPlan::with('installments')->findOrFail($planId);
        $installments = $plan->installments;

        $totalPaid      = (float) $installments->sum('paid_amount') + (float) $plan->down_payment;
        $totalRemaining = max(0, round((float) $plan->total_amount - $totalPaid, 2));
        $isOverdue      = $installments->contains(
            fn ($i) => in_array($i->status, ['missed', 'partial']) && $i->due_date->isPast()
        );

        return [
            'plan'            => $plan,
            'installments'    => $installments,
            'total_paid'      => round($totalPaid, 2),
            'total_remaining' => $totalRemaining,
            'is_overdue'      => $isOverdue,
        ];
    }
}
```

---

### 2.6 Controller: `app/Http/Controllers/Api/V1/PatientPaymentPlanController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PatientPaymentPlan;
use App\Services\Billing\PaymentPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatientPaymentPlanController extends Controller
{
    public function __construct(private readonly PaymentPlanService $service) {}

    /**
     * POST /api/payment-plans
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'         => ['required', 'uuid', 'exists:patients,id'],
            'invoice_id'         => ['required', 'uuid', 'exists:invoices,id'],
            'facility_id'        => ['required', 'uuid', 'exists:facilities,id'],
            'total_amount'       => ['required', 'numeric', 'min:0.01'],
            'down_payment'       => ['sometimes', 'numeric', 'min:0'],
            'installment_amount' => ['required', 'numeric', 'min:0.01'],
            'installment_count'  => ['required', 'integer', 'min:1', 'max:120'],
            'frequency'          => ['required', 'in:weekly,biweekly,monthly'],
            'next_due_date'      => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'notes'              => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $plan = $this->service->createPlan($validated);

        return response()->json(['data' => $plan], Response::HTTP_CREATED);
    }

    /**
     * GET /api/payment-plans/{id}
     */
    public function show(string $id): JsonResponse
    {
        $summary = $this->service->getPlanSummary($id);

        return response()->json(['data' => $summary]);
    }

    /**
     * POST /api/payment-plans/{id}/installments/{installmentId}/pay
     */
    public function recordPayment(Request $request, string $id, string $installmentId): JsonResponse
    {
        $validated = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'reference' => ['required', 'string', 'max:100'],
        ]);

        // Confirm installment belongs to this plan
        $plan = PatientPaymentPlan::findOrFail($id);
        abort_unless(
            $plan->installments()->where('id', $installmentId)->exists(),
            Response::HTTP_NOT_FOUND,
            'Installment not found for this plan.',
        );

        $installment = $this->service->recordInstallmentPayment(
            $installmentId,
            (float) $validated['amount'],
            $validated['reference'],
        );

        return response()->json(['data' => $installment]);
    }

    /**
     * GET /api/patients/{patientId}/payment-plans
     */
    public function forPatient(string $patientId): JsonResponse
    {
        $plans = PatientPaymentPlan::where('patient_id', $patientId)
            ->withCount('installments')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $plans]);
    }
}
```

---

### 2.7 Routes (add to `routes/api.php`)

```php
// Patient Payment Plans
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('payment-plans',                                                         [\App\Http\Controllers\Api\V1\PatientPaymentPlanController::class, 'store']);
    Route::get('payment-plans/{id}',                                                     [\App\Http\Controllers\Api\V1\PatientPaymentPlanController::class, 'show']);
    Route::post('payment-plans/{id}/installments/{installmentId}/pay',                  [\App\Http\Controllers\Api\V1\PatientPaymentPlanController::class, 'recordPayment']);
    Route::get('patients/{patientId}/payment-plans',                                     [\App\Http\Controllers\Api\V1\PatientPaymentPlanController::class, 'forPatient']);
});
```

---

### 2.8 Test: `tests/Feature/PatientPaymentPlanTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientPaymentPlan;
use App\Models\PaymentPlanInstallment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PatientPaymentPlanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Facility $facility;
    private Patient $patient;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->facility = Facility::factory()->create();
        $this->patient  = Patient::factory()->create();
        $this->invoice  = Invoice::factory()->create([
            'patient_id'  => $this->patient->id,
            'facility_id' => $this->facility->id,
            'total_amount'=> 1200.00,
        ]);
    }

    public function test_create_plan_generates_correct_installment_count(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/payment-plans', [
            'patient_id'         => $this->patient->id,
            'invoice_id'         => $this->invoice->id,
            'facility_id'        => $this->facility->id,
            'total_amount'       => 1200.00,
            'down_payment'       => 0.00,
            'installment_amount' => 100.00,
            'installment_count'  => 12,
            'frequency'          => 'monthly',
            'next_due_date'      => Carbon::now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertCreated();

        $planId = $response->json('data.id');
        $this->assertDatabaseCount('payment_plan_installments', 12);

        $installments = PaymentPlanInstallment::where('payment_plan_id', $planId)
            ->orderBy('due_date')
            ->get();

        $this->assertCount(12, $installments);
        $this->assertEquals('pending', $installments->first()->status);
    }

    public function test_installment_dates_advance_by_frequency(): void
    {
        $firstDue = Carbon::now()->addMonth()->startOfDay();

        $response = $this->actingAs($this->user)->postJson('/api/payment-plans', [
            'patient_id'         => $this->patient->id,
            'invoice_id'         => $this->invoice->id,
            'facility_id'        => $this->facility->id,
            'total_amount'       => 300.00,
            'down_payment'       => 0.00,
            'installment_amount' => 100.00,
            'installment_count'  => 3,
            'frequency'          => 'monthly',
            'next_due_date'      => $firstDue->format('Y-m-d'),
        ]);

        $response->assertCreated();

        $planId       = $response->json('data.id');
        $installments = PaymentPlanInstallment::where('payment_plan_id', $planId)
            ->orderBy('due_date')
            ->get();

        $this->assertTrue($installments[0]->due_date->isSameDay($firstDue));
        $this->assertTrue($installments[1]->due_date->isSameDay($firstDue->copy()->addMonth()));
        $this->assertTrue($installments[2]->due_date->isSameDay($firstDue->copy()->addMonths(2)));
    }

    public function test_total_amount_validation_fails_when_math_wrong(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/payment-plans', [
            'patient_id'         => $this->patient->id,
            'invoice_id'         => $this->invoice->id,
            'facility_id'        => $this->facility->id,
            'total_amount'       => 999.00,   // wrong — should be 1200
            'down_payment'       => 0.00,
            'installment_amount' => 100.00,
            'installment_count'  => 12,
            'frequency'          => 'monthly',
            'next_due_date'      => Carbon::now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertStatus(422);
    }

    public function test_record_payment_updates_installment_status_to_paid(): void
    {
        $plan = PatientPaymentPlan::factory()->create([
            'patient_id'         => $this->patient->id,
            'invoice_id'         => $this->invoice->id,
            'facility_id'        => $this->facility->id,
            'total_amount'       => 300.00,
            'down_payment'       => 0.00,
            'installment_amount' => 100.00,
            'installment_count'  => 3,
            'paid_count'         => 0,
            'frequency'          => 'monthly',
            'status'             => 'active',
            'next_due_date'      => Carbon::now()->addMonth()->format('Y-m-d'),
        ]);

        $installment = PaymentPlanInstallment::factory()->create([
            'payment_plan_id' => $plan->id,
            'due_date'        => Carbon::now()->addMonth()->format('Y-m-d'),
            'amount'          => 100.00,
            'paid_amount'     => 0.00,
            'status'          => 'pending',
        ]);

        $response = $this->actingAs($this->user)->postJson(
            "/api/payment-plans/{$plan->id}/installments/{$installment->id}/pay",
            ['amount' => 100.00, 'reference' => 'REF-001'],
        );

        $response->assertOk();
        $this->assertEquals('paid', $response->json('data.status'));
        $this->assertDatabaseHas('patient_payment_plans', ['id' => $plan->id, 'paid_count' => 1]);
    }
}
```

---

## Implementation Order

1. Run migrations (004000, 004001)
2. Register `PatientPaymentPlan` and `PaymentPlanInstallment` models
3. Create `RevenueCycleService` and bind in `AppServiceProvider`
4. Create `PaymentPlanService` and bind in `AppServiceProvider`
5. Create `RevenueCycleController` and `PatientPaymentPlanController`
6. Register routes in `routes/api.php`
7. Run `php artisan route:cache`
8. Run feature tests

## Notes

- `RevenueCycleService::getDenialReasons` requires `InsuranceClaim` to have a `decisions()` hasMany relationship pointing to a `ClaimDecision` model (or similar). If that model uses a different name, update the relationship call.
- The `checkForDefaults()` method is designed to be called from a scheduled command (e.g., daily via `Schedule::command(...)->daily()`). Wire it up in `routes/console.php` or a dedicated Artisan command.
- All monetary columns use `decimal(12,2)` to avoid floating-point rounding errors at the database level.
