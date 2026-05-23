# Wave 2 SP-3: Patient Self-Booking Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let patients browse care facilities, view available appointment slots, book, and cancel appointments via the mobile API.

**Architecture:** Add three new mobile API endpoints (facility browse, slot listing, booking/cancel) using the existing `CareFacility`, `AppointmentSlot`, and `Appointment` models. Slot booking is atomic (DB transaction + pessimistic lock to prevent double-booking). Notifications are fired synchronously after booking by injecting `NotificationService`.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL/SQLite (test), existing `App\Models\{CareFacility, AppointmentSlot, Appointment}`, `App\Modules\Notifications\Services\NotificationService`

---

## File Map

| Action | Path | Purpose |
|--------|------|---------|
| Create | `app/Http/Controllers/Api/Mobile/MobileFacilityController.php` | Browse care_facilities + list slots |
| Modify | `app/Http/Controllers/Api/Mobile/MobileAppointmentController.php` | Add `book()` + `cancel()` methods |
| Modify | `routes/api.php` | Wire 4 new mobile routes |
| Create | `tests/Feature/Mobile/PatientBookingTest.php` | Feature tests for all booking flows |

---

### Task 1: Facility Browse Endpoints

**Files:**
- Create: `app/Http/Controllers/Api/Mobile/MobileFacilityController.php`
- Modify: `routes/api.php` (add 2 GET routes inside the `Route::prefix('mobile')` block)
- Test: `tests/Feature/Mobile/PatientBookingTest.php`

- [ ] **Step 1.1: Write the failing tests**

Create `tests/Feature/Mobile/PatientBookingTest.php`:

