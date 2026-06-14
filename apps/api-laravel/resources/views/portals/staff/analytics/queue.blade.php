@extends('layouts.portal')
@section('title', 'Queue Analytics')

@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Queue Analytics</h1>
            <p class="portal-page-subtitle">Patient queue performance &amp; wait time analysis</p>
        </div>
        <div class="filter-bar filter-bar--flush">
            @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year'] as $val => $label)
                <a href="{{ route('portals.staff.analytics.queue', ['period' => $val]) }}"
                   class="btn btn-sm {{ $period === $val ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="stat-grid mb-6">
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="users"></i></div>
            <div class="stat-card__value">{{ number_format($totalQueued) }}</div><div class="stat-card__label">Total Queued</div>
        </div>
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="clock"></i></div>
            <div class="stat-card__value">{{ $avgWaitMin !== null ? round($avgWaitMin) . ' min' : '—' }}</div>
            <div class="stat-card__label">Avg Wait Time</div>
        </div>
        <div class="stat-card stat-card--success">
            <div class="stat-card__head"><i data-lucide="check-circle"></i></div>
            <div class="stat-card__value">{{ number_format($byStatus['completed'] ?? 0) }}</div><div class="stat-card__label">Completed</div>
        </div>
        <div class="stat-card stat-card--danger">
            <div class="stat-card__head"><i data-lucide="x-circle"></i></div>
            <div class="stat-card__value">{{ number_format($byStatus['cancelled'] ?? 0) }}</div><div class="stat-card__label">Cancelled / DNA</div>
        </div>
    </div>

    <div class="grid-2">

        {{-- Status Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">By Status</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Status</th><th>Count</th><th>Share</th></tr></thead>
                    <tbody>
                        @forelse($byStatus as $status => $count)
                            <tr>
                                <td data-label="Status"><span class="badge badge--{{ match($status) {
                                    'waiting'   => 'warning',
                                    'called'    => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default     => 'default',
                                } }}">{{ ucfirst($status) }}</span></td>
                                <td data-label="Count" class="td-strong">{{ number_format($count) }}</td>
                                <td data-label="Share" class="td-muted">
                                    {{ $totalQueued > 0 ? round($count / $totalQueued * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">No data for period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- Priority Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">By Priority</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Priority</th><th>Count</th><th>Share</th></tr></thead>
                    <tbody>
                        @forelse($byPriority as $priority => $count)
                            <tr>
                                <td data-label="Priority"><span class="badge badge--{{ $priority <= 1 ? 'danger' : ($priority <= 3 ? 'warning' : 'default') }}">P{{ $priority }}</span></td>
                                <td data-label="Count" class="td-strong">{{ number_format($count) }}</td>
                                <td data-label="Share" class="td-muted">
                                    {{ $totalQueued > 0 ? round($count / $totalQueued * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">No data.</td></tr>
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
        <div class="portal-card__header"><h2 class="portal-card__title">Daily Queue Volume</h2></div>
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
