@extends('layouts.portal')
@section('title', 'Bridge Agents — Admin')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Bridge')

@section('content')

<div class="page-head">
    <h2><i data-lucide="cable"></i> Bridge Agents</h2>
    <div class="page-head__spacer"></div>
    <button class="btn btn-primary" onclick="opOpenModal('createModal')">
        <i data-lucide="plus"></i> Register Agent
    </button>
</div>
<p class="td-muted mb-6">Manage agents that sync data from external EMR and legacy systems</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- One-time key banner --}}
@if(session('new_agent_key'))
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="key"></i> New Agent Key — Copy Now (shown once only)</h3></div>
    <div class="panel-body">
        <div class="filter-bar">
            <code id="newKey" class="code-token code-token--block">{{ session('new_agent_key') }}</code>
            <button class="btn btn-secondary btn-sm" onclick="copyKey()"><i data-lucide="copy"></i> Copy Key</button>
        </div>
        <p class="td-muted mt-6">Store this key securely in your Bridge Agent config. It cannot be recovered.</p>
    </div>
</div>
@endif

{{-- KPI Strip --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="cable"></i></div>
        <div class="stat-card__value">{{ $stats['total'] }}</div>
        <div class="stat-card__label">Total Agents</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle"></i></div>
        <div class="stat-card__value">{{ $stats['active'] }}</div>
        <div class="stat-card__label">Active</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="layers"></i></div>
        <div class="stat-card__value">{{ $stats['totalBatches'] }}</div>
        <div class="stat-card__label">Total Batches</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__head"><i data-lucide="x-circle"></i></div>
        <div class="stat-card__value">{{ $stats['failedBatches'] }}</div>
        <div class="stat-card__label">Failed Batches</div>
    </div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Agent Name</th>
                    <th>Key Prefix</th>
                    <th>Status</th>
                    <th>Version</th>
                    <th>Last Seen</th>
                    <th>Last Sync</th>
                    <th>Batches</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agents as $agent)
                    <tr>
                        <td data-label="Agent Name">
                            <div class="td-strong">{{ $agent->name }}</div>
                            @if($agent->hostname)<div class="td-muted">{{ $agent->hostname }}</div>@endif
                            @if($agent->notes)<div class="td-muted">{{ Str::limit($agent->notes, 45) }}</div>@endif
                        </td>
                        <td data-label="Key Prefix"><span class="code-token">{{ $agent->displayKey() }}</span></td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $agent->status === 'active' ? 'success' : ($agent->status === 'suspended' ? 'danger' : 'warning') }}">{{ $agent->status }}</span>
                        </td>
                        <td data-label="Version">{{ $agent->version ?: '—' }}</td>
                        <td data-label="Last Seen">
                            {{ $agent->last_seen_at ? $agent->last_seen_at->diffForHumans() : '—' }}
                            @if($agent->ip_address)<div class="td-muted">{{ $agent->ip_address }}</div>@endif
                        </td>
                        <td data-label="Last Sync">{{ $agent->last_sync_at ? $agent->last_sync_at->diffForHumans() : '—' }}</td>
                        <td data-label="Batches">
                            <a href="{{ route('portals.admin.bridge.batches', $agent->id) }}" class="btn btn-secondary btn-sm">{{ $agent->sync_batches_count }} batches</a>
                        </td>
                        <td class="row-actions" data-label="Actions">
                            <button type="button" class="btn btn-{{ $agent->status === 'active' ? 'warning' : 'success' }} btn-sm" onclick="opOpenModal('toggle-{{ $agent->id }}')">
                                {{ $agent->status === 'active' ? 'Suspend' : 'Reactivate' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="td-muted empty-cell">No bridge agents registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($agents->hasPages())<div class="panel-body">{{ $agents->links() }}</div>@endif
</div>

{{-- API Integration Guide --}}
<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="book-open"></i> Bridge Agent API Endpoints</h3></div>
    <div class="panel-body">
        <div class="field-grid">
            <div class="stat-card">
                <code class="code-token">POST /api/v1/bridge/sync</code>
                <p class="td-muted">Post a batch of records. Supports ehr_records, appointments, pharmacy_stock, blood_stock.</p>
            </div>
            <div class="stat-card">
                <code class="code-token">POST /api/v1/bridge/heartbeat</code>
                <p class="td-muted">Announce agent version, hostname, and capabilities. Updates last-seen timestamp.</p>
            </div>
            <div class="stat-card">
                <code class="code-token">GET /api/v1/bridge/status</code>
                <p class="td-muted">Query recent batch results and sync health for this agent.</p>
            </div>
        </div>
        <p class="td-muted mt-6">All endpoints require header: <code class="code-token">X-Bridge-Agent-Key: &lt;raw_key&gt;</code></p>
    </div>
</div>

{{-- Toggle confirm modals --}}
@foreach($agents as $agent)
<div id="toggle-{{ $agent->id }}" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="toggle-{{ $agent->id }}-title">
        <h3 class="modal__title" id="toggle-{{ $agent->id }}-title"><i data-lucide="cable"></i> {{ $agent->status === 'active' ? 'Suspend' : 'Reactivate' }} agent</h3>
        <form method="POST" action="{{ route('portals.admin.bridge.toggle', $agent->id) }}">
            @csrf
            <div class="modal__body"><p>{{ $agent->status === 'active' ? 'Suspend' : 'Reactivate' }} <strong>{{ $agent->name }}</strong>?</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('toggle-{{ $agent->id }}')">Cancel</button>
                <button type="submit" class="btn btn-{{ $agent->status === 'active' ? 'warning' : 'success' }}">{{ $agent->status === 'active' ? 'Suspend' : 'Reactivate' }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Register Agent Modal --}}
<div id="createModal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="createModal-title">
        <h3 class="modal__title" id="createModal-title"><i data-lucide="cable"></i> Register Bridge Agent</h3>
        <form method="POST" action="{{ route('portals.admin.bridge.store') }}">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Agent Name</label>
                    <input type="text" name="name" class="form-control" required maxlength="100" placeholder="e.g. Main Campus EMR Bridge">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Purpose, deployment location, etc."></textarea>
                </div>
                <p class="td-muted">A unique agent key will be generated and shown once after registration.</p>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('createModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Register &amp; Generate Key</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
function copyKey(){
    const key = document.getElementById('newKey')?.textContent?.trim();
    if(key) navigator.clipboard.writeText(key).then(() => alert('Key copied to clipboard!'));
}
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
