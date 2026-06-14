@extends('layouts.portal')
@section('title', 'Patient — ' . $patient->health_id)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Patients')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.patients.index') }}">Patients</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $patient->health_id }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="user"></i></div>
    <h2 class="entity-head__title">{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</h2>
    @if(($patient->identity_status??'')==='active')<span class="badge badge-success">Active</span>
    @elseif(($patient->identity_status??'')==='suspended')<span class="badge badge-danger">Suspended</span>
    @else<span class="badge badge-warning">{{ ucfirst($patient->identity_status??'provisional') }}</span>@endif
    <div class="entity-head__spacer"></div>
    @if(($patient->identity_status??'')==='active')
    <button type="button" class="btn btn-warning" onclick="opOpenModal('suspend-modal')"><i data-lucide="pause-circle"></i> Suspend</button>
    @else
    <button type="button" class="btn btn-success" onclick="opOpenModal('activate-modal')"><i data-lucide="check-circle"></i> Activate</button>
    @endif
    @if($patient->entered_in_error ?? false)
    <button type="button" class="btn btn-danger" onclick="opOpenModal('delete-modal')"><i data-lucide="trash-2"></i> Delete</button>
    @endif
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs">
    <span class="tab active">Overview</span>
</div>

<div class="field-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__label">Health ID</div>
        <div class="stat-card__value td-mono">{{ $patient->health_id }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Full Name</div>
        <div class="stat-card__value">{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Date of Birth</div>
        <div class="stat-card__value">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d M Y') : '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Sex</div>
        <div class="stat-card__value">{{ ucfirst($patient->sex ?? '—') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Status</div>
        <div class="stat-card__value">{{ ucfirst($patient->identity_status ?? 'provisional') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Registered</div>
        <div class="stat-card__value">{{ $patient->created_at?->format('d M Y, H:i') }}</div>
    </div>
</div>

<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="phone"></i> Contact</h3></div>
    <div class="panel-body">
        <div class="field-grid">
            <div class="stat-card">
                <div class="stat-card__label">Phone</div>
                <div class="stat-card__value">{{ $patient->phone ?? $patient->phone_number ?? '—' }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Email</div>
                <div class="stat-card__value">{{ $patient->email ?? '—' }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Address</div>
                <div class="stat-card__value">{{ $patient->address ?? '—' }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Facility</div>
                <div class="stat-card__value">{{ $patient->facility?->name ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="edit"></i> Quick Edit</h3></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.admin.patients.update', $patient) }}">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone ?? $patient->phone_number ?? '') }}">
                    @error('phone')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email ?? '') }}">
                    @error('email')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group mt-6">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $patient->address ?? '') }}</textarea>
            </div>
            <div class="form-group mt-6">
                <label class="form-label">Facility</label>
                <select name="facility_id" class="form-control">
                    <option value="">— Select Facility —</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @selected(old('facility_id',$patient->facility_id)==$facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="suspend-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="suspend-modal-title">
        <h3 class="modal__title" id="suspend-modal-title"><i data-lucide="pause-circle"></i> Suspend Patient</h3>
        <form method="POST" action="{{ route('portals.admin.patients.suspend', $patient) }}">@csrf @method('PATCH')
            <div class="modal__body"><p>Suspend <strong>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</strong>? They will no longer be able to access services.</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('suspend-modal')">Cancel</button>
                <button type="submit" class="btn btn-warning">Suspend</button>
            </div>
        </form>
    </div>
</div>

<div id="activate-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="activate-modal-title">
        <h3 class="modal__title" id="activate-modal-title"><i data-lucide="check-circle"></i> Activate Patient</h3>
        <form method="POST" action="{{ route('portals.admin.patients.activate', $patient) }}">@csrf @method('PATCH')
            <div class="modal__body"><p>Activate <strong>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</strong> and restore their access?</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('activate-modal')">Cancel</button>
                <button type="submit" class="btn btn-success">Activate</button>
            </div>
        </form>
    </div>
</div>

@if($patient->entered_in_error ?? false)
<div id="delete-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <h3 class="modal__title" id="delete-modal-title"><i data-lucide="alert-triangle"></i> Delete Patient Record</h3>
        <form method="POST" action="{{ route('portals.admin.patients.destroy', $patient) }}">@csrf @method('DELETE')
            <div class="modal__body">
                <div class="alert alert-danger mb-6"><i data-lucide="alert-triangle"></i><div><strong>Irreversible.</strong> The record for <strong>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</strong> ({{ $patient->health_id }}) will be permanently deleted.</div></div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-modal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete Permanently</button>
            </div>
        </form>
    </div>
</div>
@endif

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
