<?php

namespace App\Modules\OperationalFlow\Services;

use App\Models\Visit;
use Illuminate\Support\Facades\DB;

/**
 * VisitManagementService
 *
 * Manages visit lifecycle: create, status transitions, and closure.
 * Statuses: open → in_triage → in_consultation → awaiting_billing → awaiting_discharge → completed
 */
class VisitManagementService
{
    /** Valid status transitions */
    private const TRANSITIONS = [
        'open'                 => ['in_triage', 'in_consultation', 'awaiting_billing', 'awaiting_discharge', 'completed', 'cancelled'],
        'in_triage'            => ['in_consultation', 'awaiting_billing', 'awaiting_discharge', 'completed', 'cancelled'],
        'in_consultation'      => ['awaiting_lab', 'awaiting_billing', 'awaiting_pharmacy', 'awaiting_discharge', 'completed', 'cancelled'],
        'awaiting_lab'         => ['in_consultation', 'awaiting_billing', 'awaiting_discharge', 'completed', 'cancelled'],
        'awaiting_pharmacy'    => ['awaiting_billing', 'awaiting_discharge', 'completed', 'cancelled'],
        'awaiting_billing'     => ['awaiting_discharge', 'completed', 'cancelled'],
        'awaiting_discharge'   => ['completed', 'cancelled'],
        'completed'            => [],
        'cancelled'            => [],
        'abandoned'            => [],
    ];

    /**
     * Create a new visit.
     */
    public function createVisit(array $data): Visit
    {
        return Visit::create([
            'patient_id'  => $data['patient_id'],
            'facility_id' => $data['facility_id'],
            'provider_id' => $data['provider_id'] ?? null,
            'visit_type'  => $data['visit_type'] ?? 'general',
            'status'      => 'open',
            'started_at'  => now(),
        ]);
    }

    /**
     * Advance visit to a new status.
     */
    public function transition(string $visitId, string $newStatus, string $actorId): Visit
    {
        return DB::transaction(function () use ($visitId, $newStatus, $actorId) {
            $visit = Visit::findOrFail($visitId);

            $allowed = self::TRANSITIONS[$visit->status] ?? [];
            if (!in_array($newStatus, $allowed)) {
                throw new \Exception("VISIT_INVALID_TRANSITION: {$visit->status} → {$newStatus}");
            }

            $updates = ['status' => $newStatus];

            if ($newStatus === 'completed') {
                $updates['ended_at'] = now();
            }

            $visit->update($updates);

            return $visit->fresh();
        });
    }

    /**
     * Close/complete a visit.
     */
    public function complete(string $visitId, string $actorId): Visit
    {
        $visit = Visit::findOrFail($visitId);

        if ($visit->status === 'completed') {
            return $visit;
        }

        $visit->update([
            'status'   => 'completed',
            'ended_at' => now(),
        ]);

        return $visit->fresh();
    }

    /**
     * Cancel a visit.
     */
    public function cancel(string $visitId, string $actorId): Visit
    {
        $visit = Visit::findOrFail($visitId);

        if (in_array($visit->status, ['completed', 'cancelled'])) {
            throw new \Exception('VISIT_NOT_CANCELLABLE');
        }

        $visit->update([
            'status'   => 'cancelled',
            'ended_at' => now(),
        ]);

        return $visit->fresh();
    }

    /**
     * Get allowed next statuses for a visit.
     */
    public function allowedTransitions(Visit $visit): array
    {
        return self::TRANSITIONS[$visit->status] ?? [];
    }
}
