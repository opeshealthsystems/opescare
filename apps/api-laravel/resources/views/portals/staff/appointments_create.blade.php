@extends('layouts.portal')

@section('title', __('public.staff_portal.btn_book_appointment', [], app()->getLocale()) ?: 'Book Appointment')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link active">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</span>
    </a>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="stethoscope"></i>
        <span>{{ __('public.portal.nav_visits', [], app()->getLocale()) ?: 'Visits' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">HR & Staff</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_staff_directory', [], app()->getLocale()) ?: 'Directory' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
        <i data-lucide="clock"></i>
        <span>{{ __('public.portal.nav_staff_shifts', [], app()->getLocale()) ?: 'Shifts' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i>
        <span>{{ __('public.portal.nav_staff_roster', [], app()->getLocale()) ?: 'Duty Roster' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link">
        <i data-lucide="plane-takeoff"></i>
        <span>{{ __('public.portal.nav_staff_leave', [], app()->getLocale()) ?: 'Leave' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i>
        <span>{{ __('public.portal.nav_insurance', [], app()->getLocale()) ?: 'Insurance' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', __('public.staff_portal.btn_book_appointment', [], app()->getLocale()) ?: 'Book Appointment')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.staff_portal.btn_book_appointment', [], app()->getLocale()) ?: 'Book Appointment' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.appointments_subtitle', [], app()->getLocale()) ?: 'Schedule a new patient appointment.' }}</p>
    </div>
    <a href="{{ route('portals.staff.appointments') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
        Back
    </a>
</div>

@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

<div class="panel" style="max-width:640px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.appointments.store') }}">
            @csrf

            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Patient ID *</label>
                @if(count($patients) > 0)
                    <select name="patient_id" class="form-control" required>
                        <option value="">— Select Patient —</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->health_id ?? $p->id }} ({{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="patient_id" class="form-control" required
                        placeholder="Enter Patient ID" value="{{ old('patient_id') }}">
                @endif
                @error('patient_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Facility *</label>
                @if(count($facilities) > 0)
                    <select name="facility_id" class="form-control" required>
                        <option value="">— Select Facility —</option>
                        @foreach($facilities as $f)
                            <option value="{{ $f->id }}" {{ old('facility_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->name ?? $f->id }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="facility_id" class="form-control" required
                        placeholder="Facility ID" value="{{ old('facility_id') }}">
                @endif
                @error('facility_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Appointment Type *</label>
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
                    <label class="form-label">Date & Time *</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" required
                        value="{{ old('scheduled_at', now()->addDay()->format('Y-m-d\TH:i')) }}">
                    @error('scheduled_at')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label">Reason / Notes</label>
                <textarea name="reason" class="form-control" rows="3" maxlength="500"
                    placeholder="Reason for appointment…">{{ old('reason') }}</textarea>
            </div>

            <div style="display:flex;gap:.75rem;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="calendar-plus" style="width:14px;height:14px;"></i>
                    {{ __('public.staff_portal.btn_book_appointment', [], app()->getLocale()) ?: 'Book Appointment' }}
                </button>
                <a href="{{ route('portals.staff.appointments') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
