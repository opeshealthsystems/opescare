@extends('layouts.portal')
@section('title', 'Invite Family Member — OpesCare')
@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Invite Member')

@section('content')
<div class="panel" style="max-width:560px;">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="mail"></i> Invite an Existing Patient</h2>
    </div>
    <div class="panel-body">
        <p style="font-size:0.875rem;color:var(--p-text-muted);margin-bottom:var(--p-space-5);">
            Enter the Health ID or email of a patient who already has an OpesCare record. They will receive an invite link to approve the connection.
        </p>
        @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:var(--p-space-4);">
            <i data-lucide="alert-circle"></i>
            <ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('portals.patient.family.invite.send') }}">
            @csrf
            <div style="margin-bottom:var(--p-space-4);">
                <label style="font-size:0.875rem;font-weight:500;">Health ID or Email *</label>
                <input type="text" name="health_id_or_email" value="{{ old('health_id_or_email') }}" required class="form-input" placeholder="CM-HID-XXXX-XXXX-XXXX or email@example.com" style="width:100%;margin-top:0.25rem;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-6);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Relationship *</label>
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
                        <option value="read_only" {{ old('access_level','read_only') === 'read_only' ? 'selected' : '' }}>Read Only</option>
                        <option value="full" {{ old('access_level') === 'full' ? 'selected' : '' }}>Full Access</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:var(--p-space-3);">
                <button type="submit" class="btn btn-primary">Send Invite</button>
                <a href="{{ route('portals.patient.family') }}" class="btn" style="background:var(--p-surface-2);color:var(--p-text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
