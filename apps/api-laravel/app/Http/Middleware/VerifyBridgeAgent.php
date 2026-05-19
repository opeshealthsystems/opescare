<?php

namespace App\Http\Middleware;

use App\Models\BridgeAgent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBridgeAgent
{
    /**
     * Authenticate Bridge Agent requests.
     *
     * Header: X-Bridge-Agent-Key: <raw_key>
     * Stored:  SHA-256 hash of raw key
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawKey = $request->header('X-Bridge-Agent-Key');

        if (!$rawKey) {
            return response()->json([
                'error'   => 'missing_credentials',
                'message' => 'X-Bridge-Agent-Key header is required.',
            ], 401);
        }

        $keyHash = hash('sha256', $rawKey);

        try {
            $agent = BridgeAgent::where('agent_key', $keyHash)
                ->where('status', 'active')
                ->first();
        } catch (\Throwable $e) {
            return response()->json(['error' => 'server_error', 'message' => 'Auth check failed.'], 503);
        }

        if (!$agent) {
            return response()->json([
                'error'   => 'invalid_key',
                'message' => 'Invalid or inactive Bridge Agent key.',
            ], 401);
        }

        // Update heartbeat
        $agent->updateQuietly([
            'last_seen_at' => now(),
            'ip_address'   => $request->ip(),
        ]);

        $request->attributes->add([
            'bridge_agent'    => $agent,
            'bridge_agent_id' => $agent->id,
            'facility_id'     => $agent->facility_id,
        ]);

        return $next($request);
    }
}
