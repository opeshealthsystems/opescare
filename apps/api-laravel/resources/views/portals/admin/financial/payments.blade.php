@extends('layouts.portal')
@section('title', 'All Payments')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Payments')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">All Payments</h1>
        <p class="page-subtitle">Full transaction log — every payment with complete payer, gateway, device, and service details.</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-ghost btn-sm">Dashboard</a>
        <a href="{{ route('portals.admin.financial.report.by_service') }}" class="btn btn-ghost btn-sm">By Service</a>
    </div>
</div>
@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.admin.financial.payments') }}" class="panel" style="padding:1rem;margin-bottom:1rem;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.6rem;align-items:flex-end;">
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Search (ref, phone, name)</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference or phone..." class="form-control form-control-sm"></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Gateway</label>
            <select name="gateway" class="form-control form-control-sm">
                <option value="">All Gateways</option>
                @foreach(['mtn_momo'=>'MTN MoMo','orange_money'=>'Orange Money','cash'=>'Cash','card'=>'Card','insurance'=>'Insurance','bank_transfer'=>'Bank Transfer','wallet'=>'Wallet'] as $k=>$l)
                <option value="{{ $k }}" {{ request('gateway')===$k?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Status</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach(['successful'=>'Successful','pending'=>'Pending','failed'=>'Failed','refunded'=>'Refunded','completed'=>'Completed'] as $k=>$l)
                <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Service Type</label>
            <select name="service_type" class="form-control form-control-sm">
                <option value="">All Services</option>
                @foreach(['consultation','lab_test','pharmacy','radiology','admission','subscription','emergency','procedure','dental','vaccination','manual_override'] as $s)
                <option value="{{ $s }}" {{ request('service_type')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Device</label>
            <select name="device_type" class="form-control form-control-sm">
                <option value="">All Devices</option>
                @foreach(['web','android','ios','pos_terminal','ussd'] as $d)
                <option value="{{ $d }}" {{ request('device_type')===$d?'selected':'' }}>{{ strtoupper($d) }}</option>
                @endforeach
            </select></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Facility</label>
            <select name="facility_id" class="form-control form-control-sm">
                <option value="">All Facilities</option>
                @foreach($facilities as $f)<option value="{{ $f->id }}" {{ request('facility_id')==$f->id?'selected':'' }}>{{ $f->name }}</option>@endforeach
            </select></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">From</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm"></div>
        <div><label style="font-size:.78rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">To</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm"></div>
        <div style="display:flex;gap:.4rem;align-items:flex-end;">
            <button type="submit" class="btn btn-primary btn-sm" style="flex:1">Filter</button>
            <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-sm">Reset</a>
        </div>
    </div>
</form>

<div class="panel" style="padding:.75rem 1.25rem;margin-bottom:.75rem;display:flex;gap:1.5rem;flex-wrap:wrap;">
    <span style="font-size:.85rem;color:var(--p-text-muted);">{{ $summaryCount }} transactions</span>
    <span style="font-size:.85rem;font-weight:600;color:var(--p-success);">Collected: {{ number_format($summaryTotal,0,'.',',') }} XAF (filtered)</span>
</div>

<div class="panel">
    <div class="table-wrapper">
    <table class="data-table">
        <thead><tr>
            <th>Reference</th>
            <th>Payer</th>
            <th>Phone Used</th>
            <th>Gateway</th>
            <th>Gateway Txn ID</th>
            <th>Service</th>
            <th>Amount (XAF)</th>
            <th>Status</th>
            <th>Device</th>
            <th>Facility</th>
            <th>Cashier</th>
            <th>Date & Time</th>
            <th></th>
        </tr></thead>
        <tbody>
        @forelse($payments as $p)
        <tr>
            <td><code style="font-size:.76rem;">{{ $p->payment_reference }}</code></td>
            <td style="font-size:.85rem;">
                @if($p->patient)
                    <div>{{ $p->patient->first_name }} {{ $p->patient->last_name }}</div>
                    <div style="font-size:.75rem;color:var(--p-text-muted);">{{ $p->patient->health_id }}</div>
                @elseif($p->payer_name)
                    {{ $p->payer_name }}
                @else
                    <span style="color:var(--p-text-muted);">—</span>
                @endif
            </td>
            <td style="font-size:.85rem;font-family:monospace;">{{ $p->payer_phone ?? '—' }}</td>
            <td>
                @php $icons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','bank_transfer'=>'landmark','wallet'=>'wallet']; $gw=$p->gateway??$p->method??''; @endphp
                <i data-lucide="{{ $icons[$gw] ?? 'credit-card' }}" style="width:16px;height:16px;vertical-align:-2px;" title="{{ $gw }}"></i>
                <span style="font-size:.82rem;">{{ ucwords(str_replace('_',' ',$gw)) }}</span>
            </td>
            <td style="font-size:.76rem;font-family:monospace;max-width:130px;overflow:hidden;text-overflow:ellipsis;" title="{{ $p->gateway_transaction_id }}">{{ $p->gateway_transaction_id ?? '—' }}</td>
            <td style="font-size:.82rem;">{{ ucwords(str_replace('_',' ',$p->service_type??'—')) }}</td>
            <td style="font-weight:600;">{{ number_format($p->amount,0,'.',',') }}
                @if($p->refunded_amount > 0)
                <div style="font-size:.72rem;color:var(--p-warning);">-{{ number_format($p->refunded_amount,0,'.',',') }} refund</div>
                @endif
            </td>
            <td>
                @if(in_array($p->status,['successful','completed']))<span class="badge badge-success">{{ ucfirst($p->status) }}</span>
                @elseif($p->status==='pending')<span class="badge badge-warning">Pending</span>
                @elseif($p->status==='failed')<span class="badge badge-danger">Failed</span>
                @else<span class="badge" style="background:var(--p-surface-3);">{{ ucfirst($p->status) }}</span>@endif
            </td>
            <td style="font-size:.8rem;">
                @php $di=['web'=>'globe','android'=>'smartphone','ios'=>'smartphone','pos_terminal'=>'printer','ussd'=>'phone']; @endphp
                <i data-lucide="{{ $di[$p->device_type??''] ?? 'monitor' }}" style="width:14px;height:14px;vertical-align:-2px;"></i> {{ strtoupper($p->device_type ?? '—') }}
            </td>
            <td style="font-size:.82rem;">{{ $p->facility?->name ?? '—' }}</td>
            <td style="font-size:.82rem;">{{ $p->cashier?->name ?? '—' }}</td>
            <td style="font-size:.78rem;white-space:nowrap;">
                {{ $p->created_at?->format('d M Y') }}<br>
                <span style="color:var(--p-text-muted);">{{ $p->created_at?->format('H:i:s') }}</span>
            </td>
            <td><a href="{{ route('portals.admin.financial.payment.detail',$p->id) }}" class="btn btn-ghost btn-xs">Details</a></td>
        </tr>
        @empty
        <tr><td colspan="13" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No payments found for this filter.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div style="padding:.75rem 1.25rem;">{{ $payments->links() }}</div>
</div>
@endsection