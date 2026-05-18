@extends('layouts.portal')

@section('title', __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">Clinical Staff</div>
@endsection

@section('sidebar_user_role', 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link active">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</span>
    </a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link">
        <i data-lucide="syringe"></i>
        <span>{{ __('public.portal.nav_immunizations', [], app()->getLocale()) ?: 'Immunizations' }}</span>
    </a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link">
        <i data-lucide="send"></i>
        <span>{{ __('public.portal.nav_referrals', [], app()->getLocale()) ?: 'Referrals' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.appointments_subtitle', [], app()->getLocale()) ?: 'View and manage scheduled patient appointments.' }}</p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('portals.staff.appointments') }}" class="filter-bar">
    <div class="form-search">
        <span class="search-icon">
            <i data-lucide="search" style="width:13px;height:13px;"></i>
        </span>
        <input
            type="text"
            name="patient_id"
            class="form-control"
            placeholder="{{ __('public.staff_portal.filter_patient_id', [], app()->getLocale()) ?: 'Patient ID…' }}"
            value="{{ request('patient_id') }}"
            style="padding-left: 2.1rem;"
        >
    </div>
    <input
        type="text"
        name="facility_id"
        class="form-control"
        placeholder="{{ __('public.staff_portal.filter_facility', [], app()->getLocale()) ?: 'Facility ID…' }}"
        value="{{ request('facility_id') }}"
    >
    <input
        type="text"
        name="provider_id"
        class="form-control"
        placeholder="{{ __('public.staff_portal.filter_provider', [], app()->getLocale()) ?: 'Provider ID…' }}"
        value="{{ request('provider_id') }}"
    >
    <input
        type="date"
        name="date"
        class="form-control"
        value="{{ request('date') }}"
    >
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i>
        {{ __('public.staff_portal.filter_apply', [], app()->getLocale()) ?: 'Filter' }}
    </button>
    <a href="{{ route('portals.staff.appointments') }}" class="btn btn-ghost btn-sm">
        {{ __('public.staff_portal.filter_clear', [], app()->getLocale()) ?: 'Clear' }}
    </a>
</form>

<div class="panel">
    <div class="panel-body" style="padding: 0;">
        @if(count($appointments) === 0)
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="calendar-x-2"></i>
                </div>
                <h3>{{ __('public.staff_portal.no_appointments_title', [], app()->getLocale()) ?: 'No Appointments Found' }}</h3>
                <p>{{ __('public.staff_portal.no_appointments_desc', [], app()->getLocale()) ?: 'There are no appointments matching your current filters.' }}</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.staff_portal.col_datetime', [], app()->getLocale()) ?: 'Date / Time' }}</th>
                            <th>{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}</th>
                            <th>{{ __('public.staff_portal.col_provider', [], app()->getLocale()) ?: 'Provider' }}</th>
                            <th>{{ __('public.staff_portal.col_type', [], app()->getLocale()) ?: 'Type' }}</th>
                            <th>{{ __('public.staff_portal.col_facility', [], app()->getLocale()) ?: 'Facility' }}</th>
                            <th>{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}</th>
                            <th>{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appointment)
                        @php
                            $statusBadge = match($appointment->status ?? '') {
                                'completed'  => 'badge-success',
                                'cancelled'  => 'badge-danger',
                                'no_show'    => 'badge-warning',
                                'checked_in' => 'badge-teal',
                                default      => 'badge-neutral',
                            };
                            $statusLabel = ucwords(str_replace('_', ' ', $appointment->status ?? 'Unknown'));
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.staff_portal.col_datetime', [], app()->getLocale()) ?: 'Date / Time' }}">
                                {{ \Carbon\Carbon::parse($appointment->scheduled_at ?? $appointment->created_at)->format('M d, Y H:i') }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}">
                                <span style="font-family: monospace; font-size: var(--p-text-xs);">{{ $appointment->patient_id ?? '--' }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_provider', [], app()->getLocale()) ?: 'Provider' }}">
                                {{ $appointment->provider_name ?? $appointment->provider_id ?? '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_type', [], app()->getLocale()) ?: 'Type' }}">
                                <span class="badge badge-primary">{{ ucwords(str_replace('_', ' ', $appointment->appointment_type ?? 'General')) }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_facility', [], app()->getLocale()) ?: 'Facility' }}">
                                {{ $appointment->facility_name ?? $appointment->facility_id ?? '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}">
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}">
                                <a href="{{ route('portals.staff.appointments') }}?view={{ $appointment->id ?? $appointment->uuid ?? '' }}" class="btn btn-ghost btn-sm">
                                    <i data-lucide="eye" style="width:13px;height:13px;"></i>
                                    {{ __('public.staff_portal.action_view', [], app()->getLocale()) ?: 'View' }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
