<?php
namespace Tests\Feature\Portal;

use App\Models\FamilyLink;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyLinkModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_link_can_be_created(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);

        $link = FamilyLink::create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
            'relationship'        => 'parent',
            'access_level'        => 'full',
            'status'              => 'active',
            'created_by'          => 'self_registered',
        ]);

        $this->assertDatabaseHas('family_links', ['id' => $link->id]);
        $this->assertEquals($guardian->id, $link->guardianUser->id);
        $this->assertEquals($dependent->id, $link->dependentPatient->id);
    }

    public function test_active_scope_excludes_revoked(): void
    {
        $guardian  = User::factory()->create();
        $dep1 = Patient::factory()->create(['is_demo' => false]);
        $dep2 = Patient::factory()->create(['is_demo' => false]);

        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dep1->id,
            'status'              => 'active',
        ]);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dep2->id,
            'status'              => 'revoked',
        ]);

        $this->assertEquals(1, FamilyLink::active()->where('guardian_user_id', $guardian->id)->count());
    }

    public function test_is_expired_by_age_returns_true_when_grace_period_passed(): void
    {
        $link = FamilyLink::factory()->make([
            'age_transition_expires_at' => now()->subDay(),
        ]);
        $this->assertTrue($link->isExpiredByAge());
    }

    public function test_is_expired_by_age_returns_false_when_no_expiry(): void
    {
        $link = FamilyLink::factory()->make(['age_transition_expires_at' => null]);
        $this->assertFalse($link->isExpiredByAge());
    }

    public function test_unique_constraint_prevents_duplicate_links(): void
    {
        $guardian  = User::factory()->create();
        $dependent = Patient::factory()->create(['is_demo' => false]);

        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        FamilyLink::factory()->create([
            'guardian_user_id'    => $guardian->id,
            'dependent_patient_id'=> $dependent->id,
        ]);
    }
}
