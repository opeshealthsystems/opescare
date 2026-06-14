@extends('layouts.portal')
@section('title', 'Audit Explorer')
@include('portals.admin.security_ops._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Audit Explorer')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Audit Explorer</h1>
        <p class="page-subtitle">Search and filter all platform audit events with full context.</p>
    </div>
</div>

{{-- Filter panel --}}
<div class="panel mb-4">
    <div class="panel-body">
        <form method="GET" action="{{ route('portals.admin.security.audit_explorer') }}" class="field-grid">
            <div class="form-group">
                <label class="form-label">Action Type</label>
                <select name="action_type" class="form-control">
                    <option value="">All Actions</option>
                    @foreach($actionTypes as $at)
                        <option value="{{ $at }}" {{ request('action_type') === $at ? 'selected' : '' }}>{{ $at }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Resource Type</label>
                <select name="resource_type" class="form-control">
                    <option value="">All Resources</option>
                    @foreach($resourceTypes as $rt)
                        <option value="{{ $rt }}" {{ request('resource_type') === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Actor ID</label>
                <input type="text" name="actor_id" value="{{ request('actor_id') }}"
                    class="form-control" placeholder="Actor UUID or email">
            </div>
            <div class="form-group">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Emergency</label>
                <label class="form-check">
                    <input type="checkbox" name="emergency_only" value="1" {{ request('emergency_only') ? 'checked' : '' }}>
                    Emergency overrides only
                </label>
            </div>
            <div class="form-group form-actions-end">
                <div class="row-actions-inline">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    <a href="{{ route('portals.admin.security.audit_explorer') }}" class="btn btn-ghost btn-sm">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="search-code"></i> Audit Events</h3>
        <span class="badge badge-neutral badge-sm">{{ $events->total() }} total</span>
    </div>
    <div class="panel-body panel-body--flush">
        @if($events->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="search-x"></i></div>
                <h3>No audit events match your filters</h3>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Action</th><th>Resource</th><th>Actor</th><th>Patient</th><th>IP</th><th>Emergency</th><th>When</th><th></th>
                </tr></thead>
                <tbody>
                    @foreach($events as $ev)
                    <tr class="{{ $ev->emergency_override ? 'row-emergency' : '' }}">
                        <td data-label="Action"><span class="code-token">{{ $ev->action_type }}</span></td>
                        <td data-label="Resource">
                            @if($ev->resource_type)
                                <span class="badge badge-neutral badge-sm">{{ $ev->resource_type }}</span>
                                @if($ev->resource_id)
                                    <span class="code-muted">{{ substr($ev->resource_id,0,8) }}…</span>
                                @endif
                            @else —
                            @endif
                        </td>
                        <td data-label="Actor">{{ $ev->actor_id ? Str::limit($ev->actor_id,16) : '—' }}</td>
                        <td data-label="Patient" class="td-muted">{{ $ev->patient_id ? substr($ev->patient_id,0,8).'…' : '—' }}</td>
                        <td data-label="IP" class="td-muted">{{ $ev->ip_address ?? '—' }}</td>
                        <td data-label="Emergency">
                            @if($ev->emergency_override)
                                <span class="badge badge-emergency badge-sm">Emergency</span>
                            @else
                                <span class="badge badge-allowed badge-sm">Allowed</span>
                            @endif
                        </td>
                        <td data-label="When" class="td-muted">
                            {{ \Carbon\Carbon::parse($ev->created_at)->format('M d, H:i') }}
                        </td>
                        <td class="row-actions">
                            @if($ev->before_state || $ev->after_state)
                                <button type="button" class="icon-btn" aria-label="View details"
                                    onclick="showDetail({{ json_encode($ev->before_state) }}, {{ json_encode($ev->after_state) }}, '{{ addslashes($ev->action_type) }}', '{{ $ev->reason ? addslashes($ev->reason) : '' }}')">
                                    <i data-lucide="eye"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Detail modal --}}
<div id="detail-modal" class="modal-fixed">
    <div class="modal-fixed__panel">
        <div class="modal-fixed__head">
            <h3 id="detail-title" class="modal-fixed__title"></h3>
            <button type="button" class="icon-btn" aria-label="Close" onclick="closeDetail()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div id="detail-reason" class="text-muted text-sm mb-3"></div>
        <div class="diff-grid">
            <div>
                <div class="diff-label">Before</div>
                <pre id="detail-before" class="diff-pre"></pre>
            </div>
            <div>
                <div class="diff-label">After</div>
                <pre id="detail-after" class="diff-pre"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showDetail(before, after, action, reason) {
    document.getElementById('detail-title').textContent = action;
    document.getElementById('detail-reason').textContent = reason ? 'Reason: ' + reason : '';
    document.getElementById('detail-before').textContent = before ? JSON.stringify(before, null, 2) : '(empty)';
    document.getElementById('detail-after').textContent  = after  ? JSON.stringify(after,  null, 2) : '(empty)';
    document.getElementById('detail-modal').style.display = 'flex';
}
function closeDetail() { document.getElementById('detail-modal').style.display = 'none'; }
document.getElementById('detail-modal').addEventListener('click', function(e) { if(e.target===this) closeDetail(); });
</script>
@endsection
