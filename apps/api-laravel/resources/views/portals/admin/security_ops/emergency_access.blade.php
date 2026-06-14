@extends('layouts.portal')
@section('title', 'Emergency Access Events')
@include('portals.admin.security_ops._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Emergency Access')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Emergency Access Events</h1>
        <p class="page-subtitle">All break-glass patient record access events requiring review.</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('portals.admin.security.emergency_access') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="provider_id" value="{{ request('provider_id') }}" placeholder="Filter by Provider ID" aria-label="Provider ID">
    </label>
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="facility_id" value="{{ request('facility_id') }}" placeholder="Filter by Facility ID" aria-label="Facility ID">
    </label>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.security.emergency_access') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body panel-body--flush">
        @if($events->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="shield-check"></i></div>
                <h3>No emergency access events</h3>
                <p>Break-glass patient record access events will appear here.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Patient</th><th>Provider</th><th>Facility</th><th>Reason</th><th>Records Viewed</th><th>Date/Time</th>
                </tr></thead>
                <tbody>
                    @foreach($events as $ev)
                    <tr>
                        <td data-label="Patient" class="td-strong">
                            {{ $ev->patient?->health_id ?? ($ev->patient_id ? substr($ev->patient_id,0,12).'…' : '—') }}
                        </td>
                        <td data-label="Provider" class="td-muted">
                            <span class="code-muted">{{ $ev->provider_id ? substr($ev->provider_id,0,12).'…' : '—' }}</span>
                        </td>
                        <td data-label="Facility" class="td-muted">
                            <span class="code-muted">{{ $ev->facility_id ? substr($ev->facility_id,0,12).'…' : '—' }}</span>
                        </td>
                        <td data-label="Reason">{{ Str::limit($ev->reason ?? '—', 60) }}</td>
                        <td data-label="Records Viewed">
                            @if($ev->records_viewed)
                                <span class="badge badge-warning badge-sm">
                                    {{ is_array($ev->records_viewed) ? count($ev->records_viewed) : '?' }} records
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Date/Time" class="td-muted">
                            {{ \Carbon\Carbon::parse($ev->created_at)->format('M d, Y H:i') }}
                            <div class="code-muted">{{ \Carbon\Carbon::parse($ev->created_at)->diffForHumans() }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
