@extends('layouts.portal')

@section('title', 'Import — Map Columns')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">Clinical Staff</div>
@endsection
@section('sidebar_user_role', 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link active"><i data-lucide="upload-cloud"></i><span>Data Import</span></a>
</div>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i> Supply Chain</a>
@endsection

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Data Import')

@section('content')

@include('portals.staff.data_import._wizard_steps', ['step' => 2])

<div style="max-width:760px;margin:0 auto;">
    <div class="panel">
        <div class="panel-body" style="padding:2rem;">
            <h2 style="font-size:1.1rem;margin:0 0 .25rem;">Map Columns</h2>
            <p style="color:var(--p-text-muted);font-size:.85rem;margin:0 0 .75rem;">
                File: <strong>{{ $job->original_filename }}</strong> · Type: <strong>{{ $importTypes[$job->import_type]['label'] ?? $job->import_type }}</strong>
            </p>
            <p style="color:var(--p-text-muted);font-size:.85rem;margin:0 0 1.5rem;">
                Match each OpesCare field to the column in your file. Required fields must be mapped.
            </p>

            @if(session('error'))
                <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
                    <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
                </div>
            @endif

            {{-- Saved mappings picker --}}
            @if(count($saved) > 0)
            <div style="background:var(--p-surface-2,#f8f9fa);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:.75rem 1rem;margin-bottom:1.25rem;">
                <label class="form-label" style="margin-bottom:.4rem;">Load a saved mapping template:</label>
                <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                    <select id="saved-mapping-picker" class="form-control" style="max-width:280px;">
                        <option value="">— select —</option>
                        @foreach($saved as $sm)
                            <option value="{{ htmlspecialchars(json_encode($sm['mapping']), ENT_QUOTES) }}">{{ $sm['name'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="applySavedMapping()">Apply</button>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('portals.staff.data_import.mapping.store', $job->id) }}">
                @csrf

                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:2px solid var(--p-border);">
                                <th style="padding:.5rem .75rem;text-align:left;font-size:.82rem;color:var(--p-text-muted);">OpesCare Field</th>
                                <th style="padding:.5rem .75rem;text-align:left;font-size:.82rem;color:var(--p-text-muted);">Required?</th>
                                <th style="padding:.5rem .75rem;text-align:left;font-size:.82rem;color:var(--p-text-muted);">Map to Column in File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($systemFields as $sf)
                            @php
                                // Pre-fill from suggested or current mapping (reversed: field → file_col)
                                $reversedSuggested = array_flip($suggested ?? []);
                                $preSelected = $reversedSuggested[$sf['key']] ?? '';
                            @endphp
                            <tr style="border-bottom:1px solid var(--p-border);" data-field="{{ $sf['key'] }}">
                                <td style="padding:.6rem .75rem;">
                                    <span style="font-size:.88rem;font-weight:{{ $sf['required'] ? '600' : '400' }};">{{ $sf['key'] }}</span>
                                </td>
                                <td style="padding:.6rem .75rem;">
                                    @if($sf['required'])
                                        <span class="badge badge-danger">Required</span>
                                    @else
                                        <span class="badge badge-neutral">Optional</span>
                                    @endif
                                </td>
                                <td style="padding:.6rem .75rem;">
                                    <select name="mapping[{{ $preSelected ?: $sf['key'] }}]"
                                            class="form-control mapping-select"
                                            data-system-field="{{ $sf['key'] }}"
                                            style="font-size:.85rem;">
                                        <option value="">— skip —</option>
                                        @foreach(($job->detected_headers ?? []) as $col)
                                            <option value="{{ $sf['key'] }}" data-col="{{ $col }}"
                                                {{ ($preSelected === $col || (!$preSelected && $col === $sf['key'])) ? 'selected' : '' }}>
                                                {{ $col }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Save mapping for reuse --}}
                <div style="margin-top:1rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;background:var(--p-surface-2,#f8f9fa);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:.75rem 1rem;">
                    <i data-lucide="bookmark" style="width:14px;height:14px;color:var(--p-text-muted);"></i>
                    <span style="font-size:.85rem;color:var(--p-text-muted);">Save this mapping for reuse?</span>
                    <input type="text" name="save_as" class="form-control" style="max-width:240px;font-size:.85rem;" placeholder="Name, e.g. Our patient CSV format">
                </div>

                <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.25rem;">
                    <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-lucide="check-circle" style="width:13px;height:13px;"></i>
                        Save Mapping &amp; Validate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function applySavedMapping() {
    var picker = document.getElementById('saved-mapping-picker');
    if (!picker || !picker.value) return;

    try {
        var mapping = JSON.parse(picker.value); // { file_col: system_field }
        // Reverse: system_field → file_col
        var reversed = {};
        for (var col in mapping) {
            reversed[mapping[col]] = col;
        }
        // Apply to each row
        document.querySelectorAll('.mapping-select').forEach(function(sel) {
            var systemField = sel.getAttribute('data-system-field');
            var fileCol = reversed[systemField];
            if (fileCol) {
                for (var i = 0; i < sel.options.length; i++) {
                    if (sel.options[i].getAttribute('data-col') === fileCol) {
                        sel.selectedIndex = i;
                        break;
                    }
                }
            }
        });
    } catch(e) {
        console.warn('Could not apply saved mapping', e);
    }
}
</script>
@endsection
