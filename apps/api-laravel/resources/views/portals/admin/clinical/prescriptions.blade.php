@extends('layouts.portal')

@section('title', 'Prescription Register')

@include('portals.admin.clinical._sidebar')

@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Prescription Register')

@section('content')

<div class="page-head">
    <h2>Prescription Register</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="pill"></i> Pharmacy Dispense Queue
    </a>
</div>
<p class="td-muted mb-6">Facility-wide view of all prescriptions — read-only oversight.</p>

{{-- Summary chips --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="clock"></i></div>
        <div class="stat-card__value">{{ $summary['active'] }}</div>
        <div class="stat-card__label">Active</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="loader"></i></div>
        <div class="stat-card__value">{{ $summary['partially_dispensed'] }}</div>
        <div class="stat-card__label">Partial</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-card__value">{{ $summary['dispensed_today'] }}</div>
        <div class="stat-card__label">Dispensed Today</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__head"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-card__value">{{ $summary['expired'] }}</div>
        <div class="stat-card__label">Expired</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
    <select name="status" class="filter-select" aria-label="Status" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="partially_dispensed" {{ request('status') === 'partially_dispensed' ? 'selected' : '' }}>Partially Dispensed</option>
        <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" placeholder="Patient name…" value="{{ request('search') }}" aria-label="Patient">
    </label>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="from" value="{{ request('from') }}" aria-label="From date">
    </label>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="to" value="{{ request('to') }}" aria-label="To date">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
    @if(request()->hasAny(['status','search','from','to']))
        <a href="{{ route('portals.admin.clinical.prescriptions') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Items</th>
                    <th>Prescribed</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Dispensed At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prescriptions as $rx)
                <tr>
                    <td data-label="Patient">
                        <div class="td-strong">{{ $rx->patient?->full_name ?? '—' }}</div>
                        <div class="td-muted">{{ $rx->patient?->health_id ?? '' }}</div>
                    </td>
                    <td data-label="Items">{{ $rx->items->count() }} item(s)</td>
                    <td data-label="Prescribed">{{ $rx->prescribed_at?->format('d M Y') ?? $rx->created_at?->format('d M Y') }}</td>
                    <td data-label="Expires">
                        @if($rx->expires_at)
                            <span class="{{ $rx->expires_at->isPast() ? 'text-danger' : '' }}">{{ $rx->expires_at->format('d M Y') }}</span>
                        @else
                            <span class="td-muted">—</span>
                        @endif
                    </td>
                    <td data-label="Status"><span class="badge badge-{{ $rx->statusColor() }}">{{ ucfirst(str_replace('_', ' ', $rx->status)) }}</span></td>
                    <td data-label="Dispensed At">{{ $rx->dispensed_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="td-muted empty-cell">No prescriptions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $prescriptions->links() }}</div>
</div>

@endsection
