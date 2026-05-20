@extends('layouts.portal')

@section('title', 'Schedule Teleconsultation')

@section('content')
<div class="page-header">
    <div class="page-header__left">
        <a href="{{ route('portals.staff.telemedicine.index') }}" class="back-link">← Telemedicine</a>
        <h1 class="page-title">Schedule Consultation</h1>
    </div>
</div>

<div class="card" style="max-width: 640px;">
    <div class="card__header">
        <h3 class="card__title">New Teleconsultation</h3>
    </div>
    <div class="card__body">
        @if($errors->any())
            <div class="alert alert--danger mb-4">
                <ul class="m-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('portals.staff.telemedicine.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="patient_id">Patient <span class="text-danger">*</span></label>
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
                <label class="form-label" for="scheduled_at">Scheduled At <span class="text-danger">*</span></label>
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

            <div class="alert alert--info mt-2 mb-4">
                <strong>Consent required:</strong> Patient informed consent must be recorded before the
                teleconsultation session can begin. You will be prompted after scheduling.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Schedule Consultation</button>
                <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn--outline ml-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
