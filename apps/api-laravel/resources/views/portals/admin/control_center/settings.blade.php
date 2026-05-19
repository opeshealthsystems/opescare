@extends('layouts.portal')
@section('title', 'Platform Settings')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Platform Settings')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Platform Settings</h1>
        <p class="page-subtitle">Manage global platform configuration values.</p>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

@forelse($groups as $group => $settings)
<div class="panel" style="margin-bottom:1rem;">
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--p-border);">
        <h3 style="margin:0;font-size:.95rem;text-transform:capitalize;">{{ ucwords($group) }}</h3>
    </div>
    <div class="panel-body" style="padding:0;">
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Key</th><th>Value</th><th>Type</th><th>Description</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($settings as $setting)
                    <tr>
                        <td><code style="font-size:.8rem;">{{ $setting['key'] }}</code></td>
                        <td style="font-size:.85rem;font-weight:500;">{{ $setting['value'] }}</td>
                        <td><span class="badge badge-neutral" style="font-size:.72rem;">{{ $setting['value_type'] }}</span></td>
                        <td style="font-size:.8rem;color:var(--p-text-muted);">{{ $setting['description'] ?? '—' }}</td>
                        <td>
                            <button type="button" class="btn btn-ghost btn-xs"
                                onclick="openEditModal('{{ $setting['key'] }}', '{{ addslashes($setting['value']) }}')">
                                <i data-lucide="pencil" style="width:11px;height:11px;"></i> Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@empty
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="sliders-horizontal"></i></div>
        <h3>No settings</h3>
        <p>Visit the Control Center to seed default settings.</p>
    </div>
@endforelse

{{-- Edit Modal --}}
<div id="edit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;">Edit Setting</h3>
        <form method="POST" action="{{ route('portals.admin.cc.settings.update') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Key</label>
                <input type="text" id="edit-key" name="key" class="form-control" readonly style="background:var(--p-surface-2,#f8f9fa);">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Value *</label>
                <input type="text" id="edit-value" name="value" class="form-control" required maxlength="500">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openEditModal(key, value) {
        document.getElementById('edit-key').value = key;
        document.getElementById('edit-value').value = value;
        document.getElementById('edit-modal').style.display = 'flex';
    }
    function closeEditModal() { document.getElementById('edit-modal').style.display = 'none'; }
    document.getElementById('edit-modal').addEventListener('click', function(e) { if (e.target === this) closeEditModal(); });
</script>
@endsection
