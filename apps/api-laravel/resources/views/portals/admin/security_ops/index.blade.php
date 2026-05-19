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
<div style="background:rgba(220,38,38,.12);border:2px solid rgba(220,38,38,.35);border-radius:var(--p-radius);padding:1rem;margin-bottom:1rem;display:flex;gap:.75rem;align-items:center;">
    <i data-lucide="shield-alert" style="width:20px;height:20px;color:var(--p-danger);flex-shrink:0;"></i>
    <div>
        <strong style="color:var(--p-danger);">{{ $stats['critical_incidents'] }} critical incident{{ $stats['critical_incidents'] > 1 ? 's' : '' }} require attention.</strong>
    </div>
    <a href="{{ route('portals.admin.security.incidents', ['severity' => 'critical', 'status' => 'open']) }}"
       class="btn btn-danger btn-sm" style="margin-left:auto;">View Now</a>
</div>
@endif

{{-- KPI cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;">
    @php $kpis = [
        ['Open Incidents',       $stats['open_incidents'],     'file-warning', $stats['critical_incidents'] > 0 ? 'var(--p-danger)' : 'var(--p-primary)', route('portals.admin.security.incidents')],
        ['Emergency Accesses (7d)', $stats['emergency_accesses'], 'siren',     'var(--p-warning)', route('portals.admin.security.emergency_access')],
        ['Audit Events Today',   $stats['audit_events_today'], 'search-code', 'var(--p-text-muted)', route('portals.admin.security.audit_explorer')],
        ['Critical Open',        $stats['critical_incidents'], 'skull',       'var(--p-danger)', route('portals.admin.security.incidents', ['severity'=>'critical'])],
    ]; @endphp
    @foreach($kpis as [$label, $count, $icon, $color, $url])
    <a href="{{ $url }}" style="text-decoration:none;">
        <div class="panel" style="padding:1.25rem;cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;">
                <i data-lucide="{{ $icon }}" style="width:18px;height:18px;color:{{ $color }};flex-shrink:0;"></i>
                <span style="font-size:.82rem;color:var(--p-text-muted);">{{ $label }}</span>
            </div>
            <div style="font-size:1.75rem;font-weight:700;color:{{ $color }};">{{ $count }}</div>
        </div>
    </a>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;flex-wrap:wrap;">

{{-- Recent Incidents --}}
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;font-size:.9rem;display:flex;align-items:center;gap:.4rem;">
            <i data-lucide="file-warning" style="width:14px;height:14px;color:var(--p-danger);"></i> Recent Incidents
        </span>
        <a href="{{ route('portals.admin.security.incidents') }}" class="btn btn-ghost btn-xs">View All</a>
    </div>
    <div class="panel-body" style="padding:0;">
        @if($recentIncidents->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:var(--p-text-muted);font-size:.82rem;">No incidents recorded.</div>
        @else
        <table class="data-table">
            <thead><tr><th>Type</th><th>Severity</th><th>Status</th><th>When</th></tr></thead>
            <tbody>
                @foreach($recentIncidents as $inc)
                @php $sevBadge = match($inc->severity) { 'critical'=>'badge-danger', 'high'=>'badge-warning', 'medium'=>'badge-primary', default=>'badge-neutral' }; @endphp
                <tr>
                    <td style="font-size:.8rem;">{{ $inc->incident_type }}</td>
                    <td><span class="badge {{ $sevBadge }}" style="font-size:.7rem;">{{ ucfirst($inc->severity) }}</span></td>
                    <td><span class="badge badge-neutral" style="font-size:.7rem;">{{ ucfirst($inc->status) }}</span></td>
                    <td style="font-size:.75rem;color:var(--p-text-muted);">{{ \Carbon\Carbon::parse($inc->detected_at)->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- Recent Emergency Access --}}
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;font-size:.9rem;display:flex;align-items:center;gap:.4rem;">
            <i data-lucide="siren" style="width:14px;height:14px;color:var(--p-warning);"></i> Emergency Access Events
        </span>
        <a href="{{ route('portals.admin.security.emergency_access') }}" class="btn btn-ghost btn-xs">View All</a>
    </div>
    <div class="panel-body" style="padding:0;">
        @if($recentEmergency->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:var(--p-text-muted);font-size:.82rem;">No emergency access events.</div>
        @else
        <table class="data-table">
            <thead><tr><th>Patient</th><th>Provider</th><th>Reason</th><th>When</th></tr></thead>
            <tbody>
                @foreach($recentEmergency as $ev)
                <tr>
                    <td style="font-size:.78rem;font-weight:500;">{{ $ev->patient?->health_id ?? substr($ev->patient_id,0,8).'…' }}</td>
                    <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $ev->provider_id ? substr($ev->provider_id,0,8).'…' : '—' }}</td>
                    <td style="font-size:.78rem;">{{ Str::limit($ev->reason,40) }}</td>
                    <td style="font-size:.75rem;color:var(--p-text-muted);">{{ \Carbon\Carbon::parse($ev->created_at)->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

</div>
@endsection
