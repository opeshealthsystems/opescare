@extends('layouts.portal')
@section('title', 'Supply Chain — Dashboard')
@section('sidebar') @include('portals.staff.supply_chain._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Supply Chain</h1>
            <p class="portal-page-subtitle">Inventory, procurement &amp; stock management</p>
        </div>
        <a href="{{ route('portals.staff.supply.stock.receive') }}" class="btn btn--primary" onclick="event.preventDefault();openModal('receiveModal')">
            <i data-lucide="plus"></i> Receive Stock
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
    @endif

    {{-- KPI Cards --}}
    <div class="stat-grid mb-6">
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="list"></i></div>
            <div class="stat-card__value">{{ $stats['items'] }}</div><div class="stat-card__label">Items in Catalog</div>
        </div>
        <div class="stat-card stat-card--danger">
            <div class="stat-card__head"><i data-lucide="triangle-alert"></i></div>
            <div class="stat-card__value">{{ $stats['lowStock'] }}</div><div class="stat-card__label">Low / Out of Stock</div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-card__head"><i data-lucide="clock"></i></div>
            <div class="stat-card__value">{{ $stats['expiring'] }}</div><div class="stat-card__label">Expiring (30 days)</div>
        </div>
        <div class="stat-card stat-card--danger">
            <div class="stat-card__head"><i data-lucide="x-circle"></i></div>
            <div class="stat-card__value">{{ $stats['expired'] }}</div><div class="stat-card__label">Expired Batches</div>
        </div>
        <div class="stat-card stat-card--success">
            <div class="stat-card__head"><i data-lucide="truck"></i></div>
            <div class="stat-card__value">{{ $stats['suppliers'] }}</div><div class="stat-card__label">Active Suppliers</div>
        </div>
        <div class="stat-card stat-card--teal">
            <div class="stat-card__head"><i data-lucide="file-text"></i></div>
            <div class="stat-card__value">{{ $stats['openPOs'] }}</div><div class="stat-card__label">Open POs</div>
        </div>
    </div>

    <div class="grid-2 mb-6">

        {{-- Low Stock Alerts --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title"><i data-lucide="triangle-alert"></i> Low / Out of Stock</h2>
                <a href="{{ route('portals.staff.supply.items') }}" class="btn btn--sm btn--outline">All Items</a>
            </div>
            <div class="portal-card__body panel-body--flush">
                @forelse($lowStock as $item)
                    <div class="list-row">
                        <div>
                            <div class="td-strong">{{ $item->name }}</div>
                            <div class="td-muted">{{ $item->code }} · {{ ucfirst($item->category) }}</div>
                        </div>
                        <div>
                            <span class="badge badge-danger">{{ $item->totalStock(request()->facilityId ?? \App\Models\Facility::value('id')) }} {{ $item->unit }}</span>
                            <div class="td-muted">Reorder at {{ $item->reorder_level }}</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
                        <p>All stock levels are healthy</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Expiring Soon --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title"><i data-lucide="clock"></i> Expiring in 30 Days</h2>
                <a href="{{ route('portals.staff.supply.stock') }}" class="btn btn--sm btn--outline">Stock View</a>
            </div>
            <div class="portal-card__body panel-body--flush">
                @forelse($expiring as $batch)
                    <div class="list-row">
                        <div>
                            <div class="td-strong">{{ $batch->item->name ?? '—' }}</div>
                            <div class="td-muted">Batch: {{ $batch->batch_number ?: 'N/A' }}</div>
                        </div>
                        <div>
                            <span class="badge badge-warning">{{ $batch->expiry_date?->format('d M Y') }}</span>
                            <div class="td-muted">{{ $batch->availableQty() }} units</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No batches expiring in the next 30 days</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Recent Stock Movements --}}
    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title"><i data-lucide="arrow-left-right"></i> Recent Stock Movements</h2>
            <a href="{{ route('portals.staff.supply.movements') }}" class="btn btn--sm btn--outline">All Movements</a>
        </div>
        <div class="portal-card__body panel-body--flush">
            <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>By</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentMovements as $mv)
                        <tr>
                            <td data-label="Item" class="td-strong">{{ $mv->item->name ?? '—' }}</td>
                            <td data-label="Type">
                                <span class="badge badge--{{
                                    $mv->movement_type === 'receipt' ? 'success' :
                                    ($mv->movement_type === 'dispense' ? 'info' :
                                    ($mv->movement_type === 'write_off' ? 'danger' :
                                    ($mv->movement_type === 'adjustment' ? 'warning' : 'default')))
                                }}">{{ str_replace('_', ' ', $mv->movement_type) }}</span>
                            </td>
                            <td data-label="Quantity" class="td-strong">{{ $mv->quantity }}</td>
                            <td data-label="By" class="td-muted">{{ $mv->performed_by ?: '—' }}</td>
                            <td data-label="When" class="td-muted">{{ $mv->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-cell td-muted">No movements yet</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div>
@endsection
