@extends('layouts.portal')
@section('title', 'Webhook Delivery Logs')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.developer.apps.show', $client->id) }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← {{ $client->name ?? 'App' }}</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Webhook Delivery Logs</h1>
        </div>
    </div>

    <div class="portal-card">
        @if($deliveries->isEmpty())
        <div class="portal-card__body" style="padding:30px;text-align:center;color:#9ca3af;font-size:0.85rem;">
            No webhook deliveries recorded yet.
        </div>
        @else
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table" style="font-size:0.81rem;">
                <thead><tr>
                    <th>Event</th><th>Type</th><th>Attempts</th><th>HTTP</th><th>Status</th><th>Delivered At</th>
                </tr></thead>
                <tbody>
                @foreach($deliveries as $log)
                <tr>
                    <td style="font-family:monospace;font-size:0.76rem;color:#7c3aed;">{{ Str::limit($log->event_id, 20) }}</td>
                    <td style="font-family:monospace;font-size:0.76rem;">{{ $log->event_type }}</td>
                    <td style="text-align:center;">{{ $log->attempts ?? $log->retry_count ?? 0 }}</td>
                    <td style="text-align:center;">
                        @if($log->http_status_code)
                        <span style="color:{{ $log->http_status_code >= 200 && $log->http_status_code < 300 ? '#16a34a' : '#dc2626' }};">
                            {{ $log->http_status_code }}
                        </span>
                        @else
                        <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td><span class="{{ $log->statusBadgeClass() }}" style="font-size:0.68rem;">{{ ucfirst($log->status) }}</span></td>
                    <td style="color:#9ca3af;font-size:0.78rem;">{{ $log->delivered_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px;">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>

@endsection
