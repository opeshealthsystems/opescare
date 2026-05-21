<nav class="portal-sidebar__nav">
    <div class="portal-sidebar__section-title">Developer Portal</div>
    <a href="{{ route('portals.developer.dashboard') }}" class="portal-sidebar__link {{ request()->routeIs('portals.developer.dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="{{ route('portals.developer.apps') }}" class="portal-sidebar__link {{ request()->routeIs('portals.developer.apps*') ? 'active' : '' }}">My Apps</a>
    <a href="{{ route('portals.developer.production_requests') }}" class="portal-sidebar__link {{ request()->routeIs('portals.developer.production_requests*') ? 'active' : '' }}">Production Access</a>

    <div class="portal-sidebar__section-title" style="margin-top:16px;">Resources</div>
    <a href="{{ route('docs.index') }}" class="portal-sidebar__link" target="_blank">API Documentation ↗</a>
    <a href="{{ route('docs.playground') }}" class="portal-sidebar__link" target="_blank">API Playground ↗</a>
</nav>
