@extends('layouts.portal')

@section('title', 'Record Immunization — OpesCare Staff Portal')

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Record Immunization')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.staff.immunizations') }}">Immunizations</a>
    <i data-lucide="chevron-right"></i>
    <span>Record</span>
</div>

<div class="page-head">
    <h2>Record Vaccine Administration</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.immunizations', ['patient_id' => request('patient_id')]) }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> Back to Immunizations
    </a>
</div>
<p class="page-subtitle mb-4">Record a new or historical immunization for a patient.</p>

@if($errors->any())
<div class="alert alert-danger mb-6" role="alert">
    <i data-lucide="triangle-alert"></i>
    <div>
        <strong>Please fix the following errors:</strong>
        <ul class="alert-list">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
</div>
@endif

<div class="alert alert-warning mb-6">
    <i data-lucide="alert-triangle"></i>
    <div>Duplicate prevention is enforced: recording the same vaccine code on the same date with the same lot number will be rejected. Check existing records before proceeding.</div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="syringe"></i> Immunization Record</h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.immunizations.store') }}" novalidate>
            @csrf

            <!-- Patient & Facility -->
            <h3 class="panel-title mb-4">Patient &amp; Facility</h3>

            <div class="form-row mb-4">
                <div class="form-group">
                    <label class="form-label form-label-required" for="patient_id">Patient Health ID</label>
                    <input id="patient_id" name="patient_id" class="form-control mono"
                           value="{{ old('patient_id', request('patient_id')) }}" required aria-required="true"
                           placeholder="Patient UUID">
                    @error('patient_id')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required" for="facility_id">Facility ID</label>
                    <input id="facility_id" name="facility_id" class="form-control" value="{{ old('facility_id') }}" required aria-required="true">
                    @error('facility_id')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Vaccine Details -->
            <h3 class="panel-title mt-6 mb-4">Vaccine Details</h3>

            <div class="form-row mb-4">
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

            <div class="form-row-3 mb-4">
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

            <div class="form-row mb-4">
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
            <h3 class="panel-title mt-6 mb-4">Administration Details</h3>

            <div class="form-row-3 mb-4">
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

            <div class="form-row mb-4">
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

            <div class="toggle-row mb-6">
                <div class="toggle-row__body">
                    <div class="toggle-row__title">This is a historical / self-reported record</div>
                    <div class="toggle-row__desc">Historical records are clearly labelled and not treated as facility-verified administrations.</div>
                </div>
                <label class="switch">
                    <input type="checkbox" name="is_historical" value="1" @checked(old('is_historical'))>
                    <span class="switch__track"></span>
                </label>
            </div>

            <div class="row-actions">
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

@endsection
