{{-- Analytics sub-dashboard tab strip --}}
<div class="tabs mb-6">
    <a href="{{ route('portals.staff.analytics') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics') && !request()->routeIs('portals.staff.analytics.*') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i> Overview
    </a>
    <a href="{{ route('portals.staff.analytics.queue') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics.queue') ? 'active' : '' }}">
        <i data-lucide="list-ordered"></i> Queue
    </a>
    <a href="{{ route('portals.staff.analytics.ward') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics.ward') ? 'active' : '' }}">
        <i data-lucide="bed"></i> Wards & Beds
    </a>
    <a href="{{ route('portals.staff.analytics.financial') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics.financial') ? 'active' : '' }}">
        <i data-lucide="bar-chart-2"></i> Financial
    </a>
    <a href="{{ route('portals.staff.analytics.data_quality') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics.data_quality') ? 'active' : '' }}">
        <i data-lucide="shield-check"></i> Data Quality
    </a>
</div>
