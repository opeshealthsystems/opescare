@extends('layouts.portal')

@section('title', 'Telemedicine')

@section('content')
<div class="page-head">
    <h2>Telemedicine</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.telemedicine.waiting_room') }}" class="btn btn-secondary btn-sm">
        Waiting Room
        @if($waiting > 0)
            <span class="badge badge-danger">{{ $waiting }}</span>
        @endif
    </a>
    <a href="{{ route('portals.staff.telemedicine.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> Schedule Consultation
    </a>
</div>
<p class="page-subtitle mb-4">Virtual consultations for this facility</p>

{{-- CDSS Disclaimer --}}
<div class="alert alert-info mb-4">
    <i data-lucide="info"></i>
    <div>
        <strong>Clinical Note:</strong> OpesCare facilitates teleconsultation connections and records.
        Clinical decisions are the sole responsibility of the provider. This platform does not replace clinical judgment.
    </div>
</div>

{{-- Stats strip --}}
<div class="stat-grid">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__value">{{ $today }}</div>
        <div class="stat-card__label">Today's Consultations</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__value">{{ $waiting }}</div>
        <div class="stat-card__label">In Waiting Room</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__value">{{ $completed }}</div>
        <div class="stat-card__label">Completed Today</div>
    </div>
    <div class="stat-card stat-card--teal">
        <div class="stat-card__value">{{ $scheduled->total() }}</div>
        <div class="stat-card__label">Scheduled / Waiting</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mt-3 mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mt-3 mb-4"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Consultations table --}}
<div class="panel mt-6">
    <div class="panel-header">
        <h3 class="panel-title">Scheduled &amp; Waiting</h3>
    </div>
    <div class="panel-body panel-body--flush">
        @if($scheduled->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="video"></i></div>
                <p>No scheduled or waiting consultations.</p>
                <a href="{{ route('portals.staff.telemedicine.create') }}" class="btn btn-primary btn-sm mt-3">
                    Schedule Consultation
                </a>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Scheduled</th>
                            <th>Platform</th>
                            <th>Status</th>
                            <th class="row-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduled as $c)
                        @php
                            $tcBadge = match($c->status) {
                                'scheduled' => 'badge-info',
                                'waiting'   => 'badge-warning',
                                'active'    => 'badge-success',
                                'completed' => 'badge-neutral',
                                'cancelled', 'failed' => 'badge-danger',
                                default     => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="Patient">
                                @if($c->patient)
                                    {{ $c->patient->first_name }} {{ $c->patient->last_name }}
                                @else
                                    <span class="td-muted">—</span>
                                @endif
                            </td>
                            <td data-label="Scheduled">{{ $c->scheduled_at ? $c->scheduled_at->format('d M Y H:i') : '—' }}</td>
                            <td data-label="Platform">{{ ucfirst($c->platform ?? 'own') }}</td>
                            <td data-label="Status"><span class="badge {{ $tcBadge }}">{{ $c->status }}</span></td>
                            <td class="row-actions" data-label="Actions">
                                <a href="{{ route('portals.staff.telemedicine.show', $c->id) }}" class="btn btn-ghost btn-sm">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-body">
                {{ $scheduled->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
