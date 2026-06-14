@extends('layouts.portal')
@section('title', 'Subscription Invoices — Admin')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Subscription Invoices')
@section('content')

@php $badgeMap = ['success'=>'success','warning'=>'warning','danger'=>'danger','info'=>'primary','default'=>'neutral']; @endphp

<div class="breadcrumb">
    <a href="{{ route('portals.admin.subscription') }}">Subscriptions</a>
    <i data-lucide="chevron-right"></i>
    <span>Invoices</span>
</div>

<div class="page-head">
    <h2>Subscription invoices</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.subscription') }}" class="btn btn-secondary btn-sm"><i data-lucide="arrow-left"></i> Subscriptions</a>
</div>

<p class="td-muted mb-6">Platform billing invoices for all facilities.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

{{-- KPI Strip --}}
<div class="stat-grid mb-6">
    <div class="stat-card"><div class="stat-card__label">Paid this month</div><div class="stat-card__value">FCFA {{ number_format($stats['paid_this_month'] / 100, 0) }}</div></div>
    <div class="stat-card"><div class="stat-card__label">Pending</div><div class="stat-card__value">{{ $stats['pending_count'] }}</div></div>
    <div class="stat-card"><div class="stat-card__label">Overdue</div><div class="stat-card__value">{{ $stats['overdue_count'] }}</div></div>
    <div class="stat-card"><div class="stat-card__label">Overdue amount</div><div class="stat-card__value">FCFA {{ number_format($stats['overdue_amount'] / 100, 0) }}</div></div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('portals.admin.subscription.invoices') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice #…" aria-label="Search">
    </label>
    <select name="status" class="filter-select" aria-label="Status">
        <option value="">All statuses</option>
        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent / Pending</option>
        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
        <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.subscription.invoices') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Organization</th>
                    <th>Invoice date</th>
                    <th>Due date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td data-label="Invoice #"><span class="mono">{{ $inv->invoice_number }}</span></td>
                        <td data-label="Organization">{{ $inv->subscription?->organization_name ?? '—' }}</td>
                        <td data-label="Invoice date">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td data-label="Due date">
                            @if($inv->isOverdue())
                                <span class="badge badge-danger">{{ $inv->due_date->format('d M Y') }} · Overdue</span>
                            @else
                                {{ $inv->due_date->format('d M Y') }}
                            @endif
                        </td>
                        <td data-label="Amount"><strong>{{ $inv->totalFormatted() }}</strong></td>
                        <td data-label="Status"><span class="badge badge-{{ $badgeMap[$inv->statusColor()] ?? 'neutral' }}">{{ ucfirst($inv->status) }}</span></td>
                        <td class="row-actions" data-label="Actions">
                            <div class="row-actions-inline">
                                @if(in_array($inv->status, ['sent','overdue']))
                                    <button type="button" class="btn btn-success btn-sm" onclick="openPayModal('{{ $inv->id }}')"><i data-lucide="check"></i> Mark paid</button>
                                @endif
                                <a href="{{ route('portals.admin.subscription.detail', $inv->subscription_id) }}" class="btn btn-secondary btn-sm">View sub</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="td-muted empty-cell">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())<div class="panel-body">{{ $invoices->links() }}</div>@endif
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
