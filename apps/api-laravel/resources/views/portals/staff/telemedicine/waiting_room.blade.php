@extends('layouts.portal')

@section('title', 'Virtual Waiting Room')

@section('content')
<div class="page-header">
    <div class="page-header__left">
        <a href="{{ route('portals.staff.telemedicine.index') }}" class="back-link">← Telemedicine</a>
        <h1 class="page-title">Virtual Waiting Room</h1>
    </div>
    <div class="page-header__actions">
        @if($waiting->isNotEmpty())
            <form action="{{ route('portals.staff.telemedicine.call_next') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn--primary btn--sm">Call Next Patient</button>
            </form>
        @endif
    </div>
</div>

<div class="stats-strip mb-4">
    <div class="stat-card">
        <div class="stat-card__value">{{ $waiting->count() }}</div>
        <div class="stat-card__label">Waiting</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value">~{{ $estimated }} min</div>
        <div class="stat-card__label">Est. Wait Time</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success mb-4">{{ session('success') }}</div>
@endif
@if(session('info'))
    <div class="alert alert--info mb-4">{{ session('info') }}</div>
@endif

<div class="card">
    <div class="card__header"><h3 class="card__title">Patients Waiting</h3></div>
    <div class="card__body p-0">
        @if($waiting->isEmpty())
            <div class="empty-state p-6">
                <p>No patients in the virtual waiting room.</p>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Joined At</th>
                        <th>Wait Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waiting as $i => $entry)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            @if($entry->patient)
                                {{ $entry->patient->first_name }} {{ $entry->patient->last_name }}
                            @else —
                            @endif
                        </td>
                        <td>{{ $entry->joined_at?->format('H:i') }}</td>
                        <td>{{ $entry->waitMinutes() !== null ? $entry->waitMinutes() . ' min' : '—' }}</td>
                        <td><span class="badge badge--info">{{ $entry->status }}</span></td>
                        <td>
                            <a href="{{ route('portals.staff.telemedicine.show', $entry->teleconsultation_id) }}"
                               class="btn btn--outline btn--xs">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
