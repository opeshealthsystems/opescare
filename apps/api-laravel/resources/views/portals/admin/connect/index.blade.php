@extends('layouts.portal')

@section('title', 'Connect Suite — Admin Portal')

@section('sidebar')
    @include('portals.admin.connect._sidebar')
@endsection

@section('content')
<div class="portal-content">

    {{-- Header --}}
    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="plug-zap" style="width:24px;height:24px;vertical-align:middle;margin-right:8px;color:#6366f1;"></i>
                Connect Suite
            </h1>
            <p class="portal-page-subtitle">Manage API integrations, SDK tokens, and webhook subscriptions</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--danger">{{ session('error') }}</div>
    @endif

    {{-- KPI Cards --}}
    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px;margin-bottom:28px;">

        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;">
                <i data-lucide="app-window" style="color:#6366f1;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['total_clients'] }}</div>
                <div class="stat-card__label">Total Clients</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon" style="background:#d1fae5;">
                <i data-lucide="check-circle" style="color:#059669;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['active_clients'] }}</div>
                <div class="stat-card__label">Active</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fef3c7;">
                <i data-lucide="clock" style="color:#d97706;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['pending_clients'] }}</div>
                <div class="stat-card__label">Pending Approval</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon" style="background:#e0f2fe;">
                <i data-lucide="flask-conical" style="color:#0284c7;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['sandbox_clients'] }}</div>
                <div class="stat-card__label">Sandbox</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f3e8ff;">
                <i data-lucide="key-round" style="color:#7c3aed;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['active_tokens'] }}</div>
                <div class="stat-card__label">Active Tokens</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card__icon" style="background:#dcfce7;">
                <i data-lucide="webhook" style="color:#16a34a;"></i>
            </div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $stats['active_webhooks'] }}</div>
                <div class="stat-card__label">Active Webhooks</div>
            </div>
        </div>

    </div>

    {{-- Webhook Delivery Stats --}}
    <div class="portal-card" style="margin-bottom:24px;">
        <div class="portal-card__header">
            <h2 class="portal-card__title">
                <i data-lucide="activity" style="width:16px;height:16px;"></i>
                Webhook Delivery Stats
            </h2>
        </div>
        <div class="portal-card__body">
            <div style="display:flex;gap:32px;flex-wrap:wrap;">
                @php $ws = $stats['webhook_stats']; @endphp
                <div style="text-align:center;">
                    <div style="font-size:2rem;font-weight:700;color:#374151;">{{ $ws['total'] }}</div>
                    <div style="font-size:0.78rem;color:#6b7280;">Total Deliveries</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:2rem;font-weight:700;color:#059669;">{{ $ws['delivered'] }}</div>
                    <div style="font-size:0.78rem;color:#6b7280;">Delivered</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:2rem;font-weight:700;color:#dc2626;">{{ $ws['failed'] }}</div>
                    <div style="font-size:0.78rem;color:#6b7280;">Failed</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:2rem;font-weight:700;color:#d97706;">{{ $ws['pending'] }}</div>
                    <div style="font-size:0.78rem;color:#6b7280;">Pending</div>
                </div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;flex-wrap:wrap;">

        {{-- Pending Approvals --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title">
                    <i data-lucide="clock" style="width:16px;height:16px;color:#d97706;"></i>
                    Pending Approvals
                </h2>
                <a href="{{ route('portals.admin.connect.clients') }}?status=pending" class="btn btn--sm btn--outline">View All</a>
            </div>
            <div class="portal-card__body" style="padding:0;">
                @forelse($pending as $client)
                    <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;">{{ $client->name }}</div>
                            <div style="font-size:0.78rem;color:#6b7280;">{{ $client->client_id }}</div>
                            @if($client->contact_email)
                                <div style="font-size:0.78rem;color:#6b7280;">{{ $client->contact_email }}</div>
                            @endif
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <span class="badge badge--{{ $client->environment === 'sandbox' ? 'info' : 'success' }}">{{ $client->environment }}</span>
                            <form method="POST" action="{{ route('portals.admin.connect.clients.action', $client->id) }}">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button class="btn btn--sm btn--success">Approve</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="padding:24px;text-align:center;color:#9ca3af;font-size:0.875rem;">
                        <i data-lucide="check-circle" style="width:32px;height:32px;margin-bottom:8px;display:block;margin-inline:auto;"></i>
                        No pending approvals
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Clients --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title">
                    <i data-lucide="app-window" style="width:16px;height:16px;"></i>
                    Recent Clients
                </h2>
                <a href="{{ route('portals.admin.connect.clients') }}" class="btn btn--sm btn--outline">View All</a>
            </div>
            <div class="portal-card__body" style="padding:0;">
                @forelse($recent as $client)
                    <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;">{{ $client->name }}</div>
                            <div style="font-size:0.78rem;color:#9ca3af;font-family:monospace;">{{ $client->client_id }}</div>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <span class="badge badge--{{ $client->status === 'active' ? 'success' : ($client->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ $client->status }}
                            </span>
                            <span class="badge badge--{{ $client->environment === 'sandbox' ? 'info' : 'purple' }}">
                                {{ $client->environment }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="padding:24px;text-align:center;color:#9ca3af;font-size:0.875rem;">No clients yet</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
