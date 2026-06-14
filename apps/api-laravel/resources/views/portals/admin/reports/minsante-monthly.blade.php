@extends('layouts.portal')
@section('title', 'MINSANTE Monthly Report')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'MINSANTE Monthly Report')

@section('content')
@php
    $period   = $report['period'] ?? [];
    $reg      = $report['patient_registration'] ?? [];
    $access   = $report['access_events'] ?? [];
    $consent  = $report['consent_management'] ?? [];
    $dsr      = $report['data_subject_rights'] ?? [];
    $platform = $report['platform'] ?? [];
    $periodLabel = isset($period['start'])
        ? \Carbon\Carbon::parse($period['start'])->translatedFormat('F Y')
        : (($period['month'] ?? '') . '/' . ($period['year'] ?? ''));
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">MINSANTE Digital Health Monthly Audit</h1>
        <p class="page-subtitle">Reporting period: {{ $periodLabel }}
            @if(isset($period['start']) && isset($period['end']))
                ({{ $period['start'] }} &ndash; {{ $period['end'] }})
            @endif
        </p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('portals.admin.reports.minsante-monthly.download', request()->only('month')) }}" class="btn btn-primary btn-sm">
            <i data-lucide="download" style="width:13px;height:13px;"></i> Download (JSON)
        </a>
    </div>
</div>

{{-- Regulatory context --}}
<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:1.5rem;font-size:.82rem;color:var(--p-text-muted);">
        <span><i data-lucide="shield-check" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>{{ $platform['regulation'] ?? 'Cameroon Law No. 2010/012' }}</span>
        <span><i data-lucide="landmark" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>{{ $platform['strategy'] ?? 'MINSANTE Digital Health Strategy' }}</span>
        <span><i data-lucide="map-pin" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>{{ $platform['country'] ?? 'CM' }}</span>
        <span><i data-lucide="clock" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>Generated {{ \Carbon\Carbon::parse($report['generated_at'] ?? now())->format('M d, Y H:i') }}</span>
    </div>
</div>

{{-- Summary metric cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
    @php
        $cards = [
            ['label' => 'New Registrations', 'value' => $reg['new_registrations'] ?? 0, 'icon' => 'user-plus'],
            ['label' => 'Newly Verified', 'value' => $reg['newly_verified'] ?? 0, 'icon' => 'badge-check'],
            ['label' => 'Total Active & Verified', 'value' => $reg['total_active_verified'] ?? 0, 'icon' => 'users'],
            ['label' => 'Expiring within 90 days', 'value' => $reg['expiry_pending_90_days'] ?? 0, 'icon' => 'calendar-clock'],
            ['label' => 'Total Access Events', 'value' => $access['total'] ?? 0, 'icon' => 'activity'],
            ['label' => 'Emergency Accesses', 'value' => $access['emergency_accesses'] ?? 0, 'icon' => 'siren'],
        ];
    @endphp
    @foreach($cards as $c)
        <div class="panel" style="padding:1.25rem;">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;">
                <i data-lucide="{{ $c['icon'] }}" style="width:18px;height:18px;color:var(--p-primary);flex-shrink:0;"></i>
                <span style="font-size:.78rem;color:var(--p-text-muted);">{{ $c['label'] }}</span>
            </div>
            <div style="font-size:1.6rem;font-weight:700;">{{ number_format($c['value']) }}</div>
        </div>
    @endforeach
</div>

{{-- Access events breakdown --}}
<div class="panel" style="margin-bottom:1.5rem;">
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--p-border);">
        <h3 style="margin:0;font-size:.95rem;">Access Events</h3>
    </div>
    <div class="panel-body">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <tbody>
                <tr><td style="padding:.5rem 0;">Successful</td><td style="text-align:right;font-weight:600;">{{ number_format($access['successful'] ?? 0) }}</td></tr>
                <tr><td style="padding:.5rem 0;">Denied</td><td style="text-align:right;font-weight:600;">{{ number_format($access['denied'] ?? 0) }}</td></tr>
                <tr><td style="padding:.5rem 0;">Emergency</td><td style="text-align:right;font-weight:600;">{{ number_format($access['emergency_accesses'] ?? 0) }}</td></tr>
            </tbody>
        </table>
        @php $byType = $access['by_access_type'] ?? []; @endphp
        @if(!empty($byType))
            <h4 style="margin:1.25rem 0 .5rem;font-size:.85rem;color:var(--p-text-muted);">By Access Type</h4>
            <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                <thead><tr><th style="text-align:left;padding:.4rem 0;border-bottom:1px solid var(--p-border);">Type</th><th style="text-align:right;padding:.4rem 0;border-bottom:1px solid var(--p-border);">Count</th></tr></thead>
                <tbody>
                @foreach($byType as $type => $cnt)
                    <tr><td style="padding:.4rem 0;">{{ ucwords(str_replace('_', ' ', $type)) }}</td><td style="text-align:right;">{{ number_format($cnt) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Data subject rights --}}
<div class="panel" style="margin-bottom:1.5rem;">
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--p-border);">
        <h3 style="margin:0;font-size:.95rem;">Data Subject Rights Requests</h3>
    </div>
    <div class="panel-body">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <tbody>
                <tr><td style="padding:.5rem 0;">Data Exports</td><td style="text-align:right;font-weight:600;">{{ number_format($dsr['data_exports'] ?? 0) }}</td></tr>
                <tr><td style="padding:.5rem 0;">Rectification Requests</td><td style="text-align:right;font-weight:600;">{{ number_format($dsr['rectification_requests'] ?? 0) }}</td></tr>
                <tr><td style="padding:.5rem 0;">Erasure Requests</td><td style="text-align:right;font-weight:600;">{{ number_format($dsr['erasure_requests'] ?? 0) }}</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Consent management --}}
<div class="panel" style="margin-bottom:1.5rem;">
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--p-border);">
        <h3 style="margin:0;font-size:.95rem;">Consent Management</h3>
    </div>
    <div class="panel-body">
        @if(!empty($consent))
            <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                <thead><tr><th style="text-align:left;padding:.4rem 0;border-bottom:1px solid var(--p-border);">Status</th><th style="text-align:right;padding:.4rem 0;border-bottom:1px solid var(--p-border);">Count</th></tr></thead>
                <tbody>
                @foreach($consent as $status => $cnt)
                    <tr><td style="padding:.4rem 0;">{{ ucwords(str_replace('_', ' ', $status)) }}</td><td style="text-align:right;">{{ number_format($cnt) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p style="margin:0;font-size:.85rem;color:var(--p-text-muted);">No consent activity recorded for this period.</p>
        @endif
    </div>
</div>

{{-- Compliance notes --}}
@if(!empty($report['compliance_notes']))
<div class="panel">
    <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--p-border);">
        <h3 style="margin:0;font-size:.95rem;">Compliance Notes</h3>
    </div>
    <div class="panel-body">
        <ul style="margin:0;padding-left:1.1rem;font-size:.82rem;color:var(--p-text-muted);line-height:1.7;">
            @foreach($report['compliance_notes'] as $note)
                <li>{{ $note }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
@endsection
