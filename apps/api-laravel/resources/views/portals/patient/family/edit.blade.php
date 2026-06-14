@extends('layouts.portal')
@section('title', 'Edit Family Link — OpesCare')
@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Edit Family Link')

@section('content')
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="settings"></i>
            {{ $link->dependentPatient->first_name }} {{ $link->dependentPatient->last_name }}
        </h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.patient.family.update', $link->id) }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Relationship</label>
                    <select name="relationship" required class="form-control">
                        @foreach(['parent','grandparent','spouse','sibling','caregiver','legal_guardian','other'] as $r)
                        <option value="{{ $r }}" {{ $link->relationship === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Access Level</label>
                    <select name="access_level" required class="form-control">
                        <option value="full" {{ $link->access_level === 'full' ? 'selected' : '' }}>Full Access</option>
                        <option value="read_only" {{ $link->access_level === 'read_only' ? 'selected' : '' }}>Read Only</option>
                    </select>
                </div>
            </div>
            <h3 class="panel-title mb-3"><i data-lucide="bell"></i> Notification Preferences</h3>
            <div class="table-wrapper mb-6">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>In-Portal</th>
                            <th>Email</th>
                            <th>SMS</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach([
                        'lab_result'      => 'New Lab Result',
                        'appointment'     => 'Appointment',
                        'consent_request' => 'Consent Request',
                        'age_transition'  => 'Age Transition Alert',
                    ] as $key => $label)
                    <tr>
                        <td data-label="Event">{{ $label }}</td>
                        @foreach(['portal','email','sms'] as $ch)
                        <td data-label="{{ ucfirst($ch) }}">
                            <input type="checkbox" name="notification_prefs[{{ $key }}][{{ $ch }}]" value="1"
                                {{ $link->notificationPrefFor($key, $ch) ? 'checked' : '' }}>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="row-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('portals.patient.family') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
