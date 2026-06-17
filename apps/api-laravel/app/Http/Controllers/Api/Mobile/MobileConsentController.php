<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ConsentGrant;
use App\Models\ConsentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileConsentController extends Controller
{
    public function listConsentRequests(Request $request): JsonResponse
    {
        $patientId = $request->attributes->get('patient_id');

        $requests = ConsentRequest::where('patient_id', $patientId)
            ->where('status', 'pending')
            ->with('requestingFacility:id,name')
            ->latest()
            ->get()
            ->map(fn($r) => [
                'consent_request_id' => $r->id,
                'requesting_facility' => $r->requestingFacility?->name ?? 'Unknown Facility',
                'purpose'             => $r->purpose,
                'requested_scopes'    => $r->requested_scope ?? [],
                'expires_at'          => $r->created_at
                    ?->addMinutes($r->duration_minutes ?? 1440)
                    ->toIso8601String(),
            ]);

        return response()->json(['requests' => $requests], 200);
    }

    public function approveConsent(Request $request, string $id): JsonResponse
    {
        $patientId = $request->attributes->get('patient_id');

        $consentRequest = ConsentRequest::where('id', $id)
            ->where('patient_id', $patientId)
            ->where('status', 'pending')
            ->first();

        if (!$consentRequest) {
            return response()->json(['error' => __('api.consent_not_found')], 404);
        }

        $grant = ConsentGrant::create([
            'patient_id'         => $patientId,
            'facility_id'        => $consentRequest->requesting_facility_id,
            'consent_request_id' => $consentRequest->id,
            'authorizing_actor'  => $patientId,
            'scope'              => $consentRequest->requested_scope ?? [],
            'status'             => 'active',
            'expires_at'         => now()->addMinutes($consentRequest->duration_minutes ?? 1440),
        ]);

        $consentRequest->update(['status' => 'approved']);

        return response()->json([
            'status'           => 'granted',
            'consent_grant_id' => $grant->id,
            'message'          => __('api.consent_grant_created'),
        ], 200);
    }

    public function denyConsent(Request $request, string $id): JsonResponse
    {
        $patientId = $request->attributes->get('patient_id');

        $consentRequest = ConsentRequest::where('id', $id)
            ->where('patient_id', $patientId)
            ->where('status', 'pending')
            ->first();

        if (!$consentRequest) {
            return response()->json(['error' => __('api.consent_not_found')], 404);
        }

        $consentRequest->update(['status' => 'denied']);

        return response()->json([
            'status'  => 'denied',
            'message' => __('api.consent_challenge_denied'),
        ], 200);
    }

    public function revokeConsent(Request $request, string $id): JsonResponse
    {
        $patientId = $request->attributes->get('patient_id');

        $grant = ConsentGrant::where('id', $id)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->first();

        if (!$grant) {
            return response()->json(['error' => __('api.consent_grant_not_found')], 404);
        }

        $grant->update(['status' => 'revoked']);

        return response()->json([
            'status'  => 'revoked',
            'message' => __('api.consent_revoked'),
        ], 200);
    }
}
