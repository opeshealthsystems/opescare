@extends('layouts.portal')
@section('title', 'Account Closure Requests')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.legal') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Legal Documents</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Account Closure Requests</h1>
            <p class="portal-page-subtitle">Patient requests to close their OpesCare account</p>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th>Patient</th><th>Reason</th><th>Delete Req?</th><th>Export Req?</th><th>Status</th><th>Requested</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td style="font-weight:600;font-size:0.88rem;">
                                {{ $req->patient?->first_name }} {{ $req->patient?->last_name }}
                                <div style="font-family:monospace;font-size:0.72rem;color:#9ca3af;">{{ $req->patient?->health_id }}</div>
                            </td>
                            <td style="font-size:0.82rem;max-width:200px;">{{ \Illuminate\Support\Str::limit($req->reason, 60) ?: '—' }}</td>
                            <td style="font-size:0.82rem;color:{{ $req->data_delete_requested ? '#dc2626' : '#9ca3af' }};">
                                {{ $req->data_delete_requested ? 'Yes' : 'No' }}
                            </td>
                            <td style="font-size:0.82rem;color:{{ $req->data_export_requested ? '#2563eb' : '#9ca3af' }};">
                                {{ $req->data_export_requested ? 'Yes' : 'No' }}
                            </td>
                            <td>
                                <span class="badge badge--{{ $req->statusColor() }}" style="font-size:0.72rem;">{{ ucfirst($req->status) }}</span>
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">{{ $req->created_at->format('d M Y') }}</td>
                            <td>
                                @if($req->isPending())
                                <div style="display:flex;gap:6px;justify-content:flex-end;">
                                    <form method="POST" action="{{ route('portals.admin.legal.closures.review', $req) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn--success btn--sm"
                                                onclick="return confirm('Approve closure request?')">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('portals.admin.legal.closures.review', $req) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn--outline btn--sm">Reject</button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">No account closure requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $requests->links() }}

</div>
@endsection
