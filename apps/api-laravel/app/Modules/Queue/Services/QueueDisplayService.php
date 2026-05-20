<?php

namespace App\Modules\Queue\Services;

use App\Models\QueueTicket;
use App\Models\QueueStation;
use App\Models\QueueDisplaySetting;

/**
 * QueueDisplayService — Manages public-facing queue display data.
 *
 * PRIVACY RULE: The public queue display MUST NEVER show patient full names.
 * Only ticket numbers, masked identifiers, or first names may be displayed.
 * Display settings are configurable per facility and per station.
 *
 * Display shows: ticket numbers, station assignments, estimated wait time.
 */
class QueueDisplayService
{
    /**
     * Get sanitized queue data for public display board.
     * Returns ONLY ticket number and station — no PHI.
     */
    public function getPublicDisplayData(string $facilityId): array
    {
        $settings = QueueDisplaySetting::where('facility_id', $facilityId)->first();

        $calledTickets = QueueTicket::where('facility_id', $facilityId)
            ->where('status', 'called')
            ->with('station')
            ->orderByDesc('called_at')
            ->limit(10)
            ->get();

        $waitingCount = QueueTicket::where('facility_id', $facilityId)
            ->where('status', 'waiting')
            ->count();

        return [
            'called_tickets' => $calledTickets->map(fn ($ticket) => [
                'ticket_number' => $ticket->ticket_number,
                'station_name'  => $ticket->station?->name,
                'display_name'  => $this->getMaskedDisplay($ticket, $settings),
                'called_at'     => $ticket->called_at?->toTimeString(),
            ])->toArray(),
            'waiting_count' => $waitingCount,
            'last_updated'  => now()->toIso8601String(),
        ];
    }

    /**
     * Get station-level queue for staff display (internal, may show more detail).
     */
    public function getStationQueue(string $stationId): \Illuminate\Database\Eloquent\Collection
    {
        return QueueTicket::where('queue_station_id', $stationId)
            ->whereIn('status', ['waiting', 'called'])
            ->orderBy('priority_level', 'desc')
            ->orderBy('created_at')
            ->get();
    }

    private function getMaskedDisplay(QueueTicket $ticket, ?QueueDisplaySetting $settings): string
    {
        // Default: show only ticket number — never full name
        if (! $settings || $settings->display_mode === 'ticket_only') {
            return $ticket->ticket_number;
        }

        // First-name-only mode if facility has enabled it
        if ($settings->display_mode === 'first_name' && $ticket->patient_first_name) {
            return $ticket->patient_first_name . ' - ' . $ticket->ticket_number;
        }

        return $ticket->ticket_number;
    }
}
