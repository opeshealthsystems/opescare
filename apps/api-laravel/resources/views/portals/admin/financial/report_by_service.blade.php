@extends('layouts.portal')
@section('title', 'Revenue by Service')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Revenue by Service')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Revenue Report by Service Type</h1>
        <p class="page-subtitle">Breakdown of all collected payments grouped by service and gateway.</p>
    </div>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-sm">All Payments</a>
</div>

<form method="GET" action="{{ route('portals.admin.financial.report.by_service') }}" class="panel" style="padding:1rem;margin-bottom:1rem;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">From</label><input type="date" name="from_date" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm"></div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">To</label><input type="date" name="to_date" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm"></div>
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
    </div>
</form>

@php
    $grandTotal = $report->sum('total_collected');
    $byService = $report->groupBy('service_type');
@endphp
<div class="panel" style="margin-bottom:1rem;padding:.75rem 1.25rem;">
    <span style="font-size:.9rem;font-weight:600;">Grand Total Collected: </span>
    <span style="font-size:1.1rem;font-weight:700;color:var(--p-success);">{{ number_format($grandTotal,0,'.',',') }} XAF</span>
    <span style="color:var(--p-text-muted);font-size:.82rem;margin-left:1rem;">Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</span>
</div>

@foreach($byService as $service => $rows)
<div class="panel" style="margin-bottom:1rem;">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
        <h3 style="margin:0;font-size:.95rem;">{{ ucwords(str_replace('_',' ',$service)) }}</h3>
        <strong style="color:var(--p-success);">{{ number_format($rows->sum('total_collected'),0,'.',',') }} XAF</strong>
    </div>
    <div class="table-wrapper"><table class="data-table">
        <thead><tr><th>Gateway</th><th>Transactions</th><th>Total</th><th>Avg per Txn</th><th>Min</th><th>Max</th></tr></thead>
        <tbody>
        @foreach($rows as $row)
        <tr>
            <td>
                @php $icons=['mtn_momo'=>'📱','orange_money'=>'🟠','cash'=>'💵','card'=>'💳','insurance'=>'🏥','bank_transfer'=>'🏦','wallet'=>'👛','unknown'=>'❓']; @endphp
                {{ $icons[$row->gateway] ?? '💳' }} {{ ucwords(str_replace('_',' ',$row->gateway)) }}
            </td>
            <td>{{ number_format($row->txn_count) }}</td>
            <td><strong>{{ number_format($row->total_collected,0,'.',',') }}</strong></td>
            <td>{{ number_format($row->avg_amount,0,'.',',') }}</td>
            <td>{{ number_format($row->min_amount,0,'.',',') }}</td>
            <td>{{ number_format($row->max_amount,0,'.',',') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table></div>
</div>
@endforeach
@if($report->isEmpty())
<div class="panel" style="padding:2rem;text-align:center;color:var(--p-text-muted);">No payment data for this period.</div>
@endif
@endsection