```php
<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\CareFacility;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Facility;
use App\Models\User;

class PatientBookingTest extends TestCase
{
    use RefreshDatabase;

    private CareFacility $facility;
    private AppointmentSlot $slot;
    private string $patientId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->facility = CareFacility::create([
            'facility_name'       => 'City Medical Centre',
            'facility_type'       => 'hospital',
            'listing_status'      => 'active',
            'city'                => 'Yaounde',
            'country_code'        => 'CM',
            'address'             => '12 Independence Ave',
            'phone_primary'       => '+237600000001',
            'integration_status'  => 'none',
        ]);

        // AppointmentSlot belongs to `facilities` (not care_facilities) and `users` via FKs
        // Create a dummy Facility and User so the FK is satisfied in SQLite
        $facilityRow = Facility::create([
            'name'   => 'City Medical Centre',
            'type'   => 'hospital',
            'status' => 'active',
        ]);

        $provider = User::factory()->create();

        $this->slot = AppointmentSlot::create([
            'facility_id' => $facilityRow->id,
            'provider_id' => $provider->id,
            'starts_at'   => now()->addDay()->setTime(9, 0),
            'ends_at'     => now()->addDay()->setTime(9, 30),
            'capacity'    => 2,
            'booked_count' => 0,
            'status'      => 'open',
        ]);

        $this->patientId = Patient::create([
            'health_id'     => 'OC-TST-9999-0001-01',
            'first_name'    => 'Alice',
            'last_name'     => 'Patient',
            'sex'           => 'female',
            'date_of_birth' => '1990-01-01',
            'is_demo'       => false,
        ])->id;
    }

    // ── Task 1 tests ────────────────────────────────────────────────

    public function test_list_facilities_returns_active_listings(): void
    {
        $response = $this->getJson('/api/mobile/facilities');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id', 'facility_name', 'facility_type', 'city']]])
                 ->assertJsonFragment(['facility_name' => 'City Medical Centre']);
    }

    public function test_list_facilities_filters_by_type(): void
    {
        CareFacility::create([
            'facility_name'  => 'Quick Clinic',
            'facility_type'  => 'clinic',
            'listing_status' => 'active',
            'city'           => 'Douala',
            'country_code'   => 'CM',
            'address'        => '5 Port Road',
            'phone_primary'  => '+237600000002',
        ]);

        $response = $this->getJson('/api/mobile/facilities?type=clinic');

        $response->assertStatus(200)
                 ->assertJsonFragment(['facility_name' => 'Quick Clinic']);

        // hospital should not appear in clinic filter
        $data = $response->json('data');
        $this->assertNotContains('City Medical Centre', array_column($data, 'facility_name'));
    }

    public function test_get_facility_detail_returns_services_and_hours(): void
    {
        $response = $this->getJson('/api/mobile/facilities/' . $this->facility->id);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['id', 'facility_name', 'services', 'hours']])
                 ->assertJsonFragment(['facility_name' => 'City Medical Centre']);
    }

    // ── Task 2 tests ────────────────────────────────────────────────

    public function test_list_slots_returns_open_future_slots(): void
    {
        $facilityRow = \App\Models\Facility::first();

        $response = $this->getJson('/api/mobile/facilities/' . $facilityRow->id . '/slots');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id', 'starts_at', 'ends_at', 'available_count']]]);
    }

    // ── Task 3 tests ────────────────────────────────────────────────

    public function test_book_appointment_creates_appointment_and_decrements_slot(): void
    {
        $response = $this->postJson('/api/mobile/appointments', [
            '_patient_id'         => $this->patientId,
            'facility_id'         => \App\Models\Facility::first()->id,
            'appointment_slot_id' => $this->slot->id,
            'appointment_type'    => 'consultation',
            'reason'              => 'Annual checkup',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['id', 'status', 'scheduled_at']])
                 ->assertJsonFragment(['status' => 'booked']);

        $this->assertDatabaseHas('appointments', [
            'patient_id'          => $this->patientId,
            'appointment_slot_id' => $this->slot->id,
            'status'              => 'booked',
        ]);

        $this->assertDatabaseHas('appointment_slots', [
            'id'           => $this->slot->id,
            'booked_count' => 1,
        ]);
    }

    public function test_book_appointment_rejects_when_slot_is_full(): void
    {
        // Fill the slot to capacity
        $this->slot->update(['booked_count' => 2]);

        $response = $this->postJson('/api/mobile/appointments', [
            '_patient_id'         => $this->patientId,
            'facility_id'         => \App\Models\Facility::first()->id,
            'appointment_slot_id' => $this->slot->id,
            'appointment_type'    => 'consultation',
        ]);

        $response->assertStatus(409)
                 ->assertJsonFragment(['error_code' => 'SLOT_FULL']);
    }

    // ── Task 4 tests ────────────────────────────────────────────────

    public function test_cancel_appointment_updates_status_and_restores_slot(): void
    {
        // Book first
        $appointment = Appointment::create([
            'patient_id'          => $this->patientId,
            'facility_id'         => $this->slot->facility_id,
            'appointment_slot_id' => $this->slot->id,
            'appointment_type'    => 'consultation',
            'status'              => 'booked',
            'scheduled_at'        => now()->addDay(),
        ]);
        $this->slot->increment('booked_count');

        $response = $this->postJson('/api/mobile/appointments/' . $appointment->id . '/cancel', [
            '_patient_id' => $this->patientId,
            'reason'      => 'Schedule conflict',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'cancelled']);

        $this->assertDatabaseHas('appointments', [
            'id'     => $appointment->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('appointment_slots', [
            'id'           => $this->slot->id,
            'booked_count' => 0,
        ]);
    }

    public function test_cancel_non_owned_appointment_is_rejected(): void
    {
        $otherPatientId = Patient::create([
            'health_id'     => 'OC-TST-9999-0002-01',
            'first_name'    => 'Bob',
            'last_name'     => 'Other',
            'sex'           => 'male',
            'date_of_birth' => '1985-06-15',
            'is_demo'       => false,
        ])->id;

        $appointment = Appointment::create([
            'patient_id'   => $otherPatientId,
            'facility_id'  => $this->slot->facility_id,
            'appointment_type' => 'consultation',
            'status'       => 'booked',
            'scheduled_at' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/mobile/appointments/' . $appointment->id . '/cancel', [
            '_patient_id' => $this->patientId, // wrong patient
            'reason'      => 'Attempt to cancel another patient appointment',
        ]);

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 1.2: Run tests to verify they fail**

```
php artisan test --filter="PatientBookingTest" --stop-on-failure
```
Expected: FAIL — `MobileFacilityController` does not exist yet.

- [ ] **Step 1.3: Create `MobileFacilityController`**

Create `app/Http/Controllers/Api/Mobile/MobileFacilityController.php`:

```php
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CareFacility;
use App\Models\AppointmentSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileFacilityController extends Controller
{
    /**
     * GET /api/mobile/facilities
     * Query: ?type=hospital|clinic|pharmacy  ?city=Yaounde  ?q=search_term  ?page=1
     */
    public function index(Request $request): JsonResponse
    {
        $query = CareFacility::where('listing_status', 'active');

        if ($type = $request->query('type')) {
            $query->where('facility_type', $type);
        }

        if ($city = $request->query('city')) {
            $query->where('city', 'like', '%' . $city . '%');
        }

        if ($q = $request->query('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('facility_name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('city', 'like', '%' . $q . '%');
            });
        }

        $facilities = $query->select([
            'id', 'facility_name', 'facility_type', 'ownership_type',
            'city', 'region', 'address', 'latitude', 'longitude',
            'phone_primary', 'phone_secondary', 'email', 'website',
            'integration_status', 'listing_status',
        ])->paginate(20);

        return response()->json([
            'data'       => $facilities->items(),
            'pagination' => [
                'total'        => $facilities->total(),
                'per_page'     => $facilities->perPage(),
                'current_page' => $facilities->currentPage(),
                'last_page'    => $facilities->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/mobile/facilities/{id}
     */
    public function show(string $id): JsonResponse
    {
        $facility = CareFacility::with(['services', 'hours', 'insurances'])
            ->where('listing_status', 'active')
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id'                 => $facility->id,
                'facility_name'      => $facility->facility_name,
                'facility_type'      => $facility->facility_type,
                'ownership_type'     => $facility->ownership_type,
                'city'               => $facility->city,
                'region'             => $facility->region,
                'address'            => $facility->address,
                'latitude'           => $facility->latitude,
                'longitude'          => $facility->longitude,
                'phone_primary'      => $facility->phone_primary,
                'phone_secondary'    => $facility->phone_secondary,
                'email'              => $facility->email,
                'website'            => $facility->website,
                'integration_status' => $facility->integration_status,
                'description'        => $facility->description,
                'services'           => $facility->services->map(fn ($s) => [
                    'service_name'        => $s->service_name,
                    'service_category'    => $s->service_category,
                    'specialty'           => $s->specialty,
                    'appointment_required' => $s->appointment_required,
                    'walk_in_allowed'     => $s->walk_in_allowed,
                    'availability_status' => $s->availability_status,
                ]),
                'hours' => $facility->hours->map(fn ($h) => [
                    'day_of_week'     => $h->day_of_week,
                    'opens_at'        => $h->opens_at,
                    'closes_at'       => $h->closes_at,
                    'is_closed'       => $h->is_closed,
                    'is_24_hours'     => $h->is_24_hours,
                    'service_context' => $h->service_context,
                ]),
                'insurances' => $facility->insurances->map(fn ($i) => [
                    'insurance_name'   => $i->insurance_name,
                    'cashless_available' => $i->cashless_available,
                    'status'           => $i->status,
                ]),
            ],
        ]);
    }

    /**
     * GET /api/mobile/facilities/{id}/slots
     * Returns upcoming open appointment slots for the given Facility (facilities table) ID.
     * Query: ?date=2026-05-24 (optional, defaults to today onward)
     */
    public function slots(Request $request, string $facilityId): JsonResponse
    {
        $from = $request->query('date')
            ? \Carbon\Carbon::parse($request->query('date'))->startOfDay()
            : now();

        $slots = AppointmentSlot::where('facility_id', $facilityId)
            ->where('status', 'open')
            ->where('starts_at', '>=', $from)
            ->whereRaw('booked_count < capacity')
            ->orderBy('starts_at')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $slots->map(fn ($s) => [
                'id'              => $s->id,
                'starts_at'       => $s->starts_at->toIso8601String(),
                'ends_at'         => $s->ends_at->toIso8601String(),
                'available_count' => $s->capacity - $s->booked_count,
                'provider_id'     => $s->provider_id,
            ]),
        ]);
    }
}
```

- [ ] **Step 1.4: Wire the routes**

In `routes/api.php`, inside the `Route::prefix('mobile')` group (after the existing appointment read routes at ~line 186), add:

```php
        // Care facility directory — public browsable listings
        Route::get('/facilities', [\App\Http\Controllers\Api\Mobile\MobileFacilityController::class, 'index']);
        Route::get('/facilities/{id}', [\App\Http\Controllers\Api\Mobile\MobileFacilityController::class, 'show']);
        Route::get('/facilities/{id}/slots', [\App\Http\Controllers\Api\Mobile\MobileFacilityController::class, 'slots']);

        // Patient appointment self-booking + cancellation
        Route::post('/appointments', [\App\Http\Controllers\Api\Mobile\MobileAppointmentController::class, 'book']);
        Route::post('/appointments/{id}/cancel', [\App\Http\Controllers\Api\Mobile\MobileAppointmentController::class, 'cancel']);
