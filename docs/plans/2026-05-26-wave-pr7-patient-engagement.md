# Wave PR-7: Patient Engagement

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add 6 patient engagement features — mobile push framework, USSD/SMS fallback, patient-facing care plans, secure messaging, satisfaction surveys, and medical record PDF download.

**Architecture:** Push notifications extend the existing NotificationService. USSD creates a new stateless session handler. CarePlan is a new module. Messaging extends the existing Messaging module with patient-facing endpoints. Surveys are new. PDF export uses barryvdh/laravel-dompdf (already common in Laravel stacks). No existing patient portal Blade/CSS files are changed.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, barryvdh/laravel-dompdf, PHPUnit

---

## File Map

```
database/migrations/
  2026_05_26_700001_create_care_plans_table.php
  2026_05_26_700002_create_care_plan_goals_table.php
  2026_05_26_700003_create_patient_surveys_table.php
  2026_05_26_700004_create_survey_responses_table.php
  2026_05_26_700005_create_ussd_sessions_table.php
  2026_05_26_700006_add_push_token_to_patients.php
app/Models/
  CarePlan.php
  CarePlanGoal.php
  PatientSurvey.php
  SurveyResponse.php
  UssdSession.php
app/Services/
  PatientEngagement/CarePlanService.php
  PatientEngagement/PatientSurveyService.php
  PatientEngagement/UssdSessionService.php
  PatientEngagement/MedicalRecordPdfService.php
tests/Feature/PatientEngagement/
  CarePlanTest.php
  PatientSurveyTest.php
  UssdSessionTest.php
  MedicalRecordPdfTest.php
  PatientPushTokenTest.php
```

---

### Task 1: Patient-Facing Care Plans

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/PatientEngagement/CarePlanTest.php
namespace Tests\Feature\PatientEngagement;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\CarePlan;
use App\Models\CarePlanGoal;
use App\Services\PatientEngagement\CarePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarePlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_care_plan(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new CarePlanService();
        $plan    = $service->createPlan(
            patientId:   $patient->id,
            providerId:  $provider->id,
            facilityId:  $facility->id,
            title:       'Diabetes Management Plan',
            description: 'Lifestyle and medication management for T2DM',
            startDate:   '2026-07-01',
        );

        $this->assertInstanceOf(CarePlan::class, $plan);
        $this->assertEquals('active', $plan->status);
    }

    public function test_can_add_goals_to_care_plan(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new CarePlanService();
        $plan    = $service->createPlan($patient->id, $provider->id, $facility->id, 'Diabetes Plan', '', '2026-07-01');

        $goal = $service->addGoal(
            planId:      $plan->id,
            title:       'Reduce HbA1c below 7%',
            targetDate:  '2026-12-31',
            category:    'clinical',
        );

        $this->assertInstanceOf(CarePlanGoal::class, $goal);
        $this->assertEquals('pending', $goal->status);
    }

    public function test_goal_can_be_marked_achieved(): void
    {
        $patient  = Patient::factory()->create();
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new CarePlanService();
        $plan    = $service->createPlan($patient->id, $provider->id, $facility->id, 'Plan', '', '2026-07-01');
        $goal    = $service->addGoal($plan->id, 'Exercise 30 min/day', '2026-08-01', 'lifestyle');

        $goal->update(['status' => 'achieved', 'achieved_at' => now()]);
        $this->assertEquals('achieved', $goal->fresh()->status);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/PatientEngagement/CarePlanTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_700001_create_care_plans_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('care_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active','completed','cancelled'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('care_plans'); }
};
```

```php
<?php
// database/migrations/2026_05_26_700002_create_care_plan_goals_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('care_plan_goals', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('care_plan_id')->constrained('care_plans')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['clinical','lifestyle','medication','monitoring','other'])->default('clinical');
            $table->date('target_date')->nullable();
            $table->enum('status', ['pending','in_progress','achieved','abandoned'])->default('pending');
            $table->timestamp('achieved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('care_plan_goals'); }
};
```

- [ ] **Step 4: Create models**

```php
<?php
// app/Models/CarePlan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CarePlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','provider_id','facility_id','title','description',
        'start_date','end_date','status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function patient()  { return $this->belongsTo(Patient::class); }
    public function provider() { return $this->belongsTo(User::class, 'provider_id'); }
    public function facility() { return $this->belongsTo(Facility::class); }
    public function goals()    { return $this->hasMany(CarePlanGoal::class); }
}
```

```php
<?php
// app/Models/CarePlanGoal.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CarePlanGoal extends Model
{
    use HasUuids;

    protected $fillable = [
        'care_plan_id','title','description','category',
        'target_date','status','achieved_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'achieved_at' => 'datetime',
    ];

    public function carePlan() { return $this->belongsTo(CarePlan::class); }
}
```

- [ ] **Step 5: Create CarePlanService**

```php
<?php
// app/Services/PatientEngagement/CarePlanService.php
namespace App\Services\PatientEngagement;

