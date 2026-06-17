@extends('layouts.portal')
@section('title', __('public.adm_fin_pay_title'))
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', __('public.adm_fin_pay_breadcrumb'))
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.financial.index') }}">{{ __('public.adm_fin_breadcrumb') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ __('public.adm_fin_pay_breadcrumb') }}</span>
</div>

<div class="page-head">
    <h2>{{ __('public.adm_fin_pay_heading') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-secondary btn-sm"><i data-lucide="layout-dashboard"></i> {{ __('public.adm_fin_breadcrumb_dashboard') }}</a>
    <a href="{{ route('portals.admin.financial.report.by_service') }}" class="btn btn-secondary btn-sm"><i data-lucide="bar-chart-3"></i> {{ __('public.adm_fin_btn_by_service') }}</a>
</div>

<p class="td-muted mb-6">{{ __('public.adm_fin_pay_sub') }}</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.admin.financial.payments') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference or phone..." aria-label="{{ __('public.aria_search') }}">
    </label>
    <select name="gateway" class="filter-select" aria-label="{{ __('public.aria_gateway') }}">
        <option value="">{{ __('public.adm_fin_pay_opt_all_gateways') }}</option>
        @foreach(['mtn_momo'=>'MTN MoMo','orange_money'=>'Orange Money','cash'=>'Cash','card'=>'Card','insurance'=>'Insurance','bank_transfer'=>'Bank Transfer','wallet'=>'Wallet'] as $k=>$l)
        <option value="{{ $k }}" {{ request('gateway')===$k?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <select name="status" class="filter-select" aria-label="{{ __('public.aria_status') }}">
        <option value="">{{ __('public.adm_fin_pay_opt_all_statuses') }}</option>
        @foreach(['successful'=>'Successful','pending'=>'Pending','failed'=>'Failed','refunded'=>'Refunded','completed'=>'Completed'] as $k=>$l)
        <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <select name="service_type" class="filter-select" aria-label="{{ __('public.aria_service_type') }}">
        <option value="">{{ __('public.adm_fin_pay_opt_all_services') }}</option>
        @foreach(['consultation','lab_test','pharmacy','radiology','admission','subscription','emergency','procedure','dental','vaccination','manual_override'] as $s)
        <option value="{{ $s }}" {{ request('service_type')===$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <select name="device_type" class="filter-select" aria-label="{{ __('public.aria_device') }}">
        <option value="">{{ __('public.adm_fin_pay_opt_all_devices') }}</option>
        @foreach(['web','android','ios','pos_terminal','ussd'] as $d)
        <option value="{{ $d }}" {{ request('device_type')===$d?'selected':'' }}>{{ strtoupper($d) }}</option>
        @endforeach
    </select>
    <select name="facility_id" class="filter-select" aria-label="{{ __('public.aria_facility') }}">
        <option value="">{{ __('public.adm_fin_pay_opt_all_facilities') }}</option>
        @foreach($facilities as $f)<option value="{{ $f->id }}" {{ request('facility_id')==$f->id?'selected':'' }}>{{ $f->name }}</option>@endforeach
    </select>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="from_date" value="{{ request('from_date') }}" aria-label="{{ __('public.aria_from_date') }}">
    </label>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="to_date" value="{{ request('to_date') }}" aria-label="{{ __('public.aria_to_date') }}">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="panel mb-6">
    <div class="panel-body summary-bar">
        <span class="td-muted">{{ $summaryCount }} transactions</span>
        <span class="kv-strong">{{ __('public.adm_fin_pay_lbl_collected') }} {{ number_format($summaryTotal,0,'.',',') }} XAF (filtered)</span>
    </div>
</div>

<div class="panel">
    <div class="table-wrapper">
    <table class="data-table">
        <thead><tr>
            <th>{{ __('public.adm_fin_pay_col_reference') }}</th>
            <th>{{ __('public.adm_fin_pay_col_payer') }}</th>
            <th>{{ __('public.adm_fin_pay_col_phone') }}</th>
            <th>{{ __('public.adm_fin_pay_col_gateway') }}</th>
            <th>{{ __('public.adm_fin_pay_col_gw_txn') }}</th>
            <th>{{ __('public.adm_fin_pay_col_service') }}</th>
            <th>{{ __('public.adm_fin_pay_col_amount') }}</th>
            <th>{{ __('public.adm_fin_pay_col_status') }}</th>
            <th>{{ __('public.adm_fin_pay_col_device') }}</th>
            <th>{{ __('public.adm_fin_pay_col_facility') }}</th>
            <th>{{ __('public.adm_fin_pay_col_cashier') }}</th>
            <th>{{ __('public.adm_fin_pay_col_datetime') }}</th>
            <th class="row-actions"></th>
        </tr></thead>
        <tbody>
        @forelse($payments as $p)
        @php
            $icons=['mtn_momo'=>'smartphone','orange_money'=>'smartphone','cash'=>'banknote','card'=>'credit-card','insurance'=>'hospital','bank_transfer'=>'landmark','wallet'=>'wallet']; $gw=$p->gateway??$p->method??'';
            $di=['web'=>'globe','android'=>'smartphone','ios'=>'smartphone','pos_terminal'=>'printer','ussd'=>'phone'];
        @endphp
        <tr>
            <td data-label="{{ __('public.adm_fin_pay_col_reference') }}"><span class="mono">{{ $p->payment_reference }}</span></td>
            <td data-label="{{ __('public.adm_fin_pay_col_payer') }}">
                @if($p->patient)
                    <div class="td-strong">{{ $p->patient->first_name }} {{ $p->patient->last_name }}</div>
                    <div class="td-muted">{{ $p->patient->health_id }}</div>
                @elseif($p->payer_name)
                    {{ $p->payer_name }}
                @else
                    <span class="td-muted">â€”</span>
                @endif
            </td>
            <td data-label="{{ __('public.adm_fin_pay_col_phone') }}"><span class="mono">{{ $p->payer_phone ?? 'â€”' }}</span></td>
            <td data-label="{{ __('public.adm_fin_pay_col_gateway') }}">
                <span class="cell-with-icon" title="{{ $gw }}">
                    <i data-lucide="{{ $icons[$gw] ?? 'credit-card' }}"></i>
                    <span>{{ ucwords(str_replace('_',' ',$gw)) }}</span>
                </span>
            </td>
            <td data-label="{{ __('public.adm_fin_pay_col_gw_txn') }}"><span class="mono" title="{{ $p->gateway_transaction_id }}">{{ $p->gateway_transaction_id ?? 'â€”' }}</span></td>
            <td data-label="{{ __('public.adm_fin_pay_col_service') }}">{{ ucwords(str_replace('_',' ',$p->service_type??'â€”')) }}</td>
            <td data-label="{{ __('public.adm_fin_pay_col_amount') }}"><strong>{{ number_format($p->amount,0,'.',',') }}</strong>
                @if($p->refunded_amount > 0)
                <div class="td-muted">-{{ number_format($p->refunded_amount,0,'.',',') }} refund</div>
                @endif
            </td>
            <td data-label="{{ __('public.adm_fin_pay_col_status') }}">
                @if(in_array($p->status,['successful','completed']))<span class="badge badge-success">{{ ucfirst($p->status) }}</span>
                @elseif($p->status==='pending')<span class="badge badge-warning">{{ __('public.adm_fin_pay_badge_pending') }}</span>
                @elseif($p->status==='failed')<span class="badge badge-danger">{{ __('public.adm_fin_pay_badge_failed') }}</span>
                @else<span class="badge badge-neutral">{{ ucfirst($p->status) }}</span>@endif
            </td>
            <td data-label="{{ __('public.adm_fin_pay_col_device') }}">
                <span class="cell-with-icon"><i data-lucide="{{ $di[$p->device_type??''] ?? 'monitor' }}"></i> {{ strtoupper($p->device_type ?? 'â€”') }}</span>
            </td>
            <td data-label="{{ __('public.adm_fin_pay_col_facility') }}">{{ $p->facility?->name ?? 'â€”' }}</td>
            <td data-label="{{ __('public.adm_fin_pay_col_cashier') }}">{{ $p->cashier?->name ?? 'â€”' }}</td>
            <td data-label="{{ __('public.adm_fin_pay_col_datetime') }}">
                {{ $p->created_at?->format('d M Y') }}<br>
                <span class="td-muted">{{ $p->created_at?->format('H:i:s') }}</span>
            </td>
            <td class="row-actions" data-label="Actions"><a href="{{ route('portals.admin.financial.payment.detail',$p->id) }}" class="btn btn-ghost btn-sm">{{ __('public.adm_fin_btn_details') }}</a></td>
        </tr>
        @empty
        <tr><td colspan="13" class="td-muted empty-cell">{{ __('public.adm_fin_pay_empty') }}</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="panel-body">{{ $payments->links() }}</div>
</div>
@endsection
