<?php

namespace App\Modules\Insurance\Services;

use App\Models\ClaimDecision;
use App\Models\ClaimItem;
use App\Models\InsuranceClaim;
use Illuminate\Support\Facades\DB;

class ClaimService
{
    /**
     * Create a new claim draft.
     */
    public function createClaim(array $data): InsuranceClaim
    {
        return DB::transaction(function () use ($data) {
            $claim = InsuranceClaim::create([
                'patient_insurance_policy_id' => $data['policy_id'],
                'invoice_id' => $data['invoice_id'] ?? null,
                'preauthorization_request_id' => $data['preauthorization_request_id'] ?? null,
                'facility_id' => $data['facility_id'],
                'claim_number' => InsuranceClaim::generateClaimNumber(),
                'status' => 'draft',
                'claimed_amount' => $data['claimed_amount'] ?? 0,
                'submitted_by' => $data['actor_id'],
                'submission_notes' => $data['notes'] ?? null,
            ]);

            // Attach line items if provided
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $unitPrice = (float) ($item['unit_price'] ?? 0);
                    ClaimItem::create([
                        'insurance_claim_id' => $claim->id,
                        'description' => $item['description'],
                        'service_code' => $item['service_code'] ?? null,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => round($qty * $unitPrice, 2),
                        'status' => 'pending',
                    ]);
                }

                // Recalculate claimed_amount from items
                $total = $claim->items()->sum(DB::raw('quantity * unit_price'));
                $claim->update(['claimed_amount' => $total]);
            }

            return $claim->load('items');
        });
    }

    /**
     * Submit a draft claim to the payer.
     */
    public function submit(string $claimId, string $actorId): InsuranceClaim
    {
        $claim = InsuranceClaim::findOrFail($claimId);

        if (!$claim->canBeSubmitted()) {
            throw new \Exception('CLAIM_NOT_SUBMITTABLE');
        }

        $claim->update([
            'status' => 'submitted',
            'submitted_by' => $actorId,
            'submitted_at' => now(),
        ]);

        return $claim->fresh();
    }

    /**
     * Record a payer decision on a claim.
     */
    public function decide(string $claimId, string $actorId, array $data): ClaimDecision
    {
        return DB::transaction(function () use ($claimId, $actorId, $data) {
            $claim = InsuranceClaim::findOrFail($claimId);

            if (!$claim->canReceiveDecision()) {
                throw new \Exception('CLAIM_NOT_REVIEWABLE');
            }

            $decision = ClaimDecision::create([
                'insurance_claim_id' => $claim->id,
                'decided_by' => $actorId,
                'decision' => $data['decision'],
                'approved_amount' => $data['approved_amount'] ?? null,
                'reason' => $data['reason'],
                'missing_information' => $data['missing_information'] ?? null,
                'decided_at' => now(),
            ]);

            $newStatus = match ($data['decision']) {
                'approved' => 'approved',
                'partially_approved' => 'partially_approved',
                'rejected' => 'rejected',
                'more_information_required' => 'more_information_required',
                'disputed' => 'disputed',
                default => 'under_review',
            };

            $updates = [
                'status' => $newStatus,
                'decided_at' => now(),
            ];

            if (isset($data['approved_amount'])) {
                $updates['approved_amount'] = $data['approved_amount'];
            }

            $claim->update($updates);

            return $decision;
        });
    }

    /**
     * Cancel a claim.
     */
    public function cancel(string $claimId, string $actorId): InsuranceClaim
    {
        $claim = InsuranceClaim::findOrFail($claimId);

        if (!in_array($claim->status, ['draft', 'submitted', 'under_review', 'more_information_required'])) {
            throw new \Exception('CLAIM_NOT_CANCELLABLE');
        }

        $claim->update(['status' => 'cancelled']);

        return $claim->fresh();
    }
}
