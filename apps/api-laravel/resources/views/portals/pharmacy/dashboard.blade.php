@extends('layouts.portal')

@section('title', 'Pharmacy Portal')

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
@section('breadcrumb_section', 'Dashboard')

@section('content')

<div class="page-head">
    <h2>Pharmacy dashboard</h2>
    <p class="page-subtitle">Today's prescription queue, stock alerts, and dispensing activity.</p>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-primary btn-sm">
        <i data-lucide="clipboard-list"></i> View full queue
    </a>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

{{-- Stat cards --}}
<div class="stat-grid mb-6">
    <a href="{{ route('portals.pharmacy.prescriptions') }}" class="stat-card stat-card--warning">
        <div class="stat-card__label">Pending Rx</div>
        <div class="stat-card__value">{{ $stats['pending_rx'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.pharmacy.prescriptions', ['status' => 'dispensed']) }}" class="stat-card stat-card--success">
        <div class="stat-card__label">Dispensed today</div>
        <div class="stat-card__value">{{ $stats['dispensed_today'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.pharmacy.inventory') }}" class="stat-card stat-card--primary">
        <div class="stat-card__label">Drug lines</div>
        <div class="stat-card__value">{{ $stats['total_drugs'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.pharmacy.inventory') }}?stock_status=low_stock" class="stat-card stat-card--warning">
        <div class="stat-card__label">Low stock</div>
        <div class="stat-card__value">{{ $stats['low_stock'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.pharmacy.inventory') }}?stock_status=out_of_stock" class="stat-card stat-card--danger">
        <div class="stat-card__label">Out of stock</div>
        <div class="stat-card__value">{{ $stats['out_of_stock'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.pharmacy.inventory', ['stock_status' => 'expired']) }}" class="stat-card stat-card--danger">
        <div class="stat-card__label">Expired</div>
        <div class="stat-card__value">{{ $stats['expired'] ?? 0 }}</div>
    </a>
</div>

<div class="field-grid">

    {{-- Pending Prescriptions (dispense queue) --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="clipboard-list"></i> Pending prescriptions</h3>
            <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Patient</th><th>Status</th><th class="row-actions"></th></tr></thead>
                <tbody>
                @forelse($pendingRx as $rx)
                <tr>
                    <td data-label="Patient">
                        <span class="td-strong">{{ $rx->patient?->full_name ?? '—' }}</span>
                        <div class="td-muted">{{ $rx->items()->count() }} item(s) &middot; {{ $rx->created_at?->diffForHumans() }}</div>
                    </td>
                    <td data-label="Status"><span class="badge badge-{{ $rx->statusColor() }}">{{ ucfirst(str_replace('_', ' ', $rx->status)) }}</span></td>
                    <td class="row-actions" data-label="">
                        <form method="POST" action="{{ route('portals.pharmacy.dispense', $rx->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Mark as dispensed?')">Dispense</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="empty-cell">
                    <div class="empty-state">
                        <i data-lucide="check-circle-2" class="empty-state-icon"></i>
                        <p>All prescriptions are up to date.</p>
                    </div>
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stock Alerts (low-stock) --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="alert-triangle"></i> Stock alerts</h3>
            <a href="{{ route('portals.pharmacy.inventory') }}" class="btn btn-secondary btn-sm">Inventory</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Medicine</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($alerts as $drug)
                <tr>
                    <td data-label="Medicine">
                        <span class="td-strong">{{ $drug->medicine_name }}</span>
                        <div class="td-muted">{{ $drug->generic_name }} &middot; {{ $drug->form }} {{ $drug->strength }}</div>
                    </td>
                    <td data-label="Status">
                        <span class="badge badge-{{ $drug->is_expired ? 'danger' : ($drug->stock_status === 'out_of_stock' ? 'danger' : 'warning') }}">
                            {{ $drug->is_expired ? 'Expired' : ucfirst(str_replace('_', ' ', $drug->stock_status)) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="2" class="td-muted empty-cell">No stock alerts.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
