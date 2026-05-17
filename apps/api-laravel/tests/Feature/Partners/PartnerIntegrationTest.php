<?php

namespace Tests\Feature\Partners;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Partners\Models\Partner;
use App\Modules\Partners\Models\PartnerIntegration;
use App\Modules\Partners\Enums\TrustLevel;
use Illuminate\Support\Str;

class PartnerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Partner $partner;
    private PartnerIntegration $integration;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->partner = Partner::create([
            'uuid' => Str::uuid(),
            'partner_type' => 'technology_and_interoperability',
            'legal_name' => 'Demo Tech',
            'status' => 'active',
            // Needs to be at least operational verified for production
            'trust_level' => TrustLevel::LEVEL_3_OPERATIONAL_VERIFIED->value,
        ]);

        $this->integration = PartnerIntegration::create([
            'partner_id' => $this->partner->id,
            'client_id' => 'client_123',
            'status' => 'sandbox_active',
            'environment' => 'sandbox',
            'integration_type' => 'api_integration'
        ]);
    }

    public function test_can_certify_integration()
    {
        $response = $this->postJson("/api/partner-governance/partners/{$this->partner->uuid}/integrations/{$this->integration->id}/certify");

        $response->assertStatus(200);
        $this->assertEquals('certified', $this->integration->fresh()->status);
        
        $this->assertDatabaseHas('partner_audit_logs', [
            'action' => 'partner_integration_certified',
            'partner_id' => $this->partner->id
        ]);
    }

    public function test_cannot_enable_production_if_not_certified()
    {
        $response = $this->postJson("/api/partner-governance/partners/{$this->partner->uuid}/integrations/{$this->integration->id}/enable-production");

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Integration must be certified before enabling production access.');
    }

    public function test_can_enable_production_when_certified_and_trusted()
    {
        $this->integration->status = 'certified';
        $this->integration->save();

        $response = $this->postJson("/api/partner-governance/partners/{$this->partner->uuid}/integrations/{$this->integration->id}/enable-production");

        $response->assertStatus(200);
        
        $this->integration->refresh();
        $this->assertEquals('production_active', $this->integration->status);
        $this->assertEquals('production', $this->integration->environment);

        $this->assertDatabaseHas('partner_audit_logs', [
            'action' => 'partner_production_enabled',
            'partner_id' => $this->partner->id
        ]);
    }
}
