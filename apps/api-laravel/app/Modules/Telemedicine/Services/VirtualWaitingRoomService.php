<?php

namespace App\Modules\Telemedicine\Services;

use App\Models\Teleconsultation;
use App\Models\VirtualWaitingRoom;

/**
 * VirtualWaitingRoomService — Module 18 (Telemedicine)
 *
 * Manages the virtual waiting room state for telemedicine sessions.
 */
class VirtualWaitingRoomService
{
    /**
     * Estimate wait time for a patient in the waiting room.
     * Returns approximate minutes based on active sessions ahead.
     */
    public function estimateWait(string $facilityId): int
    {
        $activeCount = VirtualWaitingRoom::where('facility_id', $facilityId)
            ->where('status', 'waiting')
            ->count();

        // Estimate 10 minutes per patient ahead
        return max(0, $activeCount * 10);
    }

    /**
     * Call the next waiting patient for a consultation.
     */
    public function callNext(string $facilityId): ?VirtualWaitingRoom
    {
        $next = VirtualWaitingRoom::where('facility_id', $facilityId)
            ->where('status', 'waiting')
            ->orderBy('joined_at')
            ->first();

        if ($next) {
            $next->call();
        }

        return $next;
    }

    /**
     * Expire stale waiting room entries (e.g., patient timed out).
     */
    public function expireStale(int $timeoutMinutes = 30): int
    {
        return VirtualWaitingRoom::where('status', 'waiting')
            ->where('joined_at', '<', now()->subMinutes($timeoutMinutes))
            ->update(['status' => 'expired']);
    }

    /**
     * Get all currently waiting patients for a facility.
     */
    public function waitingPatients(string $facilityId): \Illuminate\Database\Eloquent\Collection
    {
        return VirtualWaitingRoom::where('facility_id', $facilityId)
            ->where('status', 'waiting')
            ->orderBy('joined_at')
            ->get();
    }
}