```

- [ ] **Step 1.5: Run Task 1 tests only**

```
php artisan test --filter="test_list_facilities_returns_active_listings|test_list_facilities_filters_by_type|test_get_facility_detail_returns_services_and_hours|test_list_slots_returns_open_future_slots"
```
Expected: PASS (4 tests green).

- [ ] **Step 1.6: Commit**

```
git add app/Http/Controllers/Api/Mobile/MobileFacilityController.php routes/api.php tests/Feature/Mobile/PatientBookingTest.php
git commit -m "feat(mobile): add facility browse and slot listing endpoints"
```

---

### Task 2: Book Appointment (Atomic)

**Files:**
- Modify: `app/Http/Controllers/Api/Mobile/MobileAppointmentController.php`

- [ ] **Step 2.1: Add `book()` to MobileAppointmentController**

Open `app/Http/Controllers/Api/Mobile/MobileAppointmentController.php` and add these imports at the top (after the existing ones):

```php
use App\Models\AppointmentSlot;
use Illuminate\Support\Facades\DB;
```

Then add the `book()` method after the existing `show()` method:

```php
    /**
     * POST /api/mobile/appointments
     *
     * Body:
     *   _patient_id         string  (test helper; production resolves from auth token)
     *   facility_id         string  UUID of facilities (not care_facilities) row
     *   appointment_slot_id string  UUID of appointment_slots row
     *   appointment_type    string  e.g. "consultation", "follow_up", "lab_collection"
     *   reason              string  optional
     */
    public function book(Request $request): \Illuminate\Http\JsonResponse
    {
        $patientId = $this->resolvePatientId($request);

        $validated = $request->validate([
            'facility_id'         => 'required|uuid|exists:facilities,id',
            'appointment_slot_id' => 'required|uuid|exists:appointment_slots,id',
            'appointment_type'    => 'required|string|max:100',
            'reason'              => 'nullable|string|max:1000',
        ]);

        $appointment = DB::transaction(function () use ($patientId, $validated) {
            // Pessimistic lock: prevent concurrent double-booking of same slot
            $slot = AppointmentSlot::lockForUpdate()->findOrFail($validated['appointment_slot_id']);

            if ($slot->booked_count >= $slot->capacity) {
                throw new \App\Exceptions\SlotFullException('This slot is fully booked.');
            }

            $slot->increment('booked_count');

            return Appointment::create([
                'patient_id'          => $patientId,
                'facility_id'         => $validated['facility_id'],
                'appointment_slot_id' => $validated['appointment_slot_id'],
                'appointment_type'    => $validated['appointment_type'],
                'status'              => 'booked',
                'scheduled_at'        => $slot->starts_at,
                'booked_by_type'      => 'patient',
                'booked_by_id'        => $patientId,
                'reason'              => $validated['reason'] ?? null,
            ]);
        });

        return response()->json(['data' => $this->formatAppointmentDetail($appointment)], 201);
    }
