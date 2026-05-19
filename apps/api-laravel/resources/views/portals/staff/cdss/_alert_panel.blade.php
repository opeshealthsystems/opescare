{{--
    CDSS Alert Panel — inline partial for consultation/prescription workflow
    Included via: @include('portals.staff.cdss._alert_panel', ['alerts' => $alerts, 'visitId' => $visitId])
    Or loaded via AJAX from route('portals.staff.cdss.visit_alerts', $visitId)

    IMPORTANT: Clinical alerts are decision-support tools only.
    They do not replace professional clinical judgment.
--}}
@if($alerts->isNotEmpty())
<div id="cdss-alert-panel" style="margin-bottom:16px;">

    {{-- Safety disclaimer strip --}}
    <div style="background:#fffbeb;border:1px solid #d97706;border-radius:6px 6px 0 0;padding:8px 12px;display:flex;align-items:center;gap:8px;border-bottom:none;">
        <i data-lucide="shield-alert" style="width:14px;height:14px;color:#d97706;flex-shrink:0;"></i>
        <span style="font-size:0.78rem;color:#92400e;font-weight:600;">
            Clinical Decision Support — Alerts are advisory only. Clinical judgment takes precedence.
        </span>
    </div>

    <div style="border:1px solid #fde68a;border-radius:0 0 6px 6px;overflow:hidden;">
        @foreach($alerts as $alert)
        <div class="cdss-alert-item" id="cdss-alert-{{ $alert->id }}"
             style="padding:10px 14px;border-bottom:1px solid {{ $alert->severity === 'critical' ? '#fca5a5' : '#fde68a' }};
                    background:{{ $alert->severity === 'critical' ? '#fff5f5' : '#fffdf0' }};">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                        @if($alert->severity === 'critical')
                            <i data-lucide="alert-octagon" style="width:14px;height:14px;color:#dc2626;"></i>
                            <span style="font-size:0.76rem;font-weight:700;color:#dc2626;text-transform:uppercase;">Critical Alert</span>
                        @elseif($alert->severity === 'warning')
                            <i data-lucide="alert-triangle" style="width:14px;height:14px;color:#d97706;"></i>
                            <span style="font-size:0.76rem;font-weight:700;color:#d97706;text-transform:uppercase;">Warning</span>
                        @else
                            <i data-lucide="info" style="width:14px;height:14px;color:#2563eb;"></i>
                            <span style="font-size:0.76rem;font-weight:700;color:#2563eb;text-transform:uppercase;">Info</span>
                        @endif
                        <span class="badge badge--default" style="font-size:0.68rem;">{{ str_replace('_',' ', $alert->alert_type) }}</span>
                    </div>
                    <div style="font-size:0.83rem;color:#1f2937;margin-bottom:3px;">{{ $alert->alert_message }}</div>
                    @if($alert->recommendation)
                        <div style="font-size:0.78rem;color:#6b7280;">
                            <i data-lucide="lightbulb" style="width:11px;height:11px;"></i>
                            {{ $alert->recommendation }}
                        </div>
                    @endif
                </div>
                <div style="display:flex;gap:4px;flex-shrink:0;">
                    <form method="POST" action="{{ route('portals.staff.cdss.acknowledge', $alert->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn--sm btn--outline"
                                title="Acknowledge — I have reviewed this alert"
                                style="font-size:0.72rem;padding:3px 8px;">
                            <i data-lucide="check" style="width:11px;height:11px;"></i> ACK
                        </button>
                    </form>
                    <button type="button" class="btn btn--sm btn--warning"
                            onclick="openCdssOverride('{{ $alert->id }}')"
                            title="Override with documented reason"
                            style="font-size:0.72rem;padding:3px 8px;">
                        <i data-lucide="shield-off" style="width:11px;height:11px;"></i> Override
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Override Modal (inline, per-panel) --}}
<div id="cdssOverrideModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box" style="max-width:460px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i data-lucide="shield-off" style="width:16px;height:16px;"></i>
                Override Clinical Alert
            </h3>
            <button class="modal-close" onclick="document.getElementById('cdssOverrideModal').style.display='none'">&times;</button>
        </div>
        <form id="cdssOverrideForm" method="POST" action="">
            @csrf
            <div class="modal-body">
                <div style="background:#fffbeb;border:1px solid #fbbf24;border-radius:6px;padding:10px 12px;margin-bottom:14px;font-size:0.79rem;color:#92400e;">
                    <strong>Clinical Reminder:</strong>
                    Overriding this alert creates an audited record. Proceed only when clinically justified.
                    Clinical alerts are decision-support tools only — not diagnostic.
                </div>
                <div class="form-group">
                    <label class="form-label">Override Category <span style="color:red">*</span></label>
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
                    <label class="form-label">Clinical Justification <span style="color:red">*</span></label>
                    <textarea name="override_reason" class="form-control" rows="3" required minlength="10" maxlength="500"
                              placeholder="Document clinical reasoning…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline"
                        onclick="document.getElementById('cdssOverrideModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn--danger">
                    <i data-lucide="shield-off" style="width:13px;height:13px;"></i> Override & Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCdssOverride(alertId) {
    const base = '{{ url("portals/staff/cdss/alerts") }}';
    document.getElementById('cdssOverrideForm').action = base + '/' + alertId + '/override';
    document.getElementById('cdssOverrideModal').style.display = 'flex';
    if(window.lucide) lucide.createIcons();
}
</script>
@endif
