<?php
namespace Tests\Feature\Portal;

use App\Models\FamilyLink;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $session = ['active_facility_id' => 'test-facility'];

    public function test_family_dashboard_shows_active_links(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false, 'first_name' => 'Anna']);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'status'              => 'active',
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->get(route('portals.patient.family'));

        $response->assertStatus(200);
        $response->assertViewHas('links');
    }

    public function test_store_creates_patient_and_family_link(): void
    {
        $guardian = User::factory()->create();

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.store'), [
                'first_name'   => 'Child',
                'last_name'    => 'Test',
                'date_of_birth'=> '2015-06-01',
                'sex'          => 'male',
                'relationship' => 'parent',
                'access_level' => 'full',
            ]);

        $response->assertRedirect(route('portals.patient.family'));
        $this->assertDatabaseHas('patients', ['first_name' => 'Child', 'last_name' => 'Test']);
        $this->assertDatabaseHas('family_links', [
            'guardian_user_id' => $guardian->id,
            'relationship'     => 'parent',
            'status'           => 'active',
            'created_by'       => 'self_registered',
        ]);
    }

    public function test_store_does_not_create_user_account_for_dependent(): void
    {
        $guardian = User::factory()->create();
        $userCountBefore = User::count();

        $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.store'), [
                'first_name'   => 'Baby',
                'last_name'    => 'Doe',
                'date_of_birth'=> '2020-01-01',
                'sex'          => 'female',
                'relationship' => 'parent',
                'access_level' => 'full',
            ]);

        $this->assertEquals($userCountBefore, User::count());
    }

    public function test_switch_sets_guardian_viewing_session(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'status'              => 'active',
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.switch', $dependent->id));

        $response->assertRedirect();
        $response->assertSessionHas('guardian_viewing_patient_id', $dependent->id);
    }

    public function test_switch_back_clears_guardian_session(): void
    {
        $guardian = User::factory()->create();

        $response = $this->actingAs($guardian)
            ->withSession(array_merge($this->session, ['guardian_viewing_patient_id' => 'some-id']))
            ->post(route('portals.patient.family.switch.back'));

        $response->assertRedirect(route('portals.patient'));
        $response->assertSessionMissing('guardian_viewing_patient_id');
    }

    public function test_revoke_sets_link_status_to_revoked(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        $link = FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'status'              => 'active',
        ]);

        $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.revoke', $link->id));

        $this->assertDatabaseHas('family_links', ['id' => $link->id, 'status' => 'revoked']);
    }

    public function test_cannot_revoke_another_users_link(): void
    {
        $guardian  = User::factory()->create();
        $other     = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);
        $link = FamilyLink::factory()->create([
            'guardian_user_id'    => $other->id,
            'dependent_patient_id'=> $dependent->id,
        ]);

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.revoke', $link->id));

        $response->assertStatus(403);
    }

    public function test_send_invite_cannot_link_demo_patient_by_health_id(): void
    {
        $guardian = User::factory()->create();
        $demo     = Patient::factory()->create(['is_demo' => true, 'email' => null]);

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.invite.send'), [
                'health_id_or_email' => $demo->health_id,
                'relationship'       => 'parent',
                'access_level'       => 'full',
            ]);

        $response->assertSessionHasErrors('health_id_or_email');
        $this->assertDatabaseMissing('family_links', ['guardian_user_id' => $guardian->id]);
    }

    public function test_send_invite_cannot_link_self(): void
    {
        $guardian = User::factory()->create();
        $patient  = Patient::factory()->create(['is_demo' => false]);
        $guardian->update(['patient_id' => $patient->id]);

        $response = $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.invite.send'), [
                'health_id_or_email' => $patient->health_id,
                'relationship'       => 'parent',
                'access_level'       => 'full',
            ]);

        $response->assertSessionHasErrors('health_id_or_email');
        $this->assertDatabaseMissing('family_links', ['guardian_user_id' => $guardian->id]);
    }

    public function test_send_invite_sets_created_by_guardian_invited(): void
    {
        $guardian = User::factory()->create();
        $patient  = Patient::factory()->create(['is_demo' => false]);

        $this->actingAs($guardian)
            ->withSession($this->session)
            ->post(route('portals.patient.family.invite.send'), [
                'health_id_or_email' => $patient->health_id,
                'relationship'       => 'parent',
                'access_level'       => 'full',
            ]);

        $this->assertDatabaseHas('family_links', [
            'guardian_user_id'     => $guardian->id,
            'dependent_patient_id' => $patient->id,
            'created_by'           => 'guardian_invited',
        ]);
    }
}
