@extends('layouts.portal')
@section('title', 'Code System Mappings')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Code Mappings')

@section('content')

<div class="page-head">
    <h2><i data-lucide="git-merge"></i> Code System Mappings</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.code_mappings.create') }}" class="btn btn-primary btn-sm"><i data-lucide="plus"></i> Add Mapping</a>
</div>
<p class="td-muted mb-6">LOINC · ICD-10 · ATC — terminology mapping catalog</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- Stats --}}
<div class="stat-grid mb-6">
    <div class="stat-card"><div class="stat-card__value">{{ $stats['total'] }}</div><div class="stat-card__label">Total</div></div>
    <div class="stat-card stat-card--success"><div class="stat-card__value">{{ $stats['approved'] }}</div><div class="stat-card__label">Approved</div></div>
    <div class="stat-card stat-card--warning"><div class="stat-card__value">{{ $stats['pending'] }}</div><div class="stat-card__label">Pending</div></div>
    <div class="stat-card"><div class="stat-card__value">{{ $stats['loinc'] }}</div><div class="stat-card__label">LOINC</div></div>
    <div class="stat-card"><div class="stat-card__value">{{ $stats['icd10'] }}</div><div class="stat-card__label">ICD-10</div></div>
    <div class="stat-card"><div class="stat-card__value">{{ $stats['atc'] }}</div><div class="stat-card__label">ATC</div></div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="local code, name, standard code…" aria-label="Search mappings">
    </label>
    <select name="system" class="filter-select" aria-label="System">
        <option value="">All systems</option>
        @foreach($systems as $sys)
        <option value="{{ $sys }}" {{ $system === $sys ? 'selected' : '' }}>{{ strtoupper($sys) }}</option>
        @endforeach
    </select>
    <select name="status" class="filter-select" aria-label="Status">
        <option value="">All statuses</option>
        @foreach($statuses as $s)
        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <select name="resource_type" class="filter-select" aria-label="Type">
        <option value="">All types</option>
        @foreach($resourceTypes as $rt)
        <option value="{{ $rt }}" {{ $resourceType === $rt ? 'selected' : '' }}>{{ $rt }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.code_mappings.index') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

{{-- Table --}}
<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Local Code</th>
                    <th>Local Name</th>
                    <th>System</th>
                    <th>Standard Code</th>
                    <th>Standard Display</th>
                    <th>Type</th>
                    <th>Confidence</th>
                    <th>Status</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mappings as $mapping)
                <tr>
                    <td data-label="Local Code"><span class="mono td-strong">{{ $mapping->local_code }}</span></td>
                    <td data-label="Local Name" title="{{ $mapping->local_name }}">{{ $mapping->local_name ?? '—' }}</td>
                    <td data-label="System"><span class="badge badge-primary">{{ strtoupper($mapping->standard_system) }}</span></td>
                    <td data-label="Standard Code"><span class="mono">{{ $mapping->standard_code }}</span></td>
                    <td data-label="Standard Display" title="{{ $mapping->standard_display }}">{{ $mapping->standard_display ?? '—' }}</td>
                    <td data-label="Type">{{ $mapping->resource_type }}</td>
                    <td data-label="Confidence">{{ ucfirst($mapping->mapping_confidence) }}</td>
                    <td data-label="Status"><span class="badge {{ $mapping->statusBadgeClass() }}">{{ ucfirst($mapping->status) }}</span></td>
                    <td class="row-actions" data-label="Actions">
                        @if($mapping->isPending())
                        <form method="POST" action="{{ route('portals.admin.code_mappings.approve', $mapping) }}" class="inline-form">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm"><i data-lucide="check"></i> Approve</button>
                        </form>
                        <form method="POST" action="{{ route('portals.admin.code_mappings.reject', $mapping) }}" class="inline-form">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm"><i data-lucide="x"></i> Reject</button>
                        </form>
                        @endif
                        <button type="button" class="btn btn-ghost btn-sm" onclick="opOpenModal('delete-{{ $mapping->id }}')">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="td-muted empty-cell">No mappings found. <a href="{{ route('portals.admin.code_mappings.create') }}">Add the first mapping →</a></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($mappings->hasPages())
    <div class="panel-body">{{ $mappings->links() }}</div>
    @endif
</div>

{{-- Delete confirm modals --}}
@foreach($mappings as $mapping)
<div id="delete-{{ $mapping->id }}" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-{{ $mapping->id }}-title">
        <h3 class="modal__title" id="delete-{{ $mapping->id }}-title"><i data-lucide="trash-2"></i> Delete mapping</h3>
        <form method="POST" action="{{ route('portals.admin.code_mappings.destroy', $mapping) }}">
            @csrf @method('DELETE')
            <div class="modal__body"><p>Delete mapping <strong>{{ $mapping->local_code }}</strong>? This cannot be undone.</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-{{ $mapping->id }}')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endforeach

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
