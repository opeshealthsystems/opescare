@extends('layouts.portal')

@section('title', __('public.portal.nav_appointments', [], app()->getLocale()) . ' — OpesCare Patient Portal')

@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments')

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
    <a href="{{ route('portals.patient.appointments') }}" class="sidebar-link active">
        <i data-lucide="calendar-check-2"></i>
        {{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}
    </a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">{{ __('public.portal.nav_privacy', [], app()->getLocale()) ?: 'Privacy & Access' }}</div>
    <a href="{{ route('portals.patient.logs') }}" class="sidebar-link">
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
        <h1 class="page-title">{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'My Appointments' }}</h1>
        <p class="page-subtitle">{{ __('public.portal.appointments_subtitle', [], app()->getLocale()) ?: 'View your upcoming and past appointments.' }}</p>
    </div>
</div>

@if(count($appointments) === 0)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="calendar-check-2"></i></div>
        <h3>{{ __('public.portal.no_appointments_title', [], app()->getLocale()) ?: 'No Appointments' }}</h3>
        <p>{{ __('public.portal.no_appointments_desc', [], app()->getLocale()) ?: 'You don\'t have any appointments scheduled at this time.' }}</p>
        <a href="{{ route('public.care-map') }}" class="btn btn-primary">
            <i data-lucide="map-pin"></i>
            {{ __('public.portal.nav_care_map', [], app()->getLocale()) ?: 'Find a Provider' }}
        </a>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="calendar-check-2"></i> {{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</h2>
        <span class="badge badge-primary">{{ count($appointments) }}</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="Appointments list">
            <thead>
                <tr>
                    <th>{{ __('public.portal.date_time', [], app()->getLocale()) ?: 'Date & Time' }}</th>
                    <th>{{ __('public.portal.provider', [], app()->getLocale()) ?: 'Provider' }}</th>
                    <th>{{ __('public.portal.appointment_type', [], app()->getLocale()) ?: 'Type' }}</th>
                    <th>{{ __('public.portal.facility', [], app()->getLocale()) ?: 'Facility' }}</th>
                    <th>{{ __('public.portal.status', [], app()->getLocale()) ?: 'Status' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $appt)
                <tr>
                    <td data-label="{{ __('public.portal.date_time', [], app()->getLocale()) ?: 'Date & Time' }}">
                        <span class="td-strong">
                            {{ $appt->appointment_date ? \Carbon\Carbon::parse($appt->appointment_date)->format('d M Y') : '—' }}
                        </span>
                        @if(!empty($appt->appointment_time))
                        <div class="td-muted">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}</div>
                        @endif
                    </td>
                    <td data-label="{{ __('public.portal.provider', [], app()->getLocale()) ?: 'Provider' }}">
                        <span class="td-strong">{{ $appt->provider_name ?? '—' }}</span>
                    </td>
                    <td data-label="{{ __('public.portal.appointment_type', [], app()->getLocale()) ?: 'Type' }}">
                        <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $appt->appointment_type ?? 'General')) }}</span>
                    </td>
                    <td data-label="{{ __('public.portal.facility', [], app()->getLocale()) ?: 'Facility' }}">
                        <span class="td-muted">{{ $appt->facility_name ?? $appt->facility_id ?? '—' }}</span>
                    </td>
                    <td data-label="{{ __('public.portal.status', [], app()->getLocale()) ?: 'Status' }}">
                        @php
                            $stCls = match($appt->status ?? 'scheduled') {
                                'completed'  => 'badge-success',
                                'cancelled'  => 'badge-danger',
                                'no_show'    => 'badge-warning',
                                'checked_in' => 'badge-teal',
                                default      => 'badge-primary',
                            };
                        @endphp
                        <span class="badge {{ $stCls }}">{{ ucfirst($appt->status ?? 'Scheduled') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
