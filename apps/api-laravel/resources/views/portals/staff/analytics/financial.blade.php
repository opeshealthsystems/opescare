@extends('layouts.portal')
@section('title', 'Financial Analytics')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="bar-chart-2" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Financial Analytics
            </h1>
            <p class="portal-page-subtitle">Revenue, collections, and outstanding balances</p>
        </div>
        <div style="display:flex;gap:4px;">
            @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year'] as $val => $label)
                <a href="{{ route('portals.staff.analytics.financial', ['period' => $val]) }}"
                   class="btn btn--sm {{ $period === $val ? 'btn--primary' : 'btn--outline' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="banknote" style="color:#16a34a;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#16a34a;">{{ number_format($revenue['collected'] ?? 0, 2) }}</div>
                <div class="stat-card__label">Collected</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;"><i data-lucide="trending-up" style="color:#7c3aed;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#7c3aed;">{{ number_format($revenue['billed'] ?? 0, 2) }}</div>
                <div class="stat-card__label">Total Billed</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fffbeb;"><i data-lucide="alert-triangle" style="color:#d97706;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#d97706;">{{ number_format($outstandingAmount, 2) }}</div>
                <div class="stat-card__label">Outstanding ({{ $outstandingCount }})</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="receipt" style="color:#2563eb;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ number_format($revenue['invoice_count'] ?? 0) }}</div>
                <div class="stat-card__label">Invoices</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="percent" style="color:#16a34a;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $revenue['collection_rate'] ?? 0 }}%</div>
                <div class="stat-card__label">Collection Rate</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        {{-- Payment Mode Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">Revenue by Payment Mode</h2></div>
            <div class="portal-card__body" style="padding:0;">
                <table class="portal-table">
                    <thead><tr><th>Payment Mode</th><th>Transactions</th><th>Amount</th></tr></thead>
                    <tbody>
                        @forelse($byPaymentMode as $row)
                            <tr>
                                <td>
                                    <span class="badge badge--info" style="font-size:0.72rem;">
                                        {{ ucfirst(str_replace('_', ' ', $row->payment_mode ?? 'Unknown')) }}
                                    </span>
                                </td>
                                <td style="font-size:0.84rem;">{{ number_format($row->cnt) }}</td>
                                <td style="font-weight:600;font-size:0.85rem;">{{ number_format($row->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;padding:20px;color:#9ca3af;">No payment data for period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Services --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">Top Revenue Services</h2></div>
            <div class="portal-card__body" style="padding:0;">
                <table class="portal-table">
                    <thead><tr><th>Service</th><th>Count</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse($topServices as $row)
                            <tr>
                                <td style="font-size:0.83rem;">{{ Str::limit($row->description ?? '—', 35) }}</td>
                                <td style="font-size:0.82rem;color:#6b7280;">{{ $row->cnt }}</td>
                                <td style="font-weight:600;font-size:0.85rem;">{{ number_format($row->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;padding:20px;color:#9ca3af;">No service data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Revenue Trend --}}
    @if(!empty($revTrend))
    <div class="portal-card" style="margin-top:16px;">
        <div class="portal-card__header"><h2 class="portal-card__title">Daily Revenue Trend</h2></div>
        <div class="portal-card__body">
            <div style="display:flex;align-items:flex-end;gap:3px;height:80px;">
                @php $maxRev = max($revTrend) ?: 1; @endphp
                @foreach($revTrend as $day => $amt)
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;">
                        <div title="{{ $day }}: {{ number_format($amt, 2) }}"
                             style="width:100%;background:#16a34a;opacity:0.75;border-radius:2px 2px 0 0;
                                    height:{{ max(2, round($amt / $maxRev * 70)) }}px;"></div>
                        <div style="font-size:0.58rem;color:#9ca3af;writing-mode:vertical-rl;transform:rotate(180deg);">
                            {{ \Carbon\Carbon::parse($day)->format('d M') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
