<?php

namespace App\Modules\Telemedicine\Services;

use App\Models\Teleconsultation;
use App\Models\TelemedicineConsent;
use App\Models\VirtualWaitingRoom;
use App\Models\CallSession;
use App\Models\Visit;
use App\Models\VisitTimeline;

/**
 * TelemedicineService — Module 18 (Telemedicine)
 *
 * Orchestrates the telemedicine consultation lifecycle:
 * schedule → consent → waiting room → call session → note → close.
 *
 * OpesCare disclaimer: The platform facilitates connections and records.
 * Clinical decisions remain the provider's sole responsibility.
 */
class TelemedicineService
{
    /**
     * Schedule a new teleconsultation.
     */
    public function schedule(array $data): Teleconsultation
    {
        $consultation = Teleconsultation::create([
            'visit_id'       => $data['visit_id'] ?? null,
            'patient_id'     => $data['patient_id'],
            'facility_id'    => $data['facility_id'],
            'provider_id'    => $data['provider_id'],
            'status'         => 'scheduled',
            'platform'       => $data['platform'] ?? 'own',
            'scheduled_at'   => $data['scheduled_at'],
        ]);

        if (isset($data['visit_id'])) {
            VisitTimeline::record($data['visit_id'], 'teleconsultation_scheduled', [
                'teleconsultation_id' => $consultation->id,
            ]);
        }

        return $consultation;
    }

    /**
     * Move consultation to waiting room (patient joins).
     */
    public function admitToWaitingRoom(Teleconsultation $consultation, string $patientId): VirtualWaitingRoom
    {
        $consultation->update(['status' => 'waiting']);

        return VirtualWaitingRoom::create([
            'facility_id'          => $consultation->facility_id,
            'teleconsultation_id'  => $consultation->id,
            'patient_id'           => $patientId,
            'status'               => 'waiting',
            'joined_at'            => now(),
        ]);
    }

    /**
     * Start the active call session.
     */
    public function startCall(Teleconsultation $consultation, array $sessionData = []): CallSession
    {
        $consultation->update([
            'status'     => 'active',
            'started_at' => now(),
        ]);

        // Update waiting room status
        VirtualWaitingRoom::where('teleconsultation_id', $consultation->id)
            ->where('status', 'called')
            ->update(['status' => 'joined']);

        $session = CallSession::create([
            'teleconsultation_id' => $consultation->id,
            'session_provider'    => $sessionData['session_provider'] ?? 'own',
            'external_session_id' => $sessionData['external_session_id'] ?? null,
            'status'              => 'active',
            'video_enabled'       => $sessionData['video_enabled'] ?? true,
            'audio_enabled'       => $sessionData['audio_enabled'] ?? true,
            'recording_enabled'   => $sessionData['recording_enabled'] ?? false,
            'started_at'          => now(),
        ]);

        if ($consultation->visit_id) {
            VisitTimeline::record($consultation->visit_id, 'teleconsultation_started', [
                'teleconsultation_id' => $consultation->id,
                'call_session_id'     => $session->id,
            ]);
        }

        return $session;
    }

    /**
     * End the call and mark consultation completed.
     */
    public function endCall(Teleconsultation $consultation, CallSession $session): void
    {
        $session->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        $durationSeconds = $session->durationSeconds();

        $consultation->update([
            'status'           => 'completed',
            'ended_at'         => now(),
            'duration_seconds' => $durationSeconds,
        ]);

        if ($consultation->visit_id) {
            VisitTimeline::record($consultation->visit_id, 'teleconsultation_completed', [
                'teleconsultation_id' => $consultation->id,
                'duration_seconds'    => $durationSeconds,
            ]);
        }
    }

    /**
     * Cancel a scheduled or waiting consultation.
     */
    public function cancel(Teleconsultation $consultation, string $reason): void
    {
        $consultation->update([
            'status'               => 'cancelled',
            'cancellation_reason'  => $reason,
            'ended_at'             => now(),
        ]);

        if ($consultation->visit_id) {
            VisitTimeline::record($consultation->visit_id, 'teleconsultation_cancelled', [
                'teleconsultation_id' => $consultation->id,
                'reason'              => $reason,
            ]);
        }
    }
}
