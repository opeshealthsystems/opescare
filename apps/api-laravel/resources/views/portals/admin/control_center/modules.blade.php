@extends('layouts.portal')
@section('title', 'Module Toggles')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Module Toggles')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Module Toggles</h1>
        <p class="page-subtitle">Enable or disable platform modules by scope.</p>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($modules->count() === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="puzzle"></i></div>
                <h3>No module toggles</h3>
                <p>Visit the Control Center dashboard to seed default modules.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Module</th><th>Key</th><th>Scope</th><th>Status</th><th>Disable Reason</th><th>Updated By</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($modules as $mod)
                    <tr>
                        <td style="font-weight:500;">{{ $mod->module_label }}</td>
                        <td><code style="font-size:.78rem;">{{ $mod->module_key }}</code></td>
                        <td>
                            <span class="badge badge-neutral" style="font-size:.72rem;">{{ $mod->scope }}</span>
                            @if($mod->scope_value)
                                <span style="font-size:.72rem;color:var(--p-text-muted);margin-left:4px;">{{ $mod->scope_value }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $mod->enabled ? 'badge-success' : 'badge-neutral' }}">
                                {{ $mod->enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $mod->disable_reason ?? '—' }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $mod->updated_by ?? '—' }}</td>
                        <td>
                            @if($mod->enabled)
                                <button type="button" class="btn btn-ghost btn-xs"
                                    onclick="openDisableModal('{{ $mod->module_key }}', '{{ addslashes($mod->module_label) }}')">
                                    Disable
                                </button>
                            @else
                                <form method="POST" action="{{ route('portals.admin.cc.modules.toggle', urlencode($mod->module_key)) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="enabled" value="1">
                                    <input type="hidden" name="disable_reason" value="">
                                    <button type="submit" class="btn btn-success btn-xs">Enable</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Disable Modal --}}
<div id="disable-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:400px;margin:1rem;">
        <h3 style="margin:0 0 .5rem;font-size:1.05rem;">Disable Module</h3>
        <p id="disable-module-name" style="font-size:.85rem;color:var(--p-text-muted);margin:0 0 1rem;"></p>
        <form id="disable-form" method="POST" action="">
            @csrf
            <input type="hidden" name="enabled" value="0">
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Reason for disabling <span style="color:var(--p-text-muted);font-weight:400;">(optional)</span></label>
                <textarea name="disable_reason" class="form-control" rows="2" maxlength="255" placeholder="e.g. Scheduled maintenance…"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeDisableModal()">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Disable Module</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openDisableModal(id, label) {
        document.getElementById('disable-module-name').textContent = 'Module: ' + label;
        document.getElementById('disable-form').action = '/portals/admin/cc/modules/' + encodeURIComponent(id) + '/toggle';
        document.getElementById('disable-modal').style.display = 'flex';
    }
    function closeDisableModal() { document.getElementById('disable-modal').style.display = 'none'; }
    document.getElementById('disable-modal').addEventListener('click', function(e) { if (e.target === this) closeDisableModal(); });
</script>
@endsection
