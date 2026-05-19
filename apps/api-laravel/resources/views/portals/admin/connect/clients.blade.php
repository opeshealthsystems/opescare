@extends('layouts.portal')

@section('title', 'API Clients — Connect Suite')

@section('sidebar')
    @include('portals.admin.connect._sidebar')
@endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="app-window" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;"></i>
                API Clients
            </h1>
            <p class="portal-page-subtitle">Manage third-party integrations and their access credentials</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('createModal')">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            New Client
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--danger">{{ session('error') }}</div>
    @endif

    {{-- Filters --}}
    <div class="portal-card" style="margin-bottom:20px;">
        <div class="portal-card__body">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-control--sm">
                        <option value="">All</option>
                        @foreach(['pending','active','suspended','revoked'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Environment</label>
                    <select name="environment" class="form-control form-control--sm">
                        <option value="">All</option>
                        <option value="sandbox" {{ request('environment') == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="production" {{ request('environment') == 'production' ? 'selected' : '' }}>Production</option>
                    </select>
                </div>
                <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                <a href="{{ route('portals.admin.connect.clients') }}" class="btn btn--outline btn--sm">Reset</a>
            </form>
        </div>
    </div>

    {{-- Clients Table --}}
    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Client ID</th>
                        <th>Environment</th>
                        <th>Scopes</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $client->name }}</div>
                                @if($client->description)
                                    <div style="font-size:0.78rem;color:#6b7280;">{{ Str::limit($client->description, 60) }}</div>
                                @endif
                                @if($client->contact_email)
                                    <div style="font-size:0.78rem;color:#9ca3af;">{{ $client->contact_email }}</div>
                                @endif
                            </td>
                            <td>
                                <code style="font-size:0.78rem;background:#f9fafb;padding:2px 6px;border-radius:4px;border:1px solid #e5e7eb;">{{ $client->client_id }}</code>
                            </td>
                            <td>
                                <span class="badge badge--{{ $client->environment === 'sandbox' ? 'info' : 'success' }}">
                                    {{ $client->environment }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;flex-wrap:wrap;gap:4px;max-width:260px;">
                                    @foreach(array_slice($client->scopes ?? [], 0, 3) as $scope)
                                        <span style="font-size:0.72rem;background:#ede9fe;color:#6d28d9;padding:2px 6px;border-radius:10px;">{{ $scope }}</span>
                                    @endforeach
                                    @if(count($client->scopes ?? []) > 3)
                                        <span style="font-size:0.72rem;color:#9ca3af;">+{{ count($client->scopes) - 3 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge--{{
                                    $client->status === 'active' ? 'success' :
                                    ($client->status === 'pending' ? 'warning' :
                                    ($client->status === 'suspended' ? 'info' : 'danger'))
                                }}">{{ $client->status }}</span>
                            </td>
                            <td style="font-size:0.83rem;color:#6b7280;">{{ $client->created_at->format('d M Y') }}</td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    @if($client->status === 'pending')
                                        <form method="POST" action="{{ route('portals.admin.connect.clients.action', $client->id) }}" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn--sm btn--success" title="Approve">
                                                <i data-lucide="check" style="width:13px;height:13px;"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($client->status === 'active')
                                        <form method="POST" action="{{ route('portals.admin.connect.clients.action', $client->id) }}" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="action" value="suspend">
                                            <button class="btn btn--sm btn--warning" title="Suspend">
                                                <i data-lucide="pause" style="width:13px;height:13px;"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($client->status, ['active','suspended']))
                                        <form method="POST" action="{{ route('portals.admin.connect.clients.action', $client->id) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Revoke this client? All tokens will be deactivated.')">
                                            @csrf
                                            <input type="hidden" name="action" value="revoke">
                                            <button class="btn btn--sm btn--danger" title="Revoke">
                                                <i data-lucide="ban" style="width:13px;height:13px;"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">
                                <i data-lucide="app-window" style="width:36px;height:36px;display:block;margin:0 auto 10px;"></i>
                                No clients found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
            <div class="portal-card__footer">{{ $clients->links() }}</div>
        @endif
    </div>

</div>

{{-- Create Client Modal --}}
<div id="createModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('createModal')">
    <div class="modal-box" style="max-width:620px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
                New API Client
            </h3>
            <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.admin.connect.clients.store') }}">
            @csrf
            <div class="modal-body">

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Client Name <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Acme Lab System" required maxlength="100"
                               value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" placeholder="dev@example.com" maxlength="100"
                               value="{{ old('contact_email') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" maxlength="300"
                              placeholder="Brief description of this integration">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Environment <span style="color:red">*</span></label>
                    <select name="environment" class="form-control" required>
                        <option value="sandbox" {{ old('environment') == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="production" {{ old('environment') == 'production' ? 'selected' : '' }}>Production</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Scopes <span style="color:red">*</span></label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;border:1px solid #e5e7eb;border-radius:8px;padding:14px;background:#fafafa;">
                        @foreach($scopes as $key => $label)
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.85rem;">
                                <input type="checkbox" name="scopes[]" value="{{ $key }}"
                                       {{ in_array($key, old('scopes', [])) ? 'checked' : '' }}
                                       style="accent-color:#6366f1;">
                                <span>
                                    <span style="font-weight:500;">{{ $label }}</span><br>
                                    <span style="font-size:0.72rem;color:#9ca3af;font-family:monospace;">{{ $key }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">
                    <i data-lucide="plus-circle" style="width:15px;height:15px;"></i>
                    Create Client
                </button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',()=>openModal('createModal'));</script>
@endif

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
</script>
@endsection
