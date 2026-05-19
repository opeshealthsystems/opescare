@extends('layouts.portal')
@section('title', ($client->name ?? 'App') . ' — Details')
@section('sidebar') @include('portals.developer._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.developer.apps') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← My Apps</a>
            <h1 class="portal-page-title" style="margin-top:4px;">{{ $client->name ?? 'Unnamed App' }}</h1>
            <p class="portal-page-subtitle" style="display:flex;gap:8px;align-items:center;">
                <span class="badge {{ ($client->environment ?? 'sandbox') === 'production' ? 'badge--success' : 'badge--info' }}" style="font-size:0.7rem;">
                    {{ ucfirst($client->environment ?? 'sandbox') }}
                </span>
                <span class="badge {{ ($client->status ?? 'active') === 'active' ? 'badge--success' : 'badge--neutral' }}" style="font-size:0.7rem;">
                    {{ ucfirst($client->status ?? 'active') }}
                </span>
            </p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;">✓ {{ session('success') }}</div>
    @endif

    @if(session('new_client_secret'))
    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px;margin-bottom:20px;">
        <div style="font-weight:700;color:#0369a1;margin-bottom:8px;">🔑 Save Your Credentials — Shown Only Once</div>
        <div style="background:#fff;border:1px solid #bae6fd;border-radius:6px;padding:10px;font-family:monospace;font-size:0.82rem;margin-bottom:6px;">
            <strong>Client ID:</strong> {{ session('new_client_id') }}
        </div>
        <div style="background:#fff;border:1px solid #bae6fd;border-radius:6px;padding:10px;font-family:monospace;font-size:0.82rem;">
            <strong>Client Secret:</strong> {{ session('new_client_secret') }}
        </div>
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- Credentials & Config --}}
        <div>
            <div class="portal-card" style="margin-bottom:16px;">
                <div class="portal-card__header"><h2 class="portal-card__title">Credentials</h2></div>
                <div class="portal-card__body" style="padding:16px 20px;font-size:0.84rem;">
                    <dl style="display:grid;grid-template-columns:auto 1fr;gap:6px 16px;">
                        <dt style="font-weight:600;color:#374151;">Client ID</dt>
                        <dd style="font-family:monospace;color:#7c3aed;font-size:0.8rem;word-break:break-all;">{{ $client->client_id }}</dd>
                        <dt style="font-weight:600;color:#374151;">Secret</dt>
                        <dd style="color:#9ca3af;font-size:0.8rem;">••••••••••••••••  (shown once at creation)</dd>
                        <dt style="font-weight:600;color:#374151;">Environment</dt>
                        <dd>{{ ucfirst($client->environment ?? 'sandbox') }}</dd>
                        <dt style="font-weight:600;color:#374151;">Scopes</dt>
                        <dd style="font-size:0.78rem;">
                            @foreach(json_decode($client->scopes ?? '[]', true) ?? [] as $scope)
                            <span style="display:inline-block;background:#f3f4f6;border-radius:4px;padding:1px 6px;margin:1px;font-family:monospace;">{{ $scope }}</span>
                            @endforeach
                        </dd>
                        <dt style="font-weight:600;color:#374151;">Created</dt>
                        <dd style="color:#6b7280;">{{ $client->created_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Integration Certification --}}
            @if($certification)
            <div class="portal-card" style="border-color:{{ $certification->badge ? '#bbf7d0' : '#e5e7eb' }};">
                <div class="portal-card__header"><h2 class="portal-card__title">Integration Certification</h2></div>
                <div class="portal-card__body" style="padding:14px 20px;font-size:0.84rem;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        @if($certification->badge)
                        <div style="font-size:2rem;">{{ $certification->badge->levelIcon() }}</div>
                        <div>
                            <div style="font-weight:700;text-transform:capitalize;">{{ $certification->badge->certification_level }} Certified</div>
                            <div style="font-family:monospace;color:#7c3aed;font-size:0.78rem;">{{ $certification->badge->badge_code }}</div>
                        </div>
                        @else
                        <div>
                            <span class="{{ $certification->statusBadgeClass() }}" style="font-size:0.75rem;">{{ ucfirst(str_replace('_',' ',$certification->status)) }}</span>
                            <div style="font-size:0.8rem;color:#6b7280;margin-top:4px;">Certification in progress</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Usage & Webhooks --}}
        <div>

            {{-- 30-day usage --}}
            <div class="portal-card" style="margin-bottom:16px;">
                <div class="portal-card__header"><h2 class="portal-card__title">API Usage (30 days)</h2></div>
                @if(empty($usageSummary))
                <div class="portal-card__body" style="padding:16px;color:#9ca3af;font-size:0.83rem;text-align:center;">No usage recorded yet in the last 30 days.</div>
                @else
                <div class="portal-card__body" style="padding:0;">
                    <table class="portal-table" style="font-size:0.81rem;">
                        <thead><tr><th>Endpoint Group</th><th>Requests</th><th>Errors</th></tr></thead>
                        <tbody>
                        @foreach($usageSummary as $group => $stats)
                        <tr>
                            <td style="font-family:monospace;">{{ $group }}</td>
                            <td>{{ number_format($stats['total_requests']) }}</td>
                            <td style="color:{{ $stats['total_errors'] > 0 ? '#dc2626' : '#16a34a' }};">{{ number_format($stats['total_errors']) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Webhook Subscriptions --}}
            <div class="portal-card">
                <div class="portal-card__header" style="display:flex;justify-content:space-between;align-items:center;">
                    <h2 class="portal-card__title">Webhook Subscriptions</h2>
                    <a href="{{ route('portals.developer.webhook_deliveries', $client->id) }}" style="font-size:0.78rem;color:#7c3aed;">Delivery Logs</a>
                </div>
                @if($webhooks->isEmpty())
                <div class="portal-card__body" style="padding:14px;color:#9ca3af;font-size:0.83rem;">No webhook subscriptions. Use the API to create subscriptions.</div>
                @else
                <div class="portal-card__body" style="padding:0;">
                    <table class="portal-table" style="font-size:0.8rem;">
                        <thead><tr><th>Endpoint</th><th>Events</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($webhooks as $wh)
                        <tr>
                            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $wh->callback_url }}</td>
                            <td style="color:#9ca3af;font-size:0.75rem;">{{ count((array)$wh->subscribed_events) }} events</td>
                            <td><span class="badge {{ $wh->status === 'active' ? 'badge--success' : 'badge--neutral' }}" style="font-size:0.68rem;">{{ $wh->status }}</span></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection
