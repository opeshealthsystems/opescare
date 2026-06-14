@extends('layouts.portal')
@section('title', 'Webhook Delivery Logs')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="breadcrumb">
        <a href="{{ route('portals.developer.apps.show', $client->id) }}">{{ $client->name ?? 'App' }}</a>
        <i data-lucide="chevron-right"></i>
        <span>Webhook delivery logs</span>
    </div>

    <div class="page-head">
        <h2>Webhook delivery logs</h2>
    </div>

    <div class="panel">
        @if($deliveries->isEmpty())
        <div class="empty-state">
            <i data-lucide="webhook" class="empty-state-icon"></i>
            <p>No webhook deliveries recorded yet.</p>
        </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Event</th><th>Type</th><th>Attempts</th><th>HTTP</th><th>Status</th><th>Delivered at</th>
                </tr></thead>
                <tbody>
                @foreach($deliveries as $log)
                <tr>
                    <td data-label="Event" class="mono">{{ Str::limit($log->event_id, 20) }}</td>
                    <td data-label="Type" class="mono">{{ $log->event_type }}</td>
                    <td data-label="Attempts">{{ $log->attempts ?? $log->retry_count ?? 0 }}</td>
                    <td data-label="HTTP">
                        @if($log->http_status_code)
                        <span class="badge {{ $log->http_status_code >= 200 && $log->http_status_code < 300 ? 'badge-success' : 'badge-danger' }}">{{ $log->http_status_code }}</span>
                        @else
                        <span class="td-muted">—</span>
                        @endif
                    </td>
                    <td data-label="Status"><span class="{{ $log->statusBadgeClass() }}">{{ ucfirst($log->status) }}</span></td>
                    <td data-label="Delivered at" class="td-muted">{{ $log->delivered_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="panel-body">{{ $deliveries->links() }}</div>
        @endif
    </div>

@endsection
