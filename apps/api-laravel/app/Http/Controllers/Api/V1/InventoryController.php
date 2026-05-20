<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\SupplyChainService;
use App\Modules\Inventory\Services\PharmacyInventoryService;
use App\Modules\Inventory\Services\StockAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * InventoryController — Inventory & Supply Chain API.
 *
 * Covers pharmacy stock, medical supplies, reorder management,
 * goods receipts, and stock audits.
 */
class InventoryController extends Controller
{
    public function __construct(
        private SupplyChainService      $supplyChain,
        private PharmacyInventoryService $pharmacy,
        private StockAuditService       $stockAudit
    ) {}

    // ── Inventory Items ─────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->supplyChain->listItems($request->input('facility_id'), $request->all())
        );
    }

    public function updateStock(Request $request, string $itemId): JsonResponse
    {
        $validated = $request->validate([
            'adjustment_type' => ['required', 'in:addition,reduction,write_off,correction'],
            'quantity'        => ['required', 'numeric'],
            'reason'          => ['required', 'string'],
            'batch_number'    => ['nullable', 'string'],
            'expiry_date'     => ['nullable', 'date'],
        ]);

        return response()->json(
            $this->supplyChain->adjustStock($itemId, $validated, $request->user()->id)
        );
    }

    // ── Reorder Management ─────────────────────────────────────────────────

    public function getLowStockItems(Request $request): JsonResponse
    {
        return response()->json(
            $this->supplyChain->getLowStockItems($request->input('facility_id'))
        );
    }

    public function createPurchaseOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid'],
            'supplier_id' => ['required', 'uuid'],
            'items'       => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'uuid'],
            'items.*.quantity'          => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'        => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json(
            $this->supplyChain->createPurchaseOrder($validated, $request->user()->id),
            201
        );
    }

    public function receiveGoods(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'items'          => ['required', 'array'],
            'items.*.id'     => ['required', 'uuid'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string'],
            'items.*.expiry_date'  => ['nullable', 'date'],
        ]);

        return response()->json(
            $this->supplyChain->receiveGoods($orderId, $validated['items'], $request->user()->id)
        );
    }

    // ── Stock Audits ───────────────────────────────────────────────────────

    public function openAudit(Request $request): JsonResponse
    {
        $validated = $request->validate(['facility_id' => ['required', 'uuid']]);
        return response()->json(
            $this->stockAudit->openAudit($validated['facility_id'], $request->user()->id),
            201
        );
    }

    public function closeAudit(Request $request, string $auditId): JsonResponse
    {
        return response()->json(
            $this->stockAudit->closeAudit($auditId, $request->user()->id)
        );
    }
}
