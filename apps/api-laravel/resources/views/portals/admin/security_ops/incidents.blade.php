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
        <i data-lucide="plus"></i> Log Incident
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Filter bar --}}
<form method="GET" action="{{ route('portals.admin.security.incidents') }}" class="filter-bar">
    <select name="severity" class="filter-select" aria-label="Severity">
        <option value="">All Severities</option>
        @foreach(['critical','high','medium','low'] as $sev)
            <option value="{{ $sev }}" {{ request('severity') === $sev ? 'selected' : '' }}>{{ ucfirst($sev) }}</option>
        @endforeach
    </select>
    <select name="status" class="filter-select" aria-label="Status">
        <option value="">All Statuses</option>
        @foreach(['open','investigating','contained','resolved','closed'] as $st)
            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.security.incidents') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body panel-body--flush">
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
                        <td data-label="Type" class="td-strong">{{ $inc->incident_type }}</td>
                        <td data-label="Severity"><span class="badge {{ $sevBadge }} badge-sm">{{ ucfirst($inc->severity) }}</span></td>
                        <td data-label="Status"><span class="badge {{ $stBadge }} badge-sm">{{ ucfirst($inc->status) }}</span></td>
                        <td data-label="Summary">{{ Str::limit($inc->summary, 80) }}</td>
                        <td data-label="Detected" class="td-muted">{{ \Carbon\Carbon::parse($inc->detected_at)->format('M d, Y') }}</td>
                        <td data-label="Resolved" class="td-muted">
                            {{ $inc->resolved_at ? \Carbon\Carbon::parse($inc->resolved_at)->format('M d, Y') : '—' }}
                        </td>
                        <td class="row-actions">
                            <button type="button" class="btn btn-ghost btn-sm"
                                onclick="openUpdateModal(
                                    '{{ $inc->id }}',
                                    '{{ $inc->status }}',
                                    {{ json_encode($inc->summary) }},
                                    '{{ $inc->contained_at ? \Carbon\Carbon::parse($inc->contained_at)->format('Y-m-d') : '' }}',
                                    '{{ $inc->resolved_at ? \Carbon\Carbon::parse($inc->resolved_at)->format('Y-m-d') : '' }}'
                                )">
                                <i data-lucide="pencil"></i> Update
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            {{ $incidents->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div id="create-modal" class="modal-fixed">
    <div class="modal-fixed__panel">
        <div class="modal-fixed__head">
            <h3 class="modal-fixed__title">Log Security Incident</h3>
            <button type="button" class="icon-btn" aria-label="Close" onclick="closeCreateModal()"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="{{ route('portals.admin.security.incidents.store') }}">
            @csrf
            <div class="form-row mb-3">
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
            <div class="form-group mb-3">
                <label class="form-label">Detection Date/Time *</label>
                <input type="datetime-local" name="detected_at" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Summary *</label>
                <textarea name="summary" class="form-control" rows="4" required maxlength="2000"
                    placeholder="Describe what happened, what was affected, and initial impact assessment…"></textarea>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Log Incident</button>
            </div>
        </form>
    </div>
</div>

{{-- Update Modal --}}
<div id="update-modal" class="modal-fixed">
    <div class="modal-fixed__panel">
        <div class="modal-fixed__head">
            <h3 class="modal-fixed__title">Update Incident</h3>
            <button type="button" class="icon-btn" aria-label="Close" onclick="closeUpdateModal()"><i data-lucide="x"></i></button>
        </div>
        <form id="update-form" method="POST" action="">
            @csrf
            <div class="form-group mb-3">
                <label class="form-label">Status *</label>
                <select id="upd-status" name="status" class="form-control" required>
                    @foreach(['open','investigating','contained','resolved','closed'] as $st)
                        <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Summary</label>
                <textarea id="upd-summary" name="summary" class="form-control" rows="4" maxlength="2000"></textarea>
            </div>
            <div class="form-row mb-4">
                <div class="form-group">
                    <label class="form-label">Contained At</label>
                    <input type="date" id="upd-contained" name="contained_at" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Resolved At</label>
                    <input type="date" id="upd-resolved" name="resolved_at" class="form-control">
                </div>
            </div>
            <div class="modal__footer">
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
