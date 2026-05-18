@extends('layouts.portal')

@section('title', __('public.admin_governance.page_meta_title', [], app()->getLocale()) ?: 'Admin Governance Portal — OpesCare')

@section('breadcrumb_home', __('public.admin_governance.breadcrumb_home', [], app()->getLocale()) ?: 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))

@section('sidebar_role_badge')
    <div class="sidebar-role-badge" style="background:rgba(109,40,217,.3);border-color:rgba(109,40,217,.5);color:#C4B5FD;">
        <i data-lucide="shield-check" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        {{ __('public.admin_governance.role_administrator', [], app()->getLocale()) ?: 'Administrator' }}
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">{{ __('public.admin_governance.nav_governance', [], app()->getLocale()) ?: 'Governance' }}</div>
    <a href="{{ route('portals.admin') }}" class="sidebar-link active">
        <i data-lucide="layout-dashboard"></i>
        {{ __('public.admin_governance.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}
    </a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">{{ __('public.admin_governance.nav_identity', [], app()->getLocale()) ?: 'Identity' }}</div>
    <a href="{{ route('portals.admin') }}#partners" class="sidebar-link">
        <i data-lucide="building-2"></i>
        {{ __('public.admin_governance.nav_partner_governance', [], app()->getLocale()) ?: 'Partner Governance' }}
    </a>
    <a href="{{ route('portals.admin') }}#duplicates" class="sidebar-link">
        <i data-lucide="users"></i>
        {{ __('public.admin_governance.nav_duplicate_reviews', [], app()->getLocale()) ?: 'Duplicate Reviews' }}
    </a>
    <a href="{{ route('portals.admin') }}#security" class="sidebar-link">
        <i data-lucide="activity"></i>
        {{ __('public.admin_governance.nav_security_events', [], app()->getLocale()) ?: 'Security Events' }}
    </a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">{{ __('public.admin_governance.nav_tools', [], app()->getLocale()) ?: 'Tools' }}</div>
    <a href="{{ route('public.help') }}" class="sidebar-link">
        <i data-lucide="help-circle"></i>
        {{ __('public.admin_governance.nav_help', [], app()->getLocale()) ?: 'Help' }}
    </a>
@endsection

@section('sidebar_user_role', __('public.admin_governance.role_administrator', [], app()->getLocale()) ?: 'Administrator')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.admin_governance.page_title', [], app()->getLocale()) ?: 'Admin Governance Portal' }}</h1>
        <p class="page-subtitle">{{ __('public.admin_governance.page_subtitle', [], app()->getLocale()) ?: 'Manage Health IDs, review duplicate cases, and monitor security events.' }}</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="document.getElementById('duplicates').scrollIntoView({behavior:'smooth'})">
            <i data-lucide="users"></i>
            {{ __('public.admin_governance.btn_duplicate_review', [], app()->getLocale()) ?: 'Duplicate Review' }}
        </button>
        <button class="btn btn-secondary" onclick="document.getElementById('partners').scrollIntoView({behavior:'smooth'})">
            <i data-lucide="building-2"></i>
            {{ __('public.admin_governance.btn_partners', [], app()->getLocale()) ?: 'Partners' }}
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon blue"><i data-lucide="id-card"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">{{ __('public.admin_governance.kpi_total_health_ids', [], app()->getLocale()) ?: 'Total Health IDs' }}</div>
            <div class="kpi-value">{{ number_format($stats['total_ids']) }}</div>
            <div class="kpi-sub">{{ __('public.admin_governance.kpi_registered_patients', [], app()->getLocale()) ?: 'Registered patients' }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon teal"><i data-lucide="qr-code"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">{{ __('public.admin_governance.kpi_active_tokens', [], app()->getLocale()) ?: 'Active Tokens' }}</div>
            <div class="kpi-value" style="color:var(--p-teal);">{{ number_format($stats['active_tokens']) }}</div>
            <div class="kpi-sub">{{ __('public.admin_governance.kpi_live_tokens', [], app()->getLocale()) ?: 'Live access tokens' }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon purple"><i data-lucide="activity"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">{{ __('public.admin_governance.kpi_total_lookups', [], app()->getLocale()) ?: 'Total Lookups' }}</div>
            <div class="kpi-value" style="color:#7C3AED;">{{ number_format($stats['total_access_logs']) }}</div>
            <div class="kpi-sub">{{ __('public.admin_governance.kpi_alltime_events', [], app()->getLocale()) ?: 'All-time access events' }}</div>
        </div>
    </div>
    <div class="kpi-card" style="border-color:rgba(185,28,28,.2);">
        <div class="kpi-icon danger"><i data-lucide="shield-x"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">{{ __('public.admin_governance.kpi_denied_lookups', [], app()->getLocale()) ?: 'Denied Lookups' }}</div>
            <div class="kpi-value" style="color:var(--p-danger);">{{ number_format($stats['denied_access']) }}</div>
            <div class="kpi-sub">{{ __('public.admin_governance.kpi_failed_denied', [], app()->getLocale()) ?: 'Failed / denied' }}</div>
        </div>
    </div>
</div>

<!-- Partner Governance -->
<div class="panel mb-6" id="partners" style="margin-bottom:var(--p-space-6);">
    <div class="panel-header">
        <h2 class="panel-title" style="color:var(--p-primary);">
            <i data-lucide="building-2"></i>
            {{ __('public.admin_governance.section_partner_governance', [], app()->getLocale()) ?: 'Partner Governance' }}
        </h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" id="partners-table" aria-label="{{ __('public.admin_governance.section_partner_governance', [], app()->getLocale()) ?: 'Partners governance' }}">
            <thead>
                <tr>
                    <th>{{ __('public.admin_governance.col_partner', [], app()->getLocale()) ?: 'Partner' }}</th>
                    <th>{{ __('public.admin_governance.col_type', [], app()->getLocale()) ?: 'Type' }}</th>
                    <th>{{ __('public.admin_governance.col_status', [], app()->getLocale()) ?: 'Status' }}</th>
                    <th>{{ __('public.admin_governance.col_trust_level', [], app()->getLocale()) ?: 'Trust Level' }}</th>
                    <th><span class="sr-only">{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}</span></th>
                </tr>
            </thead>
            <tbody id="partners-body">
                <tr>
                    <td colspan="5" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">
                        <i data-lucide="loader" style="width:1.25rem;height:1.25rem;display:inline-block;animation:spin 1s linear infinite;vertical-align:middle;margin-right:var(--p-space-2);"></i>
                        {{ __('public.admin_governance.loading_partners', [], app()->getLocale()) ?: 'Loading partners…' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Pending Duplicate Reviews -->
<div class="panel mb-6" id="duplicates" style="margin-bottom:var(--p-space-6);">
    <div class="panel-header">
        <h2 class="panel-title" style="color:var(--p-warning);">
            <i data-lucide="users"></i>
            {{ __('public.admin_governance.section_duplicate_reviews', [], app()->getLocale()) ?: 'Pending Duplicate Reviews' }}
        </h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" id="duplicates-table" aria-label="{{ __('public.admin_governance.section_duplicate_reviews', [], app()->getLocale()) ?: 'Duplicate review queue' }}">
            <thead>
                <tr>
                    <th>{{ __('public.admin_governance.col_match_score', [], app()->getLocale()) ?: 'Match Score' }}</th>
                    <th>{{ __('public.admin_governance.col_primary_patient', [], app()->getLocale()) ?: 'Primary Patient' }}</th>
                    <th>{{ __('public.admin_governance.col_secondary_patient', [], app()->getLocale()) ?: 'Secondary Patient' }}</th>
                    <th><span class="sr-only">{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}</span></th>
                </tr>
            </thead>
            <tbody id="duplicates-body">
                <tr>
                    <td colspan="4" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">
                        <i data-lucide="loader" style="width:1.25rem;height:1.25rem;display:inline-block;animation:spin 1s linear infinite;vertical-align:middle;margin-right:var(--p-space-2);"></i>
                        {{ __('public.admin_governance.loading_cases', [], app()->getLocale()) ?: 'Loading pending cases…' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Security Events -->
<div class="panel" id="security">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="activity"></i>
            {{ __('public.admin_governance.section_security_events', [], app()->getLocale()) ?: 'Recent Security Events' }}
        </h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="{{ __('public.admin_governance.section_security_events', [], app()->getLocale()) ?: 'Recent security events' }}">
            <thead>
                <tr>
                    <th>{{ __('public.admin_governance.col_timestamp', [], app()->getLocale()) ?: 'Timestamp' }}</th>
                    <th>{{ __('public.admin_governance.col_event_type', [], app()->getLocale()) ?: 'Event Type' }}</th>
                    <th>{{ __('public.admin_governance.col_target_health_id', [], app()->getLocale()) ?: 'Target Health ID' }}</th>
                    <th>{{ __('public.admin_governance.col_actor', [], app()->getLocale()) ?: 'Actor' }}</th>
                    <th>{{ __('public.admin_governance.col_result', [], app()->getLocale()) ?: 'Result' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
                <tr>
                    <td data-label="{{ __('public.admin_governance.col_timestamp', [], app()->getLocale()) ?: 'Timestamp' }}">
                        <span class="td-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</span>
                        <div class="td-muted" style="font-size:0.75rem;">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</div>
                    </td>
                    <td data-label="{{ __('public.admin_governance.col_event_type', [], app()->getLocale()) ?: 'Event' }}">
                        <span class="td-strong">{{ $log->access_type }}</span>
                    </td>
                    <td data-label="{{ __('public.admin_governance.col_target_health_id', [], app()->getLocale()) ?: 'Health ID' }}">
                        <span class="td-mono">{{ $log->health_id ?? __('public.admin_governance.lbl_unknown', [], app()->getLocale()) ?: 'Unknown' }}</span>
                    </td>
                    <td data-label="{{ __('public.admin_governance.col_actor', [], app()->getLocale()) ?: 'Actor' }}">
                        <span class="td-muted">{{ $log->actor_type ?? '—' }}</span>
                        @if(!empty($log->ip_address))
                        <div class="td-muted" style="font-size:0.75rem;">{{ $log->ip_address }}</div>
                        @endif
                    </td>
                    <td data-label="{{ __('public.admin_governance.col_result', [], app()->getLocale()) ?: 'Result' }}">
                        @if(($log->result ?? '') === 'success')
                            <span class="badge badge-success">{{ __('public.admin_governance.result_success', [], app()->getLocale()) ?: 'Success' }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('public.admin_governance.result_denied', [], app()->getLocale()) ?: 'Denied' }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">
                        {{ __('public.admin_governance.no_security_events', [], app()->getLocale()) ?: 'No security events recorded yet.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Duplicate Review Modal -->
<div id="duplicate-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:var(--p-space-4);" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:800px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:var(--p-space-5) var(--p-space-6);border-bottom:1px solid var(--p-border);display:flex;align-items:center;justify-content:space-between;">
            <h3 id="modal-title" style="font-size:1rem;font-weight:700;color:var(--p-text);margin:0;">{{ __('public.admin_governance.modal_review_title', [], app()->getLocale()) ?: 'Review Suspected Duplicate' }}</h3>
            <button id="close-duplicate" class="topbar-icon-btn" aria-label="Close modal" style="color:var(--p-text-muted);">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div style="padding:var(--p-space-6);">
            <div class="grid-2" style="margin-bottom:var(--p-space-5);">
                <!-- Primary -->
                <div style="background:var(--p-surface-2);border:1px solid var(--p-border);border-left:4px solid var(--p-primary);border-radius:var(--p-radius-lg);padding:var(--p-space-5);">
                    <div style="margin-bottom:var(--p-space-3);">
                        <span class="badge badge-primary">{{ __('public.admin_governance.badge_primary_record', [], app()->getLocale()) ?: 'Primary Record' }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:var(--p-space-2);font-size:0.875rem;">
                        <div><span style="color:var(--p-text-muted);">{{ __('public.admin_governance.lbl_health_id', [], app()->getLocale()) ?: 'Health ID:' }}</span> <span id="m-primary-id" style="font-family:monospace;font-weight:700;color:var(--p-primary);"></span></div>
                        <div><span style="color:var(--p-text-muted);">{{ __('public.admin_governance.lbl_name', [], app()->getLocale()) ?: 'Name:' }}</span> <strong id="m-primary-name" style="margin-left:4px;"></strong></div>
                        <div><span style="color:var(--p-text-muted);">{{ __('public.admin_governance.lbl_dob', [], app()->getLocale()) ?: 'DOB:' }}</span> <span id="m-primary-dob" style="margin-left:4px;"></span></div>
                        <div><span style="color:var(--p-text-muted);">{{ __('public.admin_governance.lbl_sex', [], app()->getLocale()) ?: 'Sex:' }}</span> <span id="m-primary-sex" style="margin-left:4px;"></span></div>
                    </div>
                </div>
                <!-- Secondary -->
                <div style="background:var(--p-surface-2);border:1px solid var(--p-border);border-left:4px solid var(--p-warning);border-radius:var(--p-radius-lg);padding:var(--p-space-5);">
                    <div style="margin-bottom:var(--p-space-3);">
                        <span class="badge badge-warning">{{ __('public.admin_governance.badge_suspected_duplicate', [], app()->getLocale()) ?: 'Suspected Duplicate' }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:var(--p-space-2);font-size:0.875rem;">
                        <div><span style="color:var(--p-text-muted);">{{ __('public.admin_governance.lbl_health_id', [], app()->getLocale()) ?: 'Health ID:' }}</span> <span id="m-secondary-id" style="font-family:monospace;font-weight:700;color:var(--p-warning);"></span></div>
                        <div><span style="color:var(--p-text-muted);">{{ __('public.admin_governance.lbl_name', [], app()->getLocale()) ?: 'Name:' }}</span> <strong id="m-secondary-name" style="margin-left:4px;"></strong></div>
                        <div><span style="color:var(--p-text-muted);">{{ __('public.admin_governance.lbl_dob', [], app()->getLocale()) ?: 'DOB:' }}</span> <span id="m-secondary-dob" style="margin-left:4px;"></span></div>
                        <div><span style="color:var(--p-text-muted);">{{ __('public.admin_governance.lbl_sex', [], app()->getLocale()) ?: 'Sex:' }}</span> <span id="m-secondary-sex" style="margin-left:4px;"></span></div>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:var(--p-space-5);">
                <label class="form-label" for="review-reason">{{ __('public.admin_governance.lbl_reviewer_notes', [], app()->getLocale()) ?: 'Reviewer Notes (Optional)' }}</label>
                <textarea id="review-reason" rows="2" class="form-control" placeholder="{{ __('public.admin_governance.lbl_reviewer_notes_ph', [], app()->getLocale()) ?: 'e.g. Verified via National ID…' }}"></textarea>
            </div>

            <div style="display:flex;gap:var(--p-space-3);">
                <button id="btn-reject-merge" class="btn btn-secondary" style="flex:1;">
                    <i data-lucide="x-circle"></i>
                    {{ __('public.admin_governance.btn_reject_match', [], app()->getLocale()) ?: 'Reject Match (Keep Separate)' }}
                </button>
                <button id="btn-approve-merge" class="btn btn-warning" style="flex:1;background:var(--p-warning);color:white;border-color:var(--p-warning);">
                    <i data-lucide="merge"></i>
                    {{ __('public.admin_governance.btn_confirm_merge', [], app()->getLocale()) ?: 'Confirm Merge' }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<script>
var _ag = {
    noPartners:        @json(__('public.admin_governance.no_partners',           [], app()->getLocale()) ?: 'No partners found.'),
    noDuplicates:      @json(__('public.admin_governance.no_duplicates',         [], app()->getLocale()) ?: 'No pending duplicate reviews.'),
    errDuplicates:     @json(__('public.admin_governance.error_loading_duplicates', [], app()->getLocale()) ?: 'Error loading duplicate cases.'),
    btnApprove:        @json(__('public.admin_governance.btn_approve',           [], app()->getLocale()) ?: 'Approve'),
    btnSuspend:        @json(__('public.admin_governance.btn_suspend',           [], app()->getLocale()) ?: 'Suspend'),
    btnReview:         @json(__('public.admin_governance.btn_review',            [], app()->getLocale()) ?: 'Review'),
    confirmApprove:    @json(__('public.admin_governance.js_confirm_approve',    [], app()->getLocale()) ?: 'Approve this partner?'),
    promptSuspension:  @json(__('public.admin_governance.js_prompt_suspension',  [], app()->getLocale()) ?: 'Enter suspension reason (min 10 chars):'),
    alertValidReason:  @json(__('public.admin_governance.js_alert_valid_reason', [], app()->getLocale()) ?: 'A valid reason of at least 10 characters is required.'),
    alertErrApproving: @json(__('public.admin_governance.js_alert_error_approving',  [], app()->getLocale()) ?: 'Error approving partner.'),
    alertErrSuspending:@json(__('public.admin_governance.js_alert_error_suspending', [], app()->getLocale()) ?: 'Error suspending partner.'),
    alertErrResolve:   @json(__('public.admin_governance.js_alert_error_resolve',    [], app()->getLocale()) ?: 'An error occurred while resolving the case.'),
    alertNetworkError: @json(__('public.admin_governance.js_alert_network_error',    [], app()->getLocale()) ?: 'Network error. Please try again.'),
    lblProcessing:     @json(__('public.admin_governance.lbl_processing',        [], app()->getLocale()) ?: 'Processing…'),
    col_match_score:      @json(__('public.admin_governance.col_match_score',      [], app()->getLocale()) ?: 'Match Score'),
    col_primary_patient:  @json(__('public.admin_governance.col_primary_patient',  [], app()->getLocale()) ?: 'Primary Patient'),
    col_secondary_patient:@json(__('public.admin_governance.col_secondary_patient',[], app()->getLocale()) ?: 'Secondary Patient'),
};
document.addEventListener('DOMContentLoaded', async function () {
    var modal         = document.getElementById('duplicate-modal');
    var closeBtn      = document.getElementById('close-duplicate');
    var btnApprove    = document.getElementById('btn-approve-merge');
    var btnReject     = document.getElementById('btn-reject-merge');
    var mergeCases    = [];
    var currentReviewId = null;

    // ── Partner Governance ──
    const loadPartners = async () => {
        try {
            const res = await fetch('/api/partner-governance/partners');
            const data = await res.json();
            const tbody = document.getElementById('partners-body');
            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">' + _ag.noPartners + '</td></tr>';
                return;
            }
            tbody.innerHTML = data.data.map(p => {
                const stBadge = p.status === 'active'
                    ? '<span class="badge badge-success">' + p.status + '</span>'
                    : p.status === 'suspended'
                    ? '<span class="badge badge-danger">' + p.status + '</span>'
                    : '<span class="badge badge-warning">' + p.status + '</span>';

                const actions = [
                    p.status === 'submitted' ? `<button onclick="approvePartner('${p.uuid}')" class="btn btn-teal btn-sm">${_ag.btnApprove}</button>` : '',
                    p.status !== 'suspended' && p.status !== 'submitted' ? `<button onclick="suspendPartner('${p.uuid}')" class="btn btn-danger btn-sm">${_ag.btnSuspend}</button>` : ''
                ].filter(Boolean).join(' ');

                return `<tr>
                    <td data-label="Partner"><span class="td-strong">${p.legal_name}</span><div class="td-muted" style="font-size:0.75rem;">${p.uuid}</div></td>
                    <td data-label="Type"><span class="badge badge-neutral">${p.partner_type}</span></td>
                    <td data-label="Status">${stBadge}</td>
                    <td data-label="Trust">${p.trust_level.replace('level_','').replace(/_/g,' ')}</td>
                    <td data-label="Actions" style="text-align:right;">${actions}</td>
                </tr>`;
            }).join('');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) { console.error(e); }
    };

    window.approvePartner = async (id) => {
        if (!confirm(_ag.confirmApprove)) return;
        try {
            await fetch(`/api/partner-governance/partners/${id}/approve`, { method: 'POST' });
            loadPartners();
        } catch(e) { alert(_ag.alertErrApproving); }
    };

    window.suspendPartner = async (id) => {
        const reason = prompt(_ag.promptSuspension);
        if (!reason || reason.length < 10) return alert(_ag.alertValidReason);
        try {
            const res = await fetch(`/api/partner-governance/partners/${id}/suspend`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ reason })
            });
            if (res.ok) loadPartners();
            else alert((await res.json()).message);
        } catch(e) { alert(_ag.alertErrSuspending); }
    };

    loadPartners();

    // ── Duplicate Cases ──
    const loadCases = async () => {
        const tbody = document.getElementById('duplicates-body');
        try {
            const res = await fetch('/api/v1/connect/admin/merge-cases');
            const data = await res.json();
            if (data.status === 'success') {
                mergeCases = data.cases;
                if (mergeCases.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">' + _ag.noDuplicates + '</td></tr>';
                    return;
                }
                tbody.innerHTML = mergeCases.map(c => `<tr>
                    <td data-label="${_ag.col_match_score ?? 'Score'}"><span class="badge badge-warning" style="font-size:0.875rem;">${c.match_score}%</span></td>
                    <td data-label="${_ag.col_primary_patient ?? 'Primary'}">
                        <span class="td-strong">${c.primary_patient.first_name} ${c.primary_patient.last_name}</span>
                        <div class="td-mono">${c.primary_patient.health_id}</div>
                    </td>
                    <td data-label="${_ag.col_secondary_patient ?? 'Secondary'}">
                        <span class="td-strong">${c.secondary_patient.first_name} ${c.secondary_patient.last_name}</span>
                        <div class="td-mono">${c.secondary_patient.health_id}</div>
                    </td>
                    <td data-label="Action" style="text-align:right;">
                        <button onclick="openReviewModal('${c.uuid}')" class="btn btn-primary btn-sm">${_ag.btnReview}</button>
                    </td>
                </tr>`).join('');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        } catch(e) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--p-danger);padding:var(--p-space-6);">' + _ag.errDuplicates + '</td></tr>';
        }
    };

    await loadCases();

    // ── Modal Logic ──
    window.openReviewModal = (id) => {
        currentReviewId = id;
        const c = mergeCases.find(x => x.uuid === id);
        if (!c) return;
        document.getElementById('m-primary-id').textContent   = c.primary_patient.health_id;
        document.getElementById('m-primary-name').textContent = c.primary_patient.first_name + ' ' + c.primary_patient.last_name;
        document.getElementById('m-primary-dob').textContent  = c.primary_patient.date_of_birth;
        document.getElementById('m-primary-sex').textContent  = c.primary_patient.sex;
        document.getElementById('m-secondary-id').textContent   = c.secondary_patient.health_id;
        document.getElementById('m-secondary-name').textContent = c.secondary_patient.first_name + ' ' + c.secondary_patient.last_name;
        document.getElementById('m-secondary-dob').textContent  = c.secondary_patient.date_of_birth;
        document.getElementById('m-secondary-sex').textContent  = c.secondary_patient.sex;
        document.getElementById('review-reason').value = '';
        modal.style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    if (closeBtn) closeBtn.addEventListener('click', () => { modal.style.display = 'none'; });
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') modal.style.display = 'none'; });

    const resolveMerge = async (resolution) => {
        const reason = document.getElementById('review-reason').value;
        const btn = resolution === 'approve' ? btnApprove : btnReject;
        const oldHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" style="width:1rem;height:1rem;"></i> ' + _ag.lblProcessing;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const res = await fetch(`/api/v1/connect/admin/merge-cases/${currentReviewId}/resolve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ resolution, review_reason: reason })
            });
            if (res.ok) {
                modal.style.display = 'none';
                await loadCases();
            } else {
                alert(_ag.alertErrResolve + ' ' + ((await res.json()).message ?? ''));
            }
        } catch(e) { alert(_ag.alertNetworkError); }
        finally {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    };

    if (btnApprove) btnApprove.addEventListener('click', () => resolveMerge('approve'));
    if (btnReject)  btnReject.addEventListener('click', () => resolveMerge('reject'));
});
</script>
@endsection
