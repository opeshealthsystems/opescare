@extends('layouts.portal')
@section('title', 'Goods Receipts — Supply Chain')
@section('sidebar') @include('portals.staff.supply_chain._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Goods Receipts</h1>
            <p class="portal-page-subtitle">Record stock received against purchase orders</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('createModal')">
            <i data-lucide="package-check" style="width:15px;height:15px;"></i> Record Receipt
        </button>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>GR Number</th>
                        <th>PO Reference</th>
                        <th>Supplier</th>
                        <th>Received By</th>
                        <th>Receipt Date</th>
                        <th>Lines</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goodsReceipts as $gr)
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:0.88rem;">{{ $gr->receipt_number ?: '—' }}</div>
                            </td>
                            <td style="font-size:0.83rem;">
                                {{ $gr->purchaseOrder->po_number ?? '—' }}
                            </td>
                            <td style="font-size:0.83rem;">
                                {{ $gr->purchaseOrder->supplier->name ?? '—' }}
                            </td>
                            <td style="font-size:0.82rem;color:#6b7280;">{{ $gr->received_by ?: '—' }}</td>
                            <td style="font-size:0.82rem;">{{ $gr->received_date?->format('d M Y') ?? '—' }}</td>
                            <td style="font-size:0.83rem;">{{ $gr->items->count() }} line(s)</td>
                            <td>
                                @php
                                    $grColor = match($gr->status) {
                                        'pending'  => 'warning',
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        default    => 'default',
                                    };
                                @endphp
                                <span class="badge badge--{{ $grColor }}">{{ $gr->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">
                            <i data-lucide="package-check" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                            No goods receipts yet. Record when stock arrives.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($goodsReceipts->hasPages())<div class="portal-card__footer">{{ $goodsReceipts->links() }}</div>@endif
    </div>

</div>

{{-- Create Goods Receipt Modal --}}
<div id="createModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('createModal')">
    <div class="modal-box" style="max-width:640px;width:97%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="package-check" style="width:18px;height:18px;"></i> Record Goods Receipt</h3>
            <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.staff.supply.goods_receipts.store') }}">
            @csrf
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    <div class="form-group">
                        <label class="form-label">Purchase Order <span style="color:red">*</span></label>
                        <select name="purchase_order_id" class="form-control" required>
                            <option value="">— Select PO —</option>
                            @foreach($openPOs as $po)
                                <option value="{{ $po->id }}">{{ $po->po_number }} — {{ $po->supplier->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Receipt Date <span style="color:red">*</span></label>
                        <input type="date" name="received_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Receipt Number</label>
                        <input type="text" name="receipt_number" class="form-control" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Received By</label>
                        <input type="text" name="received_by" class="form-control" maxlength="100">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                {{-- Receipt Lines --}}
                <div style="margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                    <strong style="font-size:0.88rem;">Items Received</strong>
                    <button type="button" class="btn btn--sm btn--outline" onclick="addGRLine()">
                        <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Line
                    </button>
                </div>
                <div style="font-size:0.75rem;color:#9ca3af;margin-bottom:8px;">Item · Qty Received · Batch # · Expiry Date</div>
                <div id="gr-lines">
                    <div class="gr-line" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;">
                        <select name="lines[0][inventory_item_id]" class="form-control form-control--sm" required>
                            <option value="">— Item —</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="lines[0][quantity_received]" class="form-control form-control--sm" placeholder="Qty" min="1" required>
                        <input type="text" name="lines[0][batch_number]" class="form-control form-control--sm" placeholder="Batch #" maxlength="80">
                        <input type="date" name="lines[0][expiry_date]" class="form-control form-control--sm">
                        <button type="button" class="btn btn--sm btn--danger" onclick="removeGRLine(this)" style="padding:4px 8px;" disabled>
                            <i data-lucide="x" style="width:13px;height:13px;"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Post Receipt</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; lucide.createIcons(); }
function closeModal(id){ document.getElementById(id).style.display='none'; }

let grLineCount = 1;
const grItemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ addslashes($item->name) }}</option>@endforeach`;

function addGRLine(){
    const container = document.getElementById('gr-lines');
    const idx = grLineCount++;
    const div = document.createElement('div');
    div.className = 'gr-line';
    div.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;';
    div.innerHTML = `
        <select name="lines[${idx}][inventory_item_id]" class="form-control form-control--sm" required>
            <option value="">— Item —</option>
            ${grItemOptions}
        </select>
        <input type="number" name="lines[${idx}][quantity_received]" class="form-control form-control--sm" placeholder="Qty" min="1" required>
        <input type="text" name="lines[${idx}][batch_number]" class="form-control form-control--sm" placeholder="Batch #" maxlength="80">
        <input type="date" name="lines[${idx}][expiry_date]" class="form-control form-control--sm">
        <button type="button" class="btn btn--sm btn--danger" onclick="removeGRLine(this)" style="padding:4px 8px;">
            <i data-lucide="x" style="width:13px;height:13px;"></i>
        </button>
    `;
    container.appendChild(div);
    updateGRRemoveButtons();
    lucide.createIcons();
}

function removeGRLine(btn){
    btn.closest('.gr-line').remove();
    updateGRRemoveButtons();
}

function updateGRRemoveButtons(){
    const lines = document.querySelectorAll('#gr-lines .gr-line');
    lines.forEach(line => {
        const btn = line.querySelector('button');
        btn.disabled = lines.length === 1;
    });
}
</script>
@endsection
