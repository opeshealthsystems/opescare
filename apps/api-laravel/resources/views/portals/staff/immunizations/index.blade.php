@extends('layouts.portal')

@section('title', 'Immunizations — OpesCare Staff Portal')

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Immunizations')

@section('sidebar_role_badge')
    <div class="sidebar-role-badge">
        <i data-lucide="stethoscope" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        {{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i> Dashboard</a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link"><i data-lucide="calendar-check-2"></i> Appointments</a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link"><i data-lucide="list-ordered"></i> Patient Queue</a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link active"><i data-lucide="syringe"></i> Immunizations</a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link"><i data-lucide="send"></i> Referrals</a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link"><i data-lucide="receipt"></i> Billing</a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link"><i data-lucide="headset"></i> Support</a>
@endsection

@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Immunization Records</h1>
        <p class="page-subtitle">Review vaccination history and record new immunizations.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('portals.staff.immunizations.record') }}" class="btn btn-primary">
            <i data-lucide="syringe"></i>
            Record Immunization
        </a>
    </div>
</div>

<!-- Filters -->
<div class="panel mb-6" style="margin-bottom:var(--p-space-6);">
    <form method="GET" action="{{ route('portals.staff.immunizations') }}">
        <div class="filter-bar">
            <div class="form-group" style="flex:1;min-width:180px;">
                <div class="form-search">
                    <span class="search-icon"><i data-lucide="search"></i></span>
                    <input type="text" name="patient_id" class="form-control" placeholder="Patient Health ID…" value="{{ request('patient_id') }}" aria-label="Filter by patient health ID">
                </div>
            </div>
            <div class="form-group" style="min-width:160px;">
                <input type="text" name="facility_id" class="form-control" placeholder="Facility ID…" value="{{ request('facility_id') }}" aria-label="Filter by facility ID">
            </div>
            <button type="submit" class="btn btn-primary"><i data-lucide="filter"></i> Filter</button>
            <a href="{{ route('portals.staff.immunizations') }}" class="btn btn-secondary"><i data-lucide="x"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Two-panel layout -->
<div class="grid-2">

    <!-- Records Panel -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="clipboard-list"></i>
                Vaccination Records
            </h2>
            <span class="badge badge-teal">{{ count($records) }}</span>
        </div>

        @if(count($records) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="syringe"></i></div>
                <h3>No Records Found</h3>
                <p>No immunization records match your filters.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table" aria-label="Immunization records">
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Patient</th>
                            <th>Date Given</th>
                            <th>Dose</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                        <tr>
                            <td data-label="Vaccine">
                                <span class="td-strong">{{ $record->vaccine_name ?? '—' }}</span>
                                @if(!empty($record->vaccine_code))
                                <div class="td-muted">{{ $record->vaccine_code }}</div>
                                @endif
                            </td>
                            <td data-label="Patient">
                                <span class="td-mono">{{ $record->patient_id }}</span>
                            </td>
                            <td data-label="Date">
                                <span class="td-muted">
                                    {{ $record->administered_at ? \Carbon\Carbon::parse($record->administered_at)->format('d M Y') : '—' }}
                                </span>
                            </td>
                            <td data-label="Dose">
                                <span class="badge badge-primary">{{ $record->dose_number ?? '1' }}</span>
                            </td>
                            <td data-label="Status">
                                @php
                                    $stCls = match($record->status ?? 'completed') {
                                        'completed'  => 'badge-success',
                                        'historical' => 'badge-neutral',
                                        'verified'   => 'badge-teal',
                                        default      => 'badge-warning',
                                    };
                                @endphp
                                <span class="badge {{ $stCls }}">{{ ucfirst($record->status ?? 'completed') }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Schedule Panel -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="calendar-clock"></i>
                Immunization Schedule
            </h2>
            <span class="badge badge-warning">{{ count($schedule) }}</span>
        </div>

        @if(count($schedule) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="calendar-check-2"></i></div>
                <h3>Schedule Clear</h3>
                <p>No upcoming immunizations scheduled.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table" aria-label="Immunization schedule">
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Patient</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule as $item)
                        <tr>
                            <td data-label="Vaccine">
                                <span class="td-strong">{{ $item->vaccine_name ?? '—' }}</span>
                                <div class="td-muted">Dose {{ $item->dose_number ?? '1' }}</div>
                            </td>
                            <td data-label="Patient">
                                <span class="td-mono">{{ $item->patient_id }}</span>
                            </td>
                            <td data-label="Due">
                                <span class="td-muted">
                                    {{ $item->scheduled_date ? \Carbon\Carbon::parse($item->scheduled_date)->format('d M Y') : '—' }}
                                </span>
                            </td>
                            <td data-label="Status">
                                @php
                                    $stCls = match($item->status ?? 'due') {
                                        'overdue' => 'badge-danger',
                                        'due'     => 'badge-warning',
                                        default   => 'badge-neutral',
                                    };
                                @endphp
                                <span class="badge {{ $stCls }}">{{ ucfirst($item->status ?? 'due') }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@endsection
