<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OpesCare | One Health ID. One Trusted Medical History.')</title>
    <meta name="description" content="@yield('meta_description', 'OpesCare is a digital Health ID and healthcare interoperability platform built to connect patients, hospitals, labs, pharmacies, and insurers.')">
    <meta name="theme-color" content="#0F4C81">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="OpesCare">
    <meta property="og:title" content="@yield('title', 'OpesCare | One Health ID. One Trusted Medical History.')">
    <meta property="og:description" content="@yield('meta_description', 'OpesCare is a digital Health ID and healthcare interoperability platform built to connect patients, hospitals, labs, pharmacies, and insurers.')">
    <meta property="og:image" content="{{ asset('favicon.svg') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'OpesCare | One Health ID. One Trusted Medical History.')">
    <meta name="twitter:description" content="@yield('meta_description', 'OpesCare is a digital Health ID and healthcare interoperability platform.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/public.css') }}">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    @yield('head_scripts')
</head>
<body>

    <!-- Header / Navigation -->
    <header class="header">
        <div class="container header-inner">
            <a href="/" class="logo" style="display:flex;align-items:center;gap:0.5rem;text-decoration:none;">
                <img src="{{ asset('favicon.svg') }}" alt="" width="28" height="28" style="flex-shrink:0;">
                <span>OpesCare</span>
            </a>

            <nav class="nav">
                <div class="nav-dropdown">
                    <a href="{{ route('public.how-it-works') }}" class="nav-link dropdown-trigger">{{ __('landing.nav.product', [], app()->getLocale()) ?: 'Product' }} <i data-lucide="chevron-down" class="icon-xs"></i></a>
                    <div class="dropdown-menu">
                        <a href="{{ route('public.how-it-works') }}" class="dropdown-item"><i data-lucide="git-branch-plus" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>How OpesCare Works</a>
                        <a href="{{ route('public.solutions.patients') }}" class="dropdown-item"><i data-lucide="id-card" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Health ID</a>
                        <a href="{{ route('public.solutions.patients') }}#timeline" class="dropdown-item"><i data-lucide="history" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Patient Timeline</a>
                        <a href="{{ route('public.consent') }}" class="dropdown-item"><i data-lucide="shield-check" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Consent and Access</a>
                        <a href="{{ route('public.care-map') }}" class="dropdown-item"><i data-lucide="map-pin" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Verified Care Map</a>
                    </div>
                </div>
                <div class="nav-dropdown">
                    <a href="{{ route('public.solutions.patients') }}" class="nav-link dropdown-trigger">{{ __('landing.nav.solutions', [], app()->getLocale()) ?: 'Solutions' }} <i data-lucide="chevron-down" class="icon-xs"></i></a>
                    <div class="dropdown-menu">
                        <a href="{{ route('public.solutions.patients') }}" class="dropdown-item"><i data-lucide="user" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>For Patients</a>
                        <a href="{{ route('public.solutions.hospitals') }}" class="dropdown-item"><i data-lucide="hospital" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>For Hospitals</a>
                        <a href="{{ route('public.solutions.pharmacies') }}" class="dropdown-item"><i data-lucide="pill" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>For Pharmacies</a>
                        <a href="{{ route('public.solutions.laboratories') }}" class="dropdown-item"><i data-lucide="flask-conical" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>For Laboratories</a>
                        <a href="{{ route('public.solutions.insurers') }}" class="dropdown-item"><i data-lucide="shield" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>For Insurers</a>
                        <a href="{{ route('public.solutions.public-health') }}" class="dropdown-item"><i data-lucide="globe" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>For Public Health</a>
                    </div>
                </div>
                <div class="nav-dropdown">
                    <a href="{{ route('public.interoperability') }}" class="nav-link dropdown-trigger">{{ __('landing.nav.interop', [], app()->getLocale()) ?: 'Interoperability' }} <i data-lucide="chevron-down" class="icon-xs"></i></a>
                    <div class="dropdown-menu">
                        <a href="{{ route('public.interoperability') }}" class="dropdown-item"><i data-lucide="network" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Overview</a>
                        <a href="{{ route('public.developers') }}" class="dropdown-item"><i data-lucide="braces" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Connect API</a>
                        <a href="{{ route('public.developers') }}#sdk" class="dropdown-item"><i data-lucide="code-2" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>SDK</a>
                        <a href="{{ route('public.developers') }}#widget" class="dropdown-item"><i data-lucide="panel-top" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Widget</a>
                        <a href="{{ route('public.developers') }}#bridge" class="dropdown-item"><i data-lucide="cpu" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Bridge Agent</a>
                        <a href="{{ route('public.developers') }}#lite" class="dropdown-item"><i data-lucide="layout-dashboard" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>OpesCare Lite</a>
                        <a href="{{ route('public.developers') }}#webhooks" class="dropdown-item"><i data-lucide="radio-tower" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Webhooks</a>
                    </div>
                </div>
                <a href="{{ route('public.security') }}" class="nav-link">{{ __('landing.nav.security', [], app()->getLocale()) ?: 'Security' }}</a>
                <div class="nav-dropdown">
                    <a href="{{ route('public.help') }}" class="nav-link dropdown-trigger">{{ __('landing.nav.resources', [], app()->getLocale()) ?: 'Resources' }} <i data-lucide="chevron-down" class="icon-xs"></i></a>
                    <div class="dropdown-menu">
                        <a href="{{ route('public.faq') }}" class="dropdown-item"><i data-lucide="help-circle" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>FAQ</a>
                        <a href="{{ route('public.help') }}" class="dropdown-item"><i data-lucide="book-open" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Help Center</a>
                        <a href="{{ route('public.status') }}" class="dropdown-item"><i data-lucide="activity" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>System Status</a>
                        <a href="{{ route('public.contact') }}" class="dropdown-item"><i data-lucide="headset" style="width:.875rem;height:.875rem;margin-right:.5rem;vertical-align:middle;"></i>Contact Support</a>
                    </div>
                </div>
                <a href="{{ route('public.contact') }}" class="nav-link">{{ __('landing.nav.contact', [], app()->getLocale()) ?: 'Contact' }}</a>
            </nav>
            
            <div class="header-actions">
                <div class="lang-switcher header-lang-switcher" style="display:flex;gap:0.5rem;margin-right:1.5rem;font-size:0.75rem;font-weight:700;">
                    <a href="{{ route('lang.switch', 'en') }}" style="color:{{ app()->getLocale()=='en' ? 'var(--color-primary)' : 'var(--color-text-secondary)' }};text-decoration:none;">EN</a>
                    <span style="color:var(--color-border);">|</span>
                    <a href="{{ route('lang.switch', 'fr') }}" style="color:{{ app()->getLocale()=='fr' ? 'var(--color-primary)' : 'var(--color-text-secondary)' }};text-decoration:none;">FR</a>
                </div>

                <div class="header-desktop-cta" style="display:flex;align-items:center;gap:1rem;">
                    <a href="{{ route('demo.public') }}" style="font-size:0.875rem;font-weight:700;color:var(--color-text-secondary);text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                        <i data-lucide="play-circle" style="width:1rem;height:1rem;"></i> Demo
                    </a>
                    <a href="{{ route('login') }}" style="font-size:0.875rem;font-weight:700;color:var(--color-text-secondary);text-decoration:none;">{{ __('auth.login.title') ?: 'Sign In' }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">{{ __('landing.nav.get_started', [], app()->getLocale()) ?: 'Get Started' }}</a>
                </div>

                <button class="mobile-menu-toggle" id="menuToggle" style="display: none; background: none; border: none; color: var(--color-text-primary); cursor: pointer; margin-left: 1rem;">
                    <i data-lucide="menu"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Drawer Backdrop -->
        <div class="mobile-drawer-backdrop" id="drawerBackdrop"></div>
        <!-- Mobile Drawer -->
        <div class="mobile-drawer" id="mobileDrawer">
            <div class="container">
                <div class="mobile-drawer-header">
                    <span class="logo" style="display:flex;align-items:center;gap:0.5rem;">
                        <img src="{{ asset('favicon.svg') }}" alt="" width="24" height="24" style="flex-shrink:0;">
                        <span>OpesCare</span>
                    </span>
                    <button id="closeMenu" style="background: none; border: none; color: var(--color-text-primary);">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <nav class="mobile-nav">
                    <div class="mobile-nav-group">
                        <span class="mobile-nav-label">{{ __('landing.nav.product', [], app()->getLocale()) ?: 'Product' }}</span>
                        <a href="{{ route('public.how-it-works') }}" class="mobile-nav-link">How OpesCare Works</a>
                        <a href="{{ route('public.solutions.patients') }}" class="mobile-nav-link">Health ID</a>
                        <a href="{{ route('public.consent') }}" class="mobile-nav-link">Consent &amp; Access</a>
                        <a href="{{ route('public.care-map') }}" class="mobile-nav-link">Verified Care Map</a>
                        <a href="{{ route('public.care-map.emergency') }}" class="mobile-nav-link">Emergency Access</a>
                    </div>
                    <div class="mobile-nav-group">
                        <span class="mobile-nav-label">{{ __('landing.nav.solutions', [], app()->getLocale()) ?: 'Solutions' }}</span>
                        <a href="{{ route('public.solutions.patients') }}" class="mobile-nav-link">For Patients</a>
                        <a href="{{ route('public.solutions.hospitals') }}" class="mobile-nav-link">For Hospitals &amp; Clinics</a>
                        <a href="{{ route('public.solutions.pharmacies') }}" class="mobile-nav-link">For Pharmacies</a>
                        <a href="{{ route('public.solutions.laboratories') }}" class="mobile-nav-link">For Laboratories</a>
                        <a href="{{ route('public.solutions.insurers') }}" class="mobile-nav-link">For Insurers</a>
                        <a href="{{ route('public.solutions.public-health') }}" class="mobile-nav-link">For Public Health</a>
                    </div>
                    <div class="mobile-nav-group">
                        <span class="mobile-nav-label">{{ __('landing.nav.interop', [], app()->getLocale()) ?: 'Interoperability' }}</span>
                        <a href="{{ route('public.interoperability') }}" class="mobile-nav-link">Overview</a>
                        <a href="{{ route('public.developers') }}" class="mobile-nav-link">Connect API &amp; SDK</a>
                    </div>
                    <div class="mobile-nav-group">
                        <span class="mobile-nav-label">{{ __('landing.nav.resources', [], app()->getLocale()) ?: 'Resources' }}</span>
                        <a href="{{ route('public.faq') }}" class="mobile-nav-link">FAQ</a>
                        <a href="{{ route('public.help') }}" class="mobile-nav-link">Help Center</a>
                        <a href="{{ route('public.status') }}" class="mobile-nav-link">System Status</a>
                        <a href="{{ route('public.contact') }}" class="mobile-nav-link">Contact Support</a>
                    </div>
                    <div class="mobile-nav-group">
                        <span class="mobile-nav-label">Company</span>
                        <a href="{{ route('public.about') }}" class="mobile-nav-link">About Opesware</a>
                        <a href="{{ route('public.security') }}" class="mobile-nav-link">Security Standards</a>
                        <a href="{{ route('public.privacy') }}" class="mobile-nav-link">Privacy Policy</a>
                        <a href="{{ route('public.terms') }}" class="mobile-nav-link">Terms of Service</a>
                    </div>

                    <div style="display:flex;gap:1rem;margin:1.5rem 0;">
                        <a href="{{ route('lang.switch', 'en') }}" style="font-weight:700;font-size:0.8125rem;color:{{ app()->getLocale()=='en' ? 'var(--color-primary)' : 'var(--color-text-secondary)' }};text-decoration:none;">EN</a>
                        <span style="color:var(--color-border);">|</span>
                        <a href="{{ route('lang.switch', 'fr') }}" style="font-weight:700;font-size:0.8125rem;color:{{ app()->getLocale()=='fr' ? 'var(--color-primary)' : 'var(--color-text-secondary)' }};text-decoration:none;">FR</a>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:0.75rem;margin-top:0.5rem;">
                        <a href="{{ route('login') }}" class="btn btn-secondary" style="text-align:center;width:100%;">{{ __('auth.login.title', [], app()->getLocale()) ?: 'Sign In' }}</a>
                        <a href="{{ route('register') }}" class="btn btn-primary" style="text-align:center;width:100%;">Get Started</a>
                        <a href="{{ route('demo.public') }}" style="display:flex;align-items:center;justify-content:center;gap:0.4rem;font-size:0.875rem;font-weight:600;color:var(--color-text-secondary);text-decoration:none;padding:0.5rem 0;">
                            <i data-lucide="play-circle" style="width:1rem;height:1rem;"></i> {{ __('landing.nav.demo', [], app()->getLocale()) ?: 'Demo' }}
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-grid">

            <!-- Brand col -->
            <div class="footer-logo">
                <a href="{{ route('public.landing') }}" class="logo" style="display:flex;align-items:center;gap:0.5rem;text-decoration:none;">
                    <img src="{{ asset('favicon.svg') }}" alt="OpesCare" width="26" height="26" style="flex-shrink:0;">
                    <span>OpesCare</span>
                </a>
                <p class="text-muted text-sm" style="margin-top:1rem;line-height:1.6;">{{ __('landing.footer.desc', [], app()->getLocale()) }}</p>
                <div style="display:flex;gap:0.75rem;margin-top:1.25rem;">
                    <a href="{{ route('public.status') }}" style="font-size:0.75rem;font-weight:700;color:var(--color-text-secondary);text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                        <span style="width:6px;height:6px;background:#22C55E;border-radius:50%;display:inline-block;"></span>
                        System Status
                    </a>
                </div>
            </div>

            <!-- Product col -->
            <div>
                <h4 style="font-size:0.6875rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-secondary);margin-bottom:1.25rem;">{{ __('landing.footer.col_product', [], app()->getLocale()) }}</h4>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:0.625rem;">
                    <li><a href="{{ route('public.how-it-works') }}" class="footer-link">How OpesCare Works</a></li>
                    <li><a href="{{ route('public.solutions.patients') }}" class="footer-link">Health ID</a></li>
                    <li><a href="{{ route('public.solutions.patients') }}#timeline" class="footer-link">Patient Timeline</a></li>
                    <li><a href="{{ route('public.consent') }}" class="footer-link">Consent Control</a></li>
                    <li><a href="{{ route('public.care-map.emergency') }}" class="footer-link">Emergency Access</a></li>
                    <li><a href="{{ route('public.solutions.pharmacies') }}" class="footer-link">Medication Availability</a></li>
                    <li><a href="{{ route('public.care-map') }}" class="footer-link">Blood Network</a></li>
                </ul>
            </div>

            <!-- Organisations col -->
            <div>
                <h4 style="font-size:0.6875rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-secondary);margin-bottom:1.25rem;">{{ __('landing.footer.col_orgs', [], app()->getLocale()) }}</h4>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:0.625rem;">
                    <li><a href="{{ route('public.solutions.hospitals') }}" class="footer-link">Hospitals &amp; Clinics</a></li>
                    <li><a href="{{ route('public.solutions.laboratories') }}" class="footer-link">Laboratories</a></li>
                    <li><a href="{{ route('public.solutions.pharmacies') }}" class="footer-link">Pharmacies</a></li>
                    <li><a href="{{ route('public.solutions.insurers') }}" class="footer-link">Insurers</a></li>
                    <li><a href="{{ route('public.solutions.public-health') }}" class="footer-link">Public Health Orgs</a></li>
                </ul>
            </div>

            <!-- Developers col -->
            <div>
                <h4 style="font-size:0.6875rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-secondary);margin-bottom:1.25rem;">{{ __('landing.footer.col_devs', [], app()->getLocale()) }}</h4>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:0.625rem;">
                    <li><a href="{{ route('public.developers') }}" class="footer-link">Connect API</a></li>
                    <li><a href="{{ route('public.developers') }}#sdk" class="footer-link">Connect SDK</a></li>
                    <li><a href="{{ route('public.developers') }}#widget" class="footer-link">Connect Widget</a></li>
                    <li><a href="{{ route('public.developers') }}#bridge" class="footer-link">Bridge Agent</a></li>
                    <li><a href="{{ route('public.developers') }}#webhooks" class="footer-link">Webhooks &amp; Alerts</a></li>
                    <li><a href="{{ route('public.interoperability') }}" class="footer-link">Interoperability Overview</a></li>
                </ul>
            </div>

            <!-- Company col -->
            <div>
                <h4 style="font-size:0.6875rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:var(--color-text-secondary);margin-bottom:1.25rem;">{{ __('landing.footer.col_company', [], app()->getLocale()) }}</h4>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:0.625rem;">
                    <li><a href="{{ route('public.about') }}" class="footer-link">About Opesware</a></li>
                    <li><a href="{{ route('public.security') }}" class="footer-link">Security Standards</a></li>
                    <li><a href="{{ route('public.privacy') }}" class="footer-link">Privacy Policy</a></li>
                    <li><a href="{{ route('public.terms') }}" class="footer-link">Terms of Service</a></li>
                    <li><a href="{{ route('public.faq') }}" class="footer-link">FAQ</a></li>
                    <li><a href="{{ route('public.contact') }}" class="footer-link">Partnerships</a></li>
                </ul>
            </div>

        </div>

        <!-- Bottom bar -->
        <div class="container" style="margin-top:var(--space-xl);padding-top:var(--space-lg);border-top:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <p style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--color-text-secondary);">{{ __('landing.footer.copyright', [], app()->getLocale()) }}</p>
            <div style="display:flex;gap:1.25rem;align-items:center;">
                <a href="{{ route('public.privacy') }}" style="font-size:0.75rem;color:var(--color-text-secondary);text-decoration:none;font-weight:600;">Privacy</a>
                <a href="{{ route('public.terms') }}" style="font-size:0.75rem;color:var(--color-text-secondary);text-decoration:none;font-weight:600;">Terms</a>
                <a href="{{ route('public.contact') }}" style="font-size:0.75rem;color:var(--color-text-secondary);text-decoration:none;font-weight:600;">Contact</a>
                <span style="font-size:0.75rem;color:var(--color-text-secondary);">
                    <a href="{{ route('lang.switch', 'en') }}" style="font-weight:700;color:{{ app()->getLocale()=='en' ? 'var(--color-primary)' : 'var(--color-text-secondary)' }};text-decoration:none;">EN</a>
                    &nbsp;/&nbsp;
                    <a href="{{ route('lang.switch', 'fr') }}" style="font-weight:700;color:{{ app()->getLocale()=='fr' ? 'var(--color-primary)' : 'var(--color-text-secondary)' }};text-decoration:none;">FR</a>
                </span>
            </div>
        </div>
    </footer>

    <!-- JS -->
    <script src="{{ asset('js/landing.js') }}"></script>
    @yield('footer_scripts')
</body>
</html>
