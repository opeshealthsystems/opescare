@extends('layouts.portal')
@section('title', 'Roles Management')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Roles & RBAC')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.roles.index') }}">Roles &amp; RBAC</a>
    <i data-lucide="chevron-right"></i>
    <span>Access matrix</span>
</div>

<div class="page-head">
    <h2>Roles &amp; RBAC</h2>
    <div class="page-head__spacer"></div>
    <button type="button" class="btn btn-primary" onclick="opOpenModal('create-role-modal')">
        <i data-lucide="plus"></i> Create Role
    </button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="shield"></i> Roles</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Tier</th>
                    <th>Description</th>
                    <th>Portal</th>
                    <th>Users</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                @php
                    $platformPortals = ['admin','finance'];
                    $isPlatform = in_array($role->portal ?? '', $platformPortals, true);
                    $pColors=['patient_family'=>'badge-primary','clinical'=>'badge-success','admin'=>'badge-danger','finance'=>'badge-warning','connect'=>'badge-teal','public'=>'badge-neutral'];
                    $pc=$pColors[$role->portal??'']??'badge-neutral';
                @endphp
                <tr>
                    <td data-label="Role Name">
                        <span class="td-strong">{{ $role->name }}</span>
                        @if($role->is_protected)<span class="badge badge-neutral">Protected</span>@endif
                    </td>
                    <td data-label="Tier">
                        @if($isPlatform)<span class="badge badge-primary">Platform</span>
                        @else<span class="badge badge-teal">Facility</span>@endif
                    </td>
                    <td data-label="Description" class="td-muted">{{ $role->description ?? '—' }}</td>
                    <td data-label="Portal"><span class="badge {{ $pc }}">{{ ucfirst(str_replace('_',' ',$role->portal??'—')) }}</span></td>
                    <td data-label="Users">{{ $role->users_count ?? 0 }}</td>
                    <td class="row-actions" data-label="Actions">
                        <a href="{{ route('portals.admin.roles.users', $role) }}" class="icon-btn" aria-label="View Users" title="View Users"><i data-lucide="users"></i></a>
                        <button type="button" class="icon-btn" aria-label="Edit" title="Edit"
                            onclick="openEditRole('{{ $role->id }}','{{ addslashes($role->name) }}','{{ addslashes($role->description ?? '') }}','{{ $role->portal }}','{{ $role->is_protected ? '1':'0' }}')">
                            <i data-lucide="pencil"></i>
                        </button>
                        @if(!$role->is_protected)
                        <button type="button" class="icon-btn" aria-label="Delete" title="Delete" onclick="opOpenModal('delete-role-{{ $role->id }}')"><i data-lucide="trash-2"></i></button>
                        <div id="delete-role-{{ $role->id }}" class="modal-backdrop mt-6" hidden>
                            <div class="modal" role="dialog" aria-modal="true">
                                <h3 class="modal__title"><i data-lucide="alert-triangle"></i> Delete role</h3>
                                <form method="POST" action="{{ route('portals.admin.roles.destroy', $role) }}">@csrf @method('DELETE')
                                    <div class="modal__body"><p>Delete role <strong>{{ $role->name }}</strong>? This cannot be undone.</p></div>
                                    <div class="modal__footer">
                                        <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-role-{{ $role->id }}')">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="td-muted empty-cell">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $roles->links() }}</div>
</div>

{{-- Create Role Modal --}}
<div id="create-role-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="create-role-title">
        <h3 class="modal__title" id="create-role-title"><i data-lucide="plus"></i> Create Role</h3>
        <form method="POST" action="{{ route('portals.admin.roles.store') }}">
            @csrf
            <div class="modal__body">
                <div class="form-group mb-6">
                    <label class="form-label form-label-required">Role Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. senior_nurse">
                    <div class="form-hint">Lowercase, numbers, underscores only.</div>
                </div>
                <div class="form-group mb-6">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Portal</label>
                    <select name="portal" class="form-control" required>
                        <option value="">— Select Portal —</option>
                        <option value="patient_family">Patient &amp; Family</option>
                        <option value="clinical">Clinical</option>
                        <option value="admin">Admin</option>
                        <option value="finance">Finance</option>
                        <option value="connect">Connect</option>
                        <option value="public">Public</option>
                    </select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('create-role-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Role</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Role Modal --}}
<div id="edit-role-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="edit-role-title">
        <h3 class="modal__title" id="edit-role-title"><i data-lucide="pencil"></i> Edit Role</h3>
        <form method="POST" id="edit-role-form" action="">
            @csrf @method('PUT')
            <div class="modal__body">
                <div class="form-group mb-6">
                    <label class="form-label">Role Name</label>
                    <input type="text" id="edit-role-name" class="form-control" disabled>
                    <div id="edit-role-note" class="form-hint"></div>
                </div>
                <div class="form-group mb-6">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit-role-desc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Portal</label>
                    <select name="portal" id="edit-role-portal" class="form-control" required>
                        <option value="patient_family">Patient &amp; Family</option>
                        <option value="clinical">Clinical</option>
                        <option value="admin">Admin</option>
                        <option value="finance">Finance</option>
                        <option value="connect">Connect</option>
                        <option value="public">Public</option>
                    </select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('edit-role-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
function openEditRole(id, name, description, portal, isProtected) {
    document.getElementById('edit-role-name').value    = name;
    document.getElementById('edit-role-desc').value    = description;
    document.getElementById('edit-role-portal').value  = portal;
    const protectedEl = document.getElementById('edit-role-portal');
    const noteEl = document.getElementById('edit-role-note');
    if (isProtected === '1') {
        protectedEl.disabled = true;
        noteEl.textContent = 'Protected role — portal cannot be changed.';
    } else {
        protectedEl.disabled = false;
        noteEl.textContent = '';
    }
    document.getElementById('edit-role-form').action = '/portals/admin/roles/' + id;
    opOpenModal('edit-role-modal');
}
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
