<nav class="portal-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i data-lucide="plug-zap"></i>
            <span>Connect Suite</span>
        </div>
    </div>

    <div class="sidebar-section-label">OVERVIEW</div>
    <a href="{{ route('portals.admin.connect') }}"
       class="sidebar-link {{ request()->routeIs('portals.admin.connect') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i>
        <span>Dashboard</span>
    </a>

    <div class="sidebar-section-label">MANAGEMENT</div>
    <a href="{{ route('portals.admin.connect.clients') }}"
       class="sidebar-link {{ request()->routeIs('portals.admin.connect.clients') ? 'active' : '' }}">
        <i data-lucide="app-window"></i>
        <span>API Clients</span>
        @php $pending = \App\Models\IntegrationClient::where('status','pending')->count(); @endphp
        @if($pending > 0)
            <span class="sidebar-badge sidebar-badge--warning">{{ $pending }}</span>
        @endif
    </a>

    <a href="{{ route('portals.admin.connect.tokens') }}"
       class="sidebar-link {{ request()->routeIs('portals.admin.connect.tokens') ? 'active' : '' }}">
        <i data-lucide="key-round"></i>
        <span>SDK Tokens</span>
    </a>

    <a href="{{ route('portals.admin.connect.webhooks') }}"
       class="sidebar-link {{ request()->routeIs('portals.admin.connect.webhooks') ? 'active' : '' }}">
        <i data-lucide="webhook"></i>
        <span>Webhooks</span>
        @php $failed = \App\Models\WebhookDeliveryLog::where('status','failed')->count(); @endphp
        @if($failed > 0)
            <span class="sidebar-badge sidebar-badge--danger">{{ $failed }}</span>
        @endif
    </a>

    <a href="{{ route('portals.admin.connect.widget') }}"
       class="sidebar-link {{ request()->routeIs('portals.admin.connect.widget') ? 'active' : '' }}">
        <i data-lucide="layout-panel-left"></i>
        <span>Widget Embed</span>
    </a>

    <div class="sidebar-section-label">NAVIGATE</div>
    <a href="{{ route('portals.admin.cc') }}" class="sidebar-link">
        <i data-lucide="arrow-left"></i>
        <span>Control Center</span>
    </a>
    <a href="{{ route('portals.admin') }}" class="sidebar-link">
        <i data-lucide="home"></i>
        <span>Admin Home</span>
    </a>
</nav>
