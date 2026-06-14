@extends('layouts.portal')

@section('title', 'Consultation — Visit')

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
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Consultation')

@section('content')

<div class="page-head">
    <h2>Clinical Consultation</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i>
        Back to Visits
    </a>
</div>
<p class="page-subtitle mb-4">
    Patient: <strong class="mono">{{ $visit->patient?->health_id ?? $visit->patient_id }}</strong>
    &nbsp;·&nbsp; Status: <span class="badge badge-primary">{{ ucwords(str_replace('_',' ',$visit->status)) }}</span>
</p>

@if(session('error'))
    <div class="alert alert-danger mb-4">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

<div class="grid-main-side">

    {{-- Consultation Form --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="file-pen"></i>
                Clinical Note
            </h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('portals.staff.visits.consult.store', $visit->id) }}">
                @csrf
                <div class="form-group mb-4">
                    <label class="form-label form-label-required">History of Present Illness *</label>
                    <textarea name="history_of_present_illness" class="form-control" rows="5"
                        required minlength="10" maxlength="5000"
                        placeholder="Describe the presenting complaint, onset, duration, character, associated symptoms…">{{ old('history_of_present_illness') }}</textarea>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Examination Findings</label>
                    <textarea name="examination_findings" class="form-control" rows="4"
                        maxlength="5000"
                        placeholder="Physical examination findings, system review…">{{ old('examination_findings') }}</textarea>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Treatment Plan / Assessment</label>
                    <textarea name="treatment_plan" class="form-control" rows="4"
                        maxlength="5000"
                        placeholder="Diagnosis, management plan, prescriptions, referrals, follow-up instructions…">{{ old('treatment_plan') }}</textarea>
                </div>
                <div class="form-group mb-6">
                    <label class="form-label">Note Status</label>
                    <select name="status" class="form-control">
                        <option value="draft">Save as Draft</option>
                        <option value="signed">Sign & Finalize</option>
                    </select>
                    <span class="form-hint">Signed notes cannot be edited — only amended.</span>
                </div>
                <div class="row-actions">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="file-pen"></i>
                        Save Note
                    </button>
                    <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar: Previous notes + triage summary --}}
    <div>
        {{-- Triage Summary --}}
        @if($visit->triageRecords->isNotEmpty())
        <div class="panel mb-6">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i data-lucide="activity"></i>
                    Triage Summary
                </h2>
            </div>
            <div class="panel-body panel-body--flush">
                @php $triage = $visit->triageRecords->sortByDesc('created_at')->first(); @endphp
                <table class="kv-table">
                    <tr><td class="kv-strong">Complaint</td><td>{{ $triage->presenting_complaint ?? '--' }}</td></tr>
                    <tr><td class="kv-strong">Acuity</td><td>{{ ucwords(str_replace('_',' ',$triage->acuity_score ?? '--')) }}</td></tr>
                    <tr><td class="kv-strong">Pain</td><td>{{ $triage->pain_score !== null ? $triage->pain_score . '/10' : '--' }}</td></tr>
                    @if($triage->vitalSigns->isNotEmpty())
                        @php $v = $triage->vitalSigns->first(); @endphp
                        <tr><td class="kv-strong">T</td><td>{{ $v->temperature ?? '--' }}°C</td></tr>
                        <tr><td class="kv-strong">BP</td><td>{{ $v->blood_pressure_systolic ?? '--' }}/{{ $v->blood_pressure_diastolic ?? '--' }} mmHg</td></tr>
                        <tr><td class="kv-strong">Pulse</td><td>{{ $v->pulse ?? '--' }} bpm</td></tr>
                        <tr><td class="kv-strong">SpO₂</td><td>{{ $v->oxygen_saturation ?? '--' }}%</td></tr>
                        @if($v->weight)<tr><td class="kv-strong">Weight</td><td>{{ $v->weight }} kg</td></tr>@endif
                    @endif
                </table>
            </div>
        </div>
        @endif

        {{-- Previous Notes --}}
        @if($visit->clinicalNotes->isNotEmpty())
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i data-lucide="notebook-text"></i>
                    Previous Notes ({{ $visit->clinicalNotes->count() }})
                </h2>
            </div>
            <div class="panel-body panel-body--flush">
                <div class="table-wrapper">
                    <table class="data-table">
                        <tbody>
                        @foreach($visit->clinicalNotes->sortByDesc('created_at') as $note)
                        <tr>
                            <td data-label="Date">{{ \Carbon\Carbon::parse($note->created_at)->format('M d, Y H:i') }}</td>
                            <td data-label="Status">
                                <span class="badge {{ $note->status === 'signed' ? 'badge-success' : 'badge-neutral' }}">{{ ucwords($note->status) }}</span>
                            </td>
                            <td data-label="Summary" class="td-muted">{{ Str::limit($note->history_of_present_illness ?? '', 120) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

@endsection
