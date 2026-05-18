@extends('layouts.portal')

@section('title', 'Admin Governance Portal — OpesCare')

@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))

@section('sidebar_role_badge')
    <div class="sidebar-role-badge" style="background:rgba(109,40,217,.3);border-color:rgba(109,40,217,.5);color:#C4B5FD;">
        <i data-lucide="shield-check" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        Administrator
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">Governance</div>
    <a href="{{ route('portals.admin') }}" class="sidebar-link active">
        <i data-lucide="layout-dashboard"></i>
        Dashboard
    </a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Identity</div>
    <a href="{{ route('portals.admin') }}#partners" class="sidebar-link">
        <i data-lucide="building-2"></i>
        Partner Governance
    </a>
    <a href="{{ route('portals.admin') }}#duplicates" class="sidebar-link">
        <i data-lucide="users"></i>
        Duplicate Reviews
    </a>
    <a href="{{ route('portals.admin') }}#security" class="sidebar-link">
        <i data-lucide="activity"></i>
        Security Events
    </a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Tools</div>
    <a href="{{ route('public.help') }}" class="sidebar-link">
        <i data-lucide="help-circle"></i>
        Help
    </a>
@endsection

@section('sidebar_user_role', 'Administrator')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Admin Governance Portal</h1>
        <p class="page-subtitle">Manage Health IDs, review duplicate cases, and monitor security events.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="document.getElementById('duplicates').scrollIntoView({behavior:'smooth'})">
            <i data-lucide="users"></i>
            Duplicate Review
        </button>
        <button class="btn btn-secondary" onclick="document.getElementById('partners').scrollIntoView({behavior:'smooth'})">
            <i data-lucide="building-2"></i>
            Partners
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon blue"><i data-lucide="id-card"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Total Health IDs</div>
            <div class="kpi-value">{{ number_format($stats['total_ids']) }}</div>
            <div class="kpi-sub">Registered patients</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon teal"><i data-lucide="qr-code"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Active Tokens</div>
            <div class="kpi-value" style="color:var(--p-teal);">{{ number_format($stats['active_tokens']) }}</div>
            <div class="kpi-sub">Live access tokens</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon purple"><i data-lucide="activity"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Total Lookups</div>
            <div class="kpi-value" style="color:#7C3AED;">{{ number_format($stats['total_access_logs']) }}</div>
            <div class="kpi-sub">All-time access events</div>
        </div>
    </div>
    <div class="kpi-card" style="border-color:rgba(185,28,28,.2);">
        <div class="kpi-icon danger"><i data-lucide="shield-x"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Denied Lookups</div>
            <div class="kpi-value" style="color:var(--p-danger);">{{ number_format($stats['denied_access']) }}</div>
            <div class="kpi-sub">Failed / denied</div>
        </div>
    </div>
</div>

