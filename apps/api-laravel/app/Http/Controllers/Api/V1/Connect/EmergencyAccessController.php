<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\AllergyRecord;
use App\Models\Diagnosis;
use App\Models\MedicalIdAccessEvent;
use Illuminate\Support\Facades\Validator;

class EmergencyAccessController extends Controller
{
    public function pullEmergencyProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'health_id' => 'required|string',
            'reason' => 'required|string|min:10',
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
            $this->logAccess($validated['health_id'], null, 'emergency_access', 'pull_emergency_profile', 'denied', $request);
            return response()->json([
                'status' => 'invalid',
                'error_code' => 'HEALTH_ID_NOT_FOUND',
                'message' => 'This Health ID could not be verified.'
            ], 404);
        }

        // 2. Audit Log (Critical for emergency access)
        $this->logAccess($validated['health_id'], $patient->id, 'emergency_access', 'pull_emergency_profile', 'success', $request);

        // 3. Fetch real clinical data from the database
        $allergies = AllergyRecord::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->get(['substance', 'severity', 'status'])
            ->toArray();

        $chronicConditions = Diagnosis::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->get(['code', 'display_name'])
            ->toArray();

        // 4. Construct Emergency Profile
        $emergencyProfile = [
            'identity' => [
                'health_id' => $patient->health_id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'sex' => $patient->sex,
                'date_of_birth' => $patient->date_of_birth,
            ],
            'emergency_contact'  => $patient->emergency_contact ?? 'Not provided',
            // blood_type is not stored per patient in this schema.
            // Returning null rather than fabricating a value — a wrong blood type
            // in an emergency is lethal. Store in PatientIdentityProfile when available.
            'blood_type'         => null,
            'allergies'          => $allergies,
            'chronic_conditions' => $chronicConditions,
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Emergency profile retrieved. This action has been audited.',
            'profile' => $emergencyProfile
        ], 200);
    }

    private function logAccess(string $healthId, ?string $patientId, string $purpose, string $accessType, string $result, Request $request)
    {
        $clientId  = $request->attributes->get('integration_client_id');
        $facilityId = $request->attributes->get('facility_id');

        MedicalIdAccessEvent::create([
            'patient_id' => $patientId,
            'health_id' => $healthId,
            'actor_id' => $clientId,
            'actor_type' => 'facility_staff',
            'facility_id' => $facilityId,
            'access_type' => $accessType,
            'purpose' => $purpose,
            'result' => $result,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
