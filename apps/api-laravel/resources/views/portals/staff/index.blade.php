@extends('layouts.portal')

@section('title', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Clinical Portal — OpesCare')

@section('breadcrumb_home', __('public.staff_portal.portal_home', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))

@section('sidebar_role_badge')
    <div class="sidebar-role-badge">
        <i data-lucide="stethoscope" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        {{ __('public.staff_portal.role_label', [], app()->getLocale()) ?: 'Clinical Staff' }}
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">{{ __('public.staff_portal.nav_overview', [], app()->getLocale()) ?: 'Overview' }}</div>

    <a href="{{ route('portals.staff') }}" class="sidebar-link active">
        <i data-lucide="layout-dashboard"></i>
        {{ __('public.staff_portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}
    </a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">{{ __('public.staff_portal.nav_clinical', [], app()->getLocale()) ?: 'Clinical' }}</div>

    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        {{ __('public.staff_portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}
    </a>

    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        {{ __('public.staff_portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}
    </a>

    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link">
        <i data-lucide="syringe"></i>
        {{ __('public.staff_portal.nav_immunizations', [], app()->getLocale()) ?: 'Immunizations' }}
    </a>

    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link">
        <i data-lucide="send"></i>
        {{ __('public.staff_portal.nav_referrals', [], app()->getLocale()) ?: 'Referrals' }}
    </a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">{{ __('public.staff_portal.nav_operations', [], app()->getLocale()) ?: 'Operations' }}</div>

    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i>
        {{ __('public.staff_portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}
    </a>

    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        {{ __('public.staff_portal.nav_support', [], app()->getLocale()) ?: 'Support' }}
    </a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">{{ __('public.staff_portal.nav_tools', [], app()->getLocale()) ?: 'Tools' }}</div>

    <a href="{{ route('care_map.directory') }}" class="sidebar-link">
        <i data-lucide="map-pin"></i>
        {{ __('public.staff_portal.nav_care_map', [], app()->getLocale()) ?: 'Care Map' }}
    </a>

    <a href="{{ route('public.interoperability') }}" class="sidebar-link">
        <i data-lucide="cable"></i>
        {{ __('public.staff_portal.nav_integrations', [], app()->getLocale()) ?: 'Integrations' }}
    </a>
@endsection

@section('sidebar_user_role')
    {{ __('public.staff_portal.role_label', [], app()->getLocale()) ?: 'Clinical Staff' }}
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.staff_portal.dashboard_title', [], app()->getLocale()) ?: 'Clinical Dashboard' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.dashboard_subtitle', [], app()->getLocale()) ?: 'Welcome back. Here\'s your facility summary for today.' }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('portals.staff.queue') }}" class="btn btn-secondary">
            <i data-lucide="list-ordered"></i>
            {{ __('public.staff_portal.view_queue', [], app()->getLocale()) ?: 'View Queue' }}
        </a>
        <a href="{{ route('portals.staff.appointments') }}" class="btn btn-primary">
            <i data-lucide="calendar-plus"></i>
            {{ __('public.staff_portal.new_appointment', [], app()->getLocale()) ?: 'New Appointment' }}
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon blue">
            <i data-lucide="calendar-check-2"></i>
        </div>
        <div class="kpi-value">—</div>
        <div class="kpi-label">{{ __('public.staff_portal.kpi_today_appointments', [], app()->getLocale()) ?: "Today's Appointments" }}</div>
        <div class="kpi-delta up">
            <i data-lucide="trending-up"></i>
            {{ __('public.staff_portal.kpi_vs_yesterday', [], app()->getLocale()) ?: 'vs. yesterday' }}
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon teal">
            <i data-lucide="list-ordered"></i>
        </div>
        <div class="kpi-value">—</div>
        <div class="kpi-label">{{ __('public.staff_portal.kpi_queue', [], app()->getLocale()) ?: 'Patients in Queue' }}</div>
        <div class="kpi-delta up">
            <i data-lucide="clock"></i>
            {{ __('public.staff_portal.kpi_queue_now', [], app()->getLocale()) ?: 'Current' }}
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon warning">
            <i data-lucide="send"></i>
        </div>
        <div class="kpi-value">—</div>
        <div class="kpi-label">{{ __('public.staff_portal.kpi_pending_referrals', [], app()->getLocale()) ?: 'Pending Referrals' }}</div>
        <div class="kpi-delta">
            <i data-lucide="arrow-right"></i>
            {{ __('public.staff_portal.kpi_action_needed', [], app()->getLocale()) ?: 'Need action' }}
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon danger">
            <i data-lucide="receipt"></i>
        </div>
        <div class="kpi-value">—</div>
        <div class="kpi-label">{{ __('public.staff_portal.kpi_open_invoices', [], app()->getLocale()) ?: 'Open Invoices' }}</div>
        <div class="kpi-delta down">
            <i data-lucide="alert-circle"></i>
            {{ __('public.staff_portal.kpi_pending_payment', [], app()->getLocale()) ?: 'Pending payment' }}
        </div>
    </div>
</div>

<!-- Verify Patient Card + Quick Navigation -->
<div class="grid-main-side mb-8" style="margin-bottom:var(--p-space-8);">

    <!-- Patient Verification -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="search"></i>
                {{ __('public.staff_portal.verify_patient_title', [], app()->getLocale()) ?: 'Verify Patient Identity' }}
            </h2>
            <a href="{{ route('portals.staff') }}" class="btn btn-sm btn-ghost">
                {{ __('public.staff_portal.search_all', [], app()->getLocale()) ?: 'Advanced Search' }}
            </a>
        </div>
        <div class="panel-body">
            <p style="font-size:0.875rem;color:var(--p-text-muted);margin-bottom:var(--p-space-5);">
                {{ __('public.staff_portal.verify_desc', [], app()->getLocale()) ?: 'Enter the patient\'s Health ID or name to verify identity before treatment.' }}
            </p>
            <form id="verifyForm" action="{{ route('portals.staff') }}" method="GET" role="search">
                <div class="form-group mb-4" style="margin-bottom:var(--p-space-4);">
                    <label class="form-label" for="health_id">{{ __('public.medical_id.health_id', [], app()->getLocale()) ?: 'Health ID Token' }}</label>
                    <div class="form-search">
                        <span class="search-icon"><i data-lucide="id-card"></i></span>
                        <input type="text"
                               id="health_id"
                               name="health_id"
                               class="form-control"
                               placeholder="e.g. OPC-8849-DX9"
                               style="text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:0.08em;"
                               aria-label="{{ __('public.medical_id.health_id', [], app()->getLocale()) ?: 'Health ID Token' }}">
                    </div>
                </div>
                <div class="form-row mb-4" style="margin-bottom:var(--p-space-4);">
                    <div class="form-group">
                        <label class="form-label" for="purpose">{{ __('public.staff_portal.purpose', [], app()->getLocale()) ?: 'Access Purpose' }}</label>
                        <select id="purpose" name="purpose" class="form-control">
                            <option value="treatment">{{ __('public.staff_portal.purpose_treatment', [], app()->getLocale()) ?: 'Treatment' }}</option>
                            <option value="pharmacy_dispense">{{ __('public.staff_portal.purpose_pharmacy', [], app()->getLocale()) ?: 'Pharmacy Dispense' }}</option>
                            <option value="lab_order">{{ __('public.staff_portal.purpose_lab', [], app()->getLocale()) ?: 'Lab Order' }}</option>
                            <option value="insurance_claim">{{ __('public.staff_portal.purpose_insurance', [], app()->getLocale()) ?: 'Insurance Claim' }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="facility_id">{{ __('public.staff_portal.facility', [], app()->getLocale()) ?: 'Facility' }}</label>
                        <input type="text" id="facility_id" name="facility_id" class="form-control" placeholder="{{ __('public.staff_portal.facility_placeholder', [], app()->getLocale()) ?: 'Facility ID' }}" value="{{ request('facility_id') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i data-lucide="shield-check"></i>
                    {{ __('public.medical_id.verify_health_id', [], app()->getLocale()) ?: 'Verify Health ID' }}
                </button>
            </form>

            <!-- Security Notice -->
            <div class="alert alert-info mt-4" role="note" style="margin-top:var(--p-space-5);">
                <i data-lucide="lock-keyhole"></i>
                <div style="font-size:0.8125rem;">{{ __('public.staff_portal.audit_notice', [], app()->getLocale()) ?: 'This lookup is fully audited. Ensure you have patient consent before proceeding.' }}</div>
            </div>

            <!-- Results Area -->
            @if(request('health_id'))
            <div id="resultsArea" style="margin-top:var(--p-space-6);padding-top:var(--p-space-5);border-top:1px solid var(--p-border);">
                <div class="alert alert-warning" role="status">
                    <i data-lucide="info"></i>
                    <div>{{ __('public.staff_portal.api_lookup_hint', [], app()->getLocale()) ?: 'Patient lookup is processed via the API. Use the mobile app or API integration for live results.' }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Nav Modules -->
    <div style="display:flex;flex-direction:column;gap:var(--p-space-4);">

        <!-- Quick Links -->
        <div class="panel" style="flex:1;">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i data-lucide="zap"></i>
                    {{ __('public.staff_portal.quick_links', [], app()->getLocale()) ?: 'Quick Access' }}
                </h2>
            </div>
            <div class="panel-body" style="padding:var(--p-space-3) var(--p-space-4);">
                <div style="display:flex;flex-direction:column;gap:var(--p-space-2);">
                    @foreach([
                        ['route' => 'portals.staff.appointments', 'icon' => 'calendar-check-2', 'label' => 'Appointments'],
                        ['route' => 'portals.staff.queue',        'icon' => 'list-ordered',     'label' => 'Patient Queue'],
                        ['route' => 'portals.staff.immunizations','icon' => 'syringe',          'label' => 'Immunizations'],
                        ['route' => 'portals.staff.referrals',   'icon' => 'send',             'label' => 'Referrals'],
                        ['route' => 'portals.staff.billing',     'icon' => 'receipt',          'label' => 'Billing'],
                        ['route' => 'portals.staff.support',     'icon' => 'headset',          'label' => 'Support Tickets'],
                    ] as $link)
                    <a href="{{ route($link['route']) }}"
                       style="display:flex;align-items:center;gap:var(--p-space-3);padding:var(--p-space-3) var(--p-space-3);border-radius:var(--p-radius);color:var(--p-text-2);font-size:0.875rem;font-weight:600;transition:all 0.15s;text-decoration:none;"
                       onmouseover="this.style.background='var(--p-primary-light)';this.style.color='var(--p-primary)';"
                       onmouseout="this.style.background='';this.style.color='var(--p-text-2)';">
                        <span style="width:1.85rem;height:1.85rem;background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i data-lucide="{{ $link['icon'] }}" style="width:0.9rem;height:0.9rem;"></i>
                        </span>
                        {{ $link['label'] }}
                        <i data-lucide="chevron-right" style="width:0.85rem;height:0.85rem;margin-left:auto;color:var(--p-text-light);"></i>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Clinical Safety Notice -->
        <div class="panel">
            <div class="panel-body" style="padding:var(--p-space-4) var(--p-space-5);">
                <div style="display:flex;align-items:flex-start;gap:var(--p-space-3);">
                    <div style="color:var(--p-danger);flex-shrink:0;margin-top:2px;">
                        <i data-lucide="shield-alert" style="width:1.25rem;height:1.25rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:0.8125rem;font-weight:700;color:var(--p-text-2);margin-bottom:4px;">
                            {{ __('public.staff_portal.safety_title', [], app()->getLocale()) ?: 'Clinical Safety Reminder' }}
                        </div>
                        <div style="font-size:0.75rem;color:var(--p-text-muted);line-height:1.5;">
                            {{ __('public.staff_portal.safety_desc', [], app()->getLocale()) ?: 'Always confirm patient identity before administering treatment. Check allergy status and consent before accessing clinical records.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
<!-- /.grid-main-side -->
@endsection
