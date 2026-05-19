@extends('layouts.portal')

@section('title', __('public.staff_portal.appointments_title', [], app()->getLocale()) ?: 'Appointments')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection

@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}</span>
    </a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link">
        <i data-lucide="bar-chart-2"></i>
        <span>{{ __('public.portal.nav_analytics', [], app()->getLocale()) ?: 'Analytics' }}</span>
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
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="stethoscope"></i>
        <span>{{ __('public.portal.nav_visits', [], app()->getLocale()) ?: 'Visits' }}</span>
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
    <div class="sidebar-nav-label">HR & Staff</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_staff_directory', [], app()->getLocale()) ?: 'Directory' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
        <i data-lucide="clock"></i>
        <span>{{ __('public.portal.nav_staff_shifts', [], app()->getLocale()) ?: 'Shifts' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i>
        <span>{{ __('public.portal.nav_staff_roster', [], app()->getLocale()) ?: 'Duty Roster' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link">
        <i data-lucide="plane-takeoff"></i>
        <span>{{ __('public.portal.nav_staff_leave', [], app()->getLocale()) ?: 'Leave' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Inventory</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="pill"></i>
        <span>{{ __('public.portal.nav_inventory_pharmacy', [], app()->getLocale()) ?: 'Pharmacy' }}</span>
    </a>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link">
        <i data-lucide="droplets"></i>
        <span>{{ __('public.portal.nav_inventory_blood', [], app()->getLocale()) ?: 'Blood Bank' }}</span>
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
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i>
        <span>{{ __('public.portal.nav_insurance', [], app()->getLocale()) ?: 'Insurance' }}</span>
    </a>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link">
        <i data-lucide="upload-cloud"></i>
        <span>{{ __('public.portal.nav_data_import', [], app()->getLocale()) ?: 'Data Import' }}</span>
    </a>
    <a href="{{ route('portals.staff.search') }}" class="sidebar-link {{ request()->routeIs('portals.staff.search') ? 'active' : '' }}">
        <i data-lucide="search"></i>
        <span>{{ __('public.portal.nav_search', [], app()->getLocale()) ?: 'Global Search' }}</span>
    </a>
    <a href="{{ route('portals.staff.files.index') }}" class="sidebar-link {{ request()->routeIs('portals.staff.files*') ? 'active' : '' }}">
        <i data-lucide="paperclip"></i>
        <span>{{ __('public.portal.nav_files', [], app()->getLocale()) ?: 'Files & Attachments' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', __('public.staff_portal.appointments_title', [], app()->getLocale()) ?: 'Appointments')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.staff_portal.appointments_title', [], app()->getLocale()) ?: 'Appointments' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.appointments_subtitle', [], app()->getLocale()) ?: 'View and manage scheduled patient appointments.' }}</p>
    </div>
    <div>
        <a href="{{ route('portals.staff.appointments.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="calendar-plus" style="width:14px;height:14px;"></i>
            {{ __('public.staff_portal.btn_book_appointment', [], app()->getLocale()) ?: 'Book Appointment' }}
        </a>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('portals.staff.appointments') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <div class="form-search">
        <span class="search-icon"><i data-lucide="search" style="width:13px;height:13px;"></i></span>
        <input type="text" name="patient_id" class="form-control"
            placeholder="{{ __('public.staff_portal.filter_patient_id', [], app()->getLocale()) ?: 'Patient ID…' }}"
            value="{{ request('patient_id') }}" style="padding-left:2.1rem;">
    </div>
    <input type="text" name="facility_id" class="form-control"
        placeholder="{{ __('public.staff_portal.filter_facility', [], app()->getLocale()) ?: 'Facility ID…' }}"
        value="{{ request('facility_id') }}">
    <input type="text" name="provider_id" class="form-control"
        placeholder="{{ __('public.staff_portal.filter_provider', [], app()->getLocale()) ?: 'Provider…' }}"
        value="{{ request('provider_id') }}">
    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
    <select name="status" class="form-control">
        <option value="">{{ __('public.staff_portal.filter_all_statuses', [], app()->getLocale()) ?: 'All Statuses' }}</option>
        @foreach(['scheduled','confirmed','checked_in','in_progress','completed','cancelled','no_show'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i>
        {{ __('public.staff_portal.filter_apply', [], app()->getLocale()) ?: 'Filter' }}
    </button>
    <a href="{{ route('portals.staff.appointments') }}" class="btn btn-ghost btn-sm">
        {{ __('public.staff_portal.filter_clear', [], app()->getLocale()) ?: 'Clear' }}
    </a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if(count($appointments) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="calendar-x-2"></i></div>
                <h3>{{ __('public.staff_portal.no_appointments_title', [], app()->getLocale()) ?: 'No Appointments Found' }}</h3>
                <p>{{ __('public.staff_portal.no_appointments_desc', [], app()->getLocale()) ?: 'There are no appointments matching your current filters.' }}</p>
                <a href="{{ route('portals.staff.appointments.create') }}" class="btn btn-primary btn-sm" style="margin-top:1rem;">
                    {{ __('public.staff_portal.btn_book_appointment', [], app()->getLocale()) ?: 'Book Appointment' }}
                </a>
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
                        @foreach($appointments as $appt)
                        @php
                            $statusBadge = match($appt->status ?? '') {
                                'completed'  => 'badge-success',
                                'cancelled'  => 'badge-danger',
                                'no_show'    => 'badge-warning',
                                'checked_in' => 'badge-teal',
                                'confirmed'  => 'badge-primary',
                                default      => 'badge-neutral',
                            };
                            $statusLabel = ucwords(str_replace('_', ' ', $appt->status ?? 'Unknown'));
                            $isActive = in_array($appt->status ?? '', ['scheduled','confirmed']);
                            $canCheckIn = in_array($appt->status ?? '', ['scheduled','confirmed']);
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.staff_portal.col_datetime', [], app()->getLocale()) ?: 'Date / Time' }}">
                                {{ \Carbon\Carbon::parse($appt->scheduled_at ?? $appt->created_at)->format('M d, Y H:i') }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}">
                                <span style="font-family:monospace;font-size:var(--p-text-xs);">{{ $appt->patient_id ?? '--' }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_provider', [], app()->getLocale()) ?: 'Provider' }}">
                                {{ $appt->provider_id ?? '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_type', [], app()->getLocale()) ?: 'Type' }}">
                                <span class="badge badge-primary">{{ ucwords(str_replace('_', ' ', $appt->appointment_type ?? 'General')) }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_facility', [], app()->getLocale()) ?: 'Facility' }}">
                                <span style="font-size:var(--p-text-xs);color:var(--p-text-muted);">{{ $appt->facility_id ?? '--' }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}">
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    @if($appt->status === 'scheduled')
                                        <form method="POST" action="{{ route('portals.staff.appointments.confirm', $appt->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-teal btn-xs">
                                                <i data-lucide="check" style="width:11px;height:11px;"></i>
                                                {{ __('public.staff_portal.btn_confirm', [], app()->getLocale()) ?: 'Confirm' }}
                                            </button>
                                        </form>
                                    @endif
                                    @if($canCheckIn)
                                        <form method="POST" action="{{ route('portals.staff.appointments.check-in', $appt->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-xs">
                                                <i data-lucide="log-in" style="width:11px;height:11px;"></i>
                                                {{ __('public.staff_portal.btn_check_in', [], app()->getLocale()) ?: 'Check In' }}
                                            </button>
                                        </form>
                                    @endif
                                    @if($isActive)
                                        <button type="button" class="btn btn-danger btn-xs"
                                            onclick="openCancelModal('{{ $appt->id }}')">
                                            <i data-lucide="x" style="width:11px;height:11px;"></i>
                                            {{ __('public.staff_portal.btn_cancel', [], app()->getLocale()) ?: 'Cancel' }}
                                        </button>
                                        <form method="POST" action="{{ route('portals.staff.appointments.no-show', $appt->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs"
                                                onclick="return confirm('Mark as no-show?')">
                                                <i data-lucide="user-x" style="width:11px;height:11px;"></i>
                                                {{ __('public.staff_portal.btn_no_show', [], app()->getLocale()) ?: 'No Show' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Cancel Modal --}}
<div id="cancel-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="margin:0 0 1rem;font-size:1.1rem;">
            {{ __('public.staff_portal.btn_cancel', [], app()->getLocale()) ?: 'Cancel Appointment' }}
        </h3>
        <form id="cancel-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">
                    {{ __('public.staff_portal.cancel_reason_label', [], app()->getLocale()) ?: 'Cancellation reason' }}
                </label>
                <textarea name="reason" class="form-control" rows="3" required
                    placeholder="{{ __('public.staff_portal.cancel_reason_placeholder', [], app()->getLocale()) ?: 'Enter reason…' }}"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeCancelModal()">
                    {{ __('public.portal.btn_cancel', [], app()->getLocale()) ?: 'Back' }}
                </button>
                <button type="submit" class="btn btn-danger btn-sm">
                    {{ __('public.staff_portal.btn_cancel', [], app()->getLocale()) ?: 'Confirm Cancellation' }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openCancelModal(appointmentId) {
        var modal = document.getElementById('cancel-modal');
        var form  = document.getElementById('cancel-form');
        var base  = '{{ url('/portals/staff/appointments') }}';
        form.setAttribute('action', base + '/' + appointmentId + '/cancel');
        modal.style.display = 'flex';
    }
    function closeCancelModal() {
        document.getElementById('cancel-modal').style.display = 'none';
    }
    document.getElementById('cancel-modal').addEventListener('click', function(e) {
        if (e.target === this) closeCancelModal();
    });
</script>
@endsection
