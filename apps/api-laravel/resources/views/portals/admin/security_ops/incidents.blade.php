@extends('layouts.portal')
@section('title', 'Security Incidents')
@include('portals.admin.security_ops._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Security Incidents')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Security Incidents</h1>
        <p class="page-subtitle">Log, track, and resolve platform security incidents.</p>
    </div>
    <button type="button" class="btn btn-danger btn-sm" onclick="openCreateModal()">
        <i data-lucide="plus" style="width:14px;height:14px;"></i> Log Incident
    </button>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Filter bar --}}
<form method="GET" action="{{ route('portals.admin.security.incidents') }}" style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;">
    <select name="severity" class="form-control form-control-sm" style="max-width:140px;">
        <option value="">All Severities</option>
        @foreach(['critical','high','medium','low'] as $sev)
            <option value="{{ $sev }}" {{ request('severity') === $sev ? 'selected' : '' }}>{{ ucfirst($sev) }}</option>
        @endforeach
    </select>
    <select name="status" class="form-control form-control-sm" style="max-width:140px;">
        <option value="">All Statuses</option>
        @foreach(['open','investigating','contained','resolved','closed'] as $st)
            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
    <a href="{{ route('portals.admin.security.incidents') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($incidents->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="shield-check"></i></div>
                <h3>No incidents found</h3>
                <p>No security incidents match your filter criteria.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Type</th><th>Severity</th><th>Status</th><th>Summary</th><th>Detected</th><th>Resolved</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($incidents as $inc)
                    @php
                        $sevBadge = match($inc->severity) {
                            'critical' => 'badge-danger',
                            'high'     => 'badge-warning',
                            'medium'   => 'badge-primary',
                            default    => 'badge-neutral',
                        };
                        $stBadge = match($inc->status) {
                            'open'         => 'badge-danger',
                            'investigating'=> 'badge-warning',
                            'contained'    => 'badge-primary',
                            'resolved','closed' => 'badge-success',
                            default        => 'badge-neutral',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:500;font-size:.85rem;">{{ $inc->incident_type }}</td>
                        <td><span class="badge {{ $sevBadge }}" style="font-size:.72rem;">{{ ucfirst($inc->severity) }}</span></td>
                        <td><span class="badge {{ $stBadge }}" style="font-size:.72rem;">{{ ucfirst($inc->status) }}</span></td>
                        <td style="font-size:.8rem;max-width:240px;">{{ Str::limit($inc->summary, 80) }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ \Carbon\Carbon::parse($inc->detected_at)->format('M d, Y') }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">
                            {{ $inc->resolved_at ? \Carbon\Carbon::parse($inc->resolved_at)->format('M d, Y') : '—' }}
                        </td>
                        <td>
                            <button type="button" class="btn btn-ghost btn-xs"
                                onclick="openUpdateModal(
                                    '{{ $inc->id }}',
                                    '{{ $inc->status }}',
                                    {{ json_encode($inc->summary) }},
                                    '{{ $inc->contained_at ? \Carbon\Carbon::parse($inc->contained_at)->format('Y-m-d') : '' }}',
                                    '{{ $inc->resolved_at ? \Carbon\Carbon::parse($inc->resolved_at)->format('Y-m-d') : '' }}'
                                )">
                                <i data-lucide="pencil" style="width:11px;height:11px;"></i> Update
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--p-border);">
            {{ $incidents->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div id="create-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:520px;margin:1rem;max-height:90vh;overflow-y:auto;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;">Log Security Incident</h3>
        <form method="POST" action="{{ route('portals.admin.security.incidents.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Incident Type *</label>
                    <input type="text" name="incident_type" class="form-control" required maxlength="100"
                        placeholder="e.g. Unauthorized Access, Data Breach…">
                </div>
                <div class="form-group">
                    <label class="form-label">Severity *</label>
                    <select name="severity" class="form-control" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Detection Date/Time *</label>
                <input type="datetime-local" name="detected_at" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Summary *</label>
                <textarea name="summary" class="form-control" rows="4" required maxlength="2000"
                    placeholder="Describe what happened, what was affected, and initial impact assessment…"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Log Incident</button>
            </div>
        </form>
    </div>
</div>

{{-- Update Modal --}}
<div id="update-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:520px;margin:1rem;max-height:90vh;overflow-y:auto;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;">Update Incident</h3>
        <form id="update-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Status *</label>
                <select id="upd-status" name="status" class="form-control" required>
                    @foreach(['open','investigating','contained','resolved','closed'] as $st)
                        <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Summary</label>
                <textarea id="upd-summary" name="summary" class="form-control" rows="4" maxlength="2000"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Contained At</label>
                    <input type="date" id="upd-contained" name="contained_at" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Resolved At</label>
                    <input type="date" id="upd-resolved" name="resolved_at" class="form-control">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeUpdateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openCreateModal()  { document.getElementById('create-modal').style.display = 'flex'; }
function closeCreateModal() { document.getElementById('create-modal').style.display = 'none'; }
function openUpdateModal(id, status, summary, containedAt, resolvedAt) {
    document.getElementById('update-form').action = '/portals/admin/security/incidents/' + id;
    document.getElementById('upd-status').value   = status;
    document.getElementById('upd-summary').value  = summary;
    document.getElementById('upd-contained').value = containedAt;
    document.getElementById('upd-resolved').value  = resolvedAt;
    document.getElementById('update-modal').style.display = 'flex';
}
function closeUpdateModal() { document.getElementById('update-modal').style.display = 'none'; }
['create-modal','update-modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) { if(e.target===this) this.style.display='none'; });
});
</script>
@endsection
