@extends('layouts.portal')

@section('title', 'Patient Visits')

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
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</span>
    </a>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link active">
        <i data-lucide="stethoscope"></i>
        <span>{{ __('public.portal.nav_visits', [], app()->getLocale()) ?: 'Visits' }}</span>
    </a>
    <a href="{{ route('portals.staff.cdss') }}" class="sidebar-link {{ request()->routeIs('portals.staff.cdss*') ? 'active' : '' }}">
        <i data-lucide="brain-circuit"></i>
        <span>Clinical Alerts</span>
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
    <div class="sidebar-nav-label">Supply Chain</div>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i>
        <span>Supply Chain</span>
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
    <a href="{{ route('portals.staff.wards') }}" class="sidebar-link {{ request()->routeIs('portals.staff.wards*') ? 'active' : '' }}">
        <i data-lucide="bed"></i>
        <span>{{ __('public.portal.nav_wards', [], app()->getLocale()) ?: 'Wards & Beds' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Visits')

@section('content')

<div class="page-head">
    <h2>Patient Visits</h2>
    <div class="page-head__spacer"></div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openVisitModal()">
        <i data-lucide="plus-circle"></i>
        New Visit
    </button>
</div>
<p class="page-subtitle mb-4">Track active patient visits through the care journey.</p>

@if(session('success'))
    <div class="alert alert-success mb-4">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.visits') }}" class="filter-bar">
    <select name="status" class="filter-select">
        <option value="">Active Visits</option>
        @foreach(['open','in_triage','in_consultation','awaiting_lab','awaiting_pharmacy','awaiting_billing','awaiting_discharge','completed','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <input type="text" name="patient_id" class="filter-search" placeholder="Patient ID…" value="{{ request('patient_id') }}">
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter"></i> Filter
    </button>
    <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body panel-body--flush">
        @if(count($visits) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="stethoscope"></i></div>
                <h3>No Active Visits</h3>
                <p>All current visits are shown here. Start a new visit to begin tracking a patient's care journey.</p>
                <button type="button" class="btn btn-primary btn-sm mt-3" onclick="openVisitModal()">
                    New Visit
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Visit ID</th>
                            <th>Patient</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visits as $visit)
                        @php
                            $statusBadge = match($visit->status) {
                                'open'                => 'badge-neutral',
                                'in_triage'           => 'badge-warning',
                                'in_consultation'     => 'badge-primary',
                                'awaiting_lab'        => 'badge-teal',
                                'awaiting_pharmacy'   => 'badge-teal',
                                'awaiting_billing'    => 'badge-warning',
                                'awaiting_discharge'  => 'badge-primary',
                                'completed'           => 'badge-success',
                                'cancelled'           => 'badge-danger',
                                default               => 'badge-neutral',
                            };
                            $durationMin = \Carbon\Carbon::parse($visit->started_at)->diffInMinutes(now());
                        @endphp
                        <tr>
                            <td data-label="Visit ID">
                                <span class="mono">{{ substr($visit->id, 0, 8) }}…</span>
                            </td>
                            <td data-label="Patient">
                                <span class="mono">{{ $visit->patient?->health_id ?? $visit->patient_id }}</span>
                            </td>
                            <td data-label="Type">
                                <span class="badge badge-neutral">{{ ucwords($visit->visit_type ?? '--') }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_', ' ', $visit->status)) }}</span>
                            </td>
                            <td data-label="Started">
                                {{ \Carbon\Carbon::parse($visit->started_at)->format('M d, H:i') }}
                            </td>
                            <td data-label="Duration">
                                {{ $durationMin }} min
                            </td>
                            <td data-label="Actions">
                                <div class="row-actions">
                                    {{-- Triage --}}
                                    @if(in_array($visit->status, ['open','in_triage']))
                                        <a href="{{ route('portals.staff.visits.triage', $visit->id) }}"
                                            class="btn btn-warning btn-xs">
                                            <i data-lucide="activity"></i>
                                            Triage
                                        </a>
                                    @endif

                                    {{-- Consult --}}
                                    @if(in_array($visit->status, ['open','in_triage','in_consultation','awaiting_lab']))
                                        <a href="{{ route('portals.staff.visits.consult', $visit->id) }}"
                                            class="btn btn-primary btn-xs">
                                            <i data-lucide="stethoscope"></i>
                                            Consult
                                        </a>
                                    @endif

                                    {{-- Status advance --}}
                                    @if(!in_array($visit->status, ['completed','cancelled']))
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openTransitionModal('{{ $visit->id }}', '{{ $visit->status }}')">
                                            <i data-lucide="arrow-right-circle"></i>
                                            Advance
                                        </button>
                                    @endif

                                    {{-- Complete --}}
                                    @if(!in_array($visit->status, ['completed','cancelled']))
                                        <form method="POST" action="{{ route('portals.staff.visits.complete', $visit->id) }}" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs"
                                                onclick="return confirm('Mark this visit as completed?')">
                                                <i data-lucide="check-check"></i>
                                                Done
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('portals.staff.visits.cancel', $visit->id) }}" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs"
                                                onclick="return confirm('Cancel this visit?')">
                                                Cancel
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

{{-- New Visit Modal --}}
<div id="visit-modal" class="modal-backdrop" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="visit-modal-title">
        <h3 class="modal__title" id="visit-modal-title">Start New Visit</h3>
        <form method="POST" action="{{ route('portals.staff.visits.store') }}">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label">Patient *</label>
                    @if(count($patients) > 0)
                        <select name="patient_id" class="form-control" required>
                            <option value="">— Select Patient —</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->health_id ?? $p->id }} ({{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="patient_id" class="form-control" required placeholder="Patient ID">
                    @endif
                </div>
                <div class="form-group">
                    <label class="form-label">Visit Type *</label>
                    <select name="visit_type" class="form-control" required>
                        <option value="general">General Consultation</option>
                        <option value="followup">Follow-Up</option>
                        <option value="specialist">Specialist</option>
                        <option value="emergency">Emergency</option>
                        <option value="lab">Lab Only</option>
                        <option value="pharmacy">Pharmacy Only</option>
                    </select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeVisitModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="stethoscope"></i>
                    Start Visit
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Transition Modal --}}
<div id="transition-modal" class="modal-backdrop" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="transition-modal-title">
        <h3 class="modal__title" id="transition-modal-title">Advance Visit Status</h3>
        <form id="transition-form" method="POST" action="">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label">Move to Status *</label>
                    <select id="transition-status" name="status" class="form-control" required></select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeTransitionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="arrow-right-circle"></i>
                    Advance
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    var visitTransitions = {
        'open':               ['in_triage','in_consultation','awaiting_billing','awaiting_discharge'],
        'in_triage':          ['in_consultation','awaiting_billing','awaiting_discharge'],
        'in_consultation':    ['awaiting_lab','awaiting_billing','awaiting_pharmacy','awaiting_discharge'],
        'awaiting_lab':       ['in_consultation','awaiting_billing','awaiting_discharge'],
        'awaiting_pharmacy':  ['awaiting_billing','awaiting_discharge'],
        'awaiting_billing':   ['awaiting_discharge'],
        'awaiting_discharge': [],
    };

    function openVisitModal() { document.getElementById('visit-modal').removeAttribute('hidden'); }
    function closeVisitModal() { document.getElementById('visit-modal').setAttribute('hidden',''); }
    document.getElementById('visit-modal').addEventListener('click', function(e) {
        if (e.target === this) closeVisitModal();
    });

    function openTransitionModal(visitId, currentStatus) {
        var form = document.getElementById('transition-form');
        form.setAttribute('action', '{{ url("/portals/staff/visits") }}/' + visitId + '/transition');

        var select = document.getElementById('transition-status');
        select.innerHTML = '';

        var options = visitTransitions[currentStatus] || [];
        if (options.length === 0) {
            closeTransitionModal();
            return;
        }
        options.forEach(function(s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            select.appendChild(opt);
        });

        document.getElementById('transition-modal').removeAttribute('hidden');
    }
    function closeTransitionModal() { document.getElementById('transition-modal').setAttribute('hidden',''); }
    document.getElementById('transition-modal').addEventListener('click', function(e) {
        if (e.target === this) closeTransitionModal();
    });
</script>
@endsection