```

- [ ] **Step 2.2: Create SlotFullException**

Create `app/Exceptions/SlotFullException.php`:

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class SlotFullException extends RuntimeException {}
```

- [ ] **Step 2.3: Register exception handler in `bootstrap/app.php`**

In `bootstrap/app.php`, inside `->withExceptions(function (Exceptions $exceptions) {`, add:

```php
        $exceptions->renderable(function (\App\Exceptions\SlotFullException $e, $request) {
            return response()->json([
                'error_code' => 'SLOT_FULL',
                'message'    => $e->getMessage(),
            ], 409);
        });
```

- [ ] **Step 2.4: Run Task 2 tests**

```
php artisan test --filter="test_book_appointment_creates_appointment_and_decrements_slot|test_book_appointment_rejects_when_slot_is_full"
```
Expected: PASS (2 tests green).

- [ ] **Step 2.5: Commit**

```
git add app/Http/Controllers/Api/Mobile/MobileAppointmentController.php app/Exceptions/SlotFullException.php bootstrap/app.php
git commit -m "feat(mobile): patient self-booking with atomic slot locking"
```

---

### Task 3: Cancel Appointment

**Files:**
- Modify: `app/Http/Controllers/Api/Mobile/MobileAppointmentController.php`

- [ ] **Step 3.1: Add `cancel()` to MobileAppointmentController**

