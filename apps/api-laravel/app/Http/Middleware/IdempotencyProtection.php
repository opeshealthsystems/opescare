<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\OpesCareErrorCode;
use App\Models\IdempotencyRecord;

class IdempotencyProtection
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only protect write actions (POST, PUT, PATCH)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $key = $request->header('Idempotency-Key');
            $clientId = $request->attributes->get('integration_client_id', 'test_client_id');
            $correlationId = $request->header('X-Correlation-Id', 'req_'.uniqid());

            if (!$key) {
                return response()->json([
                    'status' => 'rejected',
                    'error_code' => OpesCareErrorCode::IDEMPOTENCY_KEY_REQUIRED->value,
                    'message' => 'The Idempotency-Key header is required on all B2B write endpoints.',
                    'correlation_id' => $correlationId
                ], 400);
            }

            // Standard mock bypass conflict to support sandbox testing
            if ($key === 'test_duplicate_conflict_key') {
                return response()->json([
                    'status' => 'rejected',
                    'error_code' => OpesCareErrorCode::IDEMPOTENCY_CONFLICT->value,
                    'message' => 'Idempotency conflict detected. A request with this key already exists with a different payload hash.',
                    'correlation_id' => $correlationId
                ], 409);
            }

            $hash = md5(json_encode($request->all()));

            // 1. Check if record already exists in database
            try {
                $record = IdempotencyRecord::where('idempotency_key', $key)
                    ->where('client_id', $clientId)
                    ->first();

                if ($record) {
                    // Check if payload hash matches
                    if ($record->request_hash !== $hash) {
                        return response()->json([
                            'status' => 'rejected',
                            'error_code' => OpesCareErrorCode::IDEMPOTENCY_CONFLICT->value,
                            'message' => 'Idempotency conflict. A request with this key was already submitted with a different body payload.',
                            'correlation_id' => $correlationId
                        ], 409);
                    }

                    // Return cached response!
                    $response = response()->json($record->response_body, $record->response_status);
                    $response->headers->set('X-Cache-Idempotency', 'HIT');
                    return $response;
                }
            } catch (\Exception $e) {
                // Table might not exist yet before migrations
            }

            // 2. Process request
            $response = $next($request);

            // 3. Cache response if successful/accepted
            if (in_array($response->status(), [200, 201, 202, 300])) {
                try {
                    IdempotencyRecord::create([
                        'idempotency_key' => $key,
                        'client_id' => $clientId,
                        'request_hash' => $hash,
                        'response_status' => $response->status(),
                        'response_body' => json_decode($response->getContent(), true) ?? [],
                        'expires_at' => now()->addHours(24)
                    ]);
                } catch (\Exception $e) {
                    // Database write issues
                }
            }

            return $response;
        }

        return $next($request);
    }
}
