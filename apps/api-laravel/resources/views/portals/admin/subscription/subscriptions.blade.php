@extends('layouts.portal')
@section('title', 'Organization Subscriptions — Admin')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Subscriptions')
@section('content')

@php $badgeMap = ['success'=>'success','warning'=>'warning','danger'=>'danger','info'=>'primary','default'=>'neutral']; @endphp

<div class="breadcrumb">
    <a href="{{ route('portals.admin.subscription') }}">Subscriptions</a>
    <i data-lucide="chevron-right"></i>
    <span>Directory</span>
</div>

<div class="page-head">
    <h2>Subscriptions</h2>
    <div class="page-head__spacer"></div>
    <button type="button" class="btn btn-primary" onclick="opOpenModal('createSubModal')"><i data-lucide="plus"></i> New subscription</button>
</div>

<p class="td-muted mb-6">Facility &amp; organization billing management.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- KPI Strip --}}
<div class="stat-grid mb-6">
    <div class="stat-card"><div class="stat-card__label">Active</div><div class="stat-card__value">{{ $stats['active'] }}</div></div>
    <div class="stat-card"><div class="stat-card__label">Trialing</div><div class="stat-card__value">{{ $stats['trialing'] }}</div></div>
    <div class="stat-card"><div class="stat-card__label">Past due</div><div class="stat-card__value">{{ $stats['past_due'] }}</div></div>
    <div class="stat-card"><div class="stat-card__label">MRR</div><div class="stat-card__value">FCFA {{ number_format($stats['mrr_kobo'] / 100, 0) }}</div></div>
    <div class="stat-card"><div class="stat-card__label">Overdue invoices</div><div class="stat-card__value">{{ $stats['overdue_invoices'] }}</div></div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('portals.admin.subscription') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by org name…" aria-label="Search">
    </label>
    <select name="status" class="filter-select" aria-label="Status">
        <option value="">All statuses</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="trialing" {{ request('status') === 'trialing' ? 'selected' : '' }}>Trialing</option>
        <option value="past_due" {{ request('status') === 'past_due' ? 'selected' : '' }}>Past due</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.subscription') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Organization</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Period</th>
                    <th>Days left</th>
                    <th>Auto-renew</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                    <tr>
                        <td data-label="Organization">
                            <div class="td-strong">{{ $sub->organization_name }}</div>
                            @if($sub->billing_email)<div class="td-muted">{{ $sub->billing_email }}</div>@endif
                        </td>
                        <td data-label="Plan">
                            <span class="td-strong">{{ $sub->plan->name ?? '—' }}</span>
                            @if($sub->plan)<div class="td-muted">{{ ucfirst($sub->plan->billing_cycle) }}</div>@endif
                        </td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $badgeMap[$sub->statusColor()] ?? 'neutral' }}">{{ ucfirst(str_replace('_',' ',$sub->status)) }}</span>
                        </td>
                        <td data-label="Period">
                            <div>{{ $sub->current_period_start->format('d M Y') }}</div>
                            <div class="td-muted">→ {{ $sub->current_period_end->format('d M Y') }}</div>
                        </td>
                        <td data-label="Days left">
                            @php $days = $sub->daysUntilExpiry(); $dc = $days < 7 ? 'danger' : ($days < 30 ? 'warning' : 'success'); @endphp
                            <span class="badge badge-{{ $dc }}">{{ $days }}d</span>
                        </td>
                        <td data-label="Auto-renew">
                            @if($sub->auto_renew)<span class="cell-with-icon"><i data-lucide="check"></i> Yes</span>@else<span class="cell-with-icon"><i data-lucide="x"></i> No</span>@endif
                        </td>
                        <td class="row-actions" data-label="Actions">
                            <div class="row-actions-inline">
                                <a href="{{ route('portals.admin.subscription.detail', $sub->id) }}" class="btn btn-secondary btn-sm">View</a>
                                @if($sub->status === 'active')
                                    <button type="button" class="btn btn-warning btn-sm" onclick="opOpenModal('pause-modal-{{ $sub->id }}')">Pause</button>
                                @elseif(in_array($sub->status, ['paused','past_due']))
                                    <form method="POST" action="{{ route('portals.admin.subscription.reactivate', $sub->id) }}" class="inline-form">@csrf
                                        <button type="submit" class="btn btn-success btn-sm">Reactivate</button>
                                    </form>
                                @endif
                                @if(!in_array($sub->status, ['cancelled','expired']))
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="opOpenModal('renew-modal-{{ $sub->id }}')">Renew</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="td-muted empty-cell">No subscriptions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subscriptions->hasPages())<div class="panel-body">{{ $subscriptions->links() }}</div>@endif
</div>

{{-- Confirm modals --}}
@foreach($subscriptions as $sub)
    @if($sub->status === 'active')
    <div id="pause-modal-{{ $sub->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="pause-modal-title-{{ $sub->id }}">
            <h3 class="modal__title" id="pause-modal-title-{{ $sub->id }}"><i data-lucide="pause-circle"></i> Pause subscription</h3>
            <form method="POST" action="{{ route('portals.admin.subscription.pause', $sub->id) }}">@csrf
                <div class="modal__body"><p>Pause the subscription for <strong>{{ $sub->organization_name }}</strong>?</p></div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('pause-modal-{{ $sub->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-warning">Pause</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @if(!in_array($sub->status, ['cancelled','expired']))
    <div id="renew-modal-{{ $sub->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="renew-modal-title-{{ $sub->id }}">
            <h3 class="modal__title" id="renew-modal-title-{{ $sub->id }}"><i data-lucide="refresh-cw"></i> Renew subscription</h3>
            <form method="POST" action="{{ route('portals.admin.subscription.renew', $sub->id) }}">@csrf
                <div class="modal__body"><p>Manually renew the subscription for <strong>{{ $sub->organization_name }}</strong>?</p></div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('renew-modal-{{ $sub->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Renew</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

{{-- New Subscription Modal --}}
<div id="createSubModal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="createSubModal-title">
        <h3 class="modal__title" id="createSubModal-title"><i data-lucide="credit-card"></i> New subscription</h3>
        <form method="POST" action="{{ route('portals.admin.subscription.store') }}">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Facility</label>
                    <select name="organization_id" class="form-control" required onchange="fillOrgName(this)">
                        <option value="">Select facility…</option>
                        @foreach($facilities as $f)
                            <option value="{{ $f->id }}" data-name="{{ $f->name }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="organization_name" id="orgNameInput">
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Plan</label>
                    <select name="plan_id" class="form-control" required>
                        <option value="">Select plan…</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} — {{ $plan->priceFormatted() }}/{{ $plan->billing_cycle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-grid">
                    <div class="form-group">
                        <label class="form-label">Billing name</label>
                        <input type="text" name="billing_name" class="form-control" placeholder="Contact name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Billing email</label>
                        <input type="email" name="billing_email" class="form-control" placeholder="billing@facility.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment method</label>
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
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('createSubModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create subscription</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
function fillOrgName(sel){
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('orgNameInput').value = opt.dataset.name || '';
}
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
