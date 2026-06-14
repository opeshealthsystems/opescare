@extends('layouts.portal')
@section('title', 'All Users')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Users')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.users.index') }}">Users</a>
    <i data-lucide="chevron-right"></i>
    <span>Directory</span>
</div>

<div class="page-head">
    <h2>All Users</h2>
    <div class="page-head__spacer"></div>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email" aria-label="Search users">
    </label>
    <select name="role_id" class="filter-select" aria-label="Role" onchange="this.form.submit()">
        <option value="">All Roles</option>
        @foreach($roles as $role)<option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>@endforeach
    </select>
    <select name="status" class="filter-select" aria-label="Status" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Active</option>
        <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
        <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="users"></i> {{ $users->total() }} users</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td data-label="Name"><span class="td-strong">{{ $user->name }}</span></td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Role"><span class="badge badge-neutral">{{ $user->role?->name ?? 'none' }}</span></td>
                    <td data-label="Status">
                        @if($user->status==='active')<span class="badge badge-success">Active</span>
                        @elseif($user->status==='suspended')<span class="badge badge-danger">Suspended</span>
                        @else<span class="badge badge-warning">{{ ucfirst($user->status) }}</span>@endif
                    </td>
                    <td data-label="Created" class="td-muted">{{ $user->created_at?->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        @if($user->status!=='active')
                        <form method="POST" action="{{ route('admin.users.activate',$user->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Activate user" title="Activate"><i data-lucide="check-circle"></i></button>
                        </form>
                        @endif
                        @if($user->status!=='suspended')
                        <form method="POST" action="{{ route('admin.users.suspend',$user->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Suspend user" title="Suspend"><i data-lucide="ban"></i></button>
                        </form>
                        @endif
                        <button type="button" class="icon-btn" aria-label="Delete user" title="Delete" onclick="opOpenModal('delete-user-{{ $user->id }}')"><i data-lucide="trash-2"></i></button>
                        <div id="delete-user-{{ $user->id }}" class="modal-backdrop mt-6" hidden>
                            <div class="modal" role="dialog" aria-modal="true">
                                <h3 class="modal__title"><i data-lucide="alert-triangle"></i> Delete user</h3>
                                <form method="POST" action="{{ route('admin.users.destroy',$user->id) }}">@csrf @method('DELETE')
                                    <div class="modal__body"><p>Delete user <strong>{{ $user->name }}</strong>? This cannot be undone.</p></div>
                                    <div class="modal__footer">
                                        <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-user-{{ $user->id }}')">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="td-muted empty-cell">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $users->links() }}</div>
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
