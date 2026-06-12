@extends('layouts.portal')

@section('title', 'Insurance Portal')

@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.4);color:#818cf8;">
    <i data-lucide="shield-check" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
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
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <a href="{{ route('portals.insurance.providers') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;color:#7c3aed;"><i data-lucide="building-2"></i></div>
            <div class="stat-card__val">{{ $stats['providers'] }}</div>
            <div class="stat-card__label">Providers</div>
        </div>
    </a>
    <a href="{{ route('portals.insurance.providers') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dbeafe;color:#1d4ed8;"><i data-lucide="layers"></i></div>
            <div class="stat-card__val">{{ $stats['active_plans'] }}</div>
            <div class="stat-card__label">Active Plans</div>
        </div>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="shield-check"></i></div>
            <div class="stat-card__val">{{ $stats['policies'] }}</div>
            <div class="stat-card__label">Policies</div>
        </div>
    </a>
    <a href="{{ route('portals.insurance.preauths') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="clock"></i></div>
            <div class="stat-card__val">{{ $stats['pending_auth'] }}</div>
            <div class="stat-card__label">Pending Preauth</div>
        </div>
    </a>
    <a href="{{ route('portals.insurance.claims') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;color:#b91c1c;"><i data-lucide="file-text"></i></div>
            <div class="stat-card__val">{{ $stats['open_claims'] }}</div>
            <div class="stat-card__label">Open Claims</div>
        </div>
    </a>
    <a href="{{ route('portals.insurance.claims') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="check-circle-2"></i></div>
            <div class="stat-card__val">{{ $stats['paid_claims'] }}</div>
            <div class="stat-card__label">Paid Claims</div>
        </div>
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    {{-- Recent Claims --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;">Recent Claims</span>
            <a href="{{ route('portals.insurance.claims') }}" class="btn btn-outline btn-sm">View all</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($recentClaims as $claim)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $claim->patient?->full_name ?? 'Unknown Patient' }}</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ $claim->created_at?->format('d M Y') }}</div>
                </div>
                <span class="badge badge-{{ match($claim->status) { 'paid' => 'success', 'submitted' => 'info', 'rejected' => 'danger', 'cancelled' => 'default', default => 'warning' } }}">
                    {{ ucfirst($claim->status) }}
                </span>
            </div>
            @empty
            <div style="padding:1.5rem;text-align:center;color:#94a3b8;font-size:.875rem;">No claims yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Preauths --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;">Recent Preauthorizations</span>
            <a href="{{ route('portals.insurance.preauths') }}" class="btn btn-outline btn-sm">View all</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($recentPreauths as $auth)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $auth->patient?->full_name ?? 'Unknown Patient' }}</div>
                    <div style="font-size:.75rem;color:#64748b;">{{ $auth->created_at?->format('d M Y') }}</div>
                </div>
                <span class="badge badge-{{ match($auth->status) { 'approved' => 'success', 'submitted' => 'info', 'rejected' => 'danger', 'cancelled' => 'default', default => 'warning' } }}">
                    {{ ucfirst($auth->status) }}
                </span>
            </div>
            @empty
            <div style="padding:1.5rem;text-align:center;color:#94a3b8;font-size:.875rem;">No preauth requests yet.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
