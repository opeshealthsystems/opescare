@extends('layouts.admin')
@section('title', 'User: ' . $user->name)
@section('content')
<div class="admin-page">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">User Profile</h1>
    <a href="{{ route('portals.admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
      <i data-lucide="arrow-left"></i> Back to Users
    </a>
  </div>

  <div class="row g-4">
    <!-- Profile Card -->
    <div class="col-12 col-md-4">
      <div class="card">
        <div class="card-body text-center">
          <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-3" style="width:72px;height:72px;">
            <i data-lucide="user" style="width:36px;height:36px;color:#fff;"></i>
          </div>
          <h5 class="mb-1">{{ $user->name }}</h5>
          <p class="text-muted mb-2">{{ $user->email }}</p>
          <div class="d-flex justify-content-center gap-2 mb-3">
            <span class="badge bg-secondary">{{ $user->role->name ?? '—' }}</span>
            @if($user->status === 'active')
              <span class="badge bg-success">Active</span>
            @elseif($user->status === 'suspended')
              <span class="badge bg-danger">Suspended</span>
            @else
              <span class="badge bg-warning text-dark">Pending</span>
            @endif
          </div>
          <div class="text-start small text-muted">
            <div class="mb-1"><i data-lucide="calendar" style="width:14px;height:14px;"></i> Joined {{ $user->created_at->format('M d, Y') }}</div>
            <div><i data-lucide="clock" style="width:14px;height:14px;"></i> Last login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-8">
      <!-- Edit Form -->
      <div class="card mb-4">
        <div class="card-header fw-semibold"><i data-lucide="edit-2"></i> Edit Profile</div>
        <div class="card-body">
          <form method="POST" action="{{ route('portals.admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Role <span class="text-danger">*</span></label>
              <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                <option value="">Select Role...</option>
                @foreach($roles as $role)
                  <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
              </select>
              @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Changes</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Reset Password -->
      <div class="card mb-4">
        <div class="card-header fw-semibold"><i data-lucide="key"></i> Reset Password</div>
        <div class="card-body">
          <form method="POST" action="{{ route('portals.admin.users.reset-password', $user) }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">New Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-warning"><i data-lucide="key"></i> Reset Password</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="card border-danger">
        <div class="card-header fw-semibold text-danger"><i data-lucide="alert-triangle"></i> Danger Zone</div>
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2">
            @if($user->status === 'suspended')
              <form method="POST" action="{{ route('portals.admin.users.activate', $user) }}">
                @csrf
                <button type="submit" class="btn btn-success">
                  <i data-lucide="check-circle"></i> Activate Account
                </button>
              </form>
            @else
              <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#suspendModal">
                <i data-lucide="ban"></i> Suspend Account
              </button>
            @endif
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
              <i data-lucide="trash-2"></i> Delete Account
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Suspend Modal -->
  <div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="suspendModalLabel">Suspend User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to suspend <strong>{{ $user->name }}</strong>? They will not be able to log in.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="{{ route('portals.admin.users.suspend', $user) }}">
            @csrf
            <button type="submit" class="btn btn-warning"><i data-lucide="ban"></i> Suspend</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Delete User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Permanently delete <strong>{{ $user->name }}</strong>? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="{{ route('portals.admin.users.destroy', $user) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><i data-lucide="trash-2"></i> Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
