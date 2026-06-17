@extends('layouts.portal')
@section('title', __('public.adm_fin_title'))
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', __('public.adm_fin_breadcrumb'))
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.financial.index') }}">{{ __('public.adm_fin_breadcrumb') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ __('public.adm_fin_breadcrumb_dashboard') }}</span>
</div>

<div class="page-head">
    <h2>{{ __('public.adm_fin_heading') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-primary btn-sm"><i data-lucide="list"></i> {{ __('public.adm_fin_btn_payments') }}</a>
    <a href="{{ route('portals.admin.financial.invoices') }}" class="btn btn-secondary btn-sm"><i data-lucide="file-text"></i> {{ __('public.adm_fin_btn_invoices') }}</a>
    <a href="{{ route('portals.admin.financial.report.by_service') }}" class="btn btn-secondary btn-sm"><i data-lucide="bar-chart-3"></i> {{ __('public.adm_fin_btn_by_service') }}</a>
</div>

<p class="td-muted mb-6">{{ __('public.adm_fin_sub') }}</p>

{{-- Date range filter --}}
<form method="GET" action="{{ route('portals.admin.financial.index') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="from_date" value="{{ $from->format('Y-m-d') }}" aria-label="{{ __('public.aria_from_date') }}">
    </label>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="to_date" value="{{ $to->format('Y-m-d') }}" aria-label="{{ __('public.aria_to_date') }}">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> {{ __('public.adm_fin_btn_apply') }}</button>
    <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-ghost btn-sm">{{ __('public.adm_fin_btn_reset') }}</a>
</form>

{{-- KPI cards --}}
<div class="stat-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_fin_kpi_collected') }}</div>
        <div class="stat-card__value">{{ number_format($totalCollected,0,'.',',') }} XAF</div>
        <div class="stat-card__hint">{{ __('public.adm_fin_kpi_collected_hint') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_fin_kpi_pending') }}</div>
        <div class="stat-card__value">{{ number_format($totalPending,0,'.',',') }} XAF</div>
        <div class="stat-card__hint">{{ __('public.adm_fin_kpi_pending_hint') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_fin_kpi_failed') }}</div>
        <div class="stat-card__value">{{ $totalFailed }}</div>
        <div class="stat-card__hint">{{ __('public.adm_fin_kpi_failed_hint') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_fin_kpi_refunded') }}</div>
        <div class="stat-card__value">{{ number_format($totalRefunded,0,'.',',') }} XAF</div>
        <div class="stat-card__hint">{{ __('public.adm_fin_kpi_refunded_hint') }}</div>
    </div>
</div>

<div class="field-grid mb-6">
    {{-- By Gateway --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="wallet"></i> {{ __('public.adm_fin_panel_by_gateway') }}</h3></div>
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
            <p class="td-muted">{{ __('public.adm_fin_no_data') }}</p>
            @endforelse
        </div>
    </div>
    {{-- By Service --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="layers"></i> {{ __('public.adm_fin_panel_by_service') }}</h3></div>
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
            <p class="td-muted">{{ __('public.adm_fin_no_data') }}</p>
            @endforelse
        </div>
    </div>
</div>

{{-- By Facility --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="building-2"></i> {{ __('public.adm_fin_panel_by_facility') }}</h3></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>{{ __('public.adm_fin_col_facility') }}</th><th>{{ __('public.adm_fin_col_transactions') }}</th><th>{{ __('public.adm_fin_col_total') }}</th></tr></thead>
            <tbody>
            @forelse($byFacility as $row)
            <tr>
                <td data-label="{{ __('public.adm_fin_col_facility') }}">{{ $row->facility?->name ?? 'Unknown' }}</td>
                <td data-label="{{ __('public.adm_fin_col_transactions') }}">{{ number_format($row->txn_count) }}</td>
                <td data-label="{{ __('public.adm_fin_col_total') }}"><strong>{{ number_format($row->total,0,'.',',') }} XAF</strong></td>
            </tr>
            @empty<tr><td colspan="3" class="td-muted empty-cell">{{ __('public.adm_fin_no_data_short') }}</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Payments --}}
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="receipt"></i> {{ __('public.adm_fin_panel_recent') }}</h3>
        <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-sm">{{ __('public.adm_fin_btn_view_all') }}</a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr>
                <th>{{ __('public.adm_fin_col_reference') }}</th><th>{{ __('public.adm_fin_col_patient') }}</th><th>{{ __('public.adm_fin_col_gateway') }}</th><th>{{ __('public.adm_fin_col_service') }}</th><th>{{ __('public.adm_fin_col_amount') }}</th><th>{{ __('public.adm_fin_col_status') }}</th><th>{{ __('public.adm_fin_col_when') }}</th><th class="row-actions"></th>
            </tr></thead>
            <tbody>
            @forelse($recentPayments as $p)
            @php $icons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','wallet'=>'wallet']; @endphp
            <tr>
                <td data-label="{{ __('public.adm_fin_col_reference') }}"><span class="mono">{{ $p->payment_reference }}</span></td>
                <td data-label="{{ __('public.adm_fin_col_patient') }}">{{ $p->patient?->first_name.' '.$p->patient?->last_name ?? 'â€”' }}</td>
                <td data-label="{{ __('public.adm_fin_col_gateway') }}">
                    <span class="cell-with-icon">
                        <i data-lucide="{{ $icons[$p->gateway??$p->method] ?? 'credit-card' }}"></i>
                        <span>{{ ucwords(str_replace('_',' ',$p->gateway??$p->method??'â€”')) }}</span>
                    </span>
                </td>
                <td data-label="{{ __('public.adm_fin_col_service') }}">{{ ucwords(str_replace('_',' ',$p->service_type??'â€”')) }}</td>
                <td data-label="{{ __('public.adm_fin_col_amount') }}"><strong>{{ number_format($p->amount,0,'.',',') }}</strong></td>
                <td data-label="{{ __('public.adm_fin_col_status') }}">@if(in_array($p->status,['successful','completed']))<span class="badge badge-success">{{ __('public.adm_fin_badge_ok') }}</span>@elseif($p->status==='pending')<span class="badge badge-warning">{{ __('public.adm_fin_badge_pending') }}</span>@else<span class="badge badge-danger">{{ ucfirst($p->status) }}</span>@endif</td>
                <td data-label="{{ __('public.adm_fin_col_when') }}">{{ $p->created_at?->format('d M H:i') }}</td>
                <td class="row-actions" data-label="Actions"><a href="{{ route('portals.admin.financial.payment.detail',$p->id) }}" class="btn btn-ghost btn-sm">{{ __('public.adm_fin_btn_details') }}</a></td>
            </tr>
            @empty<tr><td colspan="8" class="td-muted empty-cell">{{ __('public.adm_fin_no_payments') }}</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
