@extends('layouts.portal')
@section('title', __('public.developer_portal.page_analytics', [], app()->getLocale()) ?: 'API Usage Analytics')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection
@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-head">
    <h2>{{ __('public.developer_portal.nav_analytics', [], $l) ?: 'API Analytics' }}</h2>
    <p class="portal-page-subtitle">{{ __('public.developer_portal.analytics_subtitle', [], $l) ?: 'Request volume, error rates, and response times across your apps — last 30 days.' }}</p>
    <div class="page-head__spacer"></div>
    <form method="GET" class="filter-bar filter-bar--flush">
        <select name="period" class="filter-select" aria-label="Period" onchange="this.form.submit()">
            <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="hourly" {{ $period === 'hourly' ? 'selected' : '' }}>Hourly</option>
            <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
        </select>
    </form>
</div>

{{-- Totals --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__label">{{ __('public.developer_portal.stat_total_requests', [], $l) ?: 'Total requests' }}</div>
        <div class="stat-card__value">{{ number_format($totals['requests']) }}</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__label">{{ __('public.developer_portal.stat_total_errors', [], $l) ?: 'Total errors' }}</div>
        <div class="stat-card__value">{{ number_format($totals['errors']) }}</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__label">{{ __('public.developer_portal.stat_error_rate', [], $l) ?: 'Error rate' }}</div>
        <div class="stat-card__value">{{ $totals['requests'] > 0 ? round(($totals['errors'] / $totals['requests']) * 100, 1) : 0 }}%</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__label">{{ __('public.developer_portal.stat_avg_response', [], $l) ?: 'Avg response' }}</div>
        <div class="stat-card__value">{{ round($totals['avg_ms']) }}ms</div>
    </div>
    <div class="stat-card stat-card--teal">
        <div class="stat-card__label">{{ __('public.developer_portal.stat_active_apps', [], $l) ?: 'Active apps' }}</div>
        <div class="stat-card__value">{{ $clients->count() }}</div>
    </div>
</div>

@if($metrics->isEmpty())
<div class="alert alert-info mb-6">
    <i data-lucide="info"></i>
    <div>No usage data yet for the selected period. Make your first API call to see metrics here.</div>
</div>
@endif

<div class="field-grid mb-6">

    {{-- Top Endpoints --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="list"></i> Top endpoints</h3></div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>Endpoint</th><th>Requests</th><th>Errors</th><th>Avg ms</th></tr>
                </thead>
                <tbody>
                    @forelse($byEndpoint as $row)
                    <tr>
                        <td data-label="Endpoint" class="mono">{{ $row['endpoint'] }}</td>
                        <td data-label="Requests" class="td-strong">{{ number_format($row['requests']) }}</td>
                        <td data-label="Errors">
                            @if($row['errors'] > 0)<span class="badge badge-danger">{{ number_format($row['errors']) }}</span>
                            @else<span class="td-muted">0</span>@endif
                        </td>
                        <td data-label="Avg ms" class="td-muted">{{ $row['avg_ms'] }}ms</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="td-muted empty-cell">No data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Your Apps --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="cpu"></i> Your apps</h3>
            <a href="{{ route('portals.developer.apps') }}" class="btn btn-secondary btn-sm">Manage</a>
        </div>
        @if($clients->isEmpty())
        <div class="panel-body empty-state">
            <p>No apps yet. <a href="{{ route('portals.developer.apps.create') }}">Create your first app</a>.</p>
        </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>App</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($clients as $client)
                <tr>
                    <td data-label="App">
                        <span class="td-strong">{{ $client->name }}</span>
                        <div class="mono">{{ $client->client_id }}</div>
                    </td>
                    <td data-label="Status">
                        <span class="badge badge-{{ $client->status === 'active' ? 'success' : ($client->status === 'pending' ? 'warning' : 'neutral') }}">{{ ucfirst($client->status) }}</span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

{{-- Recent metrics table --}}
@if($metrics->isNotEmpty())
<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="history"></i> Recent metric records</h3></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Endpoint</th><th>Method</th><th>Period start</th><th>Requests</th><th>Errors</th><th>Avg ms</th></tr>
            </thead>
            <tbody>
                @foreach($metrics->take(20) as $m)
                <tr>
                    <td data-label="Endpoint" class="mono">{{ $m->endpoint }}</td>
                    <td data-label="Method"><span class="badge badge-neutral">{{ $m->method }}</span></td>
                    <td data-label="Period start" class="td-muted">{{ $m->period_start?->format('d M Y H:i') }}</td>
                    <td data-label="Requests">{{ number_format($m->request_count) }}</td>
                    <td data-label="Errors">
                        @if($m->error_count > 0)<span class="badge badge-danger">{{ $m->error_count }}</span>
                        @else<span class="td-muted">0</span>@endif
                    </td>
                    <td data-label="Avg ms" class="td-muted">{{ round($m->avg_response_ms) }}ms</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
