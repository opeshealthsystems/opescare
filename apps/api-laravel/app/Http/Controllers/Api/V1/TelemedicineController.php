<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
            'scheduled_at'  => ['required', 'date', 'after:now'],
            'reason'        => ['required', 'string', 'max:500'],
        ]);

        return response()->json(
            $this->teleconsult->book($validated, $request->user()->id),
            201
        );
    }

    public function recordConsent(Request $request, string $consultId): JsonResponse
    {
        $validated = $request->validate([
            'method'          => ['required', 'in:digital_signature,verbal_recorded,written_uploaded'],
            'consent_text'    => ['nullable', 'string'],
            'recording_consent' => ['required', 'boolean'],
        ]);

        return response()->json(
            $this->consent->record($consultId, $validated, $request->user()->id)
        );
    }

    public function joinWaitingRoom(Request $request, string $consultId): JsonResponse
    {
        return response()->json(
            $this->waitingRoom->join($consultId, $request->user()->id)
        );
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
        return response()->json($this->teleconsult->get($consultId));
    }

    public function cancel(Request $request, string $consultId): JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string']]);
        return response()->json(
            $this->teleconsult->cancel($consultId, $validated['reason'], $request->user()->id)
        );
    }
}
