@extends('layouts.lite')
@section('title', 'Check-In')

@section('content')

<h1 class="lite-page-title">Queue Check-In</h1>
<p class="lite-page-sub">Add a patient to today's queue</p>

@if($errors->any())
    <div class="lite-alert lite-alert--danger" style="flex-direction:column;gap:4px;">
        @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
    </div>
@endif

{{-- Patient search / selection --}}
@if(!$patient)
<div style="margin-bottom:16px;">
    <div class="lite-alert lite-alert--info">
        <i data-lucide="info" style="width:16px;height:16px;flex-shrink:0;"></i>
        Search for the patient first to pre-fill this form.
        <a href="{{ route('portals.lite.lookup') }}" style="font-weight:700;color:inherit;margin-left:6px;">Lookup →</a>
    </div>
</div>
@else
<div class="lite-card" style="margin-bottom:16px;">
    <div class="lite-card__body" style="display:flex;align-items:center;gap:12px;">
        <div style="width:44px;height:44px;border-radius:50%;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="user" style="color:#7c3aed;width:22px;height:22px;"></i>
        </div>
        <div>
            <div style="font-weight:700;font-size:0.95rem;">{{ $patient->first_name }} {{ $patient->last_name }}</div>
            <div style="font-family:monospace;font-size:0.78rem;color:#7c3aed;">{{ $patient->health_id }}</div>
        </div>
        <a href="{{ route('portals.lite.lookup') }}" style="margin-left:auto;font-size:0.78rem;color:#64748b;">Change</a>
    </div>
</div>
@endif

<form method="POST" action="{{ route('portals.lite.checkin.store') }}">
    @csrf

    @if($patient)
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
    @else
        <div class="lite-form-group">
            <label class="lite-label" for="patient_id">Patient ID</label>
            <input id="patient_id" name="patient_id" type="text"
                   value="{{ old('patient_id') }}"
                   class="lite-input" placeholder="Paste patient UUID" required>
        </div>
    @endif

    <div class="lite-card">
        <div class="lite-card__head">Visit Details</div>
        <div class="lite-card__body">
            <div class="lite-form-group">
                <label class="lite-label" for="reason">Reason for Visit</label>
                <textarea id="reason" name="reason" class="lite-input" rows="3"
                          placeholder="Brief reason (optional)">{{ old('reason') }}</textarea>
            </div>
            <div class="lite-form-group">
                <label class="lite-label">Priority</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @foreach([1 => 'Urgent (P1)', 2 => 'High (P2)', 3 => 'Normal (P3)', 4 => 'Low (P4)', 5 => 'Routine (P5)'] as $val => $label)
                        <label style="display:flex;align-items:center;gap:5px;cursor:pointer;padding:6px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:0.83rem;font-weight:500;">
                            <input type="radio" name="priority" value="{{ $val }}"
                                   {{ old('priority', 3) == $val ? 'checked' : '' }}
                                   style="accent-color:#7c3aed;">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="lite-btn lite-btn--primary lite-btn--full" style="margin-top:8px;">
        <i data-lucide="log-in" style="width:16px;height:16px;"></i> Check In Patient
    </button>
    <div style="text-align:center;margin-top:10px;">
        <a href="{{ route('portals.lite.dashboard') }}" style="font-size:0.83rem;color:#64748b;">← Cancel</a>
    </div>
</form>

@endsection
