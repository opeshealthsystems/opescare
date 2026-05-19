@extends('layouts.portal')
@section('title', 'Queue Analytics')

@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="list-ordered" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Queue Analytics
            </h1>
            <p class="portal-page-subtitle">Patient queue performance & wait time analysis</p>
        </div>
        <form method="GET" style="display:flex;gap:4px;">
            @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year'] as $val => $label)
                <a href="{{ route('portals.staff.analytics.queue', ['period' => $val]) }}"
                   class="btn btn--sm {{ $period === $val ? 'btn--primary' : 'btn--outline' }}">{{ $label }}</a>
            @endforeach
        </form>
    </div>

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;"><i data-lucide="users" style="color:#7c3aed;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($totalQueued) }}</div><div class="stat-card__label">Total Queued</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="clock" style="color:#2563eb;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $avgWaitMin !== null ? round($avgWaitMin) . ' min' : '—' }}</div>
                <div class="stat-card__label">Avg Wait Time</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="check-circle" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($byStatus['completed'] ?? 0) }}</div><div class="stat-card__label">Completed</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;"><i data-lucide="x-circle" style="color:#dc2626;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($byStatus['cancelled'] ?? 0) }}</div><div class="stat-card__label">Cancelled / DNA</div></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        {{-- Status Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">By Status</h2></div>
            <div class="portal-card__body" style="padding:0;">
                <table class="portal-table">
                    <thead><tr><th>Status</th><th>Count</th><th>Share</th></tr></thead>
                    <tbody>
                        @forelse($byStatus as $status => $count)
                            <tr>
                                <td><span class="badge badge--{{ match($status) {
                                    'waiting'   => 'warning',
                                    'called'    => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default     => 'default',
                                } }}" style="font-size:0.72rem;">{{ ucfirst($status) }}</span></td>
                                <td style="font-weight:600;">{{ number_format($count) }}</td>
                                <td style="font-size:0.82rem;color:#6b7280;">
                                    {{ $totalQueued > 0 ? round($count / $totalQueued * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;padding:16px;color:#9ca3af;">No data for period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Priority Breakdown --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">By Priority</h2></div>
            <div class="portal-card__body" style="padding:0;">
                <table class="portal-table">
                    <thead><tr><th>Priority</th><th>Count</th><th>Share</th></tr></thead>
                    <tbody>
                        @forelse($byPriority as $priority => $count)
                            <tr>
                                <td><span class="badge badge--{{ $priority <= 1 ? 'danger' : ($priority <= 3 ? 'warning' : 'default') }}" style="font-size:0.72rem;">P{{ $priority }}</span></td>
                                <td style="font-weight:600;">{{ number_format($count) }}</td>
                                <td style="font-size:0.82rem;color:#6b7280;">
                                    {{ $totalQueued > 0 ? round($count / $totalQueued * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;padding:16px;color:#9ca3af;">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Daily Trend --}}
    @if(!empty($dailyTrend))
    <div class="portal-card" style="margin-top:16px;">
        <div class="portal-card__header"><h2 class="portal-card__title">Daily Queue Volume</h2></div>
        <div class="portal-card__body">
            <div style="display:flex;align-items:flex-end;gap:3px;height:80px;">
                @php $maxVal = max($dailyTrend) ?: 1; @endphp
                @foreach($dailyTrend as $day => $cnt)
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;">
                        <div title="{{ $day }}: {{ $cnt }}"
                             style="width:100%;background:#7c3aed;opacity:0.75;border-radius:2px 2px 0 0;
                                    height:{{ max(2, round($cnt / $maxVal * 70)) }}px;"></div>
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
