# Patient Portal Production Readiness Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminate all demo/mock data from the patient portal, fix broken QR code generation, wire the missing user↔patient link, and add all missing portal pages so every tab works end-to-end against the real database.

**Architecture:** The patient portal is a server-rendered Laravel 13 Blade app. Auth is Laravel's built-in `auth` guard. The `User` model has no `patient_id` column today — so every portal session falls through to the first patient in the DB (a demo record). All fixes must go through proper migrations + model relationships + controller cleanup. No frontend framework; JavaScript is inline Blade.

**Tech Stack:** Laravel 13, PHP 8.3.30, PostgreSQL, Blade, qrcode.js (client), chillerlan/php-qrcode (server), Lucide icons, portal layout (`layouts.portal`).

**PHP binary:** `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`
**Project root:** `C:\laragon\www\opescare\apps\api-laravel`

---

## File Map

| File | Action | Responsibility |
|------|--------|----------------|
| `database/migrations/2026_05_24_000001_add_patient_id_to_users.php` | Create | Add `patient_id` FK to `users` table |
| `app/Models/User.php` | Modify | Add `patient()` relationship + `is_demo` cast |
| `app/Http/Controllers/MedicalId/PatientPortalController.php` | Modify | Fix `resolvePatient()`, fix QR response, add 5 new methods |
| `routes/web.php` | Modify | Add 7 missing patient portal routes |
| `resources/views/portals/patient/index.blade.php` | Modify | Fix QR JS (data.url→data.raw_token, countdown timer) |
| `resources/views/portals/patient/labs.blade.php` | Create | Lab results list view |
| `resources/views/portals/patient/prescriptions.blade.php` | Create | Prescriptions list view |
| `resources/views/portals/patient/consent.blade.php` | Create | Consent requests management view |
| `resources/views/portals/patient/documents.blade.php` | Create | Official documents list view |
| `resources/views/portals/patient/profile.blade.php` | Create | Patient profile + privacy settings edit view |
| `resources/views/portals/patient/index.blade.php` | Modify | Update quick-actions grid (add new nav items) |

---

## Task 1: Add user↔patient database link

**Files:**
- Create: `database/migrations/2026_05_24_000001_add_patient_id_to_users.php`
- Modify: `app/Models/User.php`

**Context:** The `users` table has no `patient_id` column. `Patient` has no `user_id` column. The cleanest production approach is to put `patient_id` on the `users` table so one user account maps to one patient record. This is a nullable nullable FK so existing staff/admin users are unaffected.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Portal/PatientPortalLinkTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_patient_relationship(): void
    {
        $patient = Patient::factory()->create(['health_id' => 'OC-TEST-001', 'is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);

        $this->assertNotNull($user->patient);
        $this->assertEquals($patient->id, $user->patient->id);
    }

    public function test_user_without_patient_returns_null(): void
    {
        $user = User::factory()->create(['patient_id' => null]);
        $this->assertNull($user->patient);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PatientPortalLinkTest.php --no-coverage
```

Expected: FAIL — column `patient_id` doesn't exist on `users`

- [ ] **Step 3: Create migration**

```php
// database/migrations/2026_05_24_000001_add_patient_id_to_users.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('patient_id')->nullable()->after('primary_facility_id');
            $table->boolean('is_demo')->default(false)->after('status');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn(['patient_id', 'is_demo']);
        });
    }
};
```

- [ ] **Step 4: Add `patient()` relationship + `is_demo` cast to User model**

Open `app/Models/User.php`. Add `patient_id` and `is_demo` to `$fillable`, add `is_demo` cast, add relationship:

```php
// In $fillable array, add:
'patient_id',
'is_demo',

// In casts() method, add:
'is_demo' => 'boolean',

// Add relationship method:
public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(Patient::class);
}
```

- [ ] **Step 5: Add patient_id to User factory**

In `database/factories/UserFactory.php`, add to `definition()`:
```php
'patient_id' => null,
'is_demo'    => false,
```

- [ ] **Step 6: Run migration and test**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PatientPortalLinkTest.php --no-coverage
```

Expected: PASS (2 tests, 2 assertions)

- [ ] **Step 7: Run full suite to confirm no regressions**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

Expected: all tests pass

- [ ] **Step 8: Commit**

```
git add database/migrations/2026_05_24_000001_add_patient_id_to_users.php app/Models/User.php database/factories/UserFactory.php tests/Feature/Portal/PatientPortalLinkTest.php
git commit -m "feat(portal): add patient_id + is_demo to users table; link User->Patient"
```

---

## Task 2: Fix `resolvePatient()` — eliminate demo fallback

**Files:**
- Modify: `app/Http/Controllers/MedicalId/PatientPortalController.php`

