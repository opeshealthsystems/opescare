<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\Patient;
use App\Models\AllergyRecord;
use App\Models\Diagnosis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Wave10FinalHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Return the test-bypass integration client headers.
     * The VerifyIntegrationClient middleware allows these in the testing environment.
     */
    private function clientHeaders(): array
    {
        return [
            'X-Client-ID'     => 'test_client_id',
            'X-Client-Secret' => 'test_client_secret',
        ];
    }

    public function test_emergency_profile_returns_real_allergy_data_not_hardcoded_penicillin(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);

        AllergyRecord::factory()->create([
            'patient_id' => $patient->id,
            'substance'  => 'Aspirin',
            'severity'   => 'Moderate',
            'status'     => 'active',
        ]);

        $response = $this->postJson('/api/v1/connect/patients/emergency-profile', [
            'health_id' => $patient->health_id,
            'reason'    => 'Trauma — immediate clinical need',
        ], $this->clientHeaders());

        $response->assertStatus(200);

        $allergies = $response->json('profile.allergies');
        $this->assertNotEmpty($allergies);
        $this->assertEquals('Aspirin', $allergies[0]['substance']);

        $substances = array_column($allergies, 'substance');
        $this->assertNotContains('Penicillin', $substances);
    }

    public function test_emergency_profile_blood_type_is_null_not_hardcoded(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);

        $response = $this->postJson('/api/v1/connect/patients/emergency-profile', [
            'health_id' => $patient->health_id,
            'reason'    => 'Trauma — immediate clinical need',
        ], $this->clientHeaders());

        $response->assertStatus(200);
        // blood_type is not in the patient schema — must be explicitly null,
        // not the hardcoded 'O+' that was previously fabricated.
        $this->assertNull($response->json('profile.blood_type'));
    }

    public function test_emergency_profile_returns_real_diagnosis_data_not_hardcoded_e119(): void
    {
        $patient  = Patient::factory()->create(['is_demo' => false]);
        $provider = \App\Models\User::factory()->create();
        $facility = \App\Models\Facility::factory()->create();

        // Create a visit row to satisfy the diagnoses.visit_id FK constraint
        $visitId = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\DB::table('visits')->insert([
            'id'          => $visitId,
            'patient_id'  => $patient->id,
            'facility_id' => $facility->id,
            'provider_id' => $provider->id,
            'visit_type'  => 'emergency',
            'status'      => 'open',
            'started_at'  => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Seed a specific real diagnosis — NOT E11.9
        \Illuminate\Support\Facades\DB::table('diagnoses')->insert([
            'id'           => (string) \Illuminate\Support\Str::uuid(),
            'patient_id'   => $patient->id,
            'visit_id'     => $visitId,
            'provider_id'  => $provider->id,
            'code_system'  => 'ICD-10',
            'code'         => 'J45.20',
            'display_name' => 'Mild intermittent asthma',
            'status'       => 'active',
            'is_primary'   => 1,
            'is_demo'      => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $response = $this->postJson('/api/v1/connect/patients/emergency-profile', [
            'health_id' => $patient->health_id,
            'reason'    => 'Trauma — immediate clinical need',
        ], $this->clientHeaders());

        $response->assertStatus(200);

        $conditions = $response->json('profile.chronic_conditions');
        $this->assertNotEmpty($conditions, 'Expected real diagnosis data but got empty array');
        $codes = array_column($conditions, 'code');

        // Real seeded diagnosis must be present
        $this->assertContains('J45.20', $codes);
        // Hardcoded E11.9 must NOT be present
        $this->assertNotContains('E11.9', $codes);
    }
}
