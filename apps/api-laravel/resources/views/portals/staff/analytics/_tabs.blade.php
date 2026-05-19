{{-- Analytics sub-dashboard tab strip --}}
<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:20px;border-bottom:2px solid #e5e7eb;padding-bottom:0;">
    <a href="{{ route('portals.staff.analytics') }}"
       class="analytics-tab {{ request()->routeIs('portals.staff.analytics') && !request()->routeIs('portals.staff.analytics.*') ? 'analytics-tab--active' : '' }}">
        <i data-lucide="layout-dashboard" style="width:13px;height:13px;"></i> Overview
    </a>
    <a href="{{ route('portals.staff.analytics.queue') }}"
       class="analytics-tab {{ request()->routeIs('portals.staff.analytics.queue') ? 'analytics-tab--active' : '' }}">
        <i data-lucide="list-ordered" style="width:13px;height:13px;"></i> Queue
    </a>
    <a href="{{ route('portals.staff.analytics.ward') }}"
       class="analytics-tab {{ request()->routeIs('portals.staff.analytics.ward') ? 'analytics-tab--active' : '' }}">
        <i data-lucide="bed" style="width:13px;height:13px;"></i> Wards & Beds
    </a>
    <a href="{{ route('portals.staff.analytics.financial') }}"
       class="analytics-tab {{ request()->routeIs('portals.staff.analytics.financial') ? 'analytics-tab--active' : '' }}">
        <i data-lucide="bar-chart-2" style="width:13px;height:13px;"></i> Financial
    </a>
    <a href="{{ route('portals.staff.analytics.data_quality') }}"
       class="analytics-tab {{ request()->routeIs('portals.staff.analytics.data_quality') ? 'analytics-tab--active' : '' }}">
        <i data-lucide="shield-check" style="width:13px;height:13px;"></i> Data Quality
    </a>
</div>

<style>
.analytics-tab {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 6px 6px 0 0;
    font-size: 0.83rem; font-weight: 500; color: #6b7280;
    text-decoration: none; border: 1px solid transparent; border-bottom: none;
    margin-bottom: -2px;
    transition: background 0.15s, color 0.15s;
}
.analytics-tab:hover { background: #f9fafb; color: #374151; }
.analytics-tab--active {
    background: white; color: #7c3aed; font-weight: 600;
    border-color: #e5e7eb; border-bottom-color: white;
}
</style>
