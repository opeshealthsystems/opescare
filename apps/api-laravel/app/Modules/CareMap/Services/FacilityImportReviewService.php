<?php

namespace App\Modules\CareMap\Services;

use App\Enums\FacilityImportReviewStatus;
use App\Models\CareFacility;
use App\Models\FacilityImportReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The other half of OsmFacilityImporter: what happens to the candidates it
 * refused to decide about.
 *
 * The importer has been parking uncertain candidates in
 * `facility_import_reviews` since the OpenStreetMap run, on the correct
 * principle that a duplicated hospital is worse than a missing one. 439 of them
 * are still `pending` because nothing has ever read the table. This service is
 * what an administrator's accept / merge / reject actually does.
 */
class FacilityImportReviewService
{
    public const SOURCE = 'facility_import_review';

    /**
     * Accept the candidate as a new, distinct facility.
     *
     * Provenance is carried onto the new row (source_system / source_ref /
     * source_attribution) so the importer's partial UNIQUE index on source_ref
     * recognises it next run and does not insert it a second time.
     */
    public function accept(FacilityImportReview $review, string $adminId, ?string $notes = null): CareFacility
    {
        if ($review->status->isDecided()) {
            throw new \Exception('IMPORT_REVIEW_ALREADY_DECIDED');
        }

        $name = $review->displayName();

        if ($name === '') {
            // The reviewer must supply a name for an unnamed element before it
            // can enter a directory people search by name.
            throw new \Exception('IMPORT_REVIEW_NEEDS_NAME');
        }

        return DB::transaction(function () use ($review, $adminId, $notes, $name) {
            $city = $review->candidate_city ?: '';

            $listing = CareFacility::create([
                'facility_name'       => $name,
                'facility_type'       => $review->candidate_type ?: 'clinic',
                'country_code'        => 'CM',
                'region'              => $review->candidate_region,
                'city'                => $city,
                'address'             => $city !== '' ? $city . ', Cameroon' : 'Cameroon',
                'latitude'            => $review->latitude,
                'longitude'           => $review->longitude,
                'phone_primary'       => CareFacility::PHONE_PLACEHOLDER,
                'listing_status'      => 'active',
                // Untouched on purpose. An import is not a verification.
                'verification_status' => 'unverified',
                'source_system'       => $review->source_system,
                'source_ref'          => $review->source_ref,
                'source_attribution'  => $review->source_attribution,
                'source_synced_at'    => now(),
            ]);

            $review->update([
                'status'       => FacilityImportReviewStatus::Imported->value,
                'reviewed_by'  => $adminId,
                'reviewed_at'  => now(),
                'review_notes' => $notes,
            ]);

            DB::table('facility_update_audits')->insert([
                'id'              => (string) Str::uuid(),
                'facility_id'     => $listing->id,
                'actor_id'        => $adminId,
                'actor_type'      => 'admin',
                'field_changed'   => 'listing_created',
                'old_value'       => null,
                'new_value'       => $review->source_system . ':' . $review->source_ref,
                'source'          => self::SOURCE,
                'requires_review' => false,
                'created_at'      => now(),
            ]);

            return $listing;
        });
    }

    /**
     * The candidate is the matched listing under another name. Stamp the
     * upstream reference onto the existing row so the next import run updates
     * that row instead of asking again.
     */
    public function merge(FacilityImportReview $review, string $adminId, ?string $notes = null): CareFacility
    {
        if ($review->status->isDecided()) {
            throw new \Exception('IMPORT_REVIEW_ALREADY_DECIDED');
        }

        $listing = CareFacility::find($review->matched_facility_id);

        if ($listing === null) {
            throw new \Exception('IMPORT_REVIEW_NO_MATCH');
        }

        return DB::transaction(function () use ($review, $listing, $adminId, $notes) {
            // Only claim the source_ref if the row does not already carry one —
            // the partial UNIQUE index allows exactly one row per upstream element.
            if ($listing->source_ref === null) {
                $listing->forceFill([
                    'source_system'      => $review->source_system,
                    'source_ref'         => $review->source_ref,
                    'source_attribution' => $review->source_attribution,
                    'source_synced_at'   => now(),
                ])->save();
            }

            $review->update([
                'status'       => FacilityImportReviewStatus::Merged->value,
                'reviewed_by'  => $adminId,
                'reviewed_at'  => now(),
                'review_notes' => $notes,
            ]);

            DB::table('facility_update_audits')->insert([
                'id'              => (string) Str::uuid(),
                'facility_id'     => $listing->id,
                'actor_id'        => $adminId,
                'actor_type'      => 'admin',
                'field_changed'   => 'source_ref',
                'old_value'       => null,
                'new_value'       => $review->source_system . ':' . $review->source_ref,
                'source'          => self::SOURCE,
                'requires_review' => false,
                'created_at'      => now(),
            ]);

            return $listing;
        });
    }

    /**
     * Park the candidate without deciding it.
     *
     * The queue is 439 rows deep and a good proportion of them cannot be
     * settled from a screen — they need a phone call to the district, or
     * someone who knows the town. Without this, a reviewer's only ways to clear
     * such a row are to guess or to leave it, and guessing on a national
     * facility registry is exactly what this queue exists to prevent.
     *
     * Deferred is not terminal: `isOpen()` still holds, so accept / merge /
     * reject all remain available afterwards.
     */
    public function defer(FacilityImportReview $review, string $adminId, ?string $notes = null): FacilityImportReview
    {
        if ($review->status->isDecided()) {
            throw new \Exception('IMPORT_REVIEW_ALREADY_DECIDED');
        }

        $review->update([
            'status'       => FacilityImportReviewStatus::Deferred->value,
            'reviewed_by'  => $adminId,
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ]);

        return $review->fresh();
    }

    /** Not a facility, or not one this directory lists. Nothing is created. */
    public function reject(FacilityImportReview $review, string $adminId, ?string $notes = null): FacilityImportReview
    {
        if ($review->status->isDecided()) {
            throw new \Exception('IMPORT_REVIEW_ALREADY_DECIDED');
        }

        $review->update([
            'status'       => FacilityImportReviewStatus::Rejected->value,
            'reviewed_by'  => $adminId,
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ]);

        return $review->fresh();
    }
}
