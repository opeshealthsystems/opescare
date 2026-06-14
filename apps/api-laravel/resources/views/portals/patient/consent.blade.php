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
<div class="alert alert-info mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="alert-circle"></i></div>
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
@foreach($consentRequests as $req)
<div class="panel mb-4">
    <div class="panel-body">
        <div class="flex-between" style="align-items:flex-start;gap:var(--p-space-4);">
            <div style="flex:1;">
                <div class="td-strong">{{ $req->requestingFacility?->name ?? 'Unknown Facility' }}</div>
                <div class="text-muted mb-3">{{ $req->purpose ?? 'Access request' }}</div>
                <div class="gap-2" style="display:flex;flex-wrap:wrap;">
                    @foreach(($req->requested_scope ?? []) as $scope)
                    <span class="badge badge-neutral">{{ $scope }}</span>
                    @endforeach
                </div>
                <div class="text-sm text-muted mt-1">
                    Requested {{ $req->created_at->diffForHumans() }}
                    @if($req->duration_minutes)
                     · Valid for {{ round($req->duration_minutes / 60, 1) }} hours
                    @endif
                </div>
            </div>
            <div class="row-actions" style="flex-direction:column;align-items:flex-end;">
                @if($req->status === 'pending')
                <form method="POST" action="{{ route('portals.patient.consent.approve', $req->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="check"></i> Approve</button>
                </form>
                <form method="POST" action="{{ route('portals.patient.consent.deny', $req->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="x"></i> Deny</button>
                </form>
                @else
                <span class="badge {{ $req->status === 'approved' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($req->status) }}</span>
                @if($req->status === 'approved')
                <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('revoke-consent-{{ $req->id }}')">
                    <i data-lucide="shield-off"></i> Revoke access
                </button>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

@if(method_exists($consentRequests, 'links') && $consentRequests->hasPages())
<div class="mt-3">
    {{ $consentRequests->links() }}
</div>
@endif

@foreach($consentRequests as $req)
    @if($req->status === 'approved')
    <div id="revoke-consent-{{ $req->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="revoke-consent-{{ $req->id }}-title">
            <h3 class="modal__title" id="revoke-consent-{{ $req->id }}-title"><i data-lucide="shield-off"></i> Revoke access</h3>
            <form method="POST" action="{{ route('portals.patient.consent.revoke', $req->id) }}">
                @csrf
                <div class="modal__body">
                    <p>Revoke this consent? The facility will immediately lose access.</p>
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('revoke-consent-{{ $req->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Revoke access</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

@endif

@endsection

@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
