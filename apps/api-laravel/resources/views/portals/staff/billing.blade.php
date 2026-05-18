@extends('layouts.portal')

@section('title', 'Billing — OpesCare Staff Portal')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Billing')

@section('sidebar_role_badge')
    <div class="sidebar-role-badge">
        <i data-lucide="stethoscope" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        {{ __('public.staff_portal.role_label', [], app()->getLocale()) ?: 'Clinical Staff' }}
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i> Dashboard</a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link"><i data-lucide="calendar-check-2"></i> Appointments</a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link"><i data-lucide="list-ordered"></i> Patient Queue</a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link"><i data-lucide="syringe"></i> Immunizations</a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link"><i data-lucide="send"></i> Referrals</a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link active"><i data-lucide="receipt"></i> Billing</a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link"><i data-lucide="headset"></i> Support</a>
@endsection

@section('sidebar_user_role', 'Clinical Staff')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Billing &amp; Invoices</h1>
        <p class="page-subtitle">Manage patient invoices, payments, and outstanding balances.</p>
    </div>
</div>

<!-- Filters -->
<div class="panel mb-6" style="margin-bottom:var(--p-space-6);">
    <form method="get" action="{{ route('portals.staff.billing') }}">
        <div class="filter-bar">
            <div class="form-group" style="flex:1;min-width:180px;">
                <div class="form-search">
                    <span class="search-icon"><i data-lucide="search"></i></span>
                    <input type="text" name="patient_id" class="form-control" placeholder="Patient Health ID…" value="{{ request('patient_id') }}" aria-label="Search by patient ID">
                </div>
            </div>
            <div class="form-group" style="min-width:180px;">
                <input type="text" name="facility_id" class="form-control" placeholder="Facility ID…" value="{{ request('facility_id') }}" aria-label="Filter by facility">
            </div>
            <div class="form-group" style="min-width:160px;">
                <select name="status" class="form-control" aria-label="Filter by invoice status">
                    <option value="">All Statuses</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="issued"    {{ request('status') === 'issued'    ? 'selected' : '' }}>Issued</option>
                    <option value="paid"      {{ request('status') === 'paid'      ? 'selected' : '' }}>Paid</option>
                    <option value="overdue"   {{ request('status') === 'overdue'   ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i data-lucide="filter"></i> Filter</button>
            <a href="{{ route('portals.staff.billing') }}" class="btn btn-secondary"><i data-lucide="x"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Invoices Table -->
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="receipt"></i>
            Invoices
        </h2>
        <span class="badge badge-primary">
            {{ $invoices instanceof \Illuminate\Pagination\LengthAwarePaginator ? $invoices->total() : count($invoices) }} invoices
        </span>
    </div>

    @if($invoices->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-lucide="file-text"></i>
            </div>
            <h3>No Invoices Found</h3>
            <p>No invoices match the current filters. Try adjusting the search criteria.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table" aria-label="Invoices list">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Patient ID</th>
                        <th>Responsibility</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th class="td-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td data-label="Invoice #">
                            <span class="td-mono">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td data-label="Patient">
                            <span class="td-mono">{{ $invoice->patient_id }}</span>
                        </td>
                        <td data-label="Responsibility">
                            <span class="td-strong">{{ number_format((float) $invoice->patient_responsibility_amount, 2) }}</span>
                        </td>
                        <td data-label="Paid">
                            <span style="color:var(--p-teal);font-weight:700;">{{ number_format((float) $invoice->paid_amount, 2) }}</span>
                        </td>
                        <td data-label="Balance">
                            @php $balance = (float) $invoice->balance_amount; @endphp
                            <span style="color:{{ $balance > 0 ? 'var(--p-danger)' : 'var(--p-teal)' }};font-weight:700;">
                                {{ number_format($balance, 2) }}
                            </span>
                        </td>
                        <td data-label="Status">
                            @php
                                $cls = match($invoice->status ?? 'draft') {
                                    'paid'      => 'badge-success',
                                    'overdue'   => 'badge-danger',
                                    'issued'    => 'badge-primary',
                                    'cancelled' => 'badge-neutral',
                                    default     => 'badge-warning',
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ ucfirst($invoice->status ?? 'draft') }}</span>
                        </td>
                        <td data-label="Actions" class="td-actions">
                            <div style="display:flex;gap:var(--p-space-2);">
                                <button class="btn btn-sm btn-secondary" title="View invoice" aria-label="View invoice">
                                    <i data-lucide="eye" style="width:0.85rem;height:0.85rem;"></i>
                                </button>
                                @if(in_array($invoice->status ?? '', ['issued', 'overdue']))
                                <button class="btn btn-sm btn-teal" title="Record payment" aria-label="Record payment">
                                    <i data-lucide="credit-card" style="width:0.85rem;height:0.85rem;"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($invoices instanceof \Illuminate\Pagination\LengthAwarePaginator && $invoices->hasPages())
        <div class="panel-footer" style="display:flex;align-items:center;justify-content:space-between;">
            <span>Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}</span>
            <div>{{ $invoices->links() }}</div>
        </div>
        @endif
    @endif
</div>

@endsection
