<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\ConsentRequest;
use App\Models\MedicalIdAccessEvent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ConsentController extends Controller
{
    public function requestConsent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'health_id' => 'required|string',
            'purpose' => 'required|string|in:treatment,pharmacy_dispense,lab_order,insurance_claim,consultation',
            'requested_scope' => 'required|array',
            'duration_minutes' => 'required|integer|min:15|max:1440',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'error_code' => 'INVALID_PAYLOAD',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $validated = $validator->validated();

        // 1. Verify Patient
        $patient = Patient::where('health_id', $validated['health_id'])->first();

        if (!$patient) {
            $this->logAccess($validated['health_id'], null, $validated['purpose'], 'request_consent', 'denied', $request);
            return response()->json([
                'status' => 'invalid',
                'error_code' => 'HEALTH_ID_NOT_FOUND',
                'message' => 'This Health ID could not be verified.'
            ], 404);
        }

        // 2. Status Checks
        if (in_array($patient->verification_status, ['suspended', 'deceased', 'entered_in_error']) || 
            in_array($patient->identity_status, ['suspended', 'deceased', 'entered_in_error'])) {
            $this->logAccess($validated['health_id'], $patient->id, $validated['purpose'], 'request_consent', 'denied', $request);
            return response()->json([
                'status' => 'rejected',
                'error_code' => 'HEALTH_ID_SUSPENDED',
                'message' => 'Consent cannot be requested for this Health ID due to its status.'
            ], 403);
        }

        // 3. Create Consent Request
        // We auto-approve for the demo if 'is_demo' is true, otherwise it remains 'pending'.
        $consentStatus = $patient->is_demo ? 'granted' : 'pending';

        $consentRequest = ConsentRequest::create([
            'patient_id' => $patient->id,
            'requesting_facility_id' => Str::uuid(), // Mocking facility ID
            'requesting_user_id' => Str::uuid(),     // Mocking user ID
            'purpose' => $validated['purpose'],
            'requested_scope' => $validated['requested_scope'],
            'duration_minutes' => $validated['duration_minutes'],
            'status' => $consentStatus,
        ]);

        // 4. Audit Log
        $this->logAccess($validated['health_id'], $patient->id, $validated['purpose'], 'request_consent', 'success', $request);

        return response()->json([
            'status' => 'success',
            'consent_id' => $consentRequest->id,
            'consent_status' => $consentStatus,
            'message' => $consentStatus === 'granted' ? 'Consent granted for demo purposes.' : 'Consent request sent to patient.'
        ], 200);
    }

    private function logAccess(string $healthId, ?string $patientId, string $purpose, string $accessType, string $result, Request $request)
    {
        MedicalIdAccessEvent::create([
            'patient_id' => $patientId,
            'health_id' => $healthId,
            'actor_id' => Str::uuid(), // Authenticated user ID in real app
            'actor_type' => 'facility_staff',
            'facility_id' => Str::uuid(), // Authenticated facility ID
            'access_type' => $accessType,
            'purpose' => $purpose,
            'result' => $result,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
