@extends('layouts.portal')
@section('title', __('public.healthorg_portal.page_title', [], app()->getLocale()) ?: 'Outreach Sites')
@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="heart-handshake"></i>
    {{ __('public.healthorg_portal.role_badge', [], app()->getLocale()) ?: 'Health Org' }}
</div>
@endsection
@section('sidebar_user_role', __('public.healthorg_portal.role_label', [], app()->getLocale()) ?: 'Health Org Admin')
@section('sidebar_nav')
@include('portals.healthorg._sidebar')
@endsection
@section('breadcrumb_home', __('public.healthorg_portal.breadcrumb_home', [], app()->getLocale()) ?: 'Health Org Portal')
@section('breadcrumb_home_url', route('portals.healthorg.dashboard'))
@section('breadcrumb_section', __('public.healthorg_portal.breadcrumb_section_outreach', [], app()->getLocale()) ?: 'Outreach Sites')
@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.healthorg_portal.page_heading_outreach', [], $l) ?: 'Outreach & Mobile Clinic Sites' }}</h1>
        <p class="page-subtitle">{{ __('public.healthorg_portal.page_subtitle_outreach', [], $l) ?: 'Active outreach locations visible on the Care Map.' }}</p>
    </div>
    <a href="{{ route('public.care-map') }}" target="_blank" class="btn btn-secondary btn-sm">
        <i data-lucide="external-link"></i> {{ __('public.healthorg_portal.btn_open_care_map', [], $l) ?: 'Open Care Map' }}
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
                <i data-lucide="map-pin"></i> {{ __('public.healthorg_portal.btn_profile', [], $l) ?: 'Profile' }}
            </a>
        </div>
    </div>
</div>
@empty
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="map-pin"></i></div>
        <p>{{ __('public.healthorg_portal.no_outreach_sites', [], $l) ?: 'No outreach sites registered yet.' }}</p>
    </div>
</div>
@endforelse

@endsection
