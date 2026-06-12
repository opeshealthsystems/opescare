# Family Accounts Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow a guardian to link dependents, view their health data, manage consent on their behalf, and receive multi-channel notifications — without touching any existing module.

**Architecture:** New `family_links` table + `FamilyLink` model. A `GuardianAccessMiddleware` validates the active guardian session and injects the dependent patient into the request. A `FamilyController` handles all management flows. `PatientPortalController` gets two additive private methods and single-line updates to existing data-fetch/write methods. All views, routes, and notifications are net-new.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, Blade, Laravel Notifications (mail + database), Laravel Scheduler.

**Critical constraints:**
- PHP binary: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`
- Project root: `C:\laragon\www\opescare\apps\api-laravel`
- Run all tests with: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test`
- Do NOT delete or override any existing file's logic. Every modification listed is purely additive.

---

## File Map

**New files:**
- `database/migrations/2026_05_25_000001_create_family_links_table.php`
- `database/factories/FamilyLinkFactory.php`
- `app/Models/FamilyLink.php`
- `config/family.php`
- `app/Http/Middleware/GuardianAccessMiddleware.php`
- `app/Http/Controllers/MedicalId/FamilyController.php`
- `app/Notifications/FamilyEventNotification.php`
- `app/Notifications/FamilyInviteNotification.php`
- `app/Listeners/NotifyGuardiansOfPatientEvent.php`
- `app/Console/Commands/CheckAgeTransitions.php`
- `resources/views/portals/patient/family/index.blade.php`
- `resources/views/portals/patient/family/add.blade.php`
- `resources/views/portals/patient/family/invite.blade.php`
- `resources/views/portals/patient/family/invite-accept.blade.php`
- `resources/views/portals/patient/family/edit.blade.php`
- `resources/views/partials/guardian-context-banner.blade.php`
- `tests/Feature/Portal/FamilyLinkModelTest.php`
- `tests/Feature/Portal/GuardianAccessMiddlewareTest.php`
- `tests/Feature/Portal/FamilyControllerTest.php`
- `tests/Feature/Portal/GuardianPortalViewTest.php`
- `tests/Feature/Commands/CheckAgeTransitionsTest.php`

**Additive modifications to existing files:**
- `app/Http/Controllers/MedicalId/PatientPortalController.php` — add 2 private methods; update 10 call sites
- `bootstrap/app.php` — add middleware alias `guardian.context`; add daily scheduler entry
- `app/Providers/AppServiceProvider.php` — register model event listeners in `boot()`
- `routes/web.php` — append new routes inside existing auth group + 2 public invite routes
- `resources/views/partials/sidebars/patient.blade.php` — append My Family nav section

---

## Task 1: Migration, Model, and Factory

