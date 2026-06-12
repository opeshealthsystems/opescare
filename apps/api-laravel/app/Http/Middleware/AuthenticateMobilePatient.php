<?php

namespace App\Http\Middleware;

use App\Models\PatientAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMobilePatient
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (!$bearer) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $prefix = substr($bearer, 0, 12);

        $token = PatientAccessToken::where('token_prefix', $prefix)
            ->where('expires_at', '>', now())
            ->first();

        if (!$token || !Hash::check($bearer, $token->token_hash)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->attributes->set('patient_id', $token->patient_id);
        $request->attributes->set('patient_token', $token);

        // Resolve the patient's linked user account (users.patient_id) so
        // downstream controllers can attribute actions to a user identity
        // without ever trusting caller-supplied user_id values.
        $linkedUserId = \App\Models\User::where('patient_id', $token->patient_id)->value('id');
        if ($linkedUserId) {
            $request->attributes->set('patient_user_id', $linkedUserId);
        }

        return $next($request);
    }
}
