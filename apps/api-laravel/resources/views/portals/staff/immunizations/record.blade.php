@extends('layouts.portal')

@section('title', 'Record Immunization — OpesCare Staff Portal')

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Record Immunization')

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
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link active"><i data-lucide="syringe"></i> Immunizations</a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link"><i data-lucide="send"></i> Referrals</a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link"><i data-lucide="receipt"></i> Billing</a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link"><i data-lucide="headset"></i> Support</a>
@endsection

@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('content')

<div class="page-header">
    <div>
        <a href="{{ route('portals.staff.immunizations', ['patient_id' => request('patient_id')]) }}" class="btn btn-ghost btn-sm" style="margin-bottom:var(--p-space-3);">
            <i data-lucide="arrow-left"></i> Back to Immunizations
        </a>
        <h1 class="page-title">Record Vaccine Administration</h1>
        <p class="page-subtitle">Record a new or historical immunization for a patient.</p>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger mb-6" style="margin-bottom:var(--p-space-6);" role="alert">
    <i data-lucide="triangle-alert"></i>
    <div>
        <strong>Please fix the following errors:</strong>
        <ul style="margin:var(--p-space-2) 0 0;padding-left:1.25rem;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<div style="max-width:760px;">

<div class="alert alert-warning mb-6" style="margin-bottom:var(--p-space-6);">
    <i data-lucide="alert-triangle"></i>
    <div style="font-size:0.8125rem;">Duplicate prevention is enforced: recording the same vaccine code on the same date with the same lot number will be rejected. Check existing records before proceeding.</div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="syringe"></i> Immunization Record</h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.immunizations.store') }}" novalidate>
            @csrf

            <!-- Patient & Facility -->
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--p-text-muted);border-bottom:1px solid var(--p-border);padding-bottom:var(--p-space-2);margin-bottom:var(--p-space-4);">Patient &amp; Facility</div>

            <div class="form-row" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label form-label-required" for="patient_id">Patient Health ID</label>
                    <input id="patient_id" name="patient_id" class="form-control"
                           value="{{ old('patient_id', request('patient_id')) }}" required aria-required="true"
                           style="font-family:monospace;font-weight:700;text-transform:uppercase;" placeholder="Patient UUID">
                    @error('patient_id')<div style="color:var(--p-danger);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required" for="facility_id">Facility ID</label>
                    <input id="facility_id" name="facility_id" class="form-control" value="{{ old('facility_id') }}" required aria-required="true">
                    @error('facility_id')<div style="color:var(--p-danger);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Vaccine Details -->
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--p-text-muted);border-bottom:1px solid var(--p-border);padding-bottom:var(--p-space-2);margin-bottom:var(--p-space-4);margin-top:var(--p-space-6);">Vaccine Details</div>

            <div class="form-row" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label form-label-required" for="vaccine_code">Vaccine Code</label>
                    <input id="vaccine_code" name="vaccine_code" class="form-control" value="{{ old('vaccine_code') }}" placeholder="e.g. BCG, OPV, DPT" required aria-required="true">
                    <div class="form-hint">WHO-EPI code or local code</div>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required" for="vaccine_name">Vaccine Name</label>
                    <input id="vaccine_name" name="vaccine_name" class="form-control" value="{{ old('vaccine_name') }}" placeholder="e.g. Bacillus Calmette-Guérin" required aria-required="true">
                </div>
            </div>

            <div class="form-row-3" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label" for="dose_number">Dose Number</label>
                    <input type="number" id="dose_number" name="dose_number" class="form-control" value="{{ old('dose_number') }}" min="1" placeholder="1">
                </div>
                <div class="form-group">
                    <label class="form-label" for="lot_number">Lot Number</label>
                    <input id="lot_number" name="lot_number" class="form-control" value="{{ old('lot_number') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="expiry_date">Vaccine Expiry</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
                </div>
            </div>

            <div class="form-row" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label" for="manufacturer">Manufacturer</label>
                    <input id="manufacturer" name="manufacturer" class="form-control" value="{{ old('manufacturer') }}">
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required" for="administered_at">Date Administered</label>
                    <input type="datetime-local" id="administered_at" name="administered_at" class="form-control"
                           value="{{ old('administered_at', now()->format('Y-m-d\TH:i')) }}" required aria-required="true">
                </div>
            </div>

            <!-- Administration Details -->
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--p-text-muted);border-bottom:1px solid var(--p-border);padding-bottom:var(--p-space-2);margin-bottom:var(--p-space-4);margin-top:var(--p-space-6);">Administration Details</div>

            <div class="form-row-3" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label" for="route">Route</label>
                    <select id="route" name="route" class="form-control">
                        <option value="">— Select —</option>
                        <option value="IM"          @selected(old('route')==='IM')>IM (Intramuscular)</option>
                        <option value="SC"          @selected(old('route')==='SC')>SC (Subcutaneous)</option>
                        <option value="oral"        @selected(old('route')==='oral')>Oral</option>
                        <option value="intradermal" @selected(old('route')==='intradermal')>Intradermal</option>
                        <option value="IN"          @selected(old('route')==='IN')>Intranasal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="site">Injection Site</label>
                    <input id="site" name="site" class="form-control" value="{{ old('site') }}" placeholder="e.g. Left deltoid">
                </div>
                <div class="form-group">
                    <label class="form-label" for="dose_quantity">Dose Quantity (ml)</label>
                    <input type="number" step="0.01" id="dose_quantity" name="dose_quantity" class="form-control" value="{{ old('dose_quantity') }}" placeholder="0.5">
                </div>
            </div>

            <div class="form-row" style="margin-bottom:var(--p-space-5);">
                <div class="form-group">
                    <label class="form-label form-label-required" for="status">Status</label>
                    <select id="status" name="status" class="form-control" required aria-required="true">
                        <option value="completed" @selected(old('status','completed')==='completed')>Completed</option>
                        <option value="not_done"  @selected(old('status')==='not_done')>Not Done</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="not_done_reason">Not Done Reason</label>
                    <input id="not_done_reason" name="not_done_reason" class="form-control" value="{{ old('not_done_reason') }}" placeholder="If status is Not Done">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:var(--p-space-8);">
                <label style="display:flex;align-items:center;gap:var(--p-space-3);cursor:pointer;font-size:0.875rem;font-weight:600;color:var(--p-text-2);">
                    <input type="checkbox" name="is_historical" value="1" @checked(old('is_historical')) style="width:1rem;height:1rem;accent-color:var(--p-primary);">
                    This is a historical / self-reported record
                </label>
                <div class="form-hint" style="margin-left:1.75rem;">Historical records are clearly labelled and not treated as facility-verified administrations.</div>
            </div>

            <div style="display:flex;gap:var(--p-space-3);">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="syringe"></i>
                    Record Immunization
                </button>
                <a href="{{ route('portals.staff.immunizations', ['patient_id' => request('patient_id')]) }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
</div>

@endsection
