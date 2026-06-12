@extends('layouts.portal')
@section('title', 'User: ' . $user->name)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Users')
@section('content')

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
        <a href="{{ route('admin.users.index') }}" style="font-size:.82rem;color:var(--p-text-muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.4rem;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Users
        </a>
        <h1 class="page-title">{{ $user->name }}</h1>
        <p class="page-subtitle">{{ $user->email }}</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        @if(($user->status??'')==='suspended')
        <form method="POST" action="{{ route('portals.admin.users.activate', $user) }}">@csrf
            <button class="btn btn-success btn-sm"><i data-lucide="check-circle"></i> Activate</button>
        </form>
        @else
        <button onclick="document.getElementById('suspend-modal').style.display='flex'" class="btn btn-warning btn-sm">
            <i data-lucide="ban"></i> Suspend
        </button>
        @endif
        <button onclick="document.getElementById('delete-modal').style.display='flex'" class="btn btn-danger btn-sm">
            <i data-lucide="trash-2"></i> Delete
        </button>
    </div>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem;align-items:start;">
    <div>
        <div class="panel" style="margin-bottom:1rem;text-align:center;padding:2rem 1.25rem;">
            <div style="width:72px;height:72px;border-radius:50%;background:var(--p-surface-2);border:2px solid var(--p-border);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                <i data-lucide="user" style="width:32px;height:32px;color:var(--p-text-muted);"></i>
            </div>
            <div style="font-weight:700;font-size:1.05rem;">{{ $user->name }}</div>
            <div style="color:var(--p-text-muted);font-size:.85rem;margin:.25rem 0 .75rem;">{{ $user->email }}</div>
            <div style="display:flex;justify-content:center;gap:.5rem;">
                <span class="badge badge-neutral">{{ $user->role?->name ?? 'no role' }}</span>
                @if(($user->status??'')==='active')<span class="badge badge-success">Active</span>
                @elseif(($user->status??'')==='suspended')<span class="badge badge-danger">Suspended</span>
                @else<span class="badge badge-warning">{{ ucfirst($user->status??'pending') }}</span>@endif
            </div>
            <div style="margin-top:1rem;font-size:.8rem;color:var(--p-text-muted);text-align:left;">
                <div style="margin-bottom:.3rem;"><i data-lucide="calendar" style="width:13px;height:13px;vertical-align:middle;"></i> Joined {{ $user->created_at?->format('M d, Y') }}</div>
                <div><i data-lucide="clock" style="width:13px;height:13px;vertical-align:middle;"></i> Last login: {{ isset($user->last_login_at) ? $user->last_login_at->diffForHumans() : 'Never' }}</div>
            </div>
        </div>
    </div>

    <div>
        <div class="panel" style="margin-bottom:1.5rem;">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="edit-2"></i> Edit Profile</h2></div>
            <div style="padding:1.25rem;">
                <form method="POST" action="{{ route('portals.admin.users.update', $user) }}">
                    @csrf @method('PUT')
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                        <div class="form-group">
                            <label class="form-label">Name <span style="color:var(--p-danger);">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div style="font-size:.78rem;color:var(--p-danger);">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span style="color:var(--p-danger);">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div style="font-size:.78rem;color:var(--p-danger);">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label class="form-label">Role <span style="color:var(--p-danger);">*</span></label>
                        <select name="role_id" class="form-control" required>
                            <option value="">Select Role…</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id',$user->role_id)==$role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<div style="font-size:.78rem;color:var(--p-danger);">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Changes</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="key"></i> Reset Password</h2></div>
            <div style="padding:1.25rem;">
                <form method="POST" action="{{ route('portals.admin.users.reset-password', $user) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                        <div class="form-group">
                            <label class="form-label">New Password <span style="color:var(--p-danger);">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password')<div style="font-size:.78rem;color:var(--p-danger);">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password <span style="color:var(--p-danger);">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning"><i data-lucide="key"></i> Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Suspend Modal --}}
<div id="suspend-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:440px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;">Suspend Account</h3>
            <button onclick="document.getElementById('suspend-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <div style="padding:1.5rem;">
            <p>Suspend <strong>{{ $user->name }}</strong>? They will not be able to log in.</p>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button onclick="document.getElementById('suspend-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <form method="POST" action="{{ route('portals.admin.users.suspend', $user) }}">@csrf
                    <button type="submit" class="btn btn-warning"><i data-lucide="ban"></i> Suspend</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div id="delete-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:440px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--p-danger);">Delete Account</h3>
            <button onclick="document.getElementById('delete-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <div style="padding:1.5rem;">
            <p>Permanently delete <strong>{{ $user->name }}</strong>? This cannot be undone.</p>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button onclick="document.getElementById('delete-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <form method="POST" action="{{ route('portals.admin.users.destroy', $user) }}">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i data-lucide="trash-2"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('[id$="-modal"]').forEach(m=>m.style.display='none');}});</script>
@endsection
