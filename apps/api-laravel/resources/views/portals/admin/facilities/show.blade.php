@extends('layouts.admin')
@section('title', $facility->name)
@section('content')
<div class="admin-page">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="{{ route('portals.admin.facilities.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-lucide="arrow-left"></i>
      </a>
      <h1 class="h3 mb-0">{{ $facility->name }}</h1>
      @if($facility->status === 'active')
        <span class="badge bg-success">Active</span>
      @elseif($facility->status === 'suspended')
        <span class="badge bg-danger">Suspended</span>
      @else
        <span class="badge bg-warning text-dark">Pending</span>
      @endif
    </div>
    <div class="d-flex gap-2">
      @if($facility->status === 'pending')
      <form method="POST" action="{{ route('portals.admin.facilities.approve', $facility) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-success btn-sm">
          <i data-lucide="check-circle"></i> Approve
        </button>
      </form>
      @endif
      @if($facility->status !== 'active')
      <form method="POST" action="{{ route('portals.admin.facilities.activate', $facility) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-outline-success btn-sm">
          <i data-lucide="play-circle"></i> Activate
        </button>
      </form>
      @endif
      @if($facility->status !== 'suspended')
      <form method="POST" action="{{ route('portals.admin.facilities.suspend', $facility) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-outline-warning btn-sm" onclick="return confirm('Suspend this facility?')">
          <i data-lucide="pause-circle"></i> Suspend
        </button>
      </form>
      @endif
      <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteFacilityModal">
        <i data-lucide="trash-2"></i> Delete
      </button>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card mb-4">
        <div class="card-header">
          <h6 class="mb-0"><i data-lucide="building-2"></i> Facility Info</h6>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-5 text-muted">Name</dt>
            <dd class="col-7">{{ $facility->name }}</dd>
            <dt class="col-5 text-muted">Code</dt>
            <dd class="col-7"><code>{{ $facility->code }}</code></dd>
            <dt class="col-5 text-muted">Type</dt>
            <dd class="col-7">{{ ucfirst($facility->type) }}</dd>
            <dt class="col-5 text-muted">Region</dt>
            <dd class="col-7">{{ $facility->region }}</dd>
            <dt class="col-5 text-muted">Country</dt>
            <dd class="col-7">{{ strtoupper($facility->country_code ?? '—') }}</dd>
            <dt class="col-5 text-muted">Status</dt>
            <dd class="col-7">
              @if($facility->status === 'active')
                <span class="badge bg-success">Active</span>
              @elseif($facility->status === 'suspended')
                <span class="badge bg-danger">Suspended</span>
              @else
                <span class="badge bg-warning text-dark">Pending</span>
              @endif
            </dd>
            <dt class="col-5 text-muted">Created</dt>
            <dd class="col-7">{{ $facility->created_at->format('d M Y') }}</dd>
          </dl>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-6">
          <div class="card text-center">
            <div class="card-body py-3">
              <div class="h4 mb-1 text-primary">{{ $facility->patients_count ?? 0 }}</div>
              <div class="small text-muted">Patients</div>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="card text-center">
            <div class="card-body py-3">
              <div class="h4 mb-1 text-info">{{ $facility->staff_count ?? 0 }}</div>
              <div class="small text-muted">Staff</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <h6 class="mb-0"><i data-lucide="edit"></i> Edit Facility</h6>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('portals.admin.facilities.update', $facility) }}">
            @csrf @method('PUT')
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $facility->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                  <option value="hospital" @selected(old('type', $facility->type) === 'hospital')>Hospital</option>
                  <option value="clinic" @selected(old('type', $facility->type) === 'clinic')>Clinic</option>
                  <option value="pharmacy" @selected(old('type', $facility->type) === 'pharmacy')>Pharmacy</option>
                  <option value="laboratory" @selected(old('type', $facility->type) === 'laboratory')>Laboratory</option>
                  <option value="other" @selected(old('type', $facility->type) === 'other')>Other</option>
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Region <span class="text-danger">*</span></label>
                <input type="text" name="region" class="form-control @error('region') is-invalid @enderror" value="{{ old('region', $facility->region) }}" required>
                @error('region')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Country Code <span class="text-muted">(max 3 chars)</span></label>
                <input type="text" name="country_code" class="form-control @error('country_code') is-invalid @enderror" value="{{ old('country_code', $facility->country_code) }}" maxlength="3">
                @error('country_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">
                  <i data-lucide="save"></i> Save Changes
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Modal -->
  <div class="modal fade" id="deleteFacilityModal" tabindex="-1" aria-labelledby="deleteFacilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteFacilityModalLabel">Delete Facility</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>{{ $facility->name }}</strong>? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="{{ route('portals.admin.facilities.destroy', $facility) }}">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
