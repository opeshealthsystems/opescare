<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Identity\HealthIdGeneratorService;
use App\Services\Identity\QrTokenService;
use App\Models\Patient;
use App\Models\MedicalIdAccessEvent;
use App\Enums\MedicalIdErrorCode;
use App\Enums\AuditEventType;
use Illuminate\Support\Facades\Log;

class MedicalIdVerificationController extends Controller
{
    protected $healthIdService;
    protected $qrService;

    public function __construct(HealthIdGeneratorService $healthIdService, QrTokenService $qrService)
    {
        $this->healthIdService = $healthIdService;
        $this->qrService = $qrService;
    }

    /**
     * POST /api/v1/connect/medical-ids/verify
     */
    public function verifyHealthId(Request $request)
    {
        $validated = $request->validate([
            'health_id' => 'required|string',
            'purpose' => 'required|string',
            'requesting_user.external_user_id' => 'nullable|string',
            'requesting_user.role' => 'nullable|string',
        ]);

        $healthId = strtoupper($validated['health_id']);

        // 1. Check format & checksum
        if (!$this->healthIdService->isValid($healthId)) {
            $this->logAccessEvent(null, $healthId, 'verify_health_id', $validated['purpose'], 'denied', $request, 'INVALID_HEALTH_ID_FORMAT');
            return response()->json([
                'status' => 'rejected',
                'error_code' => MedicalIdErrorCode::INVALID_HEALTH_ID_FORMAT->value,
                'message' => 'This Health ID could not be verified. Check the ID and try again.',
            ], 400);
        }

        // 2. Find Patient
        $patient = Patient::where('health_id', $healthId)->first();

        if (!$patient) {
            $this->logAccessEvent(null, $healthId, 'verify_health_id', $validated['purpose'], 'denied', $request, 'HEALTH_ID_NOT_FOUND');
            return response()->json([
                'status' => 'rejected',
                'error_code' => MedicalIdErrorCode::HEALTH_ID_NOT_FOUND->value,
                'message' => 'This Health ID could not be verified. Check the ID and try again.',
            ], 404);
        }

        // 3. Status Checks
        if ($patient->verification_status === 'suspended' || $patient->identity_status === 'suspended') {
            return $this->rejectVerification($patient, $validated['purpose'], $request, MedicalIdErrorCode::HEALTH_ID_SUSPENDED, 'suspended');
        }

        if ($patient->verification_status === 'deceased' || $patient->identity_status === 'deceased') {
            return $this->rejectVerification($patient, $validated['purpose'], $request, MedicalIdErrorCode::HEALTH_ID_DECEASED, 'deceased');
        }

        if ($patient->identity_status === 'entered_in_error' || $patient->verification_status === 'entered_in_error') {
            return $this->rejectVerification($patient, $validated['purpose'], $request, MedicalIdErrorCode::HEALTH_ID_ENTERED_IN_ERROR, 'entered_in_error');
        }

        // 4. Create Audit Event
        $this->logAccessEvent($patient, $healthId, 'verify_health_id', $validated['purpose'], 'success', $request);

        // 5. Return Safe Identity Preview
        return response()->json([
            'status' => 'valid',
            'verification_status' => $patient->verification_status ?? 'provisional',
            'patient_preview' => [
                'display_name' => $this->maskName($patient),
                'sex' => $patient->sex,
                'year_of_birth' => $patient->date_of_birth ? $patient->date_of_birth->year : null,
                'health_id' => $patient->health_id,
            ],
            'next_action' => 'request_consent',
        ]);
    }

    /**
     * POST /api/v1/connect/medical-ids/verify-qr
     */
    public function verifyQr(Request $request)
    {
        $validated = $request->validate([
            'qr_token' => 'required|string',
            'purpose' => 'required|string',
        ]);

        $qrTokenRecord = $this->qrService->verifyToken($validated['qr_token']);

        if (!$qrTokenRecord) {
            return response()->json([
                'status' => 'rejected',
                'error_code' => MedicalIdErrorCode::QR_TOKEN_INVALID->value,
                'message' => 'This QR token is invalid, expired, or revoked.',
            ], 400);
        }

        $patient = $qrTokenRecord->patient;

        // Verify patient status
        if ($patient->verification_status === 'suspended' || $patient->identity_status === 'suspended') {
            return $this->rejectVerification($patient, $validated['purpose'], $request, MedicalIdErrorCode::HEALTH_ID_SUSPENDED, 'suspended');
        }

        $this->logAccessEvent($patient, $patient->health_id, 'verify_qr', $validated['purpose'], 'success', $request);

        return response()->json([
            'status' => 'valid',
            'verification_status' => $patient->verification_status ?? 'provisional',
            'patient_preview' => [
                'display_name' => $this->maskName($patient),
                'sex' => $patient->sex,
                'year_of_birth' => $patient->date_of_birth ? $patient->date_of_birth->year : null,
                'health_id' => $patient->health_id,
            ],
            'next_action' => 'request_consent',
        ]);
    }

    /**
     * Reject Verification Helper
     */
    protected function rejectVerification($patient, $purpose, $request, MedicalIdErrorCode $errorCode, $reason)
    {
        $this->logAccessEvent($patient, $patient->health_id ?? null, 'verify', $purpose, 'denied', $request, $errorCode->value);
        
        return response()->json([
            'status' => 'rejected',
            'error_code' => $errorCode->value,
            'message' => 'Access restricted due to identity status: ' . $reason,
        ], 403);
    }

    /**
     * Mask Name
     * e.g., "Marie Nfor T." -> "Marie N."
     */
    protected function maskName($patient): string
    {
        $firstName = $patient->first_name ?? '';
        $lastName = $patient->last_name ?? '';
        
        if (empty($firstName) && empty($lastName)) {
            return 'Unknown Patient';
        }

        if (empty($lastName)) {
            return $firstName;
        }

        return $firstName . ' ' . mb_substr($lastName, 0, 1) . '.';
    }

    /**
     * Log Access Event
     */
    protected function logAccessEvent($patient, $healthId, $type, $purpose, $result, $request, $reason = null)
    {
        MedicalIdAccessEvent::create([
            'patient_id' => $patient ? $patient->id : null,
            'health_id' => $healthId,
            'actor_id' => $request->user() ? $request->user()->id : null,
            'actor_type' => 'api_client',
            'facility_id' => null, // Would be derived from API auth
            'access_type' => $type,
            'purpose' => $purpose,
            'result' => $result,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $eventType = $result === 'success' ? AuditEventType::EXTERNAL_SYSTEM_VERIFIED_HEALTH_ID : AuditEventType::MEDICAL_ID_ACCESS_DENIED;
        
        Log::info($eventType->value, [
            'health_id' => $healthId,
            'purpose' => $purpose,
            'result' => $result,
            'reason' => $reason
        ]);
    }
}
