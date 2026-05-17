<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\OpesCareErrorCode;
use App\Events\AuditEventCreated;

class InventoryController extends Controller
{
    public function syncPharmacyStock(Request $request)
    {
        $facilityRef = $request->input('facility_reference');
        $items = $request->input('items');
        $clientId = $request->attributes->get('integration_client_id', 'unknown_client');
        $correlationId = $request->header('X-Correlation-Id', 'req_'.uniqid());

        if (!$facilityRef || !is_array($items)) {
            return response()->json([
                'status' => 'rejected',
                'error_code' => OpesCareErrorCode::VALIDATION_FAILED->value,
                'message' => 'Missing facility_reference or items array.'
            ], 400);
        }

        // Exclude expired stock items from synchronizations
        foreach ($items as $item) {
            if (isset($item['expiry_date']) && strtotime($item['expiry_date']) < time()) {
                return response()->json([
                    'status' => 'rejected',
                    'error_code' => OpesCareErrorCode::UNSAFE_STOCK_STATUS->value,
                    'message' => 'Expired stock synchronizations are strictly blocked from OpesCare verified locators.',
                    'correlation_id' => $correlationId
                ], 422);
            }
        }

        event(new AuditEventCreated(
            'pharmacy_stock_synced',
            $clientId,
            null,
            'system_sync',
            $correlationId,
            ['items_count' => count($items)]
        ));

        return response()->json([
            'status' => 'synced',
            'facility_reference' => $facilityRef,
            'synced_items_count' => count($items),
            'timestamp' => time()
        ], 200);
    }

    public function syncBloodStock(Request $request)
    {
        $facilityRef = $request->input('facility_reference');
        $items = $request->input('items');
        $clientId = $request->attributes->get('integration_client_id', 'unknown_client');
        $correlationId = $request->header('X-Correlation-Id', 'req_'.uniqid());

        if (!$facilityRef || !is_array($items)) {
            return response()->json([
                'status' => 'rejected',
                'error_code' => OpesCareErrorCode::VALIDATION_FAILED->value,
                'message' => 'Missing facility_reference or items array.'
            ], 400);
        }

        // Validate safe blood parameters
        foreach ($items as $item) {
            if (isset($item['screening_status']) && $item['screening_status'] !== 'screened_safe') {
                return response()->json([
                    'status' => 'rejected',
                    'error_code' => OpesCareErrorCode::UNSAFE_BLOOD_STATUS->value,
                    'message' => 'Unscreened or unsafe blood component sync is forbidden.',
                    'correlation_id' => $correlationId
                ], 422);
            }
        }

        event(new AuditEventCreated(
            'blood_stock_synced',
            $clientId,
            null,
            'system_sync',
            $correlationId,
            ['components_count' => count($items)]
        ));

        return response()->json([
            'status' => 'synced',
            'facility_reference' => $facilityRef,
            'synced_components_count' => count($items),
            'timestamp' => time()
        ], 200);
    }
}
