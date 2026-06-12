<div class="sidebar-role-badge" style="background:rgba(245,158,11,.15);border-color:rgba(245,158,11,.4);color:#fbbf24;">
    <i data-lucide="heart-handshake" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    Health Org
</div>
<div style="margin-bottom:var(--p-space-3);"></div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Organization</div>
    <a href="{{ route('portals.healthorg.dashboard') }}" class="sidebar-link {{ request()->routeIs('portals.healthorg.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('portals.healthorg.programs') }}" class="sidebar-link {{ request()->routeIs('portals.healthorg.programs') ? 'active' : '' }}">
        <i data-lucide="folder-open"></i><span>Programs</span>
    </a>
    <a href="{{ route('portals.healthorg.outreach') }}" class="sidebar-link {{ request()->routeIs('portals.healthorg.outreach') ? 'active' : '' }}">
        <i data-lucide="map-pin"></i><span>Outreach Sites</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Public Health</div>
    <a href="{{ route('portals.healthorg.reports') }}" class="sidebar-link {{ request()->routeIs('portals.healthorg.reports') ? 'active' : '' }}">
        <i data-lucide="file-bar-chart-2"></i><span>Reports</span>
    </a>
    <a href="{{ route('portals.healthorg.signals') }}" class="sidebar-link {{ request()->routeIs('portals.healthorg.signals') ? 'active' : '' }}">
        <i data-lucide="activity"></i><span>Outbreak Signals</span>
    </a>
    <a href="{{ route('public.care-map') }}" class="sidebar-link" target="_blank">
        <i data-lucide="map"></i><span>Care Map</span>
    </a>
</div>
