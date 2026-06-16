@extends('layouts.portal')
@section('title', 'Clinical Decision Support — Alerts')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')

{{-- CDSS DISCLAIMER — must appear on every CDSS page --}}
<div class="alert alert-warning mb-6">
    <i data-lucide="shield-alert"></i>
    <div>
        <strong>{{ __('public.staff_portal.cdss_disclaimer_title') }}</strong>
        {{ __('public.staff_portal.cdss_disclaimer_body') }}
    </div>
</div>

<div class="page-head">
    <h2><i data-lucide="brain-circuit"></i> {{ __('public.staff_portal.cdss_title') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss.rules') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="list-checks"></i> {{ __('public.staff_portal.cdss_btn_rules') }}
    </a>
    <a href="{{ route('portals.staff.cdss.drug_interactions') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="git-merge"></i> {{ __('public.staff_portal.cdss_btn_interactions') }}
    </a>
    <a href="{{ route('portals.staff.cdss.lab_rules') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="test-tube"></i> {{ __('public.staff_portal.cdss_btn_lab_ranges') }}
    </a>
</div>
<p class="page-subtitle mb-4">{{ __('public.staff_portal.cdss_subtitle') }}</p>

@if(session('success'))<div class="alert alert-success mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-4"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- KPI Strip --}}
<div class="stat-grid">
    <div class="stat-card stat-card--danger">
        <div class="stat-card__head"><i data-lucide="alert-octagon"></i></div>
        <div class="stat-card__value">{{ $criticalCount }}</div>
        <div class="stat-card__label">{{ __('public.staff_portal.cdss_kpi_critical') }}</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-card__value">{{ $warningCount }}</div>
        <div class="stat-card__label">{{ __('public.staff_portal.cdss_kpi_warnings') }}</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="zap"></i></div>
        <div class="stat-card__value">{{ $todayTotal }}</div>
        <div class="stat-card__label">{{ __('public.staff_portal.cdss_kpi_today') }}</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle"></i></div>
        <div class="stat-card__value">{{ $overrideCount }}</div>
        <div class="stat-card__label">{{ __('public.staff_portal.cdss_kpi_overridden') }}</div>
    </div>
</div>

<div class="panel mt-6">
    <div class="panel-header">
        <h2 class="panel-title">{{ __('public.staff_portal.cdss_panel_alerts') }}</h2>
    </div>
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('public.staff_portal.cdss_col_severity') }}</th>
                        <th>{{ __('public.staff_portal.cdss_col_type') }}</th>
                        <th>{{ __('public.staff_portal.cdss_col_patient') }}</th>
                        <th>{{ __('public.staff_portal.cdss_col_message') }}</th>
                        <th>{{ __('public.staff_portal.cdss_col_triggered') }}</th>
                        <th>{{ __('public.staff_portal.cdss_col_status') }}</th>
                        <th class="row-actions">{{ __('public.staff_portal.cdss_col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAlerts as $alert)
                        <tr>
                            <td data-label="{{ __('public.staff_portal.cdss_col_severity') }}">
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
                            <td data-label="{{ __('public.staff_portal.cdss_col_type') }}">
                                <span class="badge badge-neutral">{{ str_replace('_', ' ', $alert->alert_type) }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_col_patient') }}">
                                @if($alert->patient)
                                    <div class="td-strong">{{ $alert->patient->full_name ?? $alert->patient->name ?? '—' }}</div>
                                    <div class="td-muted">{{ $alert->patient->health_id ?? '' }}</div>
                                @else
                                    <span class="td-muted">—</span>
                                @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_col_message') }}">
                                <div>{{ Str::limit($alert->alert_message, 100) }}</div>
                                @if($alert->recommendation)
                                    <div class="td-muted">
                                        <i data-lucide="lightbulb"></i>
                                        {{ Str::limit($alert->recommendation, 80) }}
                                    </div>
                                @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_col_triggered') }}" class="td-muted">
                                {{ $alert->triggered_at->format('d M H:i') }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_col_status') }}">
                                <span class="badge badge-{{ match($alert->status) {
                                    'active'       => 'danger',
                                    'acknowledged' => 'warning',
                                    'overridden'   => 'success',
                                    default        => 'neutral',
                                } }}">{{ $alert->status }}</span>
                            </td>
                            <td class="row-actions" data-label="{{ __('public.staff_portal.cdss_col_actions') }}">
                                @if($alert->status === 'active')
                                    <form method="POST" action="{{ route('portals.staff.cdss.acknowledge', $alert->id) }}" class="inline-form">
                                        @csrf
                                        <button type="submit" class="icon-btn" aria-label="{{ __('public.staff_portal.cdss_btn_ack') }}" title="{{ __('public.staff_portal.cdss_btn_ack') }}">
                                            <i data-lucide="check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="icon-btn"
                                            onclick="openOverrideModal('{{ $alert->id }}')"
                                            aria-label="{{ __('public.staff_portal.cdss_btn_override') }}" title="{{ __('public.staff_portal.cdss_btn_override') }}">
                                        <i data-lucide="shield-off"></i>
                                    </button>
                                @endif
                                @if($alert->visit_id)
                                    <a href="{{ route('portals.staff.visits.consult', $alert->visit_id) }}"
                                       class="icon-btn" aria-label="{{ __('public.staff_portal.action_view') }}" title="{{ __('public.staff_portal.action_view') }}">
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
                                    <p>{{ __('public.staff_portal.cdss_no_alerts') }}</p>
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
            {{ __('public.staff_portal.cdss_override_modal_title') }}
        </h3>
        <form id="overrideForm" method="POST" action="">
            @csrf
            <div class="modal__body">
                {{-- Safety reminder --}}
                <div class="alert alert-warning mb-4">
                    <i data-lucide="alert-triangle"></i>
                    <div><strong>{{ __('public.staff_portal.cdss_override_reminder') }}</strong> {{ __('public.staff_portal.cdss_override_reminder_body') }}</div>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.staff_portal.cdss_override_cat_label') }}</label>
                    <select name="override_category" class="form-control" required>
                        <option value="">{{ __('public.staff_portal.cdss_override_cat_ph') }}</option>
                        <option value="clinical_necessity">{{ __('public.staff_portal.cdss_cat_clinical_necessity') }}</option>
                        <option value="allergy_not_confirmed">{{ __('public.staff_portal.cdss_cat_allergy_unconf') }}</option>
                        <option value="risk_benefit">{{ __('public.staff_portal.cdss_cat_risk_benefit') }}</option>
                        <option value="patient_preference">{{ __('public.staff_portal.cdss_cat_patient_pref') }}</option>
                        <option value="other">{{ __('public.staff_portal.cdss_cat_other') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">
                        {{ __('public.staff_portal.cdss_justification_label') }}
                        <span class="form-hint">{{ __('public.staff_portal.cdss_justification_hint') }}</span>
                    </label>
                    <textarea name="override_reason" class="form-control" rows="4" required minlength="10" maxlength="500"
                              placeholder="{{ __('public.staff_portal.cdss_justification_ph') }}"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('overrideModal')">{{ __('public.portal.cancel') }}</button>
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="shield-off"></i>
                    {{ __('public.staff_portal.cdss_btn_override_record') }}
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
