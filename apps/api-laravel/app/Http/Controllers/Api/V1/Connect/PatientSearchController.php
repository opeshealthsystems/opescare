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

        return response()->json([
            'status' => 'rejected',
            'error_code' => OpesCareErrorCode::PATIENT_NOT_FOUND->value,
            'message' => 'No patient matching these parameters was found on OpesCare.',
            'correlation_id' => $request->header('X-Correlation-Id', 'req_'.uniqid())
        ], 404);
    }
}
