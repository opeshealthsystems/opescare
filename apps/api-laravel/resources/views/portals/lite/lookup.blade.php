@extends('layouts.lite')
@section('title', 'Health ID Lookup')

@section('content')

<h1 class="lite-page-title">Health ID Lookup</h1>
<p class="lite-page-sub">Search by Health ID, name, or phone number</p>

<form method="GET" action="{{ route('portals.lite.lookup') }}">
    <div style="display:flex;gap:8px;margin-bottom:16px;">
        <input type="text" name="q" value="{{ $query }}" placeholder="Health ID, name, or phone…"
               class="lite-input" style="flex:1;" autofocus>
        <button type="submit" class="lite-btn lite-btn--primary">
            <i data-lucide="search" style="width:16px;height:16px;"></i> Search
        </button>
    </div>
</form>

@if(strlen($query) >= 2)
    @if($patients->isEmpty())
        <div class="lite-alert lite-alert--info">
            <i data-lucide="info" style="width:16px;height:16px;flex-shrink:0;"></i>
            No patients found matching "{{ $query }}".
            <a href="{{ route('portals.lite.register_patient') }}" style="font-weight:700;color:inherit;margin-left:6px;">Register new patient →</a>
        </div>
    @else
        <div class="lite-card">
            <div class="lite-card__head">{{ $patients->count() }} result(s) for "{{ $query }}"</div>
            <div class="lite-card__body" style="padding:0;">
                <table class="lite-table">
                    <thead><tr><th>Name</th><th>Health ID</th><th>DOB</th><th></th></tr></thead>
                    <tbody>
                        @foreach($patients as $p)
                        <tr>
                            <td style="font-weight:600;">{{ $p->first_name }} {{ $p->last_name }}</td>
                            <td style="font-family:monospace;font-size:0.8rem;color:#7c3aed;">{{ $p->health_id }}</td>
                            <td style="font-size:0.82rem;color:#6b7280;">
                                {{ $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->format('d M Y') : '—' }}
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;justify-content:flex-end;">
                                    <a href="{{ route('portals.lite.checkin', ['patient_id' => $p->id]) }}"
                                       class="lite-btn lite-btn--primary" style="padding:5px 10px;font-size:0.78rem;">
                                        Check-In
                                    </a>
                                    <a href="{{ route('portals.lite.consultation', ['patient_id' => $p->id]) }}"
                                       class="lite-btn lite-btn--outline" style="padding:5px 10px;font-size:0.78rem;">
                                        Consult
                                    </a>
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
    <div style="text-align:center;padding:40px 0;color:#94a3b8;">
        <i data-lucide="search" style="width:40px;height:40px;margin-bottom:12px;opacity:.4;"></i>
        <p style="margin:0;font-size:0.9rem;">Enter at least 2 characters to search</p>
        <div style="margin-top:20px;">
            <a href="{{ route('portals.lite.register_patient') }}" class="lite-btn lite-btn--outline">
                <i data-lucide="user-plus" style="width:16px;height:16px;"></i> Register New Patient
            </a>
        </div>
    </div>
@endif

@endsection
