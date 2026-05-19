@extends('layouts.portal')
@section('title', 'KPI Trend — ' . ($definition?->name ?? 'Metric'))
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.kpi.index') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← KPI Dashboard</a>
            <h1 class="portal-page-title" style="margin-top:4px;">
                <i data-lucide="trending-up" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                {{ $definition?->name ?? 'Metric Trend' }}
            </h1>
            @if($definition)
            <p class="portal-page-subtitle">
                {{ $definition->description }}
                <span class="badge badge--info" style="margin-left:8px;font-size:0.72rem;">{{ ucfirst($definition->category) }}</span>
            </p>
            @endif
        </div>
    </div>

    {{-- Controls --}}
    <div class="portal-card" style="margin-bottom:16px;">
        <div class="portal-card__body" style="padding:14px 18px;">
            <form method="GET" action="{{ route('portals.admin.kpi.trend') }}"
                  style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">Metric</label>
                    <select name="metric" onchange="this.form.submit()"
                            style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;min-width:220px;">
                        @foreach($allMetrics->groupBy(fn($m) => ucfirst($m->category)) as $cat => $group)
                        <optgroup label="{{ $cat }}">
                            @foreach($group as $m)
                            <option value="{{ $m->slug }}" {{ $slug === $m->slug ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">Period</label>
                    <select name="period" onchange="this.form.submit()"
                            style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
                        <option value="7d" {{ $period === '7d' ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30d" {{ $period === '30d' ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90d" {{ $period === '90d' ? 'selected' : '' }}>Last 90 days</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($definition && $trendData)
    {{-- Metric Info --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;">
        @php
            $values = array_values(array_filter($trendData, fn($v) => $v !== null));
            $avg = count($values) > 0 ? array_sum($values) / count($values) : null;
            $max = count($values) > 0 ? max($values) : null;
            $min = count($values) > 0 ? min($values) : null;
            $latest = count($values) > 0 ? end($values) : null;
        @endphp
        <div class="stat-card">
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $latest !== null ? number_format($latest, $definition->display_format === 'percentage' ? 1 : 0) . ($definition->display_format === 'percentage' ? '%' : '') : '—' }}</div>
                <div class="stat-card__label">Latest</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $avg !== null ? number_format($avg, 1) : '—' }}</div>
                <div class="stat-card__label">Average</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $max !== null ? number_format($max, 1) : '—' }}</div>
                <div class="stat-card__label">Peak</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $min !== null ? number_format($min, 1) : '—' }}</div>
                <div class="stat-card__label">Minimum</div>
            </div>
        </div>
        @if($definition->target_value)
        <div class="stat-card">
            <div class="stat-card__body">
                <div class="stat-card__value">{{ number_format($definition->target_value, 1) }}</div>
                <div class="stat-card__label">Target</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Trend Table --}}
    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title">Daily Values — {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</h2>
        </div>
        <div class="portal-card__body" style="padding:0;">
            @if(empty($trendData))
            <div style="text-align:center;padding:40px;color:#9ca3af;">No snapshot data available for this period.</div>
            @else
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Value</th>
                        @if($definition->target_value)
                        <th>vs Target</th>
                        @endif
                        <th>Bar</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxVal = $max ?: 1; @endphp
                    @foreach($trendData as $date => $value)
                    <tr>
                        <td style="font-size:0.84rem;color:#374151;">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                        <td style="font-weight:600;font-size:0.88rem;">
                            {{ $value !== null ? number_format($value, $definition->display_format === 'percentage' ? 1 : 0) . ($definition->display_format === 'percentage' ? '%' : '') : '—' }}
                        </td>
                        @if($definition->target_value)
                        <td style="font-size:0.82rem;">
                            @if($value !== null)
                                @php $diff = $value - $definition->target_value; @endphp
                                <span style="color:{{ $diff >= 0 ? '#16a34a' : '#dc2626' }};">
                                    {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 1) }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        @endif
                        <td style="min-width:120px;">
                            @if($value !== null)
                            <div style="background:#e5e7eb;border-radius:99px;height:6px;">
                                <div style="width:{{ min(100, ($value / $maxVal) * 100) }}%;background:#7c3aed;border-radius:99px;height:6px;"></div>
                            </div>
                            @else
                            <span style="color:#d1d5db;font-size:0.75rem;">no data</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @else
    <div class="portal-card">
        <div class="portal-card__body" style="text-align:center;padding:40px;color:#9ca3af;">
            Select a metric to view its trend.
        </div>
    </div>
    @endif

</div>
@endsection
