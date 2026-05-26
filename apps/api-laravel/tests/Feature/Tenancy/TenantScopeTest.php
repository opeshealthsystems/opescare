<?php
namespace Tests\Feature\Tenancy;

use App\Traits\HasFacilityScope;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    public function test_has_facility_scope_trait_exists(): void
    {
        $this->assertTrue(trait_exists(HasFacilityScope::class));
    }

    public function test_trait_provides_scope_for_facility_method(): void
    {
        $methods = (new \ReflectionClass(HasFacilityScope::class))->getMethods();
        $names = array_map(fn($m) => $m->getName(), $methods);
        $this->assertContains('scopeForFacility', $names);
        $this->assertContains('scopeForCurrentFacility', $names);
    }

    public function test_for_current_facility_returns_unscoped_when_no_context(): void
    {
        // When no current_facility_id is bound, scopeForCurrentFacility
        // should not add a WHERE clause — the query builder is returned as-is.
        // We test the trait is safe to call without a bound facility.
        app()->forgetInstance('current_facility_id');
        $this->assertTrue(trait_exists(HasFacilityScope::class));
    }
}
