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
    <a href="{{ route('portals.staff.cdss') }}" class="sidebar-link {{ request()->routeIs('portals.staff.cdss*') ? 'active' : '' }}">
        <i data-lucide="brain-circuit"></i> Clinical Alerts</a>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i> Supply Chain</a>
@endsection

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Data Import')

@section('content')

@include('portals.staff.data_import._wizard_steps', ['step' => 2])

    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">Map Columns</h3>
        </div>
        <div class="panel-body">
            <p class="td-muted">
                File: <strong>{{ $job->original_filename }}</strong> · Type: <strong>{{ $importTypes[$job->import_type]['label'] ?? $job->import_type }}</strong>
            </p>
            <p class="td-muted mb-6">
                Match each OpesCare field to the column in your file. Required fields must be mapped.
            </p>

            @if(session('error'))
                <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
            @endif

            {{-- Saved mappings picker --}}
            @if(count($saved) > 0)
            <div class="alert alert-info mb-6">
                <i data-lucide="bookmark"></i>
                <div>
                    <label class="form-label">Load a saved mapping template:</label>
                    <div class="row-actions-inline">
                        <select id="saved-mapping-picker" class="form-control">
                            <option value="">— select —</option>
                            @foreach($saved as $sm)
                                <option value="{{ htmlspecialchars(json_encode($sm['mapping']), ENT_QUOTES) }}">{{ $sm['name'] }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="applySavedMapping()">Apply</button>
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('portals.staff.data_import.mapping.store', $job->id) }}">
                @csrf

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>OpesCare Field</th>
                                <th>Required?</th>
                                <th>Map to Column in File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($systemFields as $sf)
                            @php
                                // Pre-fill from suggested or current mapping (reversed: field → file_col)
                                $reversedSuggested = array_flip($suggested ?? []);
                                $preSelected = $reversedSuggested[$sf['key']] ?? '';
                            @endphp
                            <tr data-field="{{ $sf['key'] }}">
                                <td data-label="OpesCare Field">
                                    <span class="{{ $sf['required'] ? 'td-strong' : '' }}">{{ $sf['key'] }}</span>
                                </td>
                                <td data-label="Required?">
                                    @if($sf['required'])
                                        <span class="badge badge-danger">Required</span>
                                    @else
                                        <span class="badge badge-neutral">Optional</span>
                                    @endif
                                </td>
                                <td data-label="Map to Column in File">
                                    <select name="mapping[{{ $preSelected ?: $sf['key'] }}]"
                                            class="form-control mapping-select"
                                            data-system-field="{{ $sf['key'] }}">
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
                <div class="alert alert-info mt-6">
                    <i data-lucide="bookmark"></i>
                    <div class="row-actions-inline">
                        <span>Save this mapping for reuse?</span>
                        <input type="text" name="save_as" class="form-control" placeholder="Name, e.g. Our patient CSV format">
                    </div>
                </div>

                <div class="row-actions-inline mt-6">
                    <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-lucide="check-circle"></i>
                        Save Mapping &amp; Validate
                    </button>
                </div>
            </form>
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
