@extends('layouts.admin')
@section('title', 'Pending Approvals')
@section('content')
<div class="admin-page">

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Pending Approvals</h1>
    <a href="{{ route('portals.admin.organizations.index') }}" class="btn btn-sm btn-outline-secondary">
      <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> All Organizations
    </a>
  </div>

  @if($organizations->isEmpty())
  <div class="card">
    <div class="card-body text-center text-muted py-5">
      <i data-lucide="check-circle" style="width:48px;height:48px;" class="text-success mb-3"></i>
      <p class="mb-0">No organizations pending approval.</p>
    </div>
  </div>
  @else
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Type</th>
              <th>Region</th>
              <th>Status</th>
              <th>Applied</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($organizations as $org)
            <tr>
              <td>
                <div class="fw-semibold">{{ $org->name }}</div>
                @if($org->email)
                <div class="text-muted small">{{ $org->email }}</div>
                @endif
              </td>
              <td>
                @php
                  $typeColors = ['hospital'=>'info','clinic'=>'success','pharmacy'=>'warning','lab'=>'secondary'];
                  $color = $typeColors[$org->type] ?? 'dark';
                @endphp
                <span class="badge bg-{{ $color }}">{{ ucfirst($org->type) }}</span>
              </td>
              <td>{{ $org->region ?? '—' }}</td>
              <td>
                @if($org->status === 'submitted')
                  <span class="badge bg-info">Submitted</span>
                @else
                  <span class="badge bg-warning text-dark">Pending</span>
                @endif
              </td>
              <td class="text-nowrap">{{ $org->created_at->format('d M Y') }}</td>
              <td class="text-end">
                <a href="{{ route('portals.admin.organizations.show', $org) }}" class="btn btn-sm btn-outline-primary me-1" title="View Details">
                  <i data-lucide="eye" style="width:14px;height:14px;"></i>
                </a>
                <form method="POST" action="{{ route('portals.admin.organizations.approve', $org) }}" class="d-inline">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn btn-sm btn-success me-1" title="Approve">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Approve
                  </button>
                </form>
                <button type="button" class="btn btn-sm btn-warning" title="Reject"
                  data-bs-toggle="modal" data-bs-target="#rejectModal"
                  data-org-id="{{ $org->id }}" data-org-name="{{ $org->name }}">
                  <i data-lucide="x" style="width:14px;height:14px;"></i> Reject
                </button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @if($organizations->hasPages())
    <div class="card-footer d-flex justify-content-end">
      {{ $organizations->links() }}
    </div>
    @endif
  </div>
  @endif

  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" id="rejectForm" action="">
        @csrf
        @method('PATCH')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="rejectModalLabel">Reject Organization</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Rejecting: <strong id="rejectOrgName"></strong></p>
            <div class="mb-3">
              <label class="form-label fw-semibold">Reason for rejection <span class="text-danger">*</span></label>
              <textarea name="reason" class="form-control" rows="4" placeholder="Provide a clear reason for rejection..." required></textarea>
              <div class="form-text">This reason may be communicated to the applicant.</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning">
              <i data-lucide="x-circle" style="width:14px;height:14px;"></i> Confirm Rejection
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  lucide.createIcons();
  document.getElementById('rejectModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('rejectOrgName').textContent = btn.dataset.orgName;
    document.getElementById('rejectForm').action = '/admin/organizations/' + btn.dataset.orgId + '/reject';
  });
</script>
@endpush
