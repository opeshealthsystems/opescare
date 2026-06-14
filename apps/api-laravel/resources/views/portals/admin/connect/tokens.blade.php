@extends('layouts.portal')

@section('title', 'SDK Tokens — Connect Suite')

@section('sidebar')
    @include('portals.admin.connect._sidebar')
@endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="key-round" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;"></i>
                SDK Tokens
            </h1>
            <p class="portal-page-subtitle">Issue and manage long-lived SDK authentication tokens</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('issueModal')">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Issue Token
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--danger">{{ session('error') }}</div>
    @endif

    {{-- One-time Token Display --}}
    @if(session('new_token'))
    <div class="alert" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:20px;margin-bottom:20px;">
        <div style="display:flex;align-items:flex-start;gap:14px;">
            <i data-lucide="shield-check" style="width:24px;height:24px;color:#16a34a;flex-shrink:0;margin-top:2px;"></i>
            <div style="flex:1;">
                <div style="font-weight:700;color:#15803d;font-size:1rem;margin-bottom:6px;">
                    <i data-lucide="alert-triangle" style="width:14px;height:14px;vertical-align:-2px;"></i> New Token Issued — Copy Now
                </div>
                <div style="font-size:0.85rem;color:#166534;margin-bottom:12px;">
                    This token will <strong>never be shown again</strong>. Copy it immediately and store it securely.
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <code id="newTokenVal" style="font-size:0.82rem;background:#dcfce7;border:1px solid #86efac;padding:10px 16px;border-radius:8px;word-break:break-all;flex:1;min-width:200px;">{{ session('new_token') }}</code>
                    <button class="btn btn--success btn--sm" onclick="copyToken()">
                        <i data-lucide="copy" style="width:14px;height:14px;"></i>
                        Copy
                    </button>
                </div>
                <div id="copiedMsg" style="display:none;margin-top:8px;font-size:0.82rem;color:#16a34a;font-weight:600;">
                    <i data-lucide="check" style="width:14px;height:14px;vertical-align:-2px;"></i> Copied to clipboard!
                </div>
            </div>
        </div>
    </div>
    <script>
    function copyToken(){
        navigator.clipboard.writeText(document.getElementById('newTokenVal').textContent.trim())
            .then(()=>{ document.getElementById('copiedMsg').style.display='block'; })
            .catch(()=>{ alert('Copy failed — please select and copy manually.'); });
    }
    </script>
    @endif

    {{-- Tokens Table --}}
    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tokens as $token)
                        <tr style="{{ !$token->is_active ? 'opacity:0.55;' : '' }}">
                            <td>
                                <code style="font-size:0.78rem;background:#f9fafb;padding:2px 6px;border-radius:4px;border:1px solid #e5e7eb;">
                                    {{ $token->displayToken() }}
                                </code>
                            </td>
                            <td style="font-size:0.83rem;color:#374151;">
                                {{ $token->client_id }}
                            </td>
                            <td style="font-size:0.83rem;">{{ $token->label ?: '—' }}</td>
                            <td>
                                <span class="badge badge--{{ $token->environment === 'sandbox' ? 'info' : 'success' }}">
                                    {{ $token->environment }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;flex-wrap:wrap;gap:3px;max-width:200px;">
                                    @foreach(array_slice($token->scopes ?? [], 0, 2) as $scope)
                                        <span style="font-size:0.7rem;background:#ede9fe;color:#6d28d9;padding:1px 5px;border-radius:8px;">{{ $scope }}</span>
                                    @endforeach
                                    @if(count($token->scopes ?? []) > 2)
                                        <span style="font-size:0.7rem;color:#9ca3af;">+{{ count($token->scopes) - 2 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if(!$token->is_active)
                                    <span class="badge badge--danger">Revoked</span>
                                @elseif($token->isExpired())
                                    <span class="badge badge--warning">Expired</span>
                                @else
                                    <span class="badge badge--success">Active</span>
                                @endif
                            </td>
                            <td style="font-size:0.82rem;color:{{ ($token->expires_at && $token->expires_at->isPast()) ? '#dc2626' : '#6b7280' }};">
                                {{ $token->expires_at ? $token->expires_at->format('d M Y') : 'Never' }}
                            </td>
                            <td style="font-size:0.82rem;color:#9ca3af;">
                                {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : '—' }}
                            </td>
                            <td>
                                @if($token->is_active && !$token->isExpired())
                                    <form method="POST"
                                          action="{{ route('portals.admin.connect.tokens.revoke', $token->id) }}"
                                          onsubmit="return confirm('Revoke this token? The client will lose access immediately.')">
                                        @csrf
                                        <button class="btn btn--sm btn--danger" title="Revoke">
                                            <i data-lucide="x-circle" style="width:13px;height:13px;"></i>
                                            Revoke
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:0.78rem;color:#9ca3af;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#9ca3af;">
                                <i data-lucide="key-round" style="width:36px;height:36px;display:block;margin:0 auto 10px;"></i>
                                No tokens issued yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tokens->hasPages())
            <div class="portal-card__footer">{{ $tokens->links() }}</div>
        @endif
    </div>

</div>

{{-- Issue Token Modal --}}
<div id="issueModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('issueModal')">
    <div class="modal-box" style="max-width:480px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i data-lucide="key-round" style="width:18px;height:18px;"></i>
                Issue New SDK Token
            </h3>
            <button class="modal-close" onclick="closeModal('issueModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.admin.connect.tokens.store') }}">
            @csrf
            <div class="modal-body">

                <div class="form-group">
                    <label class="form-label">Client <span style="color:red">*</span></label>
                    <select name="client_id" class="form-control" required>
                        <option value="">— Select Active Client —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->client_id }}" {{ old('client_id') == $client->client_id ? 'selected' : '' }}>
                                {{ $client->name }} ({{ $client->environment }})
                            </option>
                        @endforeach
                    </select>
                    @if($clients->isEmpty())
                        <div style="font-size:0.8rem;color:#dc2626;margin-top:4px;">
                            No active clients found. <a href="{{ route('portals.admin.connect.clients') }}">Create one first.</a>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">Label / Description</label>
                    <input type="text" name="label" class="form-control" placeholder="e.g. Production deployment, CI/CD pipeline"
                           maxlength="80" value="{{ old('label') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Environment <span style="color:red">*</span></label>
                    <select name="environment" class="form-control" required>
                        <option value="sandbox" {{ old('environment') == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="production" {{ old('environment') == 'production' ? 'selected' : '' }}>Production</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Expires In (Days)</label>
                    <input type="number" name="expires_days" class="form-control" placeholder="Leave blank for no expiry"
                           min="1" max="365" value="{{ old('expires_days') }}">
                    <div style="font-size:0.78rem;color:#9ca3af;margin-top:4px;">Max 365 days. Blank = never expires.</div>
                </div>

                <div class="alert" style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;font-size:0.83rem;color:#92400e;">
                    <i data-lucide="triangle-alert" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                    The token will be shown <strong>only once</strong> after creation. Copy it immediately.
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('issueModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">
                    <i data-lucide="key-round" style="width:15px;height:15px;"></i>
                    Issue Token
                </button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',()=>openModal('issueModal'));</script>
@endif

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
</script>
@endsection
