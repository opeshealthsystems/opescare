@extends('layouts.portal')
@section('title', 'Financial Analytics')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Financial Analytics</h1>
            <p class="portal-page-subtitle">Revenue, collections, and outstanding balances</p>
        </div>
        <div class="filter-bar filter-bar--flush">
            @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year'] as $val => $label)
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
            <div class="stat-card__label">Collected</div>
        </div>
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="trending-up"></i></div>
            <div class="stat-card__value">{{ number_format($revenue['billed'] ?? 0, 2) }}</div>
            <div class="stat-card__label">Total Billed</div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-card__head"><i data-lucide="alert-triangle"></i></div>
            <div class="stat-card__value">{{ number_format($outstandingAmount, 2) }}</div>
            <div class="stat-card__label">Outstanding ({{ $outstandingCount }})</div>
        </div>
        <div class="stat-card stat-card--primary">
            <div class="stat-card__head"><i data-lucide="receipt"></i></div>
            <div class="stat-card__value">{{ number_format($revenue['invoice_count'] ?? 0) }}</div>
            <div class="stat-card__label">Invoices</div>
        </div>
        <div class="stat-card stat-card--success">
            <div class="stat-card__head"><i data-lucide="percent"></i></div>
            <div class="stat-card__value">{{ $revenue['collection_rate'] ?? 0 }}%</div>
            <div class="stat-card__label">Collection Rate</div>
        </div>
    </div>

    <div class="grid-2">

        {{-- Payment Mode Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">Revenue by Payment Mode</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Payment Mode</th><th>Transactions</th><th>Amount</th></tr></thead>
                    <tbody>
                        @forelse($byPaymentMode as $row)
                            <tr>
                                <td data-label="Payment Mode">
                                    <span class="badge badge--info">
                                        {{ ucfirst(str_replace('_', ' ', $row->payment_mode ?? 'Unknown')) }}
                                    </span>
                                </td>
                                <td data-label="Transactions">{{ number_format($row->cnt) }}</td>
                                <td data-label="Amount" class="td-strong">{{ number_format($row->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">No payment data for period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- Top Services --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">Top Revenue Services</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Service</th><th>Count</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse($topServices as $row)
                            <tr>
                                <td data-label="Service">{{ Str::limit($row->description ?? '—', 35) }}</td>
                                <td data-label="Count" class="td-muted">{{ $row->cnt }}</td>
                                <td data-label="Revenue" class="td-strong">{{ number_format($row->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">No service data.</td></tr>
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
        <div class="portal-card__header"><h2 class="portal-card__title">Daily Revenue Trend</h2></div>
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
