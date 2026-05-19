@extends('layouts.portal')

@section('title', 'New Import — Upload File')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">Clinical Staff</div>
@endsection
@section('sidebar_user_role', 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link"><i data-lucide="bar-chart-2"></i><span>Analytics</span></a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link"><i data-lucide="receipt"></i><span>Billing</span></a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link"><i data-lucide="headset"></i><span>Support</span></a>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link active"><i data-lucide="upload-cloud"></i><span>Data Import</span></a>
</div>
    <a href="{{ route('portals.staff.cdss') }}" class="sidebar-link {{ request()->routeIs('portals.staff.cdss*') ? 'active' : '' }}">
        <i data-lucide="brain-circuit"></i> Clinical Alerts</a>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i> Supply Chain</a>
@endsection

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Data Import')

@section('content')

{{-- Wizard Progress --}}
@include('portals.staff.data_import._wizard_steps', ['step' => 1])

<div style="max-width:640px;margin:0 auto;">
    <div class="panel">
        <div class="panel-body" style="padding:2rem;">
            <h2 style="font-size:1.15rem;margin:0 0 .35rem;">Upload Import File</h2>
            <p style="color:var(--p-text-muted);font-size:.88rem;margin:0 0 1.5rem;">
                Choose the data type you are importing, then upload a CSV or Excel file (max 25 MB).
            </p>

            @if(session('error'))
                <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
                    <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('portals.staff.data_import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Import Type *</label>
                    <select name="import_type" class="form-control" required onchange="updateFieldHint(this.value)">
                        <option value="">— select type —</option>
                        @foreach($importTypes as $key => $def)
                            <option value="{{ $key }}" {{ old('import_type') === $key ? 'selected' : '' }}>{{ $def['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Field hint panel --}}
                <div id="field-hint" style="display:none;background:var(--p-surface-2,#f8f9fa);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:.85rem 1rem;margin-bottom:1rem;font-size:.82rem;">
                    <div style="font-weight:600;margin-bottom:.4rem;">Expected columns for this type:</div>
                    <div id="field-hint-required" style="margin-bottom:.3rem;"></div>
                    <div id="field-hint-optional" style="color:var(--p-text-muted);"></div>
                </div>

                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">File (CSV, XLSX, XLS — max 25 MB) *</label>
                    <input type="file" name="file" class="form-control" required accept=".csv,.xlsx,.xls">
                    <div style="font-size:.75rem;color:var(--p-text-muted);margin-top:.3rem;">
                        Your file should have a header row as the first row. Column names do not need to match exactly — you will be able to map them on the next step.
                    </div>
                </div>

                <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                    <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-lucide="upload" style="width:13px;height:13px;"></i>
                        Upload &amp; Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var importTypeFields = @json(collect($importTypes)->map(fn($d) => ['required' => $d['required'], 'optional' => $d['optional']]));

function updateFieldHint(type) {
    var hint = document.getElementById('field-hint');
    var req  = document.getElementById('field-hint-required');
    var opt  = document.getElementById('field-hint-optional');

    if (!type || !importTypeFields[type]) {
        hint.style.display = 'none';
        return;
    }

    var fields = importTypeFields[type];
    req.innerHTML = '<strong>Required:</strong> ' + fields.required.join(', ');
    opt.innerHTML = fields.optional.length ? '<strong>Optional:</strong> ' + fields.optional.join(', ') : '';
    hint.style.display = 'block';
}
// Trigger on page load if old value set
var sel = document.querySelector('[name=import_type]');
if (sel && sel.value) updateFieldHint(sel.value);
</script>
@endsection
