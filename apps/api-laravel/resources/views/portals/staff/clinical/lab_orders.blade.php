@extends('layouts.portal')

@section('title', 'Lab Orders')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Lab Orders')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Lab Orders Register</h1>
        <p class="page-subtitle">All facility lab orders — track from pending through to resulted.</p>
    </div>
    <a href="{{ route('portals.lab.orders') }}" class="btn btn-outline btn-sm">
        <i data-lucide="microscope" style="width:14px;height:14px;"></i>
        Lab Work Queue
    </a>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Summary chips --}}
<div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">
    <a href="{{ route('portals.staff.lab_orders') }}?status=pending" style="text-decoration:none;">
        <div class="stat-card" style="flex:0 0 auto;min-width:120px;">
            <div class="stat-card__icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="clock"></i></div>
            <div class="stat-card__val">{{ $summary['pending'] }}</div>
            <div class="stat-card__label">Pending</div>
        </div>
    </a>
    <a href="{{ route('portals.staff.lab_orders') }}?status=processing" style="text-decoration:none;">
        <div class="stat-card" style="flex:0 0 auto;min-width:120px;">
            <div class="stat-card__icon" style="background:#dbeafe;color:#1d4ed8;"><i data-lucide="loader"></i></div>
            <div class="stat-card__val">{{ $summary['processing'] }}</div>
            <div class="stat-card__label">Processing</div>
        </div>
    </a>
    <div class="stat-card" style="flex:0 0 auto;min-width:120px;">
        <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="check-circle-2"></i></div>
        <div class="stat-card__val">{{ $summary['resulted'] }}</div>
        <div class="stat-card__label">Resulted Today</div>
    </div>
    <a href="{{ route('portals.staff.lab_orders') }}?urgency=urgent" style="text-decoration:none;">
        <div class="stat-card" style="flex:0 0 auto;min-width:120px;">
            <div class="stat-card__icon" style="background:#fee2e2;color:#b91c1c;"><i data-lucide="alert-triangle"></i></div>
            <div class="stat-card__val">{{ $summary['urgent'] }}</div>
            <div class="stat-card__label">Urgent Pending</div>
        </div>
    </a>
</div>

{{-- Filters --}}
<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;align-items:flex-end;">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">All</option>
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
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Test or patient…" value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['status','urgency','search']))
        <a href="{{ route('portals.staff.lab_orders') }}" class="btn btn-outline btn-sm">Clear</a>
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
                    <th>Resulted</th>
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
                        <div style="font-size:.875rem;font-weight:500;">{{ $order->patient?->full_name ?? '—' }}</div>
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
                    <td style="font-size:.83rem;color:#64748b;">{{ $order->resulted_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8;">No lab orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:1rem;">{{ $orders->links() }}</div>

@endsection
