@extends('layouts.portal')
@section('title', 'Security Operations Center')
@include('portals.admin.security_ops._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Security Operations Center')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Security Operations Center</h1>
        <p class="page-subtitle">Monitor incidents, emergency access events, and platform audit trails.</p>
    </div>
</div>

{{-- Critical incidents banner --}}
@if($stats['critical_incidents'] > 0)
<div class="banner banner--danger">
    <i data-lucide="shield-alert"></i>
    <strong>{{ $stats['critical_incidents'] }} critical incident{{ $stats['critical_incidents'] > 1 ? 's' : '' }} require attention.</strong>
    <div class="banner__spacer"></div>
    <a href="{{ route('portals.admin.security.incidents', ['severity' => 'critical', 'status' => 'open']) }}"
       class="btn btn-danger btn-sm">View Now</a>
</div>
@endif

{{-- KPI cards --}}
<div class="stat-grid mb-6">
    @php $kpis = [
        ['Open Incidents',       $stats['open_incidents'],     'file-warning', $stats['critical_incidents'] > 0 ? 'danger' : 'primary', route('portals.admin.security.incidents')],
        ['Emergency Accesses (7d)', $stats['emergency_accesses'], 'siren',     'warning', route('portals.admin.security.emergency_access')],
        ['Audit Events Today',   $stats['audit_events_today'], 'search-code', '', route('portals.admin.security.audit_explorer')],
        ['Critical Open',        $stats['critical_incidents'], 'skull',       'danger', route('portals.admin.security.incidents', ['severity'=>'critical'])],
    ]; @endphp
    @foreach($kpis as [$label, $count, $icon, $variant, $url])
    <a href="{{ $url }}" class="stat-card {{ $variant ? 'stat-card--'.$variant : '' }}">
        <div class="stat-card__head">
            <i data-lucide="{{ $icon }}"></i>
            <span class="stat-card__label">{{ $label }}</span>
        </div>
        <div class="stat-card__value">{{ $count }}</div>
    </a>
    @endforeach
</div>

<div class="grid-2">

{{-- Recent Incidents --}}
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="file-warning"></i> Recent Incidents</h3>
        <a href="{{ route('portals.admin.security.incidents') }}" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="panel-body panel-body--flush">
        @if($recentIncidents->isEmpty())
            <div class="td-muted empty-cell">No incidents recorded.</div>
        @else
        <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Type</th><th>Severity</th><th>Status</th><th>When</th></tr></thead>
            <tbody>
                @foreach($recentIncidents as $inc)
                @php $sevBadge = match($inc->severity) { 'critical'=>'badge-danger', 'high'=>'badge-warning', 'medium'=>'badge-primary', default=>'badge-neutral' }; @endphp
                <tr>
                    <td data-label="Type">{{ $inc->incident_type }}</td>
                    <td data-label="Severity"><span class="badge {{ $sevBadge }} badge-sm">{{ ucfirst($inc->severity) }}</span></td>
                    <td data-label="Status"><span class="badge badge-neutral badge-sm">{{ ucfirst($inc->status) }}</span></td>
                    <td data-label="When" class="td-muted">{{ \Carbon\Carbon::parse($inc->detected_at)->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>

{{-- Recent Emergency Access --}}
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="siren"></i> Emergency Access Events</h3>
        <a href="{{ route('portals.admin.security.emergency_access') }}" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="panel-body panel-body--flush">
        @if($recentEmergency->isEmpty())
            <div class="td-muted empty-cell">No emergency access events.</div>
        @else
        <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Patient</th><th>Provider</th><th>Reason</th><th>When</th></tr></thead>
            <tbody>
                @foreach($recentEmergency as $ev)
                <tr>
                    <td data-label="Patient" class="td-strong">{{ $ev->patient?->health_id ?? substr($ev->patient_id,0,8).'…' }}</td>
                    <td data-label="Provider" class="td-muted">{{ $ev->provider_id ? substr($ev->provider_id,0,8).'…' : '—' }}</td>
                    <td data-label="Reason">{{ Str::limit($ev->reason,40) }}</td>
                    <td data-label="When" class="td-muted">{{ \Carbon\Carbon::parse($ev->created_at)->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>

</div>
@endsection
