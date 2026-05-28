# Phase 6: USSD Fallback, Care Plans, Surveys, Medical Record Export

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans

**Goal:** Add USSD/SMS feature-phone support, patient care plans, satisfaction surveys, and PDF/FHIR medical record export.
**Architecture:** New modules and services. PDF via barryvdh/laravel-dompdf. USSD via Africa's Talking API. No existing modules modified.
**Tech Stack:** Laravel 11, PHP 8.3, PostgreSQL, UUID PKs, DomPDF, Africa's Talking USSD API

---

## Task 1: USSD / SMS Fallback for Feature Phones (item 40)

Africa's Talking USSD API integration for patients on feature phones.

**Environment variables required:**
```
AFRICASTALKING_USERNAME=
AFRICASTALKING_API_KEY=
AFRICASTALKING_USSD_CODE=*384#
```

---

### Migration: `database/migrations/2026_05_28_006000_create_ussd_sessions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ussd_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id', 50)->unique();
            $table->string('phone_number', 20);
            $table->string('service_code', 20);
            $table->uuid('patient_id')->nullable()->index();
            $table->string('current_menu', 50)->default('MAIN');
            $table->json('menu_data')->nullable();
            $table->timestamp('initiated_at');
            $table->timestamp('last_active_at');
            $table->timestamps();

            $table->index(['phone_number', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ussd_sessions');
    }
};
```

---

### Model: `app/Models/UssdSession.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UssdSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'session_id',
        'phone_number',
        'service_code',
        'patient_id',
        'current_menu',
        'menu_data',
        'initiated_at',
        'last_active_at',
    ];

    protected $casts = [
        'menu_data'       => 'array',
        'initiated_at'    => 'datetime',
        'last_active_at'  => 'datetime',
    ];

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
```

---

### Service: `app/Services/Ussd/UssdMenuService.php`

```php
<?php

namespace App\Services\Ussd;

use App\Models\Patient;
use App\Models\UssdSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UssdMenuService
{
    private const MENUS = [
        'MAIN' => "Welcome to OpesCare\n1. Book appointment\n2. Lab results\n3. My prescriptions\n4. Emergency contacts",
        'BOOK_APPOINTMENT_FACILITY' => "Enter your facility code:",
        'BOOK_APPOINTMENT_DATE'     => "Enter preferred date (DD/MM/YYYY):",
        'BOOK_APPOINTMENT_CONFIRM'  => "Appointment request sent.\nWe will confirm via SMS.",
        'EMERGENCY'                 => "Emergency: 1800-OPES-CARE\nAmbulance: 117\nEnter 0 to go back.",
    ];

    public function handleRequest(
        string $sessionId,
        string $phone,
        string $input,
        string $serviceCode
    ): string {
        $session = $this->findOrCreateSession($sessionId, $phone, $serviceCode);

        $session->last_active_at = Carbon::now();
        $session->save();

        $response = $this->routeMenu($session, trim($input));

        return $response;
    }

    public function findOrCreateSession(
        string $sessionId,
        string $phone,
        string $serviceCode = ''
    ): UssdSession {
        $session = UssdSession::where('session_id', $sessionId)->first();

        if (! $session) {
            $patient = Patient::where('phone_number', $phone)->first();

            $session = UssdSession::create([
                'session_id'     => $sessionId,
                'phone_number'   => $phone,
                'service_code'   => $serviceCode,
                'patient_id'     => $patient?->id,
                'current_menu'   => 'MAIN',
                'menu_data'      => null,
                'initiated_at'   => Carbon::now(),
                'last_active_at' => Carbon::now(),
            ]);
        }

        return $session;
    }

    public function routeMenu(UssdSession $session, string $input): string
    {
        $menu = $session->current_menu;

        // First interaction or main menu
        if ($menu === 'MAIN' && $input === '') {
            return 'CON ' . self::MENUS['MAIN'];
        }

        if ($menu === 'MAIN') {
            return match ($input) {
                '1' => $this->transition($session, 'BOOK_APPOINTMENT_FACILITY', 'CON ' . self::MENUS['BOOK_APPOINTMENT_FACILITY']),
                '2' => $this->showLabResults($session),
                '3' => $this->showPrescriptions($session),
                '4' => $this->transition($session, 'EMERGENCY', 'CON ' . self::MENUS['EMERGENCY']),
                default => 'CON ' . self::MENUS['MAIN'],
            };
        }

        if ($menu === 'BOOK_APPOINTMENT_FACILITY') {
            $data            = $session->menu_data ?? [];
            $data['facility'] = $input;
            $session->menu_data = $data;
            return $this->transition($session, 'BOOK_APPOINTMENT_DATE', 'CON ' . self::MENUS['BOOK_APPOINTMENT_DATE']);
        }

        if ($menu === 'BOOK_APPOINTMENT_DATE') {
            // Store date, mark complete
            Log::info('USSD appointment request', [
                'phone'    => $session->phone_number,
                'facility' => $session->menu_data['facility'] ?? null,
                'date'     => $input,
            ]);
            $this->endSession($session->session_id);
            return 'END ' . self::MENUS['BOOK_APPOINTMENT_CONFIRM'];
        }

        if ($menu === 'EMERGENCY') {
            if ($input === '0') {
                return $this->transition($session, 'MAIN', 'CON ' . self::MENUS['MAIN']);
            }
            return 'END Thank you. Stay safe.';
        }

        return 'END Session ended. Dial again to restart.';
    }

    public function endSession(string $sessionId): void
    {
        UssdSession::where('session_id', $sessionId)->delete();
    }

    private function transition(UssdSession $session, string $newMenu, string $response): string
    {
        $session->current_menu = $newMenu;
        $session->save();
        return $response;
    }

    private function showLabResults(UssdSession $session): string
    {
        if (! $session->patient_id) {
            return 'END No patient record linked to this number.';
        }

        $results = \App\Models\LabResult::where('patient_id', $session->patient_id)
            ->latest()
            ->take(3)
            ->get(['test_name', 'result_value', 'result_unit', 'collected_at']);

        if ($results->isEmpty()) {
            return 'END No lab results found.';
        }

        $text = "Your recent lab results:\n";
        foreach ($results as $r) {
            $text .= "- {$r->test_name}: {$r->result_value} {$r->result_unit}\n";
        }

        $this->endSession($session->session_id);
        return 'END ' . rtrim($text);
    }

    private function showPrescriptions(UssdSession $session): string
    {
        if (! $session->patient_id) {
            return 'END No patient record linked to this number.';
        }

        $rxs = \App\Models\Prescription::where('patient_id', $session->patient_id)
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get(['drug_name', 'dosage', 'frequency']);

        if ($rxs->isEmpty()) {
            return 'END No active prescriptions.';
        }

        $text = "Active prescriptions:\n";
        foreach ($rxs as $rx) {
            $text .= "- {$rx->drug_name} {$rx->dosage} {$rx->frequency}\n";
        }

        $this->endSession($session->session_id);
        return 'END ' . rtrim($text);
    }
}
```

---

### Controller: `app/Http/Controllers/Api/Ussd/UssdController.php`

```php
<?php

