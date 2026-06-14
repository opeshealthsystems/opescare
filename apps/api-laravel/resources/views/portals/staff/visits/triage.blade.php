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
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link">
        <i data-lucide="upload"></i>
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
@section('breadcrumb_section', 'Triage')

@section('content')

@php
    use App\Modules\Triage\Services\TriageService;
    $lastTriage = $visit->triageRecords->sortByDesc('created_at')->first();
    $isCritical = $lastTriage && in_array($lastTriage->acuity_score, ['critical', 'resuscitation']);
    $isEmergency = $visit->status === 'emergency';

    // Assess vitals on last triage
    $vitalAlerts = [];
    if ($lastTriage && $lastTriage->vitalSigns->isNotEmpty()) {
        $v = $lastTriage->vitalSigns->first();
        $vitalAlerts = TriageService::assessVitals([
            'temperature'          => $v->temperature,
            'blood_pressure_systolic' => $v->blood_pressure_systolic,
            'pulse'                => $v->pulse,
            'respiratory_rate'     => $v->respiratory_rate,
            'oxygen_saturation'    => $v->oxygen_saturation,
        ]);
    }
@endphp

<div class="page-head">
    <h2>
        @if($isEmergency)<i data-lucide="siren"></i> @endif
        Triage Assessment
    </h2>
    <div class="page-head__spacer"></div>
    @if(!$isEmergency)
        <button type="button" class="btn btn-danger btn-sm" onclick="openEscalateModal()">
            <i data-lucide="siren"></i> Declare Emergency
        </button>
    @endif
    <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> Back
    </a>
</div>

<p class="page-subtitle mb-4">
    Patient: <strong class="mono">{{ $visit->patient?->health_id ?? $visit->patient_id }}</strong>
    &nbsp;·&nbsp; Visit ID: <span class="mono">{{ substr($visit->id, 0, 8) }}…</span>
    &nbsp;·&nbsp;
    @php
        $statusBadge = match($visit->status) {
            'emergency' => 'badge-danger',
            'in_triage' => 'badge-warning',
            'completed' => 'badge-success',
            default     => 'badge-neutral',
        };
    @endphp
    <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_', ' ', $visit->status)) }}</span>
</p>

{{-- Emergency Banner --}}
@if($isEmergency)
<div class="alert alert-danger mb-4">
    <i data-lucide="siren"></i>
    <div>
        <strong>EMERGENCY — Resuscitation Level</strong>
        <div>This visit has been declared an emergency. Acuity: Resuscitation (Level 1).</div>
    </div>
</div>
@elseif($isCritical)
<div class="alert alert-danger mb-4">
    <i data-lucide="alert-triangle"></i>
    <div>
        <strong>Critical acuity detected.</strong>
        <span>Last triage: {{ ucwords(str_replace('_',' ',$lastTriage->acuity_score)) }}</span>
    </div>
    <button type="button" class="btn btn-danger btn-sm" onclick="openEscalateModal()">Escalate to Emergency</button>
</div>
@endif

{{-- Vital Sign Clinical Alerts --}}
@if(count($vitalAlerts) > 0)
<div class="mb-4">
    @foreach($vitalAlerts as $alert)
    <div class="alert {{ $alert['status'] === 'critical' ? 'alert-danger' : 'alert-warning' }} mb-3">
        <i data-lucide="{{ $alert['status'] === 'critical' ? 'x-circle' : 'alert-circle' }}"></i>
        <div><strong>{{ $alert['vital'] }}:</strong> {{ $alert['value'] }} — {{ $alert['note'] }}</div>
    </div>
    @endforeach
</div>
@endif

@if(session('success'))
    <div class="alert alert-success mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Previous triage records --}}