use App\Models\CarePlan;
use App\Models\CarePlanGoal;

class CarePlanService
{
    public function createPlan(
        string  $patientId,
        string  $providerId,
        string  $facilityId,
        string  $title,
        string  $description,
        string  $startDate,
        ?string $endDate = null,
    ): CarePlan {
        return CarePlan::create([
            'patient_id'  => $patientId,
            'provider_id' => $providerId,
            'facility_id' => $facilityId,
            'title'       => $title,
            'description' => $description,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'status'      => 'active',
        ]);
    }

    public function addGoal(
        string  $planId,
        string  $title,
        ?string $targetDate = null,
        string  $category   = 'clinical',
        ?string $description = null,
    ): CarePlanGoal {
        return CarePlanGoal::create([
            'care_plan_id' => $planId,
            'title'        => $title,
            'description'  => $description,
            'category'     => $category,
            'target_date'  => $targetDate,
            'status'       => 'pending',
        ]);
    }
}
```

- [ ] **Step 6: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/PatientEngagement/CarePlanTest.php
```

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_05_26_700001_* database/migrations/2026_05_26_700002_* \
  app/Models/CarePlan.php app/Models/CarePlanGoal.php \
  app/Services/PatientEngagement/CarePlanService.php \
  tests/Feature/PatientEngagement/CarePlanTest.php
git commit -m "feat(engagement): patient-facing care plans with goal tracking"
```

---

### Task 2: Patient Satisfaction Surveys

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/PatientEngagement/PatientSurveyTest.php
namespace Tests\Feature\PatientEngagement;

use App\Models\Patient;
use App\Models\Facility;
use App\Models\PatientSurvey;
use App\Models\SurveyResponse;
use App\Services\PatientEngagement\PatientSurveyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientSurveyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_survey_template(): void
    {
        $facility = Facility::factory()->create();

        $survey = PatientSurvey::create([
            'facility_id' => $facility->id,
            'title'       => 'Post-Visit Satisfaction Survey',
            'trigger'     => 'post_appointment',
            'questions'   => [
                ['id' => 'q1', 'text' => 'How satisfied were you overall?', 'type' => 'scale_1_5'],
                ['id' => 'q2', 'text' => 'Would you recommend us?', 'type' => 'yes_no'],
                ['id' => 'q3', 'text' => 'Any additional feedback?', 'type' => 'text'],
            ],
            'is_active'   => true,
        ]);

        $this->assertEquals('post_appointment', $survey->trigger);
        $this->assertCount(3, $survey->questions);
    }

    public function test_patient_can_submit_survey_response(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $survey = PatientSurvey::create([
            'facility_id' => $facility->id,
            'title'       => 'Satisfaction Survey',
            'trigger'     => 'post_appointment',
            'questions'   => [['id' => 'q1', 'text' => 'How satisfied?', 'type' => 'scale_1_5']],
            'is_active'   => true,
        ]);

        $response = SurveyResponse::create([
            'patient_survey_id' => $survey->id,
            'patient_id'        => $patient->id,
            'facility_id'       => $facility->id,
            'answers'           => [['question_id' => 'q1', 'answer' => 4]],
            'overall_score'     => 4,
            'submitted_at'      => now(),
        ]);

        $this->assertEquals(4, $response->overall_score);
        $this->assertNotNull($response->submitted_at);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/PatientEngagement/PatientSurveyTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_700003_create_patient_surveys_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_surveys', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('title');
            $table->enum('trigger', ['post_appointment','post_discharge','scheduled','manual'])->default('manual');
            $table->json('questions');   // [{id, text, type: scale_1_5|yes_no|text|multiple_choice}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('patient_surveys'); }
};
```

