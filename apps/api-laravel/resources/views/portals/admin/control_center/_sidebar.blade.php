@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(109,40,217,.3);border-color:rgba(109,40,217,.5);color:#C4B5FD;">
    <i data-lucide="shield-check" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
    Super Admin
</div>
@endsection
@section('sidebar_user_role', 'Super Admin')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.admin') }}" class="sidebar-link {{ request()->routeIs('portals.admin') && !request()->routeIs('portals.admin.cc*') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Governance Portal</span>
    </a>
    <a href="{{ route('portals.admin.cc') }}" class="sidebar-link {{ request()->routeIs('portals.admin.cc') ? 'active' : '' }}">
        <i data-lucide="settings-2"></i><span>Control Center</span>
    </a>
    <a href="{{ route('portals.admin.cc.health') }}" class="sidebar-link {{ request()->routeIs('portals.admin.cc.health') ? 'active' : '' }}">
        <i data-lucide="activity"></i><span>System Health</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Platform Config</div>
    <a href="{{ route('portals.admin.cc.settings') }}" class="sidebar-link {{ request()->routeIs('portals.admin.cc.settings') ? 'active' : '' }}">
        <i data-lucide="sliders-horizontal"></i><span>Platform Settings</span>
    </a>
    <a href="{{ route('portals.admin.cc.feature_flags') }}" class="sidebar-link {{ request()->routeIs('portals.admin.cc.feature_flags') ? 'active' : '' }}">
        <i data-lucide="toggle-right"></i><span>Feature Flags</span>
    </a>
    <a href="{{ route('portals.admin.cc.modules') }}" class="sidebar-link {{ request()->routeIs('portals.admin.cc.modules') ? 'active' : '' }}">
        <i data-lucide="puzzle"></i><span>Module Toggles</span>
    </a>
    <a href="{{ route('portals.admin.cc.maintenance') }}" class="sidebar-link {{ request()->routeIs('portals.admin.cc.maintenance') ? 'active' : '' }}">
        <i data-lucide="wrench"></i><span>Maintenance</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Audit</div>
    <a href="{{ route('portals.admin.cc.audit') }}" class="sidebar-link {{ request()->routeIs('portals.admin.cc.audit') ? 'active' : '' }}">
        <i data-lucide="scroll-text"></i><span>Admin Action Log</span>
    </a>
    <a href="{{ route('portals.admin.go-live') }}" class="sidebar-link">
        <i data-lucide="rocket"></i><span>Facility Go-Live</span>
    </a>
    <a href="{{ route('portals.admin.security') }}" class="sidebar-link {{ request()->routeIs('portals.admin.security*') ? 'active' : '' }}">
        <i data-lucide="shield-alert"></i><span>Security Ops</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Billing</div>
    <a href="{{ route('portals.admin.subscription') }}" class="sidebar-link {{ request()->routeIs('portals.admin.subscription') || request()->routeIs('portals.admin.subscription.detail') ? 'active' : '' }}">
        <i data-lucide="credit-card"></i><span>Subscriptions</span>
        @php $pastDue = \App\Models\OrganizationSubscription::where('status','past_due')->count(); @endphp
        @if($pastDue > 0)
            <span class="sidebar-badge sidebar-badge--warning">{{ $pastDue }}</span>
        @endif
    </a>
    <a href="{{ route('portals.admin.subscription.plans') }}" class="sidebar-link {{ request()->routeIs('portals.admin.subscription.plans*') ? 'active' : '' }}">
        <i data-lucide="layers"></i><span>Plans</span>
    </a>
    <a href="{{ route('portals.admin.subscription.invoices') }}" class="sidebar-link {{ request()->routeIs('portals.admin.subscription.invoices*') ? 'active' : '' }}">
        <i data-lucide="file-text"></i><span>Invoices</span>
        @php $overdueInv = \App\Models\SubscriptionInvoice::where('status','sent')->where('due_date','<',now())->count(); @endphp
        @if($overdueInv > 0)
            <span class="sidebar-badge sidebar-badge--danger">{{ $overdueInv }}</span>
        @endif
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Integrations</div>
    <a href="{{ route('portals.admin.connect') }}" class="sidebar-link {{ request()->routeIs('portals.admin.connect*') ? 'active' : '' }}">
        <i data-lucide="plug-zap"></i><span>Connect Suite</span>
        @php $pendingConnect = \App\Models\IntegrationClient::where('status','pending')->count(); @endphp
        @if($pendingConnect > 0)
            <span class="sidebar-badge sidebar-badge--warning">{{ $pendingConnect }}</span>
        @endif
    </a>
    <a href="{{ route('portals.admin.bridge') }}" class="sidebar-link {{ request()->routeIs('portals.admin.bridge*') ? 'active' : '' }}">
        <i data-lucide="cable"></i><span>Bridge Agents</span>
    </a>
</div>
@endsection
