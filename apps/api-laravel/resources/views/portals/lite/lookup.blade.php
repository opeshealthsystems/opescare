@extends('layouts.lite')
@section('title', 'Health ID Lookup')

@section('content')

<h1 class="lite-page-title">Health ID lookup</h1>
<p class="lite-page-sub">Search by Health ID, name, or phone number</p>

<form method="GET" action="{{ route('portals.lite.lookup') }}">
    <div class="lite-row lite-mb">
        <input type="text" name="q" value="{{ $query }}" placeholder="Health ID, name, or phone…" class="lite-input lite-flex-1" autofocus>
        <button type="submit" class="lite-btn lite-btn--primary">
            <i data-lucide="search"></i> Search
        </button>
    </div>
</form>

@if(strlen($query) >= 2)
    @if($patients->isEmpty())
        <div class="lite-alert lite-alert--info">
            <i data-lucide="info"></i>
            <span>No patients found matching "{{ $query }}".
            <a href="{{ route('portals.lite.register_patient') }}" class="lite-alert__link">Register new patient →</a></span>
        </div>
    @else
        <div class="lite-card">
            <div class="lite-card__head">{{ $patients->count() }} result(s) for "{{ $query }}"</div>
            <div class="lite-card__body lite-card__body--flush">
                <table class="lite-table">
                    <thead><tr><th>Name</th><th>Health ID</th><th>DOB</th><th></th></tr></thead>
                    <tbody>
                        @foreach($patients as $p)
                        <tr>
                            <td class="lite-td-strong">{{ $p->first_name }} {{ $p->last_name }}</td>
                            <td class="lite-mono">{{ $p->health_id }}</td>
                            <td class="lite-mono">{{ $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->format('d M Y') : '—' }}</td>
                            <td>
                                <div class="lite-row lite-row--end">
                                    <a href="{{ route('portals.lite.checkin', ['patient_id' => $p->id]) }}" class="lite-btn lite-btn--primary lite-btn--sm">Check-in</a>
                                    <a href="{{ route('portals.lite.consultation', ['patient_id' => $p->id]) }}" class="lite-btn lite-btn--outline lite-btn--sm">Consult</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@else
    <div class="lite-empty">
        <i data-lucide="search"></i>
        <p>Enter at least 2 characters to search</p>
        <div class="lite-mt">
            <a href="{{ route('portals.lite.register_patient') }}" class="lite-btn lite-btn--outline">
                <i data-lucide="user-plus"></i> Register new patient
            </a>
        </div>
    </div>
@endif

@endsection
