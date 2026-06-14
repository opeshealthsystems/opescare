@extends('layouts.portal')

@section('title', 'Virtual Waiting Room')

@section('content')
<div class="page-head">
    <h2>Virtual Waiting Room</h2>
    <div class="page-head__spacer"></div>
    @if($waiting->isNotEmpty())
        <form action="{{ route('portals.staff.telemedicine.call_next') }}" method="POST" class="inline-form">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">Call Next Patient</button>
        </form>
    @endif
    <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> Telemedicine
    </a>
</div>

<div class="stat-grid">
    <div class="stat-card stat-card--warning">
        <div class="stat-card__value">{{ $waiting->count() }}</div>
        <div class="stat-card__label">Waiting</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__value">~{{ $estimated }} min</div>
        <div class="stat-card__label">Est. Wait Time</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mt-3 mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('info'))
    <div class="alert alert-info mt-3 mb-4"><i data-lucide="info"></i><div>{{ session('info') }}</div></div>
@endif

<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title">Patients Waiting</h3></div>
    <div class="panel-body panel-body--flush">
        @if($waiting->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="users"></i></div>
                <p>No patients in the virtual waiting room.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Joined At</th>
                            <th>Wait Time</th>
                            <th>Status</th>
                            <th class="row-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($waiting as $i => $entry)
                        <tr>
                            <td data-label="#">{{ $i + 1 }}</td>
                            <td data-label="Patient">
                                @if($entry->patient)
                                    {{ $entry->patient->first_name }} {{ $entry->patient->last_name }}
                                @else — @endif
                            </td>
                            <td data-label="Joined At">{{ $entry->joined_at?->format('H:i') }}</td>
                            <td data-label="Wait Time">{{ $entry->waitMinutes() !== null ? $entry->waitMinutes() . ' min' : '—' }}</td>
                            <td data-label="Status"><span class="badge badge-info">{{ $entry->status }}</span></td>
                            <td class="row-actions" data-label="Actions">
                                <a href="{{ route('portals.staff.telemedicine.show', $entry->teleconsultation_id) }}" class="btn btn-ghost btn-sm">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