**Files:**
- Create: `database/migrations/2026_05_25_000001_create_family_links_table.php`
- Create: `app/Models/FamilyLink.php`
- Create: `database/factories/FamilyLinkFactory.php`
- Test: `tests/Feature/Portal/FamilyLinkModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Portal/FamilyLinkModelTest.php
namespace Tests\Feature\Portal;

use App\Models\FamilyLink;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyLinkModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_link_can_be_created(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);

        $link = FamilyLink::create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'relationship'        => 'parent',
            'access_level'        => 'full',
            'status'              => 'active',
            'created_by'          => 'self_registered',
        ]);

        $this->assertDatabaseHas('family_links', ['id' => $link->id]);
        $this->assertEquals($guardian->id, $link->guardianUser->id);
        $this->assertEquals($dependent->id, $link->dependentPatient->id);
    }

    public function test_active_scope_excludes_revoked(): void
    {
        $guardian  = User::factory()->create();
        $dep1 = Patient::factory()->create(['is_demo' => false]);
        $dep2 = Patient::factory()->create(['is_demo' => false]);

        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dep1->id,
            'status'              => 'active',
        ]);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dep2->id,
            'status'              => 'revoked',
        ]);

        $this->assertEquals(1, FamilyLink::active()->where('guardian_user_id', $guardian->id)->count());
    }

    public function test_is_expired_by_age_returns_true_when_grace_period_passed(): void
    {
        $link = FamilyLink::factory()->make([
            'age_transition_expires_at' => now()->subDay(),
        ]);
        $this->assertTrue($link->isExpiredByAge());
    }

    public function test_is_expired_by_age_returns_false_when_no_expiry(): void
    {
        $link = FamilyLink::factory()->make(['age_transition_expires_at' => null]);
        $this->assertFalse($link->isExpiredByAge());
    }

    public function test_unique_constraint_prevents_duplicate_links(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);

        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
        ]);
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/FamilyLinkModelTest.php
```
Expected: FAIL (class FamilyLink not found / table does not exist)

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_05_25_000001_create_family_links_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_links', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('guardian_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('dependent_patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('relationship', 30);
            $table->string('access_level', 20)->default('read_only');
            $table->string('status', 30)->default('pending_invite');
            $table->string('created_by', 30);
            $table->string('invite_token', 64)->nullable();
            $table->timestamp('invite_expires_at')->nullable();
            $table->jsonb('notification_prefs')->default('{}');
            $table->timestamp('age_transition_notified_at')->nullable();
            $table->timestamp('age_transition_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['guardian_user_id', 'dependent_patient_id']);
            $table->index('guardian_user_id');
            $table->index('dependent_patient_id');
            $table->index('invite_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_links');
    }
};
```

- [ ] **Step 4: Create the FamilyLink model**

```php
<?php
// app/Models/FamilyLink.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FamilyLink extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'guardian_user_id',
        'dependent_patient_id',
        'relationship',
        'access_level',
        'status',
        'created_by',
        'invite_token',
        'invite_expires_at',
        'notification_prefs',
        'age_transition_notified_at',
        'age_transition_expires_at',
    ];

    protected $casts = [
        'notification_prefs'           => 'array',
        'invite_expires_at'            => 'datetime',
        'age_transition_notified_at'   => 'datetime',
        'age_transition_expires_at'    => 'datetime',
    ];

    public function guardianUser()
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }

    public function dependentPatient()
    {
        return $this->belongsTo(Patient::class, 'dependent_patient_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePendingInvite(Builder $query): Builder
    {
        return $query->where('status', 'pending_invite');
    }

    public function isExpiredByAge(): bool
    {
        return $this->age_transition_expires_at !== null
            && $this->age_transition_expires_at->isPast();
    }

    public function notificationPrefFor(string $eventKey, string $channel): bool
    {
        $defaults = [
            'lab_result'      => ['portal' => true, 'email' => true,  'sms' => false],
            'appointment'     => ['portal' => true, 'email' => true,  'sms' => false],
            'consent_request' => ['portal' => true, 'email' => true,  'sms' => true],
            'age_transition'  => ['portal' => true, 'email' => true,  'sms' => true],
        ];
        $prefs = $this->notification_prefs[$eventKey] ?? $defaults[$eventKey] ?? [];
        return (bool) ($prefs[$channel] ?? ($defaults[$eventKey][$channel] ?? false));
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php
// database/factories/FamilyLinkFactory.php
namespace Database\Factories;

use App\Models\FamilyLink;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyLinkFactory extends Factory
{
    protected $model = FamilyLink::class;

    public function definition(): array
    {
        return [
            'guardian_user_id'    => User::factory(),
            'dependent_patient_id'=> Patient::factory(),
            'relationship'        => $this->faker->randomElement(['parent', 'grandparent', 'caregiver', 'spouse']),
            'access_level'        => 'read_only',
            'status'              => 'active',
            'created_by'          => 'self_registered',
            'invite_token'        => null,
            'invite_expires_at'   => null,
            'notification_prefs'  => [],
            'age_transition_notified_at' => null,
            'age_transition_expires_at'  => null,
        ];
    }
}
```

- [ ] **Step 6: Run migration**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate
```
Expected: `family_links table created`

- [ ] **Step 7: Run test to confirm it passes**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/FamilyLinkModelTest.php
```
Expected: 5 tests PASS

- [ ] **Step 8: Commit**

```
git add database/migrations/2026_05_25_000001_create_family_links_table.php app/Models/FamilyLink.php database/factories/FamilyLinkFactory.php tests/Feature/Portal/FamilyLinkModelTest.php
git commit -m "feat(family): migration, FamilyLink model, and factory"
```

---

## Task 2: Config, GuardianAccessMiddleware, and Bootstrap Registration

**Files:**
- Create: `config/family.php`
- Create: `app/Http/Middleware/GuardianAccessMiddleware.php`
- Modify: `bootstrap/app.php` (add alias only — additive)
- Test: `tests/Feature/Portal/GuardianAccessMiddlewareTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Portal/GuardianAccessMiddlewareTest.php
namespace Tests\Feature\Portal;

use App\Models\FamilyLink;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianAccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuardianWithDependent(string $accessLevel = 'full', string $status = 'active'): array
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        $link = FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'access_level'        => $accessLevel,
            'status'              => $status,
        ]);
        return [$guardian, $dependent, $link];
    }

    public function test_middleware_passes_through_when_no_guardian_session(): void
    {
        [$guardian] = $this->makeGuardianWithDependent();

        $response = $this->actingAs($guardian)
            ->withSession(['active_facility_id' => 'test-facility'])
            ->get(route('portals.patient.appointments'));

        // No guardian session — should load own data without error
        $response->assertStatus(200);
    }

    public function test_middleware_binds_dependent_when_guardian_session_active(): void
    {
        [$guardian, $dependent] = $this->makeGuardianWithDependent();

        $response = $this->actingAs($guardian)
            ->withSession([
                'active_facility_id'           => 'test-facility',
                'guardian_viewing_patient_id'  => $dependent->id,
            ])
            ->get(route('portals.patient.appointments'));

        $response->assertStatus(200);
    }

    public function test_middleware_clears_session_and_redirects_for_revoked_link(): void
    {
        [$guardian, $dependent] = $this->makeGuardianWithDependent('full', 'revoked');

        $response = $this->actingAs($guardian)
            ->withSession([
                'active_facility_id'           => 'test-facility',
                'guardian_viewing_patient_id'  => $dependent->id,
            ])
            ->get(route('portals.patient.appointments'));

        $response->assertRedirect(route('portals.patient'));
    }

    public function test_middleware_rejects_expired_age_transition_link(): void
    {
        [$guardian, $dependent, $link] = $this->makeGuardianWithDependent();
        $link->update(['age_transition_expires_at' => now()->subDay()]);

        $response = $this->actingAs($guardian)
            ->withSession([
                'active_facility_id'           => 'test-facility',
                'guardian_viewing_patient_id'  => $dependent->id,
            ])
            ->get(route('portals.patient.appointments'));

        $response->assertRedirect(route('portals.patient'));
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/GuardianAccessMiddlewareTest.php
```
Expected: FAIL (class GuardianAccessMiddleware not found)

- [ ] **Step 3: Create config/family.php**

```php
<?php
// config/family.php
return [
    'majority_age' => 18,
    'age_warning_days' => 60,
    'age_grace_days'   => 30,
    'invite_ttl_hours' => 48,
];
```

- [ ] **Step 4: Create GuardianAccessMiddleware**

```php
<?php
// app/Http/Middleware/GuardianAccessMiddleware.php
namespace App\Http\Middleware;

use App\Models\FamilyLink;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardianAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $patientId = session('guardian_viewing_patient_id');

        if (!$patientId) {
            return $next($request);
        }

        $link = FamilyLink::where('guardian_user_id', Auth::id())
            ->where('dependent_patient_id', $patientId)
            ->where('status', 'active')
            ->with('dependentPatient')
            ->first();

        if (!$link || $link->isExpiredByAge()) {
            session()->forget('guardian_viewing_patient_id');
            return redirect()->route('portals.patient')
                ->with('error', 'Guardian access is no longer active for this dependent.');
        }

        $request->attributes->set('guardian_link', $link);
        $request->attributes->set('viewing_patient', $link->dependentPatient);

        return $next($request);
    }
}
```

- [ ] **Step 5: Register middleware alias in bootstrap/app.php**

Add one line to the existing `$middleware->alias([...])` block. Open `bootstrap/app.php` and add inside the alias array:

```php
'guardian.context' => \App\Http\Middleware\GuardianAccessMiddleware::class,
```

The alias block should now read:
```php
$middleware->alias([
    'sdk.token'        => \App\Http\Middleware\VerifySdkToken::class,
    'throttle.client'  => \App\Http\Middleware\ThrottleByClient::class,
    'bridge.agent'     => \App\Http\Middleware\VerifyBridgeAgent::class,
    'portal.access'    => \App\Http\Middleware\EnsurePortalAccess::class,
    'facility.context' => \App\Http\Middleware\RequireFacilityContext::class,
    'consent.grant'    => \App\Http\Middleware\RequireConsentGrant::class,
    'auth.mobile'      => \App\Http\Middleware\AuthenticateMobilePatient::class,
    'guardian.context' => \App\Http\Middleware\GuardianAccessMiddleware::class,
]);
```

- [ ] **Step 6: Apply guardian.context middleware to patient portal data routes**

In `routes/web.php`, wrap the existing patient data routes (appointments, labs, prescriptions, consent, documents, profile, logs) in a sub-group with the `guardian.context` middleware. The patient portal group already exists — add an inner group:

Find this block (lines ~168–176):
```php
Route::get('/portals/patient/appointments', [...]);
Route::get('/portals/patient/labs', [...]);
Route::get('/portals/patient/prescriptions', [...]);
Route::get('/portals/patient/consent', [...]);
Route::post('/portals/patient/consent/{id}/approve', [...]);
Route::post('/portals/patient/consent/{id}/deny', [...]);
Route::get('/portals/patient/documents', [...]);
Route::get('/portals/patient/profile', [...]);
Route::post('/portals/patient/profile', [...]);
```

Wrap those routes in an additional middleware sub-group:
```php
Route::middleware(['guardian.context'])->group(function () {
    Route::get('/portals/patient/appointments', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'appointments'])->name('portals.patient.appointments');
    Route::get('/portals/patient/labs', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'labResults'])->name('portals.patient.labs');
    Route::get('/portals/patient/prescriptions', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'prescriptions'])->name('portals.patient.prescriptions');
    Route::get('/portals/patient/consent', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'consentRequests'])->name('portals.patient.consent');
    Route::post('/portals/patient/consent/{id}/approve', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'approveConsent'])->name('portals.patient.consent.approve');
    Route::post('/portals/patient/consent/{id}/deny', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'denyConsent'])->name('portals.patient.consent.deny');
    Route::get('/portals/patient/documents', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'documents'])->name('portals.patient.documents');
    Route::get('/portals/patient/profile', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'profile'])->name('portals.patient.profile');
    Route::post('/portals/patient/profile', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'updateProfile'])->name('portals.patient.profile.update');
    Route::get('/portals/patient/logs', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'accessLogs'])->name('portals.patient.logs');
});
```

**Note:** Remove the standalone route definitions that you just wrapped (they are now inside the group). The `portals.patient.logs` route that was separately defined outside this block must also be moved inside the group.

- [ ] **Step 7: Run tests to confirm they pass**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/GuardianAccessMiddlewareTest.php
```
Expected: 4 tests PASS

- [ ] **Step 8: Run full test suite to confirm nothing broken**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```
Expected: all previously passing tests still pass

- [ ] **Step 9: Commit**

```
git add config/family.php app/Http/Middleware/GuardianAccessMiddleware.php bootstrap/app.php routes/web.php tests/Feature/Portal/GuardianAccessMiddlewareTest.php
git commit -m "feat(family): GuardianAccessMiddleware with session-based context switching"
```

---

## Task 3: PatientPortalController Additions

**Files:**
- Modify: `app/Http/Controllers/MedicalId/PatientPortalController.php` (additive only)
- Test: `tests/Feature/Portal/GuardianPortalViewTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Portal/GuardianPortalViewTest.php
namespace Tests\Feature\Portal;

