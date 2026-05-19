@extends('layouts.portal')
@section('title', 'Clinical Decision Support — Alerts')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    {{-- ⚠️ CDSS DISCLAIMER — must appear on every CDSS page --}}
    <div style="background:#fffbeb;border:1px solid #d97706;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
        <i data-lucide="shield-alert" style="width:18px;height:18px;flex-shrink:0;color:#d97706;"></i>
        <p style="margin:0;font-size:0.82rem;color:#92400e;font-weight:500;">
            <strong>Clinical Decision Support:</strong>
            Clinical alerts are decision-support tools only. They do not replace professional clinical judgment.
            All clinical decisions remain the responsibility of the treating clinician.
        </p>
    </div>

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="brain-circuit" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Clinical Alerts
            </h1>
            <p class="portal-page-subtitle">Active safety alerts requiring clinical attention</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('portals.staff.cdss.rules') }}" class="btn btn--outline btn--sm">
                <i data-lucide="list-checks" style="width:13px;height:13px;"></i> Rules
            </a>
            <a href="{{ route('portals.staff.cdss.drug_interactions') }}" class="btn btn--outline btn--sm">
                <i data-lucide="git-merge" style="width:13px;height:13px;"></i> Drug Interactions
            </a>
            <a href="{{ route('portals.staff.cdss.lab_rules') }}" class="btn btn--outline btn--sm">
                <i data-lucide="test-tube" style="width:13px;height:13px;"></i> Lab Ranges
            </a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;"><i data-lucide="alert-octagon" style="color:#dc2626;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#dc2626;">{{ $criticalCount }}</div>
                <div class="stat-card__label">Active Critical</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fffbeb;"><i data-lucide="alert-triangle" style="color:#d97706;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#d97706;">{{ $warningCount }}</div>
                <div class="stat-card__label">Active Warnings</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="zap" style="color:#2563eb;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $todayTotal }}</div>
                <div class="stat-card__label">Today's Alerts</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="check-circle" style="color:#16a34a;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#16a34a;">{{ $overrideCount }}</div>
                <div class="stat-card__label">Overridden Today</div>
            </div>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title">Active & Acknowledged Alerts</h2>
        </div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Severity</th>
                        <th>Type</th>
                        <th>Patient</th>
                        <th>Alert Message</th>
                        <th>Triggered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAlerts as $alert)
                        <tr style="{{ $alert->severity === 'critical' ? 'background:#fff5f5;' : '' }}">
                            <td>
                                <span class="badge badge--{{ $alert->severityColor() }}" style="font-size:0.72rem;">
                                    @if($alert->severity === 'critical')
                                        <i data-lucide="alert-octagon" style="width:11px;height:11px;"></i>
                                    @elseif($alert->severity === 'warning')
                                        <i data-lucide="alert-triangle" style="width:11px;height:11px;"></i>
                                    @else
                                        <i data-lucide="info" style="width:11px;height:11px;"></i>
                                    @endif
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge--default" style="font-size:0.72rem;">
                                    {{ str_replace('_', ' ', $alert->alert_type) }}
                                </span>
                            </td>
                            <td>
                                @if($alert->patient)
                                    <div style="font-size:0.83rem;font-weight:600;">{{ $alert->patient->full_name ?? $alert->patient->name ?? '—' }}</div>
                                    <div style="font-size:0.73rem;color:#9ca3af;">{{ $alert->patient->health_id ?? '' }}</div>
                                @else
                                    <span style="color:#9ca3af;font-size:0.82rem;">—</span>
                                @endif
                            </td>
                            <td style="max-width:300px;">
                                <div style="font-size:0.82rem;">{{ Str::limit($alert->alert_message, 100) }}</div>
                                @if($alert->recommendation)
                                    <div style="font-size:0.73rem;color:#6b7280;margin-top:2px;">
                                        <i data-lucide="lightbulb" style="width:11px;height:11px;"></i>
                                        {{ Str::limit($alert->recommendation, 80) }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;white-space:nowrap;">
                                {{ $alert->triggered_at->format('d M H:i') }}
                            </td>
                            <td>
                                <span class="badge badge--{{ match($alert->status) {
                                    'active'       => 'danger',
                                    'acknowledged' => 'warning',
                                    'overridden'   => 'success',
                                    default        => 'default',
                                } }}" style="font-size:0.72rem;">{{ $alert->status }}</span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    @if($alert->status === 'active')
                                        <form method="POST" action="{{ route('portals.staff.cdss.acknowledge', $alert->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn--sm btn--outline" title="Acknowledge">
                                                <i data-lucide="check" style="width:12px;height:12px;"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn--sm btn--warning"
                                                onclick="openOverrideModal('{{ $alert->id }}')"
                                                title="Override with reason">
                                            <i data-lucide="shield-off" style="width:12px;height:12px;"></i>
                                        </button>
                                    @endif
                                    @if($alert->visit_id)
                                        <a href="{{ route('portals.staff.visit', $alert->visit_id) }}"
                                           class="btn btn--sm btn--outline" title="View visit">
                                            <i data-lucide="external-link" style="width:12px;height:12px;"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">
                                <i data-lucide="shield-check" style="width:32px;height:32px;display:block;margin:0 auto 10px;color:#16a34a;"></i>
                                No active clinical alerts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentAlerts->hasPages())
            <div class="portal-card__footer">{{ $recentAlerts->links() }}</div>
        @endif
    </div>

</div>

{{-- Override Modal --}}
<div id="overrideModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('overrideModal')">
    <div class="modal-box" style="max-width:480px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i data-lucide="shield-off" style="width:16px;height:16px;"></i>
                Override Clinical Alert
            </h3>
            <button class="modal-close" onclick="closeModal('overrideModal')">&times;</button>
        </div>
        <form id="overrideForm" method="POST" action="">
            @csrf
            <div class="modal-body">
                {{-- Safety reminder --}}
                <div style="background:#fffbeb;border:1px solid #fbbf24;border-radius:6px;padding:10px 12px;margin-bottom:16px;font-size:0.8rem;color:#92400e;">
                    <strong>Clinical Reminder:</strong> You are overriding a safety alert. This action is recorded in the audit log. Ensure you have clinically assessed the risk before proceeding.
                </div>
                <div class="form-group">
                    <label class="form-label">Override Category <span style="color:red">*</span></label>
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
                    <label class="form-label">
                        Clinical Justification <span style="color:red">*</span>
                        <span style="font-size:0.75rem;color:#9ca3af;font-weight:400;">(minimum 10 characters)</span>
                    </label>
                    <textarea name="override_reason" class="form-control" rows="4" required minlength="10" maxlength="500"
                              placeholder="Document your clinical reasoning for overriding this alert…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('overrideModal')">Cancel</button>
                <button type="submit" class="btn btn--danger">
                    <i data-lucide="shield-off" style="width:13px;height:13px;"></i>
                    Override & Record Reason
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; lucide.createIcons(); }
function closeModal(id){ document.getElementById(id).style.display='none'; }

function openOverrideModal(alertId) {
    const base = '{{ url("portals/staff/cdss/alerts") }}';
    document.getElementById('overrideForm').action = base + '/' + alertId + '/override';
    openModal('overrideModal');
}
</script>
@endsection
