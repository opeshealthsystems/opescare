@extends('layouts.portal')

@section('title', 'My Profile — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Profile')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your contact details and privacy preferences.</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@else

@if(session('success'))
<div class="alert alert-info" style="margin-bottom:var(--p-space-4);"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif

<form method="POST" action="{{ route('portals.patient.profile.update') }}">
@csrf

<div class="grid-main-side">

    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">

        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="user"></i> Contact Details</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-4);">

                @error('phone_number')<p style="color:#DC2626;font-size:0.8125rem;">{{ $message }}</p>@enderror
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $patient->phone_number) }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>

                @error('email')<p style="color:#DC2626;font-size:0.8125rem;">{{ $message }}</p>@enderror
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Email</label>
                    <input type="email" name="email" value="{{ old('email', $patient->email) }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>

                @error('address')<p style="color:#DC2626;font-size:0.8125rem;">{{ $message }}</p>@enderror
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Address</label>
                    <textarea name="address" rows="2"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);resize:vertical;">{{ old('address', $patient->address) }}</textarea>
                </div>

            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="phone-call"></i> Emergency Contact</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-4);">
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Name</label>
                    <input type="text" name="emergency_contact[name]" value="{{ old('emergency_contact.name', $patient->emergency_contact['name'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Phone</label>
                    <input type="text" name="emergency_contact[phone]" value="{{ old('emergency_contact.phone', $patient->emergency_contact['phone'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Relationship</label>
                    <input type="text" name="emergency_contact[relationship]" value="{{ old('emergency_contact.relationship', $patient->emergency_contact['relationship'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
            </div>
        </div>

    </div>

    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">

        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="shield-check"></i> Privacy Settings</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-4);">

                <label style="display:flex;align-items:flex-start;gap:var(--p-space-4);padding:var(--p-space-4);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius);cursor:pointer;">
                    <input type="hidden" name="privacy_require_consent" value="0">
                    <input type="checkbox" name="privacy_require_consent" value="1"
                        {{ ($patient->privacy_preferences['require_consent_for_full_record'] ?? true) ? 'checked' : '' }}
                        style="width:1.1rem;height:1.1rem;accent-color:var(--p-primary);margin-top:1px;flex-shrink:0;">
                    <div>
                        <div style="font-size:0.875rem;font-weight:700;margin-bottom:3px;">Require Consent for Full Record</div>
                        <div style="font-size:0.8125rem;color:var(--p-text-muted);line-height:1.45;">Providers can only see a masked preview without your explicit consent.</div>
                    </div>
                </label>

                <label style="display:flex;align-items:flex-start;gap:var(--p-space-4);padding:var(--p-space-4);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius);cursor:pointer;">
                    <input type="hidden" name="privacy_emergency_access" value="0">
                    <input type="checkbox" name="privacy_emergency_access" value="1"
                        {{ ($patient->privacy_preferences['emergency_access_allowed'] ?? true) ? 'checked' : '' }}
                        style="width:1.1rem;height:1.1rem;accent-color:#DC2626;margin-top:1px;flex-shrink:0;">
                    <div>
                        <div style="font-size:0.875rem;font-weight:700;margin-bottom:3px;">Emergency Access Allowed</div>
                        <div style="font-size:0.8125rem;color:var(--p-text-muted);line-height:1.45;">Permit audited "break-glass" access during emergencies without standard consent.</div>
                    </div>
                </label>

            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width:100%;">
                <i data-lucide="save"></i> Save Changes
            </button>
        </div>

    </div>

</div>
</form>
@endif

@endsection
