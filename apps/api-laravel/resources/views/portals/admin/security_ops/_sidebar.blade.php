@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(220,38,38,.25);border-color:rgba(220,38,38,.4);color:#FCA5A5;">
    <i data-lucide="shield-alert" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    Security Ops
</div>
@endsection
@section('sidebar_user_role', 'Security Officer')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.admin') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i><span>Governance Portal</span>
    </a>
    <a href="{{ route('portals.admin.security') }}" class="sidebar-link {{ request()->routeIs('portals.admin.security') && !request()->routeIs('portals.admin.security.*') ? 'active' : '' }}">
        <i data-lucide="shield-alert"></i><span>SOC Dashboard</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Security</div>
    <a href="{{ route('portals.admin.security.incidents') }}" class="sidebar-link {{ request()->routeIs('portals.admin.security.incidents*') ? 'active' : '' }}">
        <i data-lucide="file-warning"></i><span>Security Incidents</span>
    </a>
    <a href="{{ route('portals.admin.security.emergency_access') }}" class="sidebar-link {{ request()->routeIs('portals.admin.security.emergency_access') ? 'active' : '' }}">
        <i data-lucide="siren"></i><span>Emergency Access</span>
    </a>
    <a href="{{ route('portals.admin.security.audit_explorer') }}" class="sidebar-link {{ request()->routeIs('portals.admin.security.audit_explorer') ? 'active' : '' }}">
        <i data-lucide="search-code"></i><span>Audit Explorer</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Admin</div>
    <a href="{{ route('portals.admin.cc') }}" class="sidebar-link">
        <i data-lucide="settings-2"></i><span>Control Center</span>
    </a>
</div>
@endsection
