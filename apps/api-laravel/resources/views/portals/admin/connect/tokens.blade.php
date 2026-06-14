@extends('layouts.portal')

@section('title', 'SDK Tokens — Connect Suite')

@section('sidebar')
    @include('portals.admin.connect._sidebar')
@endsection

@section('content')

<div class="page-head">
    <h2><i data-lucide="key-round"></i> SDK Tokens</h2>
    <div class="page-head__spacer"></div>
    <button class="btn btn-primary" onclick="opOpenModal('issueModal')">
        <i data-lucide="plus"></i> Issue Token
    </button>
</div>
<p class="td-muted mb-6">Issue and manage long-lived SDK authentication tokens</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- One-time Token Display --}}
@if(session('new_token'))
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="shield-check"></i> New Token Issued — Copy Now</h3></div>
    <div class="panel-body">
        <p class="mb-6"><i data-lucide="alert-triangle"></i> This token will <strong>never be shown again</strong>. Copy it immediately and store it securely.</p>
        <div class="filter-bar">
            <code id="newTokenVal" class="code-token code-token--block">{{ session('new_token') }}</code>
            <button class="btn btn-success btn-sm" onclick="copyToken()"><i data-lucide="copy"></i> Copy</button>
        </div>
        <div id="copiedMsg" class="text-success mt-6" hidden><i data-lucide="check"></i> Copied to clipboard!</div>
    </div>
</div>
<script>
function copyToken(){
    navigator.clipboard.writeText(document.getElementById('newTokenVal').textContent.trim())
        .then(()=>{ document.getElementById('copiedMsg').removeAttribute('hidden'); })
        .catch(()=>{ alert('Copy failed — please select and copy manually.'); });
}
</script>
@endif

{{-- Tokens Table --}}
<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Token</th>
                    <th>Client</th>
                    <th>Label</th>
                    <th>Environment</th>
                    <th>Scopes</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Last Used</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tokens as $token)
                    <tr>
                        <td data-label="Token"><span class="code-token">{{ $token->displayToken() }}</span></td>
                        <td data-label="Client">{{ $token->client_id }}</td>
                        <td data-label="Label">{{ $token->label ?: '—' }}</td>
                        <td data-label="Environment">
                            <span class="badge badge-{{ $token->environment === 'sandbox' ? 'primary' : 'success' }}">{{ $token->environment }}</span>
                        </td>
                        <td data-label="Scopes">
                            @foreach(array_slice($token->scopes ?? [], 0, 2) as $scope)
                                <span class="badge badge-purple">{{ $scope }}</span>
                            @endforeach
                            @if(count($token->scopes ?? []) > 2)<span class="td-muted">+{{ count($token->scopes) - 2 }}</span>@endif
                        </td>
                        <td data-label="Status">
                            @if(!$token->is_active)
                                <span class="badge badge-danger">Revoked</span>
                            @elseif($token->isExpired())
                                <span class="badge badge-warning">Expired</span>
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                        <td data-label="Expires">{{ $token->expires_at ? $token->expires_at->format('d M Y') : 'Never' }}</td>
                        <td data-label="Last Used">{{ $token->last_used_at ? $token->last_used_at->diffForHumans() : '—' }}</td>
                        <td class="row-actions" data-label="Actions">
                            @if($token->is_active && !$token->isExpired())
                                <button type="button" class="btn btn-danger btn-sm" title="Revoke" onclick="opOpenModal('revoke-{{ $token->id }}')"><i data-lucide="x-circle"></i> Revoke</button>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="td-muted empty-cell">No tokens issued yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tokens->hasPages())
        <div class="panel-body">{{ $tokens->links() }}</div>
    @endif
</div>

{{-- Revoke confirm modals --}}
@foreach($tokens as $token)
    @if($token->is_active && !$token->isExpired())
    <div id="revoke-{{ $token->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="revoke-{{ $token->id }}-title">
            <h3 class="modal__title" id="revoke-{{ $token->id }}-title"><i data-lucide="x-circle"></i> Revoke token</h3>
            <form method="POST" action="{{ route('portals.admin.connect.tokens.revoke', $token->id) }}">
                @csrf
                <div class="modal__body"><p>Revoke this token? The client will lose access immediately.</p></div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('revoke-{{ $token->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Revoke</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

{{-- Issue Token Modal --}}
<div id="issueModal" class="modal-backdrop mt-6" {{ $errors->any() ? '' : 'hidden' }}>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="issueModal-title">
        <h3 class="modal__title" id="issueModal-title"><i data-lucide="key-round"></i> Issue New SDK Token</h3>
        <form method="POST" action="{{ route('portals.admin.connect.tokens.store') }}">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Client</label>
                    <select name="client_id" class="form-control" required>
                        <option value="">— Select Active Client —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->client_id }}" {{ old('client_id') == $client->client_id ? 'selected' : '' }}>{{ $client->name }} ({{ $client->environment }})</option>
                        @endforeach
                    </select>
                    @if($clients->isEmpty())
                        <div class="form-hint">No active clients found. <a href="{{ route('portals.admin.connect.clients') }}">Create one first.</a></div>
                    @endif
                </div>
                <div class="form-group">
                    <label class="form-label">Label / Description</label>
                    <input type="text" name="label" class="form-control" placeholder="e.g. Production deployment, CI/CD pipeline" maxlength="80" value="{{ old('label') }}">
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Environment</label>
                    <select name="environment" class="form-control" required>
                        <option value="sandbox" {{ old('environment') == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="production" {{ old('environment') == 'production' ? 'selected' : '' }}>Production</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Expires In (Days)</label>
                    <input type="number" name="expires_days" class="form-control" placeholder="Leave blank for no expiry" min="1" max="365" value="{{ old('expires_days') }}">
                    <div class="form-hint">Max 365 days. Blank = never expires.</div>
                </div>
                <div class="alert alert-warning"><i data-lucide="triangle-alert"></i><div>The token will be shown <strong>only once</strong> after creation. Copy it immediately.</div></div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('issueModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="key-round"></i> Issue Token</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
