@extends('layouts.portal')
@section('title', ($client->name ?? 'App') . ' — Details')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="breadcrumb">
        <a href="{{ route('portals.developer.apps') }}">My apps</a>
        <i data-lucide="chevron-right"></i>
        <span>{{ $client->name ?? 'Unnamed App' }}</span>
    </div>

    <div class="entity-head">
        <div class="entity-head__icon"><i data-lucide="plug"></i></div>
        <div>
            <h2 class="entity-head__title">{{ $client->name ?? 'Unnamed App' }}</h2>
            <div class="entity-head__sub">
                <span class="badge {{ ($client->environment ?? 'sandbox') === 'production' ? 'badge-success' : 'badge-info' }}">{{ ucfirst($client->environment ?? 'sandbox') }}</span>
                <span class="badge {{ ($client->status ?? 'active') === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($client->status ?? 'active') }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

    @if(session('new_client_secret'))
    <div class="panel mb-6">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="key"></i> Save your credentials — shown only once</h3></div>
        <div class="panel-body">
            <div class="code-block mb-6"><strong>Client ID:</strong> <span class="code-token" data-copy="{{ session('new_client_id') }}">{{ session('new_client_id') }}</span></div>
            <div class="code-block"><strong>Client Secret:</strong> <span class="code-token" data-copy="{{ session('new_client_secret') }}">{{ session('new_client_secret') }}</span></div>
        </div>
    </div>
    @endif

    <div class="field-grid mb-6">

        {{-- Credentials & Config --}}
        <div>
            <div class="panel mb-6">
                <div class="panel-header"><h3 class="panel-title"><i data-lucide="key"></i> Credentials</h3></div>
                <div class="panel-body">
                    <table class="kv-table">
                        <tr><th>Client ID</th><td><span class="code-token" data-copy="{{ $client->client_id }}">{{ $client->client_id }}</span></td></tr>
                        <tr><th>Secret</th><td class="td-muted mono">••••••••••••••••  (shown once at creation)</td></tr>
                        <tr><th>Environment</th><td>{{ ucfirst($client->environment ?? 'sandbox') }}</td></tr>
                        <tr><th>Scopes</th><td>
                            @foreach(json_decode($client->scopes ?? '[]', true) ?? [] as $scope)
                            <span class="code-token">{{ $scope }}</span>
                            @endforeach
                        </td></tr>
                        <tr><th>Created</th><td class="td-muted">{{ $client->created_at->format('d M Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Integration Certification --}}
            @if($certification)
            <div class="panel">
                <div class="panel-header"><h3 class="panel-title"><i data-lucide="award"></i> Integration certification</h3></div>
                <div class="panel-body">
                    <div class="entity-head">
                        @if($certification->badge)
                        <div class="entity-head__icon"><i data-lucide="{{ $certification->badge->levelIcon() }}" style="color:{{ $certification->badge->levelColor() }};"></i></div>
                        <div>
                            <div class="td-strong">{{ ucfirst($certification->badge->certification_level) }} certified</div>
                            <div class="mono">{{ $certification->badge->badge_code }}</div>
                        </div>
                        @else
                        <div>
                            <span class="{{ $certification->statusBadgeClass() }}">{{ ucfirst(str_replace('_',' ',$certification->status)) }}</span>
                            <div class="td-muted">Certification in progress</div>
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
            <div class="panel mb-6">
                <div class="panel-header"><h3 class="panel-title"><i data-lucide="bar-chart-3"></i> API usage (30 days)</h3></div>
                @if(empty($usageSummary))
                <div class="panel-body empty-state"><p>No usage recorded yet in the last 30 days.</p></div>
                @else
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr><th>Endpoint group</th><th>Requests</th><th>Errors</th></tr></thead>
                        <tbody>
                        @foreach($usageSummary as $group => $stats)
                        <tr>
                            <td data-label="Endpoint group" class="mono">{{ $group }}</td>
                            <td data-label="Requests">{{ number_format($stats['total_requests']) }}</td>
                            <td data-label="Errors">
                                @if($stats['total_errors'] > 0)<span class="badge badge-danger">{{ number_format($stats['total_errors']) }}</span>
                                @else<span class="badge badge-success">0</span>@endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Webhook Subscriptions --}}
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title"><i data-lucide="webhook"></i> Webhook subscriptions</h3>
                    <a href="{{ route('portals.developer.webhook_deliveries', $client->id) }}" class="btn btn-ghost btn-sm">Delivery logs</a>
                </div>
                @if($webhooks->isEmpty())
                <div class="panel-body empty-state"><p>No webhook subscriptions. Use the API to create subscriptions.</p></div>
                @else
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr><th>Endpoint</th><th>Events</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($webhooks as $wh)
                        <tr>
                            <td data-label="Endpoint" class="mono">{{ $wh->callback_url }}</td>
                            <td data-label="Events" class="td-muted">{{ count((array)$wh->subscribed_events) }} events</td>
                            <td data-label="Status"><span class="badge {{ $wh->status === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ $wh->status }}</span></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>
    </div>

@endsection
@section('scripts')
<script>
document.addEventListener('click', function(e){
    var t = e.target.closest('.code-token[data-copy]');
    if(!t) return;
    navigator.clipboard && navigator.clipboard.writeText(t.getAttribute('data-copy'));
    var prev = t.textContent; t.textContent = 'Copied'; setTimeout(function(){ t.textContent = prev; }, 1200);
});
</script>
@endsection
