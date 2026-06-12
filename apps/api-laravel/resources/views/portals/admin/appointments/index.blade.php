@extends('layouts.admin')
@section('title', 'Appointments')
@section('content')
<div class="admin-page">

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Appointments</h1>
  </div>

  {{-- Stats Row --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card text-center">
        <div class="card-body py-3">
          <div class="fs-4 fw-bold">{{ $stats['total'] ?? 0 }}</div>
          <div class="text-muted small">Today Total</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center border-success">
        <div class="card-body py-3">
          <div class="fs-4 fw-bold text-success">{{ $stats['confirmed'] ?? 0 }}</div>
          <div class="text-muted small">Confirmed</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center border-danger">
        <div class="card-body py-3">
          <div class="fs-4 fw-bold text-danger">{{ $stats['cancelled'] ?? 0 }}</div>
          <div class="text-muted small">Cancelled</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center border-warning">
        <div class="card-body py-3">
          <div class="fs-4 fw-bold text-warning">{{ $stats['no_show'] ?? 0 }}</div>
          <div class="text-muted small">No-show</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('portals.admin.appointments.index') }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label form-label-sm mb-1">Patient Health ID</label>
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Health ID..." value="{{ request('search') }}">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm mb-1">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
            <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            <option value="no_show" @selected(request('status') === 'no_show')>No-show</option>
            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm mb-1">Facility</label>
          <select name="facility_id" class="form-select form-select-sm">
            <option value="">All Facilities</option>
            @foreach($facilities ?? [] as $facility)
              <option value="{{ $facility->id }}" @selected(request('facility_id') == $facility->id)>{{ $facility->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm mb-1">Date From</label>
          <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm mb-1">Date To</label>
          <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
        </div>
        <div class="col-12 col-md-1 d-flex gap-1">
          <button type="submit" class="btn btn-primary btn-sm w-100">
            <i data-lucide="search" style="width:14px;height:14px;"></i>
          </button>
          <a href="{{ route('portals.admin.appointments.index') }}" class="btn btn-outline-secondary btn-sm w-100">
            <i data-lucide="x" style="width:14px;height:14px;"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Patient</th>
              <th>Facility</th>
              <th>Type</th>
              <th>Scheduled At</th>
              <th>Status</th>
              <th>Provider</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($appointments ?? [] as $appointment)
            <tr>
              <td>
                <div class="fw-semibold">{{ $appointment->patient->full_name ?? '—' }}</div>
                <div class="text-muted small">{{ $appointment->patient->health_id ?? '' }}</div>
              </td>
              <td>{{ $appointment->facility->name ?? '—' }}</td>
              <td>{{ ucfirst(str_replace('_', ' ', $appointment->type ?? '—')) }}</td>
              <td>
                <div>{{ $appointment->scheduled_at ? $appointment->scheduled_at->format('M d, Y') : '—' }}</div>
                <div class="text-muted small">{{ $appointment->scheduled_at ? $appointment->scheduled_at->format('H:i') : '' }}</div>
              </td>
              <td>
                @php
                  $statusMap = [
                    'scheduled'  => 'secondary',
                    'confirmed'  => 'success',
                    'cancelled'  => 'danger',
                    'no_show'    => 'warning',
                    'completed'  => 'info',
                  ];
                  $badgeColor = $statusMap[$appointment->status ?? ''] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $badgeColor }}">{{ ucfirst(str_replace('_', ' ', $appointment->status ?? '—')) }}</span>
              </td>
              <td>{{ $appointment->provider->full_name ?? '—' }}</td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('portals.admin.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary" title="View">
                    <i data-lucide="eye" style="width:14px;height:14px;"></i>
                  </a>
                  @if(!in_array($appointment->status, ['cancelled', 'completed']))
                  <button type="button" class="btn btn-sm btn-outline-danger" title="Cancel"
                    data-bs-toggle="modal"
                    data-bs-target="#cancelModal"
                    data-id="{{ $appointment->id }}"
                    data-url="{{ route('portals.admin.appointments.cancel', $appointment) }}">
                    <i data-lucide="ban" style="width:14px;height:14px;"></i>
                  </button>
                  @endif
                  <form method="POST" action="{{ route('portals.admin.appointments.destroy', $appointment) }}" onsubmit="return confirm('Delete this appointment?')" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Delete">
                      <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <i data-lucide="calendar-off" style="width:32px;height:32px;opacity:.4;"></i>
                <div class="mt-2">No appointments found.</div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if(isset($appointments) && $appointments->hasPages())
    <div class="card-footer d-flex justify-content-end">
      {{ $appointments->withQueryString()->links() }}
    </div>
    @endif
  </div>

</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="cancelForm">
        @csrf
        @method('PATCH')
        <div class="modal-header">
          <h5 class="modal-title" id="cancelModalLabel">
            <i data-lucide="ban" style="width:18px;height:18px;" class="me-1"></i> Cancel Appointment
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="cancelReason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
            <textarea name="reason" id="cancelReason" class="form-control" rows="3" placeholder="Enter reason for cancellation..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-danger">
            <i data-lucide="ban" style="width:14px;height:14px;" class="me-1"></i> Confirm Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const cancelModal = document.getElementById('cancelModal');
    if (cancelModal) {
      cancelModal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const url = btn.getAttribute('data-url');
        document.getElementById('cancelForm').setAttribute('action', url);
        document.getElementById('cancelReason').value = '';
      });
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
</script>
@endpush
@endsection