<!-- Partner Governance -->
<div class="panel mb-6" id="partners" style="margin-bottom:var(--p-space-6);">
    <div class="panel-header">
        <h2 class="panel-title" style="color:var(--p-primary);">
            <i data-lucide="building-2"></i>
            Partner Governance
        </h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" id="partners-table" aria-label="Partners governance">
            <thead>
                <tr>
                    <th>Partner</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Trust Level</th>
                    <th><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody id="partners-body">
                <tr>
                    <td colspan="5" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">
                        <i data-lucide="loader" style="width:1.25rem;height:1.25rem;display:inline-block;animation:spin 1s linear infinite;vertical-align:middle;margin-right:var(--p-space-2);"></i>
                        Loading partners…
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
            Pending Duplicate Reviews
        </h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" id="duplicates-table" aria-label="Duplicate review queue">
            <thead>
                <tr>
                    <th>Match Score</th>
                    <th>Primary Patient</th>
                    <th>Secondary Patient</th>
                    <th><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody id="duplicates-body">
                <tr>
                    <td colspan="4" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">
                        <i data-lucide="loader" style="width:1.25rem;height:1.25rem;display:inline-block;animation:spin 1s linear infinite;vertical-align:middle;margin-right:var(--p-space-2);"></i>
                        Loading pending cases…
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
            Recent Security Events
        </h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="Recent security events">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Event Type</th>
                    <th>Target Health ID</th>
                    <th>Actor</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
                <tr>
                    <td data-label="Timestamp">
                        <span class="td-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</span>
                        <div class="td-muted" style="font-size:0.75rem;">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</div>
                    </td>
                    <td data-label="Event">
                        <span class="td-strong">{{ $log->access_type }}</span>
                    </td>
                    <td data-label="Health ID">
                        <span class="td-mono">{{ $log->health_id ?? 'Unknown' }}</span>
                    </td>
                    <td data-label="Actor">
                        <span class="td-muted">{{ $log->actor_type ?? '—' }}</span>
                        @if(!empty($log->ip_address))
                        <div class="td-muted" style="font-size:0.75rem;">{{ $log->ip_address }}</div>
                        @endif
                    </td>
                    <td data-label="Result">
                        @if(($log->result ?? '') === 'success')
                            <span class="badge badge-success">Success</span>
                        @else
                            <span class="badge badge-danger">Denied</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">
                        No security events recorded yet.
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
            <h3 id="modal-title" style="font-size:1rem;font-weight:700;color:var(--p-text);margin:0;">Review Suspected Duplicate</h3>
            <button id="close-duplicate" class="topbar-icon-btn" aria-label="Close modal" style="color:var(--p-text-muted);">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div style="padding:var(--p-space-6);">
            <div class="grid-2" style="margin-bottom:var(--p-space-5);">
                <!-- Primary -->
                <div style="background:var(--p-surface-2);border:1px solid var(--p-border);border-left:4px solid var(--p-primary);border-radius:var(--p-radius-lg);padding:var(--p-space-5);">
                    <div style="margin-bottom:var(--p-space-3);">
                        <span class="badge badge-primary">Primary Record</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:var(--p-space-2);font-size:0.875rem;">
                        <div><span style="color:var(--p-text-muted);">Health ID:</span> <span id="m-primary-id" style="font-family:monospace;font-weight:700;color:var(--p-primary);"></span></div>
                        <div><span style="color:var(--p-text-muted);">Name:</span> <strong id="m-primary-name" style="margin-left:4px;"></strong></div>
                        <div><span style="color:var(--p-text-muted);">DOB:</span> <span id="m-primary-dob" style="margin-left:4px;"></span></div>
                        <div><span style="color:var(--p-text-muted);">Sex:</span> <span id="m-primary-sex" style="margin-left:4px;"></span></div>
                    </div>
                </div>
                <!-- Secondary -->
                <div style="background:var(--p-surface-2);border:1px solid var(--p-border);border-left:4px solid var(--p-warning);border-radius:var(--p-radius-lg);padding:var(--p-space-5);">
                    <div style="margin-bottom:var(--p-space-3);">
                        <span class="badge badge-warning">Suspected Duplicate</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:var(--p-space-2);font-size:0.875rem;">
                        <div><span style="color:var(--p-text-muted);">Health ID:</span> <span id="m-secondary-id" style="font-family:monospace;font-weight:700;color:var(--p-warning);"></span></div>
                        <div><span style="color:var(--p-text-muted);">Name:</span> <strong id="m-secondary-name" style="margin-left:4px;"></strong></div>
                        <div><span style="color:var(--p-text-muted);">DOB:</span> <span id="m-secondary-dob" style="margin-left:4px;"></span></div>
                        <div><span style="color:var(--p-text-muted);">Sex:</span> <span id="m-secondary-sex" style="margin-left:4px;"></span></div>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:var(--p-space-5);">
                <label class="form-label" for="review-reason">Reviewer Notes (Optional)</label>
                <textarea id="review-reason" rows="2" class="form-control" placeholder="e.g. Verified via National ID…"></textarea>
            </div>

            <div style="display:flex;gap:var(--p-space-3);">
                <button id="btn-reject-merge" class="btn btn-secondary" style="flex:1;">
                    <i data-lucide="x-circle"></i>
                    Reject Match (Keep Separate)
                </button>
                <button id="btn-approve-merge" class="btn btn-warning" style="flex:1;background:var(--p-warning);color:white;border-color:var(--p-warning);">
                    <i data-lucide="merge"></i>
                    Confirm Merge
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
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">No partners found.</td></tr>';
                return;
            }
            tbody.innerHTML = data.data.map(p => {
                const stBadge = p.status === 'active'
                    ? '<span class="badge badge-success">' + p.status + '</span>'
                    : p.status === 'suspended'
                    ? '<span class="badge badge-danger">' + p.status + '</span>'
                    : '<span class="badge badge-warning">' + p.status + '</span>';

                const actions = [
                    p.status === 'submitted' ? `<button onclick="approvePartner('${p.uuid}')" class="btn btn-teal btn-sm">Approve</button>` : '',
                    p.status !== 'suspended' && p.status !== 'submitted' ? `<button onclick="suspendPartner('${p.uuid}')" class="btn btn-danger btn-sm">Suspend</button>` : ''
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
        if (!confirm('Approve this partner?')) return;
        try {
            await fetch(`/api/partner-governance/partners/${id}/approve`, { method: 'POST' });
            loadPartners();
        } catch(e) { alert('Error approving partner.'); }
    };

    window.suspendPartner = async (id) => {
        const reason = prompt('Enter suspension reason (min 10 chars):');
        if (!reason || reason.length < 10) return alert('Valid reason required.');
        try {
            const res = await fetch(`/api/partner-governance/partners/${id}/suspend`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ reason })
            });
            if (res.ok) loadPartners();
            else alert((await res.json()).message);
        } catch(e) { alert('Error suspending partner.'); }
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
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:var(--p-space-8);color:var(--p-text-muted);">No pending duplicate reviews.</td></tr>';
                    return;
                }
                tbody.innerHTML = mergeCases.map(c => `<tr>
                    <td data-label="Score"><span class="badge badge-warning" style="font-size:0.875rem;">${c.match_score}%</span></td>
                    <td data-label="Primary">
                        <span class="td-strong">${c.primary_patient.first_name} ${c.primary_patient.last_name}</span>
                        <div class="td-mono">${c.primary_patient.health_id}</div>
                    </td>
                    <td data-label="Secondary">
                        <span class="td-strong">${c.secondary_patient.first_name} ${c.secondary_patient.last_name}</span>
                        <div class="td-mono">${c.secondary_patient.health_id}</div>
                    </td>
                    <td data-label="Action" style="text-align:right;">
                        <button onclick="openReviewModal('${c.uuid}')" class="btn btn-primary btn-sm">Review</button>
                    </td>
                </tr>`).join('');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        } catch(e) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--p-danger);padding:var(--p-space-6);">Error loading duplicate cases.</td></tr>';
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
        btn.innerHTML = '<i data-lucide="loader" style="width:1rem;height:1rem;"></i> Processing…';
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
                alert('Error: ' + (await res.json()).message);
            }
        } catch(e) { alert('Network error.'); }
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
