@extends('layouts.portal')
@section('title', 'Clinical Decision Support — Alerts')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')

{{-- CDSS DISCLAIMER — must appear on every CDSS page --}}
<div class="alert alert-warning mb-6">
    <i data-lucide="shield-alert"></i>
    <div>
        <strong>Clinical Decision Support:</strong>
        Clinical alerts are decision-support tools only. They do not replace professional clinical judgment.
        All clinical decisions remain the responsibility of the treating clinician.
    </div>
</div>

<div class="page-head">
    <h2><i data-lucide="brain-circuit"></i> Clinical Alerts</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss.rules') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="list-checks"></i> Rules
    </a>
    <a href="{{ route('portals.staff.cdss.drug_interactions') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="git-merge"></i> Drug Interactions
    </a>
    <a href="{{ route('portals.staff.cdss.lab_rules') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="test-tube"></i> Lab Ranges
    </a>
</div>
<p class="page-subtitle mb-4">Active safety alerts requiring clinical attention</p>

@if(session('success'))<div class="alert alert-success mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-4"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- KPI Strip --}}
<div class="stat-grid">
    <div class="stat-card stat-card--danger">
        <div class="stat-card__head"><i data-lucide="alert-octagon"></i></div>
        <div class="stat-card__value">{{ $criticalCount }}</div>
        <div class="stat-card__label">Active Critical</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-card__value">{{ $warningCount }}</div>
        <div class="stat-card__label">Active Warnings</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="zap"></i></div>
        <div class="stat-card__value">{{ $todayTotal }}</div>
        <div class="stat-card__label">Today's Alerts</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle"></i></div>
        <div class="stat-card__value">{{ $overrideCount }}</div>
        <div class="stat-card__label">Overridden Today</div>
    </div>
</div>

<div class="panel mt-6">
    <div class="panel-header">
        <h2 class="panel-title">Active &amp; Acknowledged Alerts</h2>
    </div>
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Severity</th>
                        <th>Type</th>
                        <th>Patient</th>
                        <th>Alert Message</th>
                        <th>Triggered</th>
                        <th>Status</th>
                        <th class="row-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAlerts as $alert)
                        <tr>
                            <td data-label="Severity">
                                <span class="badge badge-{{ $alert->severityColor() }}">
                                    @if($alert->severity === 'critical')
                                        <i data-lucide="alert-octagon"></i>
                                    @elseif($alert->severity === 'warning')
                                        <i data-lucide="alert-triangle"></i>
                                    @else
                                        <i data-lucide="info"></i>
                                    @endif
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </td>
                            <td data-label="Type">
                                <span class="badge badge-neutral">{{ str_replace('_', ' ', $alert->alert_type) }}</span>
                            </td>
                            <td data-label="Patient">
                                @if($alert->patient)
                                    <div class="td-strong">{{ $alert->patient->full_name ?? $alert->patient->name ?? '—' }}</div>
                                    <div class="td-muted">{{ $alert->patient->health_id ?? '' }}</div>
                                @else
                                    <span class="td-muted">—</span>
                                @endif
                            </td>
                            <td data-label="Alert Message">
                                <div>{{ Str::limit($alert->alert_message, 100) }}</div>
                                @if($alert->recommendation)
                                    <div class="td-muted">
                                        <i data-lucide="lightbulb"></i>
                                        {{ Str::limit($alert->recommendation, 80) }}
                                    </div>
                                @endif
                            </td>
                            <td data-label="Triggered" class="td-muted">
                                {{ $alert->triggered_at->format('d M H:i') }}
                            </td>
                            <td data-label="Status">
                                <span class="badge badge-{{ match($alert->status) {
                                    'active'       => 'danger',
                                    'acknowledged' => 'warning',
                                    'overridden'   => 'success',
                                    default        => 'neutral',
                                } }}">{{ $alert->status }}</span>
                            </td>
                            <td class="row-actions" data-label="Actions">
                                @if($alert->status === 'active')
                                    <form method="POST" action="{{ route('portals.staff.cdss.acknowledge', $alert->id) }}" class="inline-form">
                                        @csrf
                                        <button type="submit" class="icon-btn" aria-label="Acknowledge" title="Acknowledge">
                                            <i data-lucide="check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="icon-btn"
                                            onclick="openOverrideModal('{{ $alert->id }}')"
                                            aria-label="Override with reason" title="Override with reason">
                                        <i data-lucide="shield-off"></i>
                                    </button>
                                @endif
                                @if($alert->visit_id)
                                    <a href="{{ route('portals.staff.visits.consult', $alert->visit_id) }}"
                                       class="icon-btn" aria-label="View visit" title="View visit">
                                        <i data-lucide="external-link"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i data-lucide="shield-check"></i></div>
                                    <p>No active clinical alerts.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($recentAlerts->hasPages())
        <div class="panel-body">{{ $recentAlerts->links() }}</div>
    @endif
</div>

{{-- Override Modal --}}
<div id="overrideModal" class="modal-backdrop" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="overrideModal-title">
        <h3 class="modal__title" id="overrideModal-title">
            <i data-lucide="shield-off"></i>
            Override Clinical Alert
        </h3>
        <form id="overrideForm" method="POST" action="">
            @csrf
            <div class="modal__body">
                {{-- Safety reminder --}}
                <div class="alert alert-warning mb-4">
                    <i data-lucide="alert-triangle"></i>
                    <div><strong>Clinical Reminder:</strong> You are overriding a safety alert. This action is recorded in the audit log. Ensure you have clinically assessed the risk before proceeding.</div>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Override Category *</label>
                    <select name="override_category" class="form-control" required>
                        <option value="">Select reason category…</option>
                        <option value="clinical_necessity">Clinical Necessity</option>
                        <option value="allergy_not_confirmed">Allergy Not Confirmed</option>
                        <option value="risk_benefit">Risk-Benefit Assessment Favours Treatment</option>
                        <option value="patient_preference">Patient Preference (Informed Consent)</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">
                        Clinical Justification *
                        <span class="form-hint">(minimum 10 characters)</span>
                    </label>
                    <textarea name="override_reason" class="form-control" rows="4" required minlength="10" maxlength="500"
                              placeholder="Document your clinical reasoning for overriding this alert…"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('overrideModal')">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="shield-off"></i>
                    Override &amp; Record Reason
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openModal(id){ document.getElementById(id).removeAttribute('hidden'); if(window.lucide) lucide.createIcons(); }
function closeModal(id){ document.getElementById(id).setAttribute('hidden',''); }

function openOverrideModal(alertId) {
    const base = '{{ url("portals/staff/cdss/alerts") }}';
    document.getElementById('overrideForm').action = base + '/' + alertId + '/override';
    openModal('overrideModal');
}
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
