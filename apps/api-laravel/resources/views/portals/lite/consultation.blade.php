@extends('layouts.lite')
@section('title', 'Consultation')

@section('content')

<h1 class="lite-page-title">Consultation Note</h1>
<p class="lite-page-sub">Record basic consultation details</p>

{{-- Patient card --}}
@if($patient)
<div class="lite-card" style="margin-bottom:14px;">
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
@else
<div class="lite-alert lite-alert--info" style="margin-bottom:14px;">
    <i data-lucide="info" style="width:16px;height:16px;flex-shrink:0;"></i>
    No patient selected. <a href="{{ route('portals.lite.lookup') }}" style="font-weight:700;color:inherit;">Select patient →</a>
</div>
@endif

{{-- CDSS safety disclaimer --}}
<div class="lite-alert lite-alert--warning" style="margin-bottom:14px;font-size:0.8rem;">
    <i data-lucide="shield-alert" style="width:16px;height:16px;flex-shrink:0;"></i>
    Clinical decision-support tools are advisory only. They do not replace professional clinical judgment.
</div>

<form method="POST" action="{{ route('portals.staff.visits.create') }}" onsubmit="return confirm('Submit consultation note?')">
    @csrf
    @if($patient)
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
    @endif

    <div class="lite-card">
        <div class="lite-card__head">Vitals</div>
        <div class="lite-card__body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div class="lite-form-group" style="margin:0;">
                    <label class="lite-label">Temperature (°C)</label>
                    <input type="number" name="vitals[temperature]" step="0.1" min="30" max="45" class="lite-input" placeholder="36.5">
                </div>
                <div class="lite-form-group" style="margin:0;">
                    <label class="lite-label">Pulse (bpm)</label>
                    <input type="number" name="vitals[pulse]" min="20" max="300" class="lite-input" placeholder="72">
                </div>
                <div class="lite-form-group" style="margin:0;">
                    <label class="lite-label">Systolic BP</label>
                    <input type="number" name="vitals[bp_systolic]" min="40" max="300" class="lite-input" placeholder="120">
                </div>
                <div class="lite-form-group" style="margin:0;">
                    <label class="lite-label">Diastolic BP</label>
                    <input type="number" name="vitals[bp_diastolic]" min="20" max="200" class="lite-input" placeholder="80">
                </div>
                <div class="lite-form-group" style="margin:0;">
                    <label class="lite-label">Weight (kg)</label>
                    <input type="number" name="vitals[weight_kg]" step="0.1" min="0" max="500" class="lite-input" placeholder="70">
                </div>
                <div class="lite-form-group" style="margin:0;">
                    <label class="lite-label">SpO2 (%)</label>
                    <input type="number" name="vitals[spo2]" min="50" max="100" class="lite-input" placeholder="98">
                </div>
            </div>
        </div>
    </div>

    <div class="lite-card">
        <div class="lite-card__head">Clinical Note</div>
        <div class="lite-card__body">
            <div class="lite-form-group">
                <label class="lite-label">Chief Complaint</label>
                <input type="text" name="chief_complaint" class="lite-input" placeholder="Patient's main complaint…">
            </div>
            <div class="lite-form-group">
                <label class="lite-label">Assessment / Diagnosis</label>
                <textarea name="assessment" class="lite-input" rows="3" placeholder="Clinical assessment…"></textarea>
            </div>
            <div class="lite-form-group" style="margin-bottom:0;">
                <label class="lite-label">Plan</label>
                <textarea name="plan" class="lite-input" rows="3" placeholder="Treatment plan…"></textarea>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:8px;margin-top:8px;">
        <button type="submit" class="lite-btn lite-btn--primary lite-btn--full">
            <i data-lucide="save" style="width:16px;height:16px;"></i> Save Note
        </button>
        @if($patient)
        <a href="{{ route('portals.lite.billing', ['patient_id' => $patient->id]) }}"
           class="lite-btn lite-btn--outline">
            <i data-lucide="receipt" style="width:16px;height:16px;"></i> Billing
        </a>
        @endif
    </div>
    <div style="text-align:center;margin-top:10px;">
        <a href="{{ route('portals.lite.dashboard') }}" style="font-size:0.83rem;color:#64748b;">← Back</a>
    </div>
</form>

@endsection
