@extends('layouts.portal')
@section('title', __('public.healthorg_portal.page_title', [], app()->getLocale()) ?: 'Health Programs')
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
@section('breadcrumb_section', __('public.healthorg_portal.breadcrumb_section_programs', [], app()->getLocale()) ?: 'Programs')
@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.healthorg_portal.page_heading_programs', [], $l) ?: 'Health Programs' }}</h1>
        <p class="page-subtitle">{{ __('public.healthorg_portal.page_subtitle_programs', [], $l) ?: 'Facilities and sites associated with your health programs.' }}</p>
    </div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('public.healthorg_portal.col_facility', [], $l) ?: 'Facility / Site' }}</th>
                    <th>{{ __('public.healthorg_portal.col_type', [], $l) ?: 'Type' }}</th>
                    <th>{{ __('public.healthorg_portal.col_status', [], $l) ?: 'Status' }}</th>
                    <th class="row-actions">{{ __('public.healthorg_portal.col_care_map', [], $l) ?: 'Care Map' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facilities as $facility)
                <tr>
                    <td data-label="{{ __('public.healthorg_portal.col_facility', [], $l) ?: 'Facility / Site' }}"><span class="td-strong">{{ $facility->name }}</span></td>
                    <td data-label="{{ __('public.healthorg_portal.col_type', [], $l) ?: 'Type' }}">
                        <span class="badge badge-neutral">{{ ucfirst(str_replace('_', ' ', $facility->type)) }}</span>
                    </td>
                    <td data-label="{{ __('public.healthorg_portal.col_status', [], $l) ?: 'Status' }}">
                        <span class="badge badge-{{ $facility->status === 'active' ? 'success' : 'neutral' }}">
                            {{ ucfirst($facility->status) }}
                        </span>
                    </td>
                    <td data-label="{{ __('public.healthorg_portal.col_care_map', [], $l) ?: 'Care Map' }}" class="row-actions">
                        <a href="{{ route('public.care-map.profile', $facility->id) }}" target="_blank" class="btn btn-secondary btn-sm">
                            <i data-lucide="external-link"></i> {{ __('public.healthorg_portal.btn_view', [], $l) ?: 'View' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="td-muted empty-cell">{{ __('public.healthorg_portal.no_facilities', [], $l) ?: 'No facilities found.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $facilities->links() }}</div>
</div>

@endsection
