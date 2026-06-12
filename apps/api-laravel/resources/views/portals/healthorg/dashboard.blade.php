@extends('layouts.portal')

@section('title', 'Health Organization Portal')

@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(245,158,11,.15);border-color:rgba(245,158,11,.4);color:#fbbf24;">
    <i data-lucide="heart-handshake" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    Health Org
</div>
@endsection
@section('sidebar_user_role', 'Health Org Admin')

@section('sidebar_nav')
@include('portals.healthorg._sidebar')
@endsection

@section('breadcrumb_home', 'Health Org Portal')
@section('breadcrumb_home_url', route('portals.healthorg.dashboard'))
@section('breadcrumb_section', 'Dashboard')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Health Organization Portal</h1>
        <p class="page-subtitle">Programs, outreach coordination, and public health reporting.</p>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#dbeafe;color:#1d4ed8;"><i data-lucide="users"></i></div>
        <div class="stat-card__val">{{ number_format($stats['patients']) }}</div>
        <div class="stat-card__label">Registered Patients</div>
    </div>
    <a href="{{ route('portals.healthorg.programs') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="building-2"></i></div>
            <div class="stat-card__val">{{ $stats['facilities'] }}</div>
            <div class="stat-card__label">Active Facilities</div>
        </div>
    </a>
    <a href="{{ route('portals.healthorg.reports') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="file-bar-chart-2"></i></div>
            <div class="stat-card__val">{{ $stats['reports_draft'] }}</div>
            <div class="stat-card__label">Draft Reports</div>
        </div>
    </a>
    <a href="{{ route('portals.healthorg.reports') }}" style="text-decoration:none;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="send"></i></div>
            <div class="stat-card__val">{{ $stats['reports_sent'] }}</div>
            <div class="stat-card__label">Submitted Reports</div>
        </div>
    </a>
</div>

{{-- Quick Actions --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header" style="font-weight:700;">Quick Actions</div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;">
            <a href="{{ route('portals.healthorg.programs') }}" class="btn btn-outline" style="justify-content:flex-start;gap:.75rem;">
                <i data-lucide="folder-open" style="width:18px;height:18px;color:#7c3aed;"></i>
                View Programs
            </a>
            <a href="{{ route('portals.healthorg.outreach') }}" class="btn btn-outline" style="justify-content:flex-start;gap:.75rem;">
                <i data-lucide="map-pin" style="width:18px;height:18px;color:#0ea5e9;"></i>
                Outreach Sites
            </a>
            <a href="{{ route('portals.healthorg.reports') }}" class="btn btn-outline" style="justify-content:flex-start;gap:.75rem;">
                <i data-lucide="file-bar-chart-2" style="width:18px;height:18px;color:#f59e0b;"></i>
                Public Health Reports
            </a>
            <a href="{{ route('portals.healthorg.signals') }}" class="btn btn-outline" style="justify-content:flex-start;gap:.75rem;">
                <i data-lucide="activity" style="width:18px;height:18px;color:#ef4444;"></i>
                Outbreak Signals
            </a>
            <a href="{{ route('public.care-map') }}" target="_blank" class="btn btn-outline" style="justify-content:flex-start;gap:.75rem;">
                <i data-lucide="map" style="width:18px;height:18px;color:#10b981;"></i>
                Care Map
            </a>
            <a href="{{ route('portals.healthorg.outreach') }}" class="btn btn-outline" style="justify-content:flex-start;gap:.75rem;">
                <i data-lucide="syringe" style="width:18px;height:18px;color:#8b5cf6;"></i>
                Immunization Outreach
            </a>
        </div>
    </div>
</div>

{{-- Info banner --}}
<div class="auth-alert" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;margin-bottom:0;">
    <i data-lucide="info"></i>
    <div>
        Advanced public health reporting, disease surveillance, and outbreak intelligence are available via the
        <strong>Public Health API</strong> at <code>/api/v1/public-health</code>.
        Use the <a href="{{ route('portals.developer.dashboard') }}" style="color:#1d4ed8;">Developer Portal</a> to obtain API credentials.
    </div>
</div>

@endsection
