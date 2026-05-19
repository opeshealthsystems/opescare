@extends('layouts.portal')
@section('title', 'Supply Chain — Dashboard')
@section('sidebar') @include('portals.staff.supply_chain._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="package" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#0891b2;"></i>
                Supply Chain
            </h1>
            <p class="portal-page-subtitle">Inventory, procurement & stock management</p>
        </div>
        <a href="{{ route('portals.staff.supply.stock.receive') }}" class="btn btn--primary" onclick="event.preventDefault();openModal('receiveModal')">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Receive Stock
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--danger">{{ session('error') }}</div>
    @endif

    {{-- KPI Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:16px;margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#e0f2fe;"><i data-lucide="list" style="color:#0891b2;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['items'] }}</div><div class="stat-card__label">Items in Catalog</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;"><i data-lucide="triangle-alert" style="color:#dc2626;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#dc2626;">{{ $stats['lowStock'] }}</div><div class="stat-card__label">Low / Out of Stock</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fef3c7;"><i data-lucide="clock" style="color:#d97706;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#d97706;">{{ $stats['expiring'] }}</div><div class="stat-card__label">Expiring (30 days)</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fce7f3;"><i data-lucide="x-circle" style="color:#be185d;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#be185d;">{{ $stats['expired'] }}</div><div class="stat-card__label">Expired Batches</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="truck" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['suppliers'] }}</div><div class="stat-card__label">Active Suppliers</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;"><i data-lucide="file-text" style="color:#7c3aed;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['openPOs'] }}</div><div class="stat-card__label">Open POs</div></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

        {{-- Low Stock Alerts --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title"><i data-lucide="triangle-alert" style="width:15px;height:15px;color:#dc2626;"></i> Low / Out of Stock</h2>
                <a href="{{ route('portals.staff.supply.items') }}" class="btn btn--sm btn--outline">All Items</a>
            </div>
            <div class="portal-card__body" style="padding:0;">
                @forelse($lowStock as $item)
                    <div style="padding:12px 18px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:600;font-size:0.88rem;">{{ $item->name }}</div>
                            <div style="font-size:0.76rem;color:#9ca3af;">{{ $item->code }} · {{ ucfirst($item->category) }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.9rem;font-weight:700;color:#dc2626;">{{ $item->totalStock(request()->facilityId ?? \App\Models\Facility::value('id')) }} {{ $item->unit }}</div>
                            <div style="font-size:0.72rem;color:#9ca3af;">Reorder at {{ $item->reorder_level }}</div>
                        </div>
                    </div>
                @empty
                    <div style="padding:24px;text-align:center;color:#9ca3af;font-size:0.875rem;">
                        <i data-lucide="check-circle" style="width:28px;height:28px;display:block;margin:0 auto 8px;color:#16a34a;"></i>
                        All stock levels are healthy
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Expiring Soon --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title"><i data-lucide="clock" style="width:15px;height:15px;color:#d97706;"></i> Expiring in 30 Days</h2>
                <a href="{{ route('portals.staff.supply.stock') }}" class="btn btn--sm btn--outline">Stock View</a>
            </div>
            <div class="portal-card__body" style="padding:0;">
                @forelse($expiring as $batch)
                    <div style="padding:12px 18px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:600;font-size:0.88rem;">{{ $batch->item->name ?? '—' }}</div>
                            <div style="font-size:0.76rem;color:#9ca3af;">
                                Batch: {{ $batch->batch_number ?: 'N/A' }}
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.88rem;font-weight:700;color:#d97706;">{{ $batch->expiry_date?->format('d M Y') }}</div>
                            <div style="font-size:0.76rem;color:#9ca3af;">{{ $batch->availableQty() }} units</div>
                        </div>
                    </div>
                @empty
                    <div style="padding:24px;text-align:center;color:#9ca3af;font-size:0.875rem;">
                        No batches expiring in the next 30 days
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Recent Stock Movements --}}
    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title"><i data-lucide="arrow-left-right" style="width:15px;height:15px;"></i> Recent Stock Movements</h2>
            <a href="{{ route('portals.staff.supply.movements') }}" class="btn btn--sm btn--outline">All Movements</a>
        </div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
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
                            <td style="font-size:0.85rem;font-weight:500;">{{ $mv->item->name ?? '—' }}</td>
                            <td>
                                <span class="badge badge--{{
                                    $mv->movement_type === 'receipt' ? 'success' :
                                    ($mv->movement_type === 'dispense' ? 'info' :
                                    ($mv->movement_type === 'write_off' ? 'danger' :
                                    ($mv->movement_type === 'adjustment' ? 'warning' : 'default')))
                                }}">{{ str_replace('_', ' ', $mv->movement_type) }}</span>
                            </td>
                            <td style="font-weight:600;">{{ $mv->quantity }}</td>
                            <td style="font-size:0.8rem;color:#6b7280;">{{ $mv->performed_by ?: '—' }}</td>
                            <td style="font-size:0.8rem;color:#9ca3af;">{{ $mv->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;padding:24px;color:#9ca3af;">No movements yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
