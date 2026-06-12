@extends('layouts.portal')
@section('title', 'Financial Dashboard')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Financial')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Financial Dashboard</h1>
        <p class="page-subtitle">Revenue overview across all facilities, gateways, and services.</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-primary btn-sm">All Payments</a>
        <a href="{{ route('portals.admin.financial.invoices') }}" class="btn btn-ghost btn-sm">Invoices</a>
        <a href="{{ route('portals.admin.financial.report.by_service') }}" class="btn btn-ghost btn-sm">By Service</a>
    </div>
</div>

{{-- Date range filter --}}
<form method="GET" action="{{ route('portals.admin.financial.index') }}" class="panel" style="padding:1rem;margin-bottom:1.25rem;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">From</label><input type="date" name="from_date" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm"></div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">To</label><input type="date" name="to_date" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm"></div>
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>

{{-- KPI cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="panel" style="padding:1.25rem;">
        <div style="font-size:.8rem;color:var(--p-text-muted);margin-bottom:.4rem;">Total Collected</div>
        <div style="font-size:1.6rem;font-weight:700;color:var(--p-success);">{{ number_format($totalCollected,0,'.',',') }} XAF</div>
        <div style="font-size:.78rem;color:var(--p-text-muted);margin-top:.3rem;">Successful payments</div>
    </div>
    <div class="panel" style="padding:1.25rem;">
        <div style="font-size:.8rem;color:var(--p-text-muted);margin-bottom:.4rem;">Pending</div>
        <div style="font-size:1.6rem;font-weight:700;color:var(--p-warning);">{{ number_format($totalPending,0,'.',',') }} XAF</div>
        <div style="font-size:.78rem;color:var(--p-text-muted);margin-top:.3rem;">Awaiting confirmation</div>
    </div>
    <div class="panel" style="padding:1.25rem;">
        <div style="font-size:.8rem;color:var(--p-text-muted);margin-bottom:.4rem;">Failed Transactions</div>
        <div style="font-size:1.6rem;font-weight:700;color:var(--p-danger);">{{ $totalFailed }}</div>
        <div style="font-size:.78rem;color:var(--p-text-muted);margin-top:.3rem;">Need investigation</div>
    </div>
    <div class="panel" style="padding:1.25rem;">
        <div style="font-size:.8rem;color:var(--p-text-muted);margin-bottom:.4rem;">Total Refunded</div>
        <div style="font-size:1.6rem;font-weight:700;color:var(--p-primary);">{{ number_format($totalRefunded,0,'.',',') }} XAF</div>
        <div style="font-size:.78rem;color:var(--p-text-muted);margin-top:.3rem;">Refunds processed</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
    {{-- By Gateway --}}
    <div class="panel">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);font-weight:600;font-size:.9rem;">Revenue by Payment Gateway</div>
        <div style="padding:1rem 1.25rem;">
            @forelse($byGateway as $row)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid var(--p-border);">
                <div>
                    @php $icons=['mtn_momo'=>'📱','orange_money'=>'🟠','cash'=>'💵','card'=>'💳','insurance'=>'🏥','bank_transfer'=>'🏦','wallet'=>'👛']; @endphp
                    <span>{{ $icons[$row->gw] ?? '💳' }}</span>
                    <span style="font-size:.88rem;margin-left:.4rem;">{{ ucwords(str_replace('_',' ',$row->gw)) }}</span>
                    <span style="font-size:.78rem;color:var(--p-text-muted);margin-left:.5rem;">{{ $row->txn_count }} txn{{ $row->txn_count!=1?'s':'' }}</span>
                </div>
                <strong style="font-size:.9rem;">{{ number_format($row->total,0,'.',',') }}</strong>
            </div>
            @empty
            <p style="color:var(--p-text-muted);font-size:.85rem;">No data for period.</p>
            @endforelse
        </div>
    </div>
    {{-- By Service --}}
    <div class="panel">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);font-weight:600;font-size:.9rem;">Revenue by Service Type</div>
        <div style="padding:1rem 1.25rem;">
            @forelse($byService as $row)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid var(--p-border);">
                <div>
                    <span style="font-size:.88rem;">{{ ucwords(str_replace('_',' ',$row->svc)) }}</span>
                    <span style="font-size:.78rem;color:var(--p-text-muted);margin-left:.5rem;">{{ $row->txn_count }} txn{{ $row->txn_count!=1?'s':'' }}</span>
                </div>
                <strong style="font-size:.9rem;">{{ number_format($row->total,0,'.',',') }}</strong>
            </div>
            @empty
            <p style="color:var(--p-text-muted);font-size:.85rem;">No data for period.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- By Facility --}}
<div class="panel" style="margin-bottom:1.25rem;">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);font-weight:600;font-size:.9rem;">Revenue by Facility</div>
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Facility</th><th>Transactions</th><th>Total Collected</th></tr></thead><tbody>
    @forelse($byFacility as $row)
    <tr>
        <td>{{ $row->facility?->name ?? 'Unknown' }}</td>
        <td>{{ number_format($row->txn_count) }}</td>
        <td><strong>{{ number_format($row->total,0,'.',',') }} XAF</strong></td>
    </tr>
    @empty<tr><td colspan="3" style="text-align:center;padding:1.5rem;color:var(--p-text-muted);">No data.</td></tr>@endforelse
    </tbody></table></div>
</div>

{{-- Recent Payments --}}
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;font-size:.9rem;">Recent Payments</span>
        <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-xs">View All</a>
    </div>
    <div class="table-wrapper"><table class="data-table"><thead><tr>
        <th>Reference</th><th>Patient</th><th>Gateway</th><th>Service</th><th>Amount</th><th>Status</th><th>When</th><th></th>
    </tr></thead><tbody>
    @forelse($recentPayments as $p)
    <tr>
        <td><code style="font-size:.78rem;">{{ $p->payment_reference }}</code></td>
        <td style="font-size:.85rem;">{{ $p->patient?->first_name.' '.$p->patient?->last_name ?? '—' }}</td>
        <td>
            @php $icons=['mtn_momo'=>'📱','orange_money'=>'🟠','cash'=>'💵','card'=>'💳','insurance'=>'🏥','wallet'=>'👛']; @endphp
            <span>{{ $icons[$p->gateway??$p->method] ?? '💳' }}</span>
            <span style="font-size:.82rem;">{{ ucwords(str_replace('_',' ',$p->gateway??$p->method??'—')) }}</span>
        </td>
        <td style="font-size:.82rem;">{{ ucwords(str_replace('_',' ',$p->service_type??'—')) }}</td>
        <td><strong>{{ number_format($p->amount,0,'.',',') }}</strong></td>
        <td>@if(in_array($p->status,['successful','completed']))<span class="badge badge-success">OK</span>@elseif($p->status==='pending')<span class="badge badge-warning">Pending</span>@else<span class="badge badge-danger">{{ ucfirst($p->status) }}</span>@endif</td>
        <td style="font-size:.78rem;">{{ $p->created_at?->format('d M H:i') }}</td>
        <td><a href="{{ route('portals.admin.financial.payment.detail',$p->id) }}" class="btn btn-ghost btn-xs">Details</a></td>
    </tr>
    @empty<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No payments yet.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection