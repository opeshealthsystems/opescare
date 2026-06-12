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
        <p class="page-subtitle">Manage your contact details, clinical information, and privacy preferences.</p>
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

    {{-- ── LEFT COLUMN ──────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">

        {{-- Contact Details --}}
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

                @error('date_of_birth')<p style="color:#DC2626;font-size:0.8125rem;">{{ $message }}</p>@enderror
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Date of Birth</label>
                    <input type="date" name="date_of_birth"
                        value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}"
                        max="{{ now()->subDay()->format('Y-m-d') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                    @if($patient->date_of_birth)
                        <p style="font-size:0.75rem;color:var(--p-text-muted);margin-top:4px;">Age: {{ $patient->date_of_birth->diffInYears(now()) }} years</p>
                    @endif
                </div>

            </div>
        </div>

        {{-- Clinical Information --}}
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="heart-pulse"></i> Clinical Information</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-5);">

                {{-- Blood Group --}}
                @error('blood_group')<p style="color:#DC2626;font-size:0.8125rem;">{{ $message }}</p>@enderror
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">
                        Blood Group
                    </label>
                    <select name="blood_group"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                        <option value="">— Not set —</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                    <p style="font-size:0.75rem;color:var(--p-text-muted);margin-top:4px;">Used in emergency profiles and clinical summaries shared with providers.</p>
                </div>

                {{-- Active Allergies (read-only summary + link) --}}
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--p-space-3);">
                        <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);">Active Allergies</label>
                        <a href="{{ route('portals.patient.allergies') }}" style="font-size:0.75rem;color:var(--p-primary);font-weight:600;">
                            <i data-lucide="external-link" style="width:0.75rem;height:0.75rem;vertical-align:middle;margin-right:2px;"></i>View / manage →
                        </a>
                    </div>
                    @if($allergies->isEmpty())
                        <div style="padding:var(--p-space-3);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.8125rem;color:var(--p-text-muted);">
                            <i data-lucide="check-circle" style="width:0.875rem;height:0.875rem;color:#16A34A;vertical-align:middle;margin-right:4px;"></i>No allergies on record.
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            @foreach($allergies as $allergy)
                            @php
                                $sevColor = match(strtolower($allergy->severity ?? '')) {
                                    'life-threatening','severe','high' => '#DC2626',
                                    'moderate','medium'               => '#D97706',
                                    default                           => '#6B7280',
                                };
                            @endphp
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--p-space-2) var(--p-space-3);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);">
                                <span style="font-size:0.8125rem;font-weight:600;color:var(--p-text);">
                                    @if(in_array(strtolower($allergy->severity ?? ''), ['life-threatening','severe','high']))
                                        <i data-lucide="alert-triangle" style="width:0.8rem;height:0.8rem;color:#DC2626;vertical-align:middle;margin-right:3px;"></i>
                                    @endif
                                    {{ $allergy->substance }}
                                </span>
                                <span style="font-size:0.7rem;font-weight:700;padding:2px 7px;border-radius:999px;background:{{ $sevColor }}20;color:{{ $sevColor }};">
                                    {{ ucfirst($allergy->severity ?? 'unknown') }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @endif
                    <p style="font-size:0.75rem;color:var(--p-text-muted);margin-top:6px;">Allergies are maintained by your healthcare providers. Contact your facility to add or update.</p>
                </div>

                {{-- Active Conditions (read-only summary + link) --}}
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--p-space-3);">
                        <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);">Active Conditions</label>
                        <a href="{{ route('portals.patient.clinical') }}" style="font-size:0.75rem;color:var(--p-primary);font-weight:600;">
                            <i data-lucide="external-link" style="width:0.75rem;height:0.75rem;vertical-align:middle;margin-right:2px;"></i>View all →
                        </a>
                    </div>
                    @if($conditions->isEmpty())
                        <div style="padding:var(--p-space-3);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.8125rem;color:var(--p-text-muted);">
                            <i data-lucide="check-circle" style="width:0.875rem;height:0.875rem;color:#16A34A;vertical-align:middle;margin-right:4px;"></i>No active conditions on record.
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            @foreach($conditions as $condition)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--p-space-2) var(--p-space-3);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);">
                                <span style="font-size:0.8125rem;font-weight:600;color:var(--p-text);">{{ $condition->display_name ?? $condition->code ?? '—' }}</span>
                                <span style="font-size:0.7rem;font-weight:700;padding:2px 7px;border-radius:999px;background:{{ $condition->status === 'chronic' ? '#7C3AED20' : '#2563EB20' }};color:{{ $condition->status === 'chronic' ? '#7C3AED' : '#2563EB' }};">
                                    {{ ucfirst($condition->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @endif
                    <p style="font-size:0.75rem;color:var(--p-text-muted);margin-top:6px;">Conditions are recorded by your healthcare providers and cannot be edited here.</p>
                </div>

            </div>
        </div>

        {{-- Emergency Contact --}}
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="phone-call"></i> Emergency Contact</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-4);">
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Name</label>
                    <input type="text" name="emergency_contact[name]" value="{{ old('emergency_contact.name', ($patient->emergency_contact ?? [])['name'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Phone</label>
                    <input type="text" name="emergency_contact[phone]" value="{{ old('emergency_contact.phone', ($patient->emergency_contact ?? [])['phone'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Relationship</label>
                    <input type="text" name="emergency_contact[relationship]" value="{{ old('emergency_contact.relationship', ($patient->emergency_contact ?? [])['relationship'] ?? '') }}"
                        style="width:100%;padding:var(--p-space-2) var(--p-space-3);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;background:var(--p-surface);color:var(--p-text);">
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN ─────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">

        {{-- Identity (read-only) --}}
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="fingerprint"></i> Identity</h2></div>
            <div class="panel-body" style="display:flex;flex-direction:column;gap:var(--p-space-4);">
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Health ID</label>
                    <div style="padding:var(--p-space-2) var(--p-space-3);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-family:monospace;font-size:0.875rem;color:var(--p-text);">{{ $patient->health_id }}</div>
                </div>
                @if($patient->cnamgs_id)
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">CNAMGS ID</label>
                    <div style="padding:var(--p-space-2) var(--p-space-3);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-family:monospace;font-size:0.875rem;color:var(--p-text);">{{ $patient->cnamgs_id }}</div>
                </div>
                @endif
                <div>
                    <label style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);display:block;margin-bottom:4px;">Status</label>
                    <div style="padding:var(--p-space-2) var(--p-space-3);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:0.875rem;color:var(--p-text);">
                        {{ ucfirst(str_replace('_', ' ', $patient->verification_status ?? $patient->identity_status ?? 'Active')) }}
                    </div>
                </div>
                <p style="font-size:0.75rem;color:var(--p-text-muted);">Identity fields are managed by OpesCare. To update, contact your registered facility.</p>
            </div>
        </div>

        {{-- Privacy Settings --}}
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
                        <div style="font-size:0.875rem;font-weight:700;margin-bottom:3px;">Allow Emergency Access</div>
                        <div style="font-size:0.8125rem;color:var(--p-text-muted);line-height:1.45;">Permit audited break-glass access during emergencies without standard consent.</div>
                    </div>
                </label>

            </div>
        </div>

        {{-- Save --}}
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
