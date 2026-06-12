@extends('layouts.portal')

@section('title', 'Prescription Register')

@include('portals.admin.clinical._sidebar')

@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Prescription Register')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Prescription Register</h1>
        <p class="page-subtitle">Facility-wide view of all prescriptions — read-only oversight.</p>
    </div>
    <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-outline btn-sm">
        <i data-lucide="pill" style="width:14px;height:14px;"></i>
        Pharmacy Dispense Queue
    </a>
</div>

{{-- Summary chips --}}
<div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">
    <div class="stat-card" style="flex:0 0 auto;min-width:130px;">
        <div class="stat-card__icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="clock"></i></div>
        <div class="stat-card__val">{{ $summary['active'] }}</div>
        <div class="stat-card__label">Active</div>
    </div>
    <div class="stat-card" style="flex:0 0 auto;min-width:130px;">
        <div class="stat-card__icon" style="background:#dbeafe;color:#1d4ed8;"><i data-lucide="loader"></i></div>
        <div class="stat-card__val">{{ $summary['partially_dispensed'] }}</div>
        <div class="stat-card__label">Partial</div>
    </div>
    <div class="stat-card" style="flex:0 0 auto;min-width:130px;">
        <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-card__val">{{ $summary['dispensed_today'] }}</div>
        <div class="stat-card__label">Dispensed Today</div>
    </div>
    <div class="stat-card" style="flex:0 0 auto;min-width:130px;">
        <div class="stat-card__icon" style="background:#f3e8ff;color:#7c3aed;"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-card__val">{{ $summary['expired'] }}</div>
        <div class="stat-card__label">Expired</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;align-items:flex-end;">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="partially_dispensed" {{ request('status') === 'partially_dispensed' ? 'selected' : '' }}>Partially Dispensed</option>
            <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>
    <div>
        <label class="form-label">Patient</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Patient name…" value="{{ request('search') }}">
    </div>
    <div>
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
    </div>
    <div>
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['status','search','from','to']))
        <a href="{{ route('portals.admin.clinical.prescriptions') }}" class="btn btn-outline btn-sm">Clear</a>
    @endif
</form>

<div class="card" style="overflow:hidden;">
    <div class="card-body" style="padding:0;overflow-x:auto;">
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
                    <td>
                        <div style="font-weight:600;">{{ $rx->patient?->full_name ?? '—' }}</div>
                        <div style="font-size:.75rem;color:#64748b;">{{ $rx->patient?->health_id ?? '' }}</div>
                    </td>
                    <td style="font-size:.83rem;color:#64748b;">{{ $rx->items->count() }} item(s)</td>
                    <td style="font-size:.83rem;">{{ $rx->prescribed_at?->format('d M Y') ?? $rx->created_at?->format('d M Y') }}</td>
                    <td style="font-size:.83rem;">
                        @if($rx->expires_at)
                            <span class="{{ $rx->expires_at->isPast() ? 'text-danger' : '' }}">
                                {{ $rx->expires_at->format('d M Y') }}
                            </span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $rx->statusColor() }}">
                            {{ ucfirst(str_replace('_', ' ', $rx->status)) }}
                        </span>
                    </td>
                    <td style="font-size:.83rem;color:#64748b;">
                        {{ $rx->dispensed_at?->format('d M Y H:i') ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8;">No prescriptions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:1rem;">{{ $prescriptions->links() }}</div>

@endsection
