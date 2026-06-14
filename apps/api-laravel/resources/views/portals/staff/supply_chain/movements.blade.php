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
    <form method="GET" class="filter-bar">
        <select name="item" class="filter-select">
            <option value="">All Items</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" {{ request('item') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
            @endforeach
        </select>
        <select name="type" class="filter-select">
            <option value="">All Types</option>
            @foreach(['receipt','dispense','transfer','adjustment','return','write_off','opening_stock'] as $t)
                <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" class="filter-select" value="{{ request('from') }}" aria-label="From date">
        <input type="date" name="to" class="filter-select" value="{{ request('to') }}" aria-label="To date">
        <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
        <a href="{{ route('portals.staff.supply.movements') }}" class="btn btn-ghost btn-sm">Reset</a>
    </form>

    <div class="portal-card">
        <div class="portal-card__body panel-body--flush">
            <div class="table-wrapper">
            <table class="data-table">
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
                            <td data-label="Date / Time" class="td-muted">
                                {{ $mv->created_at->format('d M Y') }}<br>
                                <span>{{ $mv->created_at->format('H:i') }}</span>
                            </td>
                            <td data-label="Item">
                                <div class="td-strong">{{ $mv->item->name ?? '—' }}</div>
                                <div class="td-muted">{{ $mv->item->code ?? '' }}</div>
                            </td>
                            <td data-label="Batch" class="td-muted">
                                {{ $mv->batch->batch_number ?? '—' }}
                            </td>
                            <td data-label="Type">
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
                                <span class="badge badge--{{ $typeColor }} badge-sm">
                                    {{ str_replace('_',' ', $mv->movement_type) }}
                                </span>
                            </td>
                            <td data-label="Qty">
                                <span class="badge {{ $mv->quantity >= 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $mv->quantity >= 0 ? '+' : '' }}{{ $mv->quantity }}
                                </span>
                            </td>
                            <td data-label="Unit Cost" class="td-muted">
                                {{ $mv->unit_cost ? number_format($mv->unit_cost, 2) : '—' }}
                            </td>
                            <td data-label="Reference" class="td-muted">
                                @if($mv->reference_type)
                                    {{ ucfirst(str_replace('_',' ',$mv->reference_type)) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td data-label="Performed By" class="td-muted">{{ $mv->performed_by ?: '—' }}</td>
                            <td data-label="Reason" class="td-muted">
                                {{ $mv->reason ? Str::limit($mv->reason, 55) : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i data-lucide="arrow-left-right"></i></div>
                                <p>No stock movements recorded yet.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        @if($movements->hasPages())<div class="panel-footer">{{ $movements->links() }}</div>@endif
    </div>

</div>
@endsection
