@extends('layouts.portal')

@section('title', 'Import — Preview & Validate')

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

@include('portals.staff.data_import._wizard_steps', ['step' => 3])

    @if(session('success'))
        <div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
    @endif

    {{-- Summary panel --}}
    <div class="panel mb-6">
        <div class="panel-header">
            <h3 class="panel-title">Validation Summary</h3>
            <span class="badge {{ $job->status === 'validated' ? 'badge-success' : 'badge-danger' }}">
                {{ ucwords(str_replace('_', ' ', $job->status)) }}
            </span>
        </div>
        <div class="panel-body">
            <p class="td-muted mb-6">
                {{ $job->original_filename }} · {{ $importTypes[$job->import_type]['label'] ?? $job->import_type }}
            </p>
            @php
                $cards = [
                    ['Total Rows', $job->total_rows,   '',                                                  'rows-height'],
                    ['Valid',      $job->valid_rows,   'stat-card--success',                                'check-circle'],
                    ['Invalid',    $job->invalid_rows, $job->invalid_rows > 0 ? 'stat-card--danger' : '',   'alert-triangle'],
                ];
            @endphp
            <div class="stat-grid">
                @foreach($cards as [$label, $value, $mod, $icon])
                <div class="stat-card {{ $mod }}">
                    <div class="stat-card__head"><i data-lucide="{{ $icon }}"></i></div>
                    <div class="stat-card__value">{{ number_format($value) }}</div>
                    <div class="stat-card__label">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Errors --}}
    @if($job->rowErrors->count() > 0)
    <div class="panel mb-6">
        <div class="panel-header">
            <h3 class="panel-title">Validation Errors <span class="badge badge-danger">{{ $job->rowErrors->count() }}</span></h3>
            <span class="td-muted">Showing up to 200 errors</span>
        </div>
        <div class="panel-body panel-body--flush">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Row #</th>
                            <th>Field</th>
                            <th>Error</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($job->rowErrors as $err)
                        <tr>
                            <td data-label="Row #">{{ $err->row_number }}</td>
                            <td data-label="Field"><span class="badge badge-neutral">{{ $err->field ?? '—' }}</span></td>
                            <td data-label="Error"><span class="mono">{{ $err->error_code }}</span></td>
                            <td data-label="Message">{{ $err->message }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Action panel --}}
    <div class="panel">
        <div class="panel-body">
            @if($job->canBeApproved())
                <div class="alert alert-success mb-6">
                    <i data-lucide="shield-check"></i>
                    <div>
                        <strong>Ready to import</strong>
                        <p>
                            {{ number_format($job->valid_rows) }} valid row(s) will be created.
                            @if($job->invalid_rows > 0)
                                {{ number_format($job->invalid_rows) }} invalid rows will be skipped.
                            @endif
                            This action cannot be undone without a rollback.
                        </p>
                    </div>
                </div>
                <div class="row-actions-inline">
                    <a href="{{ route('portals.staff.data_import.mapping', $job->id) }}" class="btn btn-ghost btn-sm">Edit Mapping</a>
                    <form method="POST" action="{{ route('portals.staff.data_import.approve', $job->id) }}" class="inline-form">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm"
                            onclick="return confirm('Approve and execute this import? This will create {{ $job->valid_rows }} record(s).')">
                            <i data-lucide="check-circle"></i>
                            Approve &amp; Import {{ number_format($job->valid_rows) }} Records
                        </button>
                    </form>
                </div>

            @elseif($job->status === 'validation_failed')
                <div class="alert alert-danger mb-6">
                    <i data-lucide="alert-triangle"></i>
                    <div>
                        <strong>All rows failed validation</strong>
                        <p>Fix the errors in your file and re-upload, or go back to edit the column mapping.</p>
                    </div>
                </div>
                <div class="row-actions-inline">
                    <a href="{{ route('portals.staff.data_import.mapping', $job->id) }}" class="btn btn-ghost btn-sm">Edit Mapping</a>
                    <a href="{{ route('portals.staff.data_import.create') }}" class="btn btn-primary btn-sm">Re-upload File</a>
                </div>

            @else
                <div class="row-actions-inline">
                    <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">Back to History</a>
                </div>
            @endif
        </div>
    </div>

@endsection
