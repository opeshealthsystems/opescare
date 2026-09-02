<?php

namespace Tests\Feature\FacilityImport;

use App\Enums\FacilityImportReviewStatus;
use App\Models\CareFacility;
use App\Models\FacilityImportReview;
use App\Models\Role;
use App\Models\User;
use App\Modules\CareMap\Services\FacilityImportReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Accepting a held-back candidate must not throw away its contact details.
 *
 * `accept()` wrote every new listing with `phone_primary = 'N/A'` and an address
 * that was only the town, even when the payload carried a real number and a real
 * street. That was invisible while the queue was all OpenStreetMap -- 8 of those
 * 439 rows have a phone -- and expensive the moment the national master records
 * arrived, because two in three of those carry one.
 *
 * A phone number is most of the value of a directory entry to someone who needs
 * a pharmacy tonight, and 'N/A' in that column is what produced `tel:N/A` links
 * across the directory in the first place.
 */
class ReviewAcceptCarriesContactTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'status'  => 'active',
            'role_id' => Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin'])->id,
        ]);
    }

    private function review(array $payload, string $ref): FacilityImportReview
    {
        return FacilityImportReview::create([
            'source_system'      => 'google_places',
            'source_ref'         => $ref,
            'source_attribution' => 'Google Maps / Google Places',
            'reason'             => 'uncertain_match',
            'status'             => FacilityImportReviewStatus::Pending->value,
            'candidate_name'     => $payload['name'] ?? 'Pharmacie du Test',
            'candidate_type'     => 'pharmacy',
            'candidate_city'     => 'Douala',
            'candidate_region'   => 'Littoral',
            'latitude'           => 4.05,
            'longitude'          => 9.70,
            'payload'            => $payload,
        ]);
    }

    public function test_an_accepted_candidate_keeps_its_phone_and_address(): void
    {
        $review = $this->review([
            'name'    => 'Pharmacie du Temple',
            'phone'   => '+237233491615',
            'address' => 'XW5P+54F, Nkongsamba',
        ], 'gplaces:test-with-contact');

        $listing = app(FacilityImportReviewService::class)->accept($review, (string) $this->admin()->id);

        $this->assertSame('+237233491615', $listing->phone_primary);
        $this->assertSame('XW5P+54F, Nkongsamba', $listing->address);
        $this->assertNotNull($listing->dialablePhone(), 'the Call button must actually dial');
    }

    /** OpenStreetMap candidates carry raw OSM tags, not the master record shape. */
    public function test_it_understands_the_openstreetmap_tag_shape(): void
    {
        $review = $this->review([
            'name'          => 'Clinique OSM',
            'contact:phone' => '+237 6 99 00 32 07',
            'addr:street'   => 'Rue Joss',
            'addr:city'     => 'Douala',
        ], 'osm:node/test-tags');

        $listing = app(FacilityImportReviewService::class)->accept($review, (string) $this->admin()->id);

        $this->assertSame('+237 6 99 00 32 07', $listing->phone_primary);
        $this->assertSame('Rue Joss, Douala', $listing->address);
    }

    /**
     * A candidate with nothing usable must still fall back to the placeholder,
     * not write a fragment that renders as a dead Call button.
     */
    public function test_an_unusable_phone_falls_back_to_the_placeholder(): void
    {
        foreach (['N/A', '', '12', 'unknown'] as $i => $junk) {
            $review = $this->review([
                'name'  => 'Pharmacie Sans Numero',
                'phone' => $junk,
            ], 'gplaces:test-junk-' . $i);

            $listing = app(FacilityImportReviewService::class)->accept($review, (string) $this->admin()->id);

            $this->assertSame(
                CareFacility::PHONE_PLACEHOLDER,
                $listing->phone_primary,
                "'{$junk}' is not a dialable number and must not be written as one"
            );
            $this->assertNull($listing->dialablePhone());
        }
    }

    public function test_no_payload_at_all_still_accepts(): void
    {
        $review = $this->review(['name' => 'Clinique Sans Payload'], 'gplaces:test-bare');

        $listing = app(FacilityImportReviewService::class)->accept($review, (string) $this->admin()->id);

        $this->assertSame(CareFacility::PHONE_PLACEHOLDER, $listing->phone_primary);
        $this->assertSame('Douala, Cameroon', $listing->address);
    }
}
