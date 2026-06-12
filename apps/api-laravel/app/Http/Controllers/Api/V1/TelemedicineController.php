<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Teleconsultation;
use App\Modules\Telemedicine\Services\TelemedicineService;
use App\Modules\Telemedicine\Services\TelemedicineConsentService;
use App\Modules\Telemedicine\Services\VirtualWaitingRoomService;
use App\Modules\Telemedicine\Services\CallProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TelemedicineController — Telemedicine & Video Consultation API.
 *
 * CONSENT REQUIRED: Every telemedicine session requires explicit patient consent
 * before the call is initiated. The system blocks call creation without consent.
 *
 * Privacy: Call content is never recorded or stored by default.
 * Recording requires additional explicit consent.
 */
class TelemedicineController extends Controller
{
    public function __construct(
        private TelemedicineService         $teleconsult,
        private TelemedicineConsentService  $consent,
        private VirtualWaitingRoomService   $waitingRoom,
        private CallProviderService         $callProvider
    ) {}

    public function book(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'    => ['required', 'uuid'],
            'provider_id'   => ['required', 'uuid'],
            'facility_id'   => ['required', 'uuid'],
            'visit_id'      => ['nullable', 'uuid'],
            'platform'      => ['nullable', 'in:own,zoom,meet,teams'],
            'scheduled_at'  => ['required', 'date', 'after:now'],
            'reason'        => ['required', 'string', 'max:500'],
        ]);

        return response()->json(
            $this->teleconsult->schedule($validated),
            201
        );
    }

    public function recordConsent(Request $request, string $consultId): JsonResponse
    {
        $validated = $request->validate([
            'method'          => ['required', 'in:digital_signature,verbal_recorded,written_uploaded'],
            'consent_text_version' => ['nullable', 'string'],
            'witnessed_by'    => ['nullable', 'uuid'],
            'recording_consent' => ['required', 'boolean'],
        ]);

        $consultation = Teleconsultation::findOrFail($consultId);

        $consent = $this->consent->grantConsent(
            $consultation,
            $consultation->patient_id,
            $validated['method'],
            $validated['consent_text_version'] ?? 'v1',
            $validated['witnessed_by'] ?? $request->user()->id
        );

        return response()->json($consent);
    }

    public function joinWaitingRoom(Request $request, string $consultId): JsonResponse
    {
        $consultation = Teleconsultation::findOrFail($consultId);

        if (! $this->consent->canProceed($consultation)) {
            return response()->json([
                'message'    => 'Telemedicine consent must be recorded before joining the waiting room.',
                'error_code' => 'TELEMEDICINE_CONSENT_REQUIRED',
            ], 422);
        }

        $entry = $this->teleconsult->admitToWaitingRoom($consultation, $consultation->patient_id);

        return response()->json([
            'waiting_room_entry'     => $entry,
            'estimated_wait_minutes' => $this->waitingRoom->estimateWait($consultation->facility_id),
        ]);
    }

    public function initiateCall(Request $request, string $consultId): JsonResponse
    {
        $session = $this->callProvider->initiateCall($consultId, $request->user()->id);
        return response()->json([
            'session_id' => $session->id,
            'room_id'    => $session->room_id,
            'expires_at' => $session->expires_at,
        ]);
    }

    public function endCall(Request $request, string $sessionId): JsonResponse
    {
        return response()->json(
            $this->callProvider->endCall($sessionId, $request->user()->id)
        );
    }

    public function show(string $consultId): JsonResponse
    {
        return response()->json(
            Teleconsultation::with(['consent', 'waitingRoom', 'callSession'])->findOrFail($consultId)
        );
    }

    public function cancel(Request $request, string $consultId): JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string']]);

        $consultation = Teleconsultation::findOrFail($consultId);
        $this->teleconsult->cancel($consultation, $validated['reason']);

        return response()->json($consultation->fresh());
    }
}
