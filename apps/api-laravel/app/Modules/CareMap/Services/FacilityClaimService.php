<?php

namespace App\Modules\CareMap\Services;

use App\Models\CareFacility;
use App\Models\FacilityClaim;
use Illuminate\Support\Facades\DB;

class FacilityClaimService
{
    /**
     * Submit a claim request for a facility
     */
    public function submitClaim($facilityId, $userId, $reason)
    {
        // Check if facility already has a pending claim from this user
        $exists = FacilityClaim::where('facility_id', $facilityId)
            ->where('claimant_user_id', $userId)
            ->where('claim_status', 'submitted')
            ->exists();

        if ($exists) {
            throw new \Exception('FACILITY_CLAIM_ALREADY_EXISTS');
        }

        return FacilityClaim::create([
            'facility_id' => $facilityId,
            'claimant_user_id' => $userId,
            'claim_reason' => $reason,
            'claim_status' => 'submitted',
        ]);
    }

    /**
     * Approve a claim request
     */
    public function approveClaim($claimId, $adminId)
    {
        $claim = FacilityClaim::findOrFail($claimId);
        
        DB::transaction(function () use ($claim, $adminId) {
            $claim->update([
                'claim_status' => 'approved',
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
            ]);

            // Link claimant as partner of the facility
            $facility = CareFacility::findOrFail($claim->facility_id);
            $facility->update([
                'partner_id' => $claim->claimant_user_id,
                'verification_status' => 'partner_verified',
            ]);
        });

        return $claim;
    }

    /**
     * Reject a claim request
     */
    public function rejectClaim($claimId, $adminId, $notes)
    {
        $claim = FacilityClaim::findOrFail($claimId);
        
        $claim->update([
            'claim_status' => 'rejected',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $claim;
    }
}
