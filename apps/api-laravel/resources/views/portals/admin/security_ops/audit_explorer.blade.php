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
<div class="panel" style="margin-bottom:1rem;">
    <div class="panel-body">
        <form method="GET" action="{{ route('portals.admin.security.audit_explorer') }}"
              style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.65rem;">
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="font-size:.75rem;">Action Type</label>
                <select name="action_type" class="form-control form-control-sm">
                    <option value="">All Actions</option>
                    @foreach($actionTypes as $at)
                        <option value="{{ $at }}" {{ request('action_type') === $at ? 'selected' : '' }}>{{ $at }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="font-size:.75rem;">Resource Type</label>
                <select name="resource_type" class="form-control form-control-sm">
                    <option value="">All Resources</option>
                    @foreach($resourceTypes as $rt)
                        <option value="{{ $rt }}" {{ request('resource_type') === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="font-size:.75rem;">Actor ID</label>
                <input type="text" name="actor_id" value="{{ request('actor_id') }}"
                    class="form-control form-control-sm" placeholder="Actor UUID or email">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="font-size:.75rem;">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="font-size:.75rem;">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
            </div>
            <div class="form-group" style="margin:0;align-self:flex-end;">
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.8rem;height:35px;">
                    <input type="checkbox" name="emergency_only" value="1" {{ request('emergency_only') ? 'checked' : '' }}>
                    Emergency overrides only
                </label>
            </div>
            <div style="align-self:flex-end;display:flex;gap:.4rem;">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="{{ route('portals.admin.security.audit_explorer') }}" class="btn btn-ghost btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;align-items:center;gap:.5rem;">
        <i data-lucide="search-code" style="width:15px;height:15px;color:var(--p-primary);"></i>
        <span style="font-weight:600;font-size:.9rem;">Audit Events</span>
        <span class="badge badge-neutral" style="font-size:.72rem;margin-left:auto;">{{ $events->total() }} total</span>
    </div>
    <div class="panel-body" style="padding:0;">
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
                    <tr style="{{ $ev->emergency_override ? 'background:rgba(239,68,68,.04);' : '' }}">
                        <td><code style="font-size:.77rem;">{{ $ev->action_type }}</code></td>
                        <td style="font-size:.78rem;">
                            @if($ev->resource_type)
                                <span class="badge badge-neutral" style="font-size:.7rem;">{{ $ev->resource_type }}</span>
                                @if($ev->resource_id)
                                    <code style="font-size:.72rem;color:var(--p-text-muted);">{{ substr($ev->resource_id,0,8) }}…</code>
                                @endif
                            @else —
                            @endif
                        </td>
                        <td style="font-size:.78rem;">{{ $ev->actor_id ? Str::limit($ev->actor_id,16) : '—' }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $ev->patient_id ? substr($ev->patient_id,0,8).'…' : '—' }}</td>
                        <td style="font-size:.75rem;color:var(--p-text-muted);">{{ $ev->ip_address ?? '—' }}</td>
                        <td>
                            @if($ev->emergency_override)
                                <span class="badge badge-danger" style="font-size:.7rem;">Yes</span>
                            @else
                                <span style="color:var(--p-text-muted);font-size:.78rem;">—</span>
                            @endif
                        </td>
                        <td style="font-size:.75rem;color:var(--p-text-muted);">
                            {{ \Carbon\Carbon::parse($ev->created_at)->format('M d, H:i') }}
                        </td>
                        <td>
                            @if($ev->before_state || $ev->after_state)
                                <button type="button" class="btn btn-ghost btn-xs"
                                    onclick="showDetail({{ json_encode($ev->before_state) }}, {{ json_encode($ev->after_state) }}, '{{ addslashes($ev->action_type) }}', '{{ $ev->reason ? addslashes($ev->reason) : '' }}')">
                                    <i data-lucide="eye" style="width:11px;height:11px;"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--p-border);">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Detail modal --}}
<div id="detail-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:660px;margin:1rem;max-height:88vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
            <h3 id="detail-title" style="margin:0;font-size:1rem;"></h3>
            <button type="button" class="btn btn-ghost btn-xs" onclick="closeDetail()">
                <i data-lucide="x" style="width:13px;height:13px;"></i>
            </button>
        </div>
        <div id="detail-reason" style="font-size:.8rem;color:var(--p-text-muted);margin-bottom:.75rem;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <div style="font-size:.73rem;font-weight:600;text-transform:uppercase;color:var(--p-text-muted);margin-bottom:.3rem;">Before</div>
                <pre id="detail-before" style="background:var(--p-surface-2,#f8f9fa);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:.65rem;font-size:.73rem;overflow-x:auto;margin:0;max-height:260px;overflow-y:auto;"></pre>
            </div>
            <div>
                <div style="font-size:.73rem;font-weight:600;text-transform:uppercase;color:var(--p-text-muted);margin-bottom:.3rem;">After</div>
                <pre id="detail-after" style="background:var(--p-surface-2,#f8f9fa);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:.65rem;font-size:.73rem;overflow-x:auto;margin:0;max-height:260px;overflow-y:auto;"></pre>
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