@if($visit->triageRecords->isNotEmpty())
<div class="panel mb-6">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="clock"></i>
            Previous Triage Records
        </h2>
    </div>
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th><th>Complaint</th><th>Acuity</th><th>Pain</th><th>Vitals</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visit->triageRecords->sortByDesc('created_at') as $triage)
                    @php
                        $acuityBadge = match($triage->acuity_score) {
                            'resuscitation','critical' => 'badge-danger',
                            'urgent'      => 'badge-warning',
                            'semi_urgent' => 'badge-primary',
                            default       => 'badge-neutral',
                        };
                        $v = $triage->vitalSigns->first();
                        $triageAlerts = $v ? TriageService::assessVitals([
                            'oxygen_saturation'       => $v->oxygen_saturation,
                            'pulse'                   => $v->pulse,
                            'blood_pressure_systolic' => $v->blood_pressure_systolic,
                            'temperature'             => $v->temperature,
                            'respiratory_rate'        => $v->respiratory_rate,
                        ]) : [];
                        $hasCriticalVital = collect($triageAlerts)->where('status','critical')->count() > 0;
                    @endphp
                    <tr>
                        <td data-label="Time">{{ \Carbon\Carbon::parse($triage->created_at)->format('M d, H:i') }}
                            @if($hasCriticalVital)<span class="badge badge-critical">Critical</span>@endif
                        </td>
                        <td data-label="Complaint">{{ Str::limit($triage->presenting_complaint ?? '--', 50) }}</td>
                        <td data-label="Acuity"><span class="badge {{ $acuityBadge }}">{{ ucwords(str_replace('_',' ',$triage->acuity_score ?? '--')) }}</span></td>
                        <td data-label="Pain">{{ $triage->pain_score !== null ? $triage->pain_score . '/10' : '--' }}</td>
                        <td data-label="Vitals" class="mono">
                            @if($v)
                                @php
                                    $spo2Crit = isset($v->oxygen_saturation) && $v->oxygen_saturation < 90;
                                    $pulseCrit = isset($v->pulse) && ($v->pulse < 50 || $v->pulse > 150);
                                @endphp
                                T:{{ $v->temperature ?? '--' }}°C
                                BP:{{ $v->blood_pressure_systolic ?? '--' }}/{{ $v->blood_pressure_diastolic ?? '--' }}
                                @if($pulseCrit)<span class="badge badge-critical">P:{{ $v->pulse }}</span>@else P:{{ $v->pulse ?? '--' }} @endif
                                @if($spo2Crit)<span class="badge badge-critical">SpO₂:{{ $v->oxygen_saturation }}%</span>@else SpO₂:{{ $v->oxygen_saturation ?? '--' }}% @endif
                            @else
                                —
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
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="activity"></i>
            Record Triage
        </h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.visits.triage.store', $visit->id) }}">
            @csrf

            <div class="form-group mb-4">
                <label class="form-label form-label-required">Presenting Complaint *</label>
                <textarea name="presenting_complaint" class="form-control" rows="3" required
                    maxlength="1000" placeholder="Chief complaint / reason for visit…">{{ old('presenting_complaint') }}</textarea>
            </div>

            <div class="field-grid mb-4">
                <div class="form-group">
                    <label class="form-label form-label-required">Acuity Score *</label>
                    <select name="acuity_score" id="acuity_score" class="form-control" required>
                        <option value="resuscitation">Resuscitation (Level 1)</option>
                        <option value="critical">Critical (Level 2)</option>
                        <option value="urgent">Urgent (Level 3)</option>
                        <option value="semi_urgent" selected>Semi-Urgent (Level 4)</option>
                        <option value="non_urgent">Non-Urgent (Level 5)</option>
                    </select>
                    <div id="acuity-hint" class="form-hint" style="display:none;"></div>
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

            <h3 class="panel-title mt-6 mb-3">
                <i data-lucide="heart-pulse"></i>
                Vital Signs
                <span id="vitals-alert-badge" style="display:none;"></span>
            </h3>
            <div class="field-grid mb-6">
                <div class="form-group">
                    <label class="form-label">Temperature (°C)</label>
                    <input type="number" id="v_temp" name="temperature" class="form-control" step="0.1" min="20" max="45" placeholder="36.5">
                    <div class="vital-hint form-hint"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">BP Systolic</label>
                    <input type="number" id="v_sys" name="blood_pressure_systolic" class="form-control" min="40" max="300" placeholder="120">
                    <div class="vital-hint form-hint"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">BP Diastolic</label>
                    <input type="number" name="blood_pressure_diastolic" class="form-control" min="20" max="200" placeholder="80">
                </div>
                <div class="form-group">
                    <label class="form-label">Pulse (bpm)</label>
                    <input type="number" id="v_pulse" name="pulse" class="form-control" min="20" max="300" placeholder="72">
                    <div class="vital-hint form-hint"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Resp. Rate (/min)</label>
                    <input type="number" id="v_rr" name="respiratory_rate" class="form-control" min="4" max="60" placeholder="16">
                    <div class="vital-hint form-hint"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">SpO₂ (%)</label>
                    <input type="number" id="v_spo2" name="oxygen_saturation" class="form-control" step="0.1" min="50" max="100" placeholder="98">
                    <div class="vital-hint form-hint"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Weight (kg)</label>
                    <input type="number" name="weight" class="form-control" step="0.1" min="0.5" max="500" placeholder="70">
                </div>
                <div class="form-group">
                    <label class="form-label">Height (cm)</label>
                    <input type="number" name="height" class="form-control" step="0.1" min="20" max="250" placeholder="170">
                </div>
            </div>

            <div class="row-actions">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="activity"></i>
                    Save Triage
                </button>
                <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Emergency Escalation Modal --}}
