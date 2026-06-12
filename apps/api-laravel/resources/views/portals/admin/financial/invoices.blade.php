@extends('layouts.portal')
@section('title', 'All Invoices')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Invoices')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">All Invoices</h1>
        <p class="page-subtitle">Full invoice ledger across all facilities.</p>
    </div>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-sm">Payments</a>
</div>
@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<form method="GET" action="{{ route('portals.admin.financial.invoices') }}" class="panel" style="padding:1rem;margin-bottom:1rem;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:160px;"><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Search</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice number..." class="form-control form-control-sm"></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Status</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach(['draft','unpaid','paid','partial','cancelled','overdue'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Facility</label>
            <select name="facility_id" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach($facilities as $f)<option value="{{ $f->id }}" {{ request('facility_id')==$f->id?'selected':'' }}>{{ $f->name }}</option>@endforeach
            </select></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">From</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm"></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">To</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm"></div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('portals.admin.financial.invoices') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>

<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);"><span style="font-size:.85rem;color:var(--p-text-muted);">{{ $invoices->total() }} invoices</span></div>
    <div class="table-wrapper"><table class="data-table"><thead><tr>
        <th>Invoice #</th><th>Patient</th><th>Facility</th><th>Subtotal</th><th>Paid</th><th>Balance</th><th>Status</th><th>Issued</th><th>Actions</th>
    </tr></thead><tbody>
    @forelse($invoices as $inv)
    <tr>
        <td><code style="font-size:.8rem;">{{ $inv->invoice_number }}</code></td>
        <td style="font-size:.85rem;">{{ $inv->patient?->first_name.' '.$inv->patient?->last_name ?? '—' }}<br><span style="font-size:.75rem;color:var(--p-text-muted);">{{ $inv->patient?->health_id }}</span></td>
        <td style="font-size:.82rem;">{{ $inv->facility?->name ?? '—' }}</td>
        <td>{{ number_format($inv->subtotal_amount,0,'.',',') }}</td>
        <td style="color:var(--p-success);">{{ number_format($inv->paid_amount ?? 0,0,'.',',') }}</td>
        <td style="{{ ($inv->balance_amount ?? 0) > 0 ? 'color:var(--p-danger);' : '' }}">{{ number_format($inv->balance_amount ?? 0,0,'.',',') }}</td>
        <td>
            @if($inv->status==='paid')<span class="badge badge-success">Paid</span>
            @elseif($inv->status==='partial')<span class="badge badge-warning">Partial</span>
            @elseif($inv->status==='cancelled')<span class="badge" style="background:var(--p-surface-3);color:var(--p-text-muted);">Voided</span>
            @elseif($inv->status==='overdue')<span class="badge badge-danger">Overdue</span>
            @else<span class="badge badge-warning">{{ ucfirst($inv->status) }}</span>@endif
        </td>
        <td style="font-size:.8rem;">{{ $inv->issued_at?->format('d M Y') ?? '—' }}</td>
        <td>
            <div style="display:flex;gap:.35rem;">
                @if(in_array($inv->status,['draft','unpaid']))
                <form method="POST" action="{{ route('portals.admin.financial.void-invoice',$inv->id) }}" onsubmit="return confirm('Void invoice?')">@csrf<button class="btn btn-warning btn-xs">Void</button></form>
                @endif
                @if(!in_array($inv->status,['paid','cancelled']))
                <button class="btn btn-success btn-xs" onclick="document.getElementById('pay-modal-{{ $inv->id }}').style.display='flex'">Mark Paid</button>
                @endif
            </div>
            {{-- Mark Paid Modal --}}
            <div id="pay-modal-{{ $inv->id }}" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
                <div class="panel" style="width:380px;max-width:95vw;padding:1.5rem;">
                    <h3 style="margin-top:0;font-size:.95rem;">Mark Invoice #{{ $inv->invoice_number }} Paid</h3>
                    <form method="POST" action="{{ route('portals.admin.financial.mark-paid',$inv->id) }}">@csrf
                        <div style="margin-bottom:.75rem;"><label class="form-label" style="font-size:.82rem;">Amount (XAF)</label><input type="number" name="amount" value="{{ $inv->balance_amount ?? $inv->subtotal_amount }}" class="form-control form-control-sm" step="0.01" required></div>
                        <div style="margin-bottom:.75rem;"><label class="form-label" style="font-size:.82rem;">Gateway</label>
                            <select name="method" class="form-control form-control-sm">@foreach(['cash'=>'Cash','mtn_momo'=>'MTN MoMo','orange_money'=>'Orange Money','card'=>'Card','insurance'=>'Insurance','bank_transfer'=>'Bank Transfer'] as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                        <div style="margin-bottom:.75rem;"><label class="form-label" style="font-size:.82rem;">Payer Phone</label><input type="text" name="payer_phone" class="form-control form-control-sm" placeholder="+237..."></div>
                        <div style="margin-bottom:1rem;"><label class="form-label" style="font-size:.82rem;">Payer Name</label><input type="text" name="payer_name" class="form-control form-control-sm"></div>
                        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('pay-modal-{{ $inv->id }}').style.display='none'">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm">Record Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </td>
    </tr>
    @empty<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No invoices found.</td></tr>@endforelse
    </tbody></table></div>
    <div style="padding:.75rem 1.25rem;">{{ $invoices->links() }}</div>
</div>
@endsection