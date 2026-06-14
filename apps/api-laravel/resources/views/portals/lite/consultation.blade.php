@extends('layouts.lite')
@section('title', 'Consultation')

@section('content')

<h1 class="lite-page-title">Consultation note</h1>
<p class="lite-page-sub">Record basic consultation details</p>

{{-- Patient card --}}
@if($patient)
<div class="lite-card lite-mb">
    <div class="lite-card__body lite-patient-chip">
        <div class="lite-patient-chip__avatar lite-patient-chip__avatar--primary"><i data-lucide="user"></i></div>
        <div>
            <div class="lite-td-strong">{{ $patient->first_name }} {{ $patient->last_name }}</div>
            <div class="lite-mono--primary">{{ $patient->health_id }}</div>
        </div>
        <a href="{{ route('portals.lite.lookup') }}" class="lite-muted-link lite-ml-auto">Change</a>
    </div>
</div>
@else
<div class="lite-alert lite-alert--info">
    <i data-lucide="info"></i>
    <span>No patient selected. <a href="{{ route('portals.lite.lookup') }}" class="lite-alert__link">Select patient →</a></span>
</div>
@endif

{{-- CDSS safety disclaimer --}}
<div class="lite-alert lite-alert--warning lite-alert--sm">
    <i data-lucide="shield-alert"></i>
    <span>Clinical decision-support tools are advisory only. They do not replace professional clinical judgment.</span>
</div>

<form method="POST" action="{{ route('portals.staff.visits.store') }}" onsubmit="return confirm('Submit consultation note?')">
    @csrf
    @if($patient)
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
    @endif

    <div class="lite-card">
        <div class="lite-card__head">Vitals</div>
        <div class="lite-card__body">
            <div class="lite-grid-2">
                <div class="lite-form-group lite-form-group--flush">
                    <label class="lite-label">Temperature (°C)</label>
                    <input type="number" name="vitals[temperature]" step="0.1" min="30" max="45" class="lite-input" placeholder="36.5">
                </div>
                <div class="lite-form-group lite-form-group--flush">
                    <label class="lite-label">Pulse (bpm)</label>
                    <input type="number" name="vitals[pulse]" min="20" max="300" class="lite-input" placeholder="72">
                </div>
                <div class="lite-form-group lite-form-group--flush">
                    <label class="lite-label">Systolic BP</label>
                    <input type="number" name="vitals[bp_systolic]" min="40" max="300" class="lite-input" placeholder="120">
                </div>
                <div class="lite-form-group lite-form-group--flush">
                    <label class="lite-label">Diastolic BP</label>
                    <input type="number" name="vitals[bp_diastolic]" min="20" max="200" class="lite-input" placeholder="80">
                </div>
                <div class="lite-form-group lite-form-group--flush">
                    <label class="lite-label">Weight (kg)</label>
                    <input type="number" name="vitals[weight_kg]" step="0.1" min="0" max="500" class="lite-input" placeholder="70">
                </div>
                <div class="lite-form-group lite-form-group--flush">
                    <label class="lite-label">SpO2 (%)</label>
                    <input type="number" name="vitals[spo2]" min="50" max="100" class="lite-input" placeholder="98">
                </div>
            </div>
        </div>
    </div>

    <div class="lite-card">
        <div class="lite-card__head">Clinical note</div>
        <div class="lite-card__body">
            <div class="lite-form-group">
                <label class="lite-label">Chief complaint</label>
                <input type="text" name="chief_complaint" class="lite-input" placeholder="Patient's main complaint…">
            </div>
            <div class="lite-form-group">
                <label class="lite-label">Assessment / diagnosis</label>
                <textarea name="assessment" class="lite-input" rows="3" placeholder="Clinical assessment…"></textarea>
            </div>
            <div class="lite-form-group lite-form-group--flush">
                <label class="lite-label">Plan</label>
                <textarea name="plan" class="lite-input" rows="3" placeholder="Treatment plan…"></textarea>
            </div>
        </div>
    </div>

    <div class="lite-row lite-mt">
        <button type="submit" class="lite-btn lite-btn--primary lite-btn--full">
            <i data-lucide="save"></i> Save note
        </button>
        @if($patient)
        <a href="{{ route('portals.lite.billing', ['patient_id' => $patient->id]) }}" class="lite-btn lite-btn--outline">
            <i data-lucide="receipt"></i> Billing
        </a>
        @endif
    </div>
    <div class="lite-empty lite-mt">
        <a href="{{ route('portals.lite.dashboard') }}" class="lite-muted-link">← Back</a>
    </div>
</form>

@endsection
