<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IntegrationClient;

class VerifyIntegrationClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = $request->header('X-Client-ID');
        $clientSecret = $request->header('X-Client-Secret');

        if (!$clientId || !$clientSecret) {
            return response()->json(['error' => 'Missing integration credentials.'], 401);
        }

        $client = IntegrationClient::where('client_id', $clientId)
            ->where('client_secret', $clientSecret)
            ->first();

        if (!$client || $client->status !== 'active') {
            return response()->json(['error' => 'Invalid or inactive integration client.'], 403);
        }

        $request->attributes->add([
            'integration_client' => $client,
            'facility_id' => $client->facility_id,
        ]);

        return $next($request);
    }
}
