<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OpesCare Portal')</title>
    <meta name="description" content="@yield('meta_description', 'OpesCare secure clinical portal.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Portal CSS -->
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @yield('head')
</head>
<body class="portal-body">

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="portal-shell">

    <!-- ==============================
         Sidebar Navigation
         ============================== -->
    <nav class="portal-sidebar" id="portalSidebar" role="navigation" aria-label="{{ __('public.portal.nav_label', [], app()->getLocale()) ?? 'Portal Navigation' }}">

        <!-- Brand -->
        <a href="{{ route('landing') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i data-lucide="shield-check"></i>
            </div>
            <span class="sidebar-brand-name">OpesCare</span>
        </a>

        <!-- Role Badge -->
        @yield('sidebar_role_badge')

        <!-- Navigation Links -->
        <div class="sidebar-nav">
            @yield('sidebar_nav')
        </div>

        <!-- User Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="sidebar-user-role">@yield('sidebar_user_role', 'OpesCare User')</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-logout" title="Sign out" aria-label="Sign out">
                        <i data-lucide="log-out"></i>
                    </button>
                </form>
            </div>
        </div>

    </nav>

    <!-- ==============================
         Main Content
         ============================== -->
    <div class="portal-main">

        <!-- Top Bar -->
        <header class="portal-topbar" role="banner">
            <div class="topbar-left">
                <button class="topbar-menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">
                    <i data-lucide="menu"></i>
                </button>
                <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
                    <a href="@yield('breadcrumb_home_url', '#')">@yield('breadcrumb_home', 'Portal')</a>
                    @hasSection('breadcrumb_section')
                        <span class="sep">/</span>
                        <span class="current">@yield('breadcrumb_section')</span>
                    @endif
                </nav>
            </div>

            <div class="topbar-right">
                <!-- Language Switcher -->
                <div class="topbar-lang" aria-label="Language selector">
                    <a href="{{ url()->current() }}?lang=en"
                       class="{{ app()->getLocale() === 'en' ? 'active' : '' }}"
                       hreflang="en">EN</a>
                    <a href="{{ url()->current() }}?lang=fr"
                       class="{{ app()->getLocale() === 'fr' ? 'active' : '' }}"
                       hreflang="fr">FR</a>
                </div>

                <!-- Notifications -->
                <button class="topbar-icon-btn" aria-label="Notifications">
                    <i data-lucide="bell"></i>
                    @yield('notification_dot')
                </button>

                <!-- Help -->
                <a href="{{ route('public.help') }}" class="topbar-icon-btn" aria-label="Help">
                    <i data-lucide="help-circle"></i>
                </a>
            </div>
        </header>

        <!-- Patient Banner (conditionally shown) -->
        @yield('patient_banner')

        <!-- Alerts -->
        @if(session('success'))
            <div style="padding: 0 var(--p-space-8); margin-top: var(--p-space-4);">
                <div class="alert alert-success" role="alert">
                    <i data-lucide="check-circle-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div style="padding: 0 var(--p-space-8); margin-top: var(--p-space-4);">
                <div class="alert alert-danger" role="alert">
                    <i data-lucide="triangle-alert"></i>
                    <div>{{ session('error') }}</div>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <main class="portal-content" role="main" id="main-content">
            @yield('content')
        </main>

    </div><!-- /.portal-main -->

</div><!-- /.portal-shell -->

<!-- Portal JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Init Lucide icons
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // Sidebar toggle (mobile)
    const menuToggle = document.getElementById('menuToggle');
    const sidebar    = document.getElementById('portalSidebar');
    const overlay    = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        menuToggle && menuToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        menuToggle && menuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) closeSidebar();
    });

    // Mark active sidebar link
    const links = document.querySelectorAll('.sidebar-link');
    links.forEach(link => {
        if (link.getAttribute('href') && link.getAttribute('href') === window.location.pathname) {
            link.classList.add('active');
        }
    });
});
</script>

@yield('scripts')

</body>
</html>
