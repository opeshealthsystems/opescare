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

    // ── Task 2 tests (slot listing) ──────────────────────────────────

    public function test_list_slots_returns_open_future_slots(): void
    {
        $facilityRow = \App\Models\Facility::first();

        $response = $this->getJson('/api/mobile/facilities/' . $facilityRow->id . '/slots');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id', 'starts_at', 'ends_at', 'available_count']]]);
    }

    // ── Task 3 tests (booking) ───────────────────────────────────────

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

    // ── Task 4 tests (cancellation) ──────────────────────────────────

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