```php
<?php
// database/migrations/2026_05_26_700004_create_survey_responses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_survey_id')
                ->constrained('patient_surveys')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->json('answers');   // [{question_id, answer}]
            $table->unsignedTinyInteger('overall_score')->nullable();  // 1-5
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('survey_responses'); }
};
```

- [ ] **Step 4: Create models**

```php
<?php
// app/Models/PatientSurvey.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PatientSurvey extends Model
{
    use HasUuids;

    protected $fillable = [
        'facility_id','title','trigger','questions','is_active',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
    ];

    public function facility()  { return $this->belongsTo(Facility::class); }
    public function responses() { return $this->hasMany(SurveyResponse::class); }
}
```

```php
<?php
// app/Models/SurveyResponse.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_survey_id','patient_id','facility_id',
        'answers','overall_score','submitted_at',
    ];

    protected $casts = [
        'answers'      => 'array',
        'submitted_at' => 'datetime',
    ];

    public function survey()   { return $this->belongsTo(PatientSurvey::class, 'patient_survey_id'); }
    public function patient()  { return $this->belongsTo(Patient::class); }
    public function facility() { return $this->belongsTo(Facility::class); }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/PatientEngagement/PatientSurveyTest.php
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_700003_* database/migrations/2026_05_26_700004_* \
  app/Models/PatientSurvey.php app/Models/SurveyResponse.php \
  tests/Feature/PatientEngagement/PatientSurveyTest.php
git commit -m "feat(engagement): patient satisfaction surveys with structured response capture"
```

---

### Task 3: Mobile Push Token + USSD Session

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/PatientEngagement/PatientPushTokenTest.php
namespace Tests\Feature\PatientEngagement;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPushTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_register_push_token(): void
    {
        $patient = Patient::factory()->create();
        $patient->update([
            'push_token'    => 'fcm:ExampleFcmToken12345',
            'push_platform' => 'android',
        ]);

        $this->assertEquals('fcm:ExampleFcmToken12345', $patient->fresh()->push_token);
        $this->assertEquals('android', $patient->fresh()->push_platform);
    }
}
```

```php
<?php
// tests/Feature/PatientEngagement/UssdSessionTest.php
namespace Tests\Feature\PatientEngagement;

use App\Models\UssdSession;
use App\Services\PatientEngagement\UssdSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UssdSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ussd_session_created_on_new_request(): void
    {
        $service = new UssdSessionService();
        $response = $service->handle(
            sessionId:   'USSD-SESSION-001',
            phoneNumber: '+237670000001',
            text:        '',
            serviceCode: '*999#',
        );

        $this->assertStringContainsString('OpesCare', $response['message']);
        $this->assertEquals('CON', $response['type']); // CON = continue session
    }

    public function test_ussd_menu_option_1_shows_appointments(): void
    {
        $service = new UssdSessionService();
        $response = $service->handle(
            sessionId:   'USSD-SESSION-002',
            phoneNumber: '+237670000001',
            text:        '1',
            serviceCode: '*999#',
        );

        $this->assertStringContainsString('appointment', strtolower($response['message']));
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/PatientEngagement/PatientPushTokenTest.php tests/Feature/PatientEngagement/UssdSessionTest.php
```

- [ ] **Step 3: Create push_token migration**

```php
<?php
// database/migrations/2026_05_26_700006_add_push_token_to_patients.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'push_token')) {
                $table->string('push_token')->nullable()->after('cnamgs_number');
                $table->enum('push_platform', ['android','ios','web'])->nullable()->after('push_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumnIfExists(['push_token', 'push_platform']);
        });
    }
};
```

- [ ] **Step 4: Create USSD session migration**

```php
<?php
// database/migrations/2026_05_26_700005_create_ussd_sessions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ussd_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('session_id')->unique();
            $table->string('phone_number', 25);
            $table->string('service_code', 20);
            $table->text('current_text')->nullable();
            $table->unsignedTinyInteger('menu_depth')->default(0);
            $table->json('session_data')->nullable();
            $table->timestamp('last_activity_at');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('ussd_sessions'); }
};
```

- [ ] **Step 5: Create UssdSession model**

```php
<?php
// app/Models/UssdSession.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UssdSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'session_id','phone_number','service_code',
        'current_text','menu_depth','session_data','last_activity_at',
    ];

    protected $casts = [
        'session_data'     => 'array',
        'last_activity_at' => 'datetime',
    ];
}
```

- [ ] **Step 6: Create UssdSessionService**

```php
<?php
// app/Services/PatientEngagement/UssdSessionService.php
namespace App\Services\PatientEngagement;

