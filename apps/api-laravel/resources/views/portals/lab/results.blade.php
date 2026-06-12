@extends('layouts.portal')

@section('title', 'Lab Results')

@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(14,165,233,.15);border-color:rgba(14,165,233,.4);color:#38bdf8;">
    <i data-lucide="microscope" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
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

<div class="page-header">
    <div>
        <h1 class="page-title">Lab Results</h1>
        <p class="page-subtitle">View all resulted tests — filter by flag or patient.</p>
    </div>
</div>

<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;align-items:flex-end;">
    <div>
        <label class="form-label">Flag</label>
        <select name="flag" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="H" {{ request('flag') === 'H' ? 'selected' : '' }}>High</option>
            <option value="HH" {{ request('flag') === 'HH' ? 'selected' : '' }}>Critical High</option>
            <option value="L" {{ request('flag') === 'L' ? 'selected' : '' }}>Low</option>
            <option value="LL" {{ request('flag') === 'LL' ? 'selected' : '' }}>Critical Low</option>
            <option value="abnormal" {{ request('flag') === 'abnormal' ? 'selected' : '' }}>Abnormal</option>
            <option value="normal" {{ request('flag') === 'normal' ? 'selected' : '' }}>Normal</option>
        </select>
    </div>
    <div>
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Parameter or patient…" value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['flag','search']))
        <a href="{{ route('portals.lab.results') }}" class="btn btn-outline btn-sm">Clear</a>
    @endif
</form>

<div class="card" style="overflow:hidden;">
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Patient</th>
                    <th>Value</th>
                    <th>Reference</th>
                    <th>Flag</th>
                    <th>Resulted At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $result)
                <tr class="{{ $result->isAbnormal() ? 'bg-danger-subtle' : '' }}">
                    <td style="font-weight:600;">{{ $result->parameter_name }}</td>
                    <td style="font-size:.875rem;">{{ $result->patient?->full_name ?? '—' }}</td>
                    <td style="font-weight:700;{{ $result->isAbnormal() ? 'color:#b91c1c;' : '' }}">
                        {{ $result->value }} {{ $result->unit }}
                    </td>
                    <td style="font-size:.83rem;color:#64748b;">{{ $result->reference_range ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $result->isAbnormal() ? 'danger' : 'success' }}">
                            {{ $result->flagLabel() }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;color:#64748b;">{{ $result->resulted_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8;">No results found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:1rem;">{{ $results->links() }}</div>

@endsection
