@extends('layouts.portal')

@section('title', 'Schedule Teleconsultation')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('portals.staff.telemedicine.index') }}">Telemedicine</a>
    <i data-lucide="chevron-right"></i>
    <span>Schedule</span>
</div>

<div class="page-head">
    <h2>Schedule Consultation</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> Telemedicine
    </a>
</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title">New Teleconsultation</h3>
    </div>
    <div class="panel-body">
        @if($errors->any())
            <div class="alert alert-danger mb-4">
                <i data-lucide="triangle-alert"></i>
                <div>
                    <ul class="alert-list">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('portals.staff.telemedicine.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label form-label-required" for="patient_id">Patient *</label>
                <select name="patient_id" id="patient_id" class="form-control" required>
                    <option value="">— Select patient —</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->first_name }} {{ $p->last_name }}
                            @if($p->health_id) ({{ $p->health_id }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label form-label-required" for="scheduled_at">Scheduled At *</label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                       class="form-control" value="{{ old('scheduled_at') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="platform">Platform</label>
                <select name="platform" id="platform" class="form-control">
                    <option value="own" {{ old('platform') == 'own' ? 'selected' : '' }}>OpesCare Built-in</option>
                    <option value="zoom" {{ old('platform') == 'zoom' ? 'selected' : '' }}>Zoom</option>
                    <option value="meet" {{ old('platform') == 'meet' ? 'selected' : '' }}>Google Meet</option>
                    <option value="teams" {{ old('platform') == 'teams' ? 'selected' : '' }}>Microsoft Teams</option>
                </select>
            </div>

            <div class="alert alert-info mb-4">
                <i data-lucide="info"></i>
                <div>
                    <strong>Consent required:</strong> Patient informed consent must be recorded before the
                    teleconsultation session can begin. You will be prompted after scheduling.
                </div>
            </div>

            <div class="row-actions">
                <button type="submit" class="btn btn-primary">Schedule Consultation</button>
                <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
