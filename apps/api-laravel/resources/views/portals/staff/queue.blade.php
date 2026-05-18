@extends('layouts.portal')

@section('title', 'Patient Queue — OpesCare Staff Portal')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Patient Queue')

@section('sidebar_role_badge')
    <div class="sidebar-role-badge">
        <i data-lucide="stethoscope" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        {{ __('public.staff_portal.role_label', [], app()->getLocale()) ?: 'Clinical Staff' }}
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i> Dashboard</a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link"><i data-lucide="calendar-check-2"></i> Appointments</a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link active"><i data-lucide="list-ordered"></i> Patient Queue</a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link"><i data-lucide="syringe"></i> Immunizations</a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link"><i data-lucide="send"></i> Referrals</a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link"><i data-lucide="receipt"></i> Billing</a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link"><i data-lucide="headset"></i> Support</a>
@endsection

@section('sidebar_user_role', 'Clinical Staff')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Patient Queue</h1>
        <p class="page-subtitle">Monitor and manage today's facility patient flow in real time.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('portals.staff.queue') }}" class="btn btn-secondary">
            <i data-lucide="refresh-cw"></i> Refresh
        </a>
    </div>
</div>

<!-- Filters -->
<div class="panel mb-6" style="margin-bottom:var(--p-space-6);">
    <form method="get" action="{{ route('portals.staff.queue') }}">
        <div class="filter-bar">
            <div class="form-group" style="flex:1;min-width:200px;">
                <input type="text" name="facility_id" class="form-control" placeholder="Facility ID…" value="{{ request('facility_id') }}" aria-label="Filter by facility">
            </div>
            <div class="form-group" style="min-width:200px;">
                <input type="text" name="queue_name" class="form-control" placeholder="Queue name…" value="{{ request('queue_name') }}" aria-label="Filter by queue">
            </div>
            <div class="form-group" style="min-width:180px;">
                <select name="status" class="form-control" aria-label="Filter by status">
                    <option value="">All Statuses</option>
                    <option value="waiting"    {{ request('status') === 'waiting'    ? 'selected' : '' }}>Waiting</option>
                    <option value="in_progress"{{ request('status') === 'in_progress'? 'selected' : '' }}>In Progress</option>
                    <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                    <option value="no_show"    {{ request('status') === 'no_show'    ? 'selected' : '' }}>No Show</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="filter"></i> Filter
            </button>
            <a href="{{ route('portals.staff.queue') }}" class="btn btn-secondary">
                <i data-lucide="x"></i> Clear
            </a>
        </div>
    </form>
</div>

<!-- Queue Table -->
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="list-ordered"></i>
            Active Queue
        </h2>
        <span class="badge badge-teal">
            {{ $tickets instanceof \Illuminate\Pagination\LengthAwarePaginator ? $tickets->total() : count($tickets) }} patients
        </span>
    </div>

    @if($tickets->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-lucide="check-circle-2"></i>
            </div>
            <h3>Queue is Clear</h3>
            <p>No patients are currently waiting. Adjust the filters or check back when appointments begin.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table" aria-label="Patient queue">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient ID</th>
                        <th>Queue</th>
                        <th>Facility</th>
                        <th>Wait Time</th>
                        <th>Status</th>
                        <th class="td-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $i => $ticket)
                    <tr>
                        <td data-label="#">
                            <span style="font-size:0.8125rem;font-weight:700;color:var(--p-text-muted);">#{{ $ticket->queue_position ?? ($i + 1) }}</span>
                        </td>
                        <td data-label="Patient">
                            <span class="td-mono">{{ $ticket->patient_id ?? '—' }}</span>
                        </td>
                        <td data-label="Queue">
                            <span class="td-strong">{{ $ticket->queue_name ?? 'General' }}</span>
                        </td>
                        <td data-label="Facility">
                            <span class="td-muted">{{ $ticket->facility_id ?? '—' }}</span>
                        </td>
                        <td data-label="Wait">
                            <span class="td-muted">
                                @if($ticket->created_at)
                                    {{ $ticket->created_at->diffForHumans(null, true) }}
                                @else
                                    —
                                @endif
                            </span>
                        </td>
                        <td data-label="Status">
                            @php
                                $cls = match($ticket->status ?? 'waiting') {
                                    'in_progress' => 'badge-teal',
                                    'completed'   => 'badge-success',
                                    'no_show'     => 'badge-warning',
                                    default       => 'badge-primary',
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ ucfirst(str_replace('_', ' ', $ticket->status ?? 'waiting')) }}</span>
                        </td>
                        <td data-label="Actions" class="td-actions">
                            <div style="display:flex;gap:var(--p-space-2);">
                                <button class="btn btn-sm btn-teal" title="Call patient" aria-label="Call patient">
                                    <i data-lucide="phone-call" style="width:0.85rem;height:0.85rem;"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" title="Begin consult" aria-label="Begin consult">
                                    <i data-lucide="stethoscope" style="width:0.85rem;height:0.85rem;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($tickets instanceof \Illuminate\Pagination\LengthAwarePaginator && $tickets->hasPages())
        <div class="panel-footer" style="display:flex;align-items:center;justify-content:space-between;">
            <span>Showing {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} of {{ $tickets->total() }}</span>
            <div>{{ $tickets->links() }}</div>
        </div>
        @endif
    @endif
</div>

@endsection
