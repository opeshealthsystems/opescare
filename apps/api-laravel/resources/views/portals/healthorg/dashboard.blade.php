@extends('layouts.portal')

@section('title', 'Health Organization Portal')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="heart-handshake"></i>
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
    <div class="alert alert-success mb-4">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif

{{-- Stat Cards --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__label">Registered Patients</div>
        <div class="stat-card__value">{{ number_format($stats['patients']) }}</div>
    </div>
    <a href="{{ route('portals.healthorg.programs') }}" class="stat-card stat-card--success">
        <div class="stat-card__label">Active Facilities</div>
        <div class="stat-card__value">{{ $stats['facilities'] }}</div>
    </a>
    <a href="{{ route('portals.healthorg.reports') }}" class="stat-card stat-card--warning">
        <div class="stat-card__label">Draft Reports</div>
        <div class="stat-card__value">{{ $stats['reports_draft'] }}</div>
    </a>
    <a href="{{ route('portals.healthorg.reports') }}" class="stat-card stat-card--success">
        <div class="stat-card__label">Submitted Reports</div>
        <div class="stat-card__value">{{ $stats['reports_sent'] }}</div>
    </a>
</div>

{{-- Quick Actions --}}
<div class="panel mb-6">
    <div class="panel-header"><h2 class="panel-title">Quick Actions</h2></div>
    <div class="panel-body">
        <div class="card-grid">
            <a href="{{ route('portals.healthorg.programs') }}" class="nav-card">
                <div class="nav-card__head">
                    <i data-lucide="folder-open"></i>
                    <span class="nav-card__title">View Programs</span>
                </div>
            </a>
            <a href="{{ route('portals.healthorg.outreach') }}" class="nav-card">
                <div class="nav-card__head">
                    <i data-lucide="map-pin"></i>
                    <span class="nav-card__title">Outreach Sites</span>
                </div>
            </a>
            <a href="{{ route('portals.healthorg.reports') }}" class="nav-card">
                <div class="nav-card__head">
                    <i data-lucide="file-bar-chart-2"></i>
                    <span class="nav-card__title">Public Health Reports</span>
                </div>
            </a>
            <a href="{{ route('portals.healthorg.signals') }}" class="nav-card nav-card--danger">
                <div class="nav-card__head">
                    <i data-lucide="activity"></i>
                    <span class="nav-card__title">Outbreak Signals</span>
                </div>
            </a>
            <a href="{{ route('public.care-map') }}" target="_blank" class="nav-card">
                <div class="nav-card__head">
                    <i data-lucide="map"></i>
                    <span class="nav-card__title">Care Map</span>
                </div>
            </a>
            <a href="{{ route('portals.healthorg.outreach') }}" class="nav-card">
                <div class="nav-card__head">
                    <i data-lucide="syringe"></i>
                    <span class="nav-card__title">Immunization Outreach</span>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- Info banner --}}
<div class="alert alert-info">
    <i data-lucide="info"></i>
    <div>
        Advanced public health reporting, disease surveillance, and outbreak intelligence are available via the
        <strong>Public Health API</strong> at <code class="mono">/api/v1/public-health</code>.
        Use the <a href="{{ route('portals.developer.dashboard') }}">Developer Portal</a> to obtain API credentials.
    </div>
</div>

@endsection