namespace App\Http\Controllers\Api\Ussd;

use App\Http\Controllers\Controller;
use App\Services\Ussd\UssdMenuService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UssdController extends Controller
{
    public function __construct(private readonly UssdMenuService $menuService)
    {
    }

    /**
     * Handle Africa's Talking USSD callback.
     * POST /api/ussd/callback
     */
    public function callback(Request $request): Response
    {
        $sessionId   = $request->input('sessionId', '');
        $serviceCode = $request->input('serviceCode', '');
        $phoneNumber = $request->input('phoneNumber', '');
        $text        = $request->input('text', '');

        // Africa's Talking sends accumulated input; extract the last step only
        $inputs     = explode('*', $text);
        $lastInput  = end($inputs);

        $responseText = $this->menuService->handleRequest(
            $sessionId,
            $phoneNumber,
            $lastInput === false ? '' : $lastInput,
            $serviceCode,
        );

        return response($responseText, 200)->header('Content-Type', 'text/plain');
    }
}
```

**Route (add to `routes/api.php`):**
```php
// USSD — no auth middleware (Africa's Talking webhook)
Route::post('/ussd/callback', [\App\Http\Controllers\Api\Ussd\UssdController::class, 'callback']);
```

---

### Test: `tests/Feature/UssdTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\UssdSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UssdTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_callback_returns_con_with_main_menu(): void
    {
        $response = $this->post('/api/ussd/callback', [
            'sessionId'   => 'AT-SESS-001',
            'serviceCode' => '*384#',
            'phoneNumber' => '+237670000001',
            'text'        => '',
        ]);

        $response->assertStatus(200);
        $body = $response->getContent();
        $this->assertStringStartsWith('CON ', $body);
        $this->assertStringContainsString('Welcome to OpesCare', $body);
    }

    public function test_session_is_created_on_first_request(): void
    {
        $this->post('/api/ussd/callback', [
            'sessionId'   => 'AT-SESS-002',
            'serviceCode' => '*384#',
            'phoneNumber' => '+237670000002',
            'text'        => '',
        ]);

        $this->assertDatabaseHas('ussd_sessions', [
            'session_id'   => 'AT-SESS-002',
            'phone_number' => '+237670000002',
        ]);
    }

    public function test_selecting_emergency_returns_con_response(): void
    {
        // First call to create session at MAIN
        $this->post('/api/ussd/callback', [
            'sessionId'   => 'AT-SESS-003',
            'serviceCode' => '*384#',
            'phoneNumber' => '+237670000003',
            'text'        => '',
        ]);

        // Second call with choice 4
        $response = $this->post('/api/ussd/callback', [
            'sessionId'   => 'AT-SESS-003',
            'serviceCode' => '*384#',
            'phoneNumber' => '+237670000003',
            'text'        => '4',
        ]);

        $body = $response->getContent();
        $this->assertStringStartsWith('CON ', $body);
        $this->assertStringContainsString('Emergency', $body);
    }

    public function test_appointment_flow_returns_end_on_date_entry(): void
    {
        // Step 1: open session
        $this->post('/api/ussd/callback', [
            'sessionId' => 'AT-SESS-004', 'serviceCode' => '*384#',
            'phoneNumber' => '+237670000004', 'text' => '',
        ]);

        // Step 2: choose book appointment
        $this->post('/api/ussd/callback', [
            'sessionId' => 'AT-SESS-004', 'serviceCode' => '*384#',
            'phoneNumber' => '+237670000004', 'text' => '1',
        ]);

        // Step 3: enter facility code
        $this->post('/api/ussd/callback', [
            'sessionId' => 'AT-SESS-004', 'serviceCode' => '*384#',
            'phoneNumber' => '+237670000004', 'text' => '1*FC001',
        ]);

        // Step 4: enter date — expect END
        $response = $this->post('/api/ussd/callback', [
            'sessionId' => 'AT-SESS-004', 'serviceCode' => '*384#',
            'phoneNumber' => '+237670000004', 'text' => '1*FC001*28/05/2026',
        ]);

        $this->assertStringStartsWith('END ', $response->getContent());
    }
}
```

---

## Task 2: Patient-Facing Care Plan (item 41)

---

### Migrations

**`database/migrations/2026_05_28_006001_create_care_plans_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id')->index();
            $table->uuid('facility_id')->index();
            $table->uuid('created_by')->index();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'on_hold', 'cancelled'])->default('active');
            $table->uuid('visit_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('facility_id')->references('id')->on('facilities');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_plans');
    }
};
```

**`database/migrations/2026_05_28_006002_create_care_plan_goals_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_plan_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('care_plan_id')->index();
            $table->text('goal_text');
            $table->date('target_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'achieved', 'abandoned'])->default('pending');
            $table->timestamp('achieved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('care_plan_id')->references('id')->on('care_plans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_plan_goals');
    }
};
```

**`database/migrations/2026_05_28_006003_create_care_plan_interventions_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_plan_interventions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('care_plan_id')->index();
            $table->enum('intervention_type', [
                'medication', 'exercise', 'diet', 'monitoring',
                'referral', 'education', 'other',
            ]);
            $table->text('description');
            $table->string('frequency', 100)->nullable();
            $table->string('responsible_party', 100)->nullable();
            $table->enum('status', ['active', 'completed', 'discontinued'])->default('active');
            $table->timestamps();

            $table->foreign('care_plan_id')->references('id')->on('care_plans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_plan_interventions');
    }
};
```

---

### Models

**`app/Models/CarePlan.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarePlan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'created_by',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'visit_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function goals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CarePlanGoal::class);
    }

    public function interventions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CarePlanIntervention::class);
    }
}
```

**`app/Models/CarePlanGoal.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarePlanGoal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'care_plan_id',
        'goal_text',
        'target_date',
        'status',
        'achieved_at',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'achieved_at' => 'datetime',
    ];

    public function carePlan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CarePlan::class);
    }
}
```

**`app/Models/CarePlanIntervention.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarePlanIntervention extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'care_plan_id',
        'intervention_type',
        'description',
        'frequency',
        'responsible_party',
        'status',
    ];

    public function carePlan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CarePlan::class);
    }
}
```

---

### Service: `app/Services/Clinical/CarePlanService.php`

```php
<?php

