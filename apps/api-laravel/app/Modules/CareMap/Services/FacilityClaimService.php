<?php

namespace App\Modules\CareMap\Services;

use App\Models\CareFacility;
use App\Models\FacilityClaim;
use App\Models\FacilityRegistry;
use Illuminate\Support\Facades\DB;

class FacilityClaimService
{
    /**
     * Submit a claim request.
     *
     * For registry claims, pass $registryEntryId (the facility_registry.id being claimed).
     * For CareMap-only claims, leave $registryEntryId null.
     *
     * @param  string      $facilityId      facilities.id (the operational Facility doing the claiming)
     * @param  string      $userId          The user submitting the claim
     * @param  string|null $reason          Optional reason text
     * @param  string|null $registryEntryId facility_registry.id being claimed (registry flow only)
     */
    public function submitClaim($facilityId, $userId, $reason = null, $registryEntryId = null)
    {
        // Prevent duplicate pending claims for the same facility + claimant
        $exists = FacilityClaim::where('facility_id', $facilityId)
            ->where('claimant_user_id', $userId)
            ->where('claim_status', 'submitted')
            ->exists();

        if ($exists) {
            throw new \Exception('FACILITY_CLAIM_ALREADY_EXISTS');
        }

        // For registry claims: also block if the registry entry is already claimed
        if ($registryEntryId) {
            $alreadyClaimed = FacilityRegistry::where('id', $registryEntryId)
                ->whereNotNull('claimed_facility_id')
                ->exists();

            if ($alreadyClaimed) {
                throw new \Exception('REGISTRY_ENTRY_ALREADY_CLAIMED');
            }
        }

        return FacilityClaim::create([
            'facility_id'       => $facilityId,
            'registry_entry_id' => $registryEntryId,
            'claimant_user_id'  => $userId,
            'claim_reason'      => $reason,
            'claim_status'      => 'submitted',
        ]);
    }

    /**
     * Approve a claim request.
     *
     * Two flows depending on whether registry_entry_id is set:
     *
     * A) Registry claim (registry_entry_id set):
     *    1. Stamps facility_registry with claimed_facility_id + claimed_at + status='verified'
     *    2. Creates or activates a care_facilities listing from the registry entry data
     *
     * B) CareMap claim (registry_entry_id null):
     *    1. Finds the care_facilities listing linked to the operational Facility
     *    2. Sets partner_id + verification_status on it
     */
    public function approveClaim($claimId, $adminId)
    {
        $claim = FacilityClaim::findOrFail($claimId);

        DB::transaction(function () use ($claim, $adminId) {
            $claim->update([
                'claim_status' => 'approved',
                'reviewed_by'  => $adminId,
                'reviewed_at'  => now(),
            ]);

            if ($claim->registry_entry_id) {
                // ── Registry claim flow ───────────────────────────────────────
                $registryEntry = FacilityRegistry::findOrFail($claim->registry_entry_id);

                // Stamp the registry entry as claimed
                $registryEntry->update([
                    'claimed_facility_id' => $claim->facility_id,
                    'claimed_at'          => now(),
                    'status'              => 'verified',
                ]);

                // Auto-create or activate the care_facilities listing
                $this->upsertCareFacilityFromRegistry($registryEntry, $claim->facility_id, $claim->claimant_user_id);
            } else {
                // ── Original CareMap claim flow ───────────────────────────────
                // Find the care_facilities entry linked to the operational Facility
                // via care_facilities.facility_id (→ facilities.id)
                $careFacility = CareFacility::where('facility_id', $claim->facility_id)->first();

                if ($careFacility) {
                    $careFacility->update([
                        'partner_id'          => $claim->claimant_user_id,
                        'verification_status' => 'partner_verified',
                        'last_verified_at'    => now(),
                    ]);
                }
            }
        });

        return $claim->fresh();
    }

    /**
     * Reject a claim request.
     */
    public function rejectClaim($claimId, $adminId, $notes = null)
    {
        $claim = FacilityClaim::findOrFail($claimId);

        $claim->update([
            'claim_status' => 'rejected',
            'reviewed_by'  => $adminId,
            'reviewed_at'  => now(),
            'review_notes' => $notes,
        ]);

        return $claim;
    }

    /**
     * Create or activate a care_facilities listing from a facility_registry entry.
     * Called on registry claim approval. Never overwrites an active, partner-verified listing.
     */
    private function upsertCareFacilityFromRegistry(
        FacilityRegistry $reg,
        string $facilityId,
        string $partnerId
    ): CareFacility {
        // Check for an existing care_facilities listing linked to this operational Facility
        $existing = CareFacility::where('facility_id', $facilityId)->first();

        if ($existing) {
            $existing->update([
                'partner_id'          => $partnerId,
                'listing_status'      => 'active',
                'verification_status' => 'partner_verified',
                'last_verified_at'    => now(),
            ]);

            return $existing;
        }

        // Create a new care_facilities listing from the registry entry
        return CareFacility::create([
            'facility_id'          => $facilityId,
            'partner_id'           => $partnerId,
            'facility_name'        => $reg->name,
            'facility_type'        => $reg->type,
            'ownership_type'       => $reg->ownership,
            'country_code'         => 'CM',
            'region'               => $reg->region,
            'city'                 => $reg->city ?? '',
            'address'              => $reg->address ?? (($reg->city ?? '') . ', Cameroon'),
            'latitude'             => $reg->gps_lat,
            'longitude'            => $reg->gps_lng,
            'phone_primary'        => $reg->phone ?? 'N/A',
            'email'                => $reg->email,
            'website'              => $reg->website,
            'listing_status'       => 'active',
            'verification_status'  => 'partner_verified',
            'last_verified_at'     => now(),
            'last_profile_update_at' => now(),
        ]);
    }
}
