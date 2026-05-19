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
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Consultation')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Clinical Consultation</h1>
        <p class="page-subtitle">
            Patient: <strong style="font-family:monospace;">{{ $visit->patient?->health_id ?? $visit->patient_id }}</strong>
            &nbsp;·&nbsp; Status: <span class="badge badge-primary">{{ ucwords(str_replace('_',' ',$visit->status)) }}</span>
        </p>
    </div>
    <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
        Back to Visits
    </a>
</div>

@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

<div class="grid-main-side" style="margin-top:0;">

    {{-- Consultation Form --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="file-pen" style="width:15px;height:15px;"></i>
                Clinical Note
            </h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('portals.staff.visits.consult.store', $visit->id) }}">
                @csrf
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label form-label-required">History of Present Illness *</label>
                    <textarea name="history_of_present_illness" class="form-control" rows="5"
                        required minlength="10" maxlength="5000"
                        placeholder="Describe the presenting complaint, onset, duration, character, associated symptoms…">{{ old('history_of_present_illness') }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Examination Findings</label>
                    <textarea name="examination_findings" class="form-control" rows="4"
                        maxlength="5000"
                        placeholder="Physical examination findings, system review…">{{ old('examination_findings') }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Treatment Plan / Assessment</label>
                    <textarea name="treatment_plan" class="form-control" rows="4"
                        maxlength="5000"
                        placeholder="Diagnosis, management plan, prescriptions, referrals, follow-up instructions…">{{ old('treatment_plan') }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Note Status</label>
                    <select name="status" class="form-control">
                        <option value="draft">Save as Draft</option>
                        <option value="signed">Sign & Finalize</option>
                    </select>
                    <span class="form-hint">Signed notes cannot be edited — only amended.</span>
                </div>
                <div style="display:flex;gap:.75rem;">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="file-pen" style="width:14px;height:14px;"></i>
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
        <div class="panel" style="margin-bottom:1.25rem;">
            <div class="panel-header">
                <h2 class="panel-title" style="font-size:.85rem;">
                    <i data-lucide="activity" style="width:13px;height:13px;"></i>
                    Triage Summary
                </h2>
            </div>
            <div class="panel-body">
                @php $triage = $visit->triageRecords->sortByDesc('created_at')->first(); @endphp
                <div style="font-size:var(--p-text-sm);line-height:1.7;">
                    <div><strong>Complaint:</strong> {{ $triage->presenting_complaint ?? '--' }}</div>
                    <div><strong>Acuity:</strong> {{ ucwords(str_replace('_',' ',$triage->acuity_score ?? '--')) }}</div>
                    <div><strong>Pain:</strong> {{ $triage->pain_score !== null ? $triage->pain_score . '/10' : '--' }}</div>
                    @if($triage->vitalSigns->isNotEmpty())
                        @php $v = $triage->vitalSigns->first(); @endphp
                        <div style="margin-top:.5rem;padding-top:.5rem;border-top:1px solid var(--p-border);">
                            <div><strong>T:</strong> {{ $v->temperature ?? '--' }}°C</div>
                            <div><strong>BP:</strong> {{ $v->blood_pressure_systolic ?? '--' }}/{{ $v->blood_pressure_diastolic ?? '--' }} mmHg</div>
                            <div><strong>Pulse:</strong> {{ $v->pulse ?? '--' }} bpm</div>
                            <div><strong>SpO₂:</strong> {{ $v->oxygen_saturation ?? '--' }}%</div>
                            @if($v->weight) <div><strong>Weight:</strong> {{ $v->weight }} kg</div> @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Previous Notes --}}
        @if($visit->clinicalNotes->isNotEmpty())
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title" style="font-size:.85rem;">
                    <i data-lucide="notebook-text" style="width:13px;height:13px;"></i>
                    Previous Notes ({{ $visit->clinicalNotes->count() }})
                </h2>
            </div>
            <div class="panel-body" style="padding:0;">
                @foreach($visit->clinicalNotes->sortByDesc('created_at') as $note)
                <div style="padding:.75rem 1rem;border-bottom:1px solid var(--p-border);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;">
                        <span style="font-size:var(--p-text-xs);color:var(--p-text-secondary);">
                            {{ \Carbon\Carbon::parse($note->created_at)->format('M d, Y H:i') }}
                        </span>
                        <span class="badge {{ $note->status === 'signed' ? 'badge-success' : 'badge-neutral' }}">
                            {{ ucwords($note->status) }}
                        </span>
                    </div>
                    <p style="font-size:var(--p-text-sm);margin:0;color:var(--p-text-secondary);">
                        {{ Str::limit($note->history_of_present_illness ?? '', 120) }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>

@endsection
