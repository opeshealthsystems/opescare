@extends('layouts.portal')

@section('title', 'Prescription Queue')

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
@section('breadcrumb_section', 'Prescription Queue')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Prescription Queue</h1>
        <p class="page-subtitle">All prescriptions awaiting dispensing at this facility.</p>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Filters --}}
<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;align-items:flex-end;">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">Pending (all)</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="partially_dispensed" {{ request('status') === 'partially_dispensed' ? 'selected' : '' }}>Partially Dispensed</option>
            <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
        </select>
    </div>
    <div>
        <label class="form-label">Search Patient</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Patient name…" value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['status','search']))
        <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-outline btn-sm">Clear</a>
    @endif
</form>

<div class="card" style="overflow:hidden;">
    <div class="card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Items</th>
                        <th>Prescribed</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $rx)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $rx->patient?->full_name ?? '—' }}</div>
                            <div style="font-size:.75rem;color:#64748b;">{{ $rx->patient?->health_id ?? '' }}</div>
                        </td>
                        <td>
                            @foreach($rx->items->take(2) as $item)
                                <div style="font-size:.8rem;">{{ $item->drug_name }} {{ $item->dosage }}</div>
                            @endforeach
                            @if($rx->items->count() > 2)
                                <div style="font-size:.75rem;color:#94a3b8;">+{{ $rx->items->count() - 2 }} more</div>
                            @endif
                        </td>
                        <td style="font-size:.85rem;">{{ $rx->prescribed_at?->format('d M Y H:i') ?? $rx->created_at?->format('d M Y') }}</td>
                        <td style="font-size:.85rem;">
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
                        <td>
                            @if(in_array($rx->status, ['active', 'partially_dispensed']))
                            <form method="POST" action="{{ route('portals.pharmacy.dispense', $rx->id) }}" style="margin:0;display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Mark prescription as fully dispensed?')">
                                    <i data-lucide="check" style="width:13px;height:13px;"></i>
                                    Dispense
                                </button>
                            </form>
                            @else
                            <span style="font-size:.8rem;color:#94a3b8;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8;">
                            No prescriptions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top:1rem;">
    {{ $prescriptions->links() }}
</div>

@endsection
