@extends('layouts.portal')

@section('title', 'Consent Requests — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Consent Requests')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Consent Requests</h1>
        <p class="page-subtitle">Review and manage access requests from healthcare providers.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-info" style="margin-bottom:var(--p-space-4);"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@elseif($consentRequests->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="shield-check"></i></div>
        <h3>No Consent Requests</h3>
        <p>You have no pending or past access requests.</p>
    </div>
</div>
@else
<div style="display:flex;flex-direction:column;gap:var(--p-space-4);">
@foreach($consentRequests as $req)
<div class="panel">
    <div class="panel-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:var(--p-space-4);">
            <div style="flex:1;">
                <div style="font-weight:700;font-size:0.9375rem;margin-bottom:4px;">
                    {{ $req->requestingFacility?->name ?? 'Unknown Facility' }}
                </div>
                <div style="font-size:0.875rem;color:var(--p-text-muted);margin-bottom:var(--p-space-3);">
                    {{ $req->purpose ?? 'Access request' }}
                </div>
                <div style="display:flex;gap:var(--p-space-2);flex-wrap:wrap;">
                    @foreach(($req->requested_scope ?? []) as $scope)
                    <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;background:var(--p-surface-2);color:var(--p-text-muted);">{{ $scope }}</span>
                    @endforeach
                </div>
                <div style="font-size:0.8125rem;color:var(--p-text-muted);margin-top:var(--p-space-2);">
                    Requested {{ $req->created_at->diffForHumans() }}
                    @if($req->duration_minutes)
                     · Valid for {{ round($req->duration_minutes / 60, 1) }} hours
                    @endif
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:var(--p-space-2);">
                @if($req->status === 'pending')
                <form method="POST" action="{{ route('portals.patient.consent.approve', $req->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="font-size:0.8125rem;">
                        <i data-lucide="check" style="width:0.875rem;height:0.875rem;"></i> Approve
                    </button>
                </form>
                <form method="POST" action="{{ route('portals.patient.consent.deny', $req->id) }}">
                    @csrf
                    <button type="submit" class="btn" style="font-size:0.8125rem;background:var(--p-surface-2);color:var(--p-text-muted);">
                        <i data-lucide="x" style="width:0.875rem;height:0.875rem;"></i> Deny
                    </button>
                </form>
                @else
                <span style="padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;
                    background:{{ $req->status === 'approved' ? '#D1FAE5' : 'var(--p-surface-2)' }};
                    color:{{ $req->status === 'approved' ? '#059669' : 'var(--p-text-muted)' }};">
                    {{ ucfirst($req->status) }}
                </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
</div>
@endif

@endsection
