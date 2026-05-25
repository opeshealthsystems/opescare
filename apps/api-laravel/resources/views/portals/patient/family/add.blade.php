@extends('layouts.portal')
@section('title', 'Add Dependent — OpesCare')
@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Add Dependent')

@section('content')
<div class="panel" style="max-width:600px;">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="user-plus"></i> Register a Dependent</h2>
    </div>
    <div class="panel-body">
        <p style="font-size:0.875rem;color:var(--p-text-muted);margin-bottom:var(--p-space-5);">
            This creates a new patient record for your dependent. No login account is created — you manage their records.
        </p>
        @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:var(--p-space-4);">
            <i data-lucide="alert-circle"></i>
            <ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('portals.patient.family.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-4);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="form-input" style="width:100%;margin-top:0.25rem;">
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="form-input" style="width:100%;margin-top:0.25rem;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-4);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="form-input" style="width:100%;margin-top:0.25rem;">
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Sex *</label>
                    <select name="sex" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="">— select —</option>
                        <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('sex') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-6);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Your Relationship *</label>
                    <select name="relationship" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="">— select —</option>
                        @foreach(['parent','grandparent','spouse','sibling','caregiver','legal_guardian','other'] as $r)
                        <option value="{{ $r }}" {{ old('relationship') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Access Level *</label>
                    <select name="access_level" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="full" {{ old('access_level','full') === 'full' ? 'selected' : '' }}>Full Access</option>
                        <option value="read_only" {{ old('access_level') === 'read_only' ? 'selected' : '' }}>Read Only</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:var(--p-space-3);">
                <button type="submit" class="btn btn-primary">Register Dependent</button>
                <a href="{{ route('portals.patient.family') }}" class="btn" style="background:var(--p-surface-2);color:var(--p-text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
