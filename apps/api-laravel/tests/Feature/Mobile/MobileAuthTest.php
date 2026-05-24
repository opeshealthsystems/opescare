<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use App\Models\Patient;
use App\Models\PatientOtpCode;
use App\Models\PatientAccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MobileAuthTest extends TestCase
{
    use RefreshDatabase;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->patient = Patient::forceCreate([
            'health_id'    => 'CM-HID-AUTH-TEST1',
            'first_name'   => 'Jane',
            'last_name'    => 'Patient',
            'date_of_birth' => '1992-06-15',
            'sex'          => 'female',
            'phone_number' => '0700000001',
            'pin_hash'     => Hash::make('1234'),
            'is_demo'      => false,
        ]);
    }

    public function test_login_with_valid_pin_sends_otp(): void
    {
        $response = $this->postJson('/api/mobile/auth/login', [
            'phone_number' => '0700000001',
            'pin'          => '1234',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'OTP sent to your registered phone number.']);

        $this->assertDatabaseHas('patient_otp_codes', [
            'phone_number' => '0700000001',
        ]);
    }

    public function test_login_with_wrong_pin_returns_401(): void
    {
        $response = $this->postJson('/api/mobile/auth/login', [
            'phone_number' => '0700000001',
            'pin'          => '9999',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_for_unknown_phone_returns_404(): void
    {
        $response = $this->postJson('/api/mobile/auth/login', [
            'phone_number' => '0000000000',
            'pin'          => '1234',
        ]);

        $response->assertStatus(404);
    }

    public function test_verify_otp_issues_access_token(): void
    {
        // Pre-create a valid OTP
        $otp = '123456';
        PatientOtpCode::create([
            'phone_number' => '0700000001',
            'code_hash'    => Hash::make($otp),
            'expires_at'   => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/mobile/auth/otp/verify', [
            'phone_number' => '0700000001',
            'otp'          => $otp,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'patient_id'])
                 ->assertJsonFragment(['status' => 'authenticated', 'token_type' => 'Bearer']);

        $this->assertDatabaseHas('patient_access_tokens', [
            'patient_id' => $this->patient->id,
        ]);
    }

    public function test_verify_with_wrong_otp_returns_401(): void
    {
        PatientOtpCode::create([
            'phone_number' => '0700000001',
            'code_hash'    => Hash::make('654321'),
            'expires_at'   => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/mobile/auth/otp/verify', [
            'phone_number' => '0700000001',
            'otp'          => '000000',
        ]);

        $response->assertStatus(401);
    }

    public function test_verify_with_expired_otp_returns_401(): void
    {
        PatientOtpCode::create([
            'phone_number' => '0700000001',
            'code_hash'    => Hash::make('123456'),
            'expires_at'   => Carbon::now()->subMinutes(1), // already expired
        ]);

        $response = $this->postJson('/api/mobile/auth/otp/verify', [
            'phone_number' => '0700000001',
            'otp'          => '123456',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_mobile_route_requires_token(): void
    {
        // Try to list facilities without a token
        $response = $this->getJson('/api/mobile/facilities');
        $response->assertStatus(401);
    }

    public function test_authenticated_mobile_route_works_with_valid_token(): void
    {
        // Create a valid access token
        $rawToken = 'pat_' . str_repeat('a', 40);
        PatientAccessToken::create([
            'patient_id' => $this->patient->id,
            'token_hash' => Hash::make($rawToken),
            'expires_at' => Carbon::now()->addHours(24),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$rawToken}"])
                         ->getJson('/api/mobile/facilities');

        // Should not be 401 (may be 200 or another code depending on facility data)
        $this->assertNotEquals(401, $response->status());
    }
}
