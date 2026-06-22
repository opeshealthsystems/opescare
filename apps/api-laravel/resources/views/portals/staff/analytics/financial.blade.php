@extends('layouts.portal')
@section('title', __('staff_analytics.title_financial', [], app()->getLocale()) ?: 'Financial Analytics')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">{{ __('public.stf_analytics_fin_title') }}</h1>
            <p class="portal-page-subtitle">{{ __('public.stf_analytics_fin_subtitle') }}</p>
        </div>
        <div class="filter-bar filter-bar--flush">
            @foreach(['7d' => __('public.stf_analytics_period_7d'), '30d' => __('public.stf_analytics_period_30d'), '90d' => __('public.stf_analytics_period_90d'), '1y' => __('public.stf_analytics_period_1y')] as $val => $label)
                <a href="{{ route('portals.staff.analytics.financial', ['period' => $val]) }}"
                   class="btn btn-sm {{ $period === $val ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="stat-grid mb-6">
        <div class="stat-card stat-card--success">
            <div class="stat-card__head"><i data-lucide="banknote"></i></div>
            <div class="stat-card__value">{{ number_format($revenue['collected'] ?? 0, 2) }}</div>
            <div class="stat-card__label">{{ __('public.stf_analytics_fin_kpi_collected') }}</div>
        </div>
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="trending-up"></i></div>
            <div class="stat-card__value">{{ number_format($revenue['billed'] ?? 0, 2) }}</div>
            <div class="stat-card__label">{{ __('public.stf_analytics_fin_kpi_billed') }}</div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-card__head"><i data-lucide="alert-triangle"></i></div>
            <div class="stat-card__value">{{ number_format($outstandingAmount, 2) }}</div>
            <div class="stat-card__label">{{ __('public.stf_analytics_fin_kpi_outstanding') }} ({{ $outstandingCount }})</div>
        </div>
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="receipt"></i></div>
            <div class="stat-card__value">{{ number_format($revenue['invoice_count'] ?? 0) }}</div>
            <div class="stat-card__label">{{ __('public.stf_analytics_fin_kpi_invoices') }}</div>
        </div>
        <div class="stat-card stat-card--success">
            <div class="stat-card__head"><i data-lucide="percent"></i></div>
            <div class="stat-card__value">{{ $revenue['collection_rate'] ?? 0 }}%</div>
            <div class="stat-card__label">{{ __('public.stf_analytics_fin_kpi_coll_rate') }}</div>
        </div>
    </div>

    <div class="grid-2">

        {{-- Payment Mode Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_fin_card_by_mode') }}</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th>{{ __('public.stf_analytics_fin_col_mode') }}</th>
                        <th>{{ __('public.stf_analytics_fin_col_txns') }}</th>
                        <th>{{ __('public.stf_analytics_fin_col_amount') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($byPaymentMode as $row)
                            <tr>
                                <td data-label="{{ __('public.stf_analytics_fin_col_mode') }}">
                                    <span class="badge badge--info">
                                        @enum($row->payment_mode ?? 'Unknown')
                                    </span>
                                </td>
                                <td data-label="{{ __('public.stf_analytics_fin_col_txns') }}">{{ number_format($row->cnt) }}</td>
                                <td data-label="{{ __('public.stf_analytics_fin_col_amount') }}" class="td-strong">{{ number_format($row->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">{{ __('public.stf_analytics_fin_empty_payments') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- Top Services --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_fin_card_services') }}</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th>{{ __('public.stf_analytics_fin_col_service') }}</th>
                        <th>{{ __('public.stf_analytics_fin_col_count') }}</th>
                        <th>{{ __('public.stf_analytics_fin_col_revenue') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($topServices as $row)
                            <tr>
                                <td data-label="{{ __('public.stf_analytics_fin_col_service') }}">{{ Str::limit($row->description ?? '—', 35) }}</td>
                                <td data-label="{{ __('public.stf_analytics_fin_col_count') }}" class="td-muted">{{ $row->cnt }}</td>
                                <td data-label="{{ __('public.stf_analytics_fin_col_revenue') }}" class="td-strong">{{ number_format($row->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">{{ __('public.stf_analytics_fin_empty_services') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Revenue Trend --}}
    @if(!empty($revTrend))
    <div class="portal-card mt-6">
        <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_fin_card_trend') }}</h2></div>
        <div class="portal-card__body">
            <div class="bar-chart">
                @php $maxRev = max($revTrend) ?: 1; @endphp
                @foreach($revTrend as $day => $amt)
                    <div class="bar-chart__col">
                        <div class="bar-chart__val">{{ number_format($amt, 0) }}</div>
                        <div class="bar-chart__bar bar-chart__bar--teal" style="height:{{ max(2, round($amt / $maxRev * 100)) }}%;"></div>
                        <div class="bar-chart__label">{{ \Carbon\Carbon::parse($day)->format('d M') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
