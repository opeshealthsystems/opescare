@extends('layouts.portal')

@section('title', 'Prescription Queue')

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
@section('breadcrumb_section', 'Prescription Queue')

@section('content')

<div class="page-head">
    <h2>Prescription queue</h2>
    <p class="page-subtitle">All prescriptions awaiting dispensing at this facility.</p>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

<form method="GET" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" placeholder="Patient name…" value="{{ request('search') }}" aria-label="Search patient">
    </label>
    <select name="status" class="filter-select" aria-label="Status" onchange="this.form.submit()">
        <option value="">Pending (all)</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="partially_dispensed" {{ request('status') === 'partially_dispensed' ? 'selected' : '' }}>Partially dispensed</option>
        <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    @if(request()->hasAny(['status','search']))
        <a href="{{ route('portals.pharmacy.prescriptions') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Patient</th><th>Items</th><th>Prescribed</th><th>Expires</th><th>Status</th><th class="row-actions">Action</th></tr>
            </thead>
            <tbody>
                @forelse($prescriptions as $rx)
                <tr>
                    <td data-label="Patient">
                        <span class="td-strong">{{ $rx->patient?->full_name ?? '—' }}</span>
                        <div class="td-muted">{{ $rx->patient?->health_id ?? '' }}</div>
                    </td>
                    <td data-label="Items">
                        @foreach($rx->items->take(2) as $item)
                            <div>{{ $item->drug_name }} {{ $item->dosage }}</div>
                        @endforeach
                        @if($rx->items->count() > 2)
                            <div class="td-muted">+{{ $rx->items->count() - 2 }} more</div>
                        @endif
                    </td>
                    <td data-label="Prescribed" class="td-muted">{{ $rx->prescribed_at?->format('d M Y H:i') ?? $rx->created_at?->format('d M Y') }}</td>
                    <td data-label="Expires">
                        @if($rx->expires_at)
                            @if($rx->expires_at->isPast())<span class="badge badge-danger badge-sm">{{ $rx->expires_at->format('d M Y') }}</span>
                            @else<span class="td-muted">{{ $rx->expires_at->format('d M Y') }}</span>@endif
                        @else
                            <span class="td-muted">—</span>
                        @endif
                    </td>
                    <td data-label="Status"><span class="badge badge-{{ $rx->statusColor() }}">{{ ucfirst(str_replace('_', ' ', $rx->status)) }}</span></td>
                    <td class="row-actions" data-label="Action">
                        @if(in_array($rx->status, ['active', 'partially_dispensed']))
                        <form method="POST" action="{{ route('portals.pharmacy.dispense', $rx->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Mark prescription as fully dispensed?')">
                                <i data-lucide="check"></i> Dispense
                            </button>
                        </form>
                        @else
                        <span class="td-muted">—</span>
                        @endif
                    </td>
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
