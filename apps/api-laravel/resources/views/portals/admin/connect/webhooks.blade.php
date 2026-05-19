@extends('layouts.portal')

@section('title', 'Webhooks — Connect Suite')

@section('sidebar')
    @include('portals.admin.connect._sidebar')
@endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="webhook" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;"></i>
                Webhooks
            </h1>
            <p class="portal-page-subtitle">Monitor webhook subscriptions and delivery activity</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--danger">{{ session('error') }}</div>
    @endif

    {{-- Delivery Stats --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#d1fae5;">
                <i data-lucide="check-circle" style="color:#059669;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['delivered'] }}</div>
                <div class="stat-card__label">Delivered</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;">
                <i data-lucide="x-circle" style="color:#dc2626;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['failed'] }}</div>
                <div class="stat-card__label">Failed</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fef3c7;">
                <i data-lucide="clock" style="color:#d97706;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['pending'] }}</div>
                <div class="stat-card__label">Pending</div>
            </div>
        </div>
    </div>

    {{-- Subscriptions --}}
    <div class="portal-card" style="margin-bottom:24px;">
        <div class="portal-card__header">
            <h2 class="portal-card__title">
                <i data-lucide="rss" style="width:16px;height:16px;"></i>
                Webhook Subscriptions
            </h2>
        </div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Client ID</th>
                        <th>Endpoint URL</th>
                        <th>Events</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td>
                                <code style="font-size:0.78rem;background:#f9fafb;padding:2px 6px;border-radius:4px;border:1px solid #e5e7eb;">
                                    {{ $sub->client_id }}
                                </code>
                            </td>
                            <td style="max-width:260px;">
                                <div style="font-size:0.82rem;color:#374151;word-break:break-all;" title="{{ $sub->endpoint_url }}">
                                    {{ Str::limit($sub->endpoint_url, 60) }}
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;flex-wrap:wrap;gap:3px;max-width:200px;">
                                    @foreach(array_slice($sub->events ?? [], 0, 3) as $event)
                                        <span style="font-size:0.7rem;background:#f0fdf4;color:#166534;padding:1px 5px;border-radius:8px;border:1px solid #bbf7d0;">{{ $event }}</span>
                                    @endforeach
                                    @if(count($sub->events ?? []) > 3)
                                        <span style="font-size:0.7rem;color:#9ca3af;">+{{ count($sub->events) - 3 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge--{{ $sub->status === 'active' ? 'success' : 'warning' }}">
                                    {{ $sub->status }}
                                </span>
                            </td>
                            <td style="font-size:0.82rem;color:#6b7280;">{{ $sub->created_at->format('d M Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('portals.admin.connect.webhooks.toggle', $sub->id) }}">
                                    @csrf
                                    <button class="btn btn--sm btn--{{ $sub->status === 'active' ? 'warning' : 'success' }}"
                                            title="{{ $sub->status === 'active' ? 'Pause' : 'Resume' }}">
                                        <i data-lucide="{{ $sub->status === 'active' ? 'pause' : 'play' }}" style="width:13px;height:13px;"></i>
                                        {{ $sub->status === 'active' ? 'Pause' : 'Resume' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">
                                <i data-lucide="webhook" style="width:36px;height:36px;display:block;margin:0 auto 10px;"></i>
                                No webhook subscriptions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="portal-card__footer">{{ $subscriptions->links() }}</div>
        @endif
    </div>

    {{-- Delivery Log --}}
    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title">
                <i data-lucide="activity" style="width:16px;height:16px;"></i>
                Recent Delivery Log
                <span style="font-size:0.75rem;font-weight:400;color:#9ca3af;margin-left:6px;">(Last 30)</span>
            </h2>
        </div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Endpoint</th>
                        <th>Status</th>
                        <th>HTTP</th>
                        <th>Attempts</th>
                        <th>Delivered At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveryLogs as $log)
                        <tr>
                            <td>
                                <span style="font-size:0.82rem;background:#f3f4f6;padding:2px 8px;border-radius:10px;font-family:monospace;">
                                    {{ $log->event_type ?? '—' }}
                                </span>
                            </td>
                            <td style="font-size:0.78rem;color:#6b7280;max-width:200px;">
                                {{ Str::limit($log->endpoint_url ?? '', 40) }}
                            </td>
                            <td>
                                @php
                                    $statusColor = match($log->status ?? '') {
                                        'delivered' => 'success',
                                        'failed'    => 'danger',
                                        default     => 'warning',
                                    };
                                @endphp
                                <span class="badge badge--{{ $statusColor }}">{{ $log->status ?? 'pending' }}</span>
                            </td>
                            <td>
                                @if($log->http_status_code ?? null)
                                    <span style="font-size:0.82rem;font-family:monospace;
                                        color:{{ ($log->http_status_code >= 200 && $log->http_status_code < 300) ? '#059669' : '#dc2626' }};">
                                        {{ $log->http_status_code }}
                                    </span>
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td style="font-size:0.82rem;text-align:center;">{{ $log->attempt_count ?? 1 }}</td>
                            <td style="font-size:0.78rem;color:#6b7280;">
                                {{ $log->delivered_at ? \Carbon\Carbon::parse($log->delivered_at)->diffForHumans() : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:32px;color:#9ca3af;">
                                No delivery logs yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
