@extends('layouts.portal')
@section('title', 'Payment Detail — ' . $payment->payment_reference)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Payment Detail')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Payment Detail</h1>
        <p class="page-subtitle" style="font-family:monospace;">{{ $payment->payment_reference }}</p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
        @if(in_array($payment->status,['successful','completed']))<span class="badge badge-success" style="font-size:.9rem;padding:.4rem .9rem;">Successful</span>
        @elseif($payment->status==='pending')<span class="badge badge-warning" style="font-size:.9rem;padding:.4rem .9rem;">Pending</span>
        @elseif($payment->status==='failed')<span class="badge badge-danger" style="font-size:.9rem;padding:.4rem .9rem;">Failed</span>
        @else<span class="badge" style="font-size:.9rem;padding:.4rem .9rem;background:var(--p-surface-3);">{{ ucfirst($payment->status) }}</span>@endif
        <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">

{{-- Payer Information --}}
<div class="panel" style="padding:1.25rem;">
    <h3 style="margin-top:0;font-size:.95rem;border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="user" style="width:14px;height:14px;vertical-align:-2px;"></i> Payer Information</h3>
    @if($payment->patient)
    <table style="width:100%;font-size:.87rem;border-collapse:collapse;">
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;width:40%;">Full Name</td><td><strong>{{ $payment->patient->first_name }} {{ $payment->patient->last_name }}</strong></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Health ID</td><td><code>{{ $payment->patient->health_id }}</code></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Sex</td><td>{{ ucfirst($payment->patient->sex ?? '—') }}</td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Date of Birth</td><td>{{ $payment->patient->date_of_birth ? \Carbon\Carbon::parse($payment->patient->date_of_birth)->format('d M Y') : '—' }}</td></tr>
    </table>
    @else
    <p style="color:var(--p-text-muted);font-size:.85rem;">Patient record not linked.</p>
    @endif
    @if($payment->payer_name)
    <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px dashed var(--p-border);font-size:.85rem;">
        <span style="color:var(--p-text-muted);">Gateway-registered name:</span> <strong>{{ $payment->payer_name }}</strong>
    </div>
    @endif
    @if($payment->payer_phone)
    <div style="margin-top:.5rem;font-size:.88rem;">
        <span style="color:var(--p-text-muted);">Phone number used:</span>
        <strong style="font-family:monospace;">{{ $payment->payer_phone }}</strong>
    </div>
    @endif
</div>

{{-- Payment Gateway --}}
<div class="panel" style="padding:1.25rem;">
    <h3 style="margin-top:0;font-size:.95rem;border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="credit-card" style="width:14px;height:14px;vertical-align:-2px;"></i> Payment Gateway</h3>
    @php $icons=['mtn_momo'=>'MTN MoMo','orange_money'=>'Orange Money','cash'=>'Cash','card'=>'Card','insurance'=>'Insurance','bank_transfer'=>'Bank Transfer','wallet'=>'Platform Wallet']; $gwIcons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','bank_transfer'=>'landmark','wallet'=>'wallet']; $gw=$payment->gateway??$payment->method??''; @endphp
    <table style="width:100%;font-size:.87rem;border-collapse:collapse;">
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;width:45%;">Gateway</td><td><strong><i data-lucide="{{ $gwIcons[$gw] ?? 'credit-card' }}" style="width:16px;height:16px;vertical-align:-2px;"></i> {{ $icons[$gw] ?? ucwords(str_replace('_',' ',$gw)) }}</strong></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Method</td><td>{{ ucwords(str_replace('_',' ',$payment->method??'—')) }}</td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Gateway Txn ID</td><td><code style="font-size:.78rem;">{{ $payment->gateway_transaction_id ?? '—' }}</code></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Gateway Status</td><td>{{ $payment->gateway_status ?? '—' }}</td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Our Reference</td><td><code style="font-size:.78rem;">{{ $payment->payment_reference }}</code></td></tr>
    </table>
</div>

