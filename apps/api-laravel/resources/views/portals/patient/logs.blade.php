@extends('layouts.portal')

@section('title', __('public.medical_id.access_logs', [], app()->getLocale()) . ' — OpesCare Patient Portal')

@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', __('public.medical_id.access_logs', [], app()->getLocale()) ?: 'Access Logs')

@section('sidebar_role_badge')
    <div class="sidebar-role-badge" style="background:rgba(15,118,110,.3);border-color:rgba(15,118,110,.5);color:#5EEAD4;">
        <i data-lucide="user" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        {{ __('public.portal.patient_role', [], app()->getLocale()) ?: 'Patient' }}
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">{{ __('public.portal.nav_my_health', [], app()->getLocale()) ?: 'My Health' }}</div>
    <a href="{{ route('portals.patient') }}" class="sidebar-link">
        <i data-lucide="id-card"></i>
        {{ __('public.medical_id.health_id', [], app()->getLocale()) ?: 'My Health ID' }}
    </a>
    <a href="{{ route('portals.patient.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        {{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}
    </a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">{{ __('public.portal.nav_privacy', [], app()->getLocale()) ?: 'Privacy & Access' }}</div>
    <a href="{{ route('portals.patient.logs') }}" class="sidebar-link active">
        <i data-lucide="history"></i>
        {{ __('public.medical_id.access_logs', [], app()->getLocale()) ?: 'Access Logs' }}
    </a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">{{ __('public.portal.nav_resources', [], app()->getLocale()) ?: 'Resources' }}</div>
    <a href="{{ route('public.care-map') }}" class="sidebar-link">
        <i data-lucide="map-pin"></i>
        {{ __('public.portal.nav_care_map', [], app()->getLocale()) ?: 'Care Map' }}
    </a>
    <a href="{{ route('public.help') }}" class="sidebar-link">
        <i data-lucide="help-circle"></i>
        {{ __('public.portal.nav_help', [], app()->getLocale()) ?: 'Help' }}
    </a>
@endsection

@section('sidebar_user_role')
    {{ __('public.portal.patient_role', [], app()->getLocale()) ?: 'Patient' }}
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.medical_id.access_logs', [], app()->getLocale()) ?: 'Access Logs' }}</h1>
        <p class="page-subtitle">{{ __('public.portal.access_logs_subtitle', [], app()->getLocale()) ?: 'Review who has accessed your Health ID and the status of those requests.' }}</p>
    </div>
</div>

<!-- Info Banner -->
<div class="alert alert-info mb-6" style="margin-bottom:var(--p-space-6);">
    <i data-lucide="shield-check"></i>
    <div style="font-size:0.8125rem;">
        {{ __('public.portal.access_logs_info', [], app()->getLocale()) ?: 'Every access to your Health ID is recorded here for your review. If you see an access you don\'t recognise, contact support immediately.' }}
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="history"></i>
            {{ __('public.portal.access_history', [], app()->getLocale()) ?: 'Access History' }}
        </h2>
        @if(count($logs) > 0)
        <span class="badge badge-primary">{{ count($logs) }}</span>
        @endif
    </div>

    @if(count($logs) === 0)
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="clipboard-list"></i></div>
            <h3>{{ __('public.portal.no_logs_title', [], app()->getLocale()) ?: 'No Access Logs' }}</h3>
            <p>{{ __('public.portal.no_logs_desc', [], app()->getLocale()) ?: 'Your Medical ID has not been accessed recently.' }}</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table" aria-label="Access logs">
                <thead>
                    <tr>
                        <th>{{ __('public.portal.date_time', [], app()->getLocale()) ?: 'Date & Time' }}</th>
                        <th>{{ __('public.portal.access_type', [], app()->getLocale()) ?: 'Access Type' }}</th>
                        <th>{{ __('public.portal.purpose', [], app()->getLocale()) ?: 'Purpose' }}</th>
                        <th>{{ __('public.portal.result', [], app()->getLocale()) ?: 'Result' }}</th>
                        <th>{{ __('public.portal.details', [], app()->getLocale()) ?: 'Details' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td data-label="{{ __('public.portal.date_time', [], app()->getLocale()) ?: 'Date & Time' }}">
                            <span class="td-strong">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</span>
                            <div class="td-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}</div>
                        </td>
                        <td data-label="{{ __('public.portal.access_type', [], app()->getLocale()) ?: 'Access Type' }}">
                            <div style="display:flex;align-items:center;gap:var(--p-space-2);">
                                @if(str_contains($log->access_type ?? '', 'qr'))
                                    <i data-lucide="qr-code" style="width:1rem;height:1rem;color:var(--p-primary);"></i>
                                    <span>{{ __('public.portal.qr_scan', [], app()->getLocale()) ?: 'QR Scan' }}</span>
                                @else
                                    <i data-lucide="search" style="width:1rem;height:1rem;color:var(--p-primary);"></i>
                                    <span>{{ __('public.portal.id_lookup', [], app()->getLocale()) ?: 'ID Lookup' }}</span>
                                @endif
                            </div>
                        </td>
                        <td data-label="{{ __('public.portal.purpose', [], app()->getLocale()) ?: 'Purpose' }}">
                            <span class="td-muted" style="text-transform:capitalize;">{{ str_replace('_', ' ', $log->purpose ?? '—') }}</span>
                        </td>
                        <td data-label="{{ __('public.portal.result', [], app()->getLocale()) ?: 'Result' }}">
                            @if(($log->result ?? '') === 'success')
                                <span class="badge badge-success">
                                    <span style="width:6px;height:6px;background:#22C55E;border-radius:50%;display:inline-block;margin-right:4px;"></span>
                                    {{ __('public.portal.granted', [], app()->getLocale()) ?: 'Granted' }}
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    <span style="width:6px;height:6px;background:#EF4444;border-radius:50%;display:inline-block;margin-right:4px;"></span>
                                    {{ __('public.portal.denied', [], app()->getLocale()) ?: 'Denied' }}
                                </span>
                            @endif
                        </td>
                        <td data-label="{{ __('public.portal.details', [], app()->getLocale()) ?: 'Details' }}">
                            <span class="td-muted" style="font-size:0.8rem;">{{ $log->ip_address ?? '—' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
