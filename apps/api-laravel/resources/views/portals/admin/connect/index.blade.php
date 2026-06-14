@extends('layouts.portal')

@section('title', 'Connect Suite — Admin Portal')

@section('sidebar')
    @include('portals.admin.connect._sidebar')
@endsection

@section('content')

<div class="page-head">
    <h2><i data-lucide="plug-zap"></i> Connect Suite</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">Manage API integrations, SDK tokens, and webhook subscriptions</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- KPI Cards --}}
<div class="stat-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__head"><i data-lucide="app-window"></i></div>
        <div class="stat-card__value">{{ $stats['total_clients'] }}</div>
        <div class="stat-card__label">Total Clients</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle"></i></div>
        <div class="stat-card__value">{{ $stats['active_clients'] }}</div>
        <div class="stat-card__label">Active</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="clock"></i></div>
        <div class="stat-card__value">{{ $stats['pending_clients'] }}</div>
        <div class="stat-card__label">Pending Approval</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="flask-conical"></i></div>
        <div class="stat-card__value">{{ $stats['sandbox_clients'] }}</div>
        <div class="stat-card__label">Sandbox</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="key-round"></i></div>
        <div class="stat-card__value">{{ $stats['active_tokens'] }}</div>
        <div class="stat-card__label">Active Tokens</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="webhook"></i></div>
        <div class="stat-card__value">{{ $stats['active_webhooks'] }}</div>
        <div class="stat-card__label">Active Webhooks</div>
    </div>
</div>

{{-- Webhook Delivery Stats --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="activity"></i> Webhook Delivery Stats</h3></div>
    <div class="panel-body">
        @php $ws = $stats['webhook_stats']; @endphp
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-card__value">{{ $ws['total'] }}</div>
                <div class="stat-card__label">Total Deliveries</div>
            </div>
            <div class="stat-card stat-card--success">
                <div class="stat-card__value">{{ $ws['delivered'] }}</div>
                <div class="stat-card__label">Delivered</div>
            </div>
            <div class="stat-card stat-card--danger">
                <div class="stat-card__value">{{ $ws['failed'] }}</div>
                <div class="stat-card__label">Failed</div>
            </div>
            <div class="stat-card stat-card--warning">
                <div class="stat-card__value">{{ $ws['pending'] }}</div>
                <div class="stat-card__label">Pending</div>
            </div>
        </div>
    </div>
</div>

<div class="field-grid mb-6">

    {{-- Pending Approvals --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="clock"></i> Pending Approvals</h3>
            <a href="{{ route('portals.admin.connect.clients') }}?status=pending" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="panel-body">
            @forelse($pending as $client)
                <div class="list-row">
                    <span class="list-row__main">
                        <span>
                            <span class="td-strong">{{ $client->name }}</span><br>
                            <span class="td-muted">{{ $client->client_id }}</span>
                            @if($client->contact_email)<br><span class="td-muted">{{ $client->contact_email }}</span>@endif
                        </span>
                    </span>
                    <span class="row-actions">
                        <span class="badge badge-{{ $client->environment === 'sandbox' ? 'primary' : 'success' }}">{{ $client->environment }}</span>
                        <form method="POST" action="{{ route('portals.admin.connect.clients.action', $client->id) }}" class="inline-form">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </span>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
                    <p>No pending approvals</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Recent Clients --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="app-window"></i> Recent Clients</h3>
            <a href="{{ route('portals.admin.connect.clients') }}" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="panel-body">
            @forelse($recent as $client)
                <div class="list-row">
                    <span class="list-row__main">
                        <span>
                            <span class="td-strong">{{ $client->name }}</span><br>
                            <span class="code-token">{{ $client->client_id }}</span>
                        </span>
                    </span>
                    <span class="row-actions">
                        <span class="badge badge-{{ $client->status === 'active' ? 'success' : ($client->status === 'pending' ? 'warning' : 'danger') }}">{{ $client->status }}</span>
                        <span class="badge badge-{{ $client->environment === 'sandbox' ? 'primary' : 'teal' }}">{{ $client->environment }}</span>
                    </span>
                </div>
            @empty
                <div class="empty-state"><p>No clients yet</p></div>
            @endforelse
        </div>
    </div>

</div>

@endsection
