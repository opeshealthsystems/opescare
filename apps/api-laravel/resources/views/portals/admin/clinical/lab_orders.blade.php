@extends('layouts.portal')

@section('title', 'Lab Orders Register')

@include('portals.admin.clinical._sidebar')

@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Lab Orders Register')

@section('content')

<div class="page-head">
    <h2>Lab Orders Register</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.lab.orders') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="microscope"></i> Lab Work Queue
    </a>
</div>
<p class="td-muted mb-6">Facility-wide view of all lab test orders — read-only oversight.</p>

{{-- Summary chips --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="clock"></i></div>
        <div class="stat-card__value">{{ $summary['pending'] }}</div>
        <div class="stat-card__label">Pending</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="loader"></i></div>
        <div class="stat-card__value">{{ $summary['processing'] }}</div>
        <div class="stat-card__label">Processing</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-card__value">{{ $summary['resulted'] }}</div>
        <div class="stat-card__label">Resulted Today</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__head"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-card__value">{{ $summary['urgent'] }}</div>
        <div class="stat-card__label">Urgent Pending</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
    <select name="status" class="filter-select" aria-label="Status" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>Collected</option>
        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
        <option value="resulted" {{ request('status') === 'resulted' ? 'selected' : '' }}>Resulted</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <select name="urgency" class="filter-select" aria-label="Urgency" onchange="this.form.submit()">
        <option value="">All urgencies</option>
        <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
        <option value="routine" {{ request('urgency') === 'routine' ? 'selected' : '' }}>Routine</option>
    </select>
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" placeholder="Test name or patient…" value="{{ request('search') }}" aria-label="Search">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
    @if(request()->hasAny(['status','urgency','search']))
        <a href="{{ route('portals.admin.clinical.lab_orders') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Patient</th>
                    <th>Urgency</th>
                    <th>Ordered</th>
                    <th>Collected</th>
                    <th>Status</th>
                    <th>Resulted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td data-label="Test">
                        <div class="td-strong">{{ $order->test_name }}</div>
                        @if($order->test_code)<div class="td-muted">{{ $order->test_code }}</div>@endif
                    </td>
                    <td data-label="Patient">
                        <div class="td-strong">{{ $order->patient?->full_name ?? '—' }}</div>
                        <div class="td-muted">{{ $order->patient?->health_id ?? '' }}</div>
                    </td>
                    <td data-label="Urgency">
                        <span class="badge {{ $order->urgency === 'urgent' ? 'badge-danger' : 'badge-neutral' }}">{{ ucfirst($order->urgency ?? 'routine') }}</span>
                    </td>
                    <td data-label="Ordered">{{ $order->ordered_at?->format('d M Y H:i') ?? $order->created_at?->format('d M Y') }}</td>
                    <td data-label="Collected">{{ $order->collected_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td data-label="Status"><span class="badge badge-{{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span></td>
                    <td data-label="Resulted">{{ $order->resulted_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="td-muted empty-cell">No lab orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $orders->links() }}</div>
</div>

@endsection
