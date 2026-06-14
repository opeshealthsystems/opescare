@extends('layouts.portal')
@section('title', 'Pending Approvals')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Organizations')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.organizations.index') }}">Organizations</a>
    <i data-lucide="chevron-right"></i>
    <span>Pending Approvals</span>
</div>

<div class="page-head">
    <h2>Pending Approvals</h2>
    <div class="page-head__spacer"></div>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

@if($organizations->isEmpty())
<div class="panel">
    <div class="panel-body">
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
            <p>No organizations pending approval.</p>
        </div>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="clock"></i> {{ $organizations->total() }} pending</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Region</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($organizations as $org)
                @php $tBadge=match($org->type??''){'hospital'=>'badge-primary','clinic'=>'badge-success','pharmacy'=>'badge-warning','lab'=>'badge-neutral',default=>'badge-neutral'}; @endphp
                <tr>
                    <td data-label="Name">
                        <span class="td-strong">{{ $org->name }}</span>
                        @if($org->email)<div class="td-muted">{{ $org->email }}</div>@endif
                    </td>
                    <td data-label="Type"><span class="badge {{ $tBadge }}">{{ ucfirst($org->type??'—') }}</span></td>
                    <td data-label="Region" class="td-muted">{{ $org->region ?? '—' }}</td>
                    <td data-label="Status">
                        @if(($org->status??'')==='submitted')<span class="badge badge-primary">Submitted</span>
                        @else<span class="badge badge-warning">Pending</span>@endif
                    </td>
                    <td data-label="Applied" class="td-muted">{{ $org->created_at->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        <form method="POST" action="{{ route('portals.admin.organizations.approve', $org) }}" class="inline-form">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm"><i data-lucide="check"></i> Approve</button>
                        </form>
                        <button type="button" class="btn btn-warning btn-sm" onclick="openRejectModal('{{ $org->id }}','{{ addslashes($org->name) }}')">
                            <i data-lucide="x"></i> Reject
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($organizations->hasPages())
    <div class="panel-body">{{ $organizations->links() }}</div>
    @endif
</div>
@endif

{{-- Reject Modal --}}
<div id="reject-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="reject-modal-title">
        <h3 class="modal__title" id="reject-modal-title"><i data-lucide="x-circle"></i> Reject Organization</h3>
        <form method="POST" id="reject-form" action="">
            @csrf @method('PATCH')
            <div class="modal__body">
                <p class="mb-6">Rejecting: <strong id="reject-org-name"></strong></p>
                <div class="form-group">
                    <label class="form-label form-label-required">Reason for rejection</label>
                    <textarea name="reason" class="form-control" rows="4" placeholder="Provide a clear reason for rejection…" required></textarea>
                    <div class="form-hint">This reason may be communicated to the applicant.</div>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('reject-modal')">Cancel</button>
                <button type="submit" class="btn btn-warning"><i data-lucide="x-circle"></i> Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
function openRejectModal(id, name) {
    document.getElementById('reject-org-name').textContent = name;
    document.getElementById('reject-form').action = '/admin/organizations/' + id + '/reject';
    opOpenModal('reject-modal');
}
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
