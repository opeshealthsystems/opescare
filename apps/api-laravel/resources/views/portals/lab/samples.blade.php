@extends('layouts.portal')

@section('title', 'Sample Tracking')

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
@section('breadcrumb_section', 'Sample Tracking')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Sample Tracking</h1>
        <p class="page-subtitle">Track samples from order through collection to the bench.</p>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    {{-- Awaiting Collection --}}
    <div class="card">
        <div class="card-header" style="font-weight:700;display:flex;align-items:center;gap:.5rem;">
            <i data-lucide="clock" style="width:15px;height:15px;color:#f59e0b;"></i>
            Awaiting Collection ({{ $pending->count() }})
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($pending as $order)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $order->test_name }}</div>
                    <div style="font-size:.75rem;color:#64748b;">
                        {{ $order->patient?->full_name ?? '—' }}
                        @if($order->urgency === 'urgent')
                            &middot; <span style="color:#ef4444;font-weight:600;">URGENT</span>
                        @endif
                    </div>
                    <div style="font-size:.72rem;color:#94a3b8;">Ordered {{ $order->ordered_at?->diffForHumans() ?? '' }}</div>
                </div>
                <form method="POST" action="{{ route('portals.lab.orders.collect', $order->id) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-lucide="test-tube" style="width:13px;height:13px;"></i>
                        Collect
                    </button>
                </form>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">No samples awaiting collection.</div>
            @endforelse
        </div>
    </div>

    {{-- Collected — Ready to Process --}}
    <div class="card">
        <div class="card-header" style="font-weight:700;display:flex;align-items:center;gap:.5rem;">
            <i data-lucide="test-tube" style="width:15px;height:15px;color:#0ea5e9;"></i>
            Collected — Ready to Process ({{ $collected->count() }})
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($collected as $order)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $order->test_name }}</div>
                    <div style="font-size:.75rem;color:#64748b;">
                        {{ $order->patient?->full_name ?? '—' }}
                        @if($order->urgency === 'urgent')
                            &middot; <span style="color:#ef4444;font-weight:600;">URGENT</span>
                        @endif
                    </div>
                    <div style="font-size:.72rem;color:#94a3b8;">Collected {{ $order->collected_at?->diffForHumans() ?? '' }}</div>
                </div>
                <form method="POST" action="{{ route('portals.lab.orders.process', $order->id) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">
                        <i data-lucide="loader" style="width:13px;height:13px;"></i>
                        Process
                    </button>
                </form>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">No collected samples waiting.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
