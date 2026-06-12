@extends('layouts.portal')
@section('title', 'Roles Management')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Roles & RBAC')
@section('content')

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1 class="page-title">Roles &amp; RBAC</h1>
        <p class="page-subtitle">Manage platform roles and their portal assignments.</p>
    </div>
    <button onclick="document.getElementById('create-role-modal').style.display='flex'" class="btn btn-primary">
        <i data-lucide="plus"></i> Create Role
    </button>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Portal</th>
                    <th>Users</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>
                        <strong>{{ $role->name }}</strong>
                        @if($role->is_protected)<span class="badge badge-neutral" style="margin-left:.4rem;">Protected</span>@endif
                    </td>
                    <td style="color:var(--p-text-muted);font-size:.85rem;">{{ $role->description ?? '—' }}</td>
                    <td>
                        @php
                        $pColors=['patient_family'=>'badge-primary','clinical'=>'badge-success','admin'=>'badge-danger','finance'=>'badge-warning','connect'=>'badge-teal','public'=>'badge-neutral'];
                        $pc=$pColors[$role->portal??'']??'badge-neutral';
                        @endphp
                        <span class="badge {{ $pc }}">{{ ucfirst(str_replace('_',' ',$role->portal??'—')) }}</span>
                    </td>
                    <td>{{ $role->users_count ?? 0 }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:.35rem;justify-content:flex-end;">
                            <a href="{{ route('portals.admin.roles.users', $role) }}" class="btn btn-primary btn-xs" title="View Users">
                                <i data-lucide="users"></i>
                            </a>
                            <button class="btn btn-ghost btn-xs" title="Edit"
                                onclick="openEditRole('{{ $role->id }}','{{ addslashes($role->name) }}','{{ addslashes($role->description ?? '') }}','{{ $role->portal }}','{{ $role->is_protected ? '1':'0' }}')">
                                <i data-lucide="pencil"></i>
                            </button>
                            @if(!$role->is_protected)
                            <form method="POST" action="{{ route('portals.admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete role {{ addslashes($role->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs" title="Delete"><i data-lucide="trash-2"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:.75rem 1.25rem;">{{ $roles->links() }}</div>
</div>

{{-- Create Role Modal --}}
<div id="create-role-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:520px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;">Create Role</h3>
            <button onclick="document.getElementById('create-role-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="{{ route('portals.admin.roles.store') }}">
            @csrf
            <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Role Name <span style="color:var(--p-danger);">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. senior_nurse">
                    <div style="font-size:.75rem;color:var(--p-text-muted);margin-top:.25rem;">Lowercase, numbers, underscores only.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Portal <span style="color:var(--p-danger);">*</span></label>
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
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--p-border);display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('create-role-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Role</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Role Modal --}}
<div id="edit-role-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:520px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;">Edit Role</h3>
            <button onclick="document.getElementById('edit-role-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" id="edit-role-form" action="">
            @csrf @method('PUT')
            <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Role Name</label>
                    <input type="text" id="edit-role-name" class="form-control" disabled>
                    <div id="edit-role-note" style="font-size:.75rem;color:var(--p-text-muted);margin-top:.25rem;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit-role-desc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Portal <span style="color:var(--p-danger);">*</span></label>
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
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--p-border);display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('edit-role-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
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
    document.getElementById('edit-role-modal').style.display = 'flex';
}
document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('[id$="-modal"]').forEach(m=>m.style.display='none');}});
</script>
@endsection
