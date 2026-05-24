<?php
namespace Tests\Feature\Portal;

use App\Models\Facility;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalDemoRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_no_profile_when_user_has_no_patient_link(): void
    {
        // Create facility and patient role
        $facility = Facility::forceCreate([
            'name' => 'Test Facility',
            'type' => 'hospital',
            'is_demo' => false,
        ]);

        $patientRole = Role::forceCreate([
            'name' => 'patient',
            'description' => 'Patient role',
        ]);

        // A demo patient exists in the DB
        Patient::factory()->create(['health_id' => 'OC-DEMO-001', 'is_demo' => true]);

        // A real user with NO linked patient logs in
        $user = User::factory()->create([
            'patient_id' => null,
            'primary_facility_id' => $facility->id,
            'role_id' => $patientRole->id,
            'is_demo' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('portals.patient'));
        $response->assertStatus(200);
        $response->assertViewHas('patient', null);
        // Must NOT expose the demo patient
        $response->assertDontSee('OC-DEMO-001');
    }

    public function test_dashboard_shows_real_patient_when_linked(): void
    {
        // Create facility and patient role
        $facility = Facility::forceCreate([
            'name' => 'Test Facility 2',
            'type' => 'hospital',
            'is_demo' => false,
        ]);

        $patientRole = Role::forceCreate([
            'name' => 'patient',
            'description' => 'Patient role',
        ]);

        // Create a real patient
        $patient = Patient::factory()->create(['health_id' => 'OC-REAL-001', 'is_demo' => false]);

        // Create a user linked to the patient
        $user = User::factory()->create([
            'patient_id' => $patient->id,
            'primary_facility_id' => $facility->id,
            'role_id' => $patientRole->id,
            'is_demo' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('portals.patient'));
        $response->assertStatus(200);
        $response->assertViewHas('patient', fn($p) => $p && $p->id === $patient->id);
    }
}
