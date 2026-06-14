@extends('layouts.portal')

@section('title', 'Lab Results')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="microscope"></i>
    Laboratory
</div>
@endsection
@section('sidebar_user_role', 'Lab Technician')

@section('sidebar_nav')
@include('portals.lab._sidebar')
@endsection

@section('breadcrumb_home', 'Lab Portal')
@section('breadcrumb_home_url', route('portals.lab.dashboard'))
@section('breadcrumb_section', 'Results')

@section('content')

<div class="page-head">
    <h2>Lab results</h2>
    <p class="page-subtitle">View all resulted tests — filter by flag or patient.</p>
</div>

<form method="GET" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" placeholder="Parameter or patient…" value="{{ request('search') }}" aria-label="Search results">
    </label>
    <select name="flag" class="filter-select" aria-label="Flag" onchange="this.form.submit()">
        <option value="">All flags</option>
        <option value="H" {{ request('flag') === 'H' ? 'selected' : '' }}>High</option>
        <option value="HH" {{ request('flag') === 'HH' ? 'selected' : '' }}>Critical high</option>
        <option value="L" {{ request('flag') === 'L' ? 'selected' : '' }}>Low</option>
        <option value="LL" {{ request('flag') === 'LL' ? 'selected' : '' }}>Critical low</option>
        <option value="abnormal" {{ request('flag') === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
        <option value="normal" {{ request('flag') === 'normal' ? 'selected' : '' }}>Normal</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    @if(request()->hasAny(['flag','search']))
        <a href="{{ route('portals.lab.results') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Parameter</th><th>Patient</th><th>Value</th><th>Reference</th><th>Flag</th><th>Resulted at</th></tr>
            </thead>
            <tbody>
                @forelse($results as $result)
                <tr class="{{ $result->isAbnormal() ? 'row-emergency' : '' }}">
                    <td data-label="Parameter" class="td-strong">{{ $result->parameter_name }}</td>
                    <td data-label="Patient">{{ $result->patient?->full_name ?? '—' }}</td>
                    <td data-label="Value">
                        @if($result->isAbnormal())<span class="badge badge-danger">{{ $result->value }} {{ $result->unit }}</span>
                        @else<span class="td-strong">{{ $result->value }} {{ $result->unit }}</span>@endif
                    </td>
                    <td data-label="Reference" class="td-muted">{{ $result->reference_range ?? '—' }}</td>
                    <td data-label="Flag"><span class="badge badge-{{ $result->isAbnormal() ? 'danger' : 'success' }}">{{ $result->flagLabel() }}</span></td>
                    <td data-label="Resulted at" class="td-muted">{{ $result->resulted_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="td-muted empty-cell">No results found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $results->links() }}</div>
</div>

@endsection
