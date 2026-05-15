<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\IntegrationGateway\Services\SynchronizationEngineService;
use Exception;

class IntegrationController extends Controller
{
    protected SynchronizationEngineService $syncService;

    public function __construct(SynchronizationEngineService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function pushEncounter(Request $request)
    {
        $facilityId = $request->attributes->get('facility_id');
        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey) {
            return response()->json(['error' => 'Idempotency-Key header is required.'], 400);
        }

        try {
            $payload = $request->all();

            $existing = $this->syncService->checkIdempotency($facilityId, $idempotencyKey, $payload);
            if ($existing) {
                return response()->json($existing->response_body, $existing->response_status_code);
            }

            $idempotencyRecord = $this->syncService->reserveIdempotencyKey($facilityId, $idempotencyKey, $payload);

            $responseBody = [
                'status' => 'accepted',
                'message' => 'Encounter pushed to patient timeline successfully.'
            ];
            $this->syncService->completeIdempotencyRecord($idempotencyRecord, $responseBody, 200);

            return response()->json($responseBody, 200);

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'conflict')) {
                return response()->json(['error' => $e->getMessage()], 409);
            }
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
