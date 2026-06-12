@extends('layouts.admin')
@section('title', 'Roles Management')
@section('content')
<div class="admin-page">

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Roles Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
      <i data-lucide="plus"></i> Create Role
    </button>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead class="table-dark">
          <tr>
            <th>Role Name</th>
            <th>Description</th>
            <th>Portal</th>
            <th>User Count</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($roles as $role)
          <tr>
            <td>
              <strong>{{ $role->name }}</strong>
              @if($role->is_protected)
                <span class="badge bg-secondary ms-1">Protected</span>
              @endif
            </td>
            <td>{{ $role->description ?? '—' }}</td>
            <td>
              @php
                $portalColors = [
                  'patient_family' => 'info',
                  'clinical'       => 'success',
                  'admin'          => 'danger',
                  'finance'        => 'warning',
                  'connect'        => 'primary',
                  'public'         => 'secondary',
                ];
                $color = $portalColors[$role->portal] ?? 'secondary';
              @endphp
              <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $role->portal)) }}</span>
            </td>
            <td>{{ $role->users_count ?? 0 }}</td>
            <td class="text-end">
              <a href="{{ route('portals.admin.roles.users', $role) }}" class="btn btn-sm btn-outline-info me-1" title="View Users">
                <i data-lucide="users"></i>
              </a>
              <button class="btn btn-sm btn-outline-secondary me-1"
                data-bs-toggle="modal"
                data-bs-target="#editRoleModal"
                data-id="{{ $role->id }}"
                data-name="{{ $role->name }}"
                data-description="{{ $role->description }}"
                data-portal="{{ $role->portal }}"
                data-protected="{{ $role->is_protected ? '1' : '0' }}"
                title="Edit Role">
                <i data-lucide="pencil"></i>
              </button>
              @if(!$role->is_protected)
              <form method="POST" action="{{ route('portals.admin.roles.destroy', $role) }}" class="d-inline"
                onsubmit="return confirm('Delete role {{ addslashes($role->name) }}? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Role">
                  <i data-lucide="trash-2"></i>
                </button>
              </form>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-4">No roles found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $roles->links() }}
  </div>

  {{-- Create Role Modal --}}
  <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="{{ route('portals.admin.roles.store') }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="createRoleModalLabel">Create Role</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Role Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required placeholder="e.g. senior_nurse">
              <div class="form-text">Use lowercase letters, numbers, and underscores only.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Portal <span class="text-danger">*</span></label>
              <select name="portal" class="form-control" required>
                <option value="">-- Select Portal --</option>
                <option value="patient_family">Patient &amp; Family</option>
                <option value="clinical">Clinical</option>
                <option value="admin">Admin</option>
                <option value="finance">Finance</option>
                <option value="connect">Connect</option>
                <option value="public">Public</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Create Role</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Edit Role Modal --}}
  <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" id="editRoleForm" action="">
          @csrf
          @method('PUT')
          <div class="modal-header">
            <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Role Name</label>
              <input type="text" id="editRoleName" class="form-control" disabled>
              <div class="form-text" id="editRoleNameNote"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" id="editRoleDescription" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Portal <span class="text-danger">*</span></label>
              <select name="portal" id="editRolePortal" class="form-control" required>
                <option value="patient_family">Patient &amp; Family</option>
                <option value="clinical">Clinical</option>
                <option value="admin">Admin</option>
                <option value="finance">Finance</option>
                <option value="connect">Connect</option>
                <option value="public">Public</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    lucide.createIcons();

    const editModal = document.getElementById('editRoleModal');
    editModal.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      const id          = btn.getAttribute('data-id');
      const name        = btn.getAttribute('data-name');
      const description = btn.getAttribute('data-description');
      const portal      = btn.getAttribute('data-portal');
      const isProtected = btn.getAttribute('data-protected') === '1';

      document.getElementById('editRoleName').value        = name;
      document.getElementById('editRoleDescription').value = description;
      document.getElementById('editRolePortal').value      = portal;

      const portalSelect = document.getElementById('editRolePortal');
      const nameNote     = document.getElementById('editRoleNameNote');

      if (isProtected) {
        portalSelect.disabled = true;
        nameNote.textContent  = 'This is a protected role — name and portal cannot be changed.';
      } else {
        portalSelect.disabled = false;
        nameNote.textContent  = '';
      }

      const baseUrl = '{{ url("portals/admin/roles") }}';
      document.getElementById('editRoleForm').action = baseUrl + '/' + id;
    });
  });
</script>
@endpush
@endsection