namespace App\Services\Clinical;

use App\Models\CarePlan;
use App\Models\CarePlanGoal;
use App\Models\CarePlanIntervention;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CarePlanService
{
    public function create(array $data): CarePlan
    {
        return CarePlan::create($data);
    }

    public function addGoal(string $carePlanId, array $data): CarePlanGoal
    {
        return CarePlanGoal::create(array_merge($data, ['care_plan_id' => $carePlanId]));
    }

    public function updateGoalStatus(string $goalId, string $status): CarePlanGoal
    {
        $goal = CarePlanGoal::findOrFail($goalId);
        $goal->status = $status;

        if ($status === 'achieved') {
            $goal->achieved_at = Carbon::now();
        }

        $goal->save();
        return $goal;
    }

    public function addIntervention(string $carePlanId, array $data): CarePlanIntervention
    {
        return CarePlanIntervention::create(array_merge($data, ['care_plan_id' => $carePlanId]));
    }

    public function getActivePlansForPatient(string $patientId): Collection
    {
        return CarePlan::where('patient_id', $patientId)
            ->where('status', 'active')
            ->with(['goals', 'interventions'])
            ->latest()
            ->get();
    }

    public function getSummary(string $carePlanId): array
    {
        $plan = CarePlan::with(['goals', 'interventions'])->findOrFail($carePlanId);

        $totalGoals    = $plan->goals->count();
        $achievedGoals = $plan->goals->where('status', 'achieved')->count();
        $progressPct   = $totalGoals > 0
            ? (int) round(($achievedGoals / $totalGoals) * 100)
            : 0;

        return [
            'plan'          => $plan,
            'goals'         => $plan->goals,
            'interventions' => $plan->interventions,
            'progress_pct'  => $progressPct,
        ];
    }
}
```

---

### Controllers

**`app/Http/Controllers/Api/V1/CarePlanController.php`**

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Clinical\CarePlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarePlanController extends Controller
{
    public function __construct(private readonly CarePlanService $service)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'  => 'required|uuid|exists:patients,id',
            'facility_id' => 'required|uuid|exists:facilities,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'nullable|in:active,completed,on_hold,cancelled',
            'visit_id'    => 'nullable|uuid',
        ]);

        $validated['created_by'] = $request->user()->id;

        $plan = $this->service->create($validated);

        return response()->json(['data' => $plan], 201);
    }

    public function show(string $id): JsonResponse
    {
        $summary = $this->service->getSummary($id);
        return response()->json(['data' => $summary]);
    }

    public function storeGoal(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'goal_text'   => 'required|string',
            'target_date' => 'nullable|date',
            'notes'       => 'nullable|string',
        ]);

        $goal = $this->service->addGoal($id, $validated);
        return response()->json(['data' => $goal], 201);
    }

    public function updateGoal(Request $request, string $id, string $goalId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,achieved,abandoned',
        ]);

        $goal = $this->service->updateGoalStatus($goalId, $validated['status']);
        return response()->json(['data' => $goal]);
    }

    public function storeIntervention(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'intervention_type' => 'required|in:medication,exercise,diet,monitoring,referral,education,other',
            'description'       => 'required|string',
            'frequency'         => 'nullable|string|max:100',
            'responsible_party' => 'nullable|string|max:100',
        ]);

        $intervention = $this->service->addIntervention($id, $validated);
        return response()->json(['data' => $intervention], 201);
    }
}
```

**`app/Http/Controllers/Api/Mobile/MobileCarePlanController.php`**

```php
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Clinical\CarePlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileCarePlanController extends Controller
{
    public function __construct(private readonly CarePlanService $service)
    {
    }

    /** GET /api/mobile/care-plans — patient's own active plans */
    public function index(Request $request): JsonResponse
    {
        $patientId = $request->user()->patient?->id;

        if (! $patientId) {
            return response()->json(['message' => 'No patient record linked to account.'], 404);
        }

        $plans = $this->service->getActivePlansForPatient($patientId);
        return response()->json(['data' => $plans]);
    }

    /** GET /api/mobile/care-plans/{id} */
    public function show(string $id): JsonResponse
    {
        $summary = $this->service->getSummary($id);
        return response()->json(['data' => $summary]);
    }
}
```

**Routes (add to `routes/api.php`):**
```php
// Clinician care-plan routes
Route::middleware(['auth:sanctum', 'role:clinician,admin'])->group(function () {
    Route::post('/care-plans', [\App\Http\Controllers\Api\V1\CarePlanController::class, 'store']);
    Route::get('/care-plans/{id}', [\App\Http\Controllers\Api\V1\CarePlanController::class, 'show']);
    Route::post('/care-plans/{id}/goals', [\App\Http\Controllers\Api\V1\CarePlanController::class, 'storeGoal']);
    Route::patch('/care-plans/{id}/goals/{goalId}', [\App\Http\Controllers\Api\V1\CarePlanController::class, 'updateGoal']);
    Route::post('/care-plans/{id}/interventions', [\App\Http\Controllers\Api\V1\CarePlanController::class, 'storeIntervention']);
});

// Patient mobile routes (read-only)
Route::middleware(['auth:sanctum', 'role:patient'])->prefix('mobile')->group(function () {
    Route::get('/care-plans', [\App\Http\Controllers\Api\Mobile\MobileCarePlanController::class, 'index']);
    Route::get('/care-plans/{id}', [\App\Http\Controllers\Api\Mobile\MobileCarePlanController::class, 'show']);
});
```

