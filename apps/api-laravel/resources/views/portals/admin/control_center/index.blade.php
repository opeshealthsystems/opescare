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
    <div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif

{{-- System Health Banner --}}
@php
    $anyError = collect($health)->whereIn('status', ['error'])->count() > 0;
    $anyWarn  = collect($health)->whereIn('status', ['warning'])->count() > 0 || ($health['failed_jobs']['count'] ?? 0) > 0;
@endphp
@if($anyError)
<div class="banner banner--danger">
    <i data-lucide="alert-triangle"></i>
    <div><strong>System issues detected.</strong> Check System Health for details.</div>
    <div class="banner__spacer"></div>
    <a href="{{ route('portals.admin.cc.health') }}" class="btn btn-danger btn-sm">View Health</a>
</div>
@elseif($anyWarn)
<div class="banner banner--warning">
    <i data-lucide="alert-circle"></i>
    <div><strong>Warning:</strong> Some system checks need attention.</div>
    <div class="banner__spacer"></div>
    <a href="{{ route('portals.admin.cc.health') }}" class="btn btn-secondary btn-sm">View Health</a>
</div>
@endif

{{-- God Mode — Platform Management --}}
<div class="mb-6">
    <div class="section-head section-head--danger">
        <i data-lucide="zap"></i>
        <h2>God Mode — Platform Management</h2>
    </div>
    <div class="card-grid">
        @php $godCards = [
            ['Users',               'users',        '/portals/admin/users',         'Manage all platform users'],
            ['Facilities',          'building',     '/portals/admin/facilities',     'Hospitals, clinics & labs'],
            ['Patients',            'heart-pulse',  '/portals/admin/patients',       'Global patient registry'],
            ['Staff',               'user-check',   '/portals/admin/staff',          'Clinical & admin staff'],
            ['Financial',           'banknote',     '/portals/admin/financial',      'Billing, invoices & revenue'],
            ['Appointments',        'calendar',     '/portals/admin/appointments',   'Scheduling across facilities'],
            ['Support Tickets',     'headphones',   '/portals/admin/support',        'Help desk & issue queue'],
            ['CDSS Rules',          'activity',     '/portals/admin/cdss',           'Clinical decision support'],
            ['Roles & Permissions', 'shield',       '/portals/admin/roles',          'Access control & RBAC'],
            ['Organizations',       'landmark',     '/portals/admin/organizations',  'Tenant & org management'],
        ]; @endphp
        @foreach($godCards as [$title, $icon, $url, $desc])
        <a href="{{ $url }}" class="nav-card nav-card--danger">
            <div class="nav-card__head">
                <i data-lucide="{{ $icon }}"></i>
                <span class="nav-card__title">{{ $title }}</span>
            </div>
            <p class="nav-card__desc">{{ $desc }}</p>
        </a>
        @endforeach
    </div>
</div>

{{-- Quick nav cards --}}
<div class="card-grid mb-6">
    @php $cards = [
        ['Platform Settings', 'sliders-horizontal', route('portals.admin.cc.settings'),   'Manage system-wide configuration'],
        ['Feature Flags',     'toggle-right',       route('portals.admin.cc.feature_flags'),'Enable/disable product features'],
        ['Module Toggles',    'puzzle',             route('portals.admin.cc.modules'),     'Turn modules on/off per scope'],
        ['Maintenance',       'wrench',             route('portals.admin.cc.maintenance'), 'Schedule downtime windows'],
        ['System Health',     'activity',           route('portals.admin.cc.health'),      'Live platform health checks'],
        ['Admin Log',         'scroll-text',        route('portals.admin.cc.audit'),       'Track all admin actions'],
    ]; @endphp
    @foreach($cards as [$title, $icon, $url, $desc])
    <a href="{{ $url }}" class="nav-card">
        <div class="nav-card__head">
            <i data-lucide="{{ $icon }}"></i>
            <span class="nav-card__title">{{ $title }}</span>
        </div>
        <p class="nav-card__desc">{{ $desc }}</p>
    </a>
    @endforeach
</div>

{{-- Recent Admin Actions --}}
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="scroll-text"></i> Recent admin actions</h3>
        <a href="{{ route('portals.admin.cc.audit') }}" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="panel-body panel-body--flush">
        @if($actions->count() === 0)
            <div class="td-muted empty-cell">No actions recorded yet.</div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Action</th><th>Resource</th><th>Actor</th><th>When</th>
                </tr></thead>
                <tbody>
                    @foreach($actions as $a)
                    <tr>
                        <td data-label="Action"><span class="code-token">{{ $a->action }}</span></td>
                        <td data-label="Resource"><span class="badge badge-neutral badge-sm">{{ $a->resource_type ?? '—' }}</span></td>
                        <td data-label="Actor">{{ $a->actor_id }}</td>
                        <td data-label="When" class="td-muted">{{ \Carbon\Carbon::parse($a->occurred_at)->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
