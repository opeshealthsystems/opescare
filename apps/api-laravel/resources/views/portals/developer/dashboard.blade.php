@extends('layouts.portal')
@section('title', 'Developer Portal')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Developer Portal</h1>
            <p class="portal-page-subtitle">Welcome back, {{ $developer->display_name }}</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            @if($developer->isSandboxOnly())
                <span class="badge badge--warning">Sandbox Only</span>
                <a href="{{ route('portals.developer.production_requests.create') }}" class="btn btn--primary btn--sm">Request Production Access</a>
            @else
                <span class="badge badge--success">Production Access</span>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:0.88rem;">✗ {{ session('error') }}</div>
    @endif

    {{-- Account status warning --}}
    @if(!$developer->isEmailVerified())
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:0.86rem;color:#92400e;">
        ⚠ Your email address has not been verified. Some features are restricted until verification is complete.
    </div>
    @endif

    {{-- Stats strip --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
        <div class="portal-card" style="text-align:center;padding:16px;">
            <div style="font-size:2rem;font-weight:800;color:#7c3aed;">{{ $clients->count() }}</div>
            <div style="font-size:0.8rem;color:#6b7280;margin-top:4px;">Apps</div>
        </div>
        <div class="portal-card" style="text-align:center;padding:16px;">
            <div style="font-size:2rem;font-weight:800;color:#0369a1;">{{ number_format($totalRequests) }}</div>
            <div style="font-size:0.8rem;color:#6b7280;margin-top:4px;">API Requests (30d)</div>
        </div>
        <div class="portal-card" style="text-align:center;padding:16px;">
            <div style="font-size:2rem;font-weight:800;color:{{ $totalErrors > 0 ? '#dc2626' : '#16a34a' }};">{{ number_format($totalErrors) }}</div>
            <div style="font-size:0.8rem;color:#6b7280;margin-top:4px;">Errors (30d)</div>
        </div>
        <div class="portal-card" style="text-align:center;padding:16px;">
            <div style="font-size:2rem;font-weight:800;color:#d97706;">{{ $productionRequests->where('status','pending')->count() }}</div>
            <div style="font-size:0.8rem;color:#6b7280;margin-top:4px;">Pending Requests</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- Apps --}}
        <div class="portal-card">
            <div class="portal-card__header" style="display:flex;justify-content:space-between;align-items:center;">
                <h2 class="portal-card__title">Your Apps</h2>
                <a href="{{ route('portals.developer.apps.create') }}" class="btn btn--primary btn--sm">+ New App</a>
            </div>
            @if($clients->isEmpty())
            <div class="portal-card__body" style="padding:20px;text-align:center;color:#9ca3af;font-size:0.85rem;">
                No apps yet. <a href="{{ route('portals.developer.apps.create') }}" style="color:#7c3aed;">Create your first app</a> to get sandbox API credentials.
            </div>
            @else
            <div class="portal-card__body" style="padding:0;">
                <table class="portal-table" style="font-size:0.83rem;">
                    <thead><tr><th>App</th><th>Environment</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td>
                            <strong>{{ $client->name ?? 'Unnamed App' }}</strong>
                            <div style="font-size:0.75rem;color:#9ca3af;font-family:monospace;">{{ Str::limit($client->client_id, 20) }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $client->environment === 'production' ? 'badge--success' : 'badge--info' }}" style="font-size:0.7rem;">
                                {{ ucfirst($client->environment ?? 'sandbox') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ ($client->status ?? 'active') === 'active' ? 'badge--success' : 'badge--neutral' }}" style="font-size:0.7rem;">
                                {{ ucfirst($client->status ?? 'active') }}
                            </span>
                        </td>
                        <td><a href="{{ route('portals.developer.apps.show', $client->id) }}" style="font-size:0.78rem;color:#7c3aed;">View</a></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Production Requests --}}
        <div class="portal-card">
            <div class="portal-card__header" style="display:flex;justify-content:space-between;align-items:center;">
                <h2 class="portal-card__title">Production Requests</h2>
                <a href="{{ route('portals.developer.production_requests.create') }}" class="btn btn--outline btn--sm">Request Access</a>
            </div>
            @if($productionRequests->isEmpty())
            <div class="portal-card__body" style="padding:20px;text-align:center;color:#9ca3af;font-size:0.85rem;">
                No production access requests yet.
            </div>
            @else
            <div class="portal-card__body" style="padding:0;">
                <table class="portal-table" style="font-size:0.83rem;">
                    <thead><tr><th>Use Case</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    @foreach($productionRequests as $req)
                    <tr>
                        <td>{{ Str::limit($req->use_case, 40) }}</td>
                        <td><span class="{{ $req->statusBadgeClass() }}" style="font-size:0.7rem;">{{ ucfirst(str_replace('_',' ',$req->status)) }}</span></td>
                        <td style="color:#9ca3af;">{{ $req->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>

    {{-- Quick Links --}}
    <div class="portal-card" style="margin-top:20px;">
        <div class="portal-card__header"><h2 class="portal-card__title">Quick Links</h2></div>
        <div class="portal-card__body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:16px;">
            <a href="{{ route('portals.developer.apps') }}" style="text-decoration:none;padding:12px;background:#f9fafb;border-radius:8px;text-align:center;border:1px solid #e5e7eb;">
                <div style="font-size:1.4rem;">🔑</div>
                <div style="font-size:0.8rem;font-weight:600;margin-top:6px;color:#374151;">API Keys</div>
            </a>
            <a href="{{ route('portals.developer.production_requests') }}" style="text-decoration:none;padding:12px;background:#f9fafb;border-radius:8px;text-align:center;border:1px solid #e5e7eb;">
                <div style="font-size:1.4rem;">🚀</div>
                <div style="font-size:0.8rem;font-weight:600;margin-top:6px;color:#374151;">Production Access</div>
            </a>
            <a href="{{ route('docs.index') }}" target="_blank" style="text-decoration:none;padding:12px;background:#f9fafb;border-radius:8px;text-align:center;border:1px solid #e5e7eb;">
                <div style="font-size:1.4rem;">📖</div>
                <div style="font-size:0.8rem;font-weight:600;margin-top:6px;color:#374151;">API Docs</div>
            </a>
            <a href="{{ route('portals.developer.apps') }}" style="text-decoration:none;padding:12px;background:#f9fafb;border-radius:8px;text-align:center;border:1px solid #e5e7eb;">
                <div style="font-size:1.4rem;">📊</div>
                <div style="font-size:0.8rem;font-weight:600;margin-top:6px;color:#374151;">Usage Metrics</div>
            </a>
        </div>
    </div>

@endsection
