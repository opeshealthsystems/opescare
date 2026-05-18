@extends('layouts.portal')

@section('title', __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue')

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
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link active">
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
@section('breadcrumb_section', __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.queue_subtitle', [], app()->getLocale()) ?: 'Monitor live patient queues across your facility.' }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('portals.staff.queue-display') }}" target="_blank" class="btn btn-secondary btn-sm">
            <i data-lucide="monitor" style="width:13px;height:13px;"></i>
            Display Board
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('portals.staff.queue') }}" class="filter-bar">
    <input
        type="text"
        name="facility_id"
        class="form-control"
        placeholder="{{ __('public.staff_portal.filter_facility', [], app()->getLocale()) ?: 'Facility ID…' }}"
        value="{{ request('facility_id') }}"
    >
    <input
        type="text"
        name="queue_name"
        class="form-control"
        placeholder="{{ __('public.staff_portal.filter_queue_name', [], app()->getLocale()) ?: 'Queue name…' }}"
        value="{{ request('queue_name') }}"
    >
    <select name="status" class="form-control">
        <option value="">{{ __('public.staff_portal.filter_all_statuses', [], app()->getLocale()) ?: 'All Statuses' }}</option>
        <option value="active"   @selected(request('status') === 'active')>{{ __('public.staff_portal.status_active', [], app()->getLocale()) ?: 'Active' }}</option>
        <option value="waiting"  @selected(request('status') === 'waiting')>{{ __('public.staff_portal.status_waiting', [], app()->getLocale()) ?: 'Waiting' }}</option>
        <option value="served"   @selected(request('status') === 'served')>{{ __('public.staff_portal.status_served', [], app()->getLocale()) ?: 'Served' }}</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i>
        {{ __('public.staff_portal.filter_apply', [], app()->getLocale()) ?: 'Filter' }}
    </button>
    <a href="{{ route('portals.staff.queue') }}" class="btn btn-ghost btn-sm">
        {{ __('public.staff_portal.filter_clear', [], app()->getLocale()) ?: 'Clear' }}
    </a>
</form>

<div class="panel">
    <div class="panel-body" style="padding: 0;">
        @if(count($entries) === 0)
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="users"></i>
                </div>
                <h3>{{ __('public.staff_portal.no_queue_title', [], app()->getLocale()) ?: 'No Queue Entries' }}</h3>
                <p>{{ __('public.staff_portal.no_queue_desc', [], app()->getLocale()) ?: 'There are no patients in the queue matching your current filters.' }}</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.staff_portal.col_position', [], app()->getLocale()) ?: '#' }}</th>
                            <th>{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}</th>
                            <th>{{ __('public.staff_portal.col_queue_name', [], app()->getLocale()) ?: 'Queue Name' }}</th>
                            <th>{{ __('public.staff_portal.col_facility', [], app()->getLocale()) ?: 'Facility' }}</th>
                            <th>{{ __('public.staff_portal.col_wait_time', [], app()->getLocale()) ?: 'Wait Time' }}</th>
                            <th>{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $index => $entry)
                        @php
                            $statusBadge = match($entry->status ?? '') {
                                'active'  => 'badge-teal',
                                'waiting' => 'badge-warning',
                                'served'  => 'badge-success',
                                default   => 'badge-neutral',
                            };
                            $statusLabel = ucfirst($entry->status ?? 'unknown');
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.staff_portal.col_position', [], app()->getLocale()) ?: '#' }}">
                                <strong>{{ $entry->position ?? ($index + 1) }}</strong>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}">
                                <span style="font-family: monospace; font-size: var(--p-text-xs);">{{ $entry->patient_id ?? '--' }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_queue_name', [], app()->getLocale()) ?: 'Queue Name' }}">
                                {{ $entry->queue_name ?? '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_facility', [], app()->getLocale()) ?: 'Facility' }}">
                                {{ $entry->facility_name ?? $entry->facility_id ?? '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_wait_time', [], app()->getLocale()) ?: 'Wait Time' }}">
                                @if(!empty($entry->checked_in_at))
                                    @php
                                        $waitMins = \Carbon\Carbon::parse($entry->checked_in_at)->diffInMinutes(now());
                                    @endphp
                                    {{ $waitMins }} {{ __('public.staff_portal.minutes_abbr', [], app()->getLocale()) ?: 'min' }}
                                @else
                                    --
                                @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}">
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
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
