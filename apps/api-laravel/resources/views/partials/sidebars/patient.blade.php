@php $l = app()->getLocale(); @endphp
<div class="sidebar-role-badge" style="background:rgba(15,118,110,.3);border-color:rgba(15,118,110,.5);color:#5EEAD4;">
    <i data-lucide="user" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    {{ __('public.portal.patient_role', [], $l) ?: 'Patient' }}
</div>
<div style="margin-bottom:var(--p-space-3);"></div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_my_health', [], $l) ?: 'My Health' }}</div>
    <a href="{{ route('portals.patient') }}" class="sidebar-link {{ request()->routeIs('portals.patient') ? 'active' : '' }}">
        <i data-lucide="id-card"></i>
        <span>{{ __('public.medical_id.health_id', [], $l) ?: 'My Health ID' }}</span>
    </a>
    <a href="{{ route('portals.patient.appointments') }}" class="sidebar-link {{ request()->routeIs('portals.patient.appointments') ? 'active' : '' }}">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], $l) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.patient.labs') }}" class="sidebar-link {{ request()->routeIs('portals.patient.labs') ? 'active' : '' }}">
        <i data-lucide="flask-conical"></i>
        <span>{{ __('public.portal.nav_labs', [], $l) ?: 'Lab Results' }}</span>
    </a>
    <a href="{{ route('portals.patient.prescriptions') }}" class="sidebar-link {{ request()->routeIs('portals.patient.prescriptions') ? 'active' : '' }}">
        <i data-lucide="pill"></i>
        <span>{{ __('public.portal.nav_prescriptions', [], $l) ?: 'Prescriptions' }}</span>
    </a>
    <a href="{{ route('portals.patient.documents') }}" class="sidebar-link {{ request()->routeIs('portals.patient.documents') ? 'active' : '' }}">
        <i data-lucide="file-text"></i>
        <span>{{ __('public.portal.nav_documents', [], $l) ?: 'Documents' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.patient.allergies') }}" class="sidebar-link {{ request()->routeIs('portals.patient.allergies') ? 'active' : '' }}">
        <i data-lucide="zap"></i>
        <span>Allergies</span>
    </a>
    <a href="{{ route('portals.patient.clinical') }}" class="sidebar-link {{ request()->routeIs('portals.patient.clinical') ? 'active' : '' }}">
        <i data-lucide="stethoscope"></i>
        <span>Conditions</span>
    </a>
    <a href="{{ route('portals.patient.immunizations') }}" class="sidebar-link {{ request()->routeIs('portals.patient.immunizations') ? 'active' : '' }}">
        <i data-lucide="syringe"></i>
        <span>Immunizations</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_privacy', [], $l) ?: 'Privacy & Access' }}</div>
    <a href="{{ route('portals.patient.consent') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i>
        <span>{{ __('public.portal.nav_consent', [], $l) ?: 'Consent Requests' }}</span>
    </a>
    <a href="{{ route('portals.patient.logs') }}" class="sidebar-link">
        <i data-lucide="history"></i>
        <span>{{ __('public.portal.nav_access_logs', [], $l) ?: 'Access Logs' }}</span>
    </a>
    <a href="{{ route('portals.patient.profile') }}" class="sidebar-link">
        <i data-lucide="user-cog"></i>
        <span>{{ __('public.portal.nav_profile', [], $l) ?: 'My Profile' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_resources', [], $l) ?: 'Resources' }}</div>
    <a href="{{ route('public.care-map') }}" class="sidebar-link">
        <i data-lucide="map-pin"></i>
        <span>{{ __('public.portal.nav_care_map', [], $l) ?: 'Care Map' }}</span>
    </a>
    <a href="{{ route('public.help') }}" class="sidebar-link">
        <i data-lucide="help-circle"></i>
        <span>{{ __('public.portal.nav_help', [], $l) ?: 'Help' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_family', [], $l) ?: 'My Family' }}</div>
    <a href="{{ route('portals.patient.family') }}"
       class="sidebar-link {{ request()->routeIs('portals.patient.family*') ? 'active' : '' }}">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_family_dashboard', [], $l) ?: 'Family Dashboard' }}</span>
    </a>
    <a href="{{ route('portals.patient.family.add') }}"
       class="sidebar-link {{ request()->routeIs('portals.patient.family.add') ? 'active' : '' }}">
        <i data-lucide="user-plus"></i>
        <span>{{ __('public.portal.nav_family_add', [], $l) ?: 'Add Dependent' }}</span>
    </a>
    <a href="{{ route('portals.patient.family.invite') }}"
       class="sidebar-link {{ request()->routeIs('portals.patient.family.invite') ? 'active' : '' }}">
        <i data-lucide="mail"></i>
        <span>{{ __('public.portal.nav_family_invite', [], $l) ?: 'Invite Member' }}</span>
    </a>
</div>
