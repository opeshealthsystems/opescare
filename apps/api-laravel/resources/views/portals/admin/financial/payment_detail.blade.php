@extends('layouts.portal')
@section('title', 'Payment Detail — ' . $payment->payment_reference)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Payment Detail')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.financial.index') }}">Financial</a>
    <i data-lucide="chevron-right"></i>
    <a href="{{ route('portals.admin.financial.payments') }}">Payments</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $payment->payment_reference }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="receipt"></i></div>
    <h2 class="entity-head__title">Payment detail</h2>
    @if(in_array($payment->status,['successful','completed']))<span class="badge badge-success">Successful</span>
    @elseif($payment->status==='pending')<span class="badge badge-warning">Pending</span>
    @elseif($payment->status==='failed')<span class="badge badge-danger">Failed</span>
    @else<span class="badge badge-neutral">{{ ucfirst($payment->status) }}</span>@endif
    <div class="entity-head__spacer"></div>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back</a>
</div>

<p class="td-muted mb-6"><span class="mono">{{ $payment->payment_reference }}</span></p>

<div class="field-grid mb-6">

{{-- Payer Information --}}
<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="user"></i> Payer information</h3></div>
    <div class="panel-body">
        @if($payment->patient)
        <table class="kv-table">
            <tr><td>Full name</td><td class="kv-strong">{{ $payment->patient->first_name }} {{ $payment->patient->last_name }}</td></tr>
            <tr><td>Health ID</td><td><span class="mono">{{ $payment->patient->health_id }}</span></td></tr>
            <tr><td>Sex</td><td>{{ ucfirst($payment->patient->sex ?? '—') }}</td></tr>
            <tr><td>Date of birth</td><td>{{ $payment->patient->date_of_birth ? \Carbon\Carbon::parse($payment->patient->date_of_birth)->format('d M Y') : '—' }}</td></tr>
        </table>
        @else
        <p class="td-muted">Patient record not linked.</p>
        @endif
        @if($payment->payer_name)
        <div class="mt-6"><span class="td-muted">Gateway-registered name:</span> <strong>{{ $payment->payer_name }}</strong></div>
        @endif
        @if($payment->payer_phone)
        <div><span class="td-muted">Phone number used:</span> <strong class="mono">{{ $payment->payer_phone }}</strong></div>
        @endif
    </div>
</div>

{{-- Payment Gateway --}}
@php $icons=['mtn_momo'=>'MTN MoMo','orange_money'=>'Orange Money','cash'=>'Cash','card'=>'Card','insurance'=>'Insurance','bank_transfer'=>'Bank Transfer','wallet'=>'Platform Wallet']; $gwIcons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','bank_transfer'=>'landmark','wallet'=>'wallet']; $gw=$payment->gateway??$payment->method??''; @endphp
<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="credit-card"></i> Payment gateway</h3></div>
    <div class="panel-body">
        <table class="kv-table">
            <tr><td>Gateway</td><td><span class="cell-with-icon kv-strong"><i data-lucide="{{ $gwIcons[$gw] ?? 'credit-card' }}"></i> {{ $icons[$gw] ?? ucwords(str_replace('_',' ',$gw)) }}</span></td></tr>
            <tr><td>Method</td><td>{{ ucwords(str_replace('_',' ',$payment->method??'—')) }}</td></tr>
            <tr><td>Gateway txn ID</td><td><span class="mono">{{ $payment->gateway_transaction_id ?? '—' }}</span></td></tr>
            <tr><td>Gateway status</td><td>{{ $payment->gateway_status ?? '—' }}</td></tr>
            <tr><td>Our reference</td><td><span class="mono">{{ $payment->payment_reference }}</span></td></tr>
        </table>
    </div>
</div>

{{-- Amount & Service --}}
<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="coins"></i> Amount &amp; service</h3></div>
    <div class="panel-body">
        <table class="kv-table">
            <tr><td>Amount paid</td><td><strong class="kv-strong">{{ number_format($payment->amount,2) }} {{ $payment->currency ?? 'XAF' }}</strong></td></tr>
            <tr><td>Refunded</td><td>{{ number_format($payment->refunded_amount ?? 0,2) }} {{ $payment->currency ?? 'XAF' }}</td></tr>
            <tr><td>Net amount</td><td><strong>{{ number_format(($payment->amount - ($payment->refunded_amount ?? 0)),2) }} {{ $payment->currency ?? 'XAF' }}</strong></td></tr>
            <tr><td>Service type</td><td class="kv-strong">{{ ucwords(str_replace('_',' ',$payment->service_type??'—')) }}</td></tr>
            @if($payment->invoice)
            <tr><td>Invoice #</td><td><span class="mono">{{ $payment->invoice->invoice_number }}</span> <span class="badge badge-{{ $payment->invoice->status==='paid'?'success':'warning' }}">{{ ucfirst($payment->invoice->status) }}</span></td></tr>
            @endif
        </table>
    </div>
</div>