use App\Models\FamilyLink;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianPortalViewTest extends TestCase
{
    use RefreshDatabase;

    private function guardianSession(string $patientId): array
    {
        return [
            'active_facility_id'          => 'test-facility',
            'guardian_viewing_patient_id' => $patientId,
        ];
    }

    public function test_guardian_can_view_dependent_lab_results(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'access_level'        => 'read_only',
            'status'              => 'active',
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->guardianSession($dependent->id))
            ->get(route('portals.patient.labs'));

        $response->assertStatus(200);
    }

    public function test_read_only_guardian_cannot_update_profile(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'access_level'        => 'read_only',
            'status'              => 'active',
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->guardianSession($dependent->id))
            ->post(route('portals.patient.profile.update'), ['phone_number' => '123']);

        $response->assertStatus(403);
    }

    public function test_full_access_guardian_can_update_profile(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false, 'phone_number' => '000']);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'access_level'        => 'full',
            'status'              => 'active',
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->guardianSession($dependent->id))
            ->post(route('portals.patient.profile.update'), ['phone_number' => '555-1234']);

        $response->assertRedirect(route('portals.patient.profile'));
        $this->assertDatabaseHas('patients', ['id' => $dependent->id, 'phone_number' => '555-1234']);
    }

    public function test_guardian_views_dependent_data_not_own_data(): void
    {
        $guardian  = User::factory()->create();
        $ownPatient = Patient::factory()->create(['is_demo' => false]);
        $guardian->update(['patient_id' => $ownPatient->id]);

        $dependent = Patient::factory()->create(['is_demo' => false]);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'access_level'        => 'full',
            'status'              => 'active',
        ]);

        LabResult::factory()->create(['patient_id' => $dependent->id]);

        $response = $this->actingAs($guardian)
            ->withSession($this->guardianSession($dependent->id))
            ->get(route('portals.patient.labs'));

        $response->assertStatus(200);
        // The view receives $patient = $dependent, not $ownPatient
        $response->assertViewHas('patient', fn($p) => $p->id === $dependent->id);
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/GuardianPortalViewTest.php
```
Expected: FAIL (resolveViewingPatient method not found)

- [ ] **Step 3: Add two private methods to PatientPortalController**

Open `app/Http/Controllers/MedicalId/PatientPortalController.php`. After the existing `resolvePatient()` method, add:

```php
private function resolveViewingPatient(): ?Patient
{
    if (request()->attributes->has('viewing_patient')) {
        return request()->attributes->get('viewing_patient');
    }
    return $this->resolvePatient();
}

private function assertWriteAllowed(): void
{
    $link = request()->attributes->get('guardian_link');
    if ($link && $link->access_level === 'read_only') {
        abort(403, 'Read-only guardian access does not permit this action.');
    }
}
```

- [ ] **Step 4: Update data-fetch methods to use resolveViewingPatient()**

In each of these methods, replace the single call `$this->resolvePatient()` with `$this->resolveViewingPatient()`:

- `appointments()` — line with `$patient = $this->resolvePatient();`
- `labResults()` — line with `$patient = $this->resolvePatient();`
- `prescriptions()` — line with `$patient = $this->resolvePatient();`
- `consentRequests()` — line with `$patient = $this->resolvePatient();`
- `documents()` — line with `$patient = $this->resolvePatient();`
- `profile()` — line with `$patient = $this->resolvePatient();`
- `accessLogs()` — line with `$patient = $this->resolvePatient();`

Each becomes: `$patient = $this->resolveViewingPatient();`

- [ ] **Step 5: Add assertWriteAllowed() to write methods**

In each of these methods, add `$this->assertWriteAllowed();` as the **first line after** `$patient = $this->resolveViewingPatient();`:

- `updateProfile()` — add after the `resolveViewingPatient()` call, before the validate()
- `approveConsent()` — add as first line
- `denyConsent()` — add as first line

- [ ] **Step 6: Run test to confirm it passes**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/GuardianPortalViewTest.php
```
Expected: 4 tests PASS

- [ ] **Step 7: Run full test suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```
Expected: all tests pass

- [ ] **Step 8: Commit**

```
git add app/Http/Controllers/MedicalId/PatientPortalController.php tests/Feature/Portal/GuardianPortalViewTest.php
git commit -m "feat(family): guardian context in PatientPortalController"
```

---

## Task 4: FamilyController — Management Flows

**Files:**
- Create: `app/Http/Controllers/MedicalId/FamilyController.php`
- Modify: `routes/web.php` (append routes — additive)
- Test: `tests/Feature/Portal/FamilyControllerTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Portal/FamilyControllerTest.php
namespace Tests\Feature\Portal;

