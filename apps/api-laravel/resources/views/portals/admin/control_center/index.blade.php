@extends('layouts.portal')
@section('title', 'Master Admin Control Center')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Control Center')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Master Admin Control Center</h1>
        <p class="page-subtitle">Manage platform settings, feature flags, modules, maintenance, and system health.</p>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif

{{-- System Health Banner --}}
@php
    $anyError = collect($health)->whereIn('status', ['error'])->count() > 0;
    $anyWarn  = collect($health)->whereIn('status', ['warning'])->count() > 0 || ($health['failed_jobs']['count'] ?? 0) > 0;
@endphp
@if($anyError)
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:var(--p-radius);padding:1rem;margin-bottom:1rem;display:flex;gap:.75rem;align-items:center;">
    <i data-lucide="alert-triangle" style="width:18px;height:18px;color:var(--p-danger);flex-shrink:0;"></i>
    <div><strong>System issues detected.</strong> Check System Health for details.</div>
    <a href="{{ route('portals.admin.cc.health') }}" class="btn btn-danger btn-sm" style="margin-left:auto;">View Health</a>
</div>
@elseif($anyWarn)
<div style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:var(--p-radius);padding:1rem;margin-bottom:1rem;display:flex;gap:.75rem;align-items:center;">
    <i data-lucide="alert-circle" style="width:18px;height:18px;color:var(--p-warning);flex-shrink:0;"></i>
    <div><strong>Warning:</strong> Some system checks need attention.</div>
    <a href="{{ route('portals.admin.cc.health') }}" class="btn btn-ghost btn-sm" style="margin-left:auto;">View Health</a>
</div>
@endif

{{-- Quick nav cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
    @php $cards = [
        ['Platform Settings', 'sliders-horizontal', route('portals.admin.cc.settings'),   'Manage system-wide configuration'],
        ['Feature Flags',     'toggle-right',       route('portals.admin.cc.feature_flags'),'Enable/disable product features'],
        ['Module Toggles',    'puzzle',             route('portals.admin.cc.modules'),     'Turn modules on/off per scope'],
        ['Maintenance',       'wrench',             route('portals.admin.cc.maintenance'), 'Schedule downtime windows'],
        ['System Health',     'activity',           route('portals.admin.cc.health'),      'Live platform health checks'],
        ['Admin Log',         'scroll-text',        route('portals.admin.cc.audit'),       'Track all admin actions'],
    ]; @endphp
    @foreach($cards as [$title, $icon, $url, $desc])
    <a href="{{ $url }}" style="text-decoration:none;">
        <div class="panel" style="padding:1.25rem;transition:box-shadow .15s;cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
                <i data-lucide="{{ $icon }}" style="width:20px;height:20px;color:var(--p-primary);"></i>
                <span style="font-weight:600;font-size:.95rem;">{{ $title }}</span>
            </div>
            <p style="font-size:.8rem;color:var(--p-text-muted);margin:0;">{{ $desc }}</p>
        </div>
    </a>
    @endforeach
</div>

{{-- Recent Admin Actions --}}
<div class="panel">
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
        <h3 style="margin:0;font-size:.95rem;">Recent Admin Actions</h3>
        <a href="{{ route('portals.admin.cc.audit') }}" class="btn btn-ghost btn-xs">View All</a>
    </div>
    <div class="panel-body" style="padding:0;">
        @if($actions->count() === 0)
            <div style="padding:1.5rem;text-align:center;color:var(--p-text-muted);font-size:.85rem;">No actions recorded yet.</div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Action</th><th>Resource</th><th>Actor</th><th>When</th>
                </tr></thead>
                <tbody>
                    @foreach($actions as $a)
                    <tr>
                        <td><code style="font-size:.78rem;">{{ $a->action }}</code></td>
                        <td><span class="badge badge-neutral" style="font-size:.74rem;">{{ $a->resource_type ?? '—' }}</span></td>
                        <td style="font-size:.82rem;">{{ $a->actor_id }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ \Carbon\Carbon::parse($a->occurred_at)->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
