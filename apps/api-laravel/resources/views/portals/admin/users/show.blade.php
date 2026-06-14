@extends('layouts.portal')
@section('title', 'User: ' . $user->name)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Users')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.users.index') }}">Users</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $user->name }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="user"></i></div>
    <h2 class="entity-head__title">{{ $user->name }}</h2>
    @if(($user->status??'')==='active')<span class="badge badge-success">Active</span>
    @elseif(($user->status??'')==='suspended')<span class="badge badge-danger">Suspended</span>
    @else<span class="badge badge-warning">{{ ucfirst($user->status??'pending') }}</span>@endif
    <div class="entity-head__spacer"></div>
    @if(($user->status??'')==='suspended')
    <form method="POST" action="{{ route('portals.admin.users.activate', $user) }}" class="inline-form">@csrf
        <button class="btn btn-success"><i data-lucide="check-circle"></i> Activate</button>
    </form>
    @else
    <button type="button" class="btn btn-warning" onclick="opOpenModal('suspend-modal')"><i data-lucide="ban"></i> Suspend</button>
    @endif
    <button type="button" class="btn btn-danger" onclick="opOpenModal('delete-modal')"><i data-lucide="trash-2"></i> Delete</button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs">
    <span class="tab active">Overview</span>
</div>

<div class="field-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__label">Email</div>
        <div class="stat-card__value">{{ $user->email }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Role</div>
        <div class="stat-card__value">{{ $user->role?->name ?? 'no role' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Status</div>
        <div class="stat-card__value">{{ ucfirst($user->status ?? 'pending') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Joined</div>
        <div class="stat-card__value">{{ $user->created_at?->format('M d, Y') ?? '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Last login</div>
        <div class="stat-card__value">{{ isset($user->last_login_at) ? $user->last_login_at->diffForHumans() : 'Never' }}</div>
    </div>
</div>

<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="edit-2"></i> Edit Profile</h3></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.admin.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group mt-6">
                <label class="form-label form-label-required">Role</label>
                <select name="role_id" class="form-control" required>
                    <option value="">Select Role…</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id',$user->role_id)==$role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')<div class="form-hint">{{ $message }}</div>@enderror
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="key"></i> Reset Password</h3></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.admin.users.reset-password', $user) }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-warning"><i data-lucide="key"></i> Reset Password</button>
            </div>
        </form>
    </div>
</div>

<div id="suspend-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="suspend-modal-title">
        <h3 class="modal__title" id="suspend-modal-title"><i data-lucide="ban"></i> Suspend Account</h3>
        <form method="POST" action="{{ route('portals.admin.users.suspend', $user) }}">@csrf
            <div class="modal__body"><p>Suspend <strong>{{ $user->name }}</strong>? They will not be able to log in.</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('suspend-modal')">Cancel</button>
                <button type="submit" class="btn btn-warning"><i data-lucide="ban"></i> Suspend</button>
            </div>
        </form>
    </div>
</div>

<div id="delete-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <h3 class="modal__title" id="delete-modal-title"><i data-lucide="alert-triangle"></i> Delete Account</h3>
        <form method="POST" action="{{ route('portals.admin.users.destroy', $user) }}">@csrf @method('DELETE')
            <div class="modal__body"><p>Permanently delete <strong>{{ $user->name }}</strong>? This cannot be undone.</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-modal')">Cancel</button>
                <button type="submit" class="btn btn-danger"><i data-lucide="trash-2"></i> Delete</button>
            </div>
        </form>
    </div>
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