---

### Test: `tests/Feature/CarePlanTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\CarePlan;
use App\Models\CarePlanGoal;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use App\Services\Clinical\CarePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarePlanTest extends TestCase
{
    use RefreshDatabase;

    private CarePlanService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CarePlanService::class);
    }

    public function test_can_create_care_plan(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();
        $user     = User::factory()->create();

        $plan = $this->service->create([
            'patient_id'  => $patient->id,
            'facility_id' => $facility->id,
            'created_by'  => $user->id,
            'title'       => 'Hypertension Management Plan',
            'start_date'  => now()->toDateString(),
        ]);

        $this->assertInstanceOf(CarePlan::class, $plan);
        $this->assertEquals('active', $plan->status);
    }

    public function test_can_add_goal_and_update_status(): void
    {
        $plan = CarePlan::factory()->create();

        $goal = $this->service->addGoal($plan->id, [
            'goal_text'   => 'Reduce systolic BP below 130 mmHg',
            'target_date' => now()->addMonths(3)->toDateString(),
        ]);

        $this->assertEquals('pending', $goal->status);

        $updated = $this->service->updateGoalStatus($goal->id, 'achieved');
        $this->assertEquals('achieved', $updated->status);
        $this->assertNotNull($updated->achieved_at);
    }

    public function test_progress_pct_calculated_correctly(): void
    {
        $plan = CarePlan::factory()->create();

        $goal1 = $this->service->addGoal($plan->id, ['goal_text' => 'Goal 1']);
        $goal2 = $this->service->addGoal($plan->id, ['goal_text' => 'Goal 2']);
        $goal3 = $this->service->addGoal($plan->id, ['goal_text' => 'Goal 3']);

        // Achieve 2 of 3
        $this->service->updateGoalStatus($goal1->id, 'achieved');
        $this->service->updateGoalStatus($goal2->id, 'achieved');

        $summary = $this->service->getSummary($plan->id);
        $this->assertEquals(67, $summary['progress_pct']);
    }
}
```

---

## Task 3: Patient Satisfaction Surveys (item 43)

---

### Migrations

**`database/migrations/2026_05_28_006004_create_patient_surveys_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_surveys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id')->index();
            $table->uuid('facility_id')->index();
            $table->uuid('visit_id')->nullable();
            $table->enum('template_key', ['post_visit', 'discharge', 'telemedicine', 'general']);
            $table->enum('status', ['pending', 'sent', 'completed', 'expired'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('facility_id')->references('id')->on('facilities');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_surveys');
    }
};
```

**`database/migrations/2026_05_28_006005_create_survey_responses_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_survey_id')->index();
            $table->string('question_key', 50);
            $table->string('question_text', 255);
            $table->enum('response_type', ['rating_5', 'rating_10', 'yes_no', 'text']);
            $table->integer('numeric_response')->nullable();
            $table->text('text_response')->nullable();
            $table->timestamps();

            $table->foreign('patient_survey_id')
                  ->references('id')->on('patient_surveys')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
```

---

### Models

**`app/Models/PatientSurvey.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientSurvey extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'visit_id',
        'template_key',
        'status',
        'sent_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'completed_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function responses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'patient_survey_id');
    }
}
```

**`app/Models/SurveyResponse.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_survey_id',
        'question_key',
        'question_text',
        'response_type',
        'numeric_response',
        'text_response',
    ];

    public function survey(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PatientSurvey::class, 'patient_survey_id');
    }
}
```

---

### Service: `app/Services/Patient/SurveyService.php`

```php
<?php

namespace App\Services\Patient;

