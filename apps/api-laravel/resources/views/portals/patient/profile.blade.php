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
        <div class="empty-state-icon"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@else

@if(session('success'))
<div class="alert alert-info mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif

<form method="POST" action="{{ route('portals.patient.profile.update') }}">
@csrf

<div class="grid-main-side">

    {{-- ── LEFT COLUMN ──────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">

        {{-- Contact Details --}}
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="user"></i> Contact Details</h2></div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $patient->phone_number) }}">
                    @error('phone_number')<div class="form-hint">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email) }}">
                    @error('email')<div class="form-hint">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-control">{{ old('address', $patient->address) }}</textarea>
                    @error('address')<div class="form-hint">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control"
                        value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}"
                        max="{{ now()->subDay()->format('Y-m-d') }}">
                    @if($patient->date_of_birth)
                        <div class="form-hint">Age: {{ $patient->date_of_birth->diffInYears(now()) }} years</div>
                    @endif
                    @error('date_of_birth')<div class="form-hint">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- Clinical Information --}}
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="heart-pulse"></i> Clinical Information</h2></div>
            <div class="panel-body">

                {{-- Blood Group --}}
                <div class="form-group">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-control">
                        <option value="">— Not set —</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">Used in emergency profiles and clinical summaries shared with providers.</div>
                    @error('blood_group')<div class="form-hint">{{ $message }}</div>@enderror
                </div>

                {{-- Active Allergies (read-only summary + link) --}}
                <div class="form-group">
                    <div class="flex-between mb-3">
                        <label class="form-label">Active Allergies</label>
                        <a href="{{ route('portals.patient.allergies') }}" class="link-action">
                            <i data-lucide="external-link"></i> View / manage →
                        </a>
                    </div>
                    @if($allergies->isEmpty())
                        <div class="empty-state">
                            <i data-lucide="check-circle"></i> No allergies on record.
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            @foreach($allergies as $allergy)
                            @php
                                $sevCls = match(strtolower($allergy->severity ?? '')) {
                                    'life-threatening','severe','high' => 'badge-danger',
                                    'moderate','medium'               => 'badge-warning',
                                    default                           => 'badge-neutral',
                                };
                            @endphp
                            <div class="list-row">
                                <span class="list-row__main">
                                    @if(in_array(strtolower($allergy->severity ?? ''), ['life-threatening','severe','high']))
                                        <i data-lucide="alert-triangle"></i>
                                    @endif
                                    {{ $allergy->substance }}
                                </span>
                                <span class="badge {{ $sevCls }}">{{ ucfirst($allergy->severity ?? 'unknown') }}</span>
                            </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="form-hint">Allergies are maintained by your healthcare providers. Contact your facility to add or update.</div>
                </div>

                {{-- Active Conditions (read-only summary + link) --}}
                <div class="form-group">
                    <div class="flex-between mb-3">
                        <label class="form-label">Active Conditions</label>
                        <a href="{{ route('portals.patient.clinical') }}" class="link-action">
                            <i data-lucide="external-link"></i> View all →
                        </a>
                    </div>
                    @if($conditions->isEmpty())
                        <div class="empty-state">
                            <i data-lucide="check-circle"></i> No active conditions on record.
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            @foreach($conditions as $condition)
                            <div class="list-row">
                                <span class="list-row__main">{{ $condition->display_name ?? $condition->code ?? '—' }}</span>
                                <span class="badge {{ $condition->status === 'chronic' ? 'badge-teal' : 'badge-primary' }}">{{ ucfirst($condition->status) }}</span>
                            </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="form-hint">Conditions are recorded by your healthcare providers and cannot be edited here.</div>
                </div>

            </div>
        </div>

        {{-- Emergency Contact --}}
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="phone-call"></i> Emergency Contact</h2></div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="emergency_contact[name]" class="form-control" value="{{ old('emergency_contact.name', ($patient->emergency_contact ?? [])['name'] ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="emergency_contact[phone]" class="form-control" value="{{ old('emergency_contact.phone', ($patient->emergency_contact ?? [])['phone'] ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="emergency_contact[relationship]" class="form-control" value="{{ old('emergency_contact.relationship', ($patient->emergency_contact ?? [])['relationship'] ?? '') }}">
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN ─────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">

        {{-- Identity (read-only) --}}
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="fingerprint"></i> Identity</h2></div>
            <div class="panel-body">
                <div class="kv-table">
                    <div class="kv-strong">Health ID</div>
                    <div class="mono">{{ $patient->health_id }}</div>
                    @if($patient->cnamgs_id)
                    <div class="kv-strong">CNAMGS ID</div>
                    <div class="mono">{{ $patient->cnamgs_id }}</div>
                    @endif
                    <div class="kv-strong">Status</div>
                    <div>
                        @php
                            $portalStatus = $patient->verification_status ?? $patient->identity_status ?? 'Active';
                            $portalStatus = $portalStatus instanceof \BackedEnum ? $portalStatus->value : $portalStatus;
                        @endphp
                        {{ ucfirst(str_replace('_', ' ', $portalStatus)) }}
                    </div>
                </div>
                <div class="form-hint mt-3">Identity fields are managed by OpesCare. To update, contact your registered facility.</div>
            </div>
        </div>

        {{-- Privacy Settings --}}
        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="shield-check"></i> Privacy Settings</h2></div>
            <div class="panel-body">

                <label class="form-check">
                    <input type="hidden" name="privacy_require_consent" value="0">
                    <input type="checkbox" name="privacy_require_consent" value="1"
                        {{ ($patient->privacy_preferences['require_consent_for_full_record'] ?? true) ? 'checked' : '' }}>
                    <div>
                        <div class="td-strong">Require Consent for Full Record</div>
                        <div class="text-sm text-muted">Providers can only see a masked preview without your explicit consent.</div>
                    </div>
                </label>

                <label class="form-check">
                    <input type="hidden" name="privacy_emergency_access" value="0">
                    <input type="checkbox" name="privacy_emergency_access" value="1"
                        {{ ($patient->privacy_preferences['emergency_access_allowed'] ?? true) ? 'checked' : '' }}>
                    <div>
                        <div class="td-strong">Allow Emergency Access</div>
                        <div class="text-sm text-muted">Permit audited break-glass access during emergencies without standard consent.</div>
                    </div>
                </label>

            </div>
        </div>

        {{-- Save --}}
        <div>
            <button type="submit" class="btn btn-primary btn-block">
                <i data-lucide="save"></i> Save Changes
            </button>
        </div>

    </div>

</div>
</form>
@endif

@endsection
