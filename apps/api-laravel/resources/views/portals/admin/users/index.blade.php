@extends('layouts.portal')
@section('title', 'All Users')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Users')

@section('content')
<div class="page-header"><div>
    <h1 class="page-title">All Users</h1>
    <p class="page-subtitle">Manage every user account on the platform.</p>
</div></div>
@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif
<form method="GET" action="{{ route('admin.users.index') }}" class="panel" style="padding:1rem;margin-bottom:1rem;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;"><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email" class="form-control form-control-sm">
        </div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Role</label>
            <select name="role_id" class="form-control form-control-sm">
                <option value="">All Roles</option>
                @foreach($roles as $role)<option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>@endforeach
            </select>
        </div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Status</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">All</option>
                <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
                <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);"><span style="font-size:.85rem;color:var(--p-text-muted);">{{ $users->total() }} users</span></div>
    <div class="table-wrapper"><table class="data-table"><thead><tr>
        <th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th>
    </tr></thead><tbody>
    @forelse($users as $user)
    <tr>
        <td>{{ $user->name }}</td><td>{{ $user->email }}</td>
        <td><span style="font-size:.78rem;background:var(--p-surface-2);padding:.1rem .4rem;border-radius:3px;">{{ $user->role?->name ?? 'none' }}</span></td>
        <td>@if($user->status==='active')<span class="badge badge-success">Active</span>@elseif($user->status==='suspended')<span class="badge badge-danger">Suspended</span>@else<span class="badge badge-warning">{{ ucfirst($user->status) }}</span>@endif</td>
        <td style="font-size:.8rem;">{{ $user->created_at?->format('d M Y') }}</td>
        <td><div style="display:flex;gap:.35rem;">
            @if($user->status!=='active')<form method="POST" action="{{ route('admin.users.activate',$user->id) }}">@csrf<button class="btn btn-success btn-xs">Activate</button></form>@endif
            @if($user->status!=='suspended')<form method="POST" action="{{ route('admin.users.suspend',$user->id) }}">@csrf<button class="btn btn-warning btn-xs">Suspend</button></form>@endif
            <form method="POST" action="{{ route('admin.users.destroy',$user->id) }}" onsubmit="return confirm('Delete user?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form>
        </div></td>
    </tr>
    @empty<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No users found.</td></tr>@endforelse
    </tbody></table></div>
    <div style="padding:.75rem 1.25rem;">{{ $users->links() }}</div>
</div>
@endsection