@extends('layouts.portal')
@section('title', 'MINSANTE Monthly Report')
@include('portals.admin.control_center._sidebar')
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

<div class="page-head">
    <h2>MINSANTE Digital Health Monthly Audit</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.reports.minsante-monthly.download', request()->only('month')) }}" class="btn btn-primary btn-sm">
        <i data-lucide="download"></i> Download (JSON)
    </a>
</div>
<p class="td-muted mb-6">Reporting period: {{ $periodLabel }}
    @if(isset($period['start']) && isset($period['end'])) ({{ $period['start'] }} &ndash; {{ $period['end'] }})@endif
</p>

{{-- Regulatory context --}}
<div class="panel mb-6">
    <div class="panel-body">
        <div class="filter-bar filter-bar--flush">
            <span class="td-muted"><i data-lucide="shield-check"></i> {{ $platform['regulation'] ?? 'Cameroon Law No. 2010/012' }}</span>
            <span class="td-muted"><i data-lucide="landmark"></i> {{ $platform['strategy'] ?? 'MINSANTE Digital Health Strategy' }}</span>
            <span class="td-muted"><i data-lucide="map-pin"></i> {{ $platform['country'] ?? 'CM' }}</span>
            <span class="td-muted"><i data-lucide="clock"></i> Generated {{ \Carbon\Carbon::parse($report['generated_at'] ?? now())->format('M d, Y H:i') }}</span>
        </div>
    </div>
</div>

{{-- Summary metric cards --}}
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
<div class="stat-grid mb-6">
    @foreach($cards as $c)
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="{{ $c['icon'] }}"></i> <span class="stat-card__label">{{ $c['label'] }}</span></div>
            <div class="stat-card__value">{{ number_format($c['value']) }}</div>
        </div>
    @endforeach
</div>

{{-- Access events breakdown --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title">Access Events</h3></div>
    <div class="panel-body">
        <table class="kv-table">
            <tr><td>Successful</td><td class="kv-strong">{{ number_format($access['successful'] ?? 0) }}</td></tr>
            <tr><td>Denied</td><td class="kv-strong">{{ number_format($access['denied'] ?? 0) }}</td></tr>
            <tr><td>Emergency</td><td class="kv-strong">{{ number_format($access['emergency_accesses'] ?? 0) }}</td></tr>
        </table>
        @php $byType = $access['by_access_type'] ?? []; @endphp
        @if(!empty($byType))
            <h4 class="panel-title mt-6 mb-6">By Access Type</h4>
            <table class="kv-table">
                @foreach($byType as $type => $cnt)
                    <tr><td>{{ ucwords(str_replace('_', ' ', $type)) }}</td><td class="kv-strong">{{ number_format($cnt) }}</td></tr>
                @endforeach
            </table>
        @endif
    </div>
</div>

{{-- Data subject rights --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title">Data Subject Rights Requests</h3></div>
    <div class="panel-body">
        <table class="kv-table">
            <tr><td>Data Exports</td><td class="kv-strong">{{ number_format($dsr['data_exports'] ?? 0) }}</td></tr>
            <tr><td>Rectification Requests</td><td class="kv-strong">{{ number_format($dsr['rectification_requests'] ?? 0) }}</td></tr>
            <tr><td>Erasure Requests</td><td class="kv-strong">{{ number_format($dsr['erasure_requests'] ?? 0) }}</td></tr>
        </table>
    </div>
</div>

{{-- Consent management --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title">Consent Management</h3></div>
    <div class="panel-body">
        @if(!empty($consent))
            <table class="kv-table">
                @foreach($consent as $status => $cnt)
                    <tr><td>{{ ucwords(str_replace('_', ' ', $status)) }}</td><td class="kv-strong">{{ number_format($cnt) }}</td></tr>
                @endforeach
            </table>
        @else
            <p class="td-muted">No consent activity recorded for this period.</p>
        @endif
    </div>
</div>

{{-- Compliance notes --}}
@if(!empty($report['compliance_notes']))
<div class="panel">
    <div class="panel-header"><h3 class="panel-title">Compliance Notes</h3></div>
    <div class="panel-body">
        <ul class="td-muted prose-list">
            @foreach($report['compliance_notes'] as $note)
                <li>{{ $note }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
@endsection
