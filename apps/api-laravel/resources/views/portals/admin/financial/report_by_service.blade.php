@extends('layouts.portal')
@section('title', __('public.adm_fin_rbs_title'))
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', __('admin_extra.breadcrumb_admin', [], app()->getLocale()) ?: 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', __('public.adm_fin_rbs_title'))
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.financial.index') }}">{{ __('public.adm_fin_breadcrumb') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ __('public.adm_fin_rbs_breadcrumb') }}</span>
</div>

<div class="page-head">
    <h2>{{ __('public.adm_fin_rbs_heading') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-secondary btn-sm"><i data-lucide="list"></i> {{ __('public.adm_fin_btn_payments') }}</a>
</div>

<p class="td-muted mb-6">{{ __('public.adm_fin_rbs_sub') }}</p>

<form method="GET" action="{{ route('portals.admin.financial.report.by_service') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="from_date" value="{{ $from->format('Y-m-d') }}" aria-label="{{ __('public.aria_from_date') }}">
    </label>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="to_date" value="{{ $to->format('Y-m-d') }}" aria-label="{{ __('public.aria_to_date') }}">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> {{ __('admin_extra.btn_apply', [], app()->getLocale()) ?: 'Apply' }}</button>
</form>

@php
    $grandTotal = $report->sum('total_collected');
    $byService = $report->groupBy('service_type');
@endphp

<div class="stat-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_fin_rbs_kpi_grand') }}</div>
        <div class="stat-card__value">{{ number_format($grandTotal,0,'.',',') }} XAF</div>
        <div class="stat-card__hint">{{ __('admin_extra.rbs_period', [], app()->getLocale()) ?: 'Period:' }} {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</div>
    </div>
</div>

@foreach($byService as $service => $rows)
<div class="panel mb-6">
    <div class="panel-header">
        <h3 class="panel-title">{{ ucwords(str_replace('_',' ',$service)) }}</h3>
        <strong>{{ number_format($rows->sum('total_collected'),0,'.',',') }} XAF</strong>
    </div>
    <div class="table-wrapper"><table class="data-table">
        <thead><tr><th>{{ __('public.adm_fin_rbs_col_gateway') }}</th><th>{{ __('public.adm_fin_rbs_col_transactions') }}</th><th>{{ __('public.adm_fin_rbs_col_total') }}</th><th>{{ __('public.adm_fin_rbs_col_avg') }}</th><th>{{ __('public.adm_fin_rbs_col_min') }}</th><th>{{ __('public.adm_fin_rbs_col_max') }}</th></tr></thead>
        <tbody>
        @foreach($rows as $row)
        @php $icons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','bank_transfer'=>'landmark','wallet'=>'wallet','unknown'=>'help-circle']; @endphp
        <tr>
            <td data-label="{{ __('public.adm_fin_rbs_col_gateway') }}">
                <span class="cell-with-icon"><i data-lucide="{{ $icons[$row->gateway] ?? 'credit-card' }}"></i> {{ ucwords(str_replace('_',' ',$row->gateway)) }}</span>
            </td>
            <td data-label="{{ __('public.adm_fin_rbs_col_transactions') }}">{{ number_format($row->txn_count) }}</td>
            <td data-label="{{ __('public.adm_fin_rbs_col_total') }}"><strong>{{ number_format($row->total_collected,0,'.',',') }}</strong></td>
            <td data-label="{{ __('public.adm_fin_rbs_col_avg') }}">{{ number_format($row->avg_amount,0,'.',',') }}</td>
            <td data-label="{{ __('public.adm_fin_rbs_col_min') }}">{{ number_format($row->min_amount,0,'.',',') }}</td>
            <td data-label="{{ __('public.adm_fin_rbs_col_max') }}">{{ number_format($row->max_amount,0,'.',',') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table></div>
</div>
@endforeach
@if($report->isEmpty())
<div class="panel">
    <div class="panel-body">
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="bar-chart-3"></i></div>
            <p>{{ __('public.adm_fin_rbs_no_data') }}</p>
        </div>
    </div>
</div>
@endif
@endsection
