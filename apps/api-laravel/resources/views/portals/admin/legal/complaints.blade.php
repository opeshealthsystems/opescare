@extends('layouts.portal')
@section('title', 'Privacy Complaints')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.legal') }}">Legal Documents</a>
    <i data-lucide="chevron-right"></i>
    <span>Privacy Complaints</span>
</div>

<div class="page-head">
    <h2>Privacy complaints</h2>
</div>
<p class="td-muted mb-6">NDPR/GDPR Article 77 complaint log.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Complainant</th><th>Type</th><th>Description</th><th>Status</th><th>Received</th><th class="row-actions"></th></tr>
            </thead>
            <tbody>
                @forelse($complaints as $c)
                    <tr>
                        <td data-label="Complainant" class="td-strong">
                            {{ $c->complainant_name ?: ($c->patient?->first_name . ' ' . $c->patient?->last_name) ?: '—' }}
                            @if($c->complainant_email)
                                <div class="td-muted code-muted">{{ $c->complainant_email }}</div>
                            @endif
                        </td>
                        <td data-label="Type">
                            <span class="badge badge-warning badge-sm">{{ ucwords(str_replace('_', ' ', $c->complaint_type)) }}</span>
                        </td>
                        <td data-label="Description">{{ \Illuminate\Support\Str::limit($c->description, 80) }}</td>
                        <td data-label="Status">
                            <span class="badge badge--{{ $c->statusColor() }} badge-sm">{{ ucfirst(str_replace('_', ' ', $c->status)) }}</span>
                        </td>
                        <td data-label="Received" class="td-muted">{{ $c->created_at->format('d M Y') }}</td>
                        <td class="row-actions">
                            @if($c->status === 'open' || $c->status === 'under_review')
                            <form method="POST" action="{{ route('portals.admin.legal.complaints.resolve', $c) }}"
                                  onsubmit="return confirm('Mark as resolved?')">
                                @csrf
                                <input type="hidden" name="resolution" value="Reviewed and resolved by OpesCare compliance team.">
                                <button type="submit" class="btn btn-success btn-sm">Resolve</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="td-muted empty-cell">No privacy complaints on file.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="mt-6">{{ $complaints->links() }}</div>

@endsection
