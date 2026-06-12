@php $l = app()->getLocale(); @endphp
<div class="sidebar-role-badge" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.4);color:#34d399;">
    <i data-lucide="pill" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    Pharmacy
</div>
<div style="margin-bottom:var(--p-space-3);"></div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Pharmacy</div>
    <a href="{{ route('portals.pharmacy.dashboard') }}" class="sidebar-link {{ request()->routeIs('portals.pharmacy.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('portals.pharmacy.prescriptions') }}" class="sidebar-link {{ request()->routeIs('portals.pharmacy.prescriptions') ? 'active' : '' }}">
        <i data-lucide="clipboard-list"></i><span>Prescription Queue</span>
    </a>
    <a href="{{ route('portals.pharmacy.inventory') }}" class="sidebar-link {{ request()->routeIs('portals.pharmacy.inventory') ? 'active' : '' }}">
        <i data-lucide="package"></i><span>Drug Inventory</span>
    </a>
    <a href="{{ route('portals.pharmacy.controlled') }}" class="sidebar-link {{ request()->routeIs('portals.pharmacy.controlled') ? 'active' : '' }}">
        <i data-lucide="lock"></i><span>Controlled Substances</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Stock Management</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="archive"></i><span>Full Stock Manager</span>
    </a>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link">
        <i data-lucide="truck"></i><span>Supply Chain</span>
    </a>
</div>
