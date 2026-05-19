@section('sidebar_role_badge')
<div class="sidebar-role-badge">Clinical Staff</div>
@endsection
@section('sidebar_user_role', 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link">
        <i data-lucide="bar-chart-2"></i><span>Analytics</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i><span>Appointments</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i><span>Patient Queue</span>
    </a>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="stethoscope"></i><span>Visits</span>
    </a>
    <a href="{{ route('portals.staff.cdss') }}" class="sidebar-link {{ request()->routeIs('portals.staff.cdss*') ? 'active' : '' }}">
        <i data-lucide="brain-circuit"></i><span>Clinical Alerts</span>
        @php
            $activeCdssAlerts = \App\Models\ClinicalAlert::where('severity','critical')->where('status','active')->count();
        @endphp
        @if($activeCdssAlerts > 0)
            <span class="sidebar-badge sidebar-badge--danger">{{ $activeCdssAlerts }}</span>
        @endif
    </a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link">
        <i data-lucide="syringe"></i><span>Immunizations</span>
    </a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link">
        <i data-lucide="send"></i><span>Referrals</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">HR & Staff</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i><span>Directory</span>
    </a>
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
        <i data-lucide="clock"></i><span>Shifts</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i><span>Duty Roster</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link">
        <i data-lucide="plane-takeoff"></i><span>Leave</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Inventory</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="pill"></i><span>Pharmacy</span>
    </a>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link">
        <i data-lucide="droplets"></i><span>Blood Bank</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Supply Chain</div>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i><span>Supply Chain</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i><span>Billing</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i><span>Support</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i><span>Insurance</span>
    </a>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link">
        <i data-lucide="upload-cloud"></i><span>Data Import</span>
    </a>
    <a href="{{ route('portals.staff.search') }}" class="sidebar-link {{ request()->routeIs('portals.staff.search') ? 'active' : '' }}">
        <i data-lucide="search"></i><span>Global Search</span>
    </a>
    <a href="{{ route('portals.staff.wards') }}" class="sidebar-link {{ request()->routeIs('portals.staff.wards*') ? 'active' : '' }}">
        <i data-lucide="bed"></i><span>Wards & Beds</span>
    </a>
</div>
@endsection