use App\Models\UssdSession;

class UssdSessionService
{
    private array $mainMenu = [
        "Welcome to OpesCare\n1. My Appointments\n2. My Results\n3. Find a Clinic\n4. Emergency Contacts\n0. Exit",
    ];

    public function handle(
        string $sessionId,
        string $phoneNumber,
        string $text,
        string $serviceCode,
    ): array {
        $session = UssdSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'phone_number'     => $phoneNumber,
                'service_code'     => $serviceCode,
                'current_text'     => '',
                'menu_depth'       => 0,
                'last_activity_at' => now(),
            ]
        );

        $session->update(['current_text' => $text, 'last_activity_at' => now()]);

        $inputs = $text === '' ? [] : explode('*', $text);
        $level  = count($inputs);

        if ($level === 0) {
            return $this->respond('CON', $this->mainMenu[0]);
        }

        return match ($inputs[0]) {
            '1' => $this->respond('CON', "My Appointments\n1. View next appointment\n2. Book appointment\n0. Back"),
            '2' => $this->respond('CON', "My Results\nPlease visit our patient portal or call your facility to access lab results."),
            '3' => $this->respond('CON', "Find a Clinic\nDial *999*3# or visit opescare.cm/caremap"),
            '4' => $this->respond('END', "Emergency: 1510 (SAMU)\nPoison Control: +237 222 222 000\nOpesCare: +237 XXX XXX XXX"),
            '0' => $this->respond('END', "Thank you for using OpesCare. Stay healthy!"),
            default => $this->respond('END', "Invalid option. Please try again."),
        };
    }

    private function respond(string $type, string $message): array
    {
        return ['type' => $type, 'message' => $message];
    }
}
```

- [ ] **Step 7: Add push_token to Patient `$fillable`**

In `app/Models/Patient.php`, add `'push_token'` and `'push_platform'` to `$fillable`.

- [ ] **Step 8: Run tests**

```bash
php artisan migrate && \
php artisan test tests/Feature/PatientEngagement/PatientPushTokenTest.php && \
php artisan test tests/Feature/PatientEngagement/UssdSessionTest.php
```

- [ ] **Step 9: Run full suite**

```bash
php artisan test
```

- [ ] **Step 10: Commit**

```bash
git add database/migrations/2026_05_26_700005_* database/migrations/2026_05_26_700006_* \
  app/Models/UssdSession.php app/Models/Patient.php \
  app/Services/PatientEngagement/UssdSessionService.php \
  tests/Feature/PatientEngagement/PatientPushTokenTest.php \
  tests/Feature/PatientEngagement/UssdSessionTest.php
git commit -m "feat(engagement): patient mobile push token registration + USSD SMS fallback menu"
```

---

### Task 4: Medical Record PDF Download

- [ ] **Step 1: Install dompdf (if not present)**

```bash
composer require barryvdh/laravel-dompdf
```

- [ ] **Step 2: Write failing tests**

```php
<?php
// tests/Feature/PatientEngagement/MedicalRecordPdfTest.php
namespace Tests\Feature\PatientEngagement;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Services\PatientEngagement\MedicalRecordPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRecordPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_summary_generated_for_patient(): void
    {
        $patient  = Patient::factory()->create(['first_name' => 'Chidi', 'last_name' => 'Nkemdirim']);
        $provider = User::factory()->create();
        $facility = Facility::factory()->create();

        $service = new MedicalRecordPdfService();
        $pdf     = $service->generateSummary($patient->id);

        $this->assertIsString($pdf);
        $this->assertGreaterThan(100, strlen($pdf)); // non-empty PDF binary
    }
}
```

- [ ] **Step 3: Run to confirm fail**

```bash
php artisan test tests/Feature/PatientEngagement/MedicalRecordPdfTest.php
```

- [ ] **Step 4: Create MedicalRecordPdfService**

```php
<?php
// app/Services/PatientEngagement/MedicalRecordPdfService.php
namespace App\Services\PatientEngagement;

