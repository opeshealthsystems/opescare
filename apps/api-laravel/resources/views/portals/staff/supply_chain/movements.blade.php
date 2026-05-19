@extends('layouts.portal')
@section('title', 'Stock Movements — Supply Chain')
@section('sidebar') @include('portals.staff.supply_chain._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Stock Movements</h1>
            <p class="portal-page-subtitle">Full audit log of all inventory transactions</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="portal-card" style="margin-bottom:18px;">
        <div class="portal-card__body">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div>
                    <label class="form-label">Item</label>
                    <select name="item" class="form-control form-control--sm" style="min-width:190px;">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ request('item') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Movement Type</label>
                    <select name="type" class="form-control form-control--sm">
                        <option value="">All Types</option>
                        @foreach(['receipt','dispense','transfer','adjustment','return','write_off','opening_stock'] as $t)
                            <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control form-control--sm" value="{{ request('from') }}">
                </div>
                <div>
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control form-control--sm" value="{{ request('to') }}">
                </div>
                <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                <a href="{{ route('portals.staff.supply.movements') }}" class="btn btn--outline btn--sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Item</th>
                        <th>Batch</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Unit Cost</th>
                        <th>Reference</th>
                        <th>Performed By</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $mv)
                        <tr>
                            <td style="font-size:0.8rem;white-space:nowrap;color:#6b7280;">
                                {{ $mv->created_at->format('d M Y') }}<br>
                                <span style="font-size:0.73rem;">{{ $mv->created_at->format('H:i') }}</span>
                            </td>
                            <td>
                                <div style="font-weight:600;font-size:0.85rem;">{{ $mv->item->name ?? '—' }}</div>
                                <div style="font-size:0.73rem;color:#9ca3af;">{{ $mv->item->code ?? '' }}</div>
                            </td>
                            <td style="font-size:0.8rem;color:#6b7280;">
                                {{ $mv->batch->batch_number ?? '—' }}
                            </td>
                            <td>
                                @php
                                    $typeColor = match($mv->movement_type) {
                                        'receipt'       => 'success',
                                        'dispense'      => 'info',
                                        'adjustment'    => 'warning',
                                        'write_off'     => 'danger',
                                        'return'        => 'default',
                                        'transfer'      => 'info',
                                        'opening_stock' => 'success',
                                        default         => 'default',
                                    };
                                @endphp
                                <span class="badge badge--{{ $typeColor }}" style="font-size:0.73rem;">
                                    {{ str_replace('_',' ', $mv->movement_type) }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight:700;font-size:0.92rem;color:{{ $mv->quantity >= 0 ? '#16a34a' : '#dc2626' }};">
                                    {{ $mv->quantity >= 0 ? '+' : '' }}{{ $mv->quantity }}
                                </span>
                            </td>
                            <td style="font-size:0.82rem;color:#6b7280;">
                                {{ $mv->unit_cost ? number_format($mv->unit_cost, 2) : '—' }}
                            </td>
                            <td style="font-size:0.78rem;color:#6b7280;">
                                @if($mv->reference_type)
                                    <span style="text-transform:capitalize;">{{ str_replace('_',' ',$mv->reference_type) }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-size:0.8rem;color:#6b7280;">{{ $mv->performed_by ?: '—' }}</td>
                            <td style="font-size:0.78rem;color:#9ca3af;max-width:160px;">
                                {{ $mv->reason ? Str::limit($mv->reason, 55) : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" style="text-align:center;padding:40px;color:#9ca3af;">
                            <i data-lucide="arrow-left-right" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                            No stock movements recorded yet.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())<div class="portal-card__footer">{{ $movements->links() }}</div>@endif
    </div>

</div>
@endsection
