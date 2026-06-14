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
        <div>Clinical Decision Support — Alerts are advisory only. Clinical judgment takes precedence.</div>
    </div>

    @foreach($alerts as $alert)
    @php
        $alertClass = match($alert->severity) {
            'critical' => 'alert-danger',
            'warning'  => 'alert-warning',
            default    => 'alert-info',
        };
        $alertLabel = match($alert->severity) {
            'critical' => 'Critical Alert',
            'warning'  => 'Warning',
            default    => 'Info',
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
                <span class="badge badge-neutral">{{ str_replace('_',' ', $alert->alert_type) }}</span>
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
                    <button type="submit" class="btn btn-secondary btn-xs" title="Acknowledge — I have reviewed this alert">
                        <i data-lucide="check"></i> ACK
                    </button>
                </form>
                <button type="button" class="btn btn-warning btn-xs"
                        onclick="openCdssOverride('{{ $alert->id }}')"
                        title="Override with documented reason">
                    <i data-lucide="shield-off"></i> Override
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
            Override Clinical Alert
        </h3>
        <form id="cdssOverrideForm" method="POST" action="">
            @csrf
            <div class="modal__body">
                <div class="alert alert-warning mb-4">
                    <i data-lucide="alert-triangle"></i>
                    <div>
                        <strong>Clinical Reminder:</strong>
                        Overriding this alert creates an audited record. Proceed only when clinically justified.
                        Clinical alerts are decision-support tools only — not diagnostic.
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Override Category *</label>
                    <select name="override_category" class="form-control" required>
                        <option value="">Select…</option>
                        <option value="clinical_necessity">Clinical Necessity</option>
                        <option value="allergy_not_confirmed">Allergy Not Confirmed</option>
                        <option value="risk_benefit">Risk-Benefit Favours Treatment</option>
                        <option value="patient_preference">Patient Preference (Informed Consent)</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Clinical Justification *</label>
                    <textarea name="override_reason" class="form-control" rows="3" required minlength="10" maxlength="500"
                              placeholder="Document clinical reasoning…"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('cdssOverrideModal').setAttribute('hidden','')">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="shield-off"></i> Override &amp; Record
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
