@extends('layouts.portal')
@section('title', 'Admin Action Log')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Admin Action Log')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Admin Action Log</h1>
        <p class="page-subtitle">Complete audit trail of all admin actions on the platform.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-body panel-body--flush">
        @if($logs->count() === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="scroll-text"></i></div>
                <h3>No admin actions recorded</h3>
                <p>Admin actions will appear here as they are performed.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Action</th><th>Resource</th><th>Resource ID</th><th>Actor</th><th>IP Address</th><th>When</th><th class="row-actions"></th>
                </tr></thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td data-label="Action"><span class="code-token">{{ $log->action }}</span></td>
                        <td data-label="Resource">
                            @if($log->resource_type)
                                <span class="badge badge-neutral badge-sm">{{ $log->resource_type }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Resource ID" class="td-muted">
                            @if($log->resource_id)
                                <span class="code-muted">{{ Str::limit($log->resource_id, 12) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td data-label="Actor">{{ $log->actor_id }}</td>
                        <td data-label="IP Address" class="td-muted">{{ $log->ip_address ?? '—' }}</td>
                        <td data-label="When" class="td-muted">
                            {{ \Carbon\Carbon::parse($log->occurred_at)->format('M d, Y H:i') }}
                            <div class="code-muted">{{ \Carbon\Carbon::parse($log->occurred_at)->diffForHumans() }}</div>
                        </td>
                        <td class="row-actions">
                            @if($log->after || $log->before)
                                <button type="button" class="icon-btn" aria-label="View diff"
                                    onclick="showDiff({{ json_encode($log->before) }}, {{ json_encode($log->after) }}, '{{ addslashes($log->action) }}')">
                                    <i data-lucide="eye"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($logs, 'links'))
        <div class="panel-footer">
            {{ $logs->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Diff Modal --}}
<div id="diff-modal" class="modal-fixed">
    <div class="modal-fixed__panel">
        <div class="modal-fixed__head">
            <h3 id="diff-title" class="modal-fixed__title"></h3>
            <button type="button" class="icon-btn" aria-label="Close" onclick="closeDiff()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="diff-grid">
            <div>
                <div class="diff-label">Before</div>
                <pre id="diff-before" class="diff-pre"></pre>
            </div>
            <div>
                <div class="diff-label">After</div>
                <pre id="diff-after" class="diff-pre"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showDiff(before, after, action) {
        document.getElementById('diff-title').textContent = 'Change diff: ' + action;
        document.getElementById('diff-before').textContent = before ? JSON.stringify(before, null, 2) : '(empty)';
        document.getElementById('diff-after').textContent  = after  ? JSON.stringify(after,  null, 2) : '(empty)';
        document.getElementById('diff-modal').classList.add('open');
    }
    function closeDiff() { document.getElementById('diff-modal').classList.remove('open'); }
    document.getElementById('diff-modal').addEventListener('click', function(e) { if (e.target === this) closeDiff(); });
</script>
@endsection