use App\Models\FamilyLink;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $session = ['active_facility_id' => 'test-facility'];

    public function test_family_dashboard_shows_active_links(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false, 'first_name' => 'Anna']);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'status'              => 'active',
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->get(route('portals.patient.family'));

        $response->assertStatus(200);
        $response->assertViewHas('links');
    }

    public function test_store_creates_patient_and_family_link(): void
    {
        $guardian = User::factory()->create();

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.store'), [
                'first_name'   => 'Child',
                'last_name'    => 'Test',
                'date_of_birth'=> '2015-06-01',
                'sex'          => 'male',
                'relationship' => 'parent',
                'access_level' => 'full',
            ]);

        $response->assertRedirect(route('portals.patient.family'));
        $this->assertDatabaseHas('patients', ['first_name' => 'Child', 'last_name' => 'Test']);
        $this->assertDatabaseHas('family_links', [
            'guardian_user_id' => $guardian->id,
            'relationship'     => 'parent',
            'status'           => 'active',
            'created_by'       => 'self_registered',
        ]);
    }

    public function test_store_does_not_create_user_account_for_dependent(): void
    {
        $guardian = User::factory()->create();
        $userCountBefore = User::count();

        $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.store'), [
                'first_name'   => 'Baby',
                'last_name'    => 'Doe',
                'date_of_birth'=> '2020-01-01',
                'sex'          => 'female',
                'relationship' => 'parent',
                'access_level' => 'full',
            ]);

        $this->assertEquals($userCountBefore, User::count());
    }

    public function test_switch_sets_guardian_viewing_session(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'status'              => 'active',
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.switch', $dependent->id));

        $response->assertRedirect();
        $response->assertSessionHas('guardian_viewing_patient_id', $dependent->id);
    }

    public function test_switch_back_clears_guardian_session(): void
    {
        $guardian = User::factory()->create();

        $response = $this->actingAs($guardian)
            ->withSession(array_merge($this->session, ['guardian_viewing_patient_id' => 'some-id']))
            ->post(route('portals.patient.family.switch.back'));

        $response->assertRedirect(route('portals.patient'));
        $response->assertSessionMissing('guardian_viewing_patient_id');
    }

    public function test_revoke_sets_link_status_to_revoked(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        $link = FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'status'              => 'active',
        ]);

        $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.revoke', $link->id));

        $this->assertDatabaseHas('family_links', ['id' => $link->id, 'status' => 'revoked']);
    }

    public function test_cannot_revoke_another_users_link(): void
    {
        $guardian  = User::factory()->create();
        $other     = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        $link = FamilyLink::factory()->create([
            'guardian_user_id'    => $other->id,
            'dependent_patient_id'=> $dependent->id,
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.revoke', $link->id));

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/FamilyControllerTest.php
```
Expected: FAIL (routes not found)

- [ ] **Step 3: Create FamilyController**

```php
<?php
// app/Http/Controllers/MedicalId/FamilyController.php
namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\FamilyLink;
use App\Models\Patient;
use App\Notifications\FamilyInviteNotification;
use App\Services\Identity\HealthIdGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FamilyController extends Controller
{
    public function index()
    {
        $links = FamilyLink::where('guardian_user_id', Auth::id())
            ->whereIn('status', ['active', 'pending_invite'])
            ->with('dependentPatient')
            ->orderByDesc('created_at')
            ->get();

        return view('portals.patient.family.index', compact('links'));
    }

    public function addForm()
    {
        return view('portals.patient.family.add');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'date_of_birth'=> 'required|date|before:today',
            'sex'          => 'required|in:male,female,other',
            'relationship' => 'required|in:parent,grandparent,spouse,sibling,caregiver,legal_guardian,other',
            'access_level' => 'required|in:full,read_only',
        ]);

        $gen = new HealthIdGeneratorService();
        $countryCode = Auth::user()?->patient?->country_code ?? 'CM';
        $healthId = $gen->generate($countryCode);

        $patient = Patient::create([
            'health_id'      => $healthId,
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'date_of_birth'  => $data['date_of_birth'],
            'sex'            => $data['sex'],
            'identity_status'=> 'provisional',
            'is_demo'        => false,
        ]);

        FamilyLink::create([
            'guardian_user_id'    => Auth::id(),
            'dependent_patient_id'=> $patient->id,
            'relationship'        => $data['relationship'],
            'access_level'        => $data['access_level'],
            'status'              => 'active',
            'created_by'          => 'self_registered',
        ]);

        return redirect()->route('portals.patient.family')
            ->with('success', 'Dependent added successfully.');
    }

    public function inviteForm()
    {
        return view('portals.patient.family.invite');
    }

    public function sendInvite(Request $request)
    {
        $data = $request->validate([
            'health_id_or_email' => 'required|string|max:255',
            'relationship'       => 'required|in:parent,grandparent,spouse,sibling,caregiver,legal_guardian,other',
            'access_level'       => 'required|in:full,read_only',
        ]);

        $search = $data['health_id_or_email'];
        $patient = Patient::where('health_id', $search)
            ->orWhere('email', $search)
            ->where('is_demo', false)
            ->first();

        if (!$patient) {
            return back()->withErrors(['health_id_or_email' => 'No patient found with that Health ID or email.']);
        }

        $existing = FamilyLink::where('guardian_user_id', Auth::id())
            ->where('dependent_patient_id', $patient->id)
            ->whereIn('status', ['active', 'pending_invite'])
            ->exists();

        if ($existing) {
            return back()->withErrors(['health_id_or_email' => 'A link already exists for this patient.']);
        }

        $rawToken = Str::random(64);
        $link = FamilyLink::create([
            'guardian_user_id'    => Auth::id(),
            'dependent_patient_id'=> $patient->id,
            'relationship'        => $data['relationship'],
            'access_level'        => $data['access_level'],
            'status'              => 'pending_invite',
            'created_by'          => 'invite_accepted',
            'invite_token'        => hash('sha256', $rawToken),
            'invite_expires_at'   => now()->addHours(config('family.invite_ttl_hours', 48)),
        ]);

        // Notify the dependent patient (if they have a user account with email)
        $dependentUser = \App\Models\User::where('patient_id', $patient->id)->first();
        if ($dependentUser) {
            $dependentUser->notify(new FamilyInviteNotification($link, $rawToken));
        }

        return redirect()->route('portals.patient.family')
            ->with('success', 'Invite sent. The link will be active once accepted.');
    }

    public function acceptInvite(string $token)
    {
        $link = $this->findPendingByToken($token);
        if (!$link) {
            return view('portals.patient.family.invite-accept', ['error' => 'This invite link is invalid or has expired.', 'link' => null]);
        }
        return view('portals.patient.family.invite-accept', ['link' => $link, 'error' => null, 'token' => $token]);
    }

    public function confirmInvite(Request $request, string $token)
    {
        $link = $this->findPendingByToken($token);
        if (!$link) {
            return redirect()->route('login')->with('error', 'Invite link is invalid or expired.');
        }

        $link->update([
            'status'         => 'active',
            'created_by'     => 'invite_accepted',
            'invite_token'   => null,
            'invite_expires_at' => null,
        ]);

        return redirect()->route('portals.patient')
            ->with('success', 'Guardian access granted successfully.');
    }

    public function editForm(string $id)
    {
        $link = FamilyLink::where('id', $id)
            ->where('guardian_user_id', Auth::id())
            ->with('dependentPatient')
            ->firstOrFail();

        return view('portals.patient.family.edit', compact('link'));
    }

    public function update(Request $request, string $id)
    {
        $link = FamilyLink::where('id', $id)
            ->where('guardian_user_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'relationship'        => 'required|in:parent,grandparent,spouse,sibling,caregiver,legal_guardian,other',
            'access_level'        => 'required|in:full,read_only',
            'notification_prefs'  => 'nullable|array',
            'notification_prefs.*.portal' => 'boolean',
            'notification_prefs.*.email'  => 'boolean',
            'notification_prefs.*.sms'    => 'boolean',
        ]);

        $link->update([
            'relationship'       => $data['relationship'],
            'access_level'       => $data['access_level'],
            'notification_prefs' => $data['notification_prefs'] ?? [],
        ]);

        return redirect()->route('portals.patient.family')
            ->with('success', 'Family link updated.');
    }

    public function revoke(string $id)
    {
        $link = FamilyLink::where('id', $id)->first();
        abort_if(!$link, 404);
        abort_if($link->guardian_user_id !== Auth::id(), 403);

        $link->update(['status' => 'revoked']);
        session()->forget('guardian_viewing_patient_id');

        return redirect()->route('portals.patient.family')
            ->with('success', 'Guardian access revoked.');
    }

    public function switchTo(string $patientId)
    {
        $link = FamilyLink::where('guardian_user_id', Auth::id())
            ->where('dependent_patient_id', $patientId)
            ->active()
            ->first();

        abort_if(!$link, 403);

        session(['guardian_viewing_patient_id' => $patientId]);

        return redirect()->route('portals.patient.appointments');
    }

    public function switchBack()
    {
        session()->forget('guardian_viewing_patient_id');
        return redirect()->route('portals.patient');
    }

    public function guardianConsentApprove(string $id)
    {
        $link = FamilyLink::where('id', $id)
            ->where('dependent_patient_id', \App\Models\User::where('id', Auth::id())->value('patient_id') ?? '')
            ->firstOrFail();

        $link->update(['age_transition_expires_at' => null]);

        return redirect()->route('portals.patient')
            ->with('success', 'Guardian access re-granted.');
    }

    public function guardianConsentDeny(string $id)
    {
        $link = FamilyLink::where('id', $id)
            ->where('dependent_patient_id', \App\Models\User::where('id', Auth::id())->value('patient_id') ?? '')
            ->firstOrFail();

        $link->update(['status' => 'revoked']);

        return redirect()->route('portals.patient')
            ->with('success', 'Guardian access removed.');
    }

    private function findPendingByToken(string $rawToken): ?FamilyLink
    {
        $hashed = hash('sha256', $rawToken);
        return FamilyLink::where('invite_token', $hashed)
            ->where('status', 'pending_invite')
            ->where('invite_expires_at', '>', now())
            ->with('dependentPatient', 'guardianUser')
            ->first();
    }
}
```

- [ ] **Step 4: Add routes to routes/web.php**

Inside the existing `Route::middleware(['web', 'auth', 'portal.access', 'facility.context'])->group(...)` block, append at the end (before the closing `}`):

```php
// ── Family Management ──────────────────────────────────────────────
Route::get('/portals/patient/family',                        [\App\Http\Controllers\MedicalId\FamilyController::class, 'index'])->name('portals.patient.family');
Route::get('/portals/patient/family/add',                    [\App\Http\Controllers\MedicalId\FamilyController::class, 'addForm'])->name('portals.patient.family.add');
Route::post('/portals/patient/family/add',                   [\App\Http\Controllers\MedicalId\FamilyController::class, 'store'])->name('portals.patient.family.store');
Route::get('/portals/patient/family/invite',                 [\App\Http\Controllers\MedicalId\FamilyController::class, 'inviteForm'])->name('portals.patient.family.invite');
Route::post('/portals/patient/family/invite',                [\App\Http\Controllers\MedicalId\FamilyController::class, 'sendInvite'])->name('portals.patient.family.invite.send');
Route::post('/portals/patient/family/switch/{patientId}',    [\App\Http\Controllers\MedicalId\FamilyController::class, 'switchTo'])->name('portals.patient.family.switch');
Route::post('/portals/patient/family/switch-back',           [\App\Http\Controllers\MedicalId\FamilyController::class, 'switchBack'])->name('portals.patient.family.switch.back');
Route::get('/portals/patient/family/{id}/edit',              [\App\Http\Controllers\MedicalId\FamilyController::class, 'editForm'])->name('portals.patient.family.edit');
Route::post('/portals/patient/family/{id}/edit',             [\App\Http\Controllers\MedicalId\FamilyController::class, 'update'])->name('portals.patient.family.update');
Route::post('/portals/patient/family/{id}/revoke',           [\App\Http\Controllers\MedicalId\FamilyController::class, 'revoke'])->name('portals.patient.family.revoke');
Route::post('/portals/patient/family/{id}/guardian-consent/approve', [\App\Http\Controllers\MedicalId\FamilyController::class, 'guardianConsentApprove'])->name('portals.patient.family.guardian_consent.approve');
Route::post('/portals/patient/family/{id}/guardian-consent/deny',    [\App\Http\Controllers\MedicalId\FamilyController::class, 'guardianConsentDeny'])->name('portals.patient.family.guardian_consent.deny');
```

Also add the two public invite routes OUTSIDE any auth middleware group (at the end of web.php):

```php
// Public — no auth required
Route::get('/family/invite/accept/{token}',  [\App\Http\Controllers\MedicalId\FamilyController::class, 'acceptInvite'])->name('portals.patient.family.invite.accept');
Route::post('/family/invite/accept/{token}', [\App\Http\Controllers\MedicalId\FamilyController::class, 'confirmInvite'])->name('portals.patient.family.invite.confirm');
```

- [ ] **Step 5: Run tests**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Portal/FamilyControllerTest.php
```
Expected: 7 tests PASS

