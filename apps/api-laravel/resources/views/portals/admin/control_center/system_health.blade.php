@extends('layouts.portal')
@section('title', 'System Health')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'System Health')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">System Health</h1>
        <p class="page-subtitle">Live platform health checks and diagnostics.</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('portals.admin.cc.health') }}" class="btn btn-ghost btn-sm">
            <i data-lucide="refresh-cw" style="width:13px;height:13px;"></i> Refresh
        </a>
    </div>
</div>

@php
    $statusColor = fn($s) => match($s) {
        'ok'      => 'var(--p-success)',
        'warning' => 'var(--p-warning)',
        'error'   => 'var(--p-danger)',
        default   => 'var(--p-text-muted)',
    };
    $statusBadge = fn($s) => match($s) {
        'ok'      => 'badge-success',
        'warning' => 'badge-warning',
        'error'   => 'badge-danger',
        default   => 'badge-neutral',
    };
    $statusIcon = fn($s) => match($s) {
        'ok'      => 'check-circle',
        'warning' => 'alert-circle',
        'error'   => 'x-circle',
        default   => 'minus-circle',
    };
@endphp

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1.5rem;">

    {{-- Database --}}
    @php $db = $health['database'] ?? ['status'=>'unknown','message'=>''] @endphp
    <div class="panel" style="padding:1.25rem;">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
            <i data-lucide="{{ $statusIcon($db['status']) }}" style="width:20px;height:20px;color:{{ $statusColor($db['status']) }};flex-shrink:0;"></i>
            <span style="font-weight:600;font-size:.95rem;">Database</span>
            <span class="badge {{ $statusBadge($db['status']) }}" style="margin-left:auto;font-size:.7rem;">{{ strtoupper($db['status']) }}</span>
        </div>
        <p style="font-size:.8rem;color:var(--p-text-muted);margin:0;">{{ $db['message'] ?? 'Connection check' }}</p>
    </div>

    {{-- Storage --}}
    @php $st = $health['storage'] ?? ['status'=>'unknown','message'=>''] @endphp
    <div class="panel" style="padding:1.25rem;">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
            <i data-lucide="{{ $statusIcon($st['status']) }}" style="width:20px;height:20px;color:{{ $statusColor($st['status']) }};flex-shrink:0;"></i>
            <span style="font-weight:600;font-size:.95rem;">Storage</span>
            <span class="badge {{ $statusBadge($st['status']) }}" style="margin-left:auto;font-size:.7rem;">{{ strtoupper($st['status']) }}</span>
        </div>
        <p style="font-size:.8rem;color:var(--p-text-muted);margin:0;">{{ $st['message'] ?? 'Disk write check' }}</p>
    </div>

    {{-- Queue --}}
    @php $qu = $health['queue'] ?? ['status'=>'unknown','message'=>''] @endphp
    <div class="panel" style="padding:1.25rem;">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
            <i data-lucide="{{ $statusIcon($qu['status']) }}" style="width:20px;height:20px;color:{{ $statusColor($qu['status']) }};flex-shrink:0;"></i>
            <span style="font-weight:600;font-size:.95rem;">Queue</span>
            <span class="badge {{ $statusBadge($qu['status']) }}" style="margin-left:auto;font-size:.7rem;">{{ strtoupper($qu['status']) }}</span>
        </div>
        <p style="font-size:.8rem;color:var(--p-text-muted);margin:0;">{{ $qu['message'] ?? 'Queue table check' }}</p>
    </div>

    {{-- Failed Jobs --}}
    @php $fj = $health['failed_jobs'] ?? ['status'=>'ok','count'=>0,'message'=>''] @endphp
    <div class="panel" style="padding:1.25rem;">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
            <i data-lucide="{{ $statusIcon(($fj['count'] ?? 0) > 0 ? 'warning' : 'ok') }}" style="width:20px;height:20px;color:{{ ($fj['count'] ?? 0) > 0 ? 'var(--p-warning)' : 'var(--p-success)' }};flex-shrink:0;"></i>
            <span style="font-weight:600;font-size:.95rem;">Failed Jobs</span>
            <span class="badge {{ ($fj['count'] ?? 0) > 0 ? 'badge-warning' : 'badge-success' }}" style="margin-left:auto;font-size:.7rem;">{{ $fj['count'] ?? 0 }}</span>
        </div>
        <p style="font-size:.8rem;color:var(--p-text-muted);margin:0;">{{ $fj['message'] ?? 'Jobs in failed_jobs table' }}</p>
    </div>

    {{-- Maintenance --}}
    @php $mn = $health['maintenance'] ?? ['status'=>'ok','message'=>''] @endphp
    <div class="panel" style="padding:1.25rem;">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
            <i data-lucide="{{ $statusIcon($mn['status']) }}" style="width:20px;height:20px;color:{{ $statusColor($mn['status']) }};flex-shrink:0;"></i>
            <span style="font-weight:600;font-size:.95rem;">Maintenance Mode</span>
            <span class="badge {{ $statusBadge($mn['status']) }}" style="margin-left:auto;font-size:.7rem;">{{ strtoupper($mn['status']) }}</span>
        </div>
        <p style="font-size:.8rem;color:var(--p-text-muted);margin:0;">{{ $mn['message'] ?? 'Platform maintenance status' }}</p>
    </div>

</div>

{{-- Summary Panel --}}
<div class="panel">
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--p-border);">
        <h3 style="margin:0;font-size:.95rem;">Diagnostic Summary</h3>
    </div>
    <div class="panel-body">
        @php
            $errors   = collect($health)->where('status', 'error')->count();
            $warnings = collect($health)->where('status', 'warning')->count();
            $oks      = collect($health)->where('status', 'ok')->count();
        @endphp
        <div style="display:flex;gap:2rem;flex-wrap:wrap;margin-bottom:1rem;">
            <div style="text-align:center;">
                <div style="font-size:1.75rem;font-weight:700;color:var(--p-success);">{{ $oks }}</div>
                <div style="font-size:.8rem;color:var(--p-text-muted);">Healthy</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:1.75rem;font-weight:700;color:var(--p-warning);">{{ $warnings }}</div>
                <div style="font-size:.8rem;color:var(--p-text-muted);">Warnings</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:1.75rem;font-weight:700;color:var(--p-danger);">{{ $errors }}</div>
                <div style="font-size:.8rem;color:var(--p-text-muted);">Errors</div>
            </div>
        </div>
        @if($errors > 0)
            <div style="font-size:.85rem;color:var(--p-danger);">
                <i data-lucide="alert-triangle" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                Critical issues detected. Immediate action required.
            </div>
        @elseif($warnings > 0)
            <div style="font-size:.85rem;color:var(--p-warning);">
                <i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                Some checks need attention. Review warnings above.
            </div>
        @else
            <div style="font-size:.85rem;color:var(--p-success);">
                <i data-lucide="check-circle" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                All systems operational.
            </div>
        @endif
        <div style="margin-top:.75rem;font-size:.78rem;color:var(--p-text-muted);">
            Last checked: {{ now()->format('M d, Y H:i:s') }} (server time)
        </div>
    </div>
</div>
@endsection
