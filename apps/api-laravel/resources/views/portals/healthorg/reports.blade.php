@extends('layouts.portal')

@section('title', 'Public Health Reports')

@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(245,158,11,.15);border-color:rgba(245,158,11,.4);color:#fbbf24;">
    <i data-lucide="heart-handshake" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    Health Org
</div>
@endsection
@section('sidebar_user_role', 'Health Org Admin')

@section('sidebar_nav')
@include('portals.healthorg._sidebar')
@endsection

@section('breadcrumb_home', 'Health Org Portal')
@section('breadcrumb_home_url', route('portals.healthorg.dashboard'))
@section('breadcrumb_section', 'Reports')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Public Health Reports</h1>
        <p class="page-subtitle">Disease surveillance, outbreak notifications, and regulatory submissions.</p>
    </div>
</div>

@if($reports->isEmpty())
<div class="auth-alert" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;">
    <i data-lucide="info"></i>
    <div>
        Public health reports are generated and submitted via the <strong>Public Health API</strong>
        (<code>POST /api/v1/public-health/reports/generate-drafts</code>).
        Once reports exist they will appear here. Use the
        <a href="{{ route('portals.developer.dashboard') }}" style="color:#1d4ed8;">Developer Portal</a>
        to get API access credentials.
    </div>
</div>
@else
<div class="card" style="overflow:hidden;">
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Report</th>
                    <th>Type</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td style="font-weight:600;">{{ $report->title ?? $report->id }}</td>
                    <td style="font-size:.85rem;color:#64748b;">{{ $report->report_type ?? '—' }}</td>
                    <td style="font-size:.83rem;color:#64748b;">{{ $report->period_start ?? '' }} – {{ $report->period_end ?? '' }}</td>
                    <td>
                        <span class="badge badge-{{ match($report->status ?? '') { 'submitted','approved' => 'success', 'draft' => 'warning', 'rejected' => 'danger', default => 'default' } }}">
                            {{ ucfirst($report->status ?? '—') }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;color:#64748b;">{{ isset($report->created_at) ? \Carbon\Carbon::parse($report->created_at)->format('d M Y') : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
