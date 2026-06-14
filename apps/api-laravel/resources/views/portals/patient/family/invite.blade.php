@extends('layouts.portal')
@section('title', 'Invite Family Member — OpesCare')
@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Invite Member')

@section('content')
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="mail"></i> Invite an Existing Patient</h2>
    </div>
    <div class="panel-body">
        <p class="text-sm text-muted mb-6">
            Enter the Health ID or email of a patient who already has an OpesCare record. They will receive an invite link to approve the connection.
        </p>
        @if($errors->any())
        <div class="alert alert-danger mb-4">
            <i data-lucide="alert-circle"></i>
            <ul class="alert-list">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="POST" action="{{ route('portals.patient.family.invite.send') }}">
            @csrf
            <div class="form-group">
                <label class="form-label form-label-required">Health ID or Email</label>
                <input type="text" name="health_id_or_email" value="{{ old('health_id_or_email') }}" required class="form-control" placeholder="CM-HID-XXXX-XXXX-XXXX or email@example.com">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">Relationship</label>
                    <select name="relationship" required class="form-control">
                        <option value="">— select —</option>
                        @foreach(['parent','grandparent','spouse','sibling','caregiver','legal_guardian','other'] as $r)
                        <option value="{{ $r }}" {{ old('relationship') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Access Level</label>
                    <select name="access_level" required class="form-control">
                        <option value="read_only" {{ old('access_level','read_only') === 'read_only' ? 'selected' : '' }}>Read Only</option>
                        <option value="full" {{ old('access_level') === 'full' ? 'selected' : '' }}>Full Access</option>
                    </select>
                </div>
            </div>
            <div class="row-actions mt-6">
                <button type="submit" class="btn btn-primary">Send Invite</button>
                <a href="{{ route('portals.patient.family') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
