@php $l = app()->getLocale(); @endphp
<div class="sidebar-role-badge" style="background:rgba(37,99,235,.2);border-color:rgba(37,99,235,.4);color:#93C5FD;">
    <i data-lucide="pill" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    {{ __('portal.pharmacy_manager_role', [], $l) ?: 'Pharmacy Manager' }}
</div>
<div style="margin-bottom:var(--p-space-3);"></div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Pharmacy Portal</div>
    <a href="{{ route('portals.pharmacy.dashboard') }}" class="sidebar-link" style="background:rgba(16,185,129,.08);border-left:3px solid #10b981;">
        <i data-lucide="pill" style="color:#10b981;"></i>
        <span style="font-weight:700;">My Pharmacy Portal</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_dashboard', [], $l) ?: 'Overview' }}</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], $l) ?: 'Dashboard' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">{{ __('public.portal.nav_pharmacy', [], $l) ?: 'Pharmacy' }}</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="pill"></i>
        <span>{{ __('public.portal.nav_pharmacy', [], $l) ?: 'Pharmacy Inventory' }}</span>
    </a>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link">
        <i data-lucide="package"></i>
        <span>{{ __('public.portal.nav_supply', [], $l) ?: 'Supply Chain' }}</span>
    </a>
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