Add after the `book()` method:

```php
    /**
     * POST /api/mobile/appointments/{id}/cancel
     *
     * Body:
     *   _patient_id  string  (test helper)
     *   reason       string  optional
     */
    public function cancel(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $patientId = $this->resolvePatientId($request);

        $appointment = Appointment::where('id', $id)->firstOrFail();

        // Ownership check
        if ($appointment->patient_id !== $patientId) {
            return response()->json([
                'error_code' => 'FORBIDDEN',
                'message'    => 'You may only cancel your own appointments.',
            ], 403);
        }

        // Only cancellable statuses
        if (!in_array($appointment->status, ['booked', 'confirmed'])) {
            return response()->json([
                'error_code' => 'INVALID_STATUS',
                'message'    => "Cannot cancel an appointment with status '{$appointment->status}'.",
            ], 422);
        }

        DB::transaction(function () use ($appointment, $request) {
            $appointment->update([
                'status'              => 'cancelled',
                'cancellation_reason' => $request->input('reason'),
                'cancelled_at'        => now(),
                'cancelled_by_id'     => $appointment->patient_id,
            ]);

            // Restore slot capacity if slot was used
            if ($appointment->appointment_slot_id) {
                AppointmentSlot::where('id', $appointment->appointment_slot_id)
                    ->where('booked_count', '>', 0)
                    ->decrement('booked_count');
            }
        });

        return response()->json(['data' => $this->formatAppointmentDetail($appointment->fresh())]);
    }
```

- [ ] **Step 3.2: Run Task 3 tests**

```
php artisan test --filter="test_cancel_appointment_updates_status_and_restores_slot|test_cancel_non_owned_appointment_is_rejected"
```
Expected: PASS (2 tests green).

- [ ] **Step 3.3: Run full PatientBookingTest suite**

```
php artisan test --filter="PatientBookingTest"
```
Expected: PASS (all tests in the file green).

- [ ] **Step 3.4: Run full regression**

```
php artisan test
```
Expected: All tests pass.

- [ ] **Step 3.5: Commit**

```
git add app/Http/Controllers/Api/Mobile/MobileAppointmentController.php
git commit -m "feat(mobile): patient appointment self-cancellation"
```

---

## Self-Review Checklist

- [x] Facility browse: index (filter by type/city/q) + show (with services, hours, insurances) ✅
- [x] Slot listing: open slots, future only, available_count exposed ✅
- [x] Book: atomic with DB transaction + lockForUpdate, 409 when full ✅
- [x] Cancel: ownership check, status guard, slot count restored ✅
- [x] No hardcoded facility UUIDs ✅
- [x] No TBD placeholders — every step has complete code ✅
- [x] `resolvePatientId` reused from existing controller ✅
- [x] Does not touch existing GET /appointments routes ✅
