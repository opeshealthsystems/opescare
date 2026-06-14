<div class="sidebar-role-badge">
    <i data-lucide="microscope"></i>
    Laboratory
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Laboratory</div>
    <a href="{{ route('portals.lab.dashboard') }}" class="sidebar-link {{ request()->routeIs('portals.lab.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('portals.lab.orders') }}" class="sidebar-link {{ request()->routeIs('portals.lab.orders') ? 'active' : '' }}">
        <i data-lucide="clipboard-list"></i><span>Work queue</span>
    </a>
    <a href="{{ route('portals.lab.samples') }}" class="sidebar-link {{ request()->routeIs('portals.lab.samples') ? 'active' : '' }}">
        <i data-lucide="test-tube"></i><span>Sample tracking</span>
    </a>
    <a href="{{ route('portals.lab.results') }}" class="sidebar-link {{ request()->routeIs('portals.lab.results') ? 'active' : '' }}">
        <i data-lucide="file-bar-chart"></i><span>Results</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">CDSS</div>
    <a href="{{ route('portals.staff.cdss.lab_rules') }}" class="sidebar-link">
        <i data-lucide="alert-triangle"></i><span>Lab alert rules</span>
    </a>
</div>
