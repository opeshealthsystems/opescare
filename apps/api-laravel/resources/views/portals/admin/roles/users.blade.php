@extends('layouts.admin')
@section('title', 'Role Users — ' . $role->name)
@section('content')
<div class="admin-page">

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="{{ route('portals.admin.roles.index') }}" class="btn btn-sm btn-outline-secondary me-2">
        <i data-lucide="arrow-left"></i> Back to Roles
      </a>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
          <i data-lucide="shield" style="color:#fff;"></i>
        </div>
        <div>
          <h4 class="mb-0">{{ $role->name }}
            @if($role->is_protected)
              <span class="badge bg-secondary ms-1">Protected</span>
            @endif
          </h4>
          <p class="text-muted mb-0">{{ $role->description ?? 'No description provided.' }}</p>
        </div>
        <div class="ms-auto text-end">
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
          <span class="badge bg-{{ $color }} fs-6">{{ ucfirst(str_replace('_', ' ', $role->portal)) }}</span>
          <div class="text-muted small mt-1">{{ $users->total() }} user(s)</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0">Users with this Role</h6>
    </div>
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead class="table-dark">
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Facility</th>
            <th>Status</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;">
                  <span class="text-white small fw-bold">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                </div>
                <span>{{ $user->name ?? '—' }}</span>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->facility->name ?? '—' }}</td>
            <td>
              @if($user->is_active ?? true)
                <span class="badge bg-success">Active</span>
              @else
                <span class="badge bg-secondary">Inactive</span>
              @endif
            </td>
            <td>{{ $user->created_at?->format('M d, Y') ?? '—' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-4">
              <i data-lucide="users" style="opacity:.4;"></i>
              <div class="mt-2">No users assigned to this role.</div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $users->links() }}
  </div>

</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    lucide.createIcons();
  });
</script>
@endpush
@endsection
