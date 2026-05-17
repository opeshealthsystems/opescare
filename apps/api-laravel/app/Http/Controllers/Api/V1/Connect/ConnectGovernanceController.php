<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Governance\Services\ConsentService;
use App\Modules\Governance\Services\EmergencyAccessService;
use App\Models\Patient;

class ConnectGovernanceController extends Controller
{
    private $consentService;
    private $emergencyService;

    public function __construct(
        ConsentService $consentService,
        EmergencyAccessService $emergencyService
    ) {
        $this->consentService = $consentService;
        $this->emergencyService = $emergencyService;
    }

    public function requestConsent(Request $request)
    {
        $patientId = $request->input('patient_id');
        $facilityId = $request->attributes->get('facility_id', '00000000-0000-0000-0000-000000000002');
        $userId = $request->input('requested_by_user_id');
        $purpose = $request->input('purpose', 'treatment');
        $scopes = $request->input('requested_scopes', []);
        $duration = $request->input('duration_minutes', 240);

        if (!$patientId || empty($scopes)) {
            return response()->json(['message' => 'Validation failed.'], 400);
        }

        $consentReq = $this->consentService->requestConsent(
            $patientId,
            $facilityId,
            $userId,
            $purpose,
            $scopes,
            $duration
        );

        return response()->json([
            'status' => 'requested',
            'consent_request_id' => $consentReq->id,
            'message' => 'Consent request transmitted to patient.'
        ], 202);
    }

    public function verifyConsent(Request $request)
    {
        $patientId = $request->input('patient_id');
        $facilityId = $request->attributes->get('facility_id', '00000000-0000-0000-0000-000000000002');
        $userId = $request->input('requested_by_user_id');
        $scope = $request->input('scope');
        $purpose = $request->input('purpose', 'treatment');

        if (!$patientId || !$scope) {
            return response()->json(['message' => 'Validation failed.'], 400);
        }

        $isValid = $this->consentService->verifyAccess($patientId, $facilityId, $userId, $scope, $purpose);

        return response()->json([
            'is_valid' => $isValid,
            'status' => $isValid ? 'granted' : 'denied'
        ], 200);
    }

    public function requestEmergencyAccess(Request $request)
    {
        $patientId = $request->input('patient_id');
        $facilityId = $request->attributes->get('facility_id', '00000000-0000-0000-0000-000000000002');
        $actorId = $request->input('actor_id', '00000000-0000-0000-0000-000000000001');
        $reason = $request->input('reason');

        if (!$patientId || !$reason) {
            return response()->json(['message' => 'Validation failed. Reason is required.'], 400);
        }

        $event = $this->emergencyService->requestEmergencyAccess($patientId, $facilityId, $actorId, $reason);

        return response()->json([
            'status' => 'emergency_authorized',
            'emergency_access_event_id' => $event->id,
            'message' => 'Emergency override activated and audited.'
        ], 201);
    }

    public function getEmergencyProfile(Request $request, $healthId)
    {
        $patient = Patient::where('health_id', $healthId)->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        $purpose = $request->header('X-Purpose-Of-Use');
        $emergencyReason = $request->header('X-Emergency-Reason');

        if ($purpose === 'emergency' && $emergencyReason) {
            \App\Services\AuditLogger::log(
                $request,
                'emergency_profile_pulled',
                'patient',
                $patient->id,
                $patient->id,
                true,
                $emergencyReason
            );
        }

        $profile = $this->emergencyService->buildEmergencyProfile($patient->id);
        $profile['emergency_status'] = 'consent_bypassed_audited';

        return response()->json($profile, 200);
    }
}
