@extends('layouts.portal')

@section('title', __('public.staff_portal.queue_title', [], app()->getLocale()) ?: 'Patient Queue')

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
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', __('public.staff_portal.queue_title', [], app()->getLocale()) ?: 'Patient Queue')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.staff_portal.queue_title', [], app()->getLocale()) ?: 'Patient Queue' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.queue_subtitle', [], app()->getLocale()) ?: 'Monitor live patient queues across your facility.' }}</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openCheckInModal()">
            <i data-lucide="user-plus" style="width:14px;height:14px;"></i>
            {{ __('public.staff_portal.btn_walk_in', [], app()->getLocale()) ?: 'Walk-In Check-In' }}
        </button>
        <a href="{{ route('portals.staff.queue-display') }}" target="_blank" class="btn btn-ghost btn-sm">
            <i data-lucide="monitor" style="width:14px;height:14px;"></i>
            Display Board
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

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.queue') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <input type="text" name="queue_name" class="form-control"
        placeholder="{{ __('public.staff_portal.filter_queue_name', [], app()->getLocale()) ?: 'Queue name…' }}"
        value="{{ request('queue_name') }}">
    <input type="text" name="facility_id" class="form-control"
        placeholder="{{ __('public.staff_portal.filter_facility', [], app()->getLocale()) ?: 'Facility ID…' }}"
        value="{{ request('facility_id') }}">
    <select name="status" class="form-control">
        <option value="">{{ __('public.staff_portal.filter_all_statuses', [], app()->getLocale()) ?: 'All Statuses' }}</option>
        @foreach(['waiting','called','service_started','completed','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
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
    <div class="panel-body" style="padding:0;">
        @if(count($entries) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="list-ordered"></i></div>
                <h3>{{ __('public.staff_portal.no_queue_title', [], app()->getLocale()) ?: 'No Queue Entries' }}</h3>
                <p>{{ __('public.staff_portal.no_queue_desc', [], app()->getLocale()) ?: 'There are no patients in the queue.' }}</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openCheckInModal()">
                    {{ __('public.staff_portal.btn_walk_in', [], app()->getLocale()) ?: 'Walk-In Check-In' }}
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.staff_portal.col_ticket_no', [], app()->getLocale()) ?: 'Ticket #' }}</th>
                            <th>{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}</th>
                            <th>{{ __('public.staff_portal.col_queue_name', [], app()->getLocale()) ?: 'Queue' }}</th>
                            <th>{{ __('public.staff_portal.col_priority', [], app()->getLocale()) ?: 'Priority' }}</th>
                            <th>{{ __('public.staff_portal.col_wait_time', [], app()->getLocale()) ?: 'Wait Time' }}</th>
                            <th>{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}</th>
                            <th>{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $ticket)
                        @php
                            $statusBadge = match($ticket->status ?? '') {
                                'called'          => 'badge-primary',
                                'service_started' => 'badge-teal',
                                'completed'       => 'badge-success',
                                'cancelled'       => 'badge-danger',
                                default           => 'badge-neutral',
                            };
                            $waitMin = $ticket->checked_in_at
                                ? (int) \Carbon\Carbon::parse($ticket->checked_in_at)->diffInMinutes(now())
                                : 0;
                            $pLevel = $ticket->priority_level ?? 5;
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.staff_portal.col_ticket_no', [], app()->getLocale()) ?: 'Ticket #' }}">
                                <strong>{{ $ticket->queue_number ?? '--' }}</strong>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}">
                                <span style="font-family:monospace;font-size:var(--p-text-xs);">{{ $ticket->patient_id ?? '--' }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_queue_name', [], app()->getLocale()) ?: 'Queue' }}">
                                {{ $ticket->current_queue ?? '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_priority', [], app()->getLocale()) ?: 'Priority' }}">
                                <span class="badge {{ $pLevel <= 2 ? 'badge-danger' : ($pLevel <= 3 ? 'badge-warning' : 'badge-neutral') }}">
                                    {{ $pLevel <= 2 ? (__('public.staff_portal.priority_critical', [], app()->getLocale()) ?: 'Critical') :
                                       ($pLevel <= 3 ? (__('public.staff_portal.priority_high', [], app()->getLocale()) ?: 'High') :
                                       (__('public.staff_portal.priority_normal', [], app()->getLocale()) ?: 'Normal')) }}
                                </span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_wait_time', [], app()->getLocale()) ?: 'Wait Time' }}">
                                {{ $waitMin }} {{ __('public.staff_portal.minutes_abbr', [], app()->getLocale()) ?: 'min' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}">
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_', ' ', $ticket->status ?? '')) }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    @if($ticket->status === 'waiting')
                                        <form method="POST" action="{{ route('portals.staff.queue.call', $ticket->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-xs">
                                                <i data-lucide="megaphone" style="width:11px;height:11px;"></i>
                                                {{ __('public.staff_portal.btn_call', [], app()->getLocale()) ?: 'Call' }}
                                            </button>
                                        </form>
                                    @endif
                                    @if($ticket->status === 'called')
                                        <form method="POST" action="{{ route('portals.staff.queue.start', $ticket->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-teal btn-xs">
                                                <i data-lucide="play" style="width:11px;height:11px;"></i>
                                                {{ __('public.staff_portal.btn_start_service', [], app()->getLocale()) ?: 'Start' }}
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($ticket->status ?? '', ['called','service_started']))
                                        <form method="POST" action="{{ route('portals.staff.queue.complete', $ticket->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs"
                                                onclick="return confirm('Complete this queue ticket?')">
                                                <i data-lucide="check-check" style="width:11px;height:11px;"></i>
                                                {{ __('public.staff_portal.btn_complete', [], app()->getLocale()) ?: 'Complete' }}
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

{{-- Walk-In Check-In Modal --}}
<div id="checkin-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">
            {{ __('public.staff_portal.btn_walk_in', [], app()->getLocale()) ?: 'Walk-In Check-In' }}
        </h3>
        <form method="POST" action="{{ route('portals.staff.queue.check-in') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }} *</label>
                <input type="text" name="patient_id" class="form-control" required placeholder="OC-NGA-XXXX-XXXX-XXXX">
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Queue Name *</label>
                <input type="text" name="destination_queue" class="form-control" required
                    placeholder="{{ __('public.staff_portal.queue_name_placeholder', [], app()->getLocale()) ?: 'e.g. reception, triage, lab' }}">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeCheckInModal()">Back</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="user-plus" style="width:13px;height:13px;"></i>
                    {{ __('public.staff_portal.btn_check_in', [], app()->getLocale()) ?: 'Check In' }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openCheckInModal() { document.getElementById('checkin-modal').style.display = 'flex'; }
    function closeCheckInModal() { document.getElementById('checkin-modal').style.display = 'none'; }
    document.getElementById('checkin-modal').addEventListener('click', function(e) {
        if (e.target === this) closeCheckInModal();
    });
</script>
@endsection