use App\Models\PatientSurvey;
use App\Models\SurveyResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SurveyService
{
    private const TEMPLATES = [
        'post_visit' => [
            ['key' => 'overall_experience',     'text' => 'How would you rate your overall experience?',          'type' => 'rating_5'],
            ['key' => 'wait_time',               'text' => 'How would you rate the wait time?',                    'type' => 'rating_5'],
            ['key' => 'provider_communication',  'text' => 'How well did the provider communicate with you?',      'type' => 'rating_5'],
            ['key' => 'would_recommend',         'text' => 'Would you recommend this facility to others?',         'type' => 'yes_no'],
            ['key' => 'comments',                'text' => 'Any additional comments?',                             'type' => 'text'],
        ],
        'discharge' => [
            ['key' => 'care_quality',            'text' => 'How would you rate the quality of care received?',     'type' => 'rating_5'],
            ['key' => 'discharge_instructions',  'text' => 'Were your discharge instructions clearly explained?',  'type' => 'yes_no'],
            ['key' => 'follow_up_clarity',       'text' => 'How clear were your follow-up instructions?',          'type' => 'rating_5'],
            ['key' => 'comments',                'text' => 'Any additional comments?',                             'type' => 'text'],
        ],
        'telemedicine' => [
            ['key' => 'connection_quality',      'text' => 'How was the video/audio connection quality?',          'type' => 'rating_5'],
            ['key' => 'overall_experience',      'text' => 'How would you rate your telemedicine experience?',     'type' => 'rating_5'],
            ['key' => 'would_recommend',         'text' => 'Would you use telemedicine again?',                    'type' => 'yes_no'],
            ['key' => 'comments',                'text' => 'Any additional comments?',                             'type' => 'text'],
        ],
        'general' => [
            ['key' => 'overall_experience',      'text' => 'How would you rate your overall experience?',          'type' => 'rating_5'],
            ['key' => 'would_recommend',         'text' => 'Would you recommend us to others?',                    'type' => 'yes_no'],
            ['key' => 'comments',                'text' => 'Any additional comments?',                             'type' => 'text'],
        ],
    ];

    public function createAndSend(
        string $patientId,
        string $facilityId,
        string $templateKey,
        ?string $visitId = null
    ): PatientSurvey {
        if (! isset(self::TEMPLATES[$templateKey])) {
            throw new \InvalidArgumentException("Unknown survey template: {$templateKey}");
        }

        $survey = PatientSurvey::create([
            'patient_id'   => $patientId,
            'facility_id'  => $facilityId,
            'visit_id'     => $visitId,
            'template_key' => $templateKey,
            'status'       => 'sent',
            'sent_at'      => Carbon::now(),
            'expires_at'   => Carbon::now()->addDays(7),
        ]);

        return $survey;
    }

    public function submitResponse(string $surveyId, array $responses): PatientSurvey
    {
        $survey = PatientSurvey::findOrFail($surveyId);

        if ($survey->status === 'expired') {
            throw new \RuntimeException('Survey has expired.');
        }

        if ($survey->status === 'completed') {
            throw new \RuntimeException('Survey already completed.');
        }

        $template = self::TEMPLATES[$survey->template_key] ?? [];

        DB::transaction(function () use ($survey, $responses, $template) {
            foreach ($template as $question) {
                if (! isset($responses[$question['key']])) {
                    continue;
                }

                $value = $responses[$question['key']];

                SurveyResponse::create([
                    'patient_survey_id' => $survey->id,
                    'question_key'      => $question['key'],
                    'question_text'     => $question['text'],
                    'response_type'     => $question['type'],
                    'numeric_response'  => in_array($question['type'], ['rating_5', 'rating_10'])
                        ? (int) $value
                        : ($question['type'] === 'yes_no' ? ($value ? 1 : 0) : null),
                    'text_response'     => $question['type'] === 'text' ? (string) $value : null,
                ]);
            }

            $survey->update([
                'status'       => 'completed',
                'completed_at' => Carbon::now(),
            ]);
        });

        return $survey->fresh();
    }

    public function getSatisfactionScore(string $facilityId, Carbon $from, Carbon $to): array
    {
        $responses = SurveyResponse::whereHas('survey', function ($q) use ($facilityId, $from, $to) {
            $q->where('facility_id', $facilityId)
              ->where('status', 'completed')
              ->whereBetween('completed_at', [$from, $to]);
        })->whereIn('response_type', ['rating_5', 'rating_10'])
          ->whereNotNull('numeric_response')
          ->get(['question_key', 'numeric_response']);

        return $responses
            ->groupBy('question_key')
            ->map(fn ($group) => round($group->avg('numeric_response'), 2))
            ->toArray();
    }

    public function expirePendingSurveys(): int
    {
        return PatientSurvey::whereIn('status', ['pending', 'sent'])
            ->where('expires_at', '<', Carbon::now())
            ->update(['status' => 'expired']);
    }

    public function getTemplate(string $templateKey): array
    {
        return self::TEMPLATES[$templateKey] ?? [];
    }
}
```

---

### Controllers

**`app/Http/Controllers/Api/Mobile/MobileSurveyController.php`**

```php
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PatientSurvey;
use App\Services\Patient\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileSurveyController extends Controller
{
    public function __construct(private readonly SurveyService $service)
    {
    }

    /** GET /api/mobile/surveys — pending surveys for the authenticated patient */
    public function index(Request $request): JsonResponse
    {
        $patientId = $request->user()->patient?->id;
        $surveys   = PatientSurvey::where('patient_id', $patientId)
            ->where('status', 'sent')
            ->with('responses')
            ->get();

        return response()->json(['data' => $surveys]);
    }

    /** GET /api/mobile/surveys/{id} — survey with questions */
    public function show(string $id): JsonResponse
    {
        $survey   = PatientSurvey::findOrFail($id);
        $template = $this->service->getTemplate($survey->template_key);

        return response()->json([
            'data'     => $survey,
            'template' => $template,
        ]);
    }

    /** POST /api/mobile/surveys/{id}/submit */
    public function submit(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'responses' => 'required|array',
        ]);

        $survey = $this->service->submitResponse($id, $validated['responses']);
        return response()->json(['data' => $survey]);
    }
}
```

**`app/Http/Controllers/Api/V1/Reports/SurveyReportController.php`**

```php
<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Services\Patient\SurveyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyReportController extends Controller
{
    public function __construct(private readonly SurveyService $service)
    {
    }

    /** GET /api/v1/reports/surveys/satisfaction?facility_id=&from=&to= */
    public function satisfaction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => 'required|uuid|exists:facilities,id',
            'from'        => 'required|date',
            'to'          => 'required|date|after_or_equal:from',
        ]);

        $scores = $this->service->getSatisfactionScore(
            $validated['facility_id'],
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return response()->json(['data' => $scores]);
    }
}
```

---

### Test: `tests/Feature/SurveyTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Patient;
use App\Services\Patient\SurveyService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyTest extends TestCase
{
    use RefreshDatabase;

    private SurveyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SurveyService::class);
    }

    public function test_can_create_and_send_survey(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $survey = $this->service->createAndSend($patient->id, $facility->id, 'post_visit');

        $this->assertDatabaseHas('patient_surveys', [
            'id'           => $survey->id,
            'status'       => 'sent',
            'template_key' => 'post_visit',
        ]);
    }

    public function test_can_submit_responses(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $survey = $this->service->createAndSend($patient->id, $facility->id, 'post_visit');

        $completed = $this->service->submitResponse($survey->id, [
            'overall_experience'    => 5,
            'wait_time'             => 4,
            'provider_communication'=> 5,
            'would_recommend'       => true,
            'comments'              => 'Excellent service!',
        ]);

        $this->assertEquals('completed', $completed->status);
        $this->assertCount(5, $completed->responses);
    }

    public function test_average_score_calculated_correctly(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $survey1 = $this->service->createAndSend($patient->id, $facility->id, 'post_visit');
        $survey2 = $this->service->createAndSend($patient->id, $facility->id, 'post_visit');

        $this->service->submitResponse($survey1->id, [
            'overall_experience' => 4,
            'wait_time'          => 4,
            'provider_communication' => 4,
        ]);

        $this->service->submitResponse($survey2->id, [
            'overall_experience' => 2,
            'wait_time'          => 2,
            'provider_communication' => 2,
        ]);

        $scores = $this->service->getSatisfactionScore(
            $facility->id,
            Carbon::now()->subDay(),
            Carbon::now()->addDay(),
        );

        $this->assertEquals(3.0, $scores['overall_experience']);
        $this->assertEquals(3.0, $scores['wait_time']);
    }

    public function test_expire_pending_surveys(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $survey = $this->service->createAndSend($patient->id, $facility->id, 'general');

        // Force expiry
        $survey->update(['expires_at' => Carbon::now()->subDay()]);

        $count = $this->service->expirePendingSurveys();

        $this->assertGreaterThanOrEqual(1, $count);
        $this->assertDatabaseHas('patient_surveys', ['id' => $survey->id, 'status' => 'expired']);
    }
}
```

---

## Task 4: Medical Record Download — PDF + FHIR Bundle (item 44)

**Installation:** Add to `composer.json` then run `composer require barryvdh/laravel-dompdf:^2.2`.

```json
"require": {
    "barryvdh/laravel-dompdf": "^2.2"
}
```

After install, publish config (optional):
```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

