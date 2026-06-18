@extends('layouts.portal')
@section('title', ($client->name ?? __('public.developer_portal.lbl_unnamed_app', [], app()->getLocale())) . ' — ' . __('public.developer_portal.lbl_view', [], app()->getLocale()))
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection
@php $l = app()->getLocale(); @endphp

@section('content')

    <div class="breadcrumb">
        <a href="{{ route('portals.developer.apps') }}">{{ __('public.developer_portal.lnk_my_apps', [], $l) ?: 'My apps' }}</a>
        <i data-lucide="chevron-right"></i>
        <span>{{ $client->name ?? __('public.developer_portal.lbl_unnamed_app', [], $l) ?: 'Unnamed App' }}</span>
    </div>

    <div class="entity-head">
        <div class="entity-head__icon"><i data-lucide="plug"></i></div>
        <div>
            <h2 class="entity-head__title">{{ $client->name ?? __('public.developer_portal.lbl_unnamed_app', [], $l) ?: 'Unnamed App' }}</h2>
            <div class="entity-head__sub">
                <span class="badge {{ ($client->environment ?? 'sandbox') === 'production' ? 'badge-success' : 'badge-info' }}">{{ ucfirst($client->environment ?? 'sandbox') }}</span>
                <span class="badge {{ ($client->status ?? 'active') === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($client->status ?? 'active') }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

    @if(session('new_client_secret'))
    <div class="panel mb-6">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="key"></i> {{ __('public.developer_portal.panel_save_credentials', [], $l) ?: 'Save your credentials — shown only once' }}</h3></div>
        <div class="panel-body">
            @if(session('new_client_id'))<div class="code-block mb-6"><strong>{{ __('public.developer_portal.lbl_client_id', [], $l) ?: 'Client ID' }}:</strong> <span class="code-token" data-copy="{{ session('new_client_id') }}">{{ session('new_client_id') }}</span></div>@endif
            <div class="code-block"><strong>{{ __('public.developer_portal.lbl_client_secret', [], $l) ?: 'Client Secret' }}:</strong> <span class="code-token" data-copy="{{ session('new_client_secret') }}">{{ session('new_client_secret') }}</span></div>
        </div>
    </div>
    @endif

    {{-- App management: rotate secret / enable-disable --}}
    <div class="panel mb-6">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="settings"></i> {{ __('public.developer_portal.panel_manage', [], $l) ?: 'Manage app' }}</h3></div>
        <div class="panel-body" style="display:flex;gap:.6rem;flex-wrap:wrap;">
            <form method="POST" action="{{ route('portals.developer.apps.rotate', $client->id) }}"
                  onsubmit="return confirm('{{ __('public.developer_portal.rotate_confirm', [], $l) ?: 'Rotate the secret? The old secret stops working immediately.' }}');">
                @csrf
                <button type="submit" class="btn btn-secondary"><i data-lucide="refresh-cw"></i> {{ __('public.developer_portal.btn_rotate', [], $l) ?: 'Rotate secret' }}</button>
            </form>
            <form method="POST" action="{{ route('portals.developer.apps.toggle', $client->id) }}"
                  onsubmit="return confirm('{{ __('public.developer_portal.toggle_confirm', [], $l) ?: 'Change this app’s status?' }}');">
                @csrf
                @if(($client->status ?? 'active') === 'active')
                <button type="submit" class="btn btn-ghost btn-danger"><i data-lucide="power"></i> {{ __('public.developer_portal.btn_disable', [], $l) ?: 'Disable app' }}</button>
                @else
                <button type="submit" class="btn btn-primary"><i data-lucide="power"></i> {{ __('public.developer_portal.btn_enable', [], $l) ?: 'Enable app' }}</button>
                @endif
            </form>
        </div>
    </div>

    <div class="field-grid mb-6">

        {{-- Credentials & Config --}}
        <div>
            <div class="panel mb-6">
                <div class="panel-header"><h3 class="panel-title"><i data-lucide="key"></i> {{ __('public.developer_portal.panel_credentials', [], $l) ?: 'Credentials' }}</h3></div>
                <div class="panel-body">
                    <table class="kv-table">
                        <tr><th>{{ __('public.developer_portal.lbl_client_id', [], $l) ?: 'Client ID' }}</th><td><span class="code-token" data-copy="{{ $client->client_id }}">{{ $client->client_id }}</span></td></tr>
                        <tr><th>{{ __('public.developer_portal.lbl_secret', [], $l) ?: 'Secret' }}</th><td class="td-muted mono">{{ __('public.developer_portal.lbl_secret_masked', [], $l) ?: '•••••••••••••••• (shown once at creation)' }}</td></tr>
                        <tr><th>{{ __('public.developer_portal.lbl_environment', [], $l) ?: 'Environment' }}</th><td>{{ ucfirst($client->environment ?? 'sandbox') }}</td></tr>
                        <tr><th>{{ __('public.developer_portal.lbl_scopes', [], $l) ?: 'Scopes' }}</th><td>
                            @foreach(json_decode($client->scopes ?? '[]', true) ?? [] as $scope)
                            <span class="code-token">{{ $scope }}</span>
                            @endforeach
                        </td></tr>
                        <tr><th>{{ __('public.developer_portal.lbl_created', [], $l) ?: 'Created' }}</th><td class="td-muted">{{ $client->created_at->format('d M Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Integration Certification --}}
            @if($certification)
            <div class="panel">
                <div class="panel-header"><h3 class="panel-title"><i data-lucide="award"></i> {{ __('public.developer_portal.panel_integration_cert', [], $l) ?: 'Integration certification' }}</h3></div>
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
                            <div class="td-muted">{{ __('public.developer_portal.lbl_cert_in_progress', [], $l) ?: 'Certification in progress' }}</div>
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
                <div class="panel-header"><h3 class="panel-title"><i data-lucide="bar-chart-3"></i> {{ __('public.developer_portal.panel_api_usage', [], $l) ?: 'API usage (30 days)' }}</h3></div>
                @if(empty($usageSummary))
                <div class="panel-body empty-state"><p>{{ __('public.developer_portal.no_usage_yet', [], $l) ?: 'No usage recorded yet in the last 30 days.' }}</p></div>
                @else
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr>
                            <th>{{ __('public.developer_portal.col_endpoint_group', [], $l) ?: 'Endpoint group' }}</th>
                            <th>{{ __('public.developer_portal.col_requests', [], $l) ?: 'Requests' }}</th>
                            <th>{{ __('public.developer_portal.col_errors', [], $l) ?: 'Errors' }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach($usageSummary as $group => $stats)
                        <tr>
                            <td data-label="{{ __('public.developer_portal.col_endpoint_group', [], $l) ?: 'Endpoint group' }}" class="mono">{{ $group }}</td>
                            <td data-label="{{ __('public.developer_portal.col_requests', [], $l) ?: 'Requests' }}">{{ number_format($stats['total_requests']) }}</td>
                            <td data-label="{{ __('public.developer_portal.col_errors', [], $l) ?: 'Errors' }}">
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
                    <h3 class="panel-title"><i data-lucide="webhook"></i> {{ __('public.developer_portal.panel_webhook_subs', [], $l) ?: 'Webhook subscriptions' }}</h3>
                    <a href="{{ route('portals.developer.webhook_deliveries', $client->id) }}" class="btn btn-ghost btn-sm">{{ __('public.developer_portal.btn_delivery_logs', [], $l) ?: 'Delivery logs' }}</a>
                </div>
                @if($webhooks->isEmpty())
                <div class="panel-body empty-state"><p>{{ __('public.developer_portal.no_webhook_subs', [], $l) ?: 'No webhook subscriptions. Use the API to create subscriptions.' }}</p></div>
                @else
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr>
                            <th>{{ __('public.developer_portal.col_endpoint', [], $l) ?: 'Endpoint' }}</th>
                            <th>{{ __('public.developer_portal.col_events', [], $l) ?: 'Events' }}</th>
                            <th>{{ __('public.portal.col_status', [], $l) ?: 'Status' }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach($webhooks as $wh)
                        <tr>
                            <td data-label="{{ __('public.developer_portal.col_endpoint', [], $l) ?: 'Endpoint' }}" class="mono">{{ $wh->callback_url }}</td>
                            <td data-label="{{ __('public.developer_portal.col_events', [], $l) ?: 'Events' }}" class="td-muted">{{ count((array)$wh->subscribed_events) }} {{ __('public.developer_portal.col_events', [], $l) ?: 'events' }}</td>
                            <td data-label="{{ __('public.portal.col_status', [], $l) ?: 'Status' }}"><span class="badge {{ $wh->status === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ $wh->status }}</span></td>
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
