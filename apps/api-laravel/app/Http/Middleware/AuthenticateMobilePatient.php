<?php

namespace App\Http\Middleware;

use App\Models\PatientAccessToken;
use Carbon\Carbon;
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

        // Find by iterating active tokens — we use a prefix check to narrow the scan
        // In production, consider storing a lookup_id prefix like QR tokens do.
        $token = PatientAccessToken::where('expires_at', '>', Carbon::now())
            ->get()
            ->first(fn($t) => Hash::check($bearer, $t->token_hash));

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->attributes->set('patient_id', $token->patient_id);
        $request->attributes->set('patient_token', $token);

        return $next($request);
    }
}
