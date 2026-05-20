@extends('layouts.portal')

@section('title', 'Telemedicine')

@section('content')
<div class="page-header">
    <div class="page-header__left">
        <h1 class="page-title">Telemedicine</h1>
        <p class="page-subtitle">Virtual consultations for this facility</p>
    </div>
    <div class="page-header__actions">
        <a href="{{ route('portals.staff.telemedicine.waiting_room') }}" class="btn btn--outline btn--sm">
            Waiting Room
            @if($waiting > 0)
                <span class="badge badge--danger ml-1">{{ $waiting }}</span>
            @endif
        </a>
        <a href="{{ route('portals.staff.telemedicine.create') }}" class="btn btn--primary btn--sm">
            + Schedule Consultation
        </a>
    </div>
</div>

{{-- CDSS Disclaimer --}}
<div class="alert alert--info mb-4">
    <strong>Clinical Note:</strong> OpesCare facilitates teleconsultation connections and records.
    Clinical decisions are the sole responsibility of the provider. This platform does not replace clinical judgment.
</div>

{{-- Stats strip --}}
<div class="stats-strip mb-4">
    <div class="stat-card">
        <div class="stat-card__value">{{ $today }}</div>
        <div class="stat-card__label">Today's Consultations</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value">{{ $waiting }}</div>
        <div class="stat-card__label">In Waiting Room</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value">{{ $completed }}</div>
        <div class="stat-card__label">Completed Today</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value">{{ $scheduled->total() }}</div>
        <div class="stat-card__label">Scheduled / Waiting</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--danger mb-4">{{ session('error') }}</div>
@endif

{{-- Consultations table --}}
<div class="card">
    <div class="card__header">
        <h3 class="card__title">Scheduled & Waiting</h3>
    </div>
    <div class="card__body p-0">
        @if($scheduled->isEmpty())
            <div class="empty-state p-6">
                <p>No scheduled or waiting consultations.</p>
                <a href="{{ route('portals.staff.telemedicine.create') }}" class="btn btn--primary btn--sm mt-2">
                    Schedule Consultation
                </a>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Scheduled</th>
                        <th>Platform</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scheduled as $c)
                    <tr>
                        <td>
                            @if($c->patient)
                                {{ $c->patient->first_name }} {{ $c->patient->last_name }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $c->scheduled_at ? $c->scheduled_at->format('d M Y H:i') : '—' }}</td>
                        <td>{{ ucfirst($c->platform ?? 'own') }}</td>
                        <td><span class="{{ $c->statusBadgeClass() }}">{{ $c->status }}</span></td>
                        <td>
                            <a href="{{ route('portals.staff.telemedicine.show', $c->id) }}"
                               class="btn btn--outline btn--xs">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 pb-4">
                {{ $scheduled->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
