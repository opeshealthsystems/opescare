@extends('layouts.portal')

@section('title', 'Prescriptions')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Prescriptions')

@section('content')

<div class="page-head">
    <h2>Prescription Register</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="pill"></i>
        Pharmacy Dispense Queue
    </a>
</div>
<p class="page-subtitle mb-4">All facility prescriptions — track status from active to dispensed.</p>

@if(session('success'))
    <div class="alert alert-success mb-4">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Summary chips --}}
<div class="stat-grid">
    <a href="{{ route('portals.staff.prescriptions') }}?status=active" class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="clock"></i></div>
        <div class="stat-card__value">{{ $summary['active'] }}</div>
        <div class="stat-card__label">Active</div>
    </a>
    <a href="{{ route('portals.staff.prescriptions') }}?status=partially_dispensed" class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="loader"></i></div>
        <div class="stat-card__value">{{ $summary['partially_dispensed'] }}</div>
        <div class="stat-card__label">Partial</div>
    </a>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-card__value">{{ $summary['dispensed_today'] }}</div>
        <div class="stat-card__label">Dispensed Today</div>
    </div>
    <a href="{{ route('portals.staff.prescriptions') }}?status=expired" class="stat-card stat-card--danger">
        <div class="stat-card__head"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-card__value">{{ $summary['expired'] }}</div>
        <div class="stat-card__label">Expired</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar mt-6">
    <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="partially_dispensed" {{ request('status') === 'partially_dispensed' ? 'selected' : '' }}>Partially Dispensed</option>
        <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <input type="text" name="search" class="filter-search" placeholder="Patient name…" value="{{ request('search') }}">
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['status','search']))
        <a href="{{ route('portals.staff.prescriptions') }}" class="btn btn-secondary btn-sm">Clear</a>
    @endif
</form>

<div class="panel">
    <div class="panel-body panel-body--flush">
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
                    @php
                        $rxBadge = match($rx->statusColor()) {
                            'success' => 'success',
                            'info'    => 'info',
                            'warning' => 'warning',
                            'danger'  => 'danger',
                            default   => 'neutral',
                        };
                    @endphp
                    <tr>
                        <td data-label="Patient">
                            <div class="td-strong">{{ $rx->patient?->full_name ?? '—' }}</div>
                            <div class="td-muted">{{ $rx->patient?->health_id ?? '' }}</div>
                        </td>
                        <td data-label="Items">
                            @foreach($rx->items->take(2) as $item)
                                <div>{{ $item->drug_name ?? '—' }} {{ $item->dosage ?? '' }}</div>
                            @endforeach
                            @if($rx->items->count() > 2)
                                <div class="td-muted">+{{ $rx->items->count()-2 }} more</div>
                            @endif
                        </td>
                        <td data-label="Prescribed" class="td-muted">{{ $rx->prescribed_at?->format('d M Y') ?? $rx->created_at?->format('d M Y') }}</td>
                        <td data-label="Expires">
                            @if($rx->expires_at)
                                @if($rx->expires_at->isPast())
                                    <span class="badge badge-danger">{{ $rx->expires_at->format('d M Y') }}</span>
                                @else
                                    {{ $rx->expires_at->format('d M Y') }}
                                @endif
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $rxBadge }}">
                                {{ ucfirst(str_replace('_', ' ', $rx->status)) }}
                            </span>
                        </td>
                        <td data-label="Dispensed At" class="td-muted">{{ $rx->dispensed_at?->format('d M Y H:i') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i data-lucide="clipboard-plus"></i></div>
                                <p>No prescriptions found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel-body">{{ $prescriptions->links() }}</div>
</div>

@endsection
