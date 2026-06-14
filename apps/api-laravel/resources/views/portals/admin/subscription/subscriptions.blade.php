@extends('layouts.portal')
@section('title', 'Organization Subscriptions — Admin')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="credit-card" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Subscriptions
            </h1>
            <p class="portal-page-subtitle">Facility & organization billing management</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('createSubModal')">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> New Subscription
        </button>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="check-circle" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#16a34a;">{{ $stats['active'] }}</div><div class="stat-card__label">Active</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="clock" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#2563eb;">{{ $stats['trialing'] }}</div><div class="stat-card__label">Trialing</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fffbeb;"><i data-lucide="alert-triangle" style="color:#d97706;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#d97706;">{{ $stats['past_due'] }}</div><div class="stat-card__label">Past Due</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;"><i data-lucide="trending-up" style="color:#7c3aed;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#7c3aed;">
                    FCFA {{ number_format($stats['mrr_kobo'] / 100, 0) }}
                </div>
                <div class="stat-card__label">MRR</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;"><i data-lucide="file-warning" style="color:#dc2626;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#dc2626;">{{ $stats['overdue_invoices'] }}</div><div class="stat-card__label">Overdue Invoices</div></div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-bar" style="margin-bottom:16px;">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by org name…" style="max-width:220px;">
        <select name="status" class="form-control" style="max-width:160px;">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="trialing" {{ request('status') === 'trialing' ? 'selected' : '' }}>Trialing</option>
            <option value="past_due" {{ request('status') === 'past_due' ? 'selected' : '' }}>Past Due</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
        </select>
        <button type="submit" class="btn btn--outline btn--sm">Filter</button>
        <a href="{{ route('portals.admin.subscription') }}" class="btn btn--outline btn--sm">Clear</a>
    </form>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Period</th>
                        <th>Days Left</th>
                        <th>Auto-Renew</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:0.88rem;">{{ $sub->organization_name }}</div>
                                @if($sub->billing_email)
                                    <div style="font-size:0.73rem;color:#9ca3af;">{{ $sub->billing_email }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="font-size:0.83rem;font-weight:600;">{{ $sub->plan->name ?? '—' }}</span>
                                @if($sub->plan)
                                    <div style="font-size:0.72rem;color:#9ca3af;">{{ ucfirst($sub->plan->billing_cycle) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--{{ $sub->statusColor() }}" style="font-size:0.72rem;">
                                    {{ ucfirst(str_replace('_',' ',$sub->status)) }}
                                </span>
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">
                                <div>{{ $sub->current_period_start->format('d M Y') }}</div>
                                <div>→ {{ $sub->current_period_end->format('d M Y') }}</div>
                            </td>
                            <td>
                                @php $days = $sub->daysUntilExpiry(); @endphp
                                <span style="font-size:0.85rem;font-weight:600;color:{{ $days < 7 ? '#dc2626' : ($days < 30 ? '#d97706' : '#16a34a') }};">
                                    {{ $days }}d
                                </span>
                            </td>
                            <td style="font-size:0.82rem;">
                                {!! $sub->auto_renew ? '<i data-lucide="check" style="width:14px;height:14px;vertical-align:-2px;"></i> Yes' : '<i data-lucide="x" style="width:14px;height:14px;vertical-align:-2px;"></i> No' !!}
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                    <a href="{{ route('portals.admin.subscription.detail', $sub->id) }}"
                                       class="btn btn--sm btn--outline" style="font-size:0.76rem;">View</a>
                                    @if($sub->status === 'active')
                                        <form method="POST" action="{{ route('portals.admin.subscription.pause', $sub->id) }}" style="display:inline;">
                                            @csrf
                                            <button class="btn btn--sm btn--warning" style="font-size:0.76rem;"
                                                    onclick="return confirm('Pause this subscription?')">Pause</button>
                                        </form>
                                    @elseif(in_array($sub->status, ['paused','past_due']))
                                        <form method="POST" action="{{ route('portals.admin.subscription.reactivate', $sub->id) }}" style="display:inline;">
                                            @csrf
                                            <button class="btn btn--sm btn--success" style="font-size:0.76rem;">Reactivate</button>
                                        </form>
                                    @endif
                                    @if(!in_array($sub->status, ['cancelled','expired']))
                                        <form method="POST" action="{{ route('portals.admin.subscription.renew', $sub->id) }}" style="display:inline;">
                                            @csrf
                                            <button class="btn btn--sm btn--outline" style="font-size:0.76rem;"
                                                    onclick="return confirm('Manually renew this subscription?')">Renew</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">
                                <i data-lucide="credit-card" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                                No subscriptions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())<div class="portal-card__footer">{{ $subscriptions->links() }}</div>@endif
    </div>

</div>

{{-- New Subscription Modal --}}
<div id="createSubModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('createSubModal')">
    <div class="modal-box" style="max-width:500px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="credit-card" style="width:16px;height:16px;"></i> New Subscription</h3>
            <button class="modal-close" onclick="closeModal('createSubModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.admin.subscription.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Facility <span style="color:red">*</span></label>
                    <select name="organization_id" class="form-control" required onchange="fillOrgName(this)">
                        <option value="">Select facility…</option>
                        @foreach($facilities as $f)
                            <option value="{{ $f->id }}" data-name="{{ $f->name }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="organization_name" id="orgNameInput">
                </div>
                <div class="form-group">
                    <label class="form-label">Plan <span style="color:red">*</span></label>
                    <select name="plan_id" class="form-control" required>
                        <option value="">Select plan…</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} — {{ $plan->priceFormatted() }}/{{ $plan->billing_cycle }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Billing Name</label>
                        <input type="text" name="billing_name" class="form-control" placeholder="Contact name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Billing Email</label>
                        <input type="email" name="billing_email" class="form-control" placeholder="billing@facility.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">Select…</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                            <option value="ussd">USSD</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Discount %</label>
                        <input type="number" name="discount_percent" class="form-control" min="0" max="100" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createSubModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Create Subscription</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; lucide.createIcons(); }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function fillOrgName(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('orgNameInput').value = opt.dataset.name || '';
}
</script>
@endsection
