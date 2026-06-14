@extends('layouts.portal')
@section('title', 'Organizations')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Organizations')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.organizations.index') }}">Organizations</a>
    <i data-lucide="chevron-right"></i>
    <span>Directory</span>
</div>

<div class="page-head">
    <h2>Organizations</h2>
    <div class="page-head__spacer"></div>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

@if($pendingCount > 0)
<div class="panel mb-6">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="clock"></i> {{ $pendingCount }} organization{{ $pendingCount>1?'s':'' }} awaiting approval</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>License</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($pending as $p)
                <tr>
                    <td data-label="Name"><span class="td-strong">{{ $p->name }}</span></td>
                    <td data-label="Type">{{ ucfirst($p->type??'') }}</td>
                    <td data-label="License" class="td-mono">{{ $p->license_number??'—' }}</td>
                    <td class="row-actions" data-label="Actions">
                        <form method="POST" action="{{ route('admin.organizations.approve',$p->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-success btn-sm"><i data-lucide="check-circle"></i> Approve</button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('reject-pending-{{ $p->id }}')"><i data-lucide="x-circle"></i> Reject</button>
                        <div id="reject-pending-{{ $p->id }}" class="modal-backdrop mt-6" hidden>
                            <div class="modal" role="dialog" aria-modal="true">
                                <h3 class="modal__title"><i data-lucide="x-circle"></i> Reject organization</h3>
                                <form method="POST" action="{{ route('admin.organizations.reject',$p->id) }}">@csrf
                                    <div class="modal__body"><p>Reject <strong>{{ $p->name }}</strong>?</p></div>
                                    <div class="modal__footer">
                                        <button type="button" class="btn btn-ghost" onclick="opCloseModal('reject-pending-{{ $p->id }}')">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="building-2"></i> Total: {{ $total }}</h3>
        <div class="page-head__spacer"></div>
        @foreach($byType as $type => $count)<span class="badge badge-neutral">{{ $count }} {{ $type }}</span> @endforeach
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>License</th>
                    <th>Status</th>
                    <th>Since</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($facilities as $f)
                <tr>
                    <td data-label="Name"><span class="td-strong">{{ $f->name }}</span></td>
                    <td data-label="Type">{{ ucfirst($f->type??'') }}</td>
                    <td data-label="License" class="td-mono">{{ $f->license_number??'—' }}</td>
                    <td data-label="Status">
                        @if($f->status==='active')<span class="badge badge-success">Active</span>
                        @elseif(in_array($f->status,['suspended','rejected']))<span class="badge badge-danger">{{ ucfirst($f->status) }}</span>
                        @else<span class="badge badge-warning">{{ ucfirst($f->status??'pending') }}</span>@endif
                    </td>
                    <td data-label="Since" class="td-muted">{{ $f->created_at?->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        @if($f->status==='pending')
                        <form method="POST" action="{{ route('admin.organizations.approve',$f->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Approve organization" title="Approve"><i data-lucide="check-circle"></i></button>
                        </form>
                        @endif
                        <button type="button" class="icon-btn" aria-label="Delete organization" title="Delete" onclick="opOpenModal('delete-org-{{ $f->id }}')"><i data-lucide="trash-2"></i></button>
                        <div id="delete-org-{{ $f->id }}" class="modal-backdrop mt-6" hidden>
                            <div class="modal" role="dialog" aria-modal="true">
                                <h3 class="modal__title"><i data-lucide="alert-triangle"></i> Delete organization</h3>
                                <form method="POST" action="{{ route('admin.organizations.destroy',$f->id) }}">@csrf @method('DELETE')
                                    <div class="modal__body"><p>Delete organization <strong>{{ $f->name }}</strong>? This cannot be undone.</p></div>
                                    <div class="modal__footer">
                                        <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-org-{{ $f->id }}')">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="td-muted empty-cell">No organizations found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $facilities->links() }}</div>
</div>

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
