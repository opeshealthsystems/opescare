@extends('layouts.portal')
@php $l = app()->getLocale(); @endphp
@section('title', 'My Family — OpesCare')
@section('breadcrumb_home', __('public.portal.my_portal', [], $l) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'My Family')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--p-space-6);">
    <h1 style="font-size:1.25rem;font-weight:700;color:var(--p-text);">My Family</h1>
    <div style="display:flex;gap:var(--p-space-3);">
        <a href="{{ route('portals.patient.family.add') }}" class="btn btn-primary" style="font-size:0.875rem;">
            <i data-lucide="user-plus"></i> Add Dependent
        </a>
        <a href="{{ route('portals.patient.family.invite') }}" class="btn btn-primary" style="font-size:0.875rem;background:var(--p-surface-2);color:var(--p-text);">
            <i data-lucide="mail"></i> Invite Member
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:var(--p-space-5);">
    <i data-lucide="check-circle"></i> {{ session('success') }}
</div>
@endif

@if($links->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="users"></i></div>
        <h3>No family members linked yet</h3>
        <p>Add a dependent or invite an existing patient to link their records to yours.</p>
    </div>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:var(--p-space-5);">
    @foreach($links as $link)
    <div class="panel">
        <div class="panel-body">
            <div style="display:flex;align-items:center;gap:var(--p-space-3);margin-bottom:var(--p-space-4);">
                <div style="width:2.5rem;height:2.5rem;background:var(--p-primary-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--p-primary);font-size:1rem;">
                    {{ strtoupper(substr($link->dependentPatient->first_name ?? 'D', 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:600;">{{ $link->dependentPatient->first_name }} {{ $link->dependentPatient->last_name }}</div>
                    <div style="font-size:0.75rem;color:var(--p-text-muted);">{{ $link->dependentPatient->health_id }}</div>
                </div>
                @if($link->status === 'pending_invite')
                <span style="margin-left:auto;font-size:0.7rem;background:#FEF3C7;color:#92400E;padding:2px 8px;border-radius:99px;">Pending</span>
                @else
                <span style="margin-left:auto;font-size:0.7rem;background:#D1FAE5;color:#065F46;padding:2px 8px;border-radius:99px;">Active</span>
                @endif
            </div>
            <div style="font-size:0.8125rem;color:var(--p-text-muted);margin-bottom:var(--p-space-4);">
                {{ ucfirst(str_replace('_', ' ', $link->relationship)) }} &middot;
                {{ $link->access_level === 'full' ? 'Full access' : 'Read only' }}
            </div>
            @if($link->isExpiredByAge())
            <div class="alert alert-warning" style="margin-bottom:var(--p-space-3);font-size:0.8rem;">
                <i data-lucide="alert-triangle"></i>
                Access in grace period — expires {{ $link->age_transition_expires_at->format('M d, Y') }}
            </div>
            @endif
            <div style="display:flex;gap:var(--p-space-2);flex-wrap:wrap;">
                @if($link->status === 'active')
                <form method="POST" action="{{ route('portals.patient.family.switch', $link->dependent_patient_id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="font-size:0.8rem;">View Records</button>
                </form>
                @endif
                <a href="{{ route('portals.patient.family.edit', $link->id) }}" class="btn" style="font-size:0.8rem;background:var(--p-surface-2);color:var(--p-text);">Edit</a>
                <form method="POST" action="{{ route('portals.patient.family.revoke', $link->id) }}" onsubmit="return confirm('Remove this family link?')">
                    @csrf
                    <button type="submit" class="btn" style="font-size:0.8rem;background:#FEE2E2;color:#991B1B;">Remove</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($incomingConsent->isNotEmpty())
<div data-section="guardian-consent" style="margin-top:var(--p-space-8);">
    <h2 style="font-size:1rem;font-weight:700;color:var(--p-text);margin-bottom:var(--p-space-4);">
        <i data-lucide="shield-alert"></i> Guardian Access — Your Approval Needed
    </h2>
    @foreach($incomingConsent as $cl)
    <div class="panel" style="margin-bottom:var(--p-space-4);border-left:3px solid #F59E0B;">
        <div class="panel-body">
            <p style="font-size:0.875rem;margin-bottom:var(--p-space-3);">
                <strong>{{ $cl->guardianUser->name ?? $cl->guardianUser->email }}</strong>
                has guardian access to your records. This access will expire on
                <strong>{{ $cl->age_transition_expires_at->format('M d, Y') }}</strong>
                unless you approve continued access.
            </p>
            <div style="display:flex;gap:var(--p-space-3);">
                <form method="POST" action="{{ route('portals.patient.family.guardian_consent.approve', $cl->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="font-size:0.8125rem;">Keep Access</button>
                </form>
                <form method="POST" action="{{ route('portals.patient.family.guardian_consent.deny', $cl->id) }}"
                      onsubmit="return confirm('Remove this guardian\'s access?')">
                    @csrf
                    <button type="submit" class="btn" style="font-size:0.8125rem;background:#FEE2E2;color:#991B1B;">Remove Access</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
