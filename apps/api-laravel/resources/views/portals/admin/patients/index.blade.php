@extends('layouts.portal')
@section('title', 'All Patients')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Patients')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.patients.index') }}">Patients</a>
    <i data-lucide="chevron-right"></i>
    <span>Registry</span>
</div>

<div class="page-head">
    <h2>All Patients</h2>
    <div class="page-head__spacer"></div>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<form method="GET" action="{{ route('admin.patients.index') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Health ID, or phone" aria-label="Search patients">
    </label>
    <select name="identity_status" class="filter-select" aria-label="Identity status" onchange="this.form.submit()">
        <option value="">All</option>
        @foreach(['provisional','verified','flagged','deceased'] as $s)<option value="{{ $s }}" {{ request('identity_status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('admin.patients.index') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="users"></i> {{ $patients->total() }} patients</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Health ID</th>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>Sex</th>
                    <th>Identity</th>
                    <th>Created</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($patients as $patient)
                @php $ist=$patient->identity_status??'provisional'; @endphp
                <tr>
                    <td data-label="Health ID"><span class="td-mono">{{ $patient->health_id }}</span></td>
                    <td data-label="Name"><span class="td-strong">{{ $patient->first_name }} {{ $patient->last_name }}</span></td>
                    <td data-label="DOB" class="td-muted">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d M Y') : '—' }}</td>
                    <td data-label="Sex">{{ ucfirst($patient->sex??'') }}</td>
                    <td data-label="Identity">
                        @if($ist==='verified')<span class="badge badge-success">Verified</span>
                        @elseif($ist==='flagged')<span class="badge badge-danger">Flagged</span>
                        @elseif($ist==='deceased')<span class="badge badge-neutral">Deceased</span>
                        @else<span class="badge badge-warning">Provisional</span>@endif
                    </td>
                    <td data-label="Created" class="td-muted">{{ $patient->created_at?->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        @if($ist!=='verified')
                        <form method="POST" action="{{ route('admin.patients.activate',$patient->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Verify patient" title="Verify"><i data-lucide="check-circle"></i></button>
                        </form>
                        @endif
                        @if($ist!=='flagged')
                        <form method="POST" action="{{ route('admin.patients.suspend',$patient->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Flag patient" title="Flag"><i data-lucide="flag"></i></button>
                        </form>
                        @endif
                        <button type="button" class="icon-btn" aria-label="Delete patient" title="Delete" onclick="opOpenModal('delete-patient-{{ $patient->id }}')"><i data-lucide="trash-2"></i></button>
                        <div id="delete-patient-{{ $patient->id }}" class="modal-backdrop mt-6" hidden>
                            <div class="modal" role="dialog" aria-modal="true">
                                <h3 class="modal__title"><i data-lucide="alert-triangle"></i> Delete patient</h3>
                                <form method="POST" action="{{ route('admin.patients.destroy',$patient->id) }}">@csrf @method('DELETE')
                                    <div class="modal__body"><p>Delete patient <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>? This cannot be undone.</p></div>
                                    <div class="modal__footer">
                                        <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-patient-{{ $patient->id }}')">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="td-muted empty-cell">No patients found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $patients->links() }}</div>
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
