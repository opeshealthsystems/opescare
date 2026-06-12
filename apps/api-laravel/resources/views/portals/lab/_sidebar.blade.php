<div class="sidebar-role-badge" style="background:rgba(14,165,233,.15);border-color:rgba(14,165,233,.4);color:#38bdf8;">
    <i data-lucide="microscope" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    Laboratory
</div>
<div style="margin-bottom:var(--p-space-3);"></div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Laboratory</div>
    <a href="{{ route('portals.lab.dashboard') }}" class="sidebar-link {{ request()->routeIs('portals.lab.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('portals.lab.orders') }}" class="sidebar-link {{ request()->routeIs('portals.lab.orders') ? 'active' : '' }}">
        <i data-lucide="clipboard-list"></i><span>Work Queue</span>
    </a>
    <a href="{{ route('portals.lab.samples') }}" class="sidebar-link {{ request()->routeIs('portals.lab.samples') ? 'active' : '' }}">
        <i data-lucide="test-tube"></i><span>Sample Tracking</span>
    </a>
    <a href="{{ route('portals.lab.results') }}" class="sidebar-link {{ request()->routeIs('portals.lab.results') ? 'active' : '' }}">
        <i data-lucide="file-bar-chart"></i><span>Results</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">CDSS</div>
    <a href="{{ route('portals.staff.cdss.lab_rules') }}" class="sidebar-link">
        <i data-lucide="alert-triangle"></i><span>Lab Alert Rules</span>
    </a>
</div>
