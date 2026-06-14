@extends('layouts.portal')
@section('title', $facility->name)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Facilities')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.facilities.index') }}">Facilities</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $facility->name }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="building-2"></i></div>
    <h2 class="entity-head__title">{{ $facility->name }}</h2>
    @if(($facility->status ?? '') === 'active')<span class="badge badge-success">Active</span>
    @elseif(($facility->status ?? '') === 'suspended')<span class="badge badge-danger">Suspended</span>
    @elseif(($facility->status ?? '') === 'pending_approval')<span class="badge badge-warning">Pending</span>
    @else<span class="badge badge-neutral">{{ ucfirst($facility->status ?? 'pending') }}</span>@endif
    <div class="entity-head__spacer"></div>
    <a href="#edit" class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @if(($facility->status ?? '') === 'pending_approval' || ($facility->status ?? '') === 'suspended')
    <form method="POST" action="{{ route('admin.facilities.approve', $facility->id) }}" class="inline-form">@csrf
        <button type="submit" class="btn btn-success"><i data-lucide="check-circle"></i> Approve</button>
    </form>
    @endif
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs">
    <span class="tab active">Overview</span>
</div>

<div class="field-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__label">Type</div>
        <div class="stat-card__value">{{ ucfirst($facility->type ?? '—') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Status</div>
        <div class="stat-card__value">{{ ucfirst($facility->status ?? 'pending') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">License number</div>
        <div class="stat-card__value">{{ $facility->license_number ?? '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Region</div>
        <div class="stat-card__value">{{ $facility->region ?? '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Staff</div>
        <div class="stat-card__value">{{ number_format($staffCount ?? 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Patients</div>
        <div class="stat-card__value">{{ number_format($patientCount ?? 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Created</div>
        <div class="stat-card__value">{{ $facility->created_at?->format('d M Y') ?? '—' }}</div>
    </div>
</div>

<div class="panel mb-6" id="edit">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="pencil"></i> Edit facility</h3></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.facilities.update', $facility->id) }}">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $facility->name) }}" required>
                    @error('name')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Type</label>
                    <select name="type" class="form-control" required>
                        @foreach(['hospital','clinic','pharmacy','laboratory','radiology','specialist','other'] as $t)
                        <option value="{{ $t }}" @selected(old('type',$facility->type)===$t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Region</label>
                    <input type="text" name="region" class="form-control" value="{{ old('region', $facility->region) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Country code</label>
                    <input type="text" name="country_code" class="form-control" value="{{ old('country_code', $facility->country_code) }}" maxlength="3">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save changes</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="alert-triangle"></i> Danger zone</h3></div>
    <div class="panel-body">
        <div class="page-head">
            @if(($facility->status ?? '') !== 'suspended')
            <button type="button" class="btn btn-warning" onclick="opOpenModal('suspend-modal')"><i data-lucide="pause-circle"></i> Suspend facility</button>
            @endif
            <button type="button" class="btn btn-danger" onclick="opOpenModal('delete-modal')"><i data-lucide="trash-2"></i> Delete facility</button>
        </div>
    </div>
</div>

<div id="suspend-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="suspend-modal-title">
        <h3 class="modal__title" id="suspend-modal-title"><i data-lucide="pause-circle"></i> Suspend facility</h3>
        <form method="POST" action="{{ route('admin.facilities.suspend', $facility->id) }}">
            @csrf
            <div class="modal__body">
                <p>Suspend <strong>{{ $facility->name }}</strong>? Provide a reason for the audit trail.</p>
                <textarea name="reason" class="form-control" rows="3" placeholder="Reason for suspension" required></textarea>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('suspend-modal')">Cancel</button>
                <button type="submit" class="btn btn-warning">Suspend</button>
            </div>
        </form>
    </div>
</div>

<div id="delete-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <h3 class="modal__title" id="delete-modal-title"><i data-lucide="alert-triangle"></i> Delete facility</h3>
        <form method="POST" action="{{ route('admin.facilities.destroy', $facility->id) }}">
            @csrf @method('DELETE')
            <div class="modal__body">
                <p>Permanently delete <strong>{{ $facility->name }}</strong>? This cannot be undone. Provide a reason for the audit trail.</p>
                <textarea name="reason" class="form-control" rows="3" placeholder="Reason for deletion" required></textarea>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-modal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
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
