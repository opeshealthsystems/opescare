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

<div class="page-header">
    <div>
        <h1 class="page-title">Data Import</h1>
        <p class="page-subtitle">Upload, map, validate, and import CSV/Excel data into OpesCare.</p>
    </div>
    <a href="{{ route('portals.staff.data_import.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        New Import
    </a>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.data_import.index') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="status" class="form-control">
        <option value="">All Statuses</option>
        @foreach(['uploaded','mapping_required','preview_ready','validated','validation_failed','approved_for_import','importing','completed','completed_with_errors','failed','rolled_back','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <select name="import_type" class="form-control">
        <option value="">All Types</option>
        @foreach($importTypes as $key => $def)
            <option value="{{ $key }}" {{ request('import_type') === $key ? 'selected' : '' }}>{{ $def['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i> Filter
    </button>
    <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if(count($jobs) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="upload-cloud"></i></div>
                <h3>No imports yet</h3>
                <p>Start by uploading a CSV or Excel file to import data into OpesCare.</p>
                <a href="{{ route('portals.staff.data_import.create') }}" class="btn btn-primary btn-sm" style="margin-top:1rem;">
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
                            <td>
                                <span style="font-weight:500;font-size:.88rem;">{{ $job->original_filename }}</span>
                                <div style="font-size:.74rem;color:var(--p-text-muted);">{{ strtoupper($job->file_extension) }} · {{ number_format($job->file_size_bytes / 1024, 1) }} KB</div>
                            </td>
                            <td>
                                <span class="badge badge-neutral">{{ $importTypes[$job->import_type]['label'] ?? $job->import_type }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_',' ',$job->status)) }}</span>
                            </td>
                            <td>
                                @if($job->total_rows > 0)
                                    <span style="font-size:.8rem;">
                                        <span style="color:var(--p-success);">{{ $job->valid_rows }}<i data-lucide="check" style="width:12px;height:12px;vertical-align:-2px;"></i></span>
                                        @if($job->invalid_rows > 0)
                                            <span style="color:var(--p-danger);">/ {{ $job->invalid_rows }}<i data-lucide="x" style="width:12px;height:12px;vertical-align:-2px;"></i></span>
                                        @endif
                                        <span style="color:var(--p-text-muted);">/ {{ $job->total_rows }} total</span>
                                    </span>
                                @else
                                    <span style="color:var(--p-text-muted);font-size:.8rem;">—</span>
                                @endif
                            </td>
                            <td style="font-size:.8rem;color:var(--p-text-muted);">
                                {{ \Carbon\Carbon::parse($job->created_at)->format('M d, Y H:i') }}
                            </td>
                            <td>
                                <div style="display:flex;gap:.3rem;flex-wrap:wrap;">
                                    {{-- Continue wizard --}}
                                    @if($job->status === 'mapping_required')
                                        <a href="{{ route('portals.staff.data_import.mapping', $job->id) }}" class="btn btn-primary btn-xs">Map Columns</a>
                                    @elseif(in_array($job->status, ['preview_ready']))
                                        <a href="{{ route('portals.staff.data_import.mapping', $job->id) }}" class="btn btn-ghost btn-xs">Edit Mapping</a>
                                        <form method="POST" action="{{ route('portals.staff.data_import.validate', $job->id) }}" style="display:inline;">@csrf
                                            <button type="submit" class="btn btn-primary btn-xs">Validate</button>
                                        </form>
                                    @elseif(in_array($job->status, ['validated','validation_failed']))
                                        <a href="{{ route('portals.staff.data_import.preview', $job->id) }}" class="btn btn-primary btn-xs">Preview</a>
                                    @elseif($job->canBeRolledBack())
                                        <button type="button" class="btn btn-ghost btn-xs" onclick="openRollbackModal('{{ $job->id }}')">Rollback</button>
                                    @endif

                                    {{-- Audit log --}}
                                    <a href="{{ route('portals.staff.data_import.audit', $job->id) }}" class="btn btn-ghost btn-xs">
                                        <i data-lucide="scroll-text" style="width:11px;height:11px;"></i> Log
                                    </a>

                                    {{-- Cancel --}}
                                    @if($job->canBeCancelled())
                                        <form method="POST" action="{{ route('portals.staff.data_import.cancel', $job->id) }}" style="display:inline;">@csrf
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
<div id="rollback-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="margin:0 0 .5rem;font-size:1.05rem;">Rollback Import</h3>
        <p style="color:var(--p-text-muted);font-size:.85rem;margin:0 0 1rem;">
            This will reverse the import. Records created by this import batch will be removed.
        </p>
        <form id="rollback-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Reason <span style="color:var(--p-text-muted)">(optional)</span></label>
                <textarea name="reason" class="form-control" rows="3" maxlength="500" placeholder="Why are you rolling back?"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeRollbackModal()">Back</button>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i data-lucide="rotate-ccw" style="width:13px;height:13px;"></i> Rollback
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
        document.getElementById('rollback-modal').style.display = 'flex';
    }
    function closeRollbackModal() { document.getElementById('rollback-modal').style.display = 'none'; }
    document.getElementById('rollback-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRollbackModal();
    });
</script>
@endsection
