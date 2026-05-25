@extends('layouts.portal')
@section('title', 'Edit Family Link — OpesCare')
@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Edit Family Link')

@section('content')
<div class="panel" style="max-width:640px;">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="settings"></i>
            {{ $link->dependentPatient->first_name }} {{ $link->dependentPatient->last_name }}
        </h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.patient.family.update', $link->id) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--p-space-4);margin-bottom:var(--p-space-5);">
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Relationship</label>
                    <select name="relationship" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        @foreach(['parent','grandparent','spouse','sibling','caregiver','legal_guardian','other'] as $r)
                        <option value="{{ $r }}" {{ $link->relationship === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:0.875rem;font-weight:500;">Access Level</label>
                    <select name="access_level" required class="form-input" style="width:100%;margin-top:0.25rem;">
                        <option value="full" {{ $link->access_level === 'full' ? 'selected' : '' }}>Full Access</option>
                        <option value="read_only" {{ $link->access_level === 'read_only' ? 'selected' : '' }}>Read Only</option>
                    </select>
                </div>
            </div>
            <h3 style="font-size:0.9375rem;font-weight:600;margin-bottom:var(--p-space-3);">Notification Preferences</h3>
            <table style="width:100%;font-size:0.875rem;margin-bottom:var(--p-space-6);">
                <thead>
                    <tr style="text-align:left;color:var(--p-text-muted);">
                        <th style="padding:0.5rem 0;font-weight:500;">Event</th>
                        <th style="padding:0.5rem;text-align:center;">In-Portal</th>
                        <th style="padding:0.5rem;text-align:center;">Email</th>
                        <th style="padding:0.5rem;text-align:center;">SMS</th>
                    </tr>
                </thead>
                <tbody>
                @foreach([
                    'lab_result'      => 'New Lab Result',
                    'appointment'     => 'Appointment',
                    'consent_request' => 'Consent Request',
                    'age_transition'  => 'Age Transition Alert',
                ] as $key => $label)
                <tr style="border-top:1px solid var(--p-border);">
                    <td style="padding:0.6rem 0;">{{ $label }}</td>
                    @foreach(['portal','email','sms'] as $ch)
                    <td style="padding:0.6rem;text-align:center;">
                        <input type="checkbox" name="notification_prefs[{{ $key }}][{{ $ch }}]" value="1"
                            {{ $link->notificationPrefFor($key, $ch) ? 'checked' : '' }}>
                    </td>
                    @endforeach
                </tr>
                @endforeach
                </tbody>
            </table>
            <div style="display:flex;gap:var(--p-space-3);">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('portals.patient.family') }}" class="btn" style="background:var(--p-surface-2);color:var(--p-text);">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
