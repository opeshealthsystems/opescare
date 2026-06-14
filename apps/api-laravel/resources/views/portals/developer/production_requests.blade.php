@extends('layouts.portal')
@section('title', 'Production Access Requests')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="breadcrumb">
        <a href="{{ route('portals.developer.dashboard') }}">Developer portal</a>
        <i data-lucide="chevron-right"></i>
        <span>Production access</span>
    </div>

    <div class="page-head">
        <h2>Production access requests</h2>
        <div class="page-head__spacer"></div>
        <a href="{{ route('portals.developer.production_requests.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> New request</a>
    </div>

    @if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

    <div class="alert alert-info mb-6">
        <i data-lucide="info"></i>
        <div>Production access requests are reviewed by the OpesCare team within <strong>3–5 business days</strong>.
        All production integrations must pass the
        <a href="{{ route('portals.admin.certifications.index') }}">Integration Certification</a> checklist before going live.</div>
    </div>

    @if($requests->isEmpty())
    <div class="panel">
        <div class="empty-state">
            <i data-lucide="rocket" class="empty-state-icon"></i>
            <p>No production access requests yet.</p>
            <a href="{{ route('portals.developer.production_requests.create') }}" class="btn btn-primary btn-sm"><i data-lucide="rocket"></i> Request production access</a>
        </div>
    </div>
    @else
    <div class="panel">
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Use case</th><th>Scopes requested</th><th>Patient data</th><th>Status</th><th>Submitted</th><th>Reviewed</th>
                </tr></thead>
                <tbody>
                @foreach($requests as $req)
                <tr>
                    <td data-label="Use case">
                        <span class="td-strong">{{ Str::limit($req->use_case, 50) }}</span>
                        @if($req->integration_client_id)
                        <div class="mono">{{ Str::limit($req->integration_client_id, 24) }}</div>
                        @endif
                    </td>
                    <td data-label="Scopes requested" class="td-muted">{{ count((array)$req->requested_scopes) }} scopes</td>
                    <td data-label="Patient data">
                        @if($req->handles_patient_data)
                        <span class="badge badge-warning">Yes</span>
                        @else
                        <span class="badge badge-neutral">No</span>
                        @endif
                    </td>
                    <td data-label="Status"><span class="{{ $req->statusBadgeClass() }}">{{ ucfirst(str_replace('_',' ',$req->status)) }}</span></td>
                    <td data-label="Submitted" class="td-muted">{{ $req->created_at->format('d M Y') }}</td>
                    <td data-label="Reviewed" class="td-muted">
                        {{ $req->reviewed_at?->format('d M Y') ?? '—' }}
                        @if($req->review_notes)
                        <div class="td-muted">{{ Str::limit($req->review_notes, 40) }}</div>
                        @endif
                        @if($req->rejected_reason)
                        <div class="badge badge-danger">{{ Str::limit($req->rejected_reason, 40) }}</div>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@endsection