**Context:** The current fallback `Patient::whereNotNull('health_id')->first()` silently serves a demo patient to any user without a linked record. Production behaviour: if the authenticated user has no linked patient, show the "no profile found" empty state instead of serving random data.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Portal/PatientPortalDemoRemovalTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalDemoRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_no_profile_when_user_has_no_patient_link(): void
    {
        // A demo patient exists in the DB
        Patient::factory()->create(['health_id' => 'OC-DEMO-001', 'is_demo' => true]);

        // A real user with NO linked patient logs in
        $user = User::factory()->create(['patient_id' => null]);
        $this->actingAs($user);

        $response = $this->get(route('portals.patient'));
        $response->assertStatus(200);
        $response->assertViewHas('patient', null);
        // Must NOT expose the demo patient
        $response->assertDontSee('OC-DEMO-001');
    }

    public function test_dashboard_shows_real_patient_when_linked(): void
    {
        $patient = Patient::factory()->create(['health_id' => 'OC-REAL-001', 'is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);
        $this->actingAs($user);

        $response = $this->get(route('portals.patient'));
        $response->assertStatus(200);
        $response->assertViewHas('patient', fn($p) => $p && $p->id === $patient->id);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PatientPortalDemoRemovalTest.php --no-coverage
```

Expected: FAIL — first test sees demo patient health ID

- [ ] **Step 3: Replace `resolvePatient()` in the controller**

In `app/Http/Controllers/MedicalId/PatientPortalController.php`, replace the entire `resolvePatient()` method:

```php
private function resolvePatient(): ?Patient
{
    $user = Auth::user();
    if (!$user) {
        return null;
    }
    return $user->patient ?? null;
}
```

- [ ] **Step 4: Run test to verify it passes**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PatientPortalDemoRemovalTest.php --no-coverage
```

Expected: PASS (2 tests, 4 assertions)

- [ ] **Step 5: Run full suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

Expected: all tests pass

- [ ] **Step 6: Commit**

```
git add app/Http/Controllers/MedicalId/PatientPortalController.php tests/Feature/Portal/PatientPortalDemoRemovalTest.php
git commit -m "fix(portal): remove demo patient fallback from resolvePatient()"
```

---

## Task 3: Fix QR code generation (JS bugs) and fix the `generateTemporaryQr` response

**Files:**
- Modify: `app/Http/Controllers/MedicalId/PatientPortalController.php`
- Modify: `resources/views/portals/patient/index.blade.php`

**Context:** Two bugs:
1. `generateTemporaryQr()` returns `{ raw_token, expires_in }` but the JS on the page checks `data.url` — so the QR never renders.
2. `startCountdown(3600)` is hardcoded to 3600 seconds even though the token expires in 60 minutes (3600 seconds is actually correct for the temp QR — but `expires_in` should come from the API response). The static QR uses the correct `route('verify.qr', ['token' => $qrToken])` but the placeholder icon is shown because the `$qrToken` is undefined in some states.

Fix: Controller returns a `url` key (the full verify URL). JS uses `data.url` and `data.expires_in` for the countdown.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Portal/PatientQrTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_temp_qr_returns_url_and_expires_in(): void
    {
        $patient = Patient::factory()->create(['health_id' => 'OC-QR-001', 'is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);
        $this->actingAs($user);

        $response = $this->postJson(route('portals.patient.qr'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['url', 'expires_in']);
        $this->assertStringContainsString('/verify/qr/', $response->json('url'));
        $this->assertEquals(3600, $response->json('expires_in'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PatientQrTest.php --no-coverage
```

Expected: FAIL — response has `raw_token` not `url`

- [ ] **Step 3: Fix `generateTemporaryQr()` in the controller**

In `app/Http/Controllers/MedicalId/PatientPortalController.php`, replace the return statement in `generateTemporaryQr()`:

```php
// Replace:
return response()->json([
    'raw_token'  => $tokenData['raw_token'],
    'expires_in' => 60,
]);

// With:
return response()->json([
    'url'        => route('verify.qr', ['token' => $tokenData['raw_token']]),
    'expires_in' => 3600,
]);
```

Also update the `generateToken()` call to use 60 minutes (3600 seconds):

```php
// Replace:
$tokenData = $qrService->generateToken($patient->id, 'temporary_consent_qr', 60);

// With:
$tokenData = $qrService->generateToken($patient->id, 'temporary_consent_qr', 60); // 60 minutes
```

- [ ] **Step 4: Fix the JavaScript in `resources/views/portals/patient/index.blade.php`**

Find line ~227 and replace the QR rendering block. Change:

```javascript
// BEFORE — wrong key:
if (data.url && typeof QRCode !== 'undefined') {
    QRCode.toDataURL(data.url,
        { width: 128, margin: 1, color: { dark: '#0F172A', light: '#FFFFFF' } },
        function (err, imgUrl) {
            if (!err && imgUrl) {
                var qrEl = document.getElementById('temp-qr');
                qrEl.innerHTML = '<img src="' + imgUrl + '" alt="Temporary QR Code"'
                    + ' style="width:8rem;height:8rem;border-radius:4px;" />';
                var container = document.getElementById('temp-qr-container');
                container.style.display = 'flex';
                startCountdown(3600);
            }
        }
    );
}
```

To:

```javascript
// AFTER — correct key + dynamic countdown:
if (data.url && typeof QRCode !== 'undefined') {
    QRCode.toDataURL(data.url,
        { width: 128, margin: 1, color: { dark: '#0F172A', light: '#FFFFFF' } },
        function (err, imgUrl) {
            if (!err && imgUrl) {
                var qrEl = document.getElementById('temp-qr');
                qrEl.innerHTML = '<img src="' + imgUrl + '" alt="Temporary QR Code"'
                    + ' style="width:8rem;height:8rem;border-radius:4px;" />';
                var container = document.getElementById('temp-qr-container');
                container.style.display = 'flex';
                startCountdown(data.expires_in || 3600);
            }
        }
    );
}
```

- [ ] **Step 5: Run test**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PatientQrTest.php --no-coverage
```

Expected: PASS

- [ ] **Step 6: Run full suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

Expected: all tests pass

- [ ] **Step 7: Commit**

```
git add app/Http/Controllers/MedicalId/PatientPortalController.php resources/views/portals/patient/index.blade.php tests/Feature/Portal/PatientQrTest.php
git commit -m "fix(portal): fix QR JS to use data.url, dynamic countdown from expires_in"
```

---

## Task 4: Add Lab Results page

**Files:**
- Modify: `app/Http/Controllers/MedicalId/PatientPortalController.php` — add `labResults()` method
- Modify: `routes/web.php` — add `/portals/patient/labs` route
- Create: `resources/views/portals/patient/labs.blade.php`

**Context:** `LabResult` model has `patient_id`, `parameter_name`, `value`, `unit`, `reference_range`, `flag`, `notes`, `resulted_at`. `LabOrder` has the order date and is related via `lab_order_id`. The mobile API at `GET /mobile/labs` returns the same data.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Portal/LabResultsPageTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\LabResult;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabResultsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_labs_page_requires_auth(): void
    {
        $this->get(route('portals.patient.labs'))->assertRedirect(route('login'));
    }

    public function test_labs_page_shows_real_results_for_linked_patient(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);
        LabResult::factory()->count(3)->create(['patient_id' => $patient->id]);

        $this->actingAs($user)->get(route('portals.patient.labs'))
            ->assertStatus(200)
            ->assertViewHas('labs', fn($l) => $l->count() === 3);
    }

    public function test_labs_page_shows_empty_state_for_unlinked_user(): void
    {
        $user = User::factory()->create(['patient_id' => null]);
        $this->actingAs($user)->get(route('portals.patient.labs'))
            ->assertStatus(200)
            ->assertViewHas('patient', null);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/LabResultsPageTest.php --no-coverage
```

Expected: FAIL — route `portals.patient.labs` does not exist

- [ ] **Step 3: Add route to `routes/web.php`**

Find the patient portal route group (around line 142) and add:

```php
Route::get('/portals/patient/labs', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'labResults'])->name('portals.patient.labs');
```

- [ ] **Step 4: Add `labResults()` method to controller**

In `app/Http/Controllers/MedicalId/PatientPortalController.php`, add after `appointments()`:

```php
public function labResults(Request $request)
{
    $patient = $this->resolvePatient();

    $labs = $patient
        ? \App\Models\LabResult::where('patient_id', $patient->id)
            ->with('labOrder')
            ->orderByDesc('resulted_at')
            ->limit(100)
            ->get()
        : collect([]);

    if ($patient) {
        $this->ctx->auditPatientAccess(
            actionType:   'patient_labs_view',
            resourceType: 'LabResult',
            resourceId:   null,
            patientId:    $patient->id,
        );
    }

    return view('portals.patient.labs', compact('patient', 'labs'));
}
```

- [ ] **Step 5: Create `resources/views/portals/patient/labs.blade.php`**

```blade
@extends('layouts.portal')

@section('title', 'Lab Results — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Lab Results')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Lab Results</h1>
        <p class="page-subtitle">View your laboratory test results from all facilities.</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@elseif($labs->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="flask-conical"></i></div>
        <h3>No Lab Results</h3>
        <p>You have no recorded lab results at this time.</p>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="flask-conical"></i> Lab Results</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--p-surface-2);font-size:0.8125rem;color:var(--p-text-muted);">
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Test</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Result</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Reference</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Flag</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($labs as $lab)
                <tr style="border-top:1px solid var(--p-border);font-size:0.875rem;">
                    <td style="padding:var(--p-space-3) var(--p-space-4);font-weight:600;">{{ $lab->parameter_name }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);">{{ $lab->value }} {{ $lab->unit }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ $lab->reference_range ?? '—' }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);">
                        @if($lab->isAbnormal())
                            <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:var(--p-danger-light,#FEE2E2);color:var(--p-danger,#DC2626);">
                                {{ $lab->flagLabel() }}
                            </span>
                        @else
                            <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:var(--p-success-light,#D1FAE5);color:var(--p-success,#059669);">
                                Normal
                            </span>
                        @endif
                    </td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">
                        {{ $lab->resulted_at?->format('d M Y') ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
```

- [ ] **Step 6: Add LabResult factory if missing**

Check if `LabResultFactory.php` exists:
```
ls database/factories/LabResultFactory.php
```

If missing, create `database/factories/LabResultFactory.php`:
```php
<?php
namespace Database\Factories;

use App\Models\LabResult;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabResultFactory extends Factory
{
    protected $model = LabResult::class;

    public function definition(): array
    {
        return [
            'lab_order_id'    => null,
            'patient_id'      => null,
            'parameter_name'  => $this->faker->randomElement(['Haemoglobin', 'WBC', 'Platelet Count', 'Glucose', 'Creatinine']),
            'value'           => (string) $this->faker->randomFloat(2, 1, 200),
            'unit'            => $this->faker->randomElement(['g/dL', '×10³/µL', 'mg/dL', 'µmol/L']),
            'reference_range' => '4.0–11.0',
            'flag'            => $this->faker->randomElement([null, null, 'H', 'L']),
            'notes'           => null,
            'resulted_at'     => now()->subDays(rand(1, 30)),
        ];
    }
}
```

Also add `use HasFactory;` to `LabResult` model if not present.

- [ ] **Step 7: Run test**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/LabResultsPageTest.php --no-coverage
```

Expected: PASS (3 tests)

- [ ] **Step 8: Run full suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

- [ ] **Step 9: Commit**

```
git add routes/web.php app/Http/Controllers/MedicalId/PatientPortalController.php resources/views/portals/patient/labs.blade.php database/factories/LabResultFactory.php tests/Feature/Portal/LabResultsPageTest.php
git commit -m "feat(portal): add lab results page"
```

---

## Task 5: Add Prescriptions page

**Files:**
- Modify: `app/Http/Controllers/MedicalId/PatientPortalController.php` — add `prescriptions()` method
- Modify: `routes/web.php` — add `/portals/patient/prescriptions` route
- Create: `resources/views/portals/patient/prescriptions.blade.php`

**Context:** `Prescription` has `patient_id`, `status`, `prescribed_at`, `dispensed_at`, `expires_at`. Eager load `items` (PrescriptionItem: `drug_name`, `dose`, `frequency`, `route`, `duration_days`, `status`). Also eager load `facility`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Portal/PrescriptionsPageTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescriptions_page_requires_auth(): void
    {
        $this->get(route('portals.patient.prescriptions'))->assertRedirect(route('login'));
    }

    public function test_prescriptions_page_shows_patient_prescriptions(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);
        Prescription::factory()->count(2)->create(['patient_id' => $patient->id]);

        $this->actingAs($user)->get(route('portals.patient.prescriptions'))
            ->assertStatus(200)
            ->assertViewHas('prescriptions', fn($p) => $p->count() === 2);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PrescriptionsPageTest.php --no-coverage
```

Expected: FAIL — route `portals.patient.prescriptions` does not exist

- [ ] **Step 3: Add route**

In `routes/web.php`, add:
```php
Route::get('/portals/patient/prescriptions', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'prescriptions'])->name('portals.patient.prescriptions');
```

- [ ] **Step 4: Add `prescriptions()` method to controller**

```php
public function prescriptions(Request $request)
{
    $patient = $this->resolvePatient();

    $prescriptions = $patient
        ? \App\Models\Prescription::where('patient_id', $patient->id)
            ->with(['items', 'facility'])
            ->orderByDesc('prescribed_at')
            ->limit(50)
            ->get()
        : collect([]);

    if ($patient) {
        $this->ctx->auditPatientAccess(
            actionType:   'patient_prescriptions_view',
            resourceType: 'Prescription',
            resourceId:   null,
            patientId:    $patient->id,
        );
    }

    return view('portals.patient.prescriptions', compact('patient', 'prescriptions'));
}
```

- [ ] **Step 5: Create `resources/views/portals/patient/prescriptions.blade.php`**

```blade
@extends('layouts.portal')

@section('title', 'Prescriptions — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Prescriptions')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Prescriptions</h1>
        <p class="page-subtitle">All medications prescribed to you across your care history.</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@elseif($prescriptions->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="pill"></i></div>
        <h3>No Prescriptions</h3>
        <p>You have no recorded prescriptions at this time.</p>
    </div>
</div>
@else
@foreach($prescriptions as $rx)
<div class="panel mb-4" style="margin-bottom:var(--p-space-4);">
    <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2 class="panel-title" style="font-size:0.9375rem;">
                <i data-lucide="pill"></i>
                Prescription — {{ $rx->prescribed_at?->format('d M Y') ?? 'Unknown date' }}
            </h2>
            @if($rx->facility)
            <p style="font-size:0.8125rem;color:var(--p-text-muted);margin-top:2px;">{{ $rx->facility->name }}</p>
            @endif
        </div>
        <span style="padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;
            background:{{ $rx->statusColor() === 'success' ? 'var(--p-success-light,#D1FAE5)' : ($rx->statusColor() === 'info' ? 'var(--p-info-light,#DBEAFE)' : 'var(--p-surface-2)') }};
            color:{{ $rx->statusColor() === 'success' ? 'var(--p-success,#059669)' : ($rx->statusColor() === 'info' ? 'var(--p-primary,#2563EB)' : 'var(--p-text-muted)') }};">
            {{ ucfirst($rx->status) }}
        </span>
    </div>
    @if($rx->items->isNotEmpty())
    <div class="panel-body" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--p-surface-2);font-size:0.8125rem;color:var(--p-text-muted);">
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Medication</th>
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Dose</th>
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Frequency</th>
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Duration</th>
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rx->items as $item)
                <tr style="border-top:1px solid var(--p-border);font-size:0.875rem;">
                    <td style="padding:var(--p-space-2) var(--p-space-4);font-weight:600;">{{ $item->drug_name }}</td>
                    <td style="padding:var(--p-space-2) var(--p-space-4);">{{ $item->dose }} ({{ $item->route }})</td>
                    <td style="padding:var(--p-space-2) var(--p-space-4);">{{ $item->frequency }}</td>
                    <td style="padding:var(--p-space-2) var(--p-space-4);">{{ $item->duration_days ? $item->duration_days . ' days' : '—' }}</td>
                    <td style="padding:var(--p-space-2) var(--p-space-4);">
                        <span style="font-size:0.75rem;color:{{ $item->isDispensed() ? 'var(--p-success,#059669)' : 'var(--p-text-muted)' }};">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endforeach
@endif

@endsection
```

- [ ] **Step 6: Add Prescription factory if missing**

Check: `ls database/factories/PrescriptionFactory.php`

If missing, create `database/factories/PrescriptionFactory.php`:
```php
<?php
namespace Database\Factories;

use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'patient_id'    => null,
            'facility_id'   => null,
            'prescribed_by' => $this->faker->name(),
            'status'        => $this->faker->randomElement(['active', 'dispensed', 'expired']),
            'notes'         => null,
            'prescribed_at' => now()->subDays(rand(1, 60)),
            'dispensed_at'  => null,
            'expires_at'    => now()->addDays(30),
        ];
    }
}
```

- [ ] **Step 7: Run test and full suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PrescriptionsPageTest.php --no-coverage
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

Expected: all pass

- [ ] **Step 8: Commit**

```
git add routes/web.php app/Http/Controllers/MedicalId/PatientPortalController.php resources/views/portals/patient/prescriptions.blade.php database/factories/PrescriptionFactory.php tests/Feature/Portal/PrescriptionsPageTest.php
git commit -m "feat(portal): add prescriptions page"
```

---

## Task 6: Add Consent Management page

**Files:**
- Modify: `app/Http/Controllers/MedicalId/PatientPortalController.php` — add `consentRequests()` + `approveConsent()` + `denyConsent()` methods
- Modify: `routes/web.php` — add GET + POST routes for consent
- Create: `resources/views/portals/patient/consent.blade.php`

**Context:** `ConsentRequest` has `patient_id`, `requesting_facility_id`, `requesting_user_id`, `purpose`, `requested_scope` (array), `duration_minutes`, `status` (pending/approved/denied). Patient can approve or deny pending requests. Approved requests create a `ConsentGrant`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Portal/ConsentPageTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\ConsentRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_consent_page_requires_auth(): void
    {
        $this->get(route('portals.patient.consent'))->assertRedirect(route('login'));
    }

    public function test_consent_page_shows_pending_requests(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);
        ConsentRequest::factory()->count(2)->create(['patient_id' => $patient->id, 'status' => 'pending']);

        $this->actingAs($user)->get(route('portals.patient.consent'))
            ->assertStatus(200)
            ->assertViewHas('consentRequests', fn($r) => $r->count() === 2);
    }

    public function test_patient_can_approve_consent_request(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);
        $req = ConsentRequest::factory()->create(['patient_id' => $patient->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->post(route('portals.patient.consent.approve', $req->id));
        $response->assertRedirect(route('portals.patient.consent'));

        $this->assertEquals('approved', $req->fresh()->status);
    }

    public function test_patient_can_deny_consent_request(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);
        $req = ConsentRequest::factory()->create(['patient_id' => $patient->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->post(route('portals.patient.consent.deny', $req->id));
        $response->assertRedirect(route('portals.patient.consent'));

        $this->assertEquals('denied', $req->fresh()->status);
    }

    public function test_patient_cannot_act_on_other_patients_consent(): void
    {
        $patientA = Patient::factory()->create(['is_demo' => false]);
        $patientB = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patientA->id]);
        $req = ConsentRequest::factory()->create(['patient_id' => $patientB->id, 'status' => 'pending']);

        $this->actingAs($user)->post(route('portals.patient.consent.approve', $req->id))
            ->assertStatus(403);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/ConsentPageTest.php --no-coverage
```

Expected: FAIL — routes do not exist

- [ ] **Step 3: Add routes**

In `routes/web.php`:
```php
Route::get('/portals/patient/consent', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'consentRequests'])->name('portals.patient.consent');
Route::post('/portals/patient/consent/{id}/approve', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'approveConsent'])->name('portals.patient.consent.approve');
Route::post('/portals/patient/consent/{id}/deny', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'denyConsent'])->name('portals.patient.consent.deny');
```

- [ ] **Step 4: Add controller methods**

In `PatientPortalController.php`, add three methods:

```php
public function consentRequests(Request $request)
{
    $patient = $this->resolvePatient();

    $consentRequests = $patient
        ? \App\Models\ConsentRequest::where('patient_id', $patient->id)
            ->with('requestingFacility')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
        : collect([]);

    if ($patient) {
        $this->ctx->auditPatientAccess(
            actionType:   'patient_consent_view',
            resourceType: 'ConsentRequest',
            resourceId:   null,
            patientId:    $patient->id,
        );
    }

    return view('portals.patient.consent', compact('patient', 'consentRequests'));
}

public function approveConsent(Request $request, string $id)
{
    $patient = $this->resolvePatient();
    abort_if(!$patient, 403);

    $req = \App\Models\ConsentRequest::where('id', $id)
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    abort_if($req->status !== 'pending', 422, 'Request is not pending.');

    $req->update(['status' => 'approved']);

    \App\Models\ConsentGrant::create([
        'patient_id'         => $patient->id,
        'facility_id'        => $req->requesting_facility_id,
        'consent_request_id' => $req->id,
        'authorizing_actor'  => 'patient',
        'scope'              => $req->requested_scope ?? [],
        'status'             => 'active',
        'expires_at'         => now()->addMinutes($req->duration_minutes ?? 1440),
    ]);

    $this->ctx->auditPatientAccess(
        actionType:   'patient_consent_approved',
        resourceType: 'ConsentRequest',
        resourceId:   $req->id,
        patientId:    $patient->id,
    );

    return redirect()->route('portals.patient.consent')->with('success', 'Consent approved.');
}

public function denyConsent(Request $request, string $id)
{
    $patient = $this->resolvePatient();
    abort_if(!$patient, 403);

    $req = \App\Models\ConsentRequest::where('id', $id)
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    abort_if($req->status !== 'pending', 422, 'Request is not pending.');

    $req->update(['status' => 'denied']);

    $this->ctx->auditPatientAccess(
        actionType:   'patient_consent_denied',
        resourceType: 'ConsentRequest',
        resourceId:   $req->id,
        patientId:    $patient->id,
    );

    return redirect()->route('portals.patient.consent')->with('success', 'Consent denied.');
}
```

- [ ] **Step 5: Add ConsentRequest and ConsentGrant factories if missing**

Check: `ls database/factories/ConsentRequestFactory.php`

If missing, create `database/factories/ConsentRequestFactory.php`:
```php
<?php
namespace Database\Factories;

use App\Models\ConsentRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsentRequestFactory extends Factory
{
    protected $model = ConsentRequest::class;

    public function definition(): array
    {
        return [
            'patient_id'             => null,
            'requesting_facility_id' => null,
            'requesting_user_id'     => null,
            'purpose'                => $this->faker->sentence(),
            'requested_scope'        => ['read_records'],
            'duration_minutes'       => 1440,
            'status'                 => 'pending',
        ];
    }
}
```

- [ ] **Step 6: Create `resources/views/portals/patient/consent.blade.php`**

```blade
@extends('layouts.portal')

@section('title', 'Consent Requests — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Consent Requests')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Consent Requests</h1>
        <p class="page-subtitle">Review and manage access requests from healthcare providers.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-info mb-4" style="margin-bottom:var(--p-space-4);">
    <i data-lucide="check-circle"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@elseif($consentRequests->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="shield-check"></i></div>
        <h3>No Consent Requests</h3>
        <p>You have no pending or past access requests.</p>
    </div>
</div>
@else
<div style="display:flex;flex-direction:column;gap:var(--p-space-4);">
@foreach($consentRequests as $req)
<div class="panel">
    <div class="panel-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:var(--p-space-4);">
            <div style="flex:1;">
                <div style="font-weight:700;font-size:0.9375rem;margin-bottom:4px;">
                    {{ $req->requestingFacility?->name ?? 'Unknown Facility' }}
                </div>
                <div style="font-size:0.875rem;color:var(--p-text-muted);margin-bottom:var(--p-space-3);">
                    {{ $req->purpose ?? 'Access request' }}
                </div>
                <div style="display:flex;gap:var(--p-space-2);flex-wrap:wrap;">
                    @foreach(($req->requested_scope ?? []) as $scope)
                    <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;background:var(--p-surface-2);color:var(--p-text-muted);">
                        {{ $scope }}
                    </span>
                    @endforeach
                </div>
                <div style="font-size:0.8125rem;color:var(--p-text-muted);margin-top:var(--p-space-2);">
                    Requested {{ $req->created_at->diffForHumans() }}
                    @if($req->duration_minutes)
                     · Valid for {{ round($req->duration_minutes / 60, 1) }} hours
                    @endif
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:var(--p-space-2);">
                @if($req->status === 'pending')
                <form method="POST" action="{{ route('portals.patient.consent.approve', $req->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="font-size:0.8125rem;">
                        <i data-lucide="check" style="width:0.875rem;height:0.875rem;"></i> Approve
                    </button>
                </form>
                <form method="POST" action="{{ route('portals.patient.consent.deny', $req->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn" style="font-size:0.8125rem;background:var(--p-surface-2);color:var(--p-text-muted);">
                        <i data-lucide="x" style="width:0.875rem;height:0.875rem;"></i> Deny
                    </button>
                </form>
                @else
                <span style="padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;
                    background:{{ $req->status === 'approved' ? 'var(--p-success-light,#D1FAE5)' : 'var(--p-surface-2)' }};
                    color:{{ $req->status === 'approved' ? 'var(--p-success,#059669)' : 'var(--p-text-muted)' }};">
                    {{ ucfirst($req->status) }}
                </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
</div>
@endif

@endsection
```

- [ ] **Step 7: Run test and full suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/ConsentPageTest.php --no-coverage
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

Expected: all pass

- [ ] **Step 8: Commit**

```
git add routes/web.php app/Http/Controllers/MedicalId/PatientPortalController.php resources/views/portals/patient/consent.blade.php database/factories/ConsentRequestFactory.php tests/Feature/Portal/ConsentPageTest.php
git commit -m "feat(portal): add consent management page with approve/deny actions"
```

---

## Task 7: Add Documents page

**Files:**
- Modify: `app/Http/Controllers/MedicalId/PatientPortalController.php` — add `documents()` method
- Modify: `routes/web.php` — add `/portals/patient/documents` route
- Create: `resources/views/portals/patient/documents.blade.php`

**Context:** `OfficialDocument` has `patient_id`, `title`, `document_type`, `status`, `issued_at`, `expires_at`. Show documents scoped to the patient's own record. Do NOT expose `pdf_path` directly; show document type, title, status, dates only (no download link for now — production PDF serving requires signed URLs).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Portal/DocumentsPageTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\OfficialDocument;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_documents_page_requires_auth(): void
    {
        $this->get(route('portals.patient.documents'))->assertRedirect(route('login'));
    }

    public function test_documents_page_shows_patient_documents(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);
        OfficialDocument::factory()->count(3)->create(['patient_id' => $patient->id]);

        $this->actingAs($user)->get(route('portals.patient.documents'))
            ->assertStatus(200)
            ->assertViewHas('documents', fn($d) => $d->count() === 3);
    }

    public function test_documents_page_does_not_expose_pdf_path(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);

        $this->actingAs($user)->get(route('portals.patient.documents'))
            ->assertStatus(200)
            ->assertDontSee('pdf_path');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/DocumentsPageTest.php --no-coverage
```

Expected: FAIL — route does not exist

- [ ] **Step 3: Add route**

```php
Route::get('/portals/patient/documents', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'documents'])->name('portals.patient.documents');
```

- [ ] **Step 4: Add `documents()` method to controller**

```php
public function documents(Request $request)
{
    $patient = $this->resolvePatient();

    $documents = $patient
        ? \App\Models\OfficialDocument::where('patient_id', $patient->id)
            ->select(['id', 'title', 'document_type', 'document_number', 'status', 'issued_at', 'expires_at', 'sensitivity_level'])
            ->orderByDesc('issued_at')
            ->limit(50)
            ->get()
        : collect([]);

    if ($patient) {
        $this->ctx->auditPatientAccess(
            actionType:   'patient_documents_view',
            resourceType: 'OfficialDocument',
            resourceId:   null,
            patientId:    $patient->id,
        );
    }

    return view('portals.patient.documents', compact('patient', 'documents'));
}
```

Note: `select()` deliberately excludes `pdf_path`, `payload_json`, `document_hash`, `standard_mapping_json` to prevent exposure.

- [ ] **Step 5: Create `resources/views/portals/patient/documents.blade.php`**

```blade
@extends('layouts.portal')

@section('title', 'My Documents — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Documents')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Documents</h1>
        <p class="page-subtitle">Official documents and certificates issued to you.</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@elseif($documents->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="file-text"></i></div>
        <h3>No Documents</h3>
        <p>You have no official documents issued at this time.</p>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="file-text"></i> Official Documents</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--p-surface-2);font-size:0.8125rem;color:var(--p-text-muted);">
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Document</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Type</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Number</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Status</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Issued</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Expires</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                <tr style="border-top:1px solid var(--p-border);font-size:0.875rem;">
                    <td style="padding:var(--p-space-3) var(--p-space-4);font-weight:600;">{{ $doc->title ?? 'Untitled Document' }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-family:monospace;font-size:0.8125rem;">{{ $doc->document_number ?? '—' }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);">
                        <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;font-weight:700;
                            background:{{ $doc->status === 'released' ? 'var(--p-success-light,#D1FAE5)' : 'var(--p-surface-2)' }};
                            color:{{ $doc->status === 'released' ? 'var(--p-success,#059669)' : 'var(--p-text-muted)' }};">
                            {{ ucfirst($doc->status) }}
                        </span>
                    </td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ $doc->issued_at?->format('d M Y') ?? '—' }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ $doc->expires_at?->format('d M Y') ?? 'No expiry' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
```

- [ ] **Step 6: Add OfficialDocument factory if missing**

Check: `ls database/factories/OfficialDocumentFactory.php`

If missing, create `database/factories/OfficialDocumentFactory.php`:
```php
<?php
namespace Database\Factories;

use App\Models\OfficialDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficialDocumentFactory extends Factory
{
    protected $model = OfficialDocument::class;

    public function definition(): array
    {
        return [
            'document_type'   => $this->faker->randomElement(['discharge_summary', 'lab_report', 'referral_letter', 'vaccination_card']),
            'document_number' => strtoupper($this->faker->bothify('DOC-????-######')),
            'patient_id'      => null,
            'title'           => $this->faker->sentence(4),
            'status'          => 'released',
            'sensitivity_level' => 'normal',
            'issued_at'       => now()->subDays(rand(1, 90)),
            'expires_at'      => null,
            'is_demo'         => false,
        ];
    }
}
```

- [ ] **Step 7: Run test and full suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/DocumentsPageTest.php --no-coverage
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

Expected: all pass

- [ ] **Step 8: Commit**

```
git add routes/web.php app/Http/Controllers/MedicalId/PatientPortalController.php resources/views/portals/patient/documents.blade.php database/factories/OfficialDocumentFactory.php tests/Feature/Portal/DocumentsPageTest.php
git commit -m "feat(portal): add documents page; select() excludes pdf_path and sensitive fields"
```

---

## Task 8: Add Profile & Privacy Settings page

**Files:**
- Modify: `app/Http/Controllers/MedicalId/PatientPortalController.php` — add `profile()` + `updateProfile()` methods
- Modify: `routes/web.php` — add GET + POST routes for profile
- Create: `resources/views/portals/patient/profile.blade.php`

**Context:** Patient editable fields: `phone_number`, `email`, `address`, `emergency_contact` (array with `name`, `phone`, `relationship`). The privacy settings checkboxes on the dashboard index currently have no backend save — they move here as real saved preferences. We store privacy preferences as a JSON column `privacy_preferences` on the patients table (add via migration if missing). Fields: `require_consent_for_full_record` (bool), `emergency_access_allowed` (bool).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Portal/ProfilePageTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_requires_auth(): void
    {
        $this->get(route('portals.patient.profile'))->assertRedirect(route('login'));
    }

    public function test_profile_page_shows_patient_data(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false, 'phone_number' => '+1234567890']);
        $user = User::factory()->create(['patient_id' => $patient->id]);

        $this->actingAs($user)->get(route('portals.patient.profile'))
            ->assertStatus(200)
            ->assertSee('+1234567890');
    }

    public function test_patient_can_update_contact_details(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);

        $response = $this->actingAs($user)->post(route('portals.patient.profile.update'), [
            'phone_number' => '+9876543210',
            'email'        => 'newemail@example.com',
            'address'      => '123 Health Street',
        ]);

        $response->assertRedirect(route('portals.patient.profile'));
        $this->assertEquals('+9876543210', $patient->fresh()->phone_number);
    }

    public function test_profile_update_rejects_invalid_email(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);

        $this->actingAs($user)->post(route('portals.patient.profile.update'), [
            'email' => 'not-an-email',
        ])->assertSessionHasErrors(['email']);
    }
}
```

- [ ] **Step 2: Create migration for `privacy_preferences` on patients**

Create `database/migrations/2026_05_24_000002_add_privacy_preferences_to_patients.php`:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->json('privacy_preferences')->nullable()->after('is_demo');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('privacy_preferences');
        });
    }
};
```

- [ ] **Step 3: Update Patient model**

Add `privacy_preferences` to `$fillable` and `$casts`:
```php
// In $fillable:
'privacy_preferences',

// In $casts:
'privacy_preferences' => 'array',
```

- [ ] **Step 4: Run migration**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate
```

- [ ] **Step 5: Add routes**

```php
Route::get('/portals/patient/profile', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'profile'])->name('portals.patient.profile');
Route::post('/portals/patient/profile', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'updateProfile'])->name('portals.patient.profile.update');
```

- [ ] **Step 6: Add controller methods**

```php
public function profile(Request $request)
{
    $patient = $this->resolvePatient();

    if (!$patient) {
        return view('portals.patient.profile', compact('patient'));
    }

    $this->ctx->auditPatientAccess(
        actionType:   'patient_profile_view',
        resourceType: 'Patient',
        resourceId:   $patient->id,
        patientId:    $patient->id,
    );

    return view('portals.patient.profile', compact('patient'));
}

public function updateProfile(Request $request)
{
    $patient = $this->resolvePatient();
    abort_if(!$patient, 403);

    $validated = $request->validate([
        'phone_number'                     => 'sometimes|nullable|string|max:30',
        'email'                            => 'sometimes|nullable|email|max:255',
        'address'                          => 'sometimes|nullable|string|max:500',
        'emergency_contact.name'           => 'sometimes|nullable|string|max:100',
        'emergency_contact.phone'          => 'sometimes|nullable|string|max:30',
        'emergency_contact.relationship'   => 'sometimes|nullable|string|max:50',
        'privacy_require_consent'          => 'sometimes|boolean',
        'privacy_emergency_access'         => 'sometimes|boolean',
    ]);

    $updateData = array_filter([
        'phone_number'    => $validated['phone_number'] ?? null,
        'email'           => $validated['email'] ?? null,
        'address'         => $validated['address'] ?? null,
    ], fn($v) => $v !== null);

    if (isset($validated['emergency_contact'])) {
        $updateData['emergency_contact'] = $validated['emergency_contact'];
    }

    $privacyPrefs = $patient->privacy_preferences ?? [];
    if (isset($validated['privacy_require_consent'])) {
        $privacyPrefs['require_consent_for_full_record'] = (bool) $validated['privacy_require_consent'];
    }
    if (isset($validated['privacy_emergency_access'])) {
        $privacyPrefs['emergency_access_allowed'] = (bool) $validated['privacy_emergency_access'];
    }
    if (!empty($privacyPrefs)) {
        $updateData['privacy_preferences'] = $privacyPrefs;
    }

    $patient->update($updateData);

    $this->ctx->auditPatientAccess(
        actionType:   'patient_profile_updated',
        resourceType: 'Patient',
        resourceId:   $patient->id,
        patientId:    $patient->id,
    );

    return redirect()->route('portals.patient.profile')->with('success', 'Profile updated successfully.');
}
```

- [ ] **Step 7: Create `resources/views/portals/patient/profile.blade.php`**

```blade
@extends('layouts.portal')

@section('title', 'My Profile — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Profile')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your contact details and privacy preferences.</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@else

@if(session('success'))
<div class="alert alert-info mb-4" style="margin-bottom:var(--p-space-4);"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif

<form method="POST" action="{{ route('portals.patient.profile.update') }}">
@csrf

<div class="grid-main-side">

    <!-- Contact Details -->
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="user"></i> Contact Details</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-4);">

                @error('phone_number')<div class="alert alert-warning" style="margin-bottom:0;padding:var(--p-space-2) var(--p-space-3);font-size:0.8125rem;">{{ $message }}</div>@enderror
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $patient->phone_number) }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>

                @error('email')<div class="alert alert-warning" style="margin-bottom:0;padding:var(--p-space-2) var(--p-space-3);font-size:0.8125rem;">{{ $message }}</div>@enderror
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Email</label>
                    <input type="email" name="email" value="{{ old('email', $patient->email) }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>

                @error('address')<div class="alert alert-warning" style="margin-bottom:0;padding:var(--p-space-2) var(--p-space-3);font-size:0.8125rem;">{{ $message }}</div>@enderror
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Address</label>
                    <textarea name="address" rows="2"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);resize:vertical;">{{ old('address', $patient->address) }}</textarea>
                </div>

            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="phone-call"></i> Emergency Contact</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-4);">
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Name</label>
                    <input type="text" name="emergency_contact[name]" value="{{ old('emergency_contact.name', $patient->emergency_contact['name'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Phone</label>
                    <input type="text" name="emergency_contact[phone]" value="{{ old('emergency_contact.phone', $patient->emergency_contact['phone'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Relationship</label>
                    <input type="text" name="emergency_contact[relationship]" value="{{ old('emergency_contact.relationship', $patient->emergency_contact['relationship'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Settings -->
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="shield-check"></i> Privacy Settings</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-4);">

                <label style="display:flex;align-items:flex-start;gap:var(--p-space-4);padding:var(--p-space-4);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius);cursor:pointer;">
                    <input type="checkbox" name="privacy_require_consent" value="1"
                        {{ ($patient->privacy_preferences['require_consent_for_full_record'] ?? true) ? 'checked' : '' }}
                        style="width:1.1rem;height:1.1rem;accent-color:var(--p-primary);margin-top:1px;flex-shrink:0;">
                    <div>
                        <div style="font-size:0.875rem;font-weight:700;color:var(--p-text);margin-bottom:3px;">Require Consent for Full Record</div>
                        <div style="font-size:0.8125rem;color:var(--p-text-muted);line-height:1.45;">Providers can only see a masked preview without your explicit consent.</div>
                    </div>
                </label>

                <label style="display:flex;align-items:flex-start;gap:var(--p-space-4);padding:var(--p-space-4);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius);cursor:pointer;">
                    <input type="checkbox" name="privacy_emergency_access" value="1"
                        {{ ($patient->privacy_preferences['emergency_access_allowed'] ?? true) ? 'checked' : '' }}
                        style="width:1.1rem;height:1.1rem;accent-color:var(--p-danger);margin-top:1px;flex-shrink:0;">
                    <div>
                        <div style="font-size:0.875rem;font-weight:700;color:var(--p-text);margin-bottom:3px;">Emergency Access Allowed</div>
                        <div style="font-size:0.8125rem;color:var(--p-text-muted);line-height:1.45;">Permit audited "break-glass" access during emergencies without standard consent.</div>
                    </div>
                </label>

            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width:100%;">
                <i data-lucide="save"></i> Save Changes
            </button>
        </div>
    </div>
</div>

</form>
@endif

@endsection
```

- [ ] **Step 8: Run test and full suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/ProfilePageTest.php --no-coverage
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

Expected: all pass

- [ ] **Step 9: Commit**

```
git add routes/web.php app/Http/Controllers/MedicalId/PatientPortalController.php resources/views/portals/patient/profile.blade.php database/migrations/2026_05_24_000002_add_privacy_preferences_to_patients.php app/Models/Patient.php tests/Feature/Portal/ProfilePageTest.php
git commit -m "feat(portal): add profile & privacy settings page with backend save"
```

---

## Task 9: Update portal navigation — add all new tabs to quick actions and remove dead privacy checkboxes from index

**Files:**
- Modify: `resources/views/portals/patient/index.blade.php`

**Context:** The dashboard index has a 4-button quick-actions grid: Appointments, Access Logs, Care Map, Help. We must expand it to include all new portal sections: Labs, Prescriptions, Consent, Documents, Profile. Also remove the privacy settings panel from `index.blade.php` (they now live in the profile page). Keep the Temporary Access QR panel and Health ID card. Keep the clinical disclaimer.

- [ ] **Step 1: Write the test**

```php
// tests/Feature/Portal/PortalNavigationTest.php
<?php
namespace Tests\Feature\Portal;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_links_to_all_portal_sections(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id]);

        $response = $this->actingAs($user)->get(route('portals.patient'));
        $response->assertStatus(200);

        // All portal section routes must be present in page output
        $response->assertSee(route('portals.patient.labs'));
        $response->assertSee(route('portals.patient.prescriptions'));
        $response->assertSee(route('portals.patient.consent'));
        $response->assertSee(route('portals.patient.documents'));
        $response->assertSee(route('portals.patient.profile'));
        $response->assertSee(route('portals.patient.appointments'));
        $response->assertSee(route('portals.patient.logs'));
    }

    public function test_dashboard_does_not_show_demo_banner_for_real_user(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);
        $user = User::factory()->create(['patient_id' => $patient->id, 'is_demo' => false]);

        $response = $this->actingAs($user)->get(route('portals.patient'));
        $response->assertDontSee('Demo Mode');
        $response->assertDontSee('sample data');
    }
}
```

- [ ] **Step 2: Run test to verify current state**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PortalNavigationTest.php --no-coverage
```

Expected: FAIL — new route links not present in page

- [ ] **Step 3: Update quick-actions grid in `index.blade.php`**

Replace the entire `<!-- Quick Actions -->` section (lines ~54–72):

```blade
<!-- Quick Actions -->
<div class="quick-actions mb-8" style="margin-bottom:var(--p-space-8);">
    <a href="{{ route('portals.patient.appointments') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="calendar-check-2"></i></div>
        <span class="quick-action-label">Appointments</span>
    </a>
    <a href="{{ route('portals.patient.labs') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="flask-conical"></i></div>
        <span class="quick-action-label">Lab Results</span>
    </a>
    <a href="{{ route('portals.patient.prescriptions') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="pill"></i></div>
        <span class="quick-action-label">Prescriptions</span>
    </a>
    <a href="{{ route('portals.patient.consent') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="shield-check"></i></div>
        <span class="quick-action-label">Consent</span>
    </a>
    <a href="{{ route('portals.patient.documents') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="file-text"></i></div>
        <span class="quick-action-label">Documents</span>
    </a>
    <a href="{{ route('portals.patient.logs') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="history"></i></div>
        <span class="quick-action-label">Access Logs</span>
    </a>
    <a href="{{ route('portals.patient.profile') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="user-cog"></i></div>
        <span class="quick-action-label">My Profile</span>
    </a>
    <a href="{{ route('public.care-map') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="map-pin"></i></div>
        <span class="quick-action-label">Care Map</span>
    </a>
</div>
```

- [ ] **Step 4: Remove the Privacy Settings panel from `index.blade.php`**

The `<!-- Right: Privacy Settings + Disclaimer -->` column currently contains both the privacy settings panel and the disclaimer. Remove the privacy settings panel entirely (lines ~114–154), keeping only the clinical disclaimer. The right column becomes just the disclaimer:

```blade
    <!-- Right: Disclaimer only -->
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">
        <!-- Clinical Safety Disclaimer -->
        <div class="panel">
            <div class="panel-body">
                <div class="alert alert-warning">
                    <i data-lucide="alert-triangle"></i>
                    <div style="font-size:0.8125rem;">
                        {{ __('onboarding.brand.clinical_disclaimer', [], app()->getLocale()) ?: 'OpesCare facilitates access to your health records but is not a substitute for clinical advice. Always consult a licensed healthcare provider for medical decisions.' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-body" style="text-align:center;">
                <p style="font-size:0.8125rem;color:var(--p-text-muted);margin-bottom:var(--p-space-3);">Manage your privacy preferences and contact details in your profile.</p>
                <a href="{{ route('portals.patient.profile') }}" class="btn btn-primary" style="font-size:0.8125rem;">
                    <i data-lucide="user-cog"></i> Go to Profile
                </a>
            </div>
        </div>
    </div>
```

- [ ] **Step 5: Run test**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/PortalNavigationTest.php --no-coverage
```

Expected: PASS

- [ ] **Step 6: Run full suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test --no-coverage
```

Expected: all tests pass

- [ ] **Step 7: Commit**

```
git add resources/views/portals/patient/index.blade.php tests/Feature/Portal/PortalNavigationTest.php
git commit -m "feat(portal): expand navigation to all portal sections; move privacy to profile page"
```

---

## Self-Review

### Spec coverage check

| Requirement | Task |
|-------------|------|
| Remove demo patient fallback | Task 2 |
| Fix demo banner (verify it only fires for is_demo=true users) | Task 1 (is_demo column) + Task 2 |
| Fix QR code not generating (data.url JS bug) | Task 3 |
| Fix countdown timer | Task 3 |
| Add Labs page | Task 4 |
| Add Prescriptions page | Task 5 |
| Add Consent page with approve/deny | Task 6 |
| Add Documents page (no pdf_path exposure) | Task 7 |
| Add Profile + Privacy Settings with backend save | Task 8 |
| Update portal navigation | Task 9 |
| All routes present and wired | Tasks 4–9 |
| All views synchronized with real DB models | All tasks |

### Type consistency check

- All controller methods use `resolvePatient()` (defined in Task 2) ✓
- All methods use `$this->ctx->auditPatientAccess()` (existing signature) ✓
- Route names: `portals.patient.labs`, `.prescriptions`, `.consent`, `.consent.approve`, `.consent.deny`, `.documents`, `.profile`, `.profile.update` — consistent across routes, controller, and tests ✓
- `ConsentGrant::create()` uses fields matching the model's `$fillable` ✓

### Security check

- `documents()` uses `select()` to exclude `pdf_path`, `payload_json`, `document_hash` ✓
- `approveConsent()` / `denyConsent()` scope to `$patient->id` before acting ✓
- `updateProfile()` uses `$request->validate()` before any update ✓
- `resolvePatient()` no longer returns foreign records ✓
