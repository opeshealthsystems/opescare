@extends('layouts.portal')

@section('title', 'Referrals — OpesCare Staff Portal')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Referrals')

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
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link"><i data-lucide="list-ordered"></i> Patient Queue</a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link"><i data-lucide="syringe"></i> Immunizations</a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link active"><i data-lucide="send"></i> Referrals</a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link"><i data-lucide="receipt"></i> Billing</a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link"><i data-lucide="headset"></i> Support</a>
@endsection

@section('sidebar_user_role', 'Clinical Staff')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Referral Network</h1>
        <p class="page-subtitle">Manage and track patient referrals across facilities.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('portals.staff.referrals.create') }}" class="btn btn-primary">
            <i data-lucide="send"></i>
            New Referral
        </a>
    </div>
</div>

<!-- Filters -->
<div class="panel mb-6" style="margin-bottom:var(--p-space-6);">
    <form method="get" action="{{ route('portals.staff.referrals') }}">
        <div class="filter-bar">
            <div class="form-group" style="flex:1;min-width:160px;">
                <div class="form-search">
                    <span class="search-icon"><i data-lucide="search"></i></span>
                    <input type="text" name="patient_id" class="form-control" placeholder="Patient ID…" value="{{ request('patient_id') }}">
                </div>
            </div>
            <div class="form-group" style="min-width:160px;">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    @foreach(['draft','sent','accepted','rejected','completed','cancelled','expired'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="min-width:160px;">
                <select name="priority" class="form-control">
                    <option value="">All Priorities</option>
                    @foreach(['routine','urgent','emergency'] as $p)
                    <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i data-lucide="filter"></i> Filter</button>
            <a href="{{ route('portals.staff.referrals') }}" class="btn btn-secondary"><i data-lucide="x"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Referrals Table -->
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="send"></i>
            Referrals
        </h2>
        <span class="badge badge-primary">{{ count($referrals) }} referrals</span>
    </div>

    @if(count($referrals) === 0)
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="send"></i></div>
            <h3>No Referrals Found</h3>
            <p>No referrals match the current filters.</p>
            <a href="{{ route('portals.staff.referrals.create') }}" class="btn btn-primary">
                <i data-lucide="send"></i> Create Referral
            </a>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table" aria-label="Referrals list">
                <thead>
                    <tr>
                        <th>Referral ID</th>
                        <th>Patient</th>
                        <th>From Facility</th>
                        <th>To Facility</th>
                        <th>Specialty</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="td-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($referrals as $referral)
                    <tr>
                        <td data-label="ID">
                            <span class="td-mono">{{ substr($referral->id, 0, 8) }}</span>
                        </td>
                        <td data-label="Patient">
                            <span class="td-mono">{{ $referral->patient_id }}</span>
                        </td>
                        <td data-label="From">
                            <span class="td-muted">{{ $referral->referring_facility_id ?? '—' }}</span>
                        </td>
                        <td data-label="To">
                            <span class="td-strong">{{ $referral->receiving_facility_id ?? '—' }}</span>
                        </td>
                        <td data-label="Specialty">
                            <span class="td-muted">{{ $referral->specialty ?? '—' }}</span>
                        </td>
                        <td data-label="Priority">
                            @php
                                $prCls = match($referral->priority ?? 'routine') {
                                    'emergency' => 'badge-critical',
                                    'urgent'    => 'badge-danger',
                                    default     => 'badge-neutral',
                                };
                            @endphp
                            <span class="badge {{ $prCls }}">{{ ucfirst($referral->priority ?? 'routine') }}</span>
                        </td>
                        <td data-label="Status">
                            @php
                                $stCls = match($referral->status ?? 'draft') {
                                    'accepted'  => 'badge-success',
                                    'completed' => 'badge-teal',
                                    'sent'      => 'badge-primary',
                                    'rejected'  => 'badge-danger',
                                    'cancelled' => 'badge-neutral',
                                    'expired'   => 'badge-neutral',
                                    default     => 'badge-warning',
                                };
                            @endphp
                            <span class="badge {{ $stCls }}">{{ ucfirst($referral->status ?? 'draft') }}</span>
                        </td>
                        <td data-label="Actions" class="td-actions">
                            <a href="{{ route('portals.staff.referrals.show', $referral->id) }}" class="btn btn-sm btn-secondary">
                                <i data-lucide="eye" style="width:0.85rem;height:0.85rem;"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
