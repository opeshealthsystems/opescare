@extends('layouts.portal')

@section('title', 'Lab Work Queue')

@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(14,165,233,.15);border-color:rgba(14,165,233,.4);color:#38bdf8;">
    <i data-lucide="microscope" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
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

<div class="page-header">
    <div>
        <h1 class="page-title">Lab Work Queue</h1>
        <p class="page-subtitle">All incoming test orders — filter by status or urgency.</p>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;align-items:flex-end;">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">Active (all)</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>Collected</option>
            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
            <option value="resulted" {{ request('status') === 'resulted' ? 'selected' : '' }}>Resulted</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>
    <div>
        <label class="form-label">Urgency</label>
        <select name="urgency" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="routine" {{ request('urgency') === 'routine' ? 'selected' : '' }}>Routine</option>
        </select>
    </div>
    <div>
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Test name or patient…" value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['status','urgency','search']))
        <a href="{{ route('portals.lab.orders') }}" class="btn btn-outline btn-sm">Clear</a>
    @endif
</form>

<div class="card" style="overflow:hidden;">
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Patient</th>
                    <th>Urgency</th>
                    <th>Ordered</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>
                        <div style="font-weight:600;">{{ $order->test_name }}</div>
                        @if($order->test_code)
                        <div style="font-size:.75rem;color:#64748b;">{{ $order->test_code }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:500;font-size:.875rem;">{{ $order->patient?->full_name ?? '—' }}</div>
                        <div style="font-size:.75rem;color:#64748b;">{{ $order->patient?->health_id ?? '' }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $order->urgency === 'urgent' ? 'badge-danger' : 'badge-default' }}">
                            {{ ucfirst($order->urgency ?? 'routine') }}
                        </span>
                    </td>
                    <td style="font-size:.83rem;color:#64748b;">{{ $order->ordered_at?->format('d M H:i') ?? $order->created_at?->format('d M') }}</td>
                    <td>
                        <span class="badge badge-{{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td>
                        <div style="display:flex;gap:.4rem;">
                            @if($order->status === 'pending')
                            <form method="POST" action="{{ route('portals.lab.orders.collect', $order->id) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm">Collect</button>
                            </form>
                            @elseif($order->status === 'collected')
                            <form method="POST" action="{{ route('portals.lab.orders.process', $order->id) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Process</button>
                            </form>
                            @else
                            <span style="color:#94a3b8;font-size:.8rem;">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8;">No orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:1rem;">{{ $orders->links() }}</div>

@endsection
