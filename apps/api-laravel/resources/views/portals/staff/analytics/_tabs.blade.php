{{-- Analytics sub-dashboard tab strip --}}
<div class="tabs mb-6">
    <a href="{{ route('portals.staff.analytics') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics') && !request()->routeIs('portals.staff.analytics.*') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i> {{ __('public.stf_analytics_tab_overview') }}
    </a>
    <a href="{{ route('portals.staff.analytics.queue') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics.queue') ? 'active' : '' }}">
        <i data-lucide="list-ordered"></i> {{ __('public.stf_analytics_tab_queue') }}
    </a>
    <a href="{{ route('portals.staff.analytics.ward') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics.ward') ? 'active' : '' }}">
        <i data-lucide="bed"></i> {{ __('public.stf_analytics_tab_wards') }}
    </a>
    <a href="{{ route('portals.staff.analytics.financial') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics.financial') ? 'active' : '' }}">
        <i data-lucide="bar-chart-2"></i> {{ __('public.stf_analytics_tab_financial') }}
    </a>
    <a href="{{ route('portals.staff.analytics.data_quality') }}"
       class="tab {{ request()->routeIs('portals.staff.analytics.data_quality') ? 'active' : '' }}">
        <i data-lucide="shield-check"></i> {{ __('public.stf_analytics_tab_data_quality') }}
    </a>
</div>
