<?php

namespace Tests\Feature\CareMap;

use App\Http\Controllers\Api\V1\CareMapController;
use App\Models\CareFacility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * `CareMapController::partnerStockSync` must never manufacture freshness.
 *
 * The method stored no stock, yet stamped `last_availability_update_at = now()`
 * and answered "success". That column is what the public directory renders as
 * how recently a facility reported, so a partner calling this made a listing
 * look freshly reported while its stock stayed empty. Freshness is the signal a
 * patient uses to decide whether to cross a city for a drug or a unit of blood;
 * it is derived from real reports or it is not shown at all.
 *
 * The ownership check was inert for the same reason it looked fine:
 * `$facility->partner_id && ...` short circuits to false when `partner_id` is
 * NULL, and on 2026-09-02 every one of the 1,863 production listings was NULL —
 * so any authenticated caller could stamp any facility in the country.
 *
 * These tests call the controller directly rather than the route. The route sits
 * behind `auth:sanctum`, and Sanctum is not installed in this application at all
 * (see `test_the_sanctum_guard_the_route_depends_on_still_does_not_exist`), so
 * an HTTP test would assert the 500 that guard produces and never reach the
 * method under test.
 */
class PartnerStockSyncTest extends TestCase
{
    use RefreshDatabase;

    private function listing(?string $partnerId = null): CareFacility
    {
        return CareFacility::forceCreate([
            'facility_name'       => 'Pharmacie du Centre',
            'facility_type'       => 'pharmacy',
            'country_code'        => 'CM',
            'region'              => 'Littoral',
            'city'                => 'Douala',
            'address'             => '12 Rue Joss',
            'phone_primary'       => '+237670000001',
            'verification_status' => 'unverified',
            'listing_status'      => 'active',
            'integration_status'  => 'none',
            'partner_id'          => $partnerId,
        ]);
    }

    private function sync(CareFacility $listing): \Illuminate\Http\JsonResponse
    {
        $request = Request::create(
            "/api/v1/care-map/partner/facilities/{$listing->id}/stock-sync",
            'POST',
            ['items' => [['drug_code' => 'C01AA05', 'quantity' => 40]]]
        );

        return app(CareMapController::class)->partnerStockSync($request, $listing->id);
    }

    /** The defect itself: an unowned listing must not be stampable by a stranger. */
    public function test_a_stranger_cannot_stamp_an_unowned_facility_as_freshly_updated(): void
    {
        $listing = $this->listing(partnerId: null);
        $this->actingAs(User::factory()->create());

        $this->assertSame(403, $this->sync($listing)->getStatusCode());

        $this->assertNull(
            $listing->fresh()->last_availability_update_at,
            'an unowned facility must not gain a freshness timestamp from a caller who does not own it'
        );
    }

    /** A partner who owns a different facility gets nothing here either. */
    public function test_a_partner_cannot_stamp_another_partners_facility(): void
    {
        $mine   = User::factory()->create();
        $theirs = User::factory()->create();

        $listing = $this->listing(partnerId: (string) $theirs->id);
        $this->actingAs($mine);

        $this->assertSame(403, $this->sync($listing)->getStatusCode());
        $this->assertNull($listing->fresh()->last_availability_update_at);
    }

    /**
     * Even the rightful owner is told the truth: this endpoint does not ingest.
     * Answering 200 over zero writes is the defect, not the fix.
     */
    public function test_the_owner_is_refused_honestly_and_no_freshness_is_written(): void
    {
        $partner = User::factory()->create();
        $listing = $this->listing(partnerId: (string) $partner->id);

        $this->actingAs($partner);

        $response = $this->sync($listing);
        $payload  = $response->getData(true);

        $this->assertSame(501, $response->getStatusCode());
        $this->assertSame('error', $payload['status']);
        $this->assertSame('STOCK_SYNC_NOT_IMPLEMENTED_HERE', $payload['error_code']);

        $this->assertNull(
            $listing->fresh()->last_availability_update_at,
            'refusing must not stamp freshness either'
        );
    }

    /**
     * Records a live production defect that is NOT this controller's fault.
     *
     * `routes/api.php` puts four routes behind `auth:sanctum` — facility save,
     * report, claim, and this stock sync — but Sanctum is not a dependency, is
     * not in vendor/, and `config/auth.php` declares only the `web` guard. Every
     * request to those four routes therefore dies with
     * "Auth guard [sanctum] is not defined" and returns a 500.
     *
     * `routes/api.php` is a SEALED file, so the fix is a human decision: install
     * Sanctum, or move those routes onto the `web` guard. This test asserts the
     * broken state deliberately, so that whoever fixes it is forced to come back
     * and re-point these tests at the real HTTP route.
     */
    public function test_the_sanctum_guard_the_route_depends_on_still_does_not_exist(): void
    {
        $this->assertArrayNotHasKey(
            'sanctum',
            config('auth.guards'),
            'Sanctum now exists — re-point the tests above at the real HTTP route and delete this test.'
        );

        $route = collect(Route::getRoutes())->first(
            fn ($r) => str_contains($r->uri(), 'partner/facilities/{id}/stock-sync')
        );

        $this->assertNotNull($route, 'the partner stock-sync route should still be registered');
        $this->assertContains(
            'auth:sanctum',
            $route->gatherMiddleware(),
            'this route depends on a guard the application does not define'
        );
    }
}
