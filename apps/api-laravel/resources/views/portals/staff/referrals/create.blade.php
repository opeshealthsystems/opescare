@extends('layouts.portal')

@section('title', 'New Referral — OpesCare Staff Portal')

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'New Referral')

@section('sidebar_role_badge')
    <div class="sidebar-role-badge">
        <i data-lucide="stethoscope" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        {{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i> Dashboard</a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link"><i data-lucide="calendar-check-2"></i> Appointments</a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link"><i data-lucide="list-ordered"></i> Patient Queue</a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link"><i data-lucide="syringe"></i> Immunizations</a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link active"><i data-lucide="send"></i> Referrals</a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link"><i data-lucide="receipt"></i> Billing</a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link"><i data-lucide="headset"></i> Support</a>
@endsection

@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('content')

<div class="page-header">
    <div>
        <a href="{{ route('portals.staff.referrals') }}" class="btn btn-ghost btn-sm" style="margin-bottom:var(--p-space-3);">
            <i data-lucide="arrow-left"></i> Back to Referrals
        </a>
        <h1 class="page-title">Create New Referral</h1>
        <p class="page-subtitle">Complete the form below to create a patient referral draft.</p>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger mb-6" style="margin-bottom:var(--p-space-6);" role="alert">
    <i data-lucide="triangle-alert"></i>
    <div>
        <div style="font-weight:700;margin-bottom:var(--p-space-2);">Please fix the following errors:</div>
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div style="max-width:760px;">
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="send"></i> Referral Details</h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.referrals.store') }}" novalidate>
            @csrf

            <div class="alert alert-info mb-6" style="margin-bottom:var(--p-space-6);">
                <i data-lucide="info"></i>
                <div style="font-size:0.8125rem;">Referrals are created as drafts. Review and send when ready. All referral activity is audited.</div>
            </div>

            <div class="form-row" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label form-label-required" for="patient_id">Patient Health ID</label>
                    <input id="patient_id" name="patient_id" class="form-control" value="{{ old('patient_id') }}" required
                           placeholder="Patient UUID" style="font-family:monospace;font-weight:700;text-transform:uppercase;"
                           aria-required="true">
                    @error('patient_id')<div style="color:var(--p-danger);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required" for="urgency">Priority / Urgency</label>
                    <select id="urgency" name="urgency" class="form-control" required aria-required="true">
                        <option value="routine"   @selected(old('urgency','routine')==='routine')>Routine</option>
                        <option value="urgent"    @selected(old('urgency')==='urgent')>Urgent</option>
                        <option value="emergency" @selected(old('urgency')==='emergency')>Emergency</option>
                    </select>
                </div>
            </div>

            <div class="form-row" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label form-label-required" for="referring_facility_id">Referring Facility ID</label>
                    <input id="referring_facility_id" name="referring_facility_id" class="form-control"
                           value="{{ old('referring_facility_id') }}" required aria-required="true">
                    @error('referring_facility_id')<div style="color:var(--p-danger);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="referring_provider_id">Referring Provider ID</label>
                    <input id="referring_provider_id" name="referring_provider_id" class="form-control"
                           value="{{ old('referring_provider_id') }}" placeholder="Optional">
                </div>
            </div>

            <div class="form-row" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label" for="receiving_facility_id">Receiving Facility ID</label>
                    <input id="receiving_facility_id" name="receiving_facility_id" class="form-control"
                           value="{{ old('receiving_facility_id') }}" placeholder="Can be added later">
                </div>
                <div class="form-group">
                    <label class="form-label" for="receiving_specialty">Specialty / Department</label>
                    <input id="receiving_specialty" name="receiving_specialty" class="form-control"
                           value="{{ old('receiving_specialty') }}" placeholder="e.g. Cardiology">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:var(--p-space-5);">
                <label class="form-label form-label-required" for="reason">Reason for Referral</label>
                <textarea id="reason" name="reason" rows="3" class="form-control" required aria-required="true">{{ old('reason') }}</textarea>
                @error('reason')<div style="color:var(--p-danger);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="margin-bottom:var(--p-space-5);">
                <label class="form-label" for="clinical_summary">Clinical Summary</label>
                <textarea id="clinical_summary" name="clinical_summary" rows="5" class="form-control">{{ old('clinical_summary') }}</textarea>
                <div class="form-hint">Include relevant diagnoses, current medications, known allergies, and care context.</div>
            </div>

            <div class="form-group" style="margin-bottom:var(--p-space-8);">
                <label class="form-label" for="expires_at">Access Expires At</label>
                <input type="datetime-local" id="expires_at" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                <div class="form-hint">Defaults to 30 days if not set. The receiving facility's access grant expires at this time.</div>
            </div>

            <div style="display:flex;gap:var(--p-space-3);">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="send"></i>
                    Create Referral (Draft)
                </button>
                <a href="{{ route('portals.staff.referrals') }}" class="btn btn-secondary">
                    <i data-lucide="x"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
</div>

@endsection
