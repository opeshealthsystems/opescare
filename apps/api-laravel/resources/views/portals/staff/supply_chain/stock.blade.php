@extends('layouts.portal')
@section('title', 'Stock Levels — Supply Chain')
@section('sidebar') @include('portals.staff.supply_chain._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Stock Levels</h1>
            <p class="portal-page-subtitle">View and manage stock batches and balances</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('receiveModal')">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Receive Stock
        </button>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    {{-- Filters --}}
    <div class="portal-card" style="margin-bottom:18px;">
        <div class="portal-card__body">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div>
                    <label class="form-label">Item</label>
                    <select name="item" class="form-control form-control--sm" style="min-width:200px;">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ request('item') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Batch Status</label>
                    <select name="status" class="form-control form-control--sm">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="quarantine" {{ request('status') == 'quarantine' ? 'selected' : '' }}>Quarantine</option>
                    </select>
                </div>
                <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                <a href="{{ route('portals.staff.supply.stock') }}" class="btn btn--outline btn--sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Batch / Lot</th>
                        <th>Location</th>
                        <th>Available</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        @php $available = $batch->availableQty(); @endphp
                        <tr style="{{ $batch->status === 'expired' ? 'opacity:0.65;' : '' }}">
                            <td>
                                <div style="font-weight:600;font-size:0.88rem;">{{ $batch->item->name ?? '—' }}</div>
                                <div style="font-size:0.76rem;color:#9ca3af;">{{ $batch->item->code ?? '' }} · {{ $batch->item->unit ?? '' }}</div>
                            </td>
                            <td style="font-size:0.82rem;">
                                {{ $batch->batch_number ?: '—' }}
                                @if($batch->lot_number)
                                    <div style="font-size:0.76rem;color:#9ca3af;">Lot: {{ $batch->lot_number }}</div>
                                @endif
                            </td>
                            <td style="font-size:0.83rem;">{{ $batch->location->name ?? '—' }}</td>
                            <td>
                                <span style="font-weight:700;font-size:0.95rem;color:{{ $available <= 0 ? '#dc2626' : ($available <= ($batch->item->reorder_level ?? 0) ? '#d97706' : '#374151') }};">
                                    {{ $available }}
                                </span>
                            </td>
                            <td style="font-size:0.83rem;">
                                @if($batch->expiry_date)
                                    <span style="color:{{ $batch->isExpired() ? '#dc2626' : ($batch->isExpiringSoon() ? '#d97706' : '#374151') }};">
                                        {{ $batch->expiry_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span style="color:#9ca3af;">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--{{ $batch->status === 'active' ? 'success' : ($batch->status === 'expired' ? 'danger' : 'warning') }}">
                                    {{ $batch->status }}
                                </span>
                            </td>
                            <td>
                                @if($batch->status === 'active')
                                    <button class="btn btn--sm btn--outline"
                                            onclick="openAdjust('{{ $batch->id }}', {{ $batch->availableQty() }})">
                                        <i data-lucide="edit-3" style="width:13px;height:13px;"></i>
                                        Adjust
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">
                            <i data-lucide="boxes" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                            No stock batches. Receive stock first.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())<div class="portal-card__footer">{{ $batches->links() }}</div>@endif
    </div>

</div>

{{-- Receive Stock Modal --}}
<div id="receiveModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('receiveModal')">
    <div class="modal-box" style="max-width:500px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="package-plus" style="width:18px;height:18px;"></i> Receive Stock</h3>
            <button class="modal-close" onclick="closeModal('receiveModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.staff.supply.stock.receive') }}">
            @csrf
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Item <span style="color:red">*</span></label>
                        <select name="inventory_item_id" class="form-control" required>
                            <option value="">— Select Item —</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quantity <span style="color:red">*</span></label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <select name="location_id" class="form-control">
                            <option value="">Default</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Batch Number</label>
                        <input type="text" name="batch_number" class="form-control" maxlength="80">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit Cost</label>
                        <input type="number" name="unit_cost" class="form-control" step="0.0001" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-control">
                            <option value="">— None —</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('receiveModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Receive Stock</button>
            </div>
        </form>
    </div>
</div>

{{-- Adjust Stock Modal --}}
<div id="adjustModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('adjustModal')">
    <div class="modal-box" style="max-width:400px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="edit-3" style="width:18px;height:18px;"></i> Adjust Stock</h3>
            <button class="modal-close" onclick="closeModal('adjustModal')">&times;</button>
        </div>
        <form method="POST" id="adjustForm" action="">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Current Quantity</label>
                    <input type="text" id="currentQty" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">New Quantity <span style="color:red">*</span></label>
                    <input type="number" name="new_quantity" class="form-control" required min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Reason <span style="color:red">*</span></label>
                    <input type="text" name="reason" class="form-control" required placeholder="e.g. Physical count correction">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('adjustModal')">Cancel</button>
                <button type="submit" class="btn btn--warning">Apply Adjustment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function openAdjust(batchId, currentQty){
    document.getElementById('currentQty').value = currentQty;
    document.getElementById('adjustForm').action = '/portals/staff/supply/stock/' + batchId + '/adjust';
    openModal('adjustModal');
}
</script>
@endsection
