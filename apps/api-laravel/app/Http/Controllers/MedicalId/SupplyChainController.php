<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\GoodsReceipt;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\StockBatch;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Modules\Inventory\Services\SupplyChainService;
use Illuminate\Http\Request;
use Throwable;

class SupplyChainController extends Controller
{
    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    // ── Dashboard ─────────────────────────────────────────────

    public function index(SupplyChainService $svc)
    {
        $facilityId = $this->demoFacilityId();
        $stats      = $svc->dashboardStats($facilityId);
        $lowStock   = $svc->getLowStockItems($facilityId)->take(10);
        $expiring   = $svc->getExpiringSoonBatches($facilityId, 30)->take(10);
        $expired    = $svc->getExpiredBatches($facilityId)->take(5);
        $recentMovements = StockMovement::where('facility_id', $facilityId)
            ->with('item')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('portals.staff.supply_chain.index',
            compact('stats', 'lowStock', 'expiring', 'expired', 'recentMovements'));
    }

    // ── Inventory Items ───────────────────────────────────────

    public function items(Request $request)
    {
        $facilityId = $this->demoFacilityId();
        $q = InventoryItem::where('facility_id', $facilityId);

        if ($request->filled('category')) $q->where('category', $request->category);
        if ($request->filled('status'))   $q->where('status', $request->status);
        if ($request->filled('search'))   $q->where(fn($sq) =>
            $sq->where('name', 'like', "%{$request->search}%")
               ->orWhere('code', 'like', "%{$request->search}%")
        );

        $items = $q->orderBy('name')->paginate(25)->withQueryString();
        $categories = InventoryItem::categories();

        return view('portals.staff.supply_chain.items', compact('items', 'categories'));
    }

