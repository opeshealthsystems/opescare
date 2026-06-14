@extends('layouts.portal')
@section('title', 'Facility Onboarding')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.onboarding') }}">Onboarding</a>
    <i data-lucide="chevron-right"></i>
    <span>Facilities</span>
</div>

<div class="page-head">
    <h2>Facility onboarding &amp; go-live</h2>
</div>
<p class="td-muted mb-6">Track onboarding progress and approve facilities for live operations.</p>

{{-- KPI Strip --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="building-2"></i><span class="stat-card__label">Facilities</span></div>
        <div class="stat-card__value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle-2"></i><span class="stat-card__label">Approved</span></div>
        <div class="stat-card__value">{{ $stats['approved'] }}</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="clock"></i><span class="stat-card__label">Ready to approve</span></div>
        <div class="stat-card__value">{{ $stats['ready_for_approval'] }}</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="alert-circle"></i><span class="stat-card__label">In progress</span></div>
        <div class="stat-card__value">{{ $stats['pending'] }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="rocket"></i> All facilities</h3>
    </div>
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Facility</th>
                    <th>Type</th>
                    <th>Checklist Progress</th>
                    <th>Status</th>
                    <th>Approved</th>
                    <th class="row-actions"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($facilities as $facility)
                    @php
                        $r = $readinessMap[$facility->id];
                        $checklist = $r->checklist_json ?? [];
                        $done = collect($checklist)->filter(fn($v) => $v === true)->count();
                        $total = count($checklist);
                        $pct = $total > 0 ? round($done / $total * 100) : 0;
                        $statusColor = match($r->status) {
                            'approved' => 'success',
                            'ready_for_approval' => 'info',
                            default => 'warning',
                        };
                    @endphp
                    <tr>
                        <td data-label="Facility" class="td-strong">{{ $facility->name }}</td>
                        <td data-label="Type">{{ ucfirst($facility->type ?? '—') }}</td>
                        <td data-label="Checklist Progress">
                            <div class="breakdown__row breakdown__row--2col">
                                <div class="breakdown__track">
                                    <div class="breakdown__fill {{ $pct === 100 ? 'breakdown__fill--teal' : '' }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="td-muted">{{ $done }}/{{ $total }}</span>
                            </div>
                        </td>
                        <td data-label="Status">
                            <span class="badge badge--{{ $statusColor }} badge-sm">{{ str_replace('_', ' ', ucfirst($r->status)) }}</span>
                        </td>
                        <td data-label="Approved" class="td-muted">{{ $r->approved_at?->format('d M Y') ?? '—' }}</td>
                        <td class="row-actions">
                            <a href="{{ route('portals.admin.onboarding.show', $facility) }}" class="btn btn-secondary btn-sm">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="td-muted empty-cell">No facilities found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@endsection
