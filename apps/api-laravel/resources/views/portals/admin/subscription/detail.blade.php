@extends('layouts.portal')
@section('title', 'Subscription Detail — ' . $subscription->organization_name)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Subscriptions')
@section('content')

@php $badgeMap = ['success'=>'success','warning'=>'warning','danger'=>'danger','info'=>'primary','default'=>'neutral']; @endphp

<div class="breadcrumb">
    <a href="{{ route('portals.admin.subscription') }}">Subscriptions</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $subscription->organization_name }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="credit-card"></i></div>
    <h2 class="entity-head__title">{{ $subscription->organization_name }}</h2>
    <span class="badge badge-{{ $badgeMap[$subscription->statusColor()] ?? 'neutral' }}">{{ ucfirst(str_replace('_',' ',$subscription->status)) }}</span>
    <div class="entity-head__spacer"></div>
    <a href="{{ route('portals.admin.subscription') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> All subscriptions</a>
    @if(!in_array($subscription->status, ['cancelled','expired']))
        <button type="button" class="btn btn-danger" onclick="opOpenModal('cancelModal')"><i data-lucide="x-circle"></i> Cancel</button>
    @endif
</div>

<p class="td-muted mb-6">{{ $subscription->plan->name ?? '—' }}</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

<div class="field-grid mb-6">

    {{-- Subscription Info --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="info"></i> Subscription details</h3></div>
        <div class="panel-body">
            <table class="kv-table">
                <tr><td>Organization</td><td class="kv-strong">{{ $subscription->organization_name }}</td></tr>
                <tr><td>Plan</td><td class="kv-strong">{{ $subscription->plan->name ?? '—' }}</td></tr>
                <tr><td>Billing cycle</td><td>{{ ucfirst($subscription->plan->billing_cycle ?? '—') }}</td></tr>
                <tr><td>Price</td><td class="kv-strong">{{ $subscription->plan->priceFormatted() ?? '—' }}</td></tr>
                <tr><td>Discount</td><td>{{ $subscription->discount_percent }}%</td></tr>
                <tr><td>Period</td><td>{{ $subscription->current_period_start->format('d M Y') }} → {{ $subscription->current_period_end->format('d M Y') }}</td></tr>
                @php $days = $subscription->daysUntilExpiry(); @endphp
                <tr><td>Days left</td><td><span class="badge badge-{{ $days < 7 ? 'danger' : 'success' }}">{{ $days }} days</span></td></tr>
                <tr><td>Auto-renew</td><td>@if($subscription->auto_renew)<span class="cell-with-icon"><i data-lucide="check"></i> Yes</span>@else<span class="cell-with-icon"><i data-lucide="x"></i> No</span>@endif</td></tr>
                @if($subscription->billing_email)
                    <tr><td>Billing contact</td><td>{{ $subscription->billing_name }}<br><span class="td-muted">{{ $subscription->billing_email }}</span></td></tr>
                @endif
                @if($subscription->notes)
                    <tr><td>Notes</td><td class="td-muted">{{ $subscription->notes }}</td></tr>
                @endif
            </table>

            @if(!in_array($subscription->status, ['cancelled','expired']))
                <div class="mt-6">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="opOpenModal('changePlanModal')"><i data-lucide="repeat"></i> Change plan</button>
                </div>
            @endif
        </div>
    </div>

    {{-- Module Entitlements --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="layers"></i> Module entitlements</h3></div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Module</th><th>Status</th><th>Granted</th></tr></thead>
                <tbody>
                    @forelse($subscription->moduleEntitlements as $ent)
                        <tr>
                            <td data-label="Module"><span class="mono">{{ $ent->module_key }}</span></td>
                            <td data-label="Status"><span class="badge badge-{{ $ent->isActive() ? 'success' : 'neutral' }}">{{ $ent->isActive() ? 'Active' : 'Revoked' }}</span></td>
                            <td data-label="Granted">{{ $ent->granted_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="td-muted empty-cell">No entitlements</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Invoices --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="file-text"></i> Invoices</h3></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Invoice #</th><th>Date</th><th>Due</th><th>Amount</th><th>Status</th><th class="row-actions">Actions</th></tr></thead>
            <tbody>
                @forelse($subscription->invoices->sortByDesc('invoice_date') as $inv)
                    <tr>
                        <td data-label="Invoice #"><span class="mono">{{ $inv->invoice_number }}</span></td>
                        <td data-label="Date">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td data-label="Due">@if($inv->isOverdue())<span class="badge badge-danger">{{ $inv->due_date->format('d M Y') }}</span>@else{{ $inv->due_date->format('d M Y') }}@endif</td>
                        <td data-label="Amount"><strong>{{ $inv->totalFormatted() }}</strong></td>
                        <td data-label="Status"><span class="badge badge-{{ $badgeMap[$inv->statusColor()] ?? 'neutral' }}">{{ ucfirst($inv->status) }}</span></td>
                        <td class="row-actions" data-label="Actions">
                            @if(in_array($inv->status, ['sent','overdue']))
                                <button type="button" class="btn btn-success btn-sm" onclick="openPayModal('{{ $inv->id }}')">Mark paid</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="td-muted empty-cell">No invoices.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Change Plan Modal (plan tiers) --}}
@if(!in_array($subscription->status, ['cancelled','expired']))
<div id="changePlanModal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="changePlanModal-title">
        <h3 class="modal__title" id="changePlanModal-title"><i data-lucide="repeat"></i> Change plan</h3>
        <form method="POST" action="{{ route('portals.admin.subscription.change_plan', $subscription->id) }}">
            @csrf
            <div class="modal__body">
                <p class="td-muted">Select a new plan for <strong>{{ $subscription->organization_name }}</strong>.</p>
                <div class="plan-grid">
                    @foreach($plans as $p)
                    <label class="plan-tier {{ $p->id === $subscription->plan_id ? 'plan-tier--current' : '' }}">
                        <span class="plan-tier__name">
                            <input type="radio" name="plan_id" value="{{ $p->id }}" {{ $p->id === $subscription->plan_id ? 'checked' : '' }}>
                            {{ $p->name }}
                        </span>
                        <span class="plan-tier__price">{{ $p->priceFormatted() }}<small>/{{ $p->billing_cycle }}</small></span>
                        @if($p->id === $subscription->plan_id)<span class="badge badge-primary">Current</span>@endif
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('changePlanModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Change plan</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Cancel Modal --}}
<div id="cancelModal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="cancelModal-title">
        <h3 class="modal__title" id="cancelModal-title"><i data-lucide="x-circle"></i> Cancel subscription</h3>
        <form method="POST" action="{{ route('portals.admin.subscription.cancel', $subscription->id) }}">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Cancellation reason</label>
                    <textarea name="reason" class="form-control" rows="3" required minlength="5" maxlength="500" placeholder="Reason for cancellation…"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('cancelModal')">Abort</button>
                <button type="submit" class="btn btn-danger">Confirm cancellation</button>
            </div>
        </form>
    </div>
</div>

{{-- Mark Paid Modal --}}
<div id="payModal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="payModal-title">
        <h3 class="modal__title" id="payModal-title"><i data-lucide="check-circle"></i> Mark invoice paid</h3>
        <form id="payForm" method="POST" action="">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Payment reference</label>
                    <input type="text" name="payment_reference" class="form-control" required placeholder="e.g. TRN-20260519-001">
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Payment method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="ussd">USSD</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('payModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm payment</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
function openPayModal(invoiceId){
    const base = '{{ url("portals/admin/subscription/invoices") }}';
    document.getElementById('payForm').action = base + '/' + invoiceId + '/mark-paid';
    opOpenModal('payModal');
}
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
