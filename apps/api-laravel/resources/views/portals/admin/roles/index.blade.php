@extends('layouts.portal')
@section('title', 'Roles')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Roles')
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Roles & RBAC</h1><p class="page-subtitle">{{ $roles->count() }} platform roles. Protected system roles cannot be deleted.</p></div>
    <div><button class="btn btn-primary btn-sm" onclick="document.getElementById('create-role-modal').style.display='flex'"><i data-lucide="plus" style="width:14px;height:14px;margin-right:.3rem;"></i>Create Role</button></div>
</div>
@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif
<div class="panel">
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Role Name</th><th>Description</th><th>Users</th><th>Actions</th></tr></thead><tbody>
    @foreach($roles as $role)
    <tr>
        <td><code style="font-size:.82rem;">{{ $role->name }}</code></td>
        <td style="font-size:.85rem;color:var(--p-text-muted);">{{ $role->description??'—' }}</td>
        <td><a href="{{ route('admin.roles.users',$role->id) }}" style="font-size:.85rem;">{{ number_format($role->users_count) }} users</a></td>
        <td>@php $isProtected=in_array(strtolower($role->name),['super_admin','platform_admin','system_admin','admin','super-admin','superadmin','system']); @endphp
            @if(!$isProtected)
            <form method="POST" action="{{ route('admin.roles.destroy',$role->id) }}" onsubmit="return confirm('Delete role?')" style="display:inline;">@csrf @method('DELETE')
                <button class="btn btn-danger btn-xs" {{ $role->users_count>0?'disabled title=Has users':'' }}>Delete</button>
            </form>
            @else<span style="font-size:.78rem;color:var(--p-text-muted);">Protected</span>@endif
        </td>
    </tr>
    @endforeach
    </tbody></table></div>
</div>
<div id="create-role-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div class="panel" style="width:420px;max-width:95vw;padding:1.5rem;">
        <h3 style="margin-top:0;">Create Role</h3>
        <form method="POST" action="{{ route('admin.roles.store') }}">@csrf
            <div class="form-group" style="margin-bottom:1rem;"><label class="form-label">Role Name</label><input type="text" name="name" class="form-control" placeholder="e.g. pharmacy_manager" required></div>
            <div class="form-group" style="margin-bottom:1.25rem;"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('create-role-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection