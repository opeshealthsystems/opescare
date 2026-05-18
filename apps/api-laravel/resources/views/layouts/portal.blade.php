<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OpesCare Portal')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @yield('head')
</head>
<body class="portal-body">

<div class="portal-wrap">

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>

    <!-- Sidebar -->
    <aside class="portal-sidebar" id="portal-sidebar" role="navigation" aria-label="Portal navigation">

        <a href="{{ url('/') }}" class="sidebar-brand" aria-label="OpesCare Home">
            <div class="sidebar-brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <div>
                <div class="sidebar-brand-name">OpesCare</div>
                <div class="sidebar-brand-sub">@yield('sidebar_user_role', 'Portal')</div>
            </div>
        </a>

        <div class="sidebar-nav">
            @yield('sidebar_role_badge')
            <div style="margin-bottom: var(--p-space-3);"></div>
            @yield('sidebar_nav')
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar" aria-hidden="true">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div style="min-width:0;">
                    <div class="sidebar-user-name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="sidebar-user-role">@yield('sidebar_user_role', 'Portal')</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Area -->
    <div class="portal-main" id="portal-main">

        <!-- Topbar -->
        <header class="portal-topbar" role="banner">
            <button class="topbar-menu-btn" id="sidebar-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="portal-sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>

            <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
                <a href="@yield('breadcrumb_home_url', url('/'))">@yield('breadcrumb_home', 'Portal')</a>
                @hasSection('breadcrumb_section')
                    <span class="topbar-breadcrumb-sep" aria-hidden="true">/</span>
                    <span class="topbar-breadcrumb-current">@yield('breadcrumb_section')</span>
                @endif
            </nav>

            <div class="topbar-actions">
                @php
                    $locale = app()->getLocale();
                    $otherLocale = $locale === 'fr' ? 'en' : 'fr';
                    $otherLabel  = $locale === 'fr' ? 'EN' : 'FR';
                @endphp
                <a href="{{ url('/lang/' . $otherLocale) }}" class="topbar-lang" aria-label="Switch language to {{ $otherLabel }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <span>{{ $otherLabel }}</span>
                </a>

                <a href="{{ route('public.help') }}" class="topbar-icon-btn" aria-label="Help">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </a>
            </div>
        </header>

        @yield('patient_banner')

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="flash-bar" role="alert">
            <div class="alert alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <div>{{ session('success') }}</div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="flash-bar" role="alert">
            <div class="alert alert-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>{{ session('error') }}</div>
            </div>
        </div>
        @endif

        <!-- Page Content -->
        <main class="portal-content" id="main-content" role="main">
            @yield('content')
        </main>

    </div><!-- /.portal-main -->

</div><!-- /.portal-wrap -->

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<!-- Portal JS -->
<script>
(function() {
    var sidebar  = document.getElementById('portal-sidebar');
    var overlay  = document.getElementById('sidebar-overlay');
    var toggleBtn = document.getElementById('sidebar-toggle');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
        toggleBtn.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', function() {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    if (overlay) overlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) closeSidebar();
    });

    // Mark active sidebar link
    var currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(function(link) {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
})();
</script>

@yield('scripts')

</body>
</html>
