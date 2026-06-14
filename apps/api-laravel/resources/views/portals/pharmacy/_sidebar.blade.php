@php $l = app()->getLocale(); @endphp
<div class="sidebar-role-badge">
    <i data-lucide="pill"></i>
    Pharmacy
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Pharmacy</div>
    <a href="{{ route('portals.pharmacy.dashboard') }}" class="sidebar-link {{ request()->routeIs('portals.pharmacy.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('portals.pharmacy.prescriptions') }}" class="sidebar-link {{ request()->routeIs('portals.pharmacy.prescriptions') ? 'active' : '' }}">
        <i data-lucide="clipboard-list"></i><span>Prescription queue</span>
    </a>
    <a href="{{ route('portals.pharmacy.inventory') }}" class="sidebar-link {{ request()->routeIs('portals.pharmacy.inventory') ? 'active' : '' }}">
        <i data-lucide="package"></i><span>Drug inventory</span>
    </a>
    <a href="{{ route('portals.pharmacy.controlled') }}" class="sidebar-link {{ request()->routeIs('portals.pharmacy.controlled') ? 'active' : '' }}">
        <i data-lucide="lock"></i><span>Controlled substances</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Stock management</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="archive"></i><span>Full stock manager</span>
    </a>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link">
        <i data-lucide="truck"></i><span>Supply chain</span>
    </a>
</div>