---

### Service: `app/Services/Patient/MedicalRecordExportService.php`

```php
<?php

namespace App\Services\Patient;

use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MedicalRecordExportService
{
    private string $diskName = 'local';
    private string $exportDir = 'exports/medical-records';

    /**
     * Generate a PDF medical record.
     *
     * Options: include_vitals, include_diagnoses, include_medications,
     *          include_labs, include_immunizations (all default true).
     *
     * Returns absolute path to the generated PDF file.
     */
    public function generatePdf(string $patientId, array $options = []): string
    {
        $defaults = [
            'include_vitals'        => true,
            'include_diagnoses'     => true,
            'include_medications'   => true,
            'include_labs'          => true,
            'include_immunizations' => true,
        ];
        $options = array_merge($defaults, $options);

        $patient = Patient::with([
            'allergies',
            'diagnoses'     => fn ($q) => $q->where('status', 'active'),
            'prescriptions' => fn ($q) => $q->where('status', 'active'),
            'vitals'        => fn ($q) => $q->latest()->limit(3),
            'labResults'    => fn ($q) => $q->latest()->limit(10),
            'immunizations',
        ])->findOrFail($patientId);

        $pdf = Pdf::loadView('exports.medical-record', compact('patient', 'options'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'  => 'sans-serif',
                'isHtml5ParserEnabled' => true,
            ]);

        $filename  = sprintf('medical-record-%s-%s.pdf', $patientId, Carbon::now()->format('YmdHis'));
        $relativePath = $this->exportDir . '/' . $filename;

        Storage::disk($this->diskName)->put($relativePath, $pdf->output());

        return Storage::disk($this->diskName)->path($relativePath);
    }

    /**
     * Generate a FHIR R4 Bundle for the patient.
     * Uses existing FHIR mappers (assumed available in app/Services/Fhir/).
     */
    public function generateFhirBundle(string $patientId): array
    {
        $patient = Patient::with([
            'allergies',
            'diagnoses',
            'prescriptions',
            'vitals',
            'labResults',
            'immunizations',
        ])->findOrFail($patientId);

        $entries = [];

        // Patient resource
        $entries[] = [
            'resource' => [
                'resourceType' => 'Patient',
                'id'           => $patient->id,
                'name'         => [[
                    'use'    => 'official',
                    'family' => $patient->last_name,
                    'given'  => [$patient->first_name],
                ]],
                'gender'       => $patient->gender ?? 'unknown',
                'birthDate'    => $patient->date_of_birth?->toDateString(),
                'identifier'   => [[
                    'system' => 'urn:opescare:patient-id',
                    'value'  => $patient->health_id ?? $patient->id,
                ]],
            ],
        ];

        // AllergyIntolerance resources
        foreach ($patient->allergies ?? [] as $allergy) {
            $entries[] = [
                'resource' => [
                    'resourceType'  => 'AllergyIntolerance',
                    'id'            => $allergy->id,
                    'patient'       => ['reference' => "Patient/{$patient->id}"],
                    'code'          => ['text' => $allergy->allergen ?? $allergy->name ?? 'Unknown'],
                    'clinicalStatus'=> ['coding' => [['code' => 'active']]],
                ],
            ];
        }

        // Condition resources (diagnoses)
        foreach ($patient->diagnoses ?? [] as $diagnosis) {
            $entries[] = [
                'resource' => [
                    'resourceType'   => 'Condition',
                    'id'             => $diagnosis->id,
                    'subject'        => ['reference' => "Patient/{$patient->id}"],
                    'code'           => ['text' => $diagnosis->diagnosis_name ?? $diagnosis->description ?? 'Unknown'],
                    'clinicalStatus' => ['coding' => [['code' => $diagnosis->status ?? 'active']]],
                    'onsetDateTime'  => $diagnosis->diagnosed_at?->toIso8601String(),
                ],
            ];
        }

        // MedicationRequest resources
        foreach ($patient->prescriptions ?? [] as $rx) {
            $entries[] = [
                'resource' => [
                    'resourceType'  => 'MedicationRequest',
                    'id'            => $rx->id,
                    'subject'       => ['reference' => "Patient/{$patient->id}"],
                    'status'        => $rx->status ?? 'active',
                    'intent'        => 'order',
                    'medicationCodeableConcept' => ['text' => $rx->drug_name ?? 'Unknown'],
                    'dosageInstruction' => [[
                        'text' => trim("{$rx->dosage} {$rx->frequency}"),
                    ]],
                ],
            ];
        }

        return [
            'resourceType' => 'Bundle',
            'id'           => \Illuminate\Support\Str::uuid()->toString(),
            'type'         => 'collection',
            'timestamp'    => Carbon::now()->toIso8601String(),
            'total'        => count($entries),
            'entry'        => $entries,
        ];
    }

    /**
     * Delete export files older than N hours. Returns count of files deleted.
     */
    public function cleanupExports(int $hoursOld = 24): int
    {
        $files   = Storage::disk($this->diskName)->files($this->exportDir);
        $cutoff  = Carbon::now()->subHours($hoursOld)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = Storage::disk($this->diskName)->lastModified($file);
            if ($lastModified < $cutoff) {
                Storage::disk($this->diskName)->delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}
```

