<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OpesCare | One Health ID. One Trusted Medical History.')</title>
    <meta name="description" content="@yield('meta_description', 'OpesCare is a digital Health ID and healthcare interoperability platform built to connect patients, hospitals, labs, pharmacies, and insurers.')">
    
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
            <a href="/" class="logo">OpesCare</a>
            
            <nav class="nav">
                <div class="nav-dropdown">
                    <a href="#" class="nav-link dropdown-trigger">Product <i data-lucide="chevron-down" class="icon-xs"></i></a>
                    <div class="dropdown-menu">
                        <a href="{{ route('public.how-it-works') }}" class="dropdown-item">How OpesCare Works</a>
                        <a href="#" class="dropdown-item">Health ID</a>
                        <a href="#" class="dropdown-item">Patient Timeline</a>
                        <a href="#" class="dropdown-item">Consent and Access</a>
                        <a href="#" class="dropdown-item">Medication Availability</a>
                        <a href="#" class="dropdown-item">Blood Availability</a>
                    </div>
                </div>
                <div class="nav-dropdown">
                    <a href="#" class="nav-link dropdown-trigger">Solutions <i data-lucide="chevron-down" class="icon-xs"></i></a>
                    <div class="dropdown-menu">
                        <a href="{{ route('public.solutions.patients') }}" class="dropdown-item">For Patients</a>
                        <a href="{{ route('public.solutions.hospitals') }}" class="dropdown-item">For Hospitals</a>
                        <a href="{{ route('public.solutions.pharmacies') }}" class="dropdown-item">For Pharmacies</a>
                        <a href="{{ route('public.solutions.laboratories') }}" class="dropdown-item">For Laboratories</a>
                        <a href="{{ route('public.solutions.insurers') }}" class="dropdown-item">For Insurers</a>
                        <a href="{{ route('public.solutions.public-health') }}" class="dropdown-item">For Public Health</a>
                    </div>
                </div>
                <div class="nav-dropdown">
                    <a href="#" class="nav-link dropdown-trigger">Interoperability <i data-lucide="chevron-down" class="icon-xs"></i></a>
                    <div class="dropdown-menu">
                        <a href="{{ route('public.interoperability') }}" class="dropdown-item">Overview</a>
                        <a href="{{ route('public.developers') }}" class="dropdown-item">Connect API</a>
                        <a href="#" class="dropdown-item">SDK</a>
                        <a href="#" class="dropdown-item">Widget</a>
                        <a href="#" class="dropdown-item">Bridge Agent</a>
                        <a href="#" class="dropdown-item">OpesCare Lite</a>
                        <a href="#" class="dropdown-item">Webhooks</a>
                    </div>
                </div>
                <a href="{{ route('public.security') }}" class="nav-link">{{ __('landing.nav.security') }}</a>
                <div class="nav-dropdown">
                    <a href="#" class="nav-link dropdown-trigger">Resources <i data-lucide="chevron-down" class="icon-xs"></i></a>
                    <div class="dropdown-menu">
                        <a href="{{ route('public.faq') }}" class="dropdown-item">FAQ</a>
                        <a href="{{ route('public.help') }}" class="dropdown-item">Help Center</a>
                        <a href="{{ route('public.contact') }}" class="dropdown-item">Contact Support</a>
                    </div>
                </div>
                <a href="{{ route('public.contact') }}" class="nav-link">{{ __('landing.nav.contact') }}</a>
            </nav>
            
            <div class="header-actions">
                <div class="lang-switcher" style="display: flex; gap: 0.5rem; margin-right: 1.5rem; font-size: 0.75rem; font-weight: 700;">
                    <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() == 'en' ? 'text-[var(--color-primary)]' : 'text-muted' }}">EN</a>
                    <span class="text-muted">|</span>
                    <a href="{{ route('lang.switch', 'fr') }}" class="{{ app()->getLocale() == 'fr' ? 'text-[var(--color-primary)]' : 'text-muted' }}">FR</a>
                </div>
                
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="{{ route('demo.public') }}" class="text-sm font-bold hover:text-[var(--color-primary)] transition-colors flex items-center">
                        <i data-lucide="play-circle" class="h-4 w-4 mr-1"></i> Demo
                    </a>
                    <a href="{{ route('login') }}" class="text-sm font-bold hover:text-[var(--color-primary)] transition-colors">{{ __('auth.login.title') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                </div>

                <button class="mobile-menu-toggle" id="menuToggle" style="display: none; background: none; border: none; color: var(--color-text-primary); cursor: pointer; margin-left: 1rem;">
                    <i data-lucide="menu"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Drawer -->
        <div class="mobile-drawer" id="mobileDrawer">
            <div class="container">
                <div class="mobile-drawer-header">
                    <span class="logo">OpesCare</span>
                    <button id="closeMenu" style="background: none; border: none; color: var(--color-text-primary);">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <nav class="mobile-nav">
                    <div class="mobile-nav-group">
                        <span class="mobile-nav-label">Product</span>
                        <a href="{{ route('public.how-it-works') }}" class="mobile-nav-link">How OpesCare Works</a>
                        <a href="#" class="mobile-nav-link">Health ID</a>
                        <a href="#" class="mobile-nav-link">Patient Timeline</a>
                    </div>
                    <div class="mobile-nav-group">
                        <span class="mobile-nav-label">Solutions</span>
                        <a href="{{ route('public.solutions.patients') }}" class="mobile-nav-link">For Patients</a>
                        <a href="{{ route('public.solutions.hospitals') }}" class="mobile-nav-link">For Hospitals</a>
                        <a href="{{ route('public.solutions.pharmacies') }}" class="mobile-nav-link">For Pharmacies</a>
                    </div>
                    <a href="{{ route('public.interoperability') }}" class="mobile-nav-link">Interoperability</a>
                    <a href="{{ route('public.security') }}" class="mobile-nav-link">Security</a>
                    <a href="{{ route('public.contact') }}" class="mobile-nav-link">Contact</a>
                    
                    <div style="display: flex; gap: 1rem; margin: 1.5rem 0;">
                        <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() == 'en' ? 'text-[var(--color-primary)]' : 'text-muted' }} font-bold">ENGLISH</a>
                        <a href="{{ route('lang.switch', 'fr') }}" class="{{ app()->getLocale() == 'fr' ? 'text-[var(--color-primary)]' : 'text-muted' }} font-bold">FRANÇAIS</a>
                    </div>
                    <a href="{{ route('demo.public') }}" class="btn btn-primary flex justify-center items-center" style="margin-top: 1rem; width: 100%;">
                        <i data-lucide="play-circle" class="h-4 w-4 mr-2"></i> {{ __('landing.nav.demo') }}
                    </a>
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
            <div class="footer-logo">
                <a href="/" class="logo">OpesCare</a>
                <p class="text-muted text-sm mt-4" style="margin-top: 1rem;">{{ __('landing.footer.desc') }}</p>
            </div>
            
            <div>
                <h4 class="uppercase tracking-widest text-[10px] font-black mb-6 text-muted">{{ __('landing.footer.col_product') }}</h4>
                <ul class="space-y-3 text-sm text-muted">
                    @foreach(__('landing.footer.product_links') as $link)
                        <li><a href="#" class="hover:text-[var(--color-primary)]">{{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>
            
            <div>
                <h4 class="uppercase tracking-widest text-[10px] font-black mb-6 text-muted">{{ __('landing.footer.col_orgs') }}</h4>
                <ul class="space-y-3 text-sm text-muted">
                    @foreach(__('landing.footer.org_links') as $link)
                        <li><a href="#" class="hover:text-[var(--color-primary)]">{{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>
            
            <div>
                <h4 class="uppercase tracking-widest text-[10px] font-black mb-6 text-muted">{{ __('landing.footer.col_devs') }}</h4>
                <ul class="space-y-3 text-sm text-muted">
                    @foreach(__('landing.footer.dev_links') as $link)
                        <li><a href="#" class="hover:text-[var(--color-primary)]">{{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="uppercase tracking-widest text-[10px] font-black mb-6 text-muted">{{ __('landing.footer.col_company') }}</h4>
                <ul class="space-y-3 text-sm text-muted">
                    @foreach(__('landing.footer.company_links') as $link)
                        <li><a href="#" class="hover:text-[var(--color-primary)]">{{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <div class="container" style="margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid var(--color-border); text-align: center;">
            <p class="text-[10px] font-black uppercase tracking-widest text-muted">{{ __('landing.footer.copyright') }}</p>
        </div>
    </footer>

    <!-- JS -->
    <script src="{{ asset('js/landing.js') }}"></script>
    @yield('footer_scripts')
</body>
</html>
