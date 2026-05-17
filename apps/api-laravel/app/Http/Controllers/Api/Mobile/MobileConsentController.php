<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\OpesCareErrorCode;

class MobileConsentController extends Controller
{
    public function listConsentRequests(Request $request)
    {
        return response()->json([
            'requests' => [
                [
                    'consent_request_id' => 'crq_mock_01',
                    'requesting_facility' => 'Metro Emergency General Clinic',
                    'purpose' => 'treatment',
                    'requested_scopes' => ['patient.summary', 'allergies.read', 'medications.read'],
                    'expires_at' => date('Y-m-d\TH:i:s\Z', time() + 3600)
                ]
            ]
        ], 200);
    }

    public function approveConsent(Request $request, $id)
    {
        return response()->json([
            'status' => 'granted',
            'consent_grant_id' => 'cgt_mobile_grant_01',
            'message' => 'Consent grant successfully created for requesting facility.'
        ], 200);
    }

    public function denyConsent(Request $request, $id)
    {
        return response()->json([
            'status' => 'denied',
            'message' => 'Consent challenge denied. External API access rejected.'
        ], 200);
    }

    public function revokeConsent(Request $request, $id)
    {
        return response()->json([
            'status' => 'revoked',
            'message' => 'Consent grant revoked. Existing tokens invalidated.'
        ], 200);
    }
}