---

### Blade Template: `resources/views/exports/medical-record.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Record — {{ $patient->first_name }} {{ $patient->last_name }}</title>
    <style>
        body  { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
        h1    { font-size: 18px; color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 6px; }
        h2    { font-size: 13px; color: #2563eb; margin-top: 18px; margin-bottom: 4px; border-left: 4px solid #2563eb; padding-left: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th    { background: #e8f0fe; text-align: left; padding: 5px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
        td    { padding: 4px 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f9fafb; }
        .label { color: #6b7280; font-weight: 600; width: 140px; }
        .badge-active   { color: #065f46; background: #d1fae5; padding: 1px 6px; border-radius: 999px; }
        .badge-inactive { color: #7f1d1d; background: #fee2e2; padding: 1px 6px; border-radius: 999px; }
        .footer { margin-top: 30px; font-size: 9px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>

<h1>OpesCare — Medical Record</h1>

<h2>Patient Demographics</h2>
<table>
    <tr><td class="label">Full Name</td><td>{{ $patient->first_name }} {{ $patient->last_name }}</td></tr>
    <tr><td class="label">Health ID</td><td>{{ $patient->health_id ?? $patient->id }}</td></tr>
    <tr><td class="label">Date of Birth</td><td>{{ $patient->date_of_birth?->format('d M Y') ?? '—' }}</td></tr>
    <tr><td class="label">Gender</td><td>{{ ucfirst($patient->gender ?? '—') }}</td></tr>
    <tr><td class="label">Phone</td><td>{{ $patient->phone_number ?? '—' }}</td></tr>
    <tr><td class="label">Blood Group</td><td>{{ $patient->blood_group ?? '—' }}</td></tr>
    <tr><td class="label">Record Generated</td><td>{{ now()->format('d M Y H:i') }}</td></tr>
</table>

@if($patient->allergies && $patient->allergies->isNotEmpty())
<h2>Allergies</h2>
<table>
    <tr><th>Allergen</th><th>Reaction</th><th>Severity</th></tr>
    @foreach($patient->allergies as $allergy)
    <tr>
        <td>{{ $allergy->allergen ?? $allergy->name ?? '—' }}</td>
        <td>{{ $allergy->reaction ?? '—' }}</td>
        <td>{{ ucfirst($allergy->severity ?? '—') }}</td>
    </tr>
    @endforeach
</table>
@endif

@if($options['include_diagnoses'] && $patient->diagnoses && $patient->diagnoses->isNotEmpty())
<h2>Active Diagnoses</h2>
<table>
    <tr><th>Diagnosis</th><th>ICD-10</th><th>Date</th><th>Status</th></tr>
    @foreach($patient->diagnoses as $dx)
    <tr>
        <td>{{ $dx->diagnosis_name ?? $dx->description ?? '—' }}</td>
        <td>{{ $dx->icd10_code ?? '—' }}</td>
        <td>{{ $dx->diagnosed_at?->format('d M Y') ?? '—' }}</td>
        <td><span class="badge-active">{{ ucfirst($dx->status ?? 'active') }}</span></td>
    </tr>
    @endforeach
</table>
@endif

@if($options['include_medications'] && $patient->prescriptions && $patient->prescriptions->isNotEmpty())
<h2>Current Medications</h2>
<table>
    <tr><th>Drug</th><th>Dosage</th><th>Frequency</th><th>Route</th></tr>
    @foreach($patient->prescriptions as $rx)
    <tr>
        <td>{{ $rx->drug_name ?? '—' }}</td>
        <td>{{ $rx->dosage ?? '—' }}</td>
        <td>{{ $rx->frequency ?? '—' }}</td>
        <td>{{ $rx->route ?? '—' }}</td>
    </tr>
    @endforeach
</table>
@endif

@if($options['include_vitals'] && $patient->vitals && $patient->vitals->isNotEmpty())
<h2>Recent Vitals (Last 3 Records)</h2>
<table>
    <tr><th>Date</th><th>BP</th><th>Pulse</th><th>Temp (°C)</th><th>SpO2 (%)</th><th>Weight (kg)</th></tr>
    @foreach($patient->vitals as $v)
    <tr>
        <td>{{ $v->recorded_at?->format('d M Y') ?? '—' }}</td>
        <td>{{ isset($v->systolic_bp, $v->diastolic_bp) ? "{$v->systolic_bp}/{$v->diastolic_bp}" : '—' }}</td>
        <td>{{ $v->pulse ?? '—' }}</td>
        <td>{{ $v->temperature ?? '—' }}</td>
        <td>{{ $v->spo2 ?? '—' }}</td>
        <td>{{ $v->weight ?? '—' }}</td>
    </tr>
    @endforeach
</table>
@endif

@if($options['include_labs'] && $patient->labResults && $patient->labResults->isNotEmpty())
<h2>Recent Lab Results (Last 10)</h2>
<table>
    <tr><th>Test</th><th>Result</th><th>Unit</th><th>Reference</th><th>Date</th><th>Flag</th></tr>
    @foreach($patient->labResults as $lr)
    <tr>
        <td>{{ $lr->test_name ?? '—' }}</td>
        <td>{{ $lr->result_value ?? '—' }}</td>
        <td>{{ $lr->result_unit ?? '—' }}</td>
        <td>{{ $lr->reference_range ?? '—' }}</td>
        <td>{{ $lr->collected_at?->format('d M Y') ?? '—' }}</td>
        <td>{{ $lr->abnormal_flag ?? '' }}</td>
    </tr>
    @endforeach
</table>
@endif

@if($options['include_immunizations'] && $patient->immunizations && $patient->immunizations->isNotEmpty())
<h2>Immunization History</h2>
<table>
    <tr><th>Vaccine</th><th>Date Given</th><th>Dose</th><th>Lot Number</th></tr>
    @foreach($patient->immunizations as $imm)
    <tr>
        <td>{{ $imm->vaccine_name ?? '—' }}</td>
        <td>{{ $imm->administered_at?->format('d M Y') ?? '—' }}</td>
        <td>{{ $imm->dose_number ?? '—' }}</td>
        <td>{{ $imm->lot_number ?? '—' }}</td>
    </tr>
    @endforeach
</table>
@endif

<div class="footer">
    This document is confidential and intended solely for the named patient.<br>
    Generated by OpesCare &mdash; {{ now()->format('d M Y H:i:s') }}
</div>

</body>
</html>
```

---

### Controller: `app/Http/Controllers/Api/Mobile/MedicalRecordExportController.php`

```php
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Patient\MedicalRecordExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicalRecordExportController extends Controller
{
    public function __construct(private readonly MedicalRecordExportService $exportService)
    {
    }

    /**
     * POST /api/mobile/medical-records/export/pdf
     * Returns the absolute server path (or a pre-signed URL if using S3).
     */
    public function exportPdf(Request $request): JsonResponse
    {
        $patientId = $request->user()->patient?->id;

        if (! $patientId) {
            return response()->json(['message' => 'No patient record linked to account.'], 404);
        }

        $validated = $request->validate([
            'include_vitals'        => 'nullable|boolean',
            'include_diagnoses'     => 'nullable|boolean',
            'include_medications'   => 'nullable|boolean',
            'include_labs'          => 'nullable|boolean',
            'include_immunizations' => 'nullable|boolean',
        ]);

        $path = $this->exportService->generatePdf($patientId, $validated);

        return response()->json([
            'message'   => 'PDF generated successfully.',
            'file_path' => $path,
            'filename'  => basename($path),
        ]);
    }

    /**
     * POST /api/mobile/medical-records/export/fhir
     * Returns a FHIR R4 Bundle JSON.
     */
    public function exportFhir(Request $request): JsonResponse
    {
        $patientId = $request->user()->patient?->id;

        if (! $patientId) {
            return response()->json(['message' => 'No patient record linked to account.'], 404);
        }

        $bundle = $this->exportService->generateFhirBundle($patientId);

        return response()->json($bundle);
    }
}
```

**Routes (add to `routes/api.php`):**
```php
Route::middleware(['auth:sanctum', 'role:patient'])->prefix('mobile')->group(function () {
    Route::post('/medical-records/export/pdf',  [\App\Http\Controllers\Api\Mobile\MedicalRecordExportController::class, 'exportPdf']);
    Route::post('/medical-records/export/fhir', [\App\Http\Controllers\Api\Mobile\MedicalRecordExportController::class, 'exportFhir']);
});
```

---

### Test: `tests/Feature/MedicalRecordExportTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Services\Patient\MedicalRecordExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MedicalRecordExportTest extends TestCase
{
    use RefreshDatabase;

    private MedicalRecordExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MedicalRecordExportService::class);
        Storage::fake('local');
    }

    public function test_pdf_is_generated_and_file_exists(): void
    {
        $patient = Patient::factory()->create();

        $path = $this->service->generatePdf($patient->id, [
            'include_vitals'        => false,
            'include_diagnoses'     => true,
            'include_medications'   => true,
            'include_labs'          => false,
            'include_immunizations' => false,
        ]);

        $this->assertNotEmpty($path);
        $this->assertStringEndsWith('.pdf', $path);
        $this->assertFileExists($path);
    }

    public function test_fhir_bundle_has_correct_resource_type(): void
    {
        $patient = Patient::factory()->create([
            'first_name'    => 'John',
            'last_name'     => 'Doe',
            'date_of_birth' => '1990-01-15',
        ]);

        $bundle = $this->service->generateFhirBundle($patient->id);

        $this->assertEquals('Bundle', $bundle['resourceType']);
        $this->assertEquals('collection', $bundle['type']);
        $this->assertArrayHasKey('entry', $bundle);
        $this->assertGreaterThanOrEqual(1, count($bundle['entry']));
    }

    public function test_fhir_bundle_contains_patient_resource(): void
    {
        $patient = Patient::factory()->create([
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
        ]);

        $bundle = $this->service->generateFhirBundle($patient->id);

        $patientResource = collect($bundle['entry'])
            ->firstWhere('resource.resourceType', 'Patient');

        $this->assertNotNull($patientResource);
        $this->assertEquals('Smith', $patientResource['resource']['name'][0]['family']);
    }

    public function test_cleanup_deletes_old_export_files(): void
    {
        $patient = Patient::factory()->create();

        // Generate two PDFs
        $this->service->generatePdf($patient->id);
        $this->service->generatePdf($patient->id);

        // Manually age them by passing 0 hours threshold — deletes all
        $deleted = $this->service->cleanupExports(0);

        $this->assertGreaterThanOrEqual(2, $deleted);
    }
}
```

---

## Implementation Order

1. Run migrations: `006000` → `006001` → `006002` → `006003` → `006004` → `006005`
2. Install DomPDF: `composer require barryvdh/laravel-dompdf:^2.2`
3. Add env vars for Africa's Talking
4. Register routes in `routes/api.php`
5. Run feature tests: `php artisan test tests/Feature/UssdTest.php tests/Feature/CarePlanTest.php tests/Feature/SurveyTest.php tests/Feature/MedicalRecordExportTest.php`

## Verification Checklist

- [ ] USSD callback returns `CON` (continue) or `END` (terminate) prefix — required by Africa's Talking protocol
- [ ] UssdSession created on first POST to `/api/ussd/callback`
- [ ] CarePlan `progress_pct` = achieved goals / total goals × 100
- [ ] Survey `submitResponse` throws if already completed or expired
- [ ] PDF file exists at returned path after `generatePdf`
- [ ] FHIR bundle `resourceType` = `Bundle`
- [ ] `cleanupExports` removes files older than threshold
