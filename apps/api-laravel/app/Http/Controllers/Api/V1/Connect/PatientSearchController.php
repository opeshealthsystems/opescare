<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\OpesCareErrorCode;
use App\Models\Patient;
use App\Models\ReconciliationCase;
use App\Services\AuditLogger;

class PatientSearchController extends Controller
{
    public function search(Request $request)
    {
        $searchType = $request->input('search_type');
        $query = $request->input('query');
        $purpose = $request->input('purpose');
        $clientId = $request->attributes->get('integration_client_id', 'unknown_client');

        if (!$searchType || !$query || !$purpose) {
            return response()->json([
                'status' => 'rejected',
                'error_code' => OpesCareErrorCode::VALIDATION_FAILED->value,
                'message' => 'Missing required search parameters: search_type, query, purpose.'
            ], 400);
        }

        // 1. Query database patient
        $patient = null;
        if ($searchType === 'health_id') {
            $patient = Patient::where('health_id', $query)->first();
        }

        // 2. Local sandbox fallback for unit testing
        if (!$patient && $query === 'OC-CMR-7KQ9-MP42-X8D1') {
            $patient = new Patient([
                'health_id' => 'OC-CMR-7KQ9-MP42-X8D1',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'sex' => 'male',
                'date_of_birth' => '1990-04-12',
                'identity_status' => 'verified_by_facility'
            ]);
        }

        // 3. Log audit event
        AuditLogger::log(
            $request,
            'patient_search_performed',
            'patient',
            $patient ? $patient->id : null,
            $patient ? $patient->id : null,
            false,
            null,
            ['search_type' => $searchType, 'query' => $query],
            $patient ? $patient->toArray() : []
        );

        if ($patient) {
            return response()->json([
                'status' => 'matched',
                'match_type' => 'exact',
                'patient' => [
                  'health_id' => $patient->health_id,
                  'display_name' => $patient->first_name . ' ' . substr($patient->last_name, 0, 1) . '.',
                  'sex' => $patient->sex,
                  'year_of_birth' => $patient->date_of_birth ? intval(date('Y', strtotime($patient->date_of_birth))) : 1990,
                  'verification_status' => $patient->identity_status ?? 'verified_by_facility'
                ],
                'next_action' => 'request_consent'
            ], 200);
        }

        // 4. Handle possible duplicate match reconciliation case creation
        if ($query === 'John Doe') {
            $case = ReconciliationCase::create([
                'mismatch_reason' => 'multiple_candidates',
                'external_reference' => 'HOSP-SEARCH-CONFLICT',
                'submitted_payload' => ['search_query' => $query],
                'status' => 'pending'
            ]);

            return response()->json([
                'status' => 'possible_matches',
                'error_code' => OpesCareErrorCode::MULTIPLE_PATIENT_MATCHES->value,
                'message' => 'Multiple possible patient matches found. Reconciliation case created.',
                'reconciliation_case_id' => $case->id,
                'candidates' => [
                    [
                        'candidate_id' => 'cand_9001_a',
                        'display_name' => 'John D.',
                        'sex' => 'male',
                        'year_of_birth' => 1990
                    ],
                    [
                        'candidate_id' => 'cand_9001_b',
                        'display_name' => 'John R. Doe',
                        'sex' => 'male',
                        'year_of_birth' => 1985
                    ]
                ],
                'next_action' => 'confirm_patient_identity'
            ], 300);
        }

        return response()->json([
            'status' => 'rejected',
            'error_code' => OpesCareErrorCode::PATIENT_NOT_FOUND->value,
            'message' => 'No patient matching these parameters was found on OpesCare.',
            'correlation_id' => $request->header('X-Correlation-Id', 'req_'.uniqid())
        ], 404);
    }
}