{{-- Amount & Service --}}
<div class="panel" style="padding:1.25rem;">
    <h3 style="margin-top:0;font-size:.95rem;border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="coins" style="width:14px;height:14px;vertical-align:-2px;"></i> Amount & Service</h3>
    <table style="width:100%;font-size:.87rem;border-collapse:collapse;">
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;width:45%;">Amount Paid</td><td><strong style="font-size:1.1rem;">{{ number_format($payment->amount,2) }} {{ $payment->currency ?? 'XAF' }}</strong></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Refunded</td><td>{{ number_format($payment->refunded_amount ?? 0,2) }} {{ $payment->currency ?? 'XAF' }}</td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Net Amount</td><td><strong>{{ number_format(($payment->amount - ($payment->refunded_amount ?? 0)),2) }} {{ $payment->currency ?? 'XAF' }}</strong></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Service Type</td><td><strong>{{ ucwords(str_replace('_',' ',$payment->service_type??'—')) }}</strong></td></tr>
        @if($payment->invoice)
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Invoice #</td><td><code>{{ $payment->invoice->invoice_number }}</code> <span class="badge badge-{{ $payment->invoice->status==='paid'?'success':'warning' }}" style="font-size:.72rem;">{{ ucfirst($payment->invoice->status) }}</span></td></tr>
        @endif
    </table>
</div>

{{-- Device & Session --}}
<div class="panel" style="padding:1.25rem;">
    <h3 style="margin-top:0;font-size:.95rem;border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="monitor" style="width:14px;height:14px;vertical-align:-2px;"></i> Device & Session</h3>
    <table style="width:100%;font-size:.87rem;border-collapse:collapse;">
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;width:45%;">Device Type</td><td>@php $di=['web'=>'Web Browser','android'=>'Android','ios'=>'iOS','pos_terminal'=>'POS Terminal','ussd'=>'USSD']; $dIcons=['web'=>'globe','android'=>'smartphone','ios'=>'smartphone','pos_terminal'=>'printer','ussd'=>'phone']; @endphp<strong><i data-lucide="{{ $dIcons[$payment->device_type??''] ?? 'monitor' }}" style="width:16px;height:16px;vertical-align:-2px;"></i> {{ $di[$payment->device_type??''] ?? ucfirst($payment->device_type ?? '—') }}</strong></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Device ID</td><td><code style="font-size:.78rem;">{{ $payment->device_id ?? '—' }}</code></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">IP Address</td><td><code>{{ $payment->ip_address ?? '—' }}</code></td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">User Agent</td><td style="font-size:.72rem;word-break:break-all;max-width:220px;">{{ $payment->user_agent ?? '—' }}</td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Cashier</td><td>{{ $payment->cashier?->name ?? '—' }}</td></tr>
        <tr><td style="color:var(--p-text-muted);padding:.25rem 0;">Facility</td><td>{{ $payment->facility?->name ?? '—' }}</td></tr>
    </table>
</div>

</div>{{-- end 2-col grid --}}

{{-- Timeline --}}
<div class="panel" style="padding:1.25rem;margin-bottom:1rem;">
    <h3 style="margin-top:0;font-size:.95rem;border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="clock" style="width:14px;height:14px;vertical-align:-2px;"></i> Transaction Timeline</h3>
    <div style="display:flex;gap:2rem;flex-wrap:wrap;font-size:.87rem;">
        <div><span style="color:var(--p-text-muted);">Initiated</span><br><strong>{{ $payment->initiated_at?->format('d M Y H:i:s') ?? $payment->created_at?->format('d M Y H:i:s') ?? '—' }}</strong></div>
        <div><span style="color:var(--p-text-muted);">Record Created</span><br><strong>{{ $payment->created_at?->format('d M Y H:i:s') ?? '—' }}</strong></div>
        <div><span style="color:var(--p-text-muted);">Confirmed</span><br><strong style="color:var(--p-success);">{{ $payment->confirmed_at?->format('d M Y H:i:s') ?? '—' }}</strong></div>
        @if($payment->failed_at)
        <div><span style="color:var(--p-text-muted);">Failed At</span><br><strong style="color:var(--p-danger);">{{ $payment->failed_at?->format('d M Y H:i:s') }}</strong></div>
        @endif
        @if($payment->failure_reason)
        <div><span style="color:var(--p-text-muted);">Failure Reason</span><br><strong style="color:var(--p-danger);">{{ $payment->failure_reason }}</strong></div>
        @endif
    </div>