    public function itemStore(Request $request, SupplyChainService $svc)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'code'          => 'nullable|string|max:50',
            'category'      => 'required|string',
            'unit'          => 'required|string|max:30',
            'reorder_level' => 'nullable|integer|min:0',
            'unit_cost'     => 'nullable|numeric|min:0',
        ]);

        try {
            $svc->createItem($this->demoFacilityId(), $request->validated(), $this->demoActorId());
            return redirect()->route('portals.staff.supply.items')
                ->with('success', 'Item created successfully.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Suppliers ─────────────────────────────────────────────

    public function suppliers(Request $request)
    {
        $facilityId = $this->demoFacilityId();
        $suppliers  = Supplier::where('facility_id', $facilityId)
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('portals.staff.supply_chain.suppliers', compact('suppliers'));
    }

    public function supplierStore(Request $request, SupplyChainService $svc)
    {
        $request->validate([
            'name'           => 'required|string|max:150',
            'code'           => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string|max:500',
        ]);

        try {
            $svc->createSupplier($this->demoFacilityId(), $request->validated(), $this->demoActorId());
            return redirect()->route('portals.staff.supply.suppliers')
                ->with('success', 'Supplier added.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Stock (batches/receive) ───────────────────────────────

    public function stock(Request $request)
    {
        $facilityId = $this->demoFacilityId();
        $q = StockBatch::where('facility_id', $facilityId)->with(['item', 'location']);

        if ($request->filled('item')) $q->where('inventory_item_id', $request->item);
        if ($request->filled('status')) $q->where('status', $request->status);

        $batches    = $q->orderByDesc('created_at')->paginate(25)->withQueryString();
        $items      = InventoryItem::where('facility_id', $facilityId)->where('status', 'active')->orderBy('name')->get();
        $locations  = StockLocation::where('facility_id', $facilityId)->where('is_active', true)->get();
        $suppliers  = Supplier::where('facility_id', $facilityId)->where('status', 'active')->orderBy('name')->get();

        return view('portals.staff.supply_chain.stock',
            compact('batches', 'items', 'locations', 'suppliers'));
    }

    public function stockReceive(Request $request, SupplyChainService $svc)
    {
        $request->validate([
            'inventory_item_id' => 'required|uuid',
            'quantity'          => 'required|integer|min:1',
            'location_id'       => 'nullable|uuid',
            'batch_number'      => 'nullable|string|max:80',
            'expiry_date'       => 'nullable|date',
            'unit_cost'         => 'nullable|numeric|min:0',
            'supplier_id'       => 'nullable|uuid',
        ]);

        try {
            $svc->receiveStock($this->demoFacilityId(), $request->validated(), $this->demoActorId());
            return redirect()->route('portals.staff.supply.stock')
                ->with('success', 'Stock received successfully.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function stockAdjust(Request $request, string $batchId, SupplyChainService $svc)
    {
        $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'reason'       => 'required|string|max:500',
        ]);

        try {
            $svc->adjustStock($this->demoFacilityId(), $batchId, $request->validated(), $this->demoActorId());
            return redirect()->route('portals.staff.supply.stock')
                ->with('success', 'Stock adjusted.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Purchase Orders ───────────────────────────────────────

    public function purchaseOrders(Request $request)
    {
        $facilityId = $this->demoFacilityId();
        $q = PurchaseOrder::where('facility_id', $facilityId)->with('supplier');

        if ($request->filled('status')) $q->where('status', $request->status);

        $purchaseOrders = $q->with('items')->orderByDesc('created_at')->paginate(20)->withQueryString();
        $suppliers = Supplier::where('facility_id', $facilityId)->where('status', 'active')->orderBy('name')->get();
        $items     = InventoryItem::where('facility_id', $facilityId)->where('status', 'active')->orderBy('name')->get();

        return view('portals.staff.supply_chain.purchase_orders',
            compact('purchaseOrders', 'suppliers', 'items'));
    }

    public function purchaseOrderStore(Request $request, SupplyChainService $svc)
    {
        $request->validate([
            'supplier_id'            => 'nullable|uuid',
            'expected_delivery_date' => 'nullable|date',
            'notes'                  => 'nullable|string',
            'items'                          => 'required|array|min:1',
            'items.*.inventory_item_id'      => 'required|uuid',
            'items.*.quantity_ordered'       => 'required|integer|min:1',
            'items.*.unit_price'             => 'nullable|numeric|min:0',
        ]);

        try {
            $po = $svc->createPurchaseOrder($this->demoFacilityId(), $request->validated(), $this->demoActorId());
            return redirect()->route('portals.staff.supply.purchase_orders')
                ->with('success', "Purchase Order {$po->po_number} created.");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function purchaseOrderApprove(string $id, SupplyChainService $svc)
    {
        $facilityId = $this->demoFacilityId();
        $po = PurchaseOrder::where('id', $id)->where('facility_id', $facilityId)->firstOrFail();

        try {
            $svc->approvePurchaseOrder($po, $this->demoActorId());
            return redirect()->route('portals.staff.supply.purchase_orders')
                ->with('success', "PO {$po->po_number} approved.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Goods Receipts ────────────────────────────────────────

    public function goodsReceipts(Request $request)
    {
        $facilityId = $this->demoFacilityId();
        $goodsReceipts = GoodsReceipt::where('facility_id', $facilityId)
            ->with(['purchaseOrder.supplier', 'items'])
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();
        $suppliers  = Supplier::where('facility_id', $facilityId)->where('status', 'active')->orderBy('name')->get();
        $openPOs    = PurchaseOrder::where('facility_id', $facilityId)
            ->whereIn('status', ['approved', 'sent', 'partial'])
            ->orderByDesc('created_at')
            ->get();
        $items      = InventoryItem::where('facility_id', $facilityId)->where('status', 'active')->orderBy('name')->get();
        $locations  = StockLocation::where('facility_id', $facilityId)->where('is_active', true)->get();

        return view('portals.staff.supply_chain.goods_receipts',
            compact('goodsReceipts', 'suppliers', 'openPOs', 'items', 'locations'));
    }

    public function goodsReceiptsStore(Request $request, SupplyChainService $svc)
    {
        $request->validate([
            'purchase_order_id' => 'required|uuid',
            'received_date'     => 'required|date',
            'receipt_number'    => 'nullable|string|max:50',
            'received_by'       => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
            'lines'             => 'required|array|min:1',
            'lines.*.inventory_item_id' => 'required|uuid',
            'lines.*.quantity_received' => 'required|integer|min:1',
            'lines.*.batch_number'      => 'nullable|string|max:80',
            'lines.*.expiry_date'       => 'nullable|date',
        ]);

        try {
            $payload = $request->validated();
            // Service expects 'items' with 'quantity' key; view sends 'lines' with 'quantity_received'
            $payload['items'] = array_map(function ($line) {
                $line['quantity'] = $line['quantity_received'];
                unset($line['quantity_received']);
                return $line;
            }, $payload['lines']);
            unset($payload['lines']);

            $gr = $svc->receiveGoodsReceipt($this->demoFacilityId(), $payload, $this->demoActorId());
            return redirect()->route('portals.staff.supply.goods_receipts')
                ->with('success', 'Goods receipt ' . ($gr->receipt_number ?: '') . ' posted.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Stock Movements ───────────────────────────────────────

    public function movements(Request $request)
    {
        $facilityId = $this->demoFacilityId();
        $q = StockMovement::where('facility_id', $facilityId)->with('item');

        if ($request->filled('type')) $q->where('movement_type', $request->type);
        if ($request->filled('item')) $q->where('inventory_item_id', $request->item);
        if ($request->filled('from')) $q->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to'))   $q->whereDate('created_at', '<=', $request->to);

        $movements = $q->with(['item', 'batch'])
            ->orderByDesc('created_at')
            ->paginate(30)->withQueryString();
        $items = InventoryItem::where('facility_id', $facilityId)->where('status', 'active')->orderBy('name')->get();

        return view('portals.staff.supply_chain.movements', compact('movements', 'items'));
    }
}
