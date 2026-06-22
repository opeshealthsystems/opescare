@extends('layouts.portal')
@section('title', __('staff_analytics.title_ward', [], app()->getLocale()) ?: 'Ward & Bed Analytics')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">{{ __('public.stf_analytics_ward_title') }}</h1>
            <p class="portal-page-subtitle">{{ __('public.stf_analytics_ward_subtitle') }}</p>
        </div>
        <div class="filter-bar filter-bar--flush">
            @foreach(['7d' => __('public.stf_analytics_period_7d'), '30d' => __('public.stf_analytics_period_30d'), '90d' => __('public.stf_analytics_period_90d'), '1y' => __('public.stf_analytics_period_1y')] as $val => $label)
                <a href="{{ route('portals.staff.analytics.ward', ['period' => $val]) }}"
                   class="btn btn-sm {{ $period === $val ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI Strip --}}
    @php $occMod = $occupancyRate >= 90 ? 'stat-card--danger' : ($occupancyRate >= 70 ? 'stat-card--warning' : 'stat-card--success'); @endphp
    <div class="stat-grid mb-6">
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="bed"></i></div>
            <div class="stat-card__value">{{ number_format($totalBeds) }}</div><div class="stat-card__label">{{ __('public.stf_analytics_ward_kpi_beds') }}</div>
        </div>
        <div class="stat-card {{ $occMod }}">
            <div class="stat-card__head"><i data-lucide="activity"></i></div>
            <div class="stat-card__value">{{ $occupancyRate }}%</div>
            <div class="stat-card__label">{{ __('public.stf_analytics_ward_kpi_occupancy') }}</div>
        </div>
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="arrow-down-to-line"></i></div>
            <div class="stat-card__value">{{ number_format($admissions) }}</div><div class="stat-card__label">{{ __('public.stf_analytics_ward_kpi_admissions') }}</div>
        </div>
        <div class="stat-card stat-card--success">
            <div class="stat-card__head"><i data-lucide="arrow-up-from-line"></i></div>
            <div class="stat-card__value">{{ number_format($discharges) }}</div><div class="stat-card__label">{{ __('public.stf_analytics_ward_kpi_discharges') }}</div>
        </div>
        <div class="stat-card stat-card--teal">
            <div class="stat-card__head"><i data-lucide="clock"></i></div>
            <div class="stat-card__value">{{ $avgLosHours !== null ? round($avgLosHours / 24, 1) . 'd' : '—' }}</div>
            <div class="stat-card__label">{{ __('public.stf_analytics_ward_kpi_los') }}</div>
        </div>
    </div>

    {{-- Ward-level Breakdown --}}
    <div class="portal-card">
        <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_ward_card_occupancy') }}</h2></div>
        <div class="portal-card__body panel-body--flush">
            <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('public.stf_analytics_ward_col_ward') }}</th>
                        <th>{{ __('public.stf_analytics_ward_col_total_beds') }}</th>
                        <th>{{ __('public.stf_analytics_ward_col_occupied') }}</th>
                        <th>{{ __('public.stf_analytics_ward_col_available') }}</th>
                        <th>{{ __('public.stf_analytics_ward_col_pct') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byWard as $row)
                        @php
                            $pct = $row->total_beds > 0 ? round($row->occupied / $row->total_beds * 100) : 0;
                            $fillMod = $pct >= 90 ? 'breakdown__fill--danger' : ($pct >= 70 ? 'breakdown__fill--warning' : '');
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.stf_analytics_ward_col_ward') }}" class="td-strong">{{ $row->ward_name }}</td>
                            <td data-label="{{ __('public.stf_analytics_ward_col_total_beds') }}">{{ $row->total_beds }}</td>
                            <td data-label="{{ __('public.stf_analytics_ward_col_occupied') }}" class="td-strong">{{ $row->occupied }}</td>
                            <td data-label="{{ __('public.stf_analytics_ward_col_available') }}">{{ $row->total_beds - $row->occupied }}</td>
                            <td data-label="{{ __('public.stf_analytics_ward_col_pct') }}">
                                <div class="breakdown__row breakdown__row--2col">
                                    <div class="breakdown__track"><div class="breakdown__fill {{ $fillMod }}" style="width:{{ $pct }}%;"></div></div>
                                    <span class="breakdown__value">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-cell td-muted">{{ __('public.stf_analytics_ward_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div>
@endsection
