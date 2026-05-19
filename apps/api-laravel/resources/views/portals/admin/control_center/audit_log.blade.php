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
    <div class="panel-body" style="padding:0;">
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
                    <th>Action</th><th>Resource</th><th>Resource ID</th><th>Actor</th><th>IP Address</th><th>When</th><th></th>
                </tr></thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td><code style="font-size:.78rem;">{{ $log->action }}</code></td>
                        <td>
                            @if($log->resource_type)
                                <span class="badge badge-neutral" style="font-size:.72rem;">{{ $log->resource_type }}</span>
                            @else
                                <span style="color:var(--p-text-muted);">—</span>
                            @endif
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">
                            @if($log->resource_id)
                                <code style="font-size:.75rem;">{{ Str::limit($log->resource_id, 12) }}</code>
                            @else
                                —
                            @endif
                        </td>
                        <td style="font-size:.82rem;">{{ $log->actor_id }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $log->ip_address ?? '—' }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">
                            {{ \Carbon\Carbon::parse($log->occurred_at)->format('M d, Y H:i') }}
                            <div style="font-size:.72rem;">{{ \Carbon\Carbon::parse($log->occurred_at)->diffForHumans() }}</div>
                        </td>
                        <td>
                            @if($log->after || $log->before)
                                <button type="button" class="btn btn-ghost btn-xs"
                                    onclick="showDiff({{ json_encode($log->before) }}, {{ json_encode($log->after) }}, '{{ addslashes($log->action) }}')">
                                    <i data-lucide="eye" style="width:11px;height:11px;"></i> Diff
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
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--p-border);">
            {{ $logs->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Diff Modal --}}
<div id="diff-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:640px;margin:1rem;max-height:85vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 id="diff-title" style="margin:0;font-size:1.05rem;"></h3>
            <button type="button" class="btn btn-ghost btn-xs" onclick="closeDiff()">
                <i data-lucide="x" style="width:13px;height:13px;"></i>
            </button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:var(--p-text-muted);margin-bottom:.35rem;">Before</div>
                <pre id="diff-before" style="background:var(--p-surface-2,#f8f9fa);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:.75rem;font-size:.75rem;overflow-x:auto;margin:0;max-height:300px;overflow-y:auto;"></pre>
            </div>
            <div>
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:var(--p-text-muted);margin-bottom:.35rem;">After</div>
                <pre id="diff-after" style="background:var(--p-surface-2,#f8f9fa);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:.75rem;font-size:.75rem;overflow-x:auto;margin:0;max-height:300px;overflow-y:auto;"></pre>
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
        document.getElementById('diff-modal').style.display = 'flex';
    }
    function closeDiff() { document.getElementById('diff-modal').style.display = 'none'; }
    document.getElementById('diff-modal').addEventListener('click', function(e) { if (e.target === this) closeDiff(); });
</script>
@endsection
