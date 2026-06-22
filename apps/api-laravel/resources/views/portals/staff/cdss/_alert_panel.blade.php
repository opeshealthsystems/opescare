{{--
    CDSS Alert Panel — inline partial for consultation/prescription workflow
    Included via: @include('portals.staff.cdss._alert_panel', ['alerts' => $alerts, 'visitId' => $visitId])
    Or loaded via AJAX from route('portals.staff.cdss.visit_alerts', $visitId)

    IMPORTANT: Clinical alerts are decision-support tools only.
    They do not replace professional clinical judgment.
--}}
@if($alerts->isNotEmpty())
<div id="cdss-alert-panel" class="mb-4">

    {{-- Safety disclaimer strip --}}
    <div class="alert alert-warning mb-3">
        <i data-lucide="shield-alert"></i>
        <div>{{ __('public.staff_portal.cdss_panel_alert_advisory') }}</div>
    </div>

    @foreach($alerts as $alert)
    @php
        $alertClass = match($alert->severity) {
            'critical' => 'alert-danger',
            'warning'  => 'alert-warning',
            default    => 'alert-info',
        };
        $alertLabel = match($alert->severity) {
            'critical' => __('public.staff_portal.cdss_alert_critical'),
            'warning'  => __('public.staff_portal.cdss_alert_warning'),
            default    => __('public.staff_portal.cdss_alert_info'),
        };
        $alertIcon = match($alert->severity) {
            'critical' => 'alert-octagon',
            'warning'  => 'alert-triangle',
            default    => 'info',
        };
    @endphp
    <div class="cdss-alert-item alert {{ $alertClass }} mb-3" id="cdss-alert-{{ $alert->id }}">
        <i data-lucide="{{ $alertIcon }}"></i>
        <div>
            <div class="mb-1">
                <strong>{{ $alertLabel }}</strong>
                <span class="badge badge-neutral">@enum($alert->alert_type)</span>
            </div>
            <div>{{ $alert->alert_message }}</div>
            @if($alert->recommendation)
                <div class="td-muted">
                    <i data-lucide="lightbulb"></i>
                    {{ $alert->recommendation }}
                </div>
            @endif
            <div class="row-actions mt-1">
                <form method="POST" action="{{ route('portals.staff.cdss.acknowledge', $alert->id) }}" class="inline-form">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-xs" title="{{ __('public.staff_portal.cdss_btn_ack') }}">
                        <i data-lucide="check"></i> {{ __('public.staff_portal.cdss_btn_ack') }}
                    </button>
                </form>
                <button type="button" class="btn btn-warning btn-xs"
                        onclick="openCdssOverride('{{ $alert->id }}')"
                        title="{{ __('public.staff_portal.cdss_btn_override') }}">
                    <i data-lucide="shield-off"></i> {{ __('public.staff_portal.cdss_btn_override') }}
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Override Modal (inline, per-panel) --}}
<div id="cdssOverrideModal" class="modal-backdrop" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="cdssOverrideModal-title">
        <h3 class="modal__title" id="cdssOverrideModal-title">
            <i data-lucide="shield-off"></i>
            {{ __('public.staff_portal.cdss_override_modal_title') }}
        </h3>
        <form id="cdssOverrideForm" method="POST" action="">
            @csrf
            <div class="modal__body">
                <div class="alert alert-warning mb-4">
                    <i data-lucide="alert-triangle"></i>
                    <div>
                        <strong>{{ __('public.staff_portal.cdss_override_reminder') }}</strong>
                        {{ __('public.staff_portal.cdss_override_note') }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.staff_portal.cdss_override_cat_label') }}</label>
                    <select name="override_category" class="form-control" required>
                        <option value="">{{ __('public.staff_portal.cdss_override_cat_ph2') }}</option>
                        <option value="clinical_necessity">{{ __('public.staff_portal.cdss_cat_clinical_necessity') }}</option>
                        <option value="allergy_not_confirmed">{{ __('public.staff_portal.cdss_cat_allergy_unconf') }}</option>
                        <option value="risk_benefit">{{ __('public.staff_portal.cdss_cat_risk_benefit2') }}</option>
                        <option value="patient_preference">{{ __('public.staff_portal.cdss_cat_patient_pref') }}</option>
                        <option value="other">{{ __('public.staff_portal.cdss_cat_other') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.staff_portal.cdss_justification_label') }}</label>
                    <textarea name="override_reason" class="form-control" rows="3" required minlength="10" maxlength="500"
                              placeholder="{{ __('public.staff_portal.cdss_justification_ph2') }}"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('cdssOverrideModal').setAttribute('hidden','')">{{ __('public.portal.cancel') }}</button>
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="shield-off"></i> {{ __('public.staff_portal.cdss_btn_override_record2') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCdssOverride(alertId) {
    const base = '{{ url("portals/staff/cdss/alerts") }}';
    document.getElementById('cdssOverrideForm').action = base + '/' + alertId + '/override';
    document.getElementById('cdssOverrideModal').removeAttribute('hidden');
    if(window.lucide) lucide.createIcons();
}
</script>
@endif
