@section('sidebar_role_badge')
<div class="sidebar-role-badge sidebar-role-badge--primary">
    <i data-lucide="hospital"></i>
    Facility Admin
</div>
@endsection
@section('sidebar_user_role', 'Facility Administrator')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.admin') }}" class="sidebar-link {{ request()->routeIs('portals.admin') && !request()->routeIs('portals.admin.clinical*') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Admin Dashboard</span>
    </a>
    <a href="{{ route('portals.admin.go-live') }}" class="sidebar-link {{ request()->routeIs('portals.admin.go-live') ? 'active' : '' }}">
        <i data-lucide="rocket"></i><span>Facility Go-Live</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical Register</div>
    <a href="{{ route('portals.admin.clinical.prescriptions') }}" class="sidebar-link {{ request()->routeIs('portals.admin.clinical.prescriptions') ? 'active' : '' }}">
        <i data-lucide="clipboard-list"></i><span>Prescription Register</span>
    </a>
    <a href="{{ route('portals.admin.clinical.lab_orders') }}" class="sidebar-link {{ request()->routeIs('portals.admin.clinical.lab_orders') ? 'active' : '' }}">
        <i data-lucide="microscope"></i><span>Lab Orders Register</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Staff & Operations</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i><span>Staff Directory</span>
    </a>
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
        <i data-lucide="calendar-clock"></i><span>Shifts & Roster</span>
    </a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link">
        <i data-lucide="bar-chart-2"></i><span>Analytics</span>
    </a>
    <a href="{{ route('portals.staff.wards') }}" class="sidebar-link">
        <i data-lucide="bed"></i><span>Wards & Admissions</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Finance</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i><span>Billing & Invoices</span>
    </a>
    <a href="{{ route('portals.staff.analytics.financial') }}" class="sidebar-link">
        <i data-lucide="trending-up"></i><span>Financial Analytics</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Security</div>
    <a href="{{ route('portals.admin.security') }}" class="sidebar-link {{ request()->routeIs('portals.admin.security*') ? 'active' : '' }}">
        <i data-lucide="shield-alert"></i><span>Security Ops</span>
    </a>
    <a href="{{ route('portals.admin.kpi.index') }}" class="sidebar-link {{ request()->routeIs('portals.admin.kpi*') ? 'active' : '' }}">
        <i data-lucide="gauge"></i><span>KPI Dashboard</span>
    </a>
</div>
@endsection