{{-- Device & Session --}}
@php $di=['web'=>'Web Browser','android'=>'Android','ios'=>'iOS','pos_terminal'=>'POS Terminal','ussd'=>'USSD']; $dIcons=['web'=>'globe','android'=>'smartphone','ios'=>'smartphone','pos_terminal'=>'printer','ussd'=>'phone']; @endphp
<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="monitor"></i> Device &amp; session</h3></div>
    <div class="panel-body">
        <table class="kv-table">
            <tr><td>Device type</td><td><span class="cell-with-icon kv-strong"><i data-lucide="{{ $dIcons[$payment->device_type??''] ?? 'monitor' }}"></i> {{ $di[$payment->device_type??''] ?? ucfirst($payment->device_type ?? '—') }}</span></td></tr>
            <tr><td>Device ID</td><td><span class="mono">{{ $payment->device_id ?? '—' }}</span></td></tr>
            <tr><td>IP address</td><td><span class="mono">{{ $payment->ip_address ?? '—' }}</span></td></tr>
            <tr><td>User agent</td><td class="mono">{{ $payment->user_agent ?? '—' }}</td></tr>
            <tr><td>Cashier</td><td>{{ $payment->cashier?->name ?? '—' }}</td></tr>
            <tr><td>Facility</td><td>{{ $payment->facility?->name ?? '—' }}</td></tr>
        </table>
    </div>
</div>

</div>{{-- end field-grid --}}

{{-- Timeline --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="clock"></i> Transaction timeline</h3></div>
    <div class="panel-body">
        <div class="stat-grid">
            <div class="stat-card"><div class="stat-card__label">Initiated</div><div class="stat-card__value">{{ $payment->initiated_at?->format('d M Y H:i:s') ?? $payment->created_at?->format('d M Y H:i:s') ?? '—' }}</div></div>
            <div class="stat-card"><div class="stat-card__label">Record created</div><div class="stat-card__value">{{ $payment->created_at?->format('d M Y H:i:s') ?? '—' }}</div></div>
            <div class="stat-card"><div class="stat-card__label">Confirmed</div><div class="stat-card__value">{{ $payment->confirmed_at?->format('d M Y H:i:s') ?? '—' }}</div></div>
            @if($payment->failed_at)
            <div class="stat-card"><div class="stat-card__label">Failed at</div><div class="stat-card__value">{{ $payment->failed_at?->format('d M Y H:i:s') }}</div></div>
            @endif
            @if($payment->failure_reason)
            <div class="stat-card"><div class="stat-card__label">Failure reason</div><div class="stat-card__value">{{ $payment->failure_reason }}</div></div>
            @endif
        </div>
    </div>
</div>

{{-- Invoice Line Items --}}
@if($payment->invoice && $payment->invoice->items->count())
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="receipt"></i> Invoice line items ({{ $payment->invoice->invoice_number }})</h3></div>
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Service code</th><th>Description</th><th>Qty</th><th>Unit price</th><th>Discount</th><th>Line total</th></tr></thead><tbody>
    @foreach($payment->invoice->items as $item)
    <tr>
        <td data-label="Service code"><span class="mono">{{ $item->service_code ?? '—' }}</span></td>
        <td data-label="Description">{{ $item->description }}</td>
        <td data-label="Qty">{{ $item->quantity }}</td>
        <td data-label="Unit price">{{ number_format($item->unit_price,2) }}</td>
        <td data-label="Discount">{{ number_format($item->discount_amount ?? 0,2) }}</td>
        <td data-label="Line total"><strong>{{ number_format($item->line_total_amount,2) }}</strong></td>
    </tr>
    @endforeach
    </tbody></table></div>
</div>
@endif

{{-- Receipts --}}
@if($payment->receipts->count())
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="receipt"></i> Receipts</h3></div>
    <div class="panel-body">
        @foreach($payment->receipts as $r)
        <div class="list-row">
            <span class="mono">{{ $r->receipt_number }}</span>
            <strong class="list-row__value">{{ number_format($r->amount,2) }} XAF</strong>
            <span class="td-muted">{{ $r->issued_at?->format('d M Y H:i') }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Reversals --}}
@if($payment->reversals->count())
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="refresh-cw"></i> Reversals / refunds</h3></div>
    <div class="panel-body">
        @foreach($payment->reversals as $rev)
        <div class="list-row">
            <span class="list-row__main">
                <span class="badge badge-danger">- {{ number_format($rev->amount,2) }} XAF</span>
                <span>Reason: {{ $rev->reason ?? '—' }}</span>
                <span class="list-row__meta">By: {{ $rev->actor?->name ?? 'System' }}</span>
            </span>
            <span class="td-muted">{{ $rev->created_at?->format('d M Y H:i') }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Raw Gateway Metadata --}}
@if($payment->gateway_metadata)
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="microscope"></i> Raw gateway metadata</h3></div>
    <div class="panel-body">
        <pre class="report-pre">{{ json_encode($payment->gateway_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
</div>
@endif

@endsection
