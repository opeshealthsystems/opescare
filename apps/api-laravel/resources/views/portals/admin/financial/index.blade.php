@extends('layouts.portal')
@section('title', 'Financial Dashboard')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Financial')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.financial.index') }}">Financial</a>
    <i data-lucide="chevron-right"></i>
    <span>Dashboard</span>
</div>

<div class="page-head">
    <h2>Financial dashboard</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-primary btn-sm"><i data-lucide="list"></i> All payments</a>
    <a href="{{ route('portals.admin.financial.invoices') }}" class="btn btn-secondary btn-sm"><i data-lucide="file-text"></i> Invoices</a>
    <a href="{{ route('portals.admin.financial.report.by_service') }}" class="btn btn-secondary btn-sm"><i data-lucide="bar-chart-3"></i> By service</a>
</div>

<p class="td-muted mb-6">Revenue overview across all facilities, gateways, and services.</p>

{{-- Date range filter --}}
<form method="GET" action="{{ route('portals.admin.financial.index') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="from_date" value="{{ $from->format('Y-m-d') }}" aria-label="From date">
    </label>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="to_date" value="{{ $to->format('Y-m-d') }}" aria-label="To date">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Apply</button>
    <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

{{-- KPI cards --}}
<div class="stat-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__label">Total collected</div>
        <div class="stat-card__value">{{ number_format($totalCollected,0,'.',',') }} XAF</div>
        <div class="stat-card__hint">Successful payments</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Pending</div>
        <div class="stat-card__value">{{ number_format($totalPending,0,'.',',') }} XAF</div>
        <div class="stat-card__hint">Awaiting confirmation</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Failed transactions</div>
        <div class="stat-card__value">{{ $totalFailed }}</div>
        <div class="stat-card__hint">Need investigation</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Total refunded</div>
        <div class="stat-card__value">{{ number_format($totalRefunded,0,'.',',') }} XAF</div>
        <div class="stat-card__hint">Refunds processed</div>
    </div>
</div>

<div class="field-grid mb-6">
    {{-- By Gateway --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="wallet"></i> Revenue by payment gateway</h3></div>
        <div class="panel-body">
            @forelse($byGateway as $row)
            @php $icons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','bank_transfer'=>'landmark','wallet'=>'wallet']; @endphp
            <div class="list-row">
                <span class="list-row__main">
                    <i data-lucide="{{ $icons[$row->gw] ?? 'credit-card' }}"></i>
                    <span>{{ ucwords(str_replace('_',' ',$row->gw)) }}</span>
                    <span class="list-row__meta">{{ $row->txn_count }} txn{{ $row->txn_count!=1?'s':'' }}</span>
                </span>
                <strong class="list-row__value">{{ number_format($row->total,0,'.',',') }}</strong>
            </div>
            @empty
            <p class="td-muted">No data for period.</p>
            @endforelse
        </div>
    </div>
    {{-- By Service --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="layers"></i> Revenue by service type</h3></div>
        <div class="panel-body">
            @forelse($byService as $row)
            <div class="list-row">
                <span class="list-row__main">
                    <span>{{ ucwords(str_replace('_',' ',$row->svc)) }}</span>
                    <span class="list-row__meta">{{ $row->txn_count }} txn{{ $row->txn_count!=1?'s':'' }}</span>
                </span>
                <strong class="list-row__value">{{ number_format($row->total,0,'.',',') }}</strong>
            </div>
            @empty
            <p class="td-muted">No data for period.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- By Facility --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="building-2"></i> Revenue by facility</h3></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Facility</th><th>Transactions</th><th>Total collected</th></tr></thead>
            <tbody>
            @forelse($byFacility as $row)
            <tr>
                <td data-label="Facility">{{ $row->facility?->name ?? 'Unknown' }}</td>
                <td data-label="Transactions">{{ number_format($row->txn_count) }}</td>
                <td data-label="Total collected"><strong>{{ number_format($row->total,0,'.',',') }} XAF</strong></td>
            </tr>
            @empty<tr><td colspan="3" class="td-muted empty-cell">No data.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Payments --}}
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="receipt"></i> Recent payments</h3>
        <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr>
                <th>Reference</th><th>Patient</th><th>Gateway</th><th>Service</th><th>Amount</th><th>Status</th><th>When</th><th class="row-actions"></th>
            </tr></thead>
            <tbody>
            @forelse($recentPayments as $p)
            @php $icons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','wallet'=>'wallet']; @endphp
            <tr>
                <td data-label="Reference"><span class="mono">{{ $p->payment_reference }}</span></td>
                <td data-label="Patient">{{ $p->patient?->first_name.' '.$p->patient?->last_name ?? '—' }}</td>
                <td data-label="Gateway">
                    <span class="cell-with-icon">
                        <i data-lucide="{{ $icons[$p->gateway??$p->method] ?? 'credit-card' }}"></i>
                        <span>{{ ucwords(str_replace('_',' ',$p->gateway??$p->method??'—')) }}</span>
                    </span>
                </td>
                <td data-label="Service">{{ ucwords(str_replace('_',' ',$p->service_type??'—')) }}</td>
                <td data-label="Amount"><strong>{{ number_format($p->amount,0,'.',',') }}</strong></td>
                <td data-label="Status">@if(in_array($p->status,['successful','completed']))<span class="badge badge-success">OK</span>@elseif($p->status==='pending')<span class="badge badge-warning">Pending</span>@else<span class="badge badge-danger">{{ ucfirst($p->status) }}</span>@endif</td>
                <td data-label="When">{{ $p->created_at?->format('d M H:i') }}</td>
                <td class="row-actions" data-label="Actions"><a href="{{ route('portals.admin.financial.payment.detail',$p->id) }}" class="btn btn-ghost btn-sm">Details</a></td>
            </tr>
            @empty<tr><td colspan="8" class="td-muted empty-cell">No payments yet.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
