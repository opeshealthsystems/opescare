@extends('layouts.portal')
@section('title', __('public.healthorg_portal.page_title', [], app()->getLocale()) ?: 'Public Health Reports')
@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="heart-handshake"></i>
    {{ __('public.healthorg_portal.role_badge', [], app()->getLocale()) ?: 'Health Org' }}
</div>
@endsection
@section('sidebar_user_role', __('public.healthorg_portal.role_label', [], app()->getLocale()) ?: 'Health Org Admin')
@section('sidebar_nav')
@include('portals.healthorg._sidebar')
@endsection
@section('breadcrumb_home', __('public.healthorg_portal.breadcrumb_home', [], app()->getLocale()) ?: 'Health Org Portal')
@section('breadcrumb_home_url', route('portals.healthorg.dashboard'))
@section('breadcrumb_section', __('public.healthorg_portal.breadcrumb_section_reports', [], app()->getLocale()) ?: 'Reports')
@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.healthorg_portal.page_heading_reports', [], $l) ?: 'Public Health Reports' }}</h1>
        <p class="page-subtitle">{{ __('public.healthorg_portal.page_subtitle_reports', [], $l) ?: 'Disease surveillance, outbreak notifications, and regulatory submissions.' }}</p>
    </div>
</div>

@if($reports->isEmpty())
<div class="alert alert-info">
    <i data-lucide="info"></i>
    <div>
        Public health reports are generated and submitted via the <strong>Public Health API</strong>
        (<code class="mono">POST /api/v1/public-health/reports/generate-drafts</code>).
        Once reports exist they will appear here. Use the
        <a href="{{ route('portals.developer.dashboard') }}">Developer Portal</a>
        to get API access credentials.
    </div>
</div>
@else
<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('public.healthorg_portal.col_report', [], $l) ?: 'Report' }}</th>
                    <th>{{ __('public.healthorg_portal.col_type', [], $l) ?: 'Type' }}</th>
                    <th>{{ __('public.healthorg_portal.col_period', [], $l) ?: 'Period' }}</th>
                    <th>{{ __('public.healthorg_portal.col_status', [], $l) ?: 'Status' }}</th>
                    <th>{{ __('public.healthorg_portal.col_created', [], $l) ?: 'Created' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td data-label="{{ __('public.healthorg_portal.col_report', [], $l) ?: 'Report' }}"><span class="td-strong">{{ $report->title ?? $report->id }}</span></td>
                    <td data-label="{{ __('public.healthorg_portal.col_type', [], $l) ?: 'Type' }}"><span class="td-muted">{{ $report->report_type ?? '—' }}</span></td>
                    <td data-label="{{ __('public.healthorg_portal.col_period', [], $l) ?: 'Period' }}"><span class="td-muted">{{ $report->period_start ?? '' }} – {{ $report->period_end ?? '' }}</span></td>
                    <td data-label="{{ __('public.healthorg_portal.col_status', [], $l) ?: 'Status' }}">
                        <span class="badge badge-{{ match($report->status ?? '') { 'submitted','approved' => 'success', 'draft' => 'warning', 'rejected' => 'danger', default => 'neutral' } }}">
                            {{ ucfirst($report->status ?? '—') }}
                        </span>
                    </td>
                    <td data-label="{{ __('public.healthorg_portal.col_created', [], $l) ?: 'Created' }}"><span class="td-muted">{{ isset($report->created_at) ? \Carbon\Carbon::parse($report->created_at)->format('d M Y') : '—' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
