@extends('layouts.portal')
@section('title', 'Ward & Bed Analytics')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="bed" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Ward & Bed Analytics
            </h1>
            <p class="portal-page-subtitle">Occupancy, admissions, and length of stay</p>
        </div>
        <div style="display:flex;gap:4px;">
            @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year'] as $val => $label)
                <a href="{{ route('portals.staff.analytics.ward', ['period' => $val]) }}"
                   class="btn btn--sm {{ $period === $val ? 'btn--primary' : 'btn--outline' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;"><i data-lucide="bed" style="color:#7c3aed;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($totalBeds) }}</div><div class="stat-card__label">Total Beds</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fffbeb;"><i data-lucide="activity" style="color:#d97706;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:{{ $occupancyRate >= 90 ? '#dc2626' : ($occupancyRate >= 70 ? '#d97706' : '#16a34a') }};">
                    {{ $occupancyRate }}%
                </div>
                <div class="stat-card__label">Occupancy</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="arrow-down-to-line" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($admissions) }}</div><div class="stat-card__label">Admissions</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="arrow-up-from-line" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($discharges) }}</div><div class="stat-card__label">Discharges</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fdf2f8;"><i data-lucide="clock" style="color:#9333ea;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value">{{ $avgLosHours !== null ? round($avgLosHours / 24, 1) . 'd' : '—' }}</div>
                <div class="stat-card__label">Avg Length of Stay</div>
            </div>
        </div>
    </div>

    {{-- Ward-level Breakdown --}}
    <div class="portal-card">
        <div class="portal-card__header"><h2 class="portal-card__title">Occupancy by Ward</h2></div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th>Ward</th><th>Total Beds</th><th>Occupied</th><th>Available</th><th>Occupancy %</th></tr>
                </thead>
                <tbody>
                    @forelse($byWard as $row)
                        @php
                            $pct = $row->total_beds > 0 ? round($row->occupied / $row->total_beds * 100) : 0;
                        @endphp
                        <tr>
                            <td style="font-weight:600;font-size:0.85rem;">{{ $row->ward_name }}</td>
                            <td style="font-size:0.85rem;">{{ $row->total_beds }}</td>
                            <td style="font-size:0.85rem;color:#d97706;font-weight:600;">{{ $row->occupied }}</td>
                            <td style="font-size:0.85rem;color:#16a34a;">{{ $row->total_beds - $row->occupied }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="flex:1;height:6px;background:#e5e7eb;border-radius:3px;overflow:hidden;min-width:60px;">
                                        <div style="height:100%;background:{{ $pct >= 90 ? '#dc2626' : ($pct >= 70 ? '#d97706' : '#7c3aed') }};width:{{ $pct }}%;border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:0.82rem;font-weight:600;color:{{ $pct >= 90 ? '#dc2626' : '#374151' }};">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:#9ca3af;">No ward data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
