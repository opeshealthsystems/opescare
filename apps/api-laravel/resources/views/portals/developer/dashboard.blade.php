@extends('layouts.portal')
@section('title', 'Developer Portal')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="page-head">
        <h2>Developer portal</h2>
        <p class="portal-page-subtitle">Welcome back, {{ $developer->display_name }}</p>
        <div class="page-head__spacer"></div>
        @if($developer->isSandboxOnly())
            <span class="badge badge-warning">Sandbox only</span>
            <a href="{{ route('portals.developer.production_requests.create') }}" class="btn btn-primary btn-sm"><i data-lucide="rocket"></i> Request production access</a>
        @else
            <span class="badge badge-success">Production access</span>
        @endif
    </div>

    @if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
    @if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

    @if(!$developer->isEmailVerified())
    <div class="alert alert-warning mb-6"><i data-lucide="alert-triangle"></i><div>Your email address has not been verified. Some features are restricted until verification is complete.</div></div>
    @endif

    {{-- Stats strip --}}
    <div class="stat-grid mb-6">
        <div class="stat-card stat-card--primary">
            <div class="stat-card__label">Apps</div>
            <div class="stat-card__value">{{ $clients->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">API requests (30d)</div>
            <div class="stat-card__value">{{ number_format($totalRequests) }}</div>
        </div>
        <div class="stat-card {{ $totalErrors > 0 ? 'stat-card--danger' : 'stat-card--success' }}">
            <div class="stat-card__label">Errors (30d)</div>
            <div class="stat-card__value">{{ number_format($totalErrors) }}</div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-card__label">Pending requests</div>
            <div class="stat-card__value">{{ $productionRequests->where('status','pending')->count() }}</div>
        </div>
    </div>

    <div class="field-grid mb-6">

        {{-- Apps --}}
        <div class="panel">
            <div class="panel-header">
                <h3 class="panel-title"><i data-lucide="plug"></i> Your apps</h3>
                <a href="{{ route('portals.developer.apps.create') }}" class="btn btn-primary btn-sm"><i data-lucide="plus"></i> New app</a>
            </div>
            @if($clients->isEmpty())
            <div class="panel-body empty-state">
                <p>No apps yet. <a href="{{ route('portals.developer.apps.create') }}">Create your first app</a> to get sandbox API credentials.</p>
            </div>
            @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>App</th><th>Environment</th><th>Status</th><th class="row-actions"></th></tr></thead>
                    <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td data-label="App">
                            <span class="td-strong">{{ $client->name ?? 'Unnamed App' }}</span>
                            <div class="mono">{{ Str::limit($client->client_id, 20) }}</div>
                        </td>
                        <td data-label="Environment">
                            <span class="badge {{ $client->environment === 'production' ? 'badge-success' : 'badge-info' }}">{{ ucfirst($client->environment ?? 'sandbox') }}</span>
                        </td>
                        <td data-label="Status">
                            <span class="badge {{ ($client->status ?? 'active') === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($client->status ?? 'active') }}</span>
                        </td>
                        <td class="row-actions" data-label="">
                            <a href="{{ route('portals.developer.apps.show', $client->id) }}" class="icon-btn" aria-label="View app" title="View"><i data-lucide="eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Production Requests --}}
        <div class="panel">
            <div class="panel-header">
                <h3 class="panel-title"><i data-lucide="rocket"></i> Production requests</h3>
                <a href="{{ route('portals.developer.production_requests.create') }}" class="btn btn-secondary btn-sm">Request access</a>
            </div>
            @if($productionRequests->isEmpty())
            <div class="panel-body empty-state">
                <p>No production access requests yet.</p>
            </div>
            @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Use case</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    @foreach($productionRequests as $req)
                    <tr>
                        <td data-label="Use case">{{ Str::limit($req->use_case, 40) }}</td>
                        <td data-label="Status"><span class="{{ $req->statusBadgeClass() }}">{{ ucfirst(str_replace('_',' ',$req->status)) }}</span></td>
                        <td data-label="Date" class="td-muted">{{ $req->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>

    {{-- Quick Links --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="link"></i> Quick links</h3></div>
        <div class="panel-body stat-grid">
            <a href="{{ route('portals.developer.apps') }}" class="stat-card">
                <i data-lucide="key"></i>
                <div class="stat-card__label">API keys</div>
            </a>
            <a href="{{ route('portals.developer.production_requests') }}" class="stat-card">
                <i data-lucide="rocket"></i>
                <div class="stat-card__label">Production access</div>
            </a>
            <a href="{{ route('portals.developer.analytics') }}" class="stat-card">
                <i data-lucide="bar-chart-3"></i>
                <div class="stat-card__label">API Analytics</div>
            </a>
            <a href="{{ route('portals.developer.analytics') }}" class="stat-card">
                <i data-lucide="bar-chart-3"></i>
                <div class="stat-card__label">Usage metrics</div>
            </a>
        </div>
    </div>

@endsection
