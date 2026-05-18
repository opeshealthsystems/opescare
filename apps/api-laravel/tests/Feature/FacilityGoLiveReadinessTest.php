<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\User;
use App\Modules\FacilityReadiness\Services\FacilityGoLiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityGoLiveReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_checklist_starts_with_required_items_incomplete()
    {
        [$facility, $admin] = $this->readinessActors();

        $readiness = app(FacilityGoLiveService::class)->getOrCreateReadiness($facility->id, $admin->id);

        $this->assertFalse($readiness->can_go_live);
        $this->assertEquals('pending', $readiness->status);
        $this->assertContains('facility_verified', array_keys($readiness->checklist_json));
        $this->assertContains('go_live_approval_recorded', array_keys($readiness->checklist_json));
        $this->assertDatabaseHas('audit_events', [
            'resource_type' => 'facility_go_live_readiness',
            'resource_id' => $readiness->id,
            'action_type' => 'create',
            'actor_id' => $admin->id,
        ]);
    }

    public function test_facility_cannot_go_live_until_all_required_items_are_complete()
    {
        [$facility, $admin] = $this->readinessActors();
        $service = app(FacilityGoLiveService::class);
        $readiness = $service->getOrCreateReadiness($facility->id, $admin->id);

        $service->markItem($readiness, 'facility_verified', true, $admin->id);

        $this->expectExceptionMessage('FACILITY_GO_LIVE_CHECKLIST_INCOMPLETE');

        $service->approveGoLive($readiness, $admin->id, 'Ready for pilot');
    }

    public function test_facility_go_live_approval_sets_status_and_audit_when_complete()
    {
        [$facility, $admin] = $this->readinessActors();
        $service = app(FacilityGoLiveService::class);
        $readiness = $service->getOrCreateReadiness($facility->id, $admin->id);

        foreach (array_keys($readiness->checklist_json) as $item) {
            $service->markItem($readiness->fresh(), $item, true, $admin->id);
        }

        $approved = $service->approveGoLive($readiness->fresh(), $admin->id, 'Ready for controlled pilot');

        $this->assertTrue($approved->can_go_live);
        $this->assertEquals('approved', $approved->status);
        $this->assertNotNull($approved->approved_at);
        $this->assertDatabaseHas('facilities', ['id' => $facility->id, 'status' => 'active']);
        $this->assertDatabaseHas('audit_events', [
            'resource_type' => 'facility_go_live_readiness',
            'resource_id' => $readiness->id,
            'action_type' => 'approve',
            'actor_id' => $admin->id,
        ]);
    }

    public function test_go_live_api_returns_missing_items_and_approval_result()
    {
        [$facility, $admin] = $this->readinessActors();

        $created = $this->postJson('/api/v1/admin/facilities/'.$facility->id.'/go-live-readiness', [
            'actor_id' => $admin->id,
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.can_go_live', false)
            ->assertJsonPath('data.status', 'pending');

        $items = array_keys($created->json('data.checklist'));
        foreach ($items as $item) {
            $this->patchJson('/api/v1/admin/facilities/'.$facility->id.'/go-live-readiness/items/'.$item, [
                'complete' => true,
                'actor_id' => $admin->id,
            ])->assertOk();
        }

        $approved = $this->postJson('/api/v1/admin/facilities/'.$facility->id.'/go-live-readiness/approve', [
            'actor_id' => $admin->id,
            'approval_note' => 'Pilot approved',
        ]);

        $approved->assertOk()
            ->assertJsonPath('data.can_go_live', true)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.missing_items', []);
    }

    private function readinessActors(): array
    {
        $facility = Facility::create([
            'name' => 'Go Live Clinic',
            'type' => 'clinic',
            'status' => 'pending',
            'license_number' => 'LIC-GO-LIVE',
        ]);
        $admin = User::create([
            'name' => 'Master Admin',
            'email' => 'master-admin@test.com',
            'password' => 'password',
            'primary_facility_id' => $facility->id,
        ]);

        return [$facility, $admin];
    }
}
