@extends('layouts.portal')
@section('title', 'My Apps')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.developer.dashboard') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Developer Portal</a>
            <h1 class="portal-page-title" style="margin-top:4px;">My Apps</h1>
        </div>
        <a href="{{ route('portals.developer.apps.create') }}" class="btn btn--primary">+ New App</a>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;">✓ {{ session('success') }}</div>
    @endif

    @if(session('new_client_secret'))
    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px;margin-bottom:20px;">
        <div style="font-weight:700;color:#0369a1;margin-bottom:8px;">🔑 App Created — Save Your Credentials Now</div>
        <p style="font-size:0.84rem;color:#0c4a6e;margin-bottom:10px;">Your client secret is shown <strong>only once</strong>. Store it securely — it cannot be retrieved again.</p>
        <div style="background:#fff;border:1px solid #bae6fd;border-radius:6px;padding:10px;font-family:monospace;font-size:0.82rem;margin-bottom:6px;">
            <strong>Client ID:</strong> {{ session('new_client_id') }}
        </div>
        <div style="background:#fff;border:1px solid #bae6fd;border-radius:6px;padding:10px;font-family:monospace;font-size:0.82rem;">
            <strong>Client Secret:</strong> {{ session('new_client_secret') }}
        </div>
    </div>
    @endif

    @if($clients->isEmpty())
    <div class="portal-card" style="padding:40px;text-align:center;color:#9ca3af;">
        <div style="font-size:1.8rem;margin-bottom:12px;">🔌</div>
        <p style="font-size:0.88rem;">No apps yet. Create your first app to receive sandbox API credentials.</p>
        <a href="{{ route('portals.developer.apps.create') }}" class="btn btn--primary btn--sm" style="margin-top:12px;">Create App</a>
    </div>
    @else
    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead><tr>
                    <th>App Name</th><th>Client ID</th><th>Environment</th><th>Status</th><th>Created</th><th></th>
                </tr></thead>
                <tbody>
                @foreach($clients as $client)
                <tr>
                    <td>
                        <strong>{{ $client->name ?? 'Unnamed App' }}</strong>
                        @if($client->description)
                        <div style="font-size:0.75rem;color:#9ca3af;">{{ Str::limit($client->description, 60) }}</div>
                        @endif
                    </td>
                    <td style="font-family:monospace;font-size:0.78rem;color:#7c3aed;">{{ Str::limit($client->client_id, 28) }}</td>
                    <td>
                        <span class="badge {{ ($client->environment ?? 'sandbox') === 'production' ? 'badge--success' : 'badge--info' }}" style="font-size:0.7rem;">
                            {{ ucfirst($client->environment ?? 'sandbox') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ ($client->status ?? 'active') === 'active' ? 'badge--success' : 'badge--neutral' }}" style="font-size:0.7rem;">
                            {{ ucfirst($client->status ?? 'active') }}
                        </span>
                    </td>
                    <td style="color:#9ca3af;font-size:0.8rem;">{{ $client->created_at->format('d M Y') }}</td>
                    <td><a href="{{ route('portals.developer.apps.show', $client->id) }}" class="btn btn--outline btn--sm">Details</a></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@endsection
