@extends('layouts.portal')

@section('title', __('public.staff_portal.billing_title', [], app()->getLocale()) ?: 'Billing')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link active">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i>
        <span>{{ __('public.portal.nav_insurance', [], app()->getLocale()) ?: 'Insurance' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', __('public.staff_portal.billing_title', [], app()->getLocale()) ?: 'Billing')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.staff_portal.billing_title', [], app()->getLocale()) ?: 'Billing' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.billing_subtitle', [], app()->getLocale()) ?: 'Review patient invoices and record payments.' }}</p>
    </div>
    <a href="{{ route('portals.staff.billing.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="file-plus" style="width:14px;height:14px;"></i>
        {{ __('public.staff_portal.btn_create_invoice', [], app()->getLocale()) ?: 'Create Invoice' }}
    </a>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.billing') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <div class="form-search">
        <span class="search-icon"><i data-lucide="search" style="width:13px;height:13px;"></i></span>
        <input type="text" name="patient_id" class="form-control"
            placeholder="{{ __('public.staff_portal.filter_patient_id', [], app()->getLocale()) ?: 'Patient ID…' }}"
            value="{{ request('patient_id') }}" style="padding-left:2.1rem;">
    </div>
    <select name="status" class="form-control">
        <option value="">{{ __('public.staff_portal.filter_all_statuses', [], app()->getLocale()) ?: 'All Statuses' }}</option>
        @foreach(['draft','issued','partial','paid','cancelled','refunded'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords($s) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i>
        {{ __('public.staff_portal.filter_apply', [], app()->getLocale()) ?: 'Filter' }}
    </button>
    <a href="{{ route('portals.staff.billing') }}" class="btn btn-ghost btn-sm">
        {{ __('public.staff_portal.filter_clear', [], app()->getLocale()) ?: 'Clear' }}
    </a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if(count($invoices) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="file-text"></i></div>
                <h3>{{ __('public.staff_portal.no_invoices_title', [], app()->getLocale()) ?: 'No Invoices Found' }}</h3>
                <p>{{ __('public.staff_portal.no_invoices_desc', [], app()->getLocale()) ?: 'There are no invoices matching your current filters.' }}</p>
                <a href="{{ route('portals.staff.billing.create') }}" class="btn btn-primary btn-sm" style="margin-top:1rem;">
                    {{ __('public.staff_portal.btn_create_invoice', [], app()->getLocale()) ?: 'Create Invoice' }}
                </a>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.staff_portal.col_invoice_no', [], app()->getLocale()) ?: 'Invoice #' }}</th>
                            <th>{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}</th>
                            <th>{{ __('public.staff_portal.col_amount', [], app()->getLocale()) ?: 'Amount' }}</th>
                            <th>{{ __('public.staff_portal.col_balance', [], app()->getLocale()) ?: 'Balance' }}</th>
                            <th>{{ __('public.staff_portal.col_datetime', [], app()->getLocale()) ?: 'Issued' }}</th>
                            <th>{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}</th>
                            <th>{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        @php
                            $statusBadge = match($invoice->status ?? '') {
                                'paid'      => 'badge-success',
                                'issued'    => 'badge-primary',
                                'partial'   => 'badge-warning',
                                'cancelled' => 'badge-danger',
                                'refunded'  => 'badge-neutral',
                                default     => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.staff_portal.col_invoice_no', [], app()->getLocale()) ?: 'Invoice #' }}">
                                <span style="font-family:monospace;font-size:var(--p-text-xs);">{{ $invoice->invoice_number ?? '--' }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_patient_id', [], app()->getLocale()) ?: 'Patient ID' }}">
                                <span style="font-family:monospace;font-size:var(--p-text-xs);">{{ $invoice->patient_id ?? '--' }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_amount', [], app()->getLocale()) ?: 'Amount' }}">
                                {{ number_format($invoice->patient_responsibility_amount ?? 0, 2) }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_balance', [], app()->getLocale()) ?: 'Balance' }}">
                                <strong>{{ number_format($invoice->balance_amount ?? 0, 2) }}</strong>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_datetime', [], app()->getLocale()) ?: 'Issued' }}">
                                {{ $invoice->issued_at ? \Carbon\Carbon::parse($invoice->issued_at)->format('M d, Y') : '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}">
                                <span class="badge {{ $statusBadge }}">{{ ucwords($invoice->status ?? '') }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}">
                                @if(in_array($invoice->status ?? '', ['issued','partial']))
                                    <button type="button" class="btn btn-primary btn-xs"
                                        onclick="openPayModal('{{ $invoice->id }}','{{ $invoice->balance_amount ?? 0 }}')">
                                        <i data-lucide="credit-card" style="width:11px;height:11px;"></i>
                                        {{ __('public.staff_portal.btn_record_payment', [], app()->getLocale()) ?: 'Record Payment' }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Payment Modal --}}
<div id="pay-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">
            {{ __('public.staff_portal.btn_record_payment', [], app()->getLocale()) ?: 'Record Payment' }}
        </h3>
        <form id="pay-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">{{ __('public.staff_portal.lbl_amount', [], app()->getLocale()) ?: 'Amount' }} *</label>
                <input type="number" name="amount" id="pay-amount" class="form-control" step="0.01" min="0.01" required>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">{{ __('public.staff_portal.lbl_payment_method', [], app()->getLocale()) ?: 'Payment Method' }} *</label>
                <select name="payment_method" class="form-control" required>
                    <option value="cash">{{ __('public.staff_portal.method_cash', [], app()->getLocale()) ?: 'Cash' }}</option>
                    <option value="card">{{ __('public.staff_portal.method_card', [], app()->getLocale()) ?: 'Card' }}</option>
                    <option value="mobile_money">{{ __('public.staff_portal.method_mobile_money', [], app()->getLocale()) ?: 'Mobile Money' }}</option>
                    <option value="bank_transfer">{{ __('public.staff_portal.method_bank_transfer', [], app()->getLocale()) ?: 'Bank Transfer' }}</option>
                    <option value="insurance">{{ __('public.staff_portal.method_insurance', [], app()->getLocale()) ?: 'Insurance' }}</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">{{ __('public.staff_portal.lbl_reference', [], app()->getLocale()) ?: 'Reference' }}</label>
                <input type="text" name="reference_number" class="form-control" placeholder="Optional reference…">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closePayModal()">Back</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="check" style="width:13px;height:13px;"></i>
                    {{ __('public.staff_portal.btn_record_payment', [], app()->getLocale()) ?: 'Record Payment' }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openPayModal(invoiceId, balance) {
        var form   = document.getElementById('pay-form');
        var amount = document.getElementById('pay-amount');
        var base   = '{{ url('/portals/staff/billing') }}';
        form.setAttribute('action', base + '/' + invoiceId + '/pay');
        amount.value = parseFloat(balance).toFixed(2);
        document.getElementById('pay-modal').style.display = 'flex';
    }
    function closePayModal() { document.getElementById('pay-modal').style.display = 'none'; }
    document.getElementById('pay-modal').addEventListener('click', function(e) {
        if (e.target === this) closePayModal();
    });
</script>
@endsection