use App\Models\Patient;
use App\Models\VitalSign;
use App\Models\LabResult;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;

class MedicalRecordPdfService
{
    /**
     * Generate a PDF summary for a patient and return the raw PDF string.
     * The controller should stream this with: response($pdf)->header('Content-Type', 'application/pdf')
     */
    public function generateSummary(string $patientId): string
    {
        $patient      = Patient::findOrFail($patientId);
        $vitals       = VitalSign::where('patient_id', $patientId)->latest()->take(5)->get();
        $labResults   = LabResult::where('patient_id', $patientId)->latest()->take(10)->get();
        $prescriptions = Prescription::where('patient_id', $patientId)->latest()->take(10)->get();

        $pdf = Pdf::loadView('pdf.medical-record-summary', compact(
            'patient', 'vitals', 'labResults', 'prescriptions'
        ));

        return $pdf->output();
    }
}
```

- [ ] **Step 5: Create PDF Blade view**

Create `resources/views/pdf/medical-record-summary.blade.php`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 8px; }
        h2 { color: #374151; font-size: 14px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 10px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .footer { margin-top: 40px; font-size: 10px; color: #9ca3af; text-align: center; }
        .confidential { background: #fef3c7; padding: 6px; border-left: 4px solid #f59e0b; }
    </style>
</head>
<body>
    <h1>OpesCare — Medical Record Summary</h1>

    <p class="confidential">CONFIDENTIAL — For authorised use only</p>

    <h2>Patient Information</h2>
    <table>
        <tr><th>Name</th><td>{{ $patient->first_name }} {{ $patient->last_name }}</td></tr>
        <tr><th>Date of Birth</th><td>{{ $patient->date_of_birth ?? 'N/A' }}</td></tr>
        <tr><th>Patient ID</th><td>{{ $patient->id }}</td></tr>
        <tr><th>CNAMGS No.</th><td>{{ $patient->cnamgs_number ?? 'Not registered' }}</td></tr>
        <tr><th>Generated</th><td>{{ now()->format('d M Y H:i') }}</td></tr>
    </table>

    @if($vitals->isNotEmpty())
    <h2>Recent Vital Signs</h2>
    <table>
        <tr><th>Date</th><th>BP</th><th>Pulse</th><th>SpO2</th><th>Temp (°C)</th></tr>
        @foreach($vitals as $v)
        <tr>
            <td>{{ $v->created_at->format('d M Y') }}</td>
            <td>{{ $v->blood_pressure_systolic ?? '-' }}/{{ $v->blood_pressure_diastolic ?? '-' }}</td>
            <td>{{ $v->pulse ?? '-' }}</td>
            <td>{{ $v->oxygen_saturation ?? '-' }}%</td>
            <td>{{ $v->temperature ?? '-' }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    @if($labResults->isNotEmpty())
    <h2>Recent Lab Results</h2>
    <table>
        <tr><th>Test</th><th>Result</th><th>Unit</th><th>Date</th></tr>
        @foreach($labResults as $r)
        <tr>
            <td>{{ $r->test_name }}</td>
            <td>{{ $r->result_value }}</td>
            <td>{{ $r->result_unit }}</td>
            <td>{{ $r->created_at->format('d M Y') }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="footer">
        OpesCare Health Information Platform &bull; Generated {{ now()->toIso8601String() }}
    </div>
</body>
</html>
```

- [ ] **Step 6: Run tests**

```bash
php artisan test tests/Feature/PatientEngagement/MedicalRecordPdfTest.php
```

- [ ] **Step 7: Run full suite**

```bash
php artisan test
```

- [ ] **Step 8: Commit**

```bash
git add app/Services/PatientEngagement/MedicalRecordPdfService.php \
  resources/views/pdf/medical-record-summary.blade.php \
  tests/Feature/PatientEngagement/MedicalRecordPdfTest.php
git commit -m "feat(engagement): medical record PDF summary export with dompdf"
```
