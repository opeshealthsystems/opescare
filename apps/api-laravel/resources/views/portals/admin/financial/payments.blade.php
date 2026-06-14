@extends('layouts.portal')
@section('title', 'All Payments')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Payments')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.financial.index') }}">Financial</a>
    <i data-lucide="chevron-right"></i>
    <span>Payments</span>
</div>

<div class="page-head">
    <h2>All payments</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-secondary btn-sm"><i data-lucide="layout-dashboard"></i> Dashboard</a>
    <a href="{{ route('portals.admin.financial.report.by_service') }}" class="btn btn-secondary btn-sm"><i data-lucide="bar-chart-3"></i> By service</a>
</div>

<p class="td-muted mb-6">Full transaction log — every payment with complete payer, gateway, device, and service details.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.admin.financial.payments') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference or phone..." aria-label="Search">
    </label>
    <select name="gateway" class="filter-select" aria-label="Gateway">
        <option value="">All gateways</option>
        @foreach(['mtn_momo'=>'MTN MoMo','orange_money'=>'Orange Money','cash'=>'Cash','card'=>'Card','insurance'=>'Insurance','bank_transfer'=>'Bank Transfer','wallet'=>'Wallet'] as $k=>$l)
        <option value="{{ $k }}" {{ request('gateway')===$k?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <select name="status" class="filter-select" aria-label="Status">
        <option value="">All statuses</option>
        @foreach(['successful'=>'Successful','pending'=>'Pending','failed'=>'Failed','refunded'=>'Refunded','completed'=>'Completed'] as $k=>$l)
        <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <select name="service_type" class="filter-select" aria-label="Service type">
        <option value="">All services</option>
        @foreach(['consultation','lab_test','pharmacy','radiology','admission','subscription','emergency','procedure','dental','vaccination','manual_override'] as $s)
        <option value="{{ $s }}" {{ request('service_type')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <select name="device_type" class="filter-select" aria-label="Device">
        <option value="">All devices</option>
        @foreach(['web','android','ios','pos_terminal','ussd'] as $d)
        <option value="{{ $d }}" {{ request('device_type')===$d?'selected':'' }}>{{ strtoupper($d) }}</option>
        @endforeach
    </select>
    <select name="facility_id" class="filter-select" aria-label="Facility">
        <option value="">All facilities</option>
        @foreach($facilities as $f)<option value="{{ $f->id }}" {{ request('facility_id')==$f->id?'selected':'' }}>{{ $f->name }}</option>@endforeach
    </select>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="from_date" value="{{ request('from_date') }}" aria-label="From date">
    </label>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="to_date" value="{{ request('to_date') }}" aria-label="To date">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="panel mb-6">
    <div class="panel-body summary-bar">
        <span class="td-muted">{{ $summaryCount }} transactions</span>
        <span class="kv-strong">Collected: {{ number_format($summaryTotal,0,'.',',') }} XAF (filtered)</span>
    </div>
</div>

<div class="panel">
    <div class="table-wrapper">
    <table class="data-table">
        <thead><tr>
            <th>Reference</th>
            <th>Payer</th>
            <th>Phone used</th>
            <th>Gateway</th>
            <th>Gateway txn ID</th>
            <th>Service</th>
            <th>Amount (XAF)</th>
            <th>Status</th>
            <th>Device</th>
            <th>Facility</th>
            <th>Cashier</th>
            <th>Date &amp; time</th>
            <th class="row-actions"></th>
        </tr></thead>
        <tbody>
        @forelse($payments as $p)
        @php
            $icons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','bank_transfer'=>'landmark','wallet'=>'wallet']; $gw=$p->gateway??$p->method??'';
            $di=['web'=>'globe','android'=>'smartphone','ios'=>'smartphone','pos_terminal'=>'printer','ussd'=>'phone'];
        @endphp
        <tr>
            <td data-label="Reference"><span class="mono">{{ $p->payment_reference }}</span></td>
            <td data-label="Payer">
                @if($p->patient)
                    <div class="td-strong">{{ $p->patient->first_name }} {{ $p->patient->last_name }}</div>
                    <div class="td-muted">{{ $p->patient->health_id }}</div>
                @elseif($p->payer_name)
                    {{ $p->payer_name }}
                @else
                    <span class="td-muted">—</span>
                @endif
            </td>
            <td data-label="Phone used"><span class="mono">{{ $p->payer_phone ?? '—' }}</span></td>
            <td data-label="Gateway">
                <span class="cell-with-icon" title="{{ $gw }}">
                    <i data-lucide="{{ $icons[$gw] ?? 'credit-card' }}"></i>
                    <span>{{ ucwords(str_replace('_',' ',$gw)) }}</span>
                </span>
            </td>
            <td data-label="Gateway txn ID"><span class="mono" title="{{ $p->gateway_transaction_id }}">{{ $p->gateway_transaction_id ?? '—' }}</span></td>
            <td data-label="Service">{{ ucwords(str_replace('_',' ',$p->service_type??'—')) }}</td>
            <td data-label="Amount"><strong>{{ number_format($p->amount,0,'.',',') }}</strong>
                @if($p->refunded_amount > 0)
                <div class="td-muted">-{{ number_format($p->refunded_amount,0,'.',',') }} refund</div>
                @endif
            </td>
            <td data-label="Status">
                @if(in_array($p->status,['successful','completed']))<span class="badge badge-success">{{ ucfirst($p->status) }}</span>
                @elseif($p->status==='pending')<span class="badge badge-warning">Pending</span>
                @elseif($p->status==='failed')<span class="badge badge-danger">Failed</span>
                @else<span class="badge badge-neutral">{{ ucfirst($p->status) }}</span>@endif
            </td>
            <td data-label="Device">
                <span class="cell-with-icon"><i data-lucide="{{ $di[$p->device_type??''] ?? 'monitor' }}"></i> {{ strtoupper($p->device_type ?? '—') }}</span>
            </td>
            <td data-label="Facility">{{ $p->facility?->name ?? '—' }}</td>
            <td data-label="Cashier">{{ $p->cashier?->name ?? '—' }}</td>
            <td data-label="Date &amp; time">
                {{ $p->created_at?->format('d M Y') }}<br>
                <span class="td-muted">{{ $p->created_at?->format('H:i:s') }}</span>
            </td>
            <td class="row-actions" data-label="Actions"><a href="{{ route('portals.admin.financial.payment.detail',$p->id) }}" class="btn btn-ghost btn-sm">Details</a></td>
        </tr>
        @empty
        <tr><td colspan="13" class="td-muted empty-cell">No payments found for this filter.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="panel-body">{{ $payments->links() }}</div>
</div>
@endsection