</div>

{{-- Invoice Line Items --}}
@if($payment->invoice && $payment->invoice->items->count())
<div class="panel" style="padding:1.25rem;margin-bottom:1rem;">
    <h3 style="margin-top:0;font-size:.95rem;border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="receipt" style="width:14px;height:14px;vertical-align:-2px;"></i> Invoice Line Items ({{ $payment->invoice->invoice_number }})</h3>
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Service Code</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th>Line Total</th></tr></thead><tbody>
    @foreach($payment->invoice->items as $item)
    <tr>
        <td><code style="font-size:.78rem;">{{ $item->service_code ?? '—' }}</code></td>
        <td>{{ $item->description }}</td>
        <td>{{ $item->quantity }}</td>
        <td>{{ number_format($item->unit_price,2) }}</td>
        <td>{{ number_format($item->discount_amount ?? 0,2) }}</td>
        <td><strong>{{ number_format($item->line_total_amount,2) }}</strong></td>
    </tr>
    @endforeach
    </tbody></table></div>
</div>
@endif

{{-- Receipts --}}
@if($payment->receipts->count())
<div class="panel" style="padding:1.25rem;margin-bottom:1rem;">
    <h3 style="margin-top:0;font-size:.95rem;border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="receipt" style="width:14px;height:14px;vertical-align:-2px;"></i> Receipts</h3>
    @foreach($payment->receipts as $r)
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.35rem 0;border-bottom:1px solid var(--p-border);font-size:.87rem;">
        <span><code>{{ $r->receipt_number }}</code></span>
        <span><strong>{{ number_format($r->amount,2) }} XAF</strong></span>
        <span style="color:var(--p-text-muted);">{{ $r->issued_at?->format('d M Y H:i') }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- Reversals --}}
@if($payment->reversals->count())
<div class="panel" style="padding:1.25rem;margin-bottom:1rem;border:1px solid rgba(239,68,68,.3);">
    <h3 style="margin-top:0;font-size:.95rem;color:var(--p-danger);border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="refresh-cw" style="width:14px;height:14px;vertical-align:-2px;"></i> Reversals / Refunds</h3>
    @foreach($payment->reversals as $rev)
    <div style="padding:.75rem 0;border-bottom:1px solid var(--p-border);font-size:.87rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
            <strong style="color:var(--p-danger);">- {{ number_format($rev->amount,2) }} XAF</strong>
            <span style="color:var(--p-text-muted);">{{ $rev->created_at?->format('d M Y H:i') }}</span>
        </div>
        <div>Reason: {{ $rev->reason ?? '—' }}</div>
        <div style="color:var(--p-text-muted);">By: {{ $rev->actor?->name ?? 'System' }}</div>
    </div>
    @endforeach
</div>
@endif

{{-- Raw Gateway Metadata --}}
@if($payment->gateway_metadata)
<div class="panel" style="padding:1.25rem;margin-bottom:1rem;">
    <h3 style="margin-top:0;font-size:.95rem;border-bottom:1px solid var(--p-border);padding-bottom:.6rem;margin-bottom:1rem;"><i data-lucide="microscope" style="width:14px;height:14px;vertical-align:-2px;"></i> Raw Gateway Metadata</h3>
    <pre style="font-size:.78rem;background:var(--p-surface-2);padding:1rem;border-radius:6px;overflow-x:auto;max-height:300px;">{{ json_encode($payment->gateway_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endif

@endsection