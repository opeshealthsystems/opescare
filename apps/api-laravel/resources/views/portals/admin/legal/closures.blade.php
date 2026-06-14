@extends('layouts.portal')
@section('title', 'Account Closure Requests')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.legal') }}">Legal Documents</a>
    <i data-lucide="chevron-right"></i>
    <span>Account Closures</span>
</div>

<div class="page-head">
    <h2>Account closure requests</h2>
</div>
<p class="td-muted mb-6">Patient requests to close their OpesCare account.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Patient</th><th>Reason</th><th>Delete Req?</th><th>Export Req?</th><th>Status</th><th>Requested</th><th class="row-actions"></th></tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td data-label="Patient" class="td-strong">
                            {{ $req->patient?->first_name }} {{ $req->patient?->last_name }}
                            <div class="code-muted">{{ $req->patient?->health_id }}</div>
                        </td>
                        <td data-label="Reason">{{ \Illuminate\Support\Str::limit($req->reason, 60) ?: '—' }}</td>
                        <td data-label="Delete Req?">
                            @if($req->data_delete_requested)<span class="badge badge-danger badge-sm">Yes</span>@else<span class="td-muted">No</span>@endif
                        </td>
                        <td data-label="Export Req?">
                            @if($req->data_export_requested)<span class="badge badge-primary badge-sm">Yes</span>@else<span class="td-muted">No</span>@endif
                        </td>
                        <td data-label="Status">
                            <span class="badge badge--{{ $req->statusColor() }} badge-sm">{{ ucfirst($req->status) }}</span>
                        </td>
                        <td data-label="Requested" class="td-muted">{{ $req->created_at->format('d M Y') }}</td>
                        <td class="row-actions">
                            @if($req->isPending())
                            <div class="row-actions-inline">
                                <form method="POST" action="{{ route('portals.admin.legal.closures.review', $req) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm"
                                            onclick="return confirm('Approve closure request?')">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('portals.admin.legal.closures.review', $req) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-secondary btn-sm">Reject</button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="td-muted empty-cell">No account closure requests.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="mt-6">{{ $requests->links() }}</div>

@endsection
