<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OpesCare | Secure Gateway')</title>
    <meta name="description" content="Access your OpesCare secure clinical profile, Health ID registry, or organizational workspace.">
    <meta name="theme-color" content="#0F4C81">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-body">

    <div class="auth-split-container">
        
        <!-- Left Sidebar: Medical Trust Points & Branding -->
        <aside class="auth-sidebar">
            <div class="sidebar-header">
                <a href="/" class="sidebar-logo">
                    <i data-lucide="shield-check" style="width: 2.25rem; height: 2.25rem; color: #38BDF8;"></i>
                    <span>OpesCare</span>
                </a>
                <div class="sidebar-tagline">{{ __('onboarding.brand.tagline') }}</div>
            </div>
            
            <div class="sidebar-content">
                <h2 class="sidebar-headline">{{ __('onboarding.login.welcome_back') }}</h2>
                
                <div class="trust-bullets">
                    <div class="trust-bullet">
                        <div class="bullet-icon">
                            <i data-lucide="badge-check"></i>
                        </div>
                        <div class="bullet-text">
                            <strong>{{ __('onboarding.brand.bullet_1_title') }}</strong>
                            <p>{{ __('onboarding.brand.bullet_1_desc') }}</p>
                        </div>
                    </div>
                    
                    <div class="trust-bullet">
                        <div class="bullet-icon">
                            <i data-lucide="shield-plus"></i>
                        </div>
                        <div class="bullet-text">
                            <strong>{{ __('onboarding.brand.bullet_2_title') }}</strong>
                            <p>{{ __('onboarding.brand.bullet_2_desc') }}</p>
                        </div>
                    </div>
                    
                    <div class="trust-bullet">
                        <div class="bullet-icon">
                            <i data-lucide="code-2"></i>
                        </div>
                        <div class="bullet-text">
                            <strong>{{ __('onboarding.brand.bullet_3_title') }}</strong>
                            <p>{{ __('onboarding.brand.bullet_3_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-footer">
                <div class="sidebar-safety">
                    <i data-lucide="shield-alert"></i>
                    <div>
                        <strong>{{ __('onboarding.brand.safety_disclaimer') }}</strong>
                        <p>{{ __('onboarding.brand.shield_note') }}</p>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Right Content Plane: Active View Form -->
        <main class="auth-main">
            <div class="auth-header-nav">
                <!-- Dynamic Language Switcher -->
                <div class="auth-lang-selector">
                    <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">English</a>
                    <span style="color: var(--auth-border);">|</span>
                    <a href="{{ route('lang.switch', 'fr') }}" class="{{ app()->getLocale() == 'fr' ? 'active' : '' }}">Français</a>
                </div>
            </div>
            
            <div class="auth-card-wrapper">
                @yield('content')
            </div>
            
            <!-- Support Footer Fallback -->
            <div class="auth-footer-links" style="margin-top: 3rem; font-size: 0.8rem; text-align: center; color: var(--auth-text-muted);">
                <p>{{ __('onboarding.brand.need_help') }} 
                    <a href="{{ route('public.contact') }}" style="color: var(--auth-primary); font-weight: 700; text-decoration: none;">
                        {{ __('onboarding.brand.contact_support') }}
                    </a>
                </p>
            </div>
        </main>
        
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>
