@extends('layouts.portal')

@section('title', 'Data Import')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">Clinical Staff</div>
@endsection
@section('sidebar_user_role', 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link">
        <i data-lucide="bar-chart-2"></i><span>Analytics</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i><span>Appointments</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i><span>Patient Queue</span>
    </a>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="stethoscope"></i><span>Visits</span>
    </a>
    <a href="{{ route('portals.staff.cdss') }}" class="sidebar-link {{ request()->routeIs('portals.staff.cdss*') ? 'active' : '' }}">
        <i data-lucide="brain-circuit"></i><span>Clinical Alerts</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">HR & Staff</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i><span>Directory</span>
    </a>
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
        <i data-lucide="clock"></i><span>Shifts</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i><span>Duty Roster</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link">
        <i data-lucide="plane-takeoff"></i><span>Leave</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Inventory</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="pill"></i><span>Pharmacy</span>
    </a>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link">
        <i data-lucide="droplets"></i><span>Blood Bank</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Supply Chain</div>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i>
        <span>Supply Chain</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i><span>Billing</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i><span>Support</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i><span>Insurance</span>
    </a>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link active">
        <i data-lucide="upload-cloud"></i><span>Data Import</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Data Import')

@section('content')

<div class="page-head">
    <h2>Data import</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.data_import.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus-circle"></i>
        New Import
    </a>
</div>
<p class="page-subtitle mb-6">Upload, map, validate, and import CSV/Excel data into OpesCare.</p>

@if(session('success'))
    <div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.data_import.index') }}" class="filter-bar">
    <select name="status" class="filter-select">
        <option value="">All Statuses</option>
        @foreach(['uploaded','mapping_required','preview_ready','validated','validation_failed','approved_for_import','importing','completed','completed_with_errors','failed','rolled_back','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <select name="import_type" class="filter-select">
        <option value="">All Types</option>
        @foreach($importTypes as $key => $def)
            <option value="{{ $key }}" {{ request('import_type') === $key ? 'selected' : '' }}>{{ $def['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter"></i> Filter
    </button>
    <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body panel-body--flush">
        @if(count($jobs) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="upload-cloud"></i></div>
                <h3>No imports yet</h3>
                <p>Start by uploading a CSV or Excel file to import data into OpesCare.</p>
                <a href="{{ route('portals.staff.data_import.create') }}" class="btn btn-primary btn-sm mt-6">
                    Start Import
                </a>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Rows</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $job)
                        @php
                            $statusBadge = match($job->status) {
                                'completed'                => 'badge-success',
                                'completed_with_errors'    => 'badge-warning',
                                'failed', 'validation_failed' => 'badge-danger',
                                'importing','approved_for_import' => 'badge-teal',
                                'validated','preview_ready' => 'badge-primary',
                                'rolled_back','cancelled'  => 'badge-neutral',
                                default                    => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="File">
                                <span class="td-strong">{{ $job->original_filename }}</span>
                                <div class="td-muted">{{ strtoupper($job->file_extension) }} · {{ number_format($job->file_size_bytes / 1024, 1) }} KB</div>
                            </td>
                            <td data-label="Type">
                                <span class="badge badge-neutral">{{ $importTypes[$job->import_type]['label'] ?? $job->import_type }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_',' ',$job->status)) }}</span>
                            </td>
                            <td data-label="Rows">
                                @if($job->total_rows > 0)
                                    <span class="badge badge-success">{{ $job->valid_rows }}</span>
                                    @if($job->invalid_rows > 0)
                                        <span class="badge badge-danger">{{ $job->invalid_rows }}</span>
                                    @endif
                                    <span class="td-muted">/ {{ $job->total_rows }} total</span>
                                @else
                                    <span class="td-muted">—</span>
                                @endif
                            </td>
                            <td data-label="Created" class="td-muted">
                                {{ \Carbon\Carbon::parse($job->created_at)->format('M d, Y H:i') }}
                            </td>
                            <td data-label="Actions">
                                <div class="row-actions-inline">
                                    {{-- Continue wizard --}}
                                    @if($job->status === 'mapping_required')
                                        <a href="{{ route('portals.staff.data_import.mapping', $job->id) }}" class="btn btn-primary btn-xs">Map Columns</a>
                                    @elseif(in_array($job->status, ['preview_ready']))
                                        <a href="{{ route('portals.staff.data_import.mapping', $job->id) }}" class="btn btn-ghost btn-xs">Edit Mapping</a>
                                        <form method="POST" action="{{ route('portals.staff.data_import.validate', $job->id) }}" class="inline-form">@csrf
                                            <button type="submit" class="btn btn-primary btn-xs">Validate</button>
                                        </form>
                                    @elseif(in_array($job->status, ['validated','validation_failed']))
                                        <a href="{{ route('portals.staff.data_import.preview', $job->id) }}" class="btn btn-primary btn-xs">Preview</a>
                                    @elseif($job->canBeRolledBack())
                                        <button type="button" class="btn btn-ghost btn-xs" onclick="openRollbackModal('{{ $job->id }}')">Rollback</button>
                                    @endif

                                    {{-- Audit log --}}
                                    <a href="{{ route('portals.staff.data_import.audit', $job->id) }}" class="btn btn-ghost btn-xs">
                                        <i data-lucide="scroll-text"></i> Log
                                    </a>

                                    {{-- Cancel --}}
                                    @if($job->canBeCancelled())
                                        <form method="POST" action="{{ route('portals.staff.data_import.cancel', $job->id) }}" class="inline-form">@csrf
                                            <button type="submit" class="btn btn-ghost btn-xs" onclick="return confirm('Cancel this import?')">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Rollback Modal --}}
<div id="rollback-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true">
        <h3 class="modal__title"><i data-lucide="rotate-ccw"></i> Rollback Import</h3>
        <form id="rollback-form" method="POST" action="">
            @csrf
            <div class="modal__body">
                <p>This will reverse the import. Records created by this import batch will be removed.</p>
                <div class="form-group">
                    <label class="form-label">Reason <span class="td-muted">(optional)</span></label>
                    <textarea name="reason" class="form-control" rows="3" maxlength="500" placeholder="Why are you rolling back?"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeRollbackModal()">Back</button>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i data-lucide="rotate-ccw"></i> Rollback
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openRollbackModal(jobId) {
        document.getElementById('rollback-form').setAttribute('action', '{{ url('/portals/staff/data-import') }}/' + jobId + '/rollback');
        document.getElementById('rollback-modal').removeAttribute('hidden');
    }
    function closeRollbackModal() { document.getElementById('rollback-modal').setAttribute('hidden',''); }
    document.getElementById('rollback-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRollbackModal();
    });
</script>
@endsection
