<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use App\Enums\BloodGroup;
use App\Enums\OpesCareErrorCode;
use App\Events\AuditEventCreated;
use App\Modules\CareMap\Services\BloodAvailabilityProjector;
use App\Modules\Inventory\Services\BloodInventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
    public function __construct(
        private readonly BloodInventoryService $bloodInventory,
    ) {
    }

    public function syncPharmacyStock(Request $request)
    {
        // [H-1 FIX] facility_id from middleware only — never from body
        $facilityId = $request->attributes->get('facility_id');
        if (! $facilityId) {
            return response()->json([
                'status'     => 'error',
                'error_code' => 'FACILITY_UNRESOLVABLE',
                'message'    => __('api.bearer_no_facility_scope'),
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
                'message'        => __('api.expired_stock_sync_blocked'),
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
                'message'    => __('api.bearer_no_facility_scope'),
            ], 403);
        }

        // A blood inventory row is keyed on (facility_id, blood_group, component),
        // so the payload has to carry the group. It previously did not, which is
        // why this endpoint could validate, audit, answer "synced" and still
        // store nothing — there was no way to express what to store.
        //
        // blood_group is a new required field. Safe to add: blood-stock/sync
        // appears only in the generated public/openapi.json, never in the
        // hand-authored contracts/openapi/opescare-connect-v1.yaml, and no SDK
        // references it. Overloading component_code to encode the group was the
        // alternative and would have been a hack.
        $validated = $request->validate([
            'facility_reference'        => ['required', 'string', 'max:100'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.blood_group'       => ['required', 'string', Rule::in(BloodGroup::values())],
            'items.*.component_code'    => ['required', 'string', Rule::in(BloodAvailabilityProjector::operationalComponents())],
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
                'message'        => __('api.unsafe_blood_sync_forbidden'),
                'unsafe_items'   => $unsafeItems,
                'correlation_id' => $correlationId,
            ], 422);
        }

        // Persist through the service, not a raw write: upsertUnit() re-publishes
        // the patient-facing blood_availability row via BloodAvailabilityProjector,
        // so the Blood Finder stays in step with what the bank actually holds.
        // Every item here passed the screening gate above, so none is unsafe.
        $stored = 0;
        foreach ($validated['items'] as $item) {
            $this->bloodInventory->upsertUnit($facilityId, [
                'blood_group'     => $item['blood_group'],
                'component'       => $item['component_code'],
                'available_units' => $item['units'],
                'is_unsafe'       => false,
            ]);
            $stored++;
        }

        event(new AuditEventCreated(
            'blood_stock_synced',
            $clientId,
            $facilityId,          // real facility
            'system_sync',
            $correlationId,
            [
                'components_count'   => count($validated['items']),
                'stored_rows'        => $stored,
                'facility_reference' => $validated['facility_reference'],
            ]
        ));

        return response()->json([
            'status'                    => 'synced',
            'facility_id'               => $facilityId,
            'facility_reference'        => $validated['facility_reference'],
            'synced_components_count'   => count($validated['items']),
            'stored_rows'               => $stored,
            'timestamp'                 => time(),
            'correlation_id'            => $correlationId,
        ], 200);
    }
}
