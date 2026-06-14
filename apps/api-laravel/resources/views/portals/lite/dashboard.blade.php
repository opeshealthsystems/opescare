@extends('layouts.lite')
@section('title', 'Dashboard')

@section('content')

<div class="lite-page-head">
    <div>
        <h1 class="lite-page-title">Dashboard</h1>
        <p class="lite-page-sub">{{ now()->format('l, d F Y') }}</p>
    </div>
    <a href="{{ route('portals.staff') }}" class="lite-btn lite-btn--outline lite-btn--sm">
        <i data-lucide="monitor"></i> Full portal
    </a>
</div>

{{-- Today's queue summary --}}
<div class="lite-stat-row">
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val">{{ array_sum($todayQueue) }}</div>
        <div class="lite-stat-chip__label">Total today</div>
    </div>
    <div class="lite-stat-chip lite-stat-chip--warning">
        <div class="lite-stat-chip__val">{{ $todayQueue['waiting'] ?? 0 }}</div>
        <div class="lite-stat-chip__label">Waiting</div>
    </div>
    <div class="lite-stat-chip lite-stat-chip--success">
        <div class="lite-stat-chip__val">{{ $todayQueue['completed'] ?? 0 }}</div>
        <div class="lite-stat-chip__label">Completed</div>
    </div>
    @if(($stats['open_conflicts'] ?? 0) > 0)
    <div class="lite-stat-chip lite-stat-chip--danger">
        <div class="lite-stat-chip__val">{{ $stats['open_conflicts'] }}</div>
        <div class="lite-stat-chip__label">Conflicts</div>
    </div>
    @endif
</div>

{{-- Quick actions --}}
<div class="lite-section-title">Quick actions</div>
<div class="lite-grid">
    <a href="{{ route('portals.lite.lookup') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon lite-btn-card__icon--info"><i data-lucide="search"></i></div>
        Health ID lookup
    </a>
    <a href="{{ route('portals.lite.register_patient') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon lite-btn-card__icon--success"><i data-lucide="user-plus"></i></div>
        Register patient
    </a>
    <a href="{{ route('portals.lite.checkin') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon lite-btn-card__icon--info"><i data-lucide="log-in"></i></div>
        Check-in
    </a>
    <a href="{{ route('portals.lite.consultation') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon lite-btn-card__icon--warning"><i data-lucide="stethoscope"></i></div>
        Consultation
    </a>
    <a href="{{ route('portals.lite.billing') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon lite-btn-card__icon--success"><i data-lucide="receipt"></i></div>
        Billing
    </a>
    <a href="{{ route('portals.lite.devices') }}" class="lite-btn-card">
        <div class="lite-btn-card__icon"><i data-lucide="monitor-smartphone"></i></div>
        Devices
    </a>
</div>

{{-- Recent patients --}}
@if($recentPatients->isNotEmpty())
<div class="lite-section-title">Recent patients</div>
<div class="lite-card">
    <div class="lite-card__body lite-card__body--flush">
        <table class="lite-table">
            <thead><tr><th>Patient</th><th>Health ID</th><th></th></tr></thead>
            <tbody>
                @foreach($recentPatients as $p)
                <tr>
                    <td class="lite-td-strong">{{ $p->first_name }} {{ $p->last_name }}</td>
                    <td class="lite-mono">{{ $p->health_id }}</td>
                    <td class="lite-td-right">
                        <a href="{{ route('portals.lite.checkin', ['patient_id' => $p->id]) }}" class="lite-btn lite-btn--outline lite-btn--sm">
                            Check-in
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
<div class="lite-alert lite-alert--warning">
    <i data-lucide="alert-triangle"></i>
    <span>
        @if($stats['open_conflicts'] > 0)
            {{ $stats['open_conflicts'] }} open sync conflict(s).
            <a href="{{ route('portals.lite.conflicts') }}" class="lite-alert__link">Review →</a>
        @endif
        @if($stats['pending_events'] > 0)
            {{ $stats['pending_events'] }} event(s) pending sync.
        @endif
    </span>
</div>
@endif

@endsection
