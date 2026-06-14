@extends('layouts.portal')

@section('title', 'Outreach Sites')

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
@section('breadcrumb_section', 'Outreach Sites')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Outreach & Mobile Clinic Sites</h1>
        <p class="page-subtitle">Active outreach locations visible on the Care Map.</p>
    </div>
    <a href="{{ route('public.care-map') }}" target="_blank" class="btn btn-secondary btn-sm">
        <i data-lucide="external-link"></i> Open Care Map
    </a>
</div>

@forelse($sites as $site)
<div class="panel mb-3">
    <div class="panel-body">
        <div class="list-row">
            <div class="list-row__main">
                <div class="td-strong">{{ $site->name }}</div>
                <div class="td-muted">
                    {{ ucfirst(str_replace('_', ' ', $site->type)) }}
                    &middot;
                    <span class="badge badge-{{ $site->status === 'active' ? 'success' : 'neutral' }}">{{ ucfirst($site->status) }}</span>
                </div>
            </div>
            <a href="{{ route('public.care-map.profile', $site->id) }}" target="_blank" class="btn btn-secondary btn-sm">
                <i data-lucide="map-pin"></i> Profile
            </a>
        </div>
    </div>
</div>
@empty
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="map-pin"></i></div>
        <p>No outreach sites registered yet.</p>
    </div>
</div>
@endforelse

@endsection
