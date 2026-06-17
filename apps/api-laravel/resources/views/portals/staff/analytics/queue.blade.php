@extends('layouts.portal')
@section('title', 'Queue Analytics')

@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">{{ __('public.stf_analytics_queue_title') }}</h1>
            <p class="portal-page-subtitle">{{ __('public.stf_analytics_queue_subtitle') }}</p>
        </div>
        <div class="filter-bar filter-bar--flush">
            @foreach(['7d' => __('public.stf_analytics_period_7d'), '30d' => __('public.stf_analytics_period_30d'), '90d' => __('public.stf_analytics_period_90d'), '1y' => __('public.stf_analytics_period_1y')] as $val => $label)
                <a href="{{ route('portals.staff.analytics.queue', ['period' => $val]) }}"
                   class="btn btn-sm {{ $period === $val ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="stat-grid mb-6">
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="users"></i></div>
            <div class="stat-card__value">{{ number_format($totalQueued) }}</div><div class="stat-card__label">{{ __('public.stf_analytics_queue_kpi_queued') }}</div>
        </div>
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="clock"></i></div>
            <div class="stat-card__value">{{ $avgWaitMin !== null ? round($avgWaitMin) . ' min' : '—' }}</div>
            <div class="stat-card__label">{{ __('public.stf_analytics_queue_kpi_wait') }}</div>
        </div>
        <div class="stat-card stat-card--success">
            <div class="stat-card__head"><i data-lucide="check-circle"></i></div>
            <div class="stat-card__value">{{ number_format($byStatus['completed'] ?? 0) }}</div><div class="stat-card__label">{{ __('public.stf_analytics_queue_kpi_completed') }}</div>
        </div>
        <div class="stat-card stat-card--danger">
            <div class="stat-card__head"><i data-lucide="x-circle"></i></div>
            <div class="stat-card__value">{{ number_format($byStatus['cancelled'] ?? 0) }}</div><div class="stat-card__label">{{ __('public.stf_analytics_queue_kpi_cancelled') }}</div>
        </div>
    </div>

    <div class="grid-2">

        {{-- Status Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_queue_card_status') }}</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>{{ __('public.stf_analytics_queue_col_status') }}</th><th>{{ __('public.stf_analytics_queue_col_count') }}</th><th>{{ __('public.stf_analytics_queue_col_share') }}</th></tr></thead>
                    <tbody>
                        @forelse($byStatus as $status => $count)
                            <tr>
                                <td data-label="{{ __('public.stf_analytics_queue_col_status') }}"><span class="badge badge--{{ match($status) {
                                    'waiting'   => 'warning',
                                    'called'    => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default     => 'default',
                                } }}">{{ ucfirst($status) }}</span></td>
                                <td data-label="{{ __('public.stf_analytics_queue_col_count') }}" class="td-strong">{{ number_format($count) }}</td>
                                <td data-label="{{ __('public.stf_analytics_queue_col_share') }}" class="td-muted">
                                    {{ $totalQueued > 0 ? round($count / $totalQueued * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">{{ __('public.stf_analytics_queue_empty_period') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- Priority Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_queue_card_priority') }}</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>{{ __('public.stf_analytics_queue_col_priority') }}</th><th>{{ __('public.stf_analytics_queue_col_count') }}</th><th>{{ __('public.stf_analytics_queue_col_share') }}</th></tr></thead>
                    <tbody>
                        @forelse($byPriority as $priority => $count)
                            <tr>
                                <td data-label="{{ __('public.stf_analytics_queue_col_priority') }}"><span class="badge badge--{{ $priority <= 1 ? 'danger' : ($priority <= 3 ? 'warning' : 'default') }}">P{{ $priority }}</span></td>
                                <td data-label="{{ __('public.stf_analytics_queue_col_count') }}" class="td-strong">{{ number_format($count) }}</td>
                                <td data-label="{{ __('public.stf_analytics_queue_col_share') }}" class="td-muted">
                                    {{ $totalQueued > 0 ? round($count / $totalQueued * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">{{ __('public.stf_analytics_queue_empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Daily Trend --}}
    @if(!empty($dailyTrend))
    <div class="portal-card mt-6">
        <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_queue_daily_volume') }}</h2></div>
        <div class="portal-card__body">
            <div class="bar-chart">
                @php $maxVal = max($dailyTrend) ?: 1; @endphp
                @foreach($dailyTrend as $day => $cnt)
                    <div class="bar-chart__col">
                        <div class="bar-chart__val">{{ $cnt }}</div>
                        <div class="bar-chart__bar" style="height:{{ max(2, round($cnt / $maxVal * 100)) }}%;"></div>
                        <div class="bar-chart__label">{{ \Carbon\Carbon::parse($day)->format('d M') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
