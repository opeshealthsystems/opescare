@extends('layouts.portal')

@section('title', 'Laboratory Portal')

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
@section('breadcrumb_section', 'Dashboard')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Laboratory Dashboard</h1>
        <p class="page-subtitle">Today's work queue, urgent orders, and recent results.</p>
    </div>
    <a href="{{ route('portals.lab.orders') }}" class="btn btn-primary btn-sm">
        <i data-lucide="clipboard-list" style="width:14px;height:14px;"></i>
        Work Queue
    </a>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <a href="{{ route('portals.lab.orders') }}?status=pending" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="clock"></i></div>
            <div class="stat-card__val">{{ $stats['pending'] }}</div>
            <div class="stat-card__label">Pending Orders</div>
        </div>
    </a>
    <a href="{{ route('portals.lab.samples') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#e0f2fe;color:#0369a1;"><i data-lucide="test-tube"></i></div>
            <div class="stat-card__val">{{ $stats['collected'] }}</div>
            <div class="stat-card__label">Samples Collected</div>
        </div>
    </a>
    <a href="{{ route('portals.lab.orders') }}?status=processing" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dbeafe;color:#1d4ed8;"><i data-lucide="loader"></i></div>
            <div class="stat-card__val">{{ $stats['processing'] }}</div>
            <div class="stat-card__label">Processing</div>
        </div>
    </a>
    <a href="{{ route('portals.lab.results') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="check-circle-2"></i></div>
            <div class="stat-card__val">{{ $stats['resulted'] }}</div>
            <div class="stat-card__label">Resulted Today</div>
        </div>
    </a>
    <a href="{{ route('portals.lab.orders') }}?urgency=urgent" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;color:#b91c1c;"><i data-lucide="alert-triangle"></i></div>
            <div class="stat-card__val">{{ $stats['urgent'] }}</div>
            <div class="stat-card__label">Urgent Pending</div>
        </div>
    </a>
    <a href="{{ route('portals.lab.results') }}?flag=H" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f3e8ff;color:#7c3aed;"><i data-lucide="activity"></i></div>
            <div class="stat-card__val">{{ $stats['abnormal'] }}</div>
            <div class="stat-card__label">Abnormal Today</div>
        </div>
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    {{-- Urgent Orders --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;display:flex;align-items:center;gap:.5rem;">
                <i data-lucide="alert-triangle" style="width:15px;height:15px;color:#ef4444;"></i>
                Urgent Orders
            </span>
            <a href="{{ route('portals.lab.orders') }}?urgency=urgent" class="btn btn-outline btn-sm">View all</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($urgentOrders as $order)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $order->test_name }}</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ $order->patient?->full_name ?? '—' }} &middot; {{ $order->ordered_at?->diffForHumans() }}</div>
                </div>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <span class="badge badge-{{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span>
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
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">No urgent pending orders.</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Results --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;">Recent Results</span>
            <a href="{{ route('portals.lab.results') }}" class="btn btn-outline btn-sm">View all</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($recentResults as $result)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $result->parameter_name }}</div>
                    <div style="font-size:.75rem;color:#64748b;">
                        {{ $result->patient?->full_name ?? '—' }} &middot;
                        {{ $result->value }} {{ $result->unit }}
                    </div>
                </div>
                <span class="badge badge-{{ $result->isAbnormal() ? 'danger' : 'success' }}">
                    {{ $result->flagLabel() }}
                </span>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">No results yet today.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
