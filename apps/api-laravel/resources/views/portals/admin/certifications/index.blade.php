@extends('layouts.portal')
@section('title', 'Integration Certifications')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.certifications.index') }}">Certifications</a>
    <i data-lucide="chevron-right"></i>
    <span>Directory</span>
</div>

<div class="page-head">
    <h2>Integration certifications</h2>
    <div class="page-head__spacer"></div>
    <form method="POST" action="{{ route('portals.admin.certifications.seed') }}" class="inline-form">
        @csrf
        <button type="submit" class="btn btn-secondary btn-sm">
            <i data-lucide="list-checks"></i> Seed Requirements
        </button>
    </form>
    <a href="{{ route('portals.admin.certifications.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> New Certification
    </a>
</div>

<p class="td-muted mb-6">OpesCare interoperability &amp; security certification program.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- Stats --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="layers"></i><span class="stat-card__label">Total</span></div>
        <div class="stat-card__value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle-2"></i><span class="stat-card__label">Certified</span></div>
        <div class="stat-card__value">{{ $stats['passed'] }}</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="clock"></i><span class="stat-card__label">In progress</span></div>
        <div class="stat-card__value">{{ $stats['in_progress'] }}</div>
    </div>
    <div class="stat-card stat-card--teal">
        <div class="stat-card__head"><i data-lucide="award"></i><span class="stat-card__label">Active badges</span></div>
        <div class="stat-card__value">{{ $stats['badges'] }}</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
    <select name="status" class="filter-select" aria-label="Status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        @foreach($statuses as $s)
        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
        @endforeach
    </select>
    <select name="type" class="filter-select" aria-label="Type" onchange="this.form.submit()">
        <option value="">All Types</option>
        @foreach($types as $t)
        <option value="{{ $t }}" {{ $type === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
        @endforeach
    </select>
    @if($status || $type)
    <a href="{{ route('portals.admin.certifications.index') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

{{-- Table --}}
<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Integration</th>
                    <th>Type</th>
                    <th>Vendor</th>
                    <th>Status</th>
                    <th>Level</th>
                    <th>Last Test Run</th>
                    <th>Badge</th>
                    <th class="row-actions"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($certifications as $cert)
                <tr>
                    <td data-label="Integration" class="td-strong">
                        {{ $cert->integration_name }}
                        @if($cert->version)
                        <span class="td-muted">v{{ $cert->version }}</span>
                        @endif
                    </td>
                    <td data-label="Type">
                        <span class="badge badge--info badge-sm">{{ strtoupper($cert->integration_type) }}</span>
                    </td>
                    <td data-label="Vendor" class="td-muted">{{ $cert->vendor_name ?? '—' }}</td>
                    <td data-label="Status">
                        <span class="badge {{ $cert->statusBadgeClass() }} badge-sm">
                            {{ ucfirst(str_replace('_', ' ', $cert->status)) }}
                        </span>
                    </td>
                    <td data-label="Level">
                        @if($cert->certification_level)
                        <span class="badge {{ $cert->levelBadgeClass() }} badge-sm">{{ ucfirst($cert->certification_level) }}</span>
                        @else
                        <span class="td-muted">—</span>
                        @endif
                    </td>
                    <td data-label="Last Test Run">
                        @if($cert->latestTestRun)
                            <span class="badge {{ $cert->latestTestRun->isPassed() ? 'badge-success' : 'badge-danger' }} badge-sm">
                                {{ $cert->latestTestRun->passRate() }}%
                            </span>
                            <span class="td-muted code-muted">({{ $cert->latestTestRun->started_at?->format('d M Y') }})</span>
                        @else
                            <span class="td-muted">No runs</span>
                        @endif
                    </td>
                    <td data-label="Badge">
                        @if($cert->badge)
                            <span class="mono code-token">{{ $cert->badge->badge_code }}</span>
                        @else
                            <span class="td-muted">—</span>
                        @endif
                    </td>
                    <td class="row-actions">
                        <a href="{{ route('portals.admin.certifications.show', $cert) }}" class="btn btn-secondary btn-sm">Manage</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="td-muted empty-cell">
                        No certifications yet. <a href="{{ route('portals.admin.certifications.create') }}">Start the first one →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($certifications->hasPages())
        <div class="panel-footer">{{ $certifications->links() }}</div>
        @endif
    </div>
</div>

@endsection
