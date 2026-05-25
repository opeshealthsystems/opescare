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

        // Sandbox/Testing Bypass credentials — only active in test environment
        if (app()->environment('testing') && $clientId === 'test_client_id' && $clientSecret === 'test_client_secret') {
            $request->attributes->add([
                'integration_client_id' => 'test_client_id',
                'provider_id' => '00000000-0000-0000-0000-000000000001', // System B2B account for testing
                'facility_id' => '00000000-0000-0000-0000-000000000001',
            ]);
            return $next($request);
        }

        // All real secrets are stored as SHA-256 hashes (set by DeveloperPortalController::storeApp)
        try {
            $hashedSecret = hash('sha256', $clientSecret);

            $client = IntegrationClient::where('client_id', $clientId)
                ->where('client_secret', $hashedSecret)
                ->first();

            if (!$client || $client->status !== 'active') {
                return response()->json(['error' => 'Invalid or inactive integration client.'], 403);
            }

            $request->attributes->add([
                'integration_client'    => $client,
                'integration_client_id' => $client->client_id,
                'facility_id'           => $client->facility_id,
                'provider_id'           => $client->created_by, // Map to user who created/owns this integration
            ]);
        } catch (\Exception $e) {
            // DB table may not exist yet in local setup before migrations are run
            // Log the actual error but return generic message to prevent information disclosure
            \Log::error('integration_client_verification_failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'authentication_error',
                'message' => 'An internal error occurred during client authentication. Please try again.'
            ], 500);
        }

        return $next($request);
    }
}
