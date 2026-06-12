@extends('layouts.portal')

@section('title', 'Drug Inventory')

@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.4);color:#34d399;">
    <i data-lucide="pill" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    Pharmacy
</div>
@endsection
@section('sidebar_user_role', 'Pharmacist')

@section('sidebar_nav')
@include('portals.pharmacy._sidebar')
@endsection

@section('breadcrumb_home', 'Pharmacy Portal')
@section('breadcrumb_home_url', route('portals.pharmacy.dashboard'))
@section('breadcrumb_section', 'Drug Inventory')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Drug Inventory</h1>
        <p class="page-subtitle">Current stock levels, expiries, and availability.</p>
    </div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="btn btn-outline btn-sm">
        <i data-lucide="settings" style="width:14px;height:14px;"></i>
        Manage Stock
    </a>
</div>

{{-- Filters --}}
<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;align-items:flex-end;">
    <div>
        <label class="form-label">Stock Status</label>
        <select name="stock_status" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
            <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
            <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
        </select>
    </div>
    <div>
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Drug or generic name…" value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['stock_status','search']))
        <a href="{{ route('portals.pharmacy.inventory') }}" class="btn btn-outline btn-sm">Clear</a>
    @endif
</form>

<div class="card" style="overflow:hidden;">
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Drug Name</th>
                    <th>Generic</th>
                    <th>Form / Strength</th>
                    <th>Qty</th>
                    <th>Stock Status</th>
                    <th>Flags</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drugs as $drug)
                <tr>
                    <td style="font-weight:600;">{{ $drug->medicine_name }}</td>
                    <td style="font-size:.85rem;color:#64748b;">{{ $drug->generic_name ?? '—' }}</td>
                    <td style="font-size:.85rem;">{{ $drug->form }} {{ $drug->strength }}</td>
                    <td style="font-weight:700;">{{ $drug->available_quantity }}</td>
                    <td>
                        <span class="badge badge-{{ match($drug->stock_status) { 'in_stock' => 'success', 'low_stock' => 'warning', 'out_of_stock' => 'danger', default => 'default' } }}">
                            {{ ucfirst(str_replace('_', ' ', $drug->stock_status)) }}
                        </span>
                    </td>
                    <td>
                        @if($drug->is_expired)
                            <span class="badge badge-danger">Expired</span>
                        @endif
                        @if($drug->is_recalled)
                            <span class="badge badge-danger">Recalled</span>
                        @endif
                        @if($drug->is_quarantined)
                            <span class="badge badge-warning">Quarantined</span>
                        @endif
                        @if(!$drug->is_expired && !$drug->is_recalled && !$drug->is_quarantined)
                            <span style="color:#94a3b8;font-size:.8rem;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;color:#64748b;">{{ $drug->last_stock_update?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8;">No drugs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:1rem;">
    {{ $drugs->links() }}
</div>

@endsection
