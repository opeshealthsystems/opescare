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
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i> Supply Chain</a>
@endsection

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Data Import')

@section('content')

@include('portals.staff.data_import._wizard_steps', ['step' => 3])

<div style="max-width:760px;margin:0 auto;">

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

    {{-- Summary panel --}}
    <div class="panel" style="margin-bottom:1rem;">
        <div class="panel-body" style="padding:1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h2 style="font-size:1.1rem;margin:0 0 .2rem;">Validation Summary</h2>
                    <p style="color:var(--p-text-muted);font-size:.83rem;margin:0;">
                        {{ $job->original_filename }} · {{ $importTypes[$job->import_type]['label'] ?? $job->import_type }}
                    </p>
                </div>
                <span class="badge {{ $job->status === 'validated' ? 'badge-success' : 'badge-danger' }}" style="font-size:.82rem;">
                    {{ ucwords(str_replace('_', ' ', $job->status)) }}
                </span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:1rem;margin-top:1.25rem;">
                @php
                    $cards = [
                        ['Total Rows',   $job->total_rows,   'neutral', 'rows-height'],
                        ['Valid',        $job->valid_rows,   'success', 'check-circle'],
                        ['Invalid',      $job->invalid_rows, $job->invalid_rows > 0 ? 'danger' : 'neutral', 'alert-triangle'],
                    ];
                @endphp
                @foreach($cards as [$label, $value, $color, $icon])
                <div style="background:var(--p-surface-2,#f8f9fa);border-radius:var(--p-radius);padding:.85rem 1rem;text-align:center;">
                    <i data-lucide="{{ $icon }}" style="width:18px;height:18px;color:var(--p-{{ $color === 'neutral' ? 'text-muted' : $color }});"></i>
                    <div style="font-size:1.5rem;font-weight:700;margin:.25rem 0;">{{ number_format($value) }}</div>
                    <div style="font-size:.75rem;color:var(--p-text-muted);">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Errors --}}
    @if($job->rowErrors->count() > 0)
    <div class="panel" style="margin-bottom:1rem;">
        <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:.95rem;">Validation Errors <span class="badge badge-danger" style="margin-left:.4rem;">{{ $job->rowErrors->count() }}</span></h3>
            <span style="font-size:.78rem;color:var(--p-text-muted);">Showing up to 200 errors</span>
        </div>
        <div class="panel-body" style="padding:0;">
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
                            <td>{{ $err->row_number }}</td>
                            <td><span class="badge badge-neutral">{{ $err->field ?? '—' }}</span></td>
                            <td><code style="font-size:.78rem;">{{ $err->error_code }}</code></td>
                            <td style="font-size:.83rem;">{{ $err->message }}</td>
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
        <div class="panel-body" style="padding:1.5rem;">
            @if($job->canBeApproved())
                <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.3);border-radius:var(--p-radius);padding:1rem;margin-bottom:1rem;">
                    <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.35rem;">
                        <i data-lucide="shield-check" style="width:16px;height:16px;color:var(--p-success);"></i>
                        <strong style="font-size:.9rem;">Ready to import</strong>
                    </div>
                    <p style="font-size:.83rem;color:var(--p-text-muted);margin:0;">
                        {{ number_format($job->valid_rows) }} valid row(s) will be created.
                        @if($job->invalid_rows > 0)
                            {{ number_format($job->invalid_rows) }} invalid rows will be skipped.
                        @endif
                        This action cannot be undone without a rollback.
                    </p>
                </div>
                <div style="display:flex;gap:.5rem;justify-content:flex-end;flex-wrap:wrap;">
                    <a href="{{ route('portals.staff.data_import.mapping', $job->id) }}" class="btn btn-ghost btn-sm">Edit Mapping</a>
                    <form method="POST" action="{{ route('portals.staff.data_import.approve', $job->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm"
                            onclick="return confirm('Approve and execute this import? This will create {{ $job->valid_rows }} record(s).')">
                            <i data-lucide="check-circle" style="width:13px;height:13px;"></i>
                            Approve &amp; Import {{ number_format($job->valid_rows) }} Records
                        </button>
                    </form>
                </div>

            @elseif($job->status === 'validation_failed')
                <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3);border-radius:var(--p-radius);padding:1rem;margin-bottom:1rem;">
                    <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.35rem;">
                        <i data-lucide="alert-triangle" style="width:16px;height:16px;color:var(--p-danger);"></i>
                        <strong style="font-size:.9rem;">All rows failed validation</strong>
                    </div>
                    <p style="font-size:.83rem;color:var(--p-text-muted);margin:0;">
                        Fix the errors in your file and re-upload, or go back to edit the column mapping.
                    </p>
                </div>
                <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                    <a href="{{ route('portals.staff.data_import.mapping', $job->id) }}" class="btn btn-ghost btn-sm">Edit Mapping</a>
                    <a href="{{ route('portals.staff.data_import.create') }}" class="btn btn-primary btn-sm">Re-upload File</a>
                </div>

            @else
                <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                    <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">Back to History</a>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
