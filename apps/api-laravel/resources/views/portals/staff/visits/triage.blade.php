@extends('layouts.portal')

@section('title', 'Triage — Visit')

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
@section('breadcrumb_section', 'Triage')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Triage Assessment</h1>
        <p class="page-subtitle">
            Patient: <strong style="font-family:monospace;">{{ $visit->patient?->health_id ?? $visit->patient_id }}</strong>
            &nbsp;·&nbsp; Visit ID: <span style="font-family:monospace;">{{ substr($visit->id, 0, 8) }}…</span>
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

{{-- Previous triage records --}}
@if($visit->triageRecords->isNotEmpty())
<div class="panel" style="margin-bottom:1.25rem;">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="clock" style="width:15px;height:15px;"></i>
            Previous Triage Records
        </h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Complaint</th>
                        <th>Acuity</th>
                        <th>Pain</th>
                        <th>Vitals</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visit->triageRecords->sortByDesc('created_at') as $triage)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($triage->created_at)->format('M d, H:i') }}</td>
                        <td>{{ Str::limit($triage->presenting_complaint ?? '--', 50) }}</td>
                        <td>
                            @php
                                $acuityBadge = match($triage->acuity_score) {
                                    'resuscitation','critical' => 'badge-danger',
                                    'urgent' => 'badge-warning',
                                    'semi_urgent' => 'badge-primary',
                                    default => 'badge-neutral',
                                };
                            @endphp
                            <span class="badge {{ $acuityBadge }}">{{ ucwords(str_replace('_',' ',$triage->acuity_score ?? '--')) }}</span>
                        </td>
                        <td>{{ $triage->pain_score !== null ? $triage->pain_score . '/10' : '--' }}</td>
                        <td style="font-size:var(--p-text-xs);">
                            @if($triage->vitalSigns->isNotEmpty())
                                @php $v = $triage->vitalSigns->first(); @endphp
                                T:{{ $v->temperature ?? '--' }}°C &nbsp;
                                BP:{{ $v->blood_pressure_systolic ?? '--' }}/{{ $v->blood_pressure_diastolic ?? '--' }} &nbsp;
                                P:{{ $v->pulse ?? '--' }} &nbsp;
                                SpO2:{{ $v->oxygen_saturation ?? '--' }}%
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- New Triage Form --}}
<div class="panel" style="max-width:700px;">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="activity" style="width:15px;height:15px;"></i>
            Record Triage
        </h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.visits.triage.store', $visit->id) }}">
            @csrf

            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label form-label-required">Presenting Complaint *</label>
                <textarea name="presenting_complaint" class="form-control" rows="3" required
                    maxlength="1000" placeholder="Chief complaint / reason for visit…">{{ old('presenting_complaint') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label form-label-required">Acuity Score *</label>
                    <select name="acuity_score" class="form-control" required>
                        <option value="resuscitation">Resuscitation (Level 1)</option>
                        <option value="critical">Critical (Level 2)</option>
                        <option value="urgent">Urgent (Level 3)</option>
                        <option value="semi_urgent" selected>Semi-Urgent (Level 4)</option>
                        <option value="non_urgent">Non-Urgent (Level 5)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pain Score (0–10)</label>
                    <input type="number" name="pain_score" class="form-control" min="0" max="10" value="{{ old('pain_score') }}" placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Pregnancy Status</label>
                    <select name="pregnancy_status" class="form-control">
                        <option value="">N/A</option>
                        <option value="not_applicable">Not Applicable</option>
                        <option value="not_pregnant">Not Pregnant</option>
                        <option value="pregnant">Pregnant</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
            </div>

            <h3 style="font-size:.9rem;font-weight:700;color:var(--p-text-secondary);margin:1.25rem 0 .75rem;">
                Vital Signs
            </h3>
            <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:.75rem;margin-bottom:1.25rem;">
                <div class="form-group">
                    <label class="form-label" style="font-size:.75rem;">Temperature (°C)</label>
                    <input type="number" name="temperature" class="form-control" step="0.1" min="20" max="45" placeholder="36.5">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:.75rem;">BP Systolic</label>
                    <input type="number" name="blood_pressure_systolic" class="form-control" min="40" max="300" placeholder="120">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:.75rem;">BP Diastolic</label>
                    <input type="number" name="blood_pressure_diastolic" class="form-control" min="20" max="200" placeholder="80">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:.75rem;">Pulse (bpm)</label>
                    <input type="number" name="pulse" class="form-control" min="20" max="300" placeholder="72">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:.75rem;">Resp. Rate (/min)</label>
                    <input type="number" name="respiratory_rate" class="form-control" min="4" max="60" placeholder="16">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:.75rem;">SpO₂ (%)</label>
                    <input type="number" name="oxygen_saturation" class="form-control" step="0.1" min="50" max="100" placeholder="98">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:.75rem;">Weight (kg)</label>
                    <input type="number" name="weight" class="form-control" step="0.1" min="0.5" max="500" placeholder="70">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-size:.75rem;">Height (cm)</label>
                    <input type="number" name="height" class="form-control" step="0.1" min="20" max="250" placeholder="170">
                </div>
            </div>

            <div style="display:flex;gap:.75rem;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="activity" style="width:14px;height:14px;"></i>
                    Save Triage
                </button>
                <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
