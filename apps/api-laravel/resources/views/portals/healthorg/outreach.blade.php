@extends('layouts.portal')

@section('title', 'Outreach Sites')

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
@section('breadcrumb_section', 'Outreach Sites')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Outreach & Mobile Clinic Sites</h1>
        <p class="page-subtitle">Active outreach locations visible on the Care Map.</p>
    </div>
    <a href="{{ route('public.care-map') }}" target="_blank" class="btn btn-outline btn-sm">
        <i data-lucide="external-link" style="width:14px;height:14px;"></i>
        Open Care Map
    </a>
</div>

@forelse($sites as $site)
<div class="card" style="margin-bottom:.75rem;">
    <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;">
        <div>
            <div style="font-weight:700;font-size:.95rem;">{{ $site->name }}</div>
            <div style="font-size:.8rem;color:#64748b;margin-top:2px;">
                {{ ucfirst(str_replace('_', ' ', $site->type)) }}
                &middot;
                <span class="badge badge-{{ $site->status === 'active' ? 'success' : 'default' }}" style="font-size:.7rem;">{{ ucfirst($site->status) }}</span>
            </div>
        </div>
        <a href="{{ route('public.care-map.profile', $site->id) }}" target="_blank" class="btn btn-outline btn-sm">
            <i data-lucide="map-pin" style="width:13px;height:13px;"></i>
            Profile
        </a>
    </div>
</div>
@empty
<div style="text-align:center;padding:3rem;color:#94a3b8;">
    <i data-lucide="map-pin" style="width:40px;height:40px;display:block;margin:0 auto 12px;opacity:.4;"></i>
    No outreach sites registered yet.
</div>
@endforelse

@endsection