- [ ] **Step 6: Run full test suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```
Expected: all tests pass

- [ ] **Step 7: Commit**

```
git add app/Http/Controllers/MedicalId/FamilyController.php routes/web.php tests/Feature/Portal/FamilyControllerTest.php
git commit -m "feat(family): FamilyController management flows and routes"
```

---

## Task 5: Views and Sidebar

**Files:**
- Create: `resources/views/portals/patient/family/index.blade.php`
- Create: `resources/views/portals/patient/family/add.blade.php`
- Create: `resources/views/portals/patient/family/invite.blade.php`
- Create: `resources/views/portals/patient/family/invite-accept.blade.php`
- Create: `resources/views/portals/patient/family/edit.blade.php`
- Create: `resources/views/partials/guardian-context-banner.blade.php`
- Modify: `resources/views/partials/sidebars/patient.blade.php` (append — additive)

No test for this task (visual verification only). Manual: log in as guardian and verify each page renders.

- [ ] **Step 1: Create guardian context banner partial**

```blade
{{-- resources/views/partials/guardian-context-banner.blade.php --}}
@php $viewingPatient = request()->attributes->get('viewing_patient'); @endphp
@if($viewingPatient)
<div style="background:#0E7490;color:#fff;padding:0.6rem var(--p-space-6);display:flex;align-items:center;justify-content:space-between;font-size:0.875rem;gap:1rem;">
    <div style="display:flex;align-items:center;gap:0.5rem;">
        <i data-lucide="eye" style="width:1rem;height:1rem;"></i>
        <span>
            {{ __('public.portal.viewing_as', [], app()->getLocale()) ?: 'Viewing:' }}
            <strong>{{ $viewingPatient->first_name }} {{ $viewingPatient->last_name }}</strong>
            &nbsp;({{ $viewingPatient->health_id }})
        </span>
    </div>
    <form method="POST" action="{{ route('portals.patient.family.switch.back') }}" style="margin:0;">
        @csrf
        <button type="submit" style="background:rgba(255,255,255,.2);border:none;color:#fff;padding:0.25rem 0.75rem;border-radius:4px;cursor:pointer;font-size:0.8125rem;">
            {{ __('public.portal.switch_back', [], app()->getLocale()) ?: 'Switch Back' }}
        </button>
    </form>
</div>
@endif
```

- [ ] **Step 2: Update portal views to yield the guardian banner**

The layout already has `@yield('patient_banner')` at line 112. Each data page that supports guardian context must yield the banner. Add this inside `@section('content')` at the very top of each of these existing views:

- `resources/views/portals/patient/appointments.blade.php`
- `resources/views/portals/patient/labs.blade.php`
- `resources/views/portals/patient/prescriptions.blade.php`
- `resources/views/portals/patient/consent.blade.php`
- `resources/views/portals/patient/documents.blade.php`
- `resources/views/portals/patient/profile.blade.php`
- `resources/views/portals/patient/logs.blade.php`

Add this `@section` before the existing `@section('content')` in each file (using `@section`/`@endsection` syntax since it yields into the layout):

```blade
@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection
```

- [ ] **Step 3: Create family dashboard view**

```blade
{{-- resources/views/portals/patient/family/index.blade.php --}}
@extends('layouts.portal')
@php $l = app()->getLocale(); @endphp
@section('title', 'My Family — OpesCare')
@section('breadcrumb_home', __('public.portal.my_portal', [], $l) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'My Family')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--p-space-6);">
    <h1 style="font-size:1.25rem;font-weight:700;color:var(--p-text);">My Family</h1>
    <div style="display:flex;gap:var(--p-space-3);">
        <a href="{{ route('portals.patient.family.add') }}" class="btn btn-primary" style="font-size:0.875rem;">
            <i data-lucide="user-plus"></i> Add Dependent
        </a>
        <a href="{{ route('portals.patient.family.invite') }}" class="btn btn-primary" style="font-size:0.875rem;background:var(--p-surface-2);">
            <i data-lucide="mail"></i> Invite Member
        </a>
    </div>
</div>

