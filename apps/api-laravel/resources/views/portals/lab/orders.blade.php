@extends('layouts.portal')

@section('title', 'Lab Work Queue')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="microscope"></i>
    Laboratory
</div>
@endsection
@section('sidebar_user_role', 'Lab Technician')

@section('sidebar_nav')
@include('portals.lab._sidebar')
@endsection

@section('breadcrumb_home', 'Lab Portal')
@section('breadcrumb_home_url', route('portals.lab.dashboard'))
@section('breadcrumb_section', 'Work Queue')

@section('content')

<div class="page-head">
    <h2>Lab work queue</h2>
    <p class="page-subtitle">All incoming test orders — filter by status or urgency.</p>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

<form method="GET" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" placeholder="Test name or patient…" value="{{ request('search') }}" aria-label="Search orders">
    </label>
    <select name="status" class="filter-select" aria-label="Status" onchange="this.form.submit()">
        <option value="">Active (all)</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>Collected</option>
        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
        <option value="resulted" {{ request('status') === 'resulted' ? 'selected' : '' }}>Resulted</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <select name="urgency" class="filter-select" aria-label="Urgency" onchange="this.form.submit()">
        <option value="">All urgency</option>
        <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
        <option value="routine" {{ request('urgency') === 'routine' ? 'selected' : '' }}>Routine</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    @if(request()->hasAny(['status','urgency','search']))
        <a href="{{ route('portals.lab.orders') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Test</th><th>Patient</th><th>Urgency</th><th>Ordered</th><th>Status</th><th class="row-actions">Action</th></tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td data-label="Test">
                        <span class="td-strong">{{ $order->test_name }}</span>
                        @if($order->test_code)<div class="td-muted">{{ $order->test_code }}</div>@endif
                    </td>
                    <td data-label="Patient">
                        <span class="td-strong">{{ $order->patient?->full_name ?? '—' }}</span>
                        <div class="td-muted">{{ $order->patient?->health_id ?? '' }}</div>
                    </td>
                    <td data-label="Urgency">
                        <span class="badge {{ $order->urgency === 'urgent' ? 'badge-danger' : 'badge-neutral' }}">{{ ucfirst($order->urgency ?? 'routine') }}</span>
                    </td>
                    <td data-label="Ordered" class="td-muted">{{ $order->ordered_at?->format('d M H:i') ?? $order->created_at?->format('d M') }}</td>
                    <td data-label="Status"><span class="badge badge-{{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span></td>
                    <td class="row-actions" data-label="Action">
                        @if($order->status === 'pending')
                        <form method="POST" action="{{ route('portals.lab.orders.collect', $order->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Collect</button>
                        </form>
                        @elseif($order->status === 'collected')
                        <form method="POST" action="{{ route('portals.lab.orders.process', $order->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-primary btn-sm">Process</button>
                        </form>
                        @else
                        <span class="td-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="td-muted empty-cell">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $orders->links() }}</div>
</div>

@endsection
