@extends('layouts.portal')

@section('title', 'Insurance Portal')

@section('sidebar_role_badge')
<div class="sidebar-role-badge sidebar-role-badge--primary">
    <i data-lucide="shield-check"></i>
    Insurance
</div>
@endsection
@section('sidebar_user_role', 'Insurance Admin')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Insurance</div>
    <a href="{{ route('portals.insurance.dashboard') }}" class="sidebar-link {{ request()->routeIs('portals.insurance.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('portals.insurance.providers') }}" class="sidebar-link {{ request()->routeIs('portals.insurance.providers') ? 'active' : '' }}">
        <i data-lucide="building-2"></i><span>Providers & Plans</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link {{ request()->routeIs('portals.insurance.policies') ? 'active' : '' }}">
        <i data-lucide="shield-check"></i><span>Patient Policies</span>
    </a>
    <a href="{{ route('portals.insurance.preauths') }}" class="sidebar-link {{ request()->routeIs('portals.insurance.preauths') ? 'active' : '' }}">
        <i data-lucide="clipboard-list"></i><span>Preauthorization</span>
    </a>
    <a href="{{ route('portals.insurance.claims') }}" class="sidebar-link {{ request()->routeIs('portals.insurance.claims') ? 'active' : '' }}">
        <i data-lucide="file-text"></i><span>Claims</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', 'Insurance Portal')
@section('breadcrumb_home_url', route('portals.insurance.dashboard'))
@section('breadcrumb_section', 'Dashboard')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Insurance Portal</h1>
        <p class="page-subtitle">Overview of providers, policies, authorizations and claims.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Stat cards --}}
<div class="stat-grid mb-6">
    <a href="{{ route('portals.insurance.providers') }}" class="stat-card stat-card--primary">
        <div class="stat-card__label">Providers</div>
        <div class="stat-card__value">{{ $stats['providers'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.insurance.providers') }}" class="stat-card stat-card--primary">
        <div class="stat-card__label">Active Plans</div>
        <div class="stat-card__value">{{ $stats['active_plans'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="stat-card stat-card--success">
        <div class="stat-card__label">Policies</div>
        <div class="stat-card__value">{{ $stats['policies'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.insurance.preauths') }}" class="stat-card stat-card--warning">
        <div class="stat-card__label">Pending Preauth</div>
        <div class="stat-card__value">{{ $stats['pending_auth'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.insurance.claims') }}" class="stat-card stat-card--danger">
        <div class="stat-card__label">Open Claims</div>
        <div class="stat-card__value">{{ $stats['open_claims'] ?? 0 }}</div>
    </a>
    <a href="{{ route('portals.insurance.claims') }}" class="stat-card stat-card--success">
        <div class="stat-card__label">Paid Claims</div>
        <div class="stat-card__value">{{ $stats['paid_claims'] ?? 0 }}</div>
    </a>
    <div class="stat-card">
        <div class="stat-card__icon"><i data-lucide="banknote"></i></div>
        <div class="stat-card__value">{{ number_format($stats['total_claim_value'] ?? 0) }} XAF</div>
        <div class="stat-card__label">Total Claims Value</div>
    </div>
</div>

<div class="grid-2">

    {{-- Recent Claims --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Recent Claims</h2>
            <a href="{{ route('portals.insurance.claims') }}" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="panel-body--flush">
            @forelse($recentClaims as $claim)
            <div class="list-row">
                <div class="list-row__main">
                    <div class="td-strong">{{ $claim->patient?->full_name ?? 'Unknown Patient' }}</div>
                    <div class="td-muted">{{ $claim->created_at?->format('d M Y') }}</div>
                </div>
                <span class="badge badge-{{ match($claim->status) { 'paid' => 'success', 'submitted' => 'info', 'rejected' => 'danger', 'cancelled' => 'neutral', default => 'warning' } }}">
                    {{ ucfirst($claim->status) }}
                </span>
            </div>
            @empty
            <div class="empty-state"><p>No claims yet.</p></div>
            @endforelse
        </div>
    </div>

    {{-- Recent Preauths --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Recent Preauthorizations</h2>
            <a href="{{ route('portals.insurance.preauths') }}" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="panel-body--flush">
            @forelse($recentPreauths as $auth)
            <div class="list-row">
                <div class="list-row__main">
                    <div class="td-strong">{{ $auth->patient?->full_name ?? 'Unknown Patient' }}</div>
                    <div class="td-muted">{{ $auth->created_at?->format('d M Y') }}</div>
                </div>
                <span class="badge badge-{{ match($auth->status) { 'approved' => 'success', 'submitted' => 'info', 'rejected' => 'danger', 'cancelled' => 'neutral', default => 'warning' } }}">
                    {{ ucfirst($auth->status) }}
                </span>
            </div>
            @empty
            <div class="empty-state"><p>No preauth requests yet.</p></div>
            @endforelse
        </div>
    </div>

</div>

@endsection
