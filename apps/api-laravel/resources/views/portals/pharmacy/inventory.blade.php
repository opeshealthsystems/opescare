@extends('layouts.portal')

@section('title', 'Drug Inventory')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="pill"></i>
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

<div class="page-head">
    <h2>Drug inventory</h2>
    <p class="page-subtitle">Current stock levels, expiries, and availability.</p>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="settings"></i> Manage stock
    </a>
</div>

<form method="GET" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" placeholder="Drug or generic name…" value="{{ request('search') }}" aria-label="Search drugs">
    </label>
    <select name="stock_status" class="filter-select" aria-label="Stock status" onchange="this.form.submit()">
        <option value="">All stock</option>
        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In stock</option>
        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low stock</option>
        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    @if(request()->hasAny(['stock_status','search']))
        <a href="{{ route('portals.pharmacy.inventory') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Drug name</th><th>Generic</th><th>Form / strength</th><th>Qty</th><th>Stock status</th><th>Flags</th><th>Last updated</th></tr>
            </thead>
            <tbody>
                @forelse($drugs as $drug)
                <tr>
                    <td data-label="Drug name" class="td-strong">{{ $drug->medicine_name }}</td>
                    <td data-label="Generic" class="td-muted">{{ $drug->generic_name ?? '—' }}</td>
                    <td data-label="Form / strength">{{ $drug->form }} {{ $drug->strength }}</td>
                    <td data-label="Qty" class="td-strong">{{ $drug->available_quantity }}</td>
                    <td data-label="Stock status">
                        <span class="badge badge-{{ match($drug->stock_status) { 'in_stock' => 'success', 'low_stock' => 'warning', 'out_of_stock' => 'danger', default => 'neutral' } }}">
                            {{ ucfirst(str_replace('_', ' ', $drug->stock_status)) }}
                        </span>
                    </td>
                    <td data-label="Flags">
                        @if($drug->is_expired)<span class="badge badge-danger">Expired</span>@endif
                        @if($drug->is_recalled)<span class="badge badge-danger">Recalled</span>@endif
                        @if($drug->is_quarantined)<span class="badge badge-warning">Quarantined</span>@endif
                        @if(!$drug->is_expired && !$drug->is_recalled && !$drug->is_quarantined)<span class="td-muted">—</span>@endif
                    </td>
                    <td data-label="Last updated" class="td-muted">{{ $drug->last_stock_update?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="td-muted empty-cell">No drugs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $drugs->links() }}</div>
</div>

@endsection
