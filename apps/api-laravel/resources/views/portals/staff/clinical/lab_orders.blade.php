@extends('layouts.portal')

@section('title', 'Lab Orders')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Lab Orders')

@section('content')

<div class="page-head">
    <h2>Lab Orders Register</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.lab.orders') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="microscope"></i>
        Lab Work Queue
    </a>
</div>
<p class="page-subtitle mb-4">All facility lab orders — track from pending through to resulted.</p>

@if(session('success'))
    <div class="alert alert-success mb-4">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Summary chips --}}
<div class="stat-grid">
    <a href="{{ route('portals.staff.lab_orders') }}?status=pending" class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="clock"></i></div>
        <div class="stat-card__value">{{ $summary['pending'] }}</div>
        <div class="stat-card__label">Pending</div>
    </a>
    <a href="{{ route('portals.staff.lab_orders') }}?status=processing" class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="loader"></i></div>
        <div class="stat-card__value">{{ $summary['processing'] }}</div>
        <div class="stat-card__label">Processing</div>
    </a>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-card__value">{{ $summary['resulted'] }}</div>
        <div class="stat-card__label">Resulted Today</div>
    </div>
    <a href="{{ route('portals.staff.lab_orders') }}?urgency=urgent" class="stat-card stat-card--danger">
        <div class="stat-card__head"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-card__value">{{ $summary['urgent'] }}</div>
        <div class="stat-card__label">Urgent Pending</div>
    </a>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar mt-6">
    <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>Collected</option>
        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
        <option value="resulted" {{ request('status') === 'resulted' ? 'selected' : '' }}>Resulted</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <select name="urgency" class="filter-select" onchange="this.form.submit()">
        <option value="">All urgency</option>
        <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
        <option value="routine" {{ request('urgency') === 'routine' ? 'selected' : '' }}>Routine</option>
    </select>
    <input type="text" name="search" class="filter-search" placeholder="Test or patient…" value="{{ request('search') }}">
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['status','urgency','search']))
        <a href="{{ route('portals.staff.lab_orders') }}" class="btn btn-secondary btn-sm">Clear</a>
    @endif
</form>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Test</th>
                        <th>Patient</th>
                        <th>Urgency</th>
                        <th>Ordered</th>
                        <th>Status</th>
                        <th>Resulted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    @php
                        $orderBadge = match($order->statusColor()) {
                            'success' => 'success',
                            'info'    => 'info',
                            'warning' => 'warning',
                            'danger'  => 'danger',
                            default   => 'neutral',
                        };
                    @endphp
                    <tr>
                        <td data-label="Test">
                            <div class="td-strong">{{ $order->test_name }}</div>
                            @if($order->test_code)
                            <div class="td-muted mono">{{ $order->test_code }}</div>
                            @endif
                        </td>
                        <td data-label="Patient">
                            <div class="td-strong">{{ $order->patient?->full_name ?? '—' }}</div>
                            <div class="td-muted">{{ $order->patient?->health_id ?? '' }}</div>
                        </td>
                        <td data-label="Urgency">
                            <span class="badge {{ $order->urgency === 'urgent' ? 'badge-danger' : 'badge-neutral' }}">
                                {{ ucfirst($order->urgency ?? 'routine') }}
                            </span>
                        </td>
                        <td data-label="Ordered" class="td-muted">{{ $order->ordered_at?->format('d M H:i') ?? $order->created_at?->format('d M') }}</td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $orderBadge }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td data-label="Resulted" class="td-muted">{{ $order->resulted_at?->format('d M Y H:i') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i data-lucide="flask-conical"></i></div>
                                <p>No lab orders found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel-body">{{ $orders->links() }}</div>
</div>

@endsection
