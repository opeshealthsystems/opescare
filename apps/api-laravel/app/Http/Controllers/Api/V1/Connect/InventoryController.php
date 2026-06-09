<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use App\Enums\OpesCareErrorCode;
use App\Events\AuditEventCreated;
use Illuminate\Http\Request;

/**
 * InventoryController
 *
 * Handles pharmacy and blood-bank stock synchronisation from external HIS.
 *
 * SECURITY:
 *  - facility_id MUST come from $request->attributes->get('facility_id') only
 *    (set by VerifyIntegrationClient middleware). An integration client may only
 *    sync stock for the facility bound to its bearer token.
 *  - facility_reference in the request body is informational (the HIS-side
 *    reference number) and is NEVER used for scoping or authorisation.
 *
 * [H-1 FIX] Removed any path where facility_reference from the request body
 *   could substitute for or override the middleware-resolved facility_id.
 *   The authenticated facility_id is now the only authorisation scope used.
 *
 * [M-1 FIX] Expired/unsafe items are now collected and reported in bulk
 *   rather than short-circuiting on the first offending item. The caller
 *   receives a complete list of which items were rejected and why.
 */
class InventoryController extends Controller
{
    public function syncPharmacyStock(Request $request)
    {
        // [H-1 FIX] facility_id from middleware only — never from body
        $facilityId = $request->attributes->get('facility_id');
        if (! $facilityId) {
            return response()->json([
                'status'     => 'error',
                'error_code' => 'FACILITY_UNRESOLVABLE',
                'message'    => 'Bearer token does not carry a facility scope. Request rejected.',
            ], 403);
        }

        $validated = $request->validate([
            'facility_reference' => ['required', 'string', 'max:100'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.drug_code'  => ['required', 'string'],
            'items.*.quantity'   => ['required', 'integer', 'min:0'],
            'items.*.expiry_date'=> ['nullable', 'date'],
        ]);

        $clientId      = $request->attributes->get('integration_client_id', 'unknown_client');
        $correlationId = $request->header('X-Correlation-Id') ?? ('req_' . bin2hex(random_bytes(8)));

        // [M-1 FIX] Collect ALL expired items before rejecting — give caller full picture
        $expiredItems = [];
        foreach ($validated['items'] as $index => $item) {
            if (
                isset($item['expiry_date']) &&
                strtotime($item['expiry_date']) < time()
            ) {
                $expiredItems[] = [
                    'index'       => $index,
                    'drug_code'   => $item['drug_code'],
                    'expiry_date' => $item['expiry_date'],
                ];
            }
        }

        if (! empty($expiredItems)) {
            return response()->json([
                'status'         => 'rejected',
                'error_code'     => OpesCareErrorCode::UNSAFE_STOCK_STATUS->value,
                'message'        => 'Expired stock synchronisation blocked. Remove the listed items and retry.',
                'expired_items'  => $expiredItems,
                'correlation_id' => $correlationId,
            ], 422);
        }

        event(new AuditEventCreated(
            'pharmacy_stock_synced',
            $clientId,
            $facilityId,          // real facility, not a magic UUID
            'system_sync',
            $correlationId,
            [
                'items_count'        => count($validated['items']),
                'facility_reference' => $validated['facility_reference'],
            ]
        ));

        return response()->json([
            'status'              => 'synced',
            'facility_id'         => $facilityId,
            'facility_reference'  => $validated['facility_reference'],
            'synced_items_count'  => count($validated['items']),
            'timestamp'           => time(),
            'correlation_id'      => $correlationId,
        ], 200);
    }

    public function syncBloodStock(Request $request)
    {
        // [H-1 FIX] facility_id from middleware only
        $facilityId = $request->attributes->get('facility_id');
        if (! $facilityId) {
            return response()->json([
                'status'     => 'error',
                'error_code' => 'FACILITY_UNRESOLVABLE',
                'message'    => 'Bearer token does not carry a facility scope. Request rejected.',
            ], 403);
        }

        $validated = $request->validate([
            'facility_reference'        => ['required', 'string', 'max:100'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.component_code'    => ['required', 'string'],
            'items.*.units'             => ['required', 'integer', 'min:0'],
            'items.*.screening_status'  => ['required', 'string'],
        ]);

        $clientId      = $request->attributes->get('integration_client_id', 'unknown_client');
        $correlationId = $request->header('X-Correlation-Id') ?? ('req_' . bin2hex(random_bytes(8)));

        // [M-1 FIX] Collect ALL unsafe components before rejecting
        $unsafeItems = [];
        foreach ($validated['items'] as $index => $item) {
            if ($item['screening_status'] !== 'screened_safe') {
                $unsafeItems[] = [
                    'index'            => $index,
                    'component_code'   => $item['component_code'],
                    'screening_status' => $item['screening_status'],
                ];
            }
        }

        if (! empty($unsafeItems)) {
            return response()->json([
                'status'         => 'rejected',
                'error_code'     => OpesCareErrorCode::UNSAFE_BLOOD_STATUS->value,
                'message'        => 'Unscreened or unsafe blood component sync is forbidden. Remove the listed items and retry.',
                'unsafe_items'   => $unsafeItems,
                'correlation_id' => $correlationId,
            ], 422);
        }

        event(new AuditEventCreated(
            'blood_stock_synced',
            $clientId,
            $facilityId,          // real facility
            'system_sync',
            $correlationId,
            [
                'components_count'   => count($validated['items']),
                'facility_reference' => $validated['facility_reference'],
            ]
        ));

        return response()->json([
            'status'                    => 'synced',
            'facility_id'               => $facilityId,
            'facility_reference'        => $validated['facility_reference'],
            'synced_components_count'   => count($validated['items']),
            'timestamp'                 => time(),
            'correlation_id'            => $correlationId,
        ], 200);
    }
}
