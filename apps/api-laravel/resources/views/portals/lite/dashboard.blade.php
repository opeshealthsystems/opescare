@extends('layouts.lite')
@section('title', 'Dashboard')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
    <div>
        <h1 class="lite-page-title">Dashboard</h1>
        <p class="lite-page-sub">{{ now()->format('l, d F Y') }}</p>
    </div>
    <a href="{{ route('portals.staff') }}" class="lite-btn lite-btn--outline" style="font-size:0.78rem;padding:6px 12px;">
        <i data-lucide="monitor" style="width:14px;height:14px;"></i> Full Portal
    </a>
</div>

{{-- Today's queue summary --}}
<div class="lite-stat-row">
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val">{{ array_sum($todayQueue) }}</div>
        <div class="lite-stat-chip__label">Total Today</div>
    </div>
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val" style="color:#d97706;">{{ $todayQueue['waiting'] ?? 0 }}</div>
        <div class="lite-stat-chip__label">Waiting</div>
    </div>
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val" style="color:#16a34a;">{{ $todayQueue['completed'] ?? 0 }}</div>
        <div class="lite-stat-chip__label">Completed</div>
    </div>
    @if(($stats['open_conflicts'] ?? 0) > 0)
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val" style="color:#dc2626;">{{ $stats['open_conflicts'] }}</div>
        <div class="lite-stat-chip__label">Conflicts</div>
    </div>
    @endif
</div>

{{-- Quick actions --}}
<div class="lite-section-title">Quick Actions</div>
<div class="lite-grid">
    <a href="{{ route('portals.lite.lookup') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon" style="background:#eff6ff;">
            <i data-lucide="search" style="color:#2563eb;"></i>
        </div>
        Health ID Lookup
    </a>
    <a href="{{ route('portals.lite.register_patient') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon" style="background:#f0fdf4;">
            <i data-lucide="user-plus" style="color:#16a34a;"></i>
        </div>
        Register Patient
    </a>
    <a href="{{ route('portals.lite.checkin') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon" style="background:#fdf4ff;">
            <i data-lucide="log-in" style="color:#9333ea;"></i>
        </div>
        Check-In
    </a>
    <a href="{{ route('portals.lite.consultation') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon" style="background:#fff7ed;">
            <i data-lucide="stethoscope" style="color:#d97706;"></i>
        </div>
        Consultation
    </a>
    <a href="{{ route('portals.lite.billing') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon" style="background:#f0fdf4;">
            <i data-lucide="receipt" style="color:#16a34a;"></i>
        </div>
        Billing
    </a>
    <a href="{{ route('portals.lite.devices') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon" style="background:#f8fafc;">
            <i data-lucide="monitor-smartphone" style="color:#64748b;"></i>
        </div>
        Devices
    </a>
</div>

{{-- Recent patients --}}
@if($recentPatients->isNotEmpty())
<div class="lite-section-title">Recent Patients</div>
<div class="lite-card">
    <div class="lite-card__body" style="padding:0;">
        <table class="lite-table">
            <thead><tr><th>Patient</th><th>Health ID</th><th></th></tr></thead>
            <tbody>
                @foreach($recentPatients as $p)
                <tr>
                    <td style="font-weight:600;">{{ $p->first_name }} {{ $p->last_name }}</td>
                    <td style="font-family:monospace;font-size:0.8rem;">{{ $p->health_id }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('portals.lite.checkin', ['patient_id' => $p->id]) }}"
                           class="lite-btn lite-btn--outline" style="padding:4px 10px;font-size:0.78rem;">
                            Check-In
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Device health alert --}}
@if(($stats['open_conflicts'] ?? 0) > 0 || ($stats['pending_events'] ?? 0) > 0)
<div class="lite-alert lite-alert--warning" style="margin-top:8px;">
    <i data-lucide="alert-triangle" style="width:16px;height:16px;flex-shrink:0;"></i>
    <span>
        @if($stats['open_conflicts'] > 0)
            {{ $stats['open_conflicts'] }} open sync conflict(s).
            <a href="{{ route('portals.lite.conflicts') }}" style="color:inherit;font-weight:700;">Review →</a>
        @endif
        @if($stats['pending_events'] > 0)
            {{ $stats['pending_events'] }} event(s) pending sync.
        @endif
    </span>
</div>
@endif

@endsection
