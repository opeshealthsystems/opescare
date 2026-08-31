<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Teleconsultation;
use App\Models\User;
use App\Modules\Telemedicine\Services\TelemedicineConsentService;
use App\Modules\Telemedicine\Services\TelemedicineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile Patient API — Telemedicine
 *
 * Real patient-facing entry point onto the Telemedicine module (Module 18).
 * The existing `Api\V1\TelemedicineController` (book/consent/waiting-room/
 * call) is B2B-only, behind VerifyIntegrationClient — unusable from the
 * patient bearer-token context, so it is not reused here.
 *
 * Deliberately calls TelemedicineService::startCall() (not
 * CallProviderService::initiateCall()) to create the CallSession row —
 * initiateCall() writes room_id/initiated_by/expires_at, none of which are
 * in the call_sessions migration or the model's $fillable, so those values
 * are silently dropped and the resulting session is unusable. startCall()
 * writes only schema-correct columns.
 *
 * There is no provider-side counterpart in this build to "call" a waiting
 * patient in, so join() progresses scheduled -> waiting -> active in one
 * patient-initiated step once consent is on file (self-service telehealth,
 * matching platform=own). No native video/WebRTC SDK is wired into this
 * Expo app — join()/end() manage the real consultation + call-session
 * lifecycle server-side; the screen renders that real state rather than a
 * simulated video feed.
 */
class MobileTelemedicineController extends Controller
{
    public function __construct(
        private readonly TelemedicineService $teleconsult,
        private readonly TelemedicineConsentService $consentService,
    ) {
    }

    /** GET /api/mobile/telemedicine/consultations */
    public function index(Request $request): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $scope = $request->query('scope', 'upcoming');

        $query = Teleconsultation::where('patient_id', $patientId)
            ->orderBy('scheduled_at', $scope === 'past' ? 'desc' : 'asc');

        match ($scope) {
            'upcoming' => $query->whereIn('status', ['scheduled', 'waiting', 'active']),
            'past'     => $query->whereIn('status', ['completed', 'cancelled', 'failed']),
            default    => null,
        };

        $consultations = $query->limit(50)->get();

        $providerIds = $consultations->pluck('provider_id')->filter()->unique()->values();
        $providers = $providerIds->isEmpty()
            ? collect()
            : User::whereIn('id', $providerIds)->get(['id', 'name'])->keyBy('id');

        $data = $consultations->map(fn (Teleconsultation $c) => $this->format($c, $providers->get($c->provider_id)));

        return response()->json(['data' => $data->values()]);
    }

    /** GET /api/mobile/telemedicine/consultations/{id} */
    public function show(Request $request, string $id): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $consultation = Teleconsultation::where('id', $id)
            ->where('patient_id', $patientId)
            ->with(['consent', 'waitingRoom', 'callSession', 'facility:id,name'])
            ->firstOrFail();

        $provider = $consultation->provider_id
            ? User::where('id', $consultation->provider_id)->first(['id', 'name'])
            : null;

        $data = $this->format($consultation, $provider);
        $data['facility_name'] = $consultation->facility?->name;

        $data['consent'] = $consultation->consent ? [
            'consented'    => (bool) $consultation->consent->consented,
            'method'       => $consultation->consent->consent_method,
            'consented_at' => $consultation->consent->consented_at?->toISOString(),
            'revoked_at'   => $consultation->consent->revoked_at?->toISOString(),
        ] : null;

        $data['waiting_room'] = $consultation->waitingRoom ? [
            'status'                 => $consultation->waitingRoom->status,
            'estimated_wait_minutes' => $consultation->waitingRoom->estimated_wait_minutes,
        ] : null;

        $data['call_session'] = $consultation->callSession ? [
            'status'        => $consultation->callSession->status,
            'started_at'    => $consultation->callSession->started_at?->toISOString(),
            'video_enabled' => (bool) $consultation->callSession->video_enabled,
            'audio_enabled' => (bool) $consultation->callSession->audio_enabled,
        ] : null;

        return response()->json(['data' => $data]);
    }

    /** POST /api/mobile/telemedicine/consultations/{id}/consent */
    public function consent(Request $request, string $id): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $validated = $request->validate(['recording_consent' => ['required', 'boolean']]);

        $consultation = Teleconsultation::where('id', $id)->where('patient_id', $patientId)->firstOrFail();

        $consent = $this->consentService->grantConsent(
            $consultation,
            $patientId,
            'digital_signature',
            'v1',
            null,
        );

        return response()->json(['data' => [
            'consented'    => (bool) $consent->consented,
            'method'       => $consent->consent_method,
            'consented_at' => $consent->consented_at?->toISOString(),
        ]], 201);
    }

    /** POST /api/mobile/telemedicine/consultations/{id}/join */
    public function join(Request $request, string $id): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $consultation = Teleconsultation::where('id', $id)->where('patient_id', $patientId)->firstOrFail();

        if (! $this->consentService->canProceed($consultation)) {
            return response()->json([
                'error_code' => 'TELEMEDICINE_CONSENT_REQUIRED',
                'message'    => 'Consent is required before joining this video visit.',
            ], 422);
        }

        if (in_array($consultation->status, ['completed', 'cancelled', 'failed'], true)) {
            return response()->json([
                'error_code' => 'INVALID_STATUS',
                'message'    => "This visit is {$consultation->status} and cannot be joined.",
            ], 422);
        }

        if (! $consultation->waitingRoom) {
            $this->teleconsult->admitToWaitingRoom($consultation, $patientId);
        }

        $session = $consultation->callSession()->first();
        if (! $session || $session->status !== 'active') {
            $session = $this->teleconsult->startCall($consultation->fresh(), [
                'session_provider' => 'webrtc',
                'video_enabled'    => true,
                'audio_enabled'    => true,
            ]);
        }

        $consultation->refresh();

        return response()->json(['data' => [
            'status'      => $consultation->status,
            'started_at'  => $consultation->started_at?->toISOString(),
            'call_session' => [
                'status'     => $session->status,
                'started_at' => $session->started_at?->toISOString(),
            ],
        ]]);
    }

    /** POST /api/mobile/telemedicine/consultations/{id}/end */
    public function end(Request $request, string $id): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $consultation = Teleconsultation::where('id', $id)->where('patient_id', $patientId)->firstOrFail();

        $session = $consultation->callSession;
        if (! $session) {
            return response()->json([
                'error_code' => 'NO_ACTIVE_SESSION',
                'message'    => 'There is no active call session to end.',
            ], 422);
        }

        $this->teleconsult->endCall($consultation, $session);
        $consultation->refresh();

        return response()->json(['data' => [
            'status'           => $consultation->status,
            'duration_seconds' => $consultation->duration_seconds,
        ]]);
    }

    private function format(Teleconsultation $c, ?User $provider): array
    {
        return [
            'id'               => $c->id,
            'status'           => $c->status,
            'platform'         => $c->platform,
            'provider_id'      => $c->provider_id,
            'provider_name'    => $provider?->name,
            'scheduled_at'     => $c->scheduled_at?->toISOString(),
            'started_at'       => $c->started_at?->toISOString(),
            'ended_at'         => $c->ended_at?->toISOString(),
            'duration_seconds' => $c->duration_seconds,
        ];
    }

    private function resolvePatientId(Request $request): string
    {
        $patientId = $request->attributes->get('patient_id');
        if (! $patientId) {
            abort(401, 'Unauthenticated.');
        }

        return $patientId;
    }
}
