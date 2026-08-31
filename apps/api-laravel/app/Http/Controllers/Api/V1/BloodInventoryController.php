<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BloodInventoryResource;
use App\Modules\Inventory\Services\BloodInventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * BloodInventoryController — Blood Bank Inventory API.
 *
 * Manages blood unit inventory per facility.
 * Tracks blood group, component, available units, expiry, and safety flags.
 *
 * SAFETY RULE: Units flagged is_expired, is_quarantined, or is_unsafe
 * must never be allocated to a patient. Flags should trigger notifications
 * to the responsible clinician/blood bank manager.
 *
 * Routes protected by VerifyIntegrationClient middleware.
 * facility_id always from middleware attributes.
 *
 * Endpoints:
 *  GET   /v1/inventory/blood              — list blood inventory for facility
 *  GET   /v1/inventory/blood/summary      — summary counts (total/expired/unsafe)
 *  POST  /v1/inventory/blood              — upsert a blood group × component row
 *  POST  /v1/inventory/blood/{item}/adjust   — increment or decrement available units
 *  PATCH /v1/inventory/blood/{item}/flags    — set safety flags
 */
class BloodInventoryController extends Controller
{
    public function __construct(private readonly BloodInventoryService $service) {}

    /**
     * List blood inventory for the authenticated facility.
     * ?blood_group=A+ &component=whole_blood
     */
    public function index(Request $request): JsonResponse
    {
        $facilityId = $request->attributes->get('facility_id');
        if (!$facilityId) {
            return response()->json(['message' => __('api.facility_unresolved_id')], 422);
        }

        $request->validate([
            'blood_group' => ['nullable', 'string'],
            'component'   => ['nullable', 'string'],
        ]);

        $items = $this->service->list($facilityId, $request->only(['blood_group', 'component']));

        return response()->json(['facility_id' => $facilityId, 'data' => BloodInventoryResource::collection($items)]);
    }

    /**
     * Summary — total units, groups covered, expired/unsafe/quarantined counts.
     */
    public function summary(Request $request): JsonResponse
    {
        $facilityId = $request->attributes->get('facility_id');
        if (!$facilityId) {
            return response()->json(['message' => __('api.facility_unresolved_id')], 422);
        }

        return response()->json([
            'facility_id' => $facilityId,
            'data'        => $this->service->summary($facilityId),
        ]);
    }

    /**
     * Upsert a blood group × component inventory row.
     *
     * Body: {
     *   blood_group: A+|A-|B+|B-|AB+|AB-|O+|O-,
     *   component: whole_blood|packed_cells|plasma|platelets|cryoprecipitate,
     *   available_units: integer,
     *   expiry_date?: date
     * }
     */
    public function upsert(Request $request): JsonResponse
    {
        $facilityId = $request->attributes->get('facility_id');
        if (!$facilityId) {
            return response()->json(['message' => __('api.facility_unresolved_id')], 422);
        }

        $validated = $request->validate([
            'blood_group'     => ['required', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'component'       => ['required', 'string', 'in:whole_blood,packed_cells,plasma,platelets,cryoprecipitate'],
            'available_units' => ['required', 'integer', 'min:0'],
            'expiry_date'     => ['nullable', 'date'],
        ]);

        $item = $this->service->upsertUnit($facilityId, $validated);

        return response()->json(['message' => __('api.blood_inventory_updated'), 'data' => BloodInventoryResource::make($item)], 201);
    }

    /**
     * Adjust available unit count (add or subtract).
     *
     * Body: { delta: integer (>=1), direction: add|subtract }
     */
    public function adjust(string $itemId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'delta'     => ['required', 'integer', 'min:1'],
            'direction' => ['required', 'in:add,subtract'],
        ]);

        try {
            $item = $this->service->adjustUnits($itemId, $validated['delta'], $validated['direction']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => __('api.inventory_item_not_found')], 404);
        }

        return response()->json([
            'message'         => __('api.units_direction', ['direction' => $validated['direction'] . 'ed']),
            'available_units' => $item->available_units,
            'data'            => BloodInventoryResource::make($item),
        ]);
    }

    /**
     * Set safety flags on a blood inventory item.
     * Allowed flags: is_expired, is_quarantined, is_unsafe
     *
     * Body: { is_expired?: bool, is_quarantined?: bool, is_unsafe?: bool }
     */
    public function setFlags(string $itemId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'is_expired'     => ['nullable', 'boolean'],
            'is_quarantined' => ['nullable', 'boolean'],
            'is_unsafe'      => ['nullable', 'boolean'],
        ]);

        if (empty($validated)) {
            return response()->json(['message' => __('api.safety_flag_required')], 422);
        }

        try {
            $item = $this->service->setFlags($itemId, $validated);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => __('api.inventory_item_not_found')], 404);
        }

        return response()->json(['message' => __('api.safety_flags_updated'), 'data' => BloodInventoryResource::make($item)]);
    }
}
