<?php

namespace App\Modules\Telemedicine\Services;

use App\Models\Teleconsultation;
use App\Models\CallSession;
use App\Models\AuditEvent;

/**
 * CallProviderService — Manages video/audio call session lifecycle for telemedicine.
 *
 * OpesCare integrates with external call providers (WebRTC-based or third-party).
 * This service abstracts call initiation, token generation, and call recording policy.
 *
 * Privacy rules:
 *  - Recording requires explicit patient consent captured before session start
 *  - Call metadata is logged but call content is never stored by default
 *  - Session tokens expire after call completion or timeout (30 minutes max)
 */
class CallProviderService
{
    /**
     * Initiate a call session for a teleconsultation.
     * Returns a CallSession with provider-specific room credentials.
     *
     * @throws \RuntimeException if consent not recorded
     */
    public function initiateCall(string $teleconsultationId, string $initiatedBy): CallSession
    {
        $consult = Teleconsultation::findOrFail($teleconsultationId);

        if (! $consult->consent_obtained) {
            throw new \RuntimeException(
                'Telemedicine consent must be obtained before initiating a call session.'
            );
        }

        $session = CallSession::create([
            'teleconsultation_id' => $teleconsultationId,
            'initiated_by'        => $initiatedBy,
            'status'              => 'initiated',
            'room_id'             => $this->generateRoomId($teleconsultationId),
            'expires_at'          => now()->addMinutes(30),
        ]);

        AuditEvent::create([
            'actor_id' => $initiatedBy,
            'action'   => 'telemedicine.call_initiated',
            'module'   => 'telemedicine',
            'metadata' => [
                'teleconsultation_id' => $teleconsultationId,
                'session_id'          => $session->id,
            ],
        ]);

        return $session;
    }

    public function endCall(string $sessionId, string $endedBy): CallSession
    {
        $session = CallSession::findOrFail($sessionId);
        $session->update([
            'status'   => 'ended',
            'ended_by' => $endedBy,
            'ended_at' => now(),
        ]);

        AuditEvent::create([
            'actor_id' => $endedBy,
            'action'   => 'telemedicine.call_ended',
            'module'   => 'telemedicine',
            'metadata' => [
                'session_id'  => $sessionId,
                'duration_s'  => $session->created_at->diffInSeconds(now()),
            ],
        ]);

        return $session->fresh();
    }

    private function generateRoomId(string $teleconsultationId): string
    {
        return 'opes-' . substr(hash('sha256', $teleconsultationId . now()->timestamp), 0, 16);
    }
}
