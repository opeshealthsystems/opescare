@extends('layouts.portal')

@section('title', __('public.staff_portal.btn_book_appointment', [], app()->getLocale()) ?: 'Book Appointment')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
@endif

<div class="panel">
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.appointments.store') }}">
            @csrf

            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.stf_appt_patient_id') }}</label>
                @if(count($patients) > 0)
                    <select name="patient_id" class="form-control" required>
                        <option value="">{{ __('public.stf_select_patient') }}</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->health_id ?? $p->id }} ({{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="patient_id" class="form-control" required
                        placeholder="{{ __('public.stf_appt_enter_patient_id') }}" value="{{ old('patient_id') }}">
                @endif
                @error('patient_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.stf_appt_facility') }}</label>
                @if(count($facilities) > 0)
                    <select name="facility_id" class="form-control" required>
                        <option value="">{{ __('public.stf_select_facility') }}</option>
                        @foreach($facilities as $f)
                            <option value="{{ $f->id }}" {{ old('facility_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->name ?? $f->id }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="facility_id" class="form-control" required
                        placeholder="{{ __('public.stf_appt_facility_id') }}" value="{{ old('facility_id') }}">
                @endif
                @error('facility_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-row mb-4">
                <div class="form-group">
                    <label class="form-label">{{ __('public.stf_appt_type') }}</label>
                    <select name="appointment_type" class="form-control" required>
                        <option value="general" {{ old('appointment_type') === 'general' ? 'selected' : '' }}>
                            {{ __('public.staff_portal.type_general', [], app()->getLocale()) ?: 'General' }}
                        </option>
                        <option value="follow_up" {{ old('appointment_type') === 'follow_up' ? 'selected' : '' }}>
                            {{ __('public.staff_portal.type_followup', [], app()->getLocale()) ?: 'Follow-up' }}
                        </option>
                        <option value="specialist" {{ old('appointment_type') === 'specialist' ? 'selected' : '' }}>
                            {{ __('public.staff_portal.type_specialist', [], app()->getLocale()) ?: 'Specialist' }}
                        </option>
                        <option value="lab" {{ old('appointment_type') === 'lab' ? 'selected' : '' }}>
                            {{ __('public.staff_portal.type_lab', [], app()->getLocale()) ?: 'Lab / Diagnostics' }}
                        </option>
                        <option value="pharmacy" {{ old('appointment_type') === 'pharmacy' ? 'selected' : '' }}>
                            {{ __('public.staff_portal.type_pharmacy', [], app()->getLocale()) ?: 'Pharmacy' }}
                        </option>
                        <option value="emergency" {{ old('appointment_type') === 'emergency' ? 'selected' : '' }}>
                            {{ __('public.staff_portal.type_emergency', [], app()->getLocale()) ?: 'Emergency' }}
                        </option>
                    </select>
                    @error('appointment_type')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('public.stf_appt_datetime') }}</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" required
                        value="{{ old('scheduled_at', now()->addDay()->format('Y-m-d\TH:i')) }}">
                    @error('scheduled_at')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">{{ __('public.stf_appt_reason') }}</label>
                <textarea name="reason" class="form-control" rows="3" maxlength="500"
                    placeholder="{{ __('public.stf_appt_reason_placeholder') }}">{{ old('reason') }}</textarea>
            </div>

            <div class="row-actions-inline">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="calendar-plus"></i>
                    {{ __('public.staff_portal.btn_book_appointment', [], app()->getLocale()) ?: 'Book Appointment' }}
                </button>
                <a href="{{ route('portals.staff.appointments') }}" class="btn btn-ghost">{{ __('public.stf_cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@endsection