@if($links->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="users"></i></div>
        <h3>No family members linked yet</h3>
        <p>Add a dependent or invite an existing patient to link their records to yours.</p>
    </div>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:var(--p-space-5);">
    @foreach($links as $link)
    <div class="panel">
        <div class="panel-body">
            <div style="display:flex;align-items:center;gap:var(--p-space-3);margin-bottom:var(--p-space-4);">
                <div style="width:2.5rem;height:2.5rem;background:var(--p-primary-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--p-primary);font-size:1rem;">
                    {{ strtoupper(substr($link->dependentPatient->first_name ?? 'D', 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:600;">{{ $link->dependentPatient->first_name }} {{ $link->dependentPatient->last_name }}</div>
                    <div style="font-size:0.75rem;color:var(--p-text-muted);">{{ $link->dependentPatient->health_id }}</div>
                </div>
                @if($link->status === 'pending_invite')
                <span style="margin-left:auto;font-size:0.7rem;background:#FEF3C7;color:#92400E;padding:2px 8px;border-radius:99px;">Pending</span>
                @else
                <span style="margin-left:auto;font-size:0.7rem;background:#D1FAE5;color:#065F46;padding:2px 8px;border-radius:99px;">Active</span>
                @endif
            </div>
            <div style="font-size:0.8125rem;color:var(--p-text-muted);margin-bottom:var(--p-space-4);">
                {{ ucfirst(str_replace('_', ' ', $link->relationship)) }} &middot;
                {{ $link->access_level === 'full' ? 'Full access' : 'Read only' }}
            </div>
            @if($link->isExpiredByAge())
            <div class="alert alert-warning" style="margin-bottom:var(--p-space-3);font-size:0.8rem;">
                <i data-lucide="alert-triangle"></i>
                Access in grace period — expires {{ $link->age_transition_expires_at->format('M d, Y') }}
            </div>
            @endif
            <div style="display:flex;gap:var(--p-space-2);">
                @if($link->status === 'active')
                <form method="POST" action="{{ route('portals.patient.family.switch', $link->dependent_patient_id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="font-size:0.8rem;">View Records</button>
                </form>
                @endif
                <a href="{{ route('portals.patient.family.edit', $link->id) }}" class="btn" style="font-size:0.8rem;background:var(--p-surface-2);color:var(--p-text);">Edit</a>
                <form method="POST" action="{{ route('portals.patient.family.revoke', $link->id) }}" onsubmit="return confirm('Remove this family link?')">
                    @csrf
                    <button type="submit" class="btn" style="font-size:0.8rem;background:#FEE2E2;color:#991B1B;">Remove</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
```

- [ ] **Step 4: Create add dependent form**

```blade
{{-- resources/views/portals/patient/family/add.blade.php --}}
@extends('layouts.portal')
@section('title', 'Add Dependent — OpesCare')
@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Add Dependent')

@section('content')
<div class="panel" style="max-width:600px;">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="user-plus"></i> Register a Dependent</h2>
    </div>
    <div class="panel-body">
        <p style="font-size:0.875rem;color:var(--p-text-muted);margin-bottom:var(--p-space-5);">
            This creates a new patient record for your dependent. No login account is created — you manage their records.
        </p>
        @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:var(--p-space-4);">
            <i data-lucide="alert-circle"></i>
            <ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('portals.patient.family.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-4);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="form-input" style="width:100%;margin-top:0.25rem;">
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="form-input" style="width:100%;margin-top:0.25rem;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-4);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="form-input" style="width:100%;margin-top:0.25rem;">
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Sex *</label>
                    <select name="sex" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="">— select —</option>
                        <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('sex') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-6);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Your Relationship *</label>
                    <select name="relationship" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="">— select —</option>
                        @foreach(['parent','grandparent','spouse','sibling','caregiver','legal_guardian','other'] as $r)
                        <option value="{{ $r }}" {{ old('relationship') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Access Level *</label>
                    <select name="access_level" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="full" {{ old('access_level','full') === 'full' ? 'selected' : '' }}>Full Access</option>
                        <option value="read_only" {{ old('access_level') === 'read_only' ? 'selected' : '' }}>Read Only</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:var(--p-space-3);">
                <button type="submit" class="btn btn-primary">Register Dependent</button>
                <a href="{{ route('portals.patient.family') }}" class="btn" style="background:var(--p-surface-2);color:var(--p-text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
```

- [ ] **Step 5: Create invite form**

```blade
{{-- resources/views/portals/patient/family/invite.blade.php --}}
@extends('layouts.portal')
@section('title', 'Invite Family Member — OpesCare')
@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Invite Member')

@section('content')
<div class="panel" style="max-width:560px;">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="mail"></i> Invite an Existing Patient</h2>
    </div>
    <div class="panel-body">
        <p style="font-size:0.875rem;color:var(--p-text-muted);margin-bottom:var(--p-space-5);">
            Enter the Health ID or email of a patient who already has an OpesCare record. They will receive an invite link to approve the connection.
        </p>
        @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:var(--p-space-4);">
            <i data-lucide="alert-circle"></i>
            <ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('portals.patient.family.invite.send') }}">
            @csrf
            <div style="margin-bottom:var(--p-space-4);">
                <label style="font-size:0.875rem;font-weight:500;">Health ID or Email *</label>
                <input type="text" name="health_id_or_email" value="{{ old('health_id_or_email') }}" required class="form-input" placeholder="CM-HID-XXXX-XXXX-XXXX or email@example.com" style="width:100%;margin-top:0.25rem;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-6);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Relationship *</label>
                    <select name="relationship" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="">— select —</option>
                        @foreach(['parent','grandparent','spouse','sibling','caregiver','legal_guardian','other'] as $r)
                        <option value="{{ $r }}" {{ old('relationship') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Access Level *</label>
                    <select name="access_level" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="read_only" {{ old('access_level','read_only') === 'read_only' ? 'selected' : '' }}>Read Only</option>
                        <option value="full" {{ old('access_level') === 'full' ? 'selected' : '' }}>Full Access</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:var(--p-space-3);">
                <button type="submit" class="btn btn-primary">Send Invite</button>
                <a href="{{ route('portals.patient.family') }}" class="btn" style="background:var(--p-surface-2);color:var(--p-text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
```

- [ ] **Step 6: Create invite acceptance page**

```blade
{{-- resources/views/portals/patient/family/invite-accept.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Invite — OpesCare</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body class="portal-body" style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
<div class="panel" style="max-width:480px;width:100%;margin:2rem;">
    <div class="panel-body" style="text-align:center;">
        @if($error)
        <div class="alert alert-danger" style="margin-bottom:var(--p-space-5);">
            <i data-lucide="alert-circle"></i> {{ $error }}
        </div>
        <a href="{{ route('login') }}" class="btn btn-primary">Go to Login</a>
        @else
        <div style="margin-bottom:var(--p-space-5);">
            <div style="width:3rem;height:3rem;background:var(--p-primary-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto var(--p-space-4);">
                <i data-lucide="users" style="color:var(--p-primary);"></i>
            </div>
            <h2 style="font-size:1.125rem;font-weight:700;margin-bottom:0.5rem;">Family Access Request</h2>
            <p style="font-size:0.875rem;color:var(--p-text-muted);">
                <strong>{{ $link->guardianUser->name }}</strong> wants to link to
                <strong>{{ $link->dependentPatient->first_name }} {{ $link->dependentPatient->last_name }}</strong>'s health records
                as <strong>{{ ucfirst(str_replace('_',' ',$link->relationship)) }}</strong>
                with <strong>{{ $link->access_level === 'full' ? 'full' : 'read-only' }}</strong> access.
            </p>
        </div>
        <form method="POST" action="{{ route('portals.patient.family.invite.confirm', $token) }}">
            @csrf
            <div style="display:flex;gap:var(--p-space-3);justify-content:center;">
                <button type="submit" class="btn btn-primary">Accept Invite</button>
                <a href="{{ route('login') }}" class="btn" style="background:var(--p-surface-2);color:var(--p-text);">Decline</a>
            </div>
        </form>
        @endif
    </div>
</div>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
```

- [ ] **Step 7: Create edit link form**

```blade
{{-- resources/views/portals/patient/family/edit.blade.php --}}
@extends('layouts.portal')
@section('title', 'Edit Family Link — OpesCare')
@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Edit Family Link')

@section('content')
<div class="panel" style="max-width:640px;">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="settings"></i>
            {{ $link->dependentPatient->first_name }} {{ $link->dependentPatient->last_name }}
        </h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.patient.family.update', $link->id) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-5);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Relationship</label>
                    <select name="relationship" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        @foreach(['parent','grandparent','spouse','sibling','caregiver','legal_guardian','other'] as $r)
                        <option value="{{ $r }}" {{ $link->relationship === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Access Level</label>
                    <select name="access_level" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="full" {{ $link->access_level === 'full' ? 'selected' : '' }}>Full Access</option>
                        <option value="read_only" {{ $link->access_level === 'read_only' ? 'selected' : '' }}>Read Only</option>
                    </select>
                </div>
            </div>

            <h3 style="font-size:0.9375rem;font-weight:600;margin-bottom:var(--p-space-3);">Notification Preferences</h3>
            <table style="width:100%;font-size:0.875rem;margin-bottom:var(--p-space-6);">
                <thead>
                    <tr style="text-align:left;color:var(--p-text-muted);">
                        <th style="padding:0.5rem 0;font-weight:500;">Event</th>
                        <th style="padding:0.5rem;text-align:center;">In-Portal</th>
                        <th style="padding:0.5rem;text-align:center;">Email</th>
                        <th style="padding:0.5rem;text-align:center;">SMS</th>
                    </tr>
                </thead>
                <tbody>
                @foreach([
                    'lab_result'      => 'New Lab Result',
                    'appointment'     => 'Appointment',
                    'consent_request' => 'Consent Request',
                    'age_transition'  => 'Age Transition Alert',
                ] as $key => $label)
                <tr style="border-top:1px solid var(--p-border);">
                    <td style="padding:0.6rem 0;">{{ $label }}</td>
                    @foreach(['portal','email','sms'] as $ch)
                    <td style="padding:0.6rem;text-align:center;">
                        <input type="checkbox" name="notification_prefs[{{ $key }}][{{ $ch }}]" value="1"
                            {{ $link->notificationPrefFor($key, $ch) ? 'checked' : '' }}>
                    </td>
                    @endforeach
                </tr>
                @endforeach
                </tbody>
            </table>

            <div style="display:flex;gap:var(--p-space-3);">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('portals.patient.family') }}" class="btn" style="background:var(--p-surface-2);color:var(--p-text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
```

- [ ] **Step 8: Append My Family section to patient sidebar**

Open `resources/views/partials/sidebars/patient.blade.php` and append after the closing `</div>` of the Resources section:

```blade
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_family', [], $l) ?: 'My Family' }}</div>
    <a href="{{ route('portals.patient.family') }}" class="sidebar-link">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_family_dashboard', [], $l) ?: 'Family Dashboard' }}</span>
    </a>
    <a href="{{ route('portals.patient.family.add') }}" class="sidebar-link">
        <i data-lucide="user-plus"></i>
        <span>{{ __('public.portal.nav_family_add', [], $l) ?: 'Add Dependent' }}</span>
    </a>
    <a href="{{ route('portals.patient.family.invite') }}" class="sidebar-link">
        <i data-lucide="mail"></i>
        <span>{{ __('public.portal.nav_family_invite', [], $l) ?: 'Invite Member' }}</span>
    </a>
</div>
```

- [ ] **Step 9: Commit**

```
git add resources/views/portals/patient/family/ resources/views/partials/guardian-context-banner.blade.php resources/views/partials/sidebars/patient.blade.php resources/views/portals/patient/appointments.blade.php resources/views/portals/patient/labs.blade.php resources/views/portals/patient/prescriptions.blade.php resources/views/portals/patient/consent.blade.php resources/views/portals/patient/documents.blade.php resources/views/portals/patient/profile.blade.php resources/views/portals/patient/logs.blade.php
git commit -m "feat(family): views, guardian context banner, sidebar section"
```

---

## Task 6: Notifications

**Files:**
- Create: `app/Notifications/FamilyEventNotification.php`
- Create: `app/Notifications/FamilyInviteNotification.php`
- Create: `app/Listeners/NotifyGuardiansOfPatientEvent.php`
- Modify: `app/Providers/AppServiceProvider.php` (append model observers — additive)
- Test: inline in existing `FamilyControllerTest.php` (notification assertions added)

- [ ] **Step 1: Create FamilyEventNotification**

```php
<?php
// app/Notifications/FamilyEventNotification.php
namespace App\Notifications;

use App\Models\FamilyLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FamilyEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly FamilyLink $link,
        public readonly string $eventKey,
        public readonly string $eventDescription,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        if ($this->link->notificationPrefFor($this->eventKey, 'portal')) {
            $channels[] = 'database';
        }
        if ($this->link->notificationPrefFor($this->eventKey, 'email')) {
            $channels[] = 'mail';
        }
        // SMS: add 'vonage' or 'twilio' channel here when gateway is configured
        return $channels ?: ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->link->dependentPatient->first_name ?? 'your dependent';
        return (new MailMessage)
            ->subject("OpesCare: Update for {$name}")
            ->line($this->eventDescription)
            ->action('View in Portal', route('portals.patient.family'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event_key'   => $this->eventKey,
            'description' => $this->eventDescription,
            'patient_id'  => $this->link->dependent_patient_id,
            'patient_name'=> trim(($this->link->dependentPatient->first_name ?? '') . ' ' . ($this->link->dependentPatient->last_name ?? '')),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
```

- [ ] **Step 2: Create FamilyInviteNotification**

```php
<?php
// app/Notifications/FamilyInviteNotification.php
namespace App\Notifications;

use App\Models\FamilyLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FamilyInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly FamilyLink $link,
        public readonly string $rawToken,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $guardian = $this->link->guardianUser->name ?? 'Someone';
        $patient  = $this->link->dependentPatient->first_name ?? 'you';
        $acceptUrl = route('portals.patient.family.invite.accept', $this->rawToken);

        return (new MailMessage)
            ->subject("OpesCare: Family Access Request")
            ->greeting("Hello,")
            ->line("{$guardian} is requesting guardian access to {$patient}'s OpesCare health record.")
            ->line("Access level: " . ($this->link->access_level === 'full' ? 'Full access' : 'Read only'))
            ->line("Relationship: " . ucfirst(str_replace('_', ' ', $this->link->relationship)))
            ->action('Review & Accept', $acceptUrl)
            ->line("This invite expires in " . config('family.invite_ttl_hours', 48) . " hours.")
            ->line("If you did not expect this request, you can safely ignore this email.");
    }

    public function toArray(object $notifiable): array
    {
        return ['invite_token' => $this->rawToken];
    }
}
```

- [ ] **Step 3: Create NotifyGuardiansOfPatientEvent listener**

```php
<?php
// app/Listeners/NotifyGuardiansOfPatientEvent.php
namespace App\Listeners;

use App\Models\FamilyLink;
use App\Notifications\FamilyEventNotification;

class NotifyGuardiansOfPatientEvent
{
    public function handleLabResult(object $labResult): void
    {
        $this->dispatch(
            $labResult->patient_id,
            'lab_result',
            'A new lab result is available for your dependent.'
        );
    }

    public function handleAppointment(object $appointment): void
    {
        $this->dispatch(
            $appointment->patient_id,
            'appointment',
            'An appointment has been scheduled for your dependent.'
        );
    }

    public function handleAppointmentUpdated(object $appointment): void
    {
        if (!$appointment->isDirty('status')) {
            return;
        }
        $this->dispatch(
            $appointment->patient_id,
            'appointment',
            'An appointment for your dependent has been updated.'
        );
    }

    public function handleConsentRequest(object $consentRequest): void
    {
        $this->dispatch(
            $consentRequest->patient_id,
            'consent_request',
            'A consent request is pending approval for your dependent.'
        );
    }

    private function dispatch(string $patientId, string $eventKey, string $description): void
    {
        $links = FamilyLink::active()
            ->where('dependent_patient_id', $patientId)
            ->with('guardianUser', 'dependentPatient')
            ->get();

        foreach ($links as $link) {
            $link->guardianUser?->notify(
                new FamilyEventNotification($link, $eventKey, $description)
            );
        }
    }
}
```

- [ ] **Step 4: Register model event listeners in AppServiceProvider**

Open `app/Providers/AppServiceProvider.php` and add to the `boot()` method:

```php
public function boot(): void
{
    // Existing rate limiter config — do not remove
    \Illuminate\Support\Facades\RateLimiter::for('verify', function (\Illuminate\Http\Request $request) {
        return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->ip());
    });

    // Family: notify guardians when patient events occur
    $listener = new \App\Listeners\NotifyGuardiansOfPatientEvent();
    \App\Models\LabResult::created(fn($m) => $listener->handleLabResult($m));
    \App\Models\Appointment::created(fn($m) => $listener->handleAppointment($m));
    \App\Models\Appointment::updated(fn($m) => $listener->handleAppointmentUpdated($m));
    \App\Models\ConsentRequest::created(fn($m) => $listener->handleConsentRequest($m));
}
```

- [ ] **Step 5: Create notification tables migration**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan notifications:table
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate
```

Expected: `notifications table created`

- [ ] **Step 6: Run full test suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```
Expected: all tests pass

- [ ] **Step 7: Commit**

```
git add app/Notifications/ app/Listeners/NotifyGuardiansOfPatientEvent.php app/Providers/AppServiceProvider.php
git commit -m "feat(family): FamilyEventNotification, FamilyInviteNotification, guardian event listener"
```

---

## Task 7: CheckAgeTransitions Command and Scheduler

**Files:**
- Create: `app/Console/Commands/CheckAgeTransitions.php`
- Modify: `bootstrap/app.php` (append withSchedule — additive)
- Test: `tests/Feature/Commands/CheckAgeTransitionsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Commands/CheckAgeTransitionsTest.php
namespace Tests\Feature\Commands;

use App\Models\FamilyLink;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckAgeTransitionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sets_grace_period_on_18th_birthday(): void
    {
        Notification::fake();

        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create([
            'is_demo'       => false,
            'date_of_birth' => now()->subYears(18)->toDateString(),
        ]);
        $link = FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'status'              => 'active',
        ]);

        $this->artisan('family:check-age-transitions')->assertExitCode(0);

        $link->refresh();
        $this->assertNotNull($link->age_transition_expires_at);
        $this->assertTrue($link->age_transition_expires_at->isFuture());
    }

    public function test_command_expires_links_past_grace_period(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        $link = FamilyLink::factory()->create([
            'guardian_user_id'          => $guardian->id,
            'dependent_patient_id'      => $dependent->id,
            'status'                    => 'active',
            'age_transition_expires_at' => now()->subDay(),
        ]);

        $this->artisan('family:check-age-transitions')->assertExitCode(0);

        $link->refresh();
        $this->assertEquals('expired', $link->status);
    }

    public function test_command_sends_60_day_warning(): void
    {
        Notification::fake();

        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create([
            'is_demo'       => false,
            'date_of_birth' => now()->subYears(18)->addDays(60)->toDateString(),
        ]);
        $link = FamilyLink::factory()->create([
            'guardian_user_id'           => $guardian->id,
            'dependent_patient_id'       => $dependent->id,
            'status'                     => 'active',
            'age_transition_notified_at' => null,
        ]);

        $this->artisan('family:check-age-transitions')->assertExitCode(0);

        $link->refresh();
        $this->assertNotNull($link->age_transition_notified_at);
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Commands/CheckAgeTransitionsTest.php
```
Expected: FAIL (command not found)

- [ ] **Step 3: Create the command**

```php
<?php
// app/Console/Commands/CheckAgeTransitions.php
namespace App\Console\Commands;

use App\Models\FamilyLink;
use App\Models\Patient;
use App\Notifications\FamilyEventNotification;
use Illuminate\Console\Command;

class CheckAgeTransitions extends Command
{
    protected $signature   = 'family:check-age-transitions';
    protected $description = 'Expire grace-period family links and send age transition warnings';

    public function handle(): int
    {
        $majorityAge  = config('family.majority_age', 18);
        $warningDays  = config('family.age_warning_days', 60);
        $graceDays    = config('family.age_grace_days', 30);

        // 1. Expire links past their grace period
        $expired = FamilyLink::where('status', 'active')
            ->whereNotNull('age_transition_expires_at')
            ->where('age_transition_expires_at', '<', now())
            ->get();

        foreach ($expired as $link) {
            $link->update(['status' => 'expired']);
            $this->line("Expired link {$link->id}");
        }

        // 2. Set grace period for patients who turn 18 today
        $birthday = now()->subYears($majorityAge)->toDateString();
        $patients18Today = Patient::whereDate('date_of_birth', $birthday)->get();

        foreach ($patients18Today as $patient) {
            $links = FamilyLink::active()
                ->where('dependent_patient_id', $patient->id)
                ->whereNull('age_transition_expires_at')
                ->with('guardianUser', 'dependentPatient')
                ->get();

            foreach ($links as $link) {
                $link->update(['age_transition_expires_at' => now()->addDays($graceDays)]);
                $link->guardianUser?->notify(
                    new FamilyEventNotification($link, 'age_transition',
                        "{$patient->first_name} has turned {$majorityAge}. Guardian access enters a {$graceDays}-day grace period.")
                );
                $this->line("Grace period set for link {$link->id}");
            }
        }

        // 3. Send 60-day warning for patients approaching 18
        $warningBirthday = now()->subYears($majorityAge)->addDays($warningDays)->toDateString();
        $patientsApproaching = Patient::whereDate('date_of_birth', $warningBirthday)->get();

        foreach ($patientsApproaching as $patient) {
            $links = FamilyLink::active()
                ->where('dependent_patient_id', $patient->id)
                ->whereNull('age_transition_notified_at')
                ->with('guardianUser', 'dependentPatient')
                ->get();

            foreach ($links as $link) {
                $link->update(['age_transition_notified_at' => now()]);
                $link->guardianUser?->notify(
                    new FamilyEventNotification($link, 'age_transition',
                        "{$patient->first_name} will turn {$majorityAge} in {$warningDays} days. Guardian access will require re-consent.")
                );
                $this->line("60-day warning sent for link {$link->id}");
            }
        }

        $this->info('Age transition check complete.');
        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Register daily schedule in bootstrap/app.php**

Add `->withSchedule()` to the application builder. Open `bootstrap/app.php` and add before `->create()`:

```php
->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
    $schedule->command('family:check-age-transitions')->daily();
})
```

The final lines of bootstrap/app.php should be:
```php
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\App\Exceptions\SlotFullException $e, $request) {
            return response()->json([
                'error_code' => 'SLOT_FULL',
                'message'    => $e->getMessage(),
            ], 409);
        });
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('family:check-age-transitions')->daily();
    })
    ->create();
```

- [ ] **Step 5: Run test to confirm it passes**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test tests/Feature/Commands/CheckAgeTransitionsTest.php
```
Expected: 3 tests PASS

- [ ] **Step 6: Run full test suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```
Expected: all tests pass

- [ ] **Step 7: Commit**

```
git add app/Console/Commands/CheckAgeTransitions.php bootstrap/app.php tests/Feature/Commands/CheckAgeTransitionsTest.php
git commit -m "feat(family): CheckAgeTransitions command and daily schedule"
```

---

## Task 8: Final Verification

- [ ] **Step 1: Run complete test suite**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```
Expected: all tests pass (including all pre-existing portal, consent, and security tests)

- [ ] **Step 2: Clear all caches**

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan config:clear
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan view:clear
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan route:clear
```

- [ ] **Step 3: Manual smoke test checklist**

Log in as `nshomejude@gmail.com`:
- [ ] Sidebar shows "My Family" section with 3 links
- [ ] `/portals/patient/family` loads family dashboard (empty state if no links)
- [ ] `/portals/patient/family/add` form submits → new patient + link created → redirected to family dashboard
- [ ] New dependent card appears on family dashboard with "View Records" button
- [ ] Click "View Records" → guardian context banner appears at top of appointments page
- [ ] Guardian context banner shows dependent's name and Health ID
- [ ] "Switch Back" clears context and returns to own Health ID dashboard
- [ ] Edit link → change access level to read_only → save → try updating profile as that dependent → 403 returned

- [ ] **Step 4: Commit final cleanup**

```
git add -A
git commit -m "feat(family): complete family accounts feature — guardian context, invite flow, notifications, age transitions"
```
