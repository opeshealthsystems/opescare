@extends('layouts.admin')
@section('title', 'Patient — ' . $patient->health_id)
@section('content')
<div class="admin-page">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="{{ route('portals.admin.patients.index') }}" class="text-muted small"><i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Patients</a>
      <h1 class="h3 mb-0 mt-1">{{ $patient->full_name }}</h1>
    </div>
    <div>
      @if($patient->identity_status === 'active')
        <span class="badge bg-success fs-6">Active</span>
      @elseif($patient->identity_status === 'provisional')
        <span class="badge bg-warning text-dark fs-6">Provisional</span>
      @elseif($patient->identity_status === 'suspended')
        <span class="badge bg-danger fs-6">Suspended</span>
      @else
        <span class="badge bg-secondary fs-6">{{ ucfirst($patient->identity_status) }}</span>
      @endif
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">

      <div class="card mb-4">
        <div class="card-header"><i data-lucide="user" style="width:16px;height:16px;"></i> Patient Identity</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Health ID</dt>
            <dd class="col-sm-8"><code>{{ $patient->health_id }}</code></dd>
            <dt class="col-sm-4 text-muted">Full Name</dt>
            <dd class="col-sm-8">{{ $patient->full_name }}</dd>
            <dt class="col-sm-4 text-muted">Date of Birth</dt>
            <dd class="col-sm-8">{{ $patient->date_of_birth ? $patient->date_of_birth->format('d M Y') : '—' }}</dd>
            <dt class="col-sm-4 text-muted">Sex</dt>
            <dd class="col-sm-8">{{ ucfirst($patient->sex ?? '—') }}</dd>
            <dt class="col-sm-4 text-muted">Registered</dt>
            <dd class="col-sm-8">{{ $patient->created_at->format('d M Y, H:i') }}</dd>
          </dl>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header"><i data-lucide="phone" style="width:16px;height:16px;"></i> Contact Information</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Phone</dt>
            <dd class="col-sm-8">{{ $patient->phone ?? '—' }}</dd>
            <dt class="col-sm-4 text-muted">Email</dt>
            <dd class="col-sm-8">{{ $patient->email ?? '—' }}</dd>
            <dt class="col-sm-4 text-muted">Address</dt>
            <dd class="col-sm-8">{{ $patient->address ?? '—' }}</dd>
          </dl>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header"><i data-lucide="building-2" style="width:16px;height:16px;"></i> Facility</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Facility Name</dt>
            <dd class="col-sm-8">{{ $patient->facility->name ?? '—' }}</dd>
            <dt class="col-sm-4 text-muted">Facility Code</dt>
            <dd class="col-sm-8">{{ $patient->facility->code ?? '—' }}</dd>
            <dt class="col-sm-4 text-muted">Location</dt>
            <dd class="col-sm-8">{{ $patient->facility->location ?? '—' }}</dd>
          </dl>
        </div>
      </div>

    </div>

    <div class="col-lg-4">

      <div class="card mb-4">
        <div class="card-header"><i data-lucide="edit" style="width:16px;height:16px;"></i> Quick Edit</div>
        <div class="card-body">
          <form method="POST" action="{{ route('portals.admin.patients.update', $patient) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
              <label class="form-label small">Phone</label>
              <input type="text" name="phone" class="form-control form-control-sm @error('phone') is-invalid @enderror" value="{{ old('phone', $patient->phone) }}">
              @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label small">Email</label>
              <input type="email" name="email" class="form-control form-control-sm @error('email') is-invalid @enderror" value="{{ old('email', $patient->email) }}">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label small">Address</label>
              <textarea name="address" class="form-control form-control-sm @error('address') is-invalid @enderror" rows="2">{{ old('address', $patient->address) }}</textarea>
              @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label small">Facility</label>
              <select name="facility_id" class="form-select form-select-sm @error('facility_id') is-invalid @enderror">
                <option value="">— Select Facility —</option>
                @foreach($facilities as $facility)
                  <option value="{{ $facility->id }}" @selected(old('facility_id', $patient->facility_id) == $facility->id)>{{ $facility->name }}</option>
                @endforeach
              </select>
              @error('facility_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100">
              <i data-lucide="save" style="width:14px;height:14px;"></i> Save Changes
            </button>
          </form>
        </div>
      </div>

      <div class="card border-danger">
        <div class="card-header text-danger"><i data-lucide="alert-triangle" style="width:16px;height:16px;"></i> Danger Zone</div>
        <div class="card-body d-grid gap-2">
          @if($patient->identity_status !== 'suspended')
            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#suspendModal">
              <i data-lucide="pause-circle" style="width:14px;height:14px;"></i> Suspend Patient
            </button>
          @endif
          @if($patient->identity_status !== 'active')
            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#activateModal">
              <i data-lucide="check-circle" style="width:14px;height:14px;"></i> Activate Patient
            </button>
          @endif
          @if($patient->entered_in_error)
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
              <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Delete Record
            </button>
          @endif
        </div>
      </div>

    </div>
  </div>

  {{-- Suspend Modal --}}
  <div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="suspendModalLabel">Suspend Patient</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to suspend <strong>{{ $patient->full_name }}</strong>? They will no longer be able to access services.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="{{ route('portals.admin.patients.suspend', $patient) }}" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-warning btn-sm">Suspend</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Activate Modal --}}
  <div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="activateModalLabel">Activate Patient</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Activate <strong>{{ $patient->full_name }}</strong> and restore their access to services?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="{{ route('portals.admin.patients.activate', $patient) }}" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-success btn-sm">Activate</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Delete Modal --}}
  @if($patient->entered_in_error)
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger" id="deleteModalLabel">Delete Patient Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger">
            <strong>This action is irreversible.</strong> The record for <strong>{{ $patient->full_name }}</strong> ({{ $patient->health_id }}) will be permanently deleted because it was marked as entered in error.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="{{ route('portals.admin.patients.destroy', $patient) }}" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endif

</div>
@endsection
