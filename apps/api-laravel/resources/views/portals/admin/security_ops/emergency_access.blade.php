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
<form method="GET" action="{{ route('portals.admin.security.emergency_access') }}"
      style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;">
    <input type="text" name="provider_id" value="{{ request('provider_id') }}"
        class="form-control form-control-sm" style="max-width:200px;" placeholder="Filter by Provider ID">
    <input type="text" name="facility_id" value="{{ request('facility_id') }}"
        class="form-control form-control-sm" style="max-width:200px;" placeholder="Filter by Facility ID">
    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
    <a href="{{ route('portals.admin.security.emergency_access') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
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
                        <td style="font-weight:500;font-size:.82rem;">
                            {{ $ev->patient?->health_id ?? ($ev->patient_id ? substr($ev->patient_id,0,12).'…' : '—') }}
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">
                            <code style="font-size:.73rem;">{{ $ev->provider_id ? substr($ev->provider_id,0,12).'…' : '—' }}</code>
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">
                            <code style="font-size:.73rem;">{{ $ev->facility_id ? substr($ev->facility_id,0,12).'…' : '—' }}</code>
                        </td>
                        <td style="font-size:.8rem;">{{ Str::limit($ev->reason ?? '—', 60) }}</td>
                        <td style="font-size:.78rem;">
                            @if($ev->records_viewed)
                                <span class="badge badge-warning" style="font-size:.7rem;">
                                    {{ is_array($ev->records_viewed) ? count($ev->records_viewed) : '?' }} records
                                </span>
                            @else
                                <span style="color:var(--p-text-muted);">—</span>
                            @endif
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">
                            {{ \Carbon\Carbon::parse($ev->created_at)->format('M d, Y H:i') }}
                            <div style="font-size:.72rem;">{{ \Carbon\Carbon::parse($ev->created_at)->diffForHumans() }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--p-border);">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
