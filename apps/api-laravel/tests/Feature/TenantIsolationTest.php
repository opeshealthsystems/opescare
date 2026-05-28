<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_facility_a_cannot_retrieve_facility_b_appointments(): void
    {
        $facilityA = Facility::factory()->create();
        $facilityB = Facility::factory()->create();

        $patientA = Patient::factory()->create(['facility_id' => $facilityA->id]);
        $patientB = Patient::factory()->create(['facility_id' => $facilityB->id]);

        $appointmentA = Appointment::factory()->create([
            'facility_id' => $facilityA->id,
            'patient_id'  => $patientA->id,
        ]);
        $appointmentB = Appointment::factory()->create([
            'facility_id' => $facilityB->id,
            'patient_id'  => $patientB->id,
        ]);

        // Simulate request scoped to Facility A
        app()->instance('current_facility_id', $facilityA->id);

        $results = Appointment::all();

        $ids = $results->pluck('id')->toArray();

        $this->assertContains($appointmentA->id, $ids, 'Facility A record should be visible');
        $this->assertNotContains($appointmentB->id, $ids, 'Facility B record must NOT be visible from Facility A scope');
    }

    public function test_facility_scope_applies_to_all_scoped_models(): void
    {
        // Smoke-test: all scoped models respond to facility() scope without error
        $facility = Facility::factory()->create();
        app()->instance('current_facility_id', $facility->id);

        $models = [
            \App\Models\ClinicalNote::class,
            \App\Models\LabOrder::class,
            \App\Models\Prescription::class,
            \App\Models\VitalSign::class,
            \App\Models\InsuranceClaim::class,
        ];

        foreach ($models as $model) {
            $this->assertIsObject(
                $model::query()->first(),
                "Model {$model} should support HasFacilityScope without error"
            );
        }
    }
}
