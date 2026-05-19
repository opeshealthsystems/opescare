@extends('layouts.portal')
@section('title', 'Purchase Orders — Supply Chain')
@section('sidebar') @include('portals.staff.supply_chain._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Purchase Orders</h1>
            <p class="portal-page-subtitle">Manage procurement orders to suppliers</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('createModal')">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> New PO
        </button>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    {{-- Filters --}}
    <div class="portal-card" style="margin-bottom:18px;">
        <div class="portal-card__body">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-control--sm">
                        <option value="">All</option>
                        @foreach(['draft','submitted','approved','sent','partial','received','cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Supplier</label>
                    <select name="supplier" class="form-control form-control--sm" style="min-width:180px;">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                <a href="{{ route('portals.staff.supply.purchase_orders') }}" class="btn btn--outline btn--sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Lines</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Expected</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:0.88rem;">{{ $po->po_number }}</div>
                                @if($po->notes)
                                    <div style="font-size:0.75rem;color:#9ca3af;">{{ Str::limit($po->notes, 40) }}</div>
                                @endif
                            </td>
                            <td style="font-size:0.83rem;">{{ $po->supplier->name ?? '—' }}</td>
                            <td style="font-size:0.83rem;">{{ $po->items->count() }} item(s)</td>
                            <td style="font-weight:600;font-size:0.88rem;">
                                {{ number_format($po->total_amount, 2) }}
                            </td>
                            <td>
                                @php
                                    $statusColor = match($po->status) {
                                        'draft'     => 'default',
                                        'submitted' => 'info',
                                        'approved'  => 'success',
                                        'sent'      => 'info',
                                        'partial'   => 'warning',
                                        'received'  => 'success',
                                        'cancelled' => 'danger',
                                        default     => 'default',
                                    };
                                @endphp
                                <span class="badge badge--{{ $statusColor }}">{{ $po->status }}</span>
                            </td>
                            <td style="font-size:0.82rem;color:#6b7280;">{{ $po->order_date?->format('d M Y') ?? '—' }}</td>
                            <td style="font-size:0.82rem;color:#6b7280;">{{ $po->expected_delivery_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                @if(in_array($po->status, ['draft','submitted']))
                                    <form method="POST" action="{{ route('portals.staff.supply.purchase_orders.approve', $po->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn--sm btn--success"
                                                onclick="return confirm('Approve PO {{ $po->po_number }}?')">
                                            <i data-lucide="check" style="width:13px;height:13px;"></i>
                                            Approve
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">
                            <i data-lucide="file-text" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                            No purchase orders yet.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())<div class="portal-card__footer">{{ $purchaseOrders->links() }}</div>@endif
    </div>

</div>

{{-- Create Purchase Order Modal --}}
<div id="createModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('createModal')">
    <div class="modal-box" style="max-width:680px;width:97%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="file-plus" style="width:18px;height:18px;"></i> New Purchase Order</h3>
            <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.staff.supply.purchase_orders.store') }}">
            @csrf
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    <div class="form-group">
                        <label class="form-label">Supplier <span style="color:red">*</span></label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">— Select —</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expected Delivery</label>
                        <input type="date" name="expected_delivery_date" class="form-control">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes or instructions"></textarea>
                    </div>
                </div>

                {{-- Line Items --}}
                <div style="margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                    <strong style="font-size:0.88rem;">Order Lines</strong>
                    <button type="button" class="btn btn--sm btn--outline" onclick="addPOLine()">
                        <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Line
                    </button>
                </div>
                <div id="po-lines">
                    <div class="po-line" style="display:grid;grid-template-columns:3fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;">
                        <select name="items[0][inventory_item_id]" class="form-control form-control--sm" required>
                            <option value="">— Item —</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="items[0][quantity_ordered]" class="form-control form-control--sm" placeholder="Qty" min="1" required onchange="recalcTotal()">
                        <input type="number" name="items[0][unit_price]" class="form-control form-control--sm" placeholder="Unit Price" min="0" step="0.0001" onchange="recalcTotal()">
                        <button type="button" class="btn btn--sm btn--danger" onclick="removePOLine(this)" style="padding:4px 8px;" disabled title="At least one line required">
                            <i data-lucide="x" style="width:13px;height:13px;"></i>
                        </button>
                    </div>
                </div>
                <div style="text-align:right;font-size:0.88rem;color:#6b7280;margin-top:4px;">
                    Estimated Total: <strong id="po-total" style="color:#111827;">0.00</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Create PO (Draft)</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; lucide.createIcons(); }
function closeModal(id){ document.getElementById(id).style.display='none'; }

let poLineCount = 1;
const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ addslashes($item->name) }} ({{ $item->unit }})</option>@endforeach`;

function addPOLine(){
    const container = document.getElementById('po-lines');
    const idx = poLineCount++;
    const div = document.createElement('div');
    div.className = 'po-line';
    div.style.cssText = 'display:grid;grid-template-columns:3fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;';
    div.innerHTML = `
        <select name="items[${idx}][inventory_item_id]" class="form-control form-control--sm" required>
            <option value="">— Item —</option>
            ${itemOptions}
        </select>
        <input type="number" name="items[${idx}][quantity_ordered]" class="form-control form-control--sm" placeholder="Qty" min="1" required onchange="recalcTotal()">
        <input type="number" name="items[${idx}][unit_price]" class="form-control form-control--sm" placeholder="Unit Price" min="0" step="0.0001" onchange="recalcTotal()">
        <button type="button" class="btn btn--sm btn--danger" onclick="removePOLine(this)" style="padding:4px 8px;">
            <i data-lucide="x" style="width:13px;height:13px;"></i>
        </button>
    `;
    container.appendChild(div);
    // enable remove buttons when more than 1 line
    updateRemoveButtons();
    lucide.createIcons();
}

function removePOLine(btn){
    const line = btn.closest('.po-line');
    line.remove();
    updateRemoveButtons();
    recalcTotal();
}

function updateRemoveButtons(){
    const lines = document.querySelectorAll('#po-lines .po-line');
    lines.forEach((line, i) => {
        const btn = line.querySelector('button');
        btn.disabled = lines.length === 1;
    });
}

function recalcTotal(){
    let total = 0;
    document.querySelectorAll('#po-lines .po-line').forEach(line => {
        const qty = parseFloat(line.querySelector('input[name$="[quantity_ordered]"]')?.value) || 0;
        const price = parseFloat(line.querySelector('input[name$="[unit_price]"]')?.value) || 0;
        total += qty * price;
    });
    document.getElementById('po-total').textContent = total.toFixed(2);
}
</script>
@endsection
