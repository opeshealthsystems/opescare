@extends('layouts.portal')

@section('title', 'Appointments — OpesCare Staff Portal')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Appointments')

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
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link active"><i data-lucide="calendar-check-2"></i> Appointments</a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link"><i data-lucide="list-ordered"></i> Patient Queue</a>
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
        <h1 class="page-title">Appointments</h1>
        <p class="page-subtitle">Manage scheduled appointments and patient attendance.</p>
    </div>
    <div class="page-actions">
        <a href="#" class="btn btn-primary">
            <i data-lucide="calendar-plus"></i>
            New Appointment
        </a>
    </div>
</div>

<!-- Filters -->
<div class="panel mb-6" style="margin-bottom:var(--p-space-6);">
    <form method="get" action="{{ route('portals.staff.appointments') }}">
        <div class="filter-bar">
            <div class="form-group" style="flex:1;min-width:160px;">
                <div class="form-search">
                    <span class="search-icon"><i data-lucide="search"></i></span>
                    <input type="text" name="patient_id" class="form-control" placeholder="Patient Health ID…" value="{{ request('patient_id') }}" aria-label="Search by patient ID">
                </div>
            </div>
            <div class="form-group" style="min-width:180px;">
                <input type="text" name="facility_id" class="form-control" placeholder="Facility ID…" value="{{ request('facility_id') }}" aria-label="Filter by facility">
            </div>
            <div class="form-group" style="min-width:160px;">
                <input type="text" name="provider_id" class="form-control" placeholder="Provider ID…" value="{{ request('provider_id') }}" aria-label="Filter by provider">
            </div>
            <div class="form-group" style="min-width:140px;">
                <input type="date" name="date" class="form-control" value="{{ request('date') }}" aria-label="Filter by date">
            </div>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="filter"></i> Filter
            </button>
            <a href="{{ route('portals.staff.appointments') }}" class="btn btn-secondary">
                <i data-lucide="x"></i> Clear
            </a>
        </div>
    </form>
</div>

<!-- Appointments Table -->
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="calendar-check-2"></i>
            Scheduled Appointments
        </h2>
        <span class="badge badge-primary">
            {{ $appointments instanceof \Illuminate\Pagination\LengthAwarePaginator ? $appointments->total() : count($appointments) }} total
        </span>
    </div>

    @if($appointments->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-lucide="calendar-x-2"></i>
            </div>
            <h3>No Appointments Found</h3>
            <p>No appointments match the current filters. Try adjusting your search or creating a new appointment.</p>
            <a href="#" class="btn btn-primary">
                <i data-lucide="calendar-plus"></i> New Appointment
            </a>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table" aria-label="Appointments list">
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>Patient ID</th>
                        <th>Provider</th>
                        <th>Type</th>
                        <th>Facility</th>
                        <th>Status</th>
                        <th class="td-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appointment)
                    <tr>
                        <td data-label="Date">
                            <div class="td-strong">{{ $appointment->scheduled_at?->format('d M Y') }}</div>
                            <div class="td-muted">{{ $appointment->scheduled_at?->format('H:i') }}</div>
                        </td>
                        <td data-label="Patient">
                            <span class="td-mono">{{ $appointment->patient_id }}</span>
                        </td>
                        <td data-label="Provider">
                            <span class="td-muted">{{ $appointment->provider_id ?? '—' }}</span>
                        </td>
                        <td data-label="Type">
                            <span class="badge badge-primary">{{ str_replace('_', ' ', $appointment->appointment_type) }}</span>
                        </td>
                        <td data-label="Facility">
                            <span class="td-muted">{{ $appointment->facility_id ?? '—' }}</span>
                        </td>
                        <td data-label="Status">
                            @php
                                $statusClass = match($appointment->status ?? 'scheduled') {
                                    'completed'  => 'badge-success',
                                    'cancelled'  => 'badge-danger',
                                    'no_show'    => 'badge-warning',
                                    'checked_in' => 'badge-teal',
                                    default      => 'badge-neutral',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $appointment->status ?? 'scheduled')) }}
                            </span>
                        </td>
                        <td data-label="Actions" class="td-actions">
                            <div style="display:flex;gap:var(--p-space-2);">
                                <button class="btn btn-sm btn-secondary" title="View" aria-label="View appointment">
                                    <i data-lucide="eye" style="width:0.85rem;height:0.85rem;"></i>
                                </button>
                                <button class="btn btn-sm btn-teal" title="Check In" aria-label="Check in patient">
                                    <i data-lucide="log-in" style="width:0.85rem;height:0.85rem;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($appointments instanceof \Illuminate\Pagination\LengthAwarePaginator && $appointments->hasPages())
        <div class="panel-footer" style="display:flex;align-items:center;justify-content:space-between;">
            <span>
                Showing {{ $appointments->firstItem() }}–{{ $appointments->lastItem() }} of {{ $appointments->total() }}
            </span>
            <div>{{ $appointments->links() }}</div>
        </div>
        @endif
    @endif
</div>

@endsection
