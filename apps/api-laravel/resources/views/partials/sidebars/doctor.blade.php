@php $l = app()->getLocale(); @endphp
<div class="sidebar-role-badge" style="background:rgba(37,99,235,.2);border-color:rgba(37,99,235,.4);color:#93C5FD;">
    <i data-lucide="stethoscope" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    {{ __('portal.doctor_role', [], $l) ?: 'Doctor' }}
</div>
<div style="margin-bottom:var(--p-space-3);"></div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_dashboard', [], $l) ?: 'Overview' }}</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], $l) ?: 'Dashboard' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_clinical', [], $l) ?: 'Clinical' }}</div>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="clipboard-list"></i>
        <span>{{ __('public.portal.nav_visits', [], $l) ?: 'Visits' }}</span>
    </a>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], $l) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], $l) ?: 'Patient Queue' }}</span>
    </a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link">
        <i data-lucide="send"></i>
        <span>{{ __('public.portal.nav_referrals', [], $l) ?: 'Referrals' }}</span>
    </a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link">
        <i data-lucide="syringe"></i>
        <span>{{ __('public.portal.nav_immunizations', [], $l) ?: 'Immunizations' }}</span>
    </a>
    <a href="{{ route('portals.staff.prescriptions') }}" class="sidebar-link">
        <i data-lucide="clipboard-list"></i>
        <span>Prescriptions</span>
    </a>
    <a href="{{ route('portals.staff.lab_orders') }}" class="sidebar-link">
        <i data-lucide="microscope"></i>
        <span>Lab Orders</span>
    </a>
    <a href="{{ route('portals.staff.cdss') }}" class="sidebar-link">
        <i data-lucide="brain"></i>
        <span>{{ __('public.portal.nav_cdss', [], $l) ?: 'CDSS' }}</span>
    </a>
    <a href="{{ route('portals.staff.telemedicine.index') }}" class="sidebar-link">
        <i data-lucide="video"></i>
        <span>{{ __('public.portal.nav_telemedicine', [], $l) ?: 'Telemedicine' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_operations', [], $l) ?: 'Operations' }}</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], $l) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link">
        <i data-lucide="bar-chart-2"></i>
        <span>{{ __('public.portal.nav_analytics', [], $l) ?: 'Analytics' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], $l) ?: 'Support' }}</span>
    </a>
</div>
