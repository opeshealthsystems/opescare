@extends('layouts.portal')
@section('title', 'My Apps')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="breadcrumb">
        <a href="{{ route('portals.developer.dashboard') }}">Developer portal</a>
        <i data-lucide="chevron-right"></i>
        <span>My apps</span>
    </div>

    <div class="page-head">
        <h2>My apps</h2>
        <div class="page-head__spacer"></div>
        <a href="{{ route('portals.developer.apps.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> New app</a>
    </div>

    @if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

    @if(session('new_client_secret'))
    <div class="panel mb-6">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="key"></i> App created — save your credentials now</h3></div>
        <div class="panel-body">
            <p class="mb-6">Your client secret is shown <strong>only once</strong>. Store it securely — it cannot be retrieved again.</p>
            <div class="code-block mb-6"><strong>Client ID:</strong> <span class="code-token" data-copy="{{ session('new_client_id') }}">{{ session('new_client_id') }}</span></div>
            <div class="code-block"><strong>Client Secret:</strong> <span class="code-token" data-copy="{{ session('new_client_secret') }}">{{ session('new_client_secret') }}</span></div>
        </div>
    </div>
    @endif

    @if($clients->isEmpty())
    <div class="panel">
        <div class="empty-state">
            <i data-lucide="plug" class="empty-state-icon"></i>
            <p>No apps yet. Create your first app to receive sandbox API credentials.</p>
            <a href="{{ route('portals.developer.apps.create') }}" class="btn btn-primary btn-sm"><i data-lucide="plus"></i> Create app</a>
        </div>
    </div>
    @else
    <div class="panel">
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>App name</th><th>Client ID</th><th>Environment</th><th>Status</th><th>Created</th><th class="row-actions"></th>
                </tr></thead>
                <tbody>
                @foreach($clients as $client)
                <tr>
                    <td data-label="App name">
                        <span class="td-strong">{{ $client->name ?? 'Unnamed App' }}</span>
                        @if($client->description)
                        <div class="td-muted">{{ Str::limit($client->description, 60) }}</div>
                        @endif
                    </td>
                    <td data-label="Client ID"><span class="code-token" data-copy="{{ $client->client_id }}">{{ Str::limit($client->client_id, 28) }}</span></td>
                    <td data-label="Environment">
                        <span class="badge {{ ($client->environment ?? 'sandbox') === 'production' ? 'badge-success' : 'badge-info' }}">{{ ucfirst($client->environment ?? 'sandbox') }}</span>
                    </td>
                    <td data-label="Status">
                        <span class="badge {{ ($client->status ?? 'active') === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($client->status ?? 'active') }}</span>
                    </td>
                    <td data-label="Created" class="td-muted">{{ $client->created_at->format('d M Y') }}</td>
                    <td class="row-actions" data-label=""><a href="{{ route('portals.developer.apps.show', $client->id) }}" class="btn btn-secondary btn-sm">Details</a></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

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
