@extends('layouts.public')

@section('title', 'OpesCare | One Health ID. One Trusted Medical History.')

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-content">
                <div class="badge">{{ __('landing.hero.badge') }}</div>
                <h1>{{ __('landing.hero.title') }}</h1>
                <p class="hero-subtitle">{{ __('landing.hero.subtitle') }}</p>
                <div class="hero-actions">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">{{ __('landing.hero.cta_primary') }}</a>
                    <a href="{{ route('public.how-it-works') }}" class="btn btn-secondary btn-lg">{{ __('landing.hero.cta_secondary') }}</a>
                </div>
                <div class="hero-trust">
                    <div class="trust-item"><i data-lucide="shield-check"></i> {{ __('landing.hero.trust1') }}</div>
                    <div class="trust-item"><i data-lucide="lock"></i> {{ __('landing.hero.trust2') }}</div>
                    <div class="trust-item"><i data-lucide="languages"></i> {{ __('landing.hero.trust3') }}</div>
                </div>
            </div>
            <div class="hero-visual">
                <!-- Product Mockup Simulation -->
                <div class="mockup-container">
                    <div class="mockup-card card-health-id">
                        <div class="card-header">
                            <i data-lucide="id-card"></i>
                            <span>Health ID</span>
                        </div>
                        <div class="card-body">
                            <div class="qr-placeholder"></div>
                            <div class="card-info">
                                <div class="info-line"></div>
                                <div class="info-line short"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mockup-card card-consent">
                        <i data-lucide="shield-check" class="text-success"></i>
                        <span>Consent Approved</span>
                    </div>
                    <div class="mockup-card card-timeline">
                        <div class="timeline-item">
                            <div class="dot"></div>
                            <div class="line"></div>
                        </div>
                        <div class="timeline-item">
                            <div class="dot active"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Strip -->
    <section class="trust-strip">
        <div class="container strip-grid">
            <div class="strip-item">
                <div class="strip-icon"><i data-lucide="id-card"></i></div>
                <div class="strip-text">
                    <strong>{{ __('landing.trust_strip.item1_title') }}</strong>
                    <span>{{ __('landing.trust_strip.item1_desc') }}</span>
                </div>
            </div>
            <div class="strip-item">
                <div class="strip-icon"><i data-lucide="history"></i></div>
                <div class="strip-text">
                    <strong>{{ __('landing.trust_strip.item2_title') }}</strong>
                    <span>{{ __('landing.trust_strip.item2_desc') }}</span>
                </div>
            </div>
            <div class="strip-item">
                <div class="strip-icon"><i data-lucide="shield-check"></i></div>
                <div class="strip-text">
                    <strong>{{ __('landing.trust_strip.item3_title') }}</strong>
                    <span>{{ __('landing.trust_strip.item3_desc') }}</span>
                </div>
            </div>
            <div class="strip-item">
                <div class="strip-icon"><i data-lucide="heart-pulse"></i></div>
                <div class="strip-text">
                    <strong>{{ __('landing.trust_strip.item4_title') }}</strong>
                    <span>{{ __('landing.trust_strip.item4_desc') }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Problem Section -->
    <section class="section section-muted">
        <div class="container text-center mb-12">
            <h2>{{ __('landing.problem.title') }}</h2>
            <p class="section-subtitle">{{ __('landing.problem.subtitle') }}</p>
        </div>
        <div class="container grid-3">
            @foreach(['lost_books', 'repeated_tests', 'blind_treatment', 'disconnected', 'availability', 'weak_audit'] as $key)
                <div class="card card-problem">
                    <i data-lucide="{{ $key == 'lost_books' ? 'file-x' : ($key == 'repeated_tests' ? 'flask-conical' : ($key == 'blind_treatment' ? 'badge-alert' : ($key == 'disconnected' ? 'cable' : ($key == 'availability' ? 'siren' : 'clipboard-check')))) }}"></i>
                    <h3>{{ __("landing.problem.{$key}_title") }}</h3>
                    <p>{{ __("landing.problem.{$key}_desc") }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Solution Section -->
    <section class="section">
        <div class="container grid-2 items-center">
            <div>
                <h2>{{ __('landing.solution.title') }}</h2>
                <p class="text-lg text-muted mb-8">{{ __('landing.solution.desc') }}</p>
                <ul class="solution-list">
                    <li><i data-lucide="check-circle"></i> {{ __('landing.solution.pill1') }}</li>
                    <li><i data-lucide="check-circle"></i> {{ __('landing.solution.pill2') }}</li>
                    <li><i data-lucide="check-circle"></i> {{ __('landing.solution.pill3') }}</li>
                    <li><i data-lucide="check-circle"></i> {{ __('landing.solution.pill4') }}</li>
                </ul>
            </div>
            <div class="solution-visual">
                <div class="hub-graphic">
                    <div class="hub-center">OC</div>
                    <div class="hub-node n1">H</div>
                    <div class="hub-node n2">L</div>
                    <div class="hub-node n3">P</div>
                    <div class="hub-node n4">I</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="section section-dark text-center">
        <div class="container">
            <h2 class="text-white">{{ __('landing.footer_cta.title') }}</h2>
            <p class="text-muted-light mb-8">{{ __('landing.footer_cta.subtitle') }}</p>
            <div style="display: flex; justify-content: center; gap: 1rem;">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">{{ __('landing.footer_cta.cta_primary') }}</a>
                <a href="{{ route('public.contact') }}" class="btn btn-secondary btn-lg">{{ __('landing.footer_cta.cta_secondary') }}</a>
            </div>
        </div>
    </section>
@endsection
