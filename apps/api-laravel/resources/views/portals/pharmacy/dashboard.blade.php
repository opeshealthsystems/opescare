@extends('layouts.portal')

@section('title', 'Pharmacy Portal')

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
@section('breadcrumb_section', 'Dashboard')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Pharmacy Dashboard</h1>
        <p class="page-subtitle">Today's prescription queue, stock alerts, and dispensing activity.</p>
    </div>
    <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-primary btn-sm">
        <i data-lucide="clipboard-list" style="width:14px;height:14px;"></i>
        View Full Queue
    </a>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <a href="{{ route('portals.pharmacy.prescriptions') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="clipboard-list"></i></div>
            <div class="stat-card__val">{{ $stats['pending_rx'] }}</div>
            <div class="stat-card__label">Pending Rx</div>
        </div>
    </a>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-card__val">{{ $stats['dispensed_today'] }}</div>
        <div class="stat-card__label">Dispensed Today</div>
    </div>
    <a href="{{ route('portals.pharmacy.inventory') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dbeafe;color:#1d4ed8;"><i data-lucide="package"></i></div>
            <div class="stat-card__val">{{ $stats['total_drugs'] }}</div>
            <div class="stat-card__label">Drug Lines</div>
        </div>
    </a>
    <a href="{{ route('portals.pharmacy.inventory') }}?stock_status=low_stock" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="alert-triangle"></i></div>
            <div class="stat-card__val">{{ $stats['low_stock'] }}</div>
            <div class="stat-card__label">Low Stock</div>
        </div>
    </a>
    <a href="{{ route('portals.pharmacy.inventory') }}?stock_status=out_of_stock" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;color:#b91c1c;"><i data-lucide="x-circle"></i></div>
            <div class="stat-card__val">{{ $stats['out_of_stock'] }}</div>
            <div class="stat-card__label">Out of Stock</div>
        </div>
    </a>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#f3e8ff;color:#7c3aed;"><i data-lucide="trash-2"></i></div>
        <div class="stat-card__val">{{ $stats['expired'] }}</div>
        <div class="stat-card__label">Expired</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    {{-- Pending Prescriptions --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;">Pending Prescriptions</span>
            <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-outline btn-sm">View all</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($pendingRx as $rx)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $rx->patient?->full_name ?? '—' }}</div>
                    <div style="font-size:.75rem;color:#64748b;">
                        {{ $rx->items->count() }} item(s) &middot; {{ $rx->created_at?->diffForHumans() }}
                    </div>
                </div>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <span class="badge badge-{{ $rx->statusColor() }}">{{ ucfirst(str_replace('_', ' ', $rx->status)) }}</span>
                    <form method="POST" action="{{ route('portals.pharmacy.dispense', $rx->id) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Mark as dispensed?')">
                            Dispense
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">
                <i data-lucide="check-circle-2" style="width:32px;height:32px;display:block;margin:0 auto 8px;color:#86efac;"></i>
                All prescriptions are up to date.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Stock Alerts --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;">Stock Alerts</span>
            <a href="{{ route('portals.pharmacy.inventory') }}" class="btn btn-outline btn-sm">Inventory</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($alerts as $drug)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $drug->medicine_name }}</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ $drug->generic_name }} &middot; {{ $drug->form }} {{ $drug->strength }}</div>
                </div>
                <span class="badge badge-{{ $drug->is_expired ? 'danger' : ($drug->stock_status === 'out_of_stock' ? 'danger' : 'warning') }}">
                    {{ $drug->is_expired ? 'Expired' : ucfirst(str_replace('_', ' ', $drug->stock_status)) }}
                </span>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">No stock alerts.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
