@extends('layouts.portal')
@section('title', 'Bridge Agents — Admin')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="cable" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Bridge Agents
            </h1>
            <p class="portal-page-subtitle">Manage agents that sync data from external EMR and legacy systems</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('createModal')">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Register Agent
        </button>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    {{-- One-time key banner --}}
    @if(session('new_agent_key'))
    <div class="alert alert--warning" style="background:#fffbeb;border:1px solid #d97706;border-radius:8px;padding:16px;margin-bottom:18px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <div>
                <div style="font-weight:700;font-size:0.88rem;margin-bottom:6px;">
                    <i data-lucide="key" style="width:14px;height:14px;"></i>
                    New Agent Key — Copy Now (shown once only)
                </div>
                <code id="newKey" style="background:#1e293b;color:#fbbf24;padding:10px 16px;border-radius:6px;font-size:0.85rem;word-break:break-all;display:block;">{{ session('new_agent_key') }}</code>
            </div>
            <button class="btn btn--sm btn--outline" onclick="copyKey()" style="white-space:nowrap;">
                <i data-lucide="copy" style="width:13px;height:13px;"></i> Copy Key
            </button>
        </div>
        <p style="font-size:0.78rem;color:#92400e;margin-top:8px;margin-bottom:0;">
            Store this key securely in your Bridge Agent config. It cannot be recovered.
        </p>
    </div>
    @endif

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;"><i data-lucide="cable" style="color:#7c3aed;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['total'] }}</div><div class="stat-card__label">Total Agents</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="check-circle" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['active'] }}</div><div class="stat-card__label">Active</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="layers" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['totalBatches'] }}</div><div class="stat-card__label">Total Batches</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;"><i data-lucide="x-circle" style="color:#dc2626;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#dc2626;">{{ $stats['failedBatches'] }}</div><div class="stat-card__label">Failed Batches</div></div>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Agent Name</th>
                        <th>Key Prefix</th>
                        <th>Status</th>
                        <th>Version</th>
                        <th>Last Seen</th>
                        <th>Last Sync</th>
                        <th>Batches</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:0.88rem;">{{ $agent->name }}</div>
                                @if($agent->hostname)
                                    <div style="font-size:0.74rem;color:#9ca3af;">{{ $agent->hostname }}</div>
                                @endif
                                @if($agent->notes)
                                    <div style="font-size:0.74rem;color:#9ca3af;">{{ Str::limit($agent->notes, 45) }}</div>
                                @endif
                            </td>
                            <td><code style="font-size:0.78rem;background:#f9fafb;padding:2px 6px;border-radius:4px;">{{ $agent->displayKey() }}</code></td>
                            <td>
                                <span class="badge badge--{{ $agent->status === 'active' ? 'success' : ($agent->status === 'suspended' ? 'danger' : 'warning') }}">
                                    {{ $agent->status }}
                                </span>
                            </td>
                            <td style="font-size:0.82rem;color:#6b7280;">{{ $agent->version ?: '—' }}</td>
                            <td style="font-size:0.8rem;color:#6b7280;">
                                {{ $agent->last_seen_at ? $agent->last_seen_at->diffForHumans() : '—' }}
                                @if($agent->ip_address)
                                    <div style="font-size:0.72rem;">{{ $agent->ip_address }}</div>
                                @endif
                            </td>
                            <td style="font-size:0.8rem;color:#6b7280;">
                                {{ $agent->last_sync_at ? $agent->last_sync_at->diffForHumans() : '—' }}
                            </td>
                            <td>
                                <a href="{{ route('portals.admin.bridge.batches', $agent->id) }}"
                                   class="btn btn--sm btn--outline" style="font-size:0.78rem;">
                                    {{ $agent->sync_batches_count }} batches
                                </a>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('portals.admin.bridge.toggle', $agent->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn--sm {{ $agent->status === 'active' ? 'btn--warning' : 'btn--success' }}"
                                            onclick="return confirm('{{ $agent->status === 'active' ? 'Suspend' : 'Reactivate' }} this agent?')">
                                        {{ $agent->status === 'active' ? 'Suspend' : 'Reactivate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">
                            <i data-lucide="cable" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                            No bridge agents registered yet.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($agents->hasPages())<div class="portal-card__footer">{{ $agents->links() }}</div>@endif
    </div>

    {{-- API Integration Guide --}}
    <div class="portal-card" style="margin-top:20px;">
        <div class="portal-card__header">
            <h2 class="portal-card__title"><i data-lucide="book-open" style="width:15px;height:15px;"></i> Bridge Agent API Endpoints</h2>
        </div>
        <div class="portal-card__body">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;font-size:0.82rem;">
                <div style="background:#f8fafc;border-radius:6px;padding:12px;">
                    <code style="color:#6366f1;">POST /api/v1/bridge/sync</code>
                    <p style="margin:6px 0 0;color:#6b7280;">Post a batch of records. Supports ehr_records, appointments, pharmacy_stock, blood_stock.</p>
                </div>
                <div style="background:#f8fafc;border-radius:6px;padding:12px;">
                    <code style="color:#6366f1;">POST /api/v1/bridge/heartbeat</code>
                    <p style="margin:6px 0 0;color:#6b7280;">Announce agent version, hostname, and capabilities. Updates last-seen timestamp.</p>
                </div>
                <div style="background:#f8fafc;border-radius:6px;padding:12px;">
                    <code style="color:#6366f1;">GET /api/v1/bridge/status</code>
                    <p style="margin:6px 0 0;color:#6b7280;">Query recent batch results and sync health for this agent.</p>
                </div>
            </div>
            <p style="font-size:0.8rem;color:#9ca3af;margin-top:10px;margin-bottom:0;">
                All endpoints require header: <code>X-Bridge-Agent-Key: &lt;raw_key&gt;</code>
            </p>
        </div>
    </div>

</div>

{{-- Register Agent Modal --}}
<div id="createModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('createModal')">
    <div class="modal-box" style="max-width:440px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="cable" style="width:18px;height:18px;"></i> Register Bridge Agent</h3>
            <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.admin.bridge.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Agent Name <span style="color:red">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="100" placeholder="e.g. Main Campus EMR Bridge">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Purpose, deployment location, etc."></textarea>
                </div>
                <p style="font-size:0.8rem;color:#6b7280;margin:0;">
                    A unique agent key will be generated and shown once after registration.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Register & Generate Key</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; lucide.createIcons(); }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function copyKey(){
    const key = document.getElementById('newKey')?.textContent?.trim();
    if(key) navigator.clipboard.writeText(key).then(() => alert('Key copied to clipboard!'));
}
</script>
@endsection
