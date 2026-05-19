@extends('layouts.portal')

@section('title', __('public.staff_portal.dashboard_title', [], app()->getLocale()) ?: 'Staff Clinical Portal')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection

@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link active">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}</span>
    </a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link">
        <i data-lucide="bar-chart-2"></i>
        <span>{{ __('public.portal.nav_analytics', [], app()->getLocale()) ?: 'Analytics' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</span>
    </a>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="stethoscope"></i>
        <span>{{ __('public.portal.nav_visits', [], app()->getLocale()) ?: 'Visits' }}</span>
    </a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link">
        <i data-lucide="syringe"></i>
        <span>{{ __('public.portal.nav_immunizations', [], app()->getLocale()) ?: 'Immunizations' }}</span>
    </a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link">
        <i data-lucide="send"></i>
        <span>{{ __('public.portal.nav_referrals', [], app()->getLocale()) ?: 'Referrals' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">HR & Staff</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_staff_directory', [], app()->getLocale()) ?: 'Directory' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
        <i data-lucide="clock"></i>
        <span>{{ __('public.portal.nav_staff_shifts', [], app()->getLocale()) ?: 'Shifts' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i>
        <span>{{ __('public.portal.nav_staff_roster', [], app()->getLocale()) ?: 'Duty Roster' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link">
        <i data-lucide="plane-takeoff"></i>
        <span>{{ __('public.portal.nav_staff_leave', [], app()->getLocale()) ?: 'Leave' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Inventory</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="pill"></i>
        <span>{{ __('public.portal.nav_inventory_pharmacy', [], app()->getLocale()) ?: 'Pharmacy' }}</span>
    </a>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link">
        <i data-lucide="droplets"></i>
        <span>{{ __('public.portal.nav_inventory_blood', [], app()->getLocale()) ?: 'Blood Bank' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i>
        <span>{{ __('public.portal.nav_insurance', [], app()->getLocale()) ?: 'Insurance' }}</span>
    </a>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link">
        <i data-lucide="upload-cloud"></i>
        <span>{{ __('public.portal.nav_data_import', [], app()->getLocale()) ?: 'Data Import' }}</span>
    </a>
    <a href="{{ route('portals.staff.search') }}" class="sidebar-link {{ request()->routeIs('portals.staff.search') ? 'active' : '' }}">
        <i data-lucide="search"></i>
        <span>{{ __('public.portal.nav_search', [], app()->getLocale()) ?: 'Global Search' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.staff_portal.dashboard_title', [], app()->getLocale()) ?: 'Staff Clinical Portal' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.dashboard_subtitle', [], app()->getLocale()) ?: 'Manage appointments, queues, and patient care from one place.' }}</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon blue">
            <i data-lucide="calendar-check-2"></i>
        </div>
        <div class="kpi-label">{{ __('public.staff_portal.kpi_todays_appointments', [], app()->getLocale()) ?: "Today's Appointments" }}</div>
        <div class="kpi-value">{{ $kpis['todays_appointments'] ?? 0 }}</div>
        <div class="kpi-sub">{{ __('public.staff_portal.kpi_scheduled_today', [], app()->getLocale()) ?: 'Scheduled today' }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon teal">
            <i data-lucide="list-ordered"></i>
        </div>
        <div class="kpi-label">{{ __('public.staff_portal.kpi_patient_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</div>
        <div class="kpi-value">{{ $kpis['in_queue'] ?? 0 }}</div>
        <div class="kpi-sub">{{ __('public.staff_portal.kpi_currently_waiting', [], app()->getLocale()) ?: 'Currently waiting' }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon warning">
            <i data-lucide="send"></i>
        </div>
        <div class="kpi-label">{{ __('public.staff_portal.kpi_pending_referrals', [], app()->getLocale()) ?: 'Pending Referrals' }}</div>
        <div class="kpi-value">{{ $kpis['pending_referrals'] ?? 0 }}</div>
        <div class="kpi-sub">{{ __('public.staff_portal.kpi_awaiting_action', [], app()->getLocale()) ?: 'Awaiting action' }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon purple">
            <i data-lucide="receipt"></i>
        </div>
        <div class="kpi-label">{{ __('public.staff_portal.kpi_open_invoices', [], app()->getLocale()) ?: 'Open Invoices' }}</div>
        <div class="kpi-value">{{ $kpis['open_invoices'] ?? 0 }}</div>
        <div class="kpi-sub">{{ __('public.staff_portal.kpi_unpaid_balance', [], app()->getLocale()) ?: 'Unpaid balance' }}</div>
    </div>
</div>

<div class="grid-main-side" style="margin-top: var(--p-space-6);">

    {{-- Patient Verification Form --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="shield-check" style="width:16px;height:16px;"></i>
                {{ __('public.staff_portal.patient_verification', [], app()->getLocale()) ?: 'Patient Verification' }}
            </h2>
        </div>
        <div class="panel-body">
            <p style="margin-bottom: var(--p-space-4); color: var(--p-text-secondary); font-size: var(--p-text-sm);">
                {{ __('public.staff_portal.verification_desc', [], app()->getLocale()) ?: 'Enter a patient Health ID to verify their identity before providing care.' }}
            </p>
            <form method="GET" action="{{ route('portals.staff') }}" autocomplete="off">
                <div class="form-group">
                    <label class="form-label form-label-required" for="health_id">
                        {{ __('public.medical_id.health_id', [], app()->getLocale()) ?: 'Health ID' }}
                    </label>
                    <div class="form-search">
                        <span class="search-icon">
                            <i data-lucide="search" style="width:14px;height:14px;"></i>
                        </span>
                        <input
                            type="text"
                            id="health_id"
                            name="health_id"
                            class="form-control"
                            placeholder="e.g. CM-HID-7KQ9-MP42-X8D1"
                            value="{{ request('health_id') }}"
                            style="padding-left: 2.25rem; font-family: monospace; text-transform: uppercase; letter-spacing: 0.05em;"
                        >
                    </div>
                    <span class="form-hint">{{ __('public.staff_portal.health_id_hint', [], app()->getLocale()) ?: 'Enter the full Health ID as printed on the patient card.' }}</span>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required" for="purpose">
                            {{ __('public.staff_portal.purpose', [], app()->getLocale()) ?: 'Access Purpose' }}
                        </label>
                        <select id="purpose" name="purpose" class="form-control">
                            <option value="treatment" @selected(request('purpose') === 'treatment')>{{ __('public.staff_portal.purpose_treatment', [], app()->getLocale()) ?: 'Treatment' }}</option>
                            <option value="pharmacy_dispense" @selected(request('purpose') === 'pharmacy_dispense')>{{ __('public.staff_portal.purpose_pharmacy', [], app()->getLocale()) ?: 'Pharmacy Dispense' }}</option>
                            <option value="lab_order" @selected(request('purpose') === 'lab_order')>{{ __('public.staff_portal.purpose_lab', [], app()->getLocale()) ?: 'Lab Order' }}</option>
                            <option value="insurance_claim" @selected(request('purpose') === 'insurance_claim')>{{ __('public.staff_portal.purpose_insurance', [], app()->getLocale()) ?: 'Insurance Claim' }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="facility_id">
                            {{ __('public.staff_portal.facility_id', [], app()->getLocale()) ?: 'Facility ID' }}
                        </label>
                        <input
                            type="text"
                            id="facility_id"
                            name="facility_id"
                            class="form-control"
                            placeholder="{{ __('public.staff_portal.facility_id_placeholder', [], app()->getLocale()) ?: 'FAC-XXXXX' }}"
                            value="{{ request('facility_id') }}"
                        >
                    </div>
                </div>
                <div style="margin-top: var(--p-space-4);">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="search" style="width:14px;height:14px;"></i>
                        {{ __('public.medical_id.verify_health_id', [], app()->getLocale()) ?: 'Verify Patient' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="zap" style="width:16px;height:16px;"></i>
                {{ __('public.staff_portal.quick_links', [], app()->getLocale()) ?: 'Quick Links' }}
            </h2>
        </div>
        <div class="panel-body">
            <div class="grid-2">
                <a href="{{ route('portals.staff.appointments') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="calendar-check-2" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}
                </a>
                <a href="{{ route('portals.staff.queue') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="list-ordered" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}
                </a>
                <a href="{{ route('portals.staff.billing') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="receipt" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}
                </a>
                <a href="{{ route('portals.staff.support') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="headset" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}
                </a>
                <a href="{{ route('portals.staff.immunizations') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="syringe" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_immunizations', [], app()->getLocale()) ?: 'Immunizations' }}
                </a>
                <a href="{{ route('portals.staff.referrals') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="send" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_referrals', [], app()->getLocale()) ?: 'Referrals' }}
                </a>
                <a href="{{ route('portals.staff.visits') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="stethoscope" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_visits', [], app()->getLocale()) ?: 'Visits' }}
                </a>
                <a href="{{ route('portals.insurance.policies') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="shield-check" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_insurance', [], app()->getLocale()) ?: 'Insurance' }}
                </a>
                <a href="{{ route('portals.staff.hr.directory') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="users" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_staff_directory', [], app()->getLocale()) ?: 'Staff Directory' }}
                </a>
                <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="pill" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_inventory_pharmacy', [], app()->getLocale()) ?: 'Pharmacy Stock' }}
                </a>
                <a href="{{ route('portals.staff.analytics') }}" class="btn btn-secondary" style="justify-content: flex-start; gap: var(--p-space-2);">
                    <i data-lucide="bar-chart-2" style="width:15px;height:15px;"></i>
                    {{ __('public.portal.nav_analytics', [], app()->getLocale()) ?: 'Analytics' }}
                </a>
            </div>
        </div>
    </div>

</div>

{{-- Clinical Safety Disclaimer --}}
<div class="alert alert-warning" style="margin-top: var(--p-space-6);">
    <i data-lucide="triangle-alert" style="width:16px;height:16px; flex-shrink:0;"></i>
    <div>
        <strong>{{ __('public.staff_portal.disclaimer_title', [], app()->getLocale()) ?: 'Clinical Safety Disclaimer' }}</strong>
        {{ __('public.staff_portal.disclaimer_text', [], app()->getLocale()) ?: 'This portal is for authorised clinical staff only. All access is logged and audited. Do not share login credentials. Patient data must be handled in accordance with applicable data protection regulations and facility privacy policies. In an emergency, use the break-glass access procedure and document the clinical justification.' }}
    </div>
</div>

@endsection
