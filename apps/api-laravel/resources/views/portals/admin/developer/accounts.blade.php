@extends('layouts.portal')
@section('title', 'Developer Accounts — Admin')
@section('sidebar') @include('portals.admin.connect._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.connect') }}">Connect Suite</a>
    <i data-lucide="chevron-right"></i>
    <span>Developer Accounts</span>
</div>

<div class="page-head">
    <h2>Developer Accounts</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">Manage external developer accounts and sandbox access</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- Stats strip --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__value">{{ $stats['total'] }}</div>
        <div class="stat-card__label">Total Developers</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__value">{{ $stats['active'] }}</div>
        <div class="stat-card__label">Active</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__value">{{ $stats['sandbox_only'] }}</div>
        <div class="stat-card__label">Sandbox Only</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__value">{{ $stats['suspended'] }}</div>
        <div class="stat-card__label">Suspended</div>
    </div>
</div>

@if($accounts->isEmpty())
<div class="empty-state">
    <div class="empty-state-icon"><i data-lucide="users"></i></div>
    <p>No developer accounts registered yet.</p>
</div>
@else
<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr>
                <th>Developer</th>
                <th>Email</th>
                <th>Company</th>
                <th>Apps</th>
                <th>Access</th>
                <th>Status</th>
                <th>Joined</th>
                <th class="row-actions">Actions</th>
            </tr></thead>
            <tbody>
            @foreach($accounts as $account)
            <tr>
                <td data-label="Developer">
                    <div class="td-strong">{{ $account->display_name ?? '—' }}</div>
                    @if($account->website_url)
                    <div class="td-muted"><a href="{{ $account->website_url }}" target="_blank">{{ Str::limit($account->website_url, 30) }}</a></div>
                    @endif
                </td>
                <td data-label="Email"><span class="code-token">{{ $account->email }}</span></td>
                <td data-label="Company">{{ $account->company_name ?? '—' }}</td>
                <td data-label="Apps"><span class="badge badge-neutral">{{ $account->integrationClients_count ?? 0 }}</span></td>
                <td data-label="Access">
                    @if($account->sandbox_only)
                    <span class="badge badge-primary">Sandbox</span>
                    @else
                    <span class="badge badge-success">Production</span>
                    @endif
                </td>
                <td data-label="Status">
                    <span class="{{ $account->statusBadgeClass() }}">{{ ucfirst($account->status) }}</span>
                    @if($account->status === 'suspended' && $account->suspend_reason)
                    <div class="td-muted">{{ Str::limit($account->suspend_reason, 30) }}</div>
                    @endif
                </td>
                <td data-label="Joined">
                    {{ $account->created_at->format('d M Y') }}
                    @if($account->api_terms_accepted)<div class="td-muted">Terms accepted</div>@endif
                </td>
                <td class="row-actions" data-label="Actions">
                    @if($account->status !== 'suspended')
                    <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('suspend-{{ $account->id }}')">Suspend</button>
                    @else
                    <span class="td-muted">Suspended</span>
                    @if($account->suspended_at)<div class="td-muted">{{ $account->suspended_at->format('d M Y') }}</div>@endif
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $accounts->links() }}</div>
</div>

{{-- Suspend confirm modals --}}
@foreach($accounts as $account)
    @if($account->status !== 'suspended')
    <div id="suspend-{{ $account->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="suspend-{{ $account->id }}-title">
            <h3 class="modal__title" id="suspend-{{ $account->id }}-title"><i data-lucide="ban"></i> Suspend developer</h3>
            <form method="POST" action="{{ route('portals.admin.developer.accounts.suspend', $account->id) }}">
                @csrf
                <div class="modal__body">
                    <p>Suspend <strong>{{ $account->display_name ?? $account->email }}</strong>? Provide a reason for the audit trail.</p>
                    <textarea name="reason" rows="3" required placeholder="Suspension reason…" class="form-control"></textarea>
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('suspend-{{ $account->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach
@endif

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
