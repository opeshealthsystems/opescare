<?php

namespace Tests\Feature\Partners;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Partners\Models\Partner;
use App\Modules\Partners\Models\PartnerDocument;
use App\Modules\Partners\Models\PartnerAgreement;
use App\Modules\Partners\Models\PartnerAuditLog;
use App\Modules\Partners\Enums\TrustLevel;
use Illuminate\Support\Str;

class PartnerGovernanceTest extends TestCase
{
    use RefreshDatabase;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->partner = Partner::create([
            'uuid' => Str::uuid(),
            'partner_type' => 'healthcare_facility',
            'legal_name' => 'Demo Testing Hospital',
            'status' => 'approved',
            'trust_level' => TrustLevel::LEVEL_1_REGISTERED->value,
        ]);
    }

    public function test_verifying_document_upgrades_trust_and_audits()
    {
        $document = PartnerDocument::create([
            'partner_id' => $this->partner->id,
            'document_type' => 'facility_license',
            'file_path' => '/tmp/doc.pdf',
            'file_name' => 'doc.pdf',
            'mime_type' => 'application/pdf',
            'status' => 'uploaded'
        ]);

        $response = $this->postJson("/api/partner-governance/partners/{$this->partner->uuid}/documents/{$document->id}/verify", [
            'notes' => 'Looks valid.'
        ]);

        $response->assertStatus(200);

        $this->partner->refresh();
        $this->assertEquals(TrustLevel::LEVEL_2_DOCUMENT_VERIFIED->value, $this->partner->trust_level);

        $this->assertDatabaseHas('partner_audit_logs', [
            'partner_id' => $this->partner->id,
            'action' => 'partner_document_verified'
        ]);
        
        $this->assertDatabaseHas('partner_audit_logs', [
            'partner_id' => $this->partner->id,
            'action' => 'partner_trust_level_changed'
        ]);
    }

    public function test_signing_clinical_agreement_upgrades_trust_and_audits()
    {
        $this->partner->trust_level = TrustLevel::LEVEL_2_DOCUMENT_VERIFIED->value;
        $this->partner->save();

        $agreement = PartnerAgreement::create([
            'partner_id' => $this->partner->id,
            'agreement_type' => 'clinical_contribution_agreement',
            'status' => 'draft',
        ]);

        $response = $this->postJson("/api/partner-governance/partners/{$this->partner->uuid}/agreements/{$agreement->id}/mark-signed");

        $response->assertStatus(200);

        $this->partner->refresh();
        $this->assertEquals(TrustLevel::LEVEL_4_CLINICAL_TRUSTED->value, $this->partner->trust_level);

        $this->assertDatabaseHas('partner_audit_logs', [
            'partner_id' => $this->partner->id,
            'action' => 'partner_agreement_signed'
        ]);
    }
}