<div id="escalate-modal" class="modal-backdrop" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="escalate-modal-title">
        <h3 class="modal__title" id="escalate-modal-title"><i data-lucide="siren"></i> Declare Emergency</h3>
        <form method="POST" action="{{ route('portals.staff.visits.triage.escalate', $visit->id) }}">
            @csrf
            <div class="modal__body">
                <p>
                    This will set acuity to <strong>Resuscitation (Level 1)</strong> and mark the visit as an emergency. This action is logged and cannot be undone without re-assessment.
                </p>
                <div class="form-group">
                    <label class="form-label">Reason for Emergency Escalation *</label>
                    <textarea name="reason" class="form-control" rows="3" required maxlength="500"
                        placeholder="e.g. Sudden cardiac arrest, severe respiratory distress…"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeEscalateModal()">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i data-lucide="siren"></i> Confirm Emergency
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Emergency modal
function openEscalateModal()  { document.getElementById('escalate-modal').removeAttribute('hidden'); }
function closeEscalateModal() { document.getElementById('escalate-modal').setAttribute('hidden',''); }
document.getElementById('escalate-modal').addEventListener('click', function(e) { if (e.target === this) closeEscalateModal(); });

// Live vital sign range feedback + auto-acuity suggestion
const ranges = {
    v_spo2:  { critical: [0, 90],  warning: [90, 95],  label: 'SpO₂',       criticalNote: 'Severe hypoxia — suggest Resuscitation', warningNote: 'Low O₂ — suggest Critical' },
    v_pulse: { critical: [[0,50],[150,400]], warning: [[50,60],[100,150]], label: 'Pulse', criticalNote: 'Extreme HR — suggest Critical', warningNote: 'Abnormal HR' },
    v_sys:   { critical: [0, 90],  warning: [90, 100], label: 'BP Systolic', criticalNote: 'Hypotension — suggest Critical', warningNote: 'Low blood pressure' },
    v_temp:  { critical: [[0,35],[40,50]], warning: [[35,36],[38.5,40]], label: 'Temp', criticalNote: 'Extreme temperature', warningNote: 'Abnormal temperature' },
    v_rr:    { critical: [0, 8],   warning: [8, 12],   label: 'Resp. Rate',  criticalNote: 'Respiratory failure risk', warningNote: 'Abnormal breathing rate' },
};

function checkRange(id) {
    const el = document.getElementById(id);
    if (!el) return null;
    const val = parseFloat(el.value);
    if (isNaN(val)) { clearHint(el); return null; }
    const r = ranges[id];
    if (!r) return null;

    let status = 'ok';
    let note = '';

    // Support both flat [min,max] and array-of-ranges
    const isCrit = Array.isArray(r.critical[0])
        ? r.critical.some(([a,b]) => val >= a && val < b)
        : (val < r.critical[0] || val >= r.critical[1]);
    const isWarn = !isCrit && (Array.isArray(r.warning[0])
        ? r.warning.some(([a,b]) => val >= a && val < b)
        : (val < r.warning[0] || val >= r.warning[1]));

    if (isCrit) { status = 'critical'; note = r.criticalNote; }
    else if (isWarn) { status = 'warning'; note = r.warningNote; }

    const hint = el.parentNode.querySelector('.vital-hint');
    if (hint) {
        hint.textContent = note;
        hint.style.color = status === 'critical' ? 'var(--p-danger)' : status === 'warning' ? 'var(--p-warning)' : '';
    }
    el.style.borderColor = status === 'critical' ? 'var(--p-danger)' : status === 'warning' ? 'var(--p-warning)' : '';
    return status;
}

function clearHint(el) {
    const hint = el.parentNode.querySelector('.vital-hint');
    if (hint) { hint.textContent = ''; }
    el.style.borderColor = '';
}

function updateAcuitySuggestion() {
    let worstStatus = 'ok';
    let worstNote = '';
    Object.keys(ranges).forEach(id => {
        const s = checkRange(id);
        if (s === 'critical') { worstStatus = 'critical'; worstNote = ranges[id].criticalNote; }
        else if (s === 'warning' && worstStatus !== 'critical') { worstStatus = 'warning'; worstNote = ranges[id].warningNote; }
    });

    const hint = document.getElementById('acuity-hint');
    const sel  = document.getElementById('acuity_score');
    if (worstStatus === 'critical') {
        hint.textContent = worstNote;
        hint.style.color = 'var(--p-danger)';
        hint.style.display = 'block';
        if (sel.value === 'semi_urgent' || sel.value === 'non_urgent') sel.value = 'critical';
    } else if (worstStatus === 'warning') {
        hint.textContent = '△ ' + worstNote;
        hint.style.color = 'var(--p-warning)';
        hint.style.display = 'block';
        if (sel.value === 'non_urgent') sel.value = 'urgent';
    } else {
        hint.textContent = '';
        hint.style.display = 'none';
    }
}

Object.keys(ranges).forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', updateAcuitySuggestion);
});
</script>
@endsection
