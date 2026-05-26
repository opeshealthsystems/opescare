<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\Patient;
use App\Models\AllergyRecord;
use App\Models\Diagnosis;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    public function test_emergency_profile_has_no_hardcoded_blood_type(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);

        $response = $this->postJson('/api/v1/connect/patients/emergency-profile', [
            'health_id' => $patient->health_id,
            'reason'    => 'Trauma — immediate clinical need',
        ], $this->clientHeaders());

        $response->assertStatus(200);
        $this->assertNotEquals('O+', $response->json('profile.blood_type'));
    }

    public function test_emergency_profile_has_no_hardcoded_chronic_conditions(): void
    {
        $patient = Patient::factory()->create(['is_demo' => false]);

        $response = $this->postJson('/api/v1/connect/patients/emergency-profile', [
            'health_id' => $patient->health_id,
            'reason'    => 'Trauma — immediate clinical need',
        ], $this->clientHeaders());

        $response->assertStatus(200);
        $conditions = $response->json('profile.chronic_conditions');
        $codes = array_column($conditions ?? [], 'code');
        $this->assertNotContains('E11.9', $codes);
    }
}
