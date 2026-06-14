@extends('layouts.portal')

@section('title', 'Laboratory Portal')

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
@section('breadcrumb_section', 'Dashboard')

@section('content')

<div class="page-head">
    <h2>Laboratory dashboard</h2>
    <p class="page-subtitle">Today's work queue, urgent orders, and recent results.</p>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.lab.orders') }}" class="btn btn-primary btn-sm">
        <i data-lucide="clipboard-list"></i> Work queue
    </a>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

{{-- Pipeline: order → sample → result --}}
<div class="stat-grid mb-6">
    <a href="{{ route('portals.lab.orders') }}?status=pending" class="stat-card stat-card--warning">
        <div class="stat-card__label">Pending orders</div>
        <div class="stat-card__value">{{ $stats['pending'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.lab.samples') }}" class="stat-card stat-card--teal">
        <div class="stat-card__label">Samples collected</div>
        <div class="stat-card__value">{{ $stats['collected'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.lab.orders') }}?status=processing" class="stat-card stat-card--primary">
        <div class="stat-card__label">Processing</div>
        <div class="stat-card__value">{{ $stats['processing'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.lab.results') }}" class="stat-card stat-card--success">
        <div class="stat-card__label">Resulted today</div>
        <div class="stat-card__value">{{ $stats['resulted'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.lab.orders') }}?urgency=urgent" class="stat-card stat-card--danger">
        <div class="stat-card__label">Urgent pending</div>
        <div class="stat-card__value">{{ $stats['urgent'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.lab.results') }}?flag=H" class="stat-card stat-card--danger">
        <div class="stat-card__label">Abnormal today</div>
        <div class="stat-card__value">{{ $stats['abnormal'] ?? 0 }}</div>
    </a>
</div>

<div class="field-grid">

    {{-- Urgent Orders --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="alert-triangle"></i> Urgent orders</h3>
            <a href="{{ route('portals.lab.orders') }}?urgency=urgent" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Test</th><th>Patient</th><th>Status</th><th class="row-actions"></th></tr></thead>
                <tbody>
                @forelse($urgentOrders as $order)
                <tr>
                    <td data-label="Test">
                        <span class="td-strong">{{ $order->test_name ?? '—' }}</span>
                        <div class="td-muted">{{ $order->ordered_at?->diffForHumans() }}</div>
                    </td>
                    <td data-label="Patient">{{ $order->patient?->full_name ?? '—' }}</td>
                    <td data-label="Status"><span class="badge badge-{{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span></td>
                    <td class="row-actions" data-label="">
                        @if($order->status === 'pending')
                        <form method="POST" action="{{ route('portals.lab.orders.collect', $order->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Collect</button>
                        </form>
                        @elseif($order->status === 'collected')
                        <form method="POST" action="{{ route('portals.lab.orders.process', $order->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-primary btn-sm">Process</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="td-muted empty-cell">No urgent pending orders.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Results --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="file-bar-chart"></i> Recent results</h3>
            <a href="{{ route('portals.lab.results') }}" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Parameter</th><th>Value</th><th>Flag</th></tr></thead>
                <tbody>
                @forelse($recentResults as $result)
                <tr>
                    <td data-label="Parameter">
                        <span class="td-strong">{{ $result->parameter_name }}</span>
                        <div class="td-muted">{{ $result->patient?->full_name ?? '—' }}</div>
                    </td>
                    <td data-label="Value">{{ $result->value }} {{ $result->unit }}</td>
                    <td data-label="Flag"><span class="badge badge-{{ $result->isAbnormal() ? 'danger' : 'success' }}">{{ $result->flagLabel() }}</span></td>
                </tr>
                @empty
                <tr><td colspan="3" class="td-muted empty-cell">No results yet today.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
