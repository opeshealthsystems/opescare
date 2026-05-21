@extends('layouts.portal')
@section('title', 'Production Access Requests')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.developer.dashboard') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Developer Portal</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Production Access Requests</h1>
        </div>
        <a href="{{ route('portals.developer.production_requests.create') }}" class="btn btn--primary">+ New Request</a>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;">✓ {{ session('success') }}</div>
    @endif

    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:0.84rem;color:#0369a1;">
        Production access requests are reviewed by the OpesCare team within <strong>3–5 business days</strong>.
        All production integrations must pass the
        <a href="{{ route('portals.admin.certifications.index') }}" style="color:#0369a1;">Integration Certification</a> checklist
        before going live.
    </div>

    @if($requests->isEmpty())
    <div class="portal-card" style="padding:40px;text-align:center;color:#9ca3af;">
        <div style="font-size:1.8rem;margin-bottom:12px;">🚀</div>
        <p style="font-size:0.88rem;">No production access requests yet.</p>
        <a href="{{ route('portals.developer.production_requests.create') }}" class="btn btn--primary btn--sm" style="margin-top:12px;">Request Production Access</a>
    </div>
    @else
    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead><tr>
                    <th>Use Case</th><th>Scopes Requested</th><th>Patient Data</th><th>Status</th><th>Submitted</th><th>Reviewed</th>
                </tr></thead>
                <tbody>
                @foreach($requests as $req)
                <tr>
                    <td>
                        <strong>{{ Str::limit($req->use_case, 50) }}</strong>
                        @if($req->integration_client_id)
                        <div style="font-size:0.75rem;color:#9ca3af;font-family:monospace;">{{ Str::limit($req->integration_client_id, 24) }}</div>
                        @endif
                    </td>
                    <td style="font-size:0.78rem;color:#6b7280;">{{ count((array)$req->requested_scopes) }} scopes</td>
                    <td>
                        @if($req->handles_patient_data)
                        <span class="badge badge--warning" style="font-size:0.68rem;">Yes</span>
                        @else
                        <span class="badge badge--neutral" style="font-size:0.68rem;">No</span>
                        @endif
                    </td>
                    <td><span class="{{ $req->statusBadgeClass() }}" style="font-size:0.72rem;">{{ ucfirst(str_replace('_',' ',$req->status)) }}</span></td>
                    <td style="color:#9ca3af;font-size:0.8rem;">{{ $req->created_at->format('d M Y') }}</td>
                    <td style="color:#9ca3af;font-size:0.8rem;">
                        {{ $req->reviewed_at?->format('d M Y') ?? '—' }}
                        @if($req->review_notes)
                        <div style="font-size:0.74rem;color:#6b7280;">{{ Str::limit($req->review_notes, 40) }}</div>
                        @endif
                        @if($req->rejected_reason)
                        <div style="font-size:0.74rem;color:#dc2626;">{{ Str::limit($req->rejected_reason, 40) }}</div>
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
