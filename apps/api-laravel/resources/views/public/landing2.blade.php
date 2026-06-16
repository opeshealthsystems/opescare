@extends('layouts.public')

@section('title', __('landing.hero.title', [], app()->getLocale()))

@section('head_scripts')
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing2.css') }}">
@endsection

@section('content')
<a href="#l2-main-content" class="l2-skip-link">{{ __('landing.l2_skip_link', [], app()->getLocale()) }}</a>
<div class="l2">

{{-- Mesh background — 3 brand-blue orbs only --}}
<div class="l2-mesh" aria-hidden="true">
    <div class="l2-orb l2-orb-1"></div>
    <div class="l2-orb l2-orb-2"></div>
    <div class="l2-orb l2-orb-3"></div>
</div>

{{-- ═══════════════════════════════════════
     1. HERO SLIDER v2  (l2h- classes, mobile-first)
     ═══════════════════════════════════════ --}}
<span id="l2hAnnounce" class="sr-only" aria-live="polite" aria-atomic="true"></span>
<section class="l2h" role="region" aria-roledescription="carousel" aria-label="{{ __('landing.l2_carousel_aria', [], app()->getLocale()) }}">
    <div class="l2h-wrap">
        <div class="l2h-slides" id="l2hSlides">

            {{-- Slide 1: Health ID --}}
            <div class="l2h-slide" role="group" aria-roledescription="slide" aria-label="{{ __('landing.l2_slide1_aria', [], app()->getLocale()) }}">
                <div class="l2h-container">
                    <div class="l2h-copy">
                        <span class="l2h-badge">
                            <i data-lucide="shield-check"></i>
                            {{ __('landing.hero.badge', [], app()->getLocale()) }}
                        </span>
                        <h1>{{ __('landing.hero.title', [], app()->getLocale()) }}</h1>
                        <p>{{ __('landing.hero.subtitle', [], app()->getLocale()) }}</p>
                        <div class="l2h-btns">
                            <a href="{{ route('public.contact') }}" class="l2h-btn l2h-btn-primary">
                                <i data-lucide="handshake"></i>
                                {{ __('landing.hero.cta_primary', [], app()->getLocale()) }}
                            </a>
                            <a href="{{ route('public.how-it-works') }}" class="l2h-btn l2h-btn-ghost">
                                <i data-lucide="play-circle"></i>
                                {{ __('landing.hero.cta_secondary', [], app()->getLocale()) }}
                            </a>
                        </div>
                        <div class="l2h-trust">
                            <div class="l2h-trust-item">
                                <i data-lucide="check-circle"></i>
                                {{ __('landing.hero.trust1', [], app()->getLocale()) }}
                            </div>
                            <div class="l2h-trust-item">
                                <i data-lucide="check-circle"></i>
                                {{ __('landing.hero.trust2', [], app()->getLocale()) }}
                            </div>
                        </div>
                    </div>
                    <div class="l2h-visual" aria-hidden="true">
                        <div class="l2h-card">
                            <div class="l2h-card-top">
                                <div class="l2h-card-label">
                                    <i data-lucide="credit-card"></i>
                                    {{ __('landing.hero_card.label_health_id', [], app()->getLocale()) }}
                                </div>
                                <span class="l2h-card-badge">{{ __('landing.l2_verified', [], app()->getLocale()) }}</span>
                            </div>
                            <div class="l2h-card-id">{{ __('landing.hero_card.demo_id', [], app()->getLocale()) }}</div>
                            <div class="l2h-card-sub">{{ __('landing.hero_card.secure_label', [], app()->getLocale()) }}</div>
                            <div class="l2h-card-item">
                                <i data-lucide="check-circle"></i>
                                <span>{{ __('landing.hero_card.label_verified', [], app()->getLocale()) }} · {{ __('landing.hero_card.consent_approved', [], app()->getLocale()) }}</span>
                            </div>
                            <div class="l2h-card-item">
                                <i data-lucide="clock"></i>
                                <span>{{ __('landing.hero_card.timeline_title', [], app()->getLocale()) }} · {{ __('landing.hero_card.timeline_ago', [], app()->getLocale()) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 2: Consent & Access --}}
            <div class="l2h-slide" role="group" aria-roledescription="slide" aria-label="{{ __('landing.l2_slide2_aria', [], app()->getLocale()) }}" aria-hidden="true">
                <div class="l2h-container">
                    <div class="l2h-copy">
                        <span class="l2h-badge">
                            <i data-lucide="lock"></i>
                            {{ __('landing.l2_consent_badge', [], app()->getLocale()) }}
                        </span>
                        <h1>{{ __('landing.l2_consent_h1', [], app()->getLocale()) }}</h1>
                        <p>{{ __('landing.l2_consent_p', [], app()->getLocale()) }}</p>
                        <div class="l2h-btns">
                            <a href="{{ route('public.contact') }}" class="l2h-btn l2h-btn-primary">
                                <i data-lucide="handshake"></i>
                                {{ __('landing.l2_request_demo', [], app()->getLocale()) }}
                            </a>
                            <a href="{{ route('public.how-it-works') }}" class="l2h-btn l2h-btn-ghost">
                                <i data-lucide="shield"></i>
                                {{ __('landing.l2_consent_cta_ghost', [], app()->getLocale()) }}
                            </a>
                        </div>
                    </div>
                    <div class="l2h-visual" aria-hidden="true">
                        <div class="l2h-card">
                            <div class="l2h-card-top">
                                <div class="l2h-card-label">
                                    <i data-lucide="shield-check"></i>
                                    {{ __('landing.l2_consent_card_label', [], app()->getLocale()) }}
                                </div>
                                <span class="l2h-card-badge l2h-badge-warn">{{ __('landing.l2_pending', [], app()->getLocale()) }}</span>
                            </div>
                            <div class="l2h-card-alert">
                                <i data-lucide="alert-triangle"></i>
                                {{ __('landing.l2_consent_card_alert', [], app()->getLocale()) }}
                            </div>
                            <div class="l2h-card-item l2h-scope-row">
                                <span>{{ __('landing.l2_scope_demographics', [], app()->getLocale()) }}</span><span class="l2h-toggle on"></span>
                            </div>
                            <div class="l2h-card-item l2h-scope-row">
                                <span>{{ __('landing.l2_scope_prescriptions', [], app()->getLocale()) }}</span><span class="l2h-toggle on"></span>
                            </div>
                            <div class="l2h-card-item l2h-scope-row">
                                <span>{{ __('landing.l2_scope_lab', [], app()->getLocale()) }}</span><span class="l2h-toggle off"></span>
                            </div>
                            <div class="l2h-card-actions">
                                <button class="l2h-card-deny">{{ __('landing.l2_card_deny', [], app()->getLocale()) }}</button>
                                <button class="l2h-card-approve">{{ __('landing.l2_card_approve', [], app()->getLocale()) }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 3: Emergency Access --}}
            <div class="l2h-slide" role="group" aria-roledescription="slide" aria-label="{{ __('landing.l2_slide3_aria', [], app()->getLocale()) }}" aria-hidden="true">
                <div class="l2h-container">
                    <div class="l2h-copy">
                        <span class="l2h-badge">
                            <i data-lucide="siren"></i>
                            {{ __('landing.l2_em_badge', [], app()->getLocale()) }}
                        </span>
                        <h1>{{ __('landing.l2_em_h1', [], app()->getLocale()) }}</h1>
                        <p>{{ __('landing.l2_em_p', [], app()->getLocale()) }}</p>
                        <div class="l2h-btns">
                            <a href="{{ route('public.contact') }}" class="l2h-btn l2h-btn-primary">
                                <i data-lucide="handshake"></i>
                                {{ __('landing.l2_request_demo', [], app()->getLocale()) }}
                            </a>
                            <a href="{{ route('public.how-it-works') }}" class="l2h-btn l2h-btn-ghost">
                                <i data-lucide="file-text"></i>
                                {{ __('landing.l2_em_cta_ghost', [], app()->getLocale()) }}
                            </a>
                        </div>
                    </div>
                    <div class="l2h-visual" aria-hidden="true">
                        <div class="l2h-card l2h-card-emr">
                            <div class="l2h-emr-header">
                                <i data-lucide="alert-octagon"></i>
                                {{ __('landing.l2_em_override', [], app()->getLocale()) }}
                            </div>
                            <div class="l2h-card-item l2h-em-row">
                                <span>{{ __('landing.l2_em_blood_group', [], app()->getLocale()) }}</span>
                                <span class="l2h-em-val danger">{{ __('landing.l2_em_blood_val', [], app()->getLocale()) }}</span>
                            </div>
                            <div class="l2h-card-item l2h-em-row">
                                <span>{{ __('landing.l2_em_allergies', [], app()->getLocale()) }}</span>
                                <span class="l2h-em-val warn">{{ __('landing.l2_em_allergies_val', [], app()->getLocale()) }}</span>
                            </div>
                            <div class="l2h-card-item l2h-em-row">
                                <span>{{ __('landing.l2_em_conditions', [], app()->getLocale()) }}</span>
                                <span class="l2h-em-val">{{ __('landing.l2_em_conditions_val', [], app()->getLocale()) }}</span>
                            </div>
                            <div class="l2h-card-item" style="margin-top:.35rem;font-size:.7rem;color:rgba(255,255,255,.4);">
                                <i data-lucide="shield-alert"></i>
                                <span>{{ __('landing.l2_em_audit_notice', [], app()->getLocale()) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 4: Connected Care Network --}}
            <div class="l2h-slide" role="group" aria-roledescription="slide" aria-label="{{ __('landing.l2_slide4_aria', [], app()->getLocale()) }}" aria-hidden="true">
                <div class="l2h-container">
                    <div class="l2h-copy">
                        <span class="l2h-badge">
                            <i data-lucide="network"></i>
                            {{ __('landing.l2_net_badge', [], app()->getLocale()) }}
                        </span>
                        <h1>{{ __('landing.l2_net_h1', [], app()->getLocale()) }}</h1>
                        <p>{{ __('landing.l2_net_p', [], app()->getLocale()) }}</p>
                        <div class="l2h-btns">
                            <a href="{{ route('public.contact') }}" class="l2h-btn l2h-btn-primary">
                                <i data-lucide="handshake"></i>
                                {{ __('landing.hero.cta_primary', [], app()->getLocale()) }}
                            </a>
                            <a href="{{ route('public.interoperability') }}" class="l2h-btn l2h-btn-ghost">
                                <i data-lucide="plug"></i>
                                {{ __('landing.l2_net_cta_ghost', [], app()->getLocale()) }}
                            </a>
                        </div>
                    </div>
                    <div class="l2h-visual" aria-hidden="true">
                        <div class="l2h-net-grid">
                            <div class="l2h-net-node"><i data-lucide="hospital"></i><span>{{ __('landing.l2_net_node_hospital', [], app()->getLocale()) }}</span></div>
                            <div class="l2h-net-node"><i data-lucide="flask-conical"></i><span>{{ __('landing.l2_net_node_lab', [], app()->getLocale()) }}</span></div>
                            <div class="l2h-net-node l2h-net-center"><i data-lucide="shield"></i><span>{{ __('landing.l2_net_node_health_id', [], app()->getLocale()) }}</span></div>
                            <div class="l2h-net-node"><i data-lucide="pill"></i><span>{{ __('landing.l2_net_node_pharmacy', [], app()->getLocale()) }}</span></div>
                            <div class="l2h-net-node"><i data-lucide="building-2"></i><span>{{ __('landing.l2_net_node_insurer', [], app()->getLocale()) }}</span></div>
                            <div class="l2h-net-node"><i data-lucide="user"></i><span>{{ __('landing.l2_net_node_patient', [], app()->getLocale()) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 5: Connected Journey --}}
            <div class="l2h-slide" role="group" aria-roledescription="slide" aria-label="{{ __('landing.l2_slide5_aria', [], app()->getLocale()) }}" aria-hidden="true">
                <div class="l2h-container">
                    <div class="l2h-copy">
                        <span class="l2h-badge">
                            <i data-lucide="route"></i>
                            {{ __('landing.l2_journey_badge', [], app()->getLocale()) }}
                        </span>
                        <h1>{{ __('landing.l2_journey_h1', [], app()->getLocale()) }}</h1>
                        <p>{{ __('landing.l2_journey_p', [], app()->getLocale()) }}</p>
                        <div class="l2h-btns">
                            <a href="{{ route('public.contact') }}" class="l2h-btn l2h-btn-primary">
                                <i data-lucide="handshake"></i>
                                {{ __('landing.l2_request_demo', [], app()->getLocale()) }}
                            </a>
                            <a href="{{ route('public.how-it-works') }}" class="l2h-btn l2h-btn-ghost">
                                <i data-lucide="arrow-right"></i>
                                {{ __('landing.l2_journey_cta_ghost', [], app()->getLocale()) }}
                            </a>
                        </div>
                        <div class="l2h-trust">
                            <div class="l2h-trust-item">
                                <i data-lucide="check-circle"></i>
                                {{ __('landing.l2_journey_trust1', [], app()->getLocale()) }}
                            </div>
                            <div class="l2h-trust-item">
                                <i data-lucide="check-circle"></i>
                                {{ __('landing.l2_journey_trust2', [], app()->getLocale()) }}
                            </div>
                        </div>
                    </div>
                    <div class="l2h-visual" aria-hidden="true">
                        <div class="l2h-hub">
                            <div class="l2h-hub-pulse"></div>
                            <div class="l2h-hub-center">
                                <i data-lucide="shield-check"></i>
                                <span>{{ __('landing.l2_hub_health_id', [], app()->getLocale()) }}</span>
                            </div>
                            {{-- Connecting lines --}}
                            <div class="l2h-hub-line hl1"></div>
                            <div class="l2h-hub-line hl2"></div>
                            <div class="l2h-hub-line hl3"></div>
                            <div class="l2h-hub-line hl4"></div>
                            <div class="l2h-hub-line hl5"></div>
                            <div class="l2h-hub-line hl6"></div>
                            {{-- Surrounding facility nodes --}}
                            <div class="l2h-hub-node hn1"><i data-lucide="hospital"></i>{{ __('landing.l2_net_node_hospital', [], app()->getLocale()) }}</div>
                            <div class="l2h-hub-node hn2"><i data-lucide="flask-conical"></i>{{ __('landing.l2_net_node_lab', [], app()->getLocale()) }}</div>
                            <div class="l2h-hub-node hn3"><i data-lucide="pill"></i>{{ __('landing.l2_net_node_pharmacy', [], app()->getLocale()) }}</div>
                            <div class="l2h-hub-node hn4"><i data-lucide="building-2"></i>{{ __('landing.l2_net_node_insurer', [], app()->getLocale()) }}</div>
                            <div class="l2h-hub-node hn5"><i data-lucide="stethoscope"></i>{{ __('landing.l2_net_node_clinic', [], app()->getLocale()) }}</div>
                            <div class="l2h-hub-node hn6"><i data-lucide="user-round"></i>{{ __('landing.l2_net_node_patient', [], app()->getLocale()) }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /.l2h-slides --}}
    </div>{{-- /.l2h-wrap --}}

    {{-- Nav bar --}}
    <div class="l2h-nav">
        <button class="l2h-arrow" id="l2hPrev" onclick="l2hPrev()" disabled aria-label="{{ __('landing.aria_prev_slide', [], app()->getLocale()) }}" aria-controls="l2hSlides">
            <i data-lucide="chevron-left"></i>
        </button>
        <div class="l2h-dots">
            <button class="l2h-dot l2h-dot-active" onclick="l2hGo(0)" aria-label="{{ __('landing.l2_dot1_aria', [], app()->getLocale()) }}" aria-current="true"></button>
            <button class="l2h-dot" onclick="l2hGo(1)" aria-label="{{ __('landing.l2_dot2_aria', [], app()->getLocale()) }}"></button>
            <button class="l2h-dot" onclick="l2hGo(2)" aria-label="{{ __('landing.l2_dot3_aria', [], app()->getLocale()) }}"></button>
            <button class="l2h-dot" onclick="l2hGo(3)" aria-label="{{ __('landing.l2_dot4_aria', [], app()->getLocale()) }}"></button>
            <button class="l2h-dot" onclick="l2hGo(4)" aria-label="{{ __('landing.l2_dot5_aria', [], app()->getLocale()) }}"></button>
        </div>
        <span class="l2h-slide-label" id="l2hLabel">{{ __('landing.l2_slide_label_default', [], app()->getLocale()) }}</span>
        <button class="l2h-arrow" id="l2hNext" onclick="l2hNext()" aria-label="{{ __('landing.aria_next_slide', [], app()->getLocale()) }}" aria-controls="l2hSlides">
            <i data-lucide="chevron-right"></i>
        </button>
    </div>
</section>

{{-- ═══════════════════════════════════════
     2. TRUST STRIP
     ═══════════════════════════════════════ --}}
<div class="l2-trust" id="l2-main-content" tabindex="-1">
    <div class="l2-container">
        <div class="l2-trust-grid">
            <div class="l2-trust-item">
                <i data-lucide="id-card"></i>
                <div>
                    <strong>{{ __('landing.trust_strip.item1_title', [], app()->getLocale()) }}</strong>
                    <span>{{ __('landing.trust_strip.item1_desc', [], app()->getLocale()) }}</span>
                </div>
            </div>
            <div class="l2-trust-item">
                <i data-lucide="link"></i>
                <div>
                    <strong>{{ __('landing.trust_strip.item2_title', [], app()->getLocale()) }}</strong>
                    <span>{{ __('landing.trust_strip.item2_desc', [], app()->getLocale()) }}</span>
                </div>
            </div>
            <div class="l2-trust-item">
                <i data-lucide="shield-check"></i>
                <div>
                    <strong>{{ __('landing.trust_strip.item3_title', [], app()->getLocale()) }}</strong>
                    <span>{{ __('landing.trust_strip.item3_desc', [], app()->getLocale()) }}</span>
                </div>
            </div>
            <div class="l2-trust-item">
                <i data-lucide="heart-pulse"></i>
                <div>
                    <strong>{{ __('landing.trust_strip.item4_title', [], app()->getLocale()) }}</strong>
                    <span>{{ __('landing.trust_strip.item4_desc', [], app()->getLocale()) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════
     3. PROBLEM (3 cards)
     ═══════════════════════════════════════ --}}
<section class="l2-section l2-section--muted">
    <div class="l2-container">
        <span class="l2-section-label">{{ __('landing.l2_section_challenge', [], app()->getLocale()) }}</span>
        <h2 class="l2-section-title">{{ __('landing.problem.title', [], app()->getLocale()) }}</h2>
        <p class="l2-section-sub">{{ __('landing.problem.subtitle', [], app()->getLocale()) }}</p>
        <div class="l2-problem-grid">
            <div class="l2-problem-card">
                <div class="l2-problem-icon"><i data-lucide="book-x"></i></div>
                <div>
                    <h3>{{ __('landing.problem.lost_books_title', [], app()->getLocale()) }}</h3>
                    <p>{{ __('landing.problem.lost_books_desc', [], app()->getLocale()) }}</p>
                </div>
            </div>
            <div class="l2-problem-card">
                <div class="l2-problem-icon"><i data-lucide="flask-conical"></i></div>
                <div>
                    <h3>{{ __('landing.problem.repeated_tests_title', [], app()->getLocale()) }}</h3>
                    <p>{{ __('landing.problem.repeated_tests_desc', [], app()->getLocale()) }}</p>
                </div>
            </div>
            <div class="l2-problem-card">
                <div class="l2-problem-icon"><i data-lucide="unplug"></i></div>
                <div>
                    <h3>{{ __('landing.problem.disconnected_title', [], app()->getLocale()) }}</h3>
                    <p>{{ __('landing.problem.disconnected_desc', [], app()->getLocale()) }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════
     4. SOLUTION HUB
     ═══════════════════════════════════════ --}}
<section class="l2-section">
    <div class="l2-container">
        <div class="l2-solution-inner">
            <div>
                <span class="l2-section-label">{{ __('landing.l2_section_solution', [], app()->getLocale()) }}</span>
                <h2 class="l2-section-title">{{ __('landing.solution.title', [], app()->getLocale()) }}</h2>
                <p class="l2-section-sub">{{ __('landing.solution.desc', [], app()->getLocale()) }}</p>
                <ul class="l2-solution-list">
                    <li><i data-lucide="check-circle"></i> {{ __('landing.solution.pill1', [], app()->getLocale()) }}</li>
                    <li><i data-lucide="check-circle"></i> {{ __('landing.solution.pill2', [], app()->getLocale()) }}</li>
                    <li><i data-lucide="check-circle"></i> {{ __('landing.solution.pill3', [], app()->getLocale()) }}</li>
                    <li><i data-lucide="check-circle"></i> {{ __('landing.solution.pill4', [], app()->getLocale()) }}</li>
                </ul>
                <a href="{{ route('public.contact') }}" class="l2-btn l2-btn--primary">
                    <i data-lucide="handshake" style="width:16px;height:16px;"></i>
                    {{ __('landing.hero.cta_primary', [], app()->getLocale()) }}
                </a>
            </div>
            <div class="l2-hub" aria-hidden="true">
                <div class="l2-hub-center">
                    <i data-lucide="shield" style="width:1.35rem;height:1.35rem;color:#fff;"></i>
                    <span>{{ __('landing.l2_hub_health_id', [], app()->getLocale()) }}</span>
                </div>
                <div class="l2-hub-pulse"></div>
                <div class="l2-hub-line ll1"></div>
                <div class="l2-hub-line ll2"></div>
                <div class="l2-hub-line ll3"></div>
                <div class="l2-hub-line ll4"></div>
                <div class="l2-hub-line ll5"></div>
                <div class="l2-hub-line ll6"></div>
                <div class="l2-hub-node n1"><i data-lucide="hospital"></i>{{ __('landing.l2_net_node_hospital', [], app()->getLocale()) }}</div>
                <div class="l2-hub-node n2"><i data-lucide="flask-conical"></i>{{ __('landing.l2_net_node_lab', [], app()->getLocale()) }}</div>
                <div class="l2-hub-node n3"><i data-lucide="pill"></i>{{ __('landing.l2_net_node_pharmacy', [], app()->getLocale()) }}</div>
                <div class="l2-hub-node n4"><i data-lucide="building-2"></i>{{ __('landing.l2_net_node_insurer', [], app()->getLocale()) }}</div>
                <div class="l2-hub-node n5"><i data-lucide="heart-pulse"></i>{{ __('landing.l2_net_node_clinic', [], app()->getLocale()) }}</div>
                <div class="l2-hub-node n6"><i data-lucide="user"></i>{{ __('landing.l2_net_node_patient', [], app()->getLocale()) }}</div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════
     5. HOW IT WORKS (3 steps)
     ═══════════════════════════════════════ --}}
<section class="l2-section l2-section--muted">
    <div class="l2-container">
        <span class="l2-section-label">{{ __('landing.l2_section_process', [], app()->getLocale()) }}</span>
        <h2 class="l2-section-title">{{ __('landing.how_it_works.title', [], app()->getLocale()) }}</h2>
        <p class="l2-section-sub">{{ __('landing.how_it_works.subtitle', [], app()->getLocale()) }}</p>
        <div class="l2-steps">
            <div class="l2-step">
                <div class="l2-step-num">1</div>
                <div class="l2-step-body">
                    <h3>{{ __('landing.how_it_works.step1_title', [], app()->getLocale()) }}</h3>
                    <p>{{ __('landing.how_it_works.step1_desc', [], app()->getLocale()) }}</p>
                </div>
            </div>
            <div class="l2-step">
                <div class="l2-step-num">2</div>
                <div class="l2-step-body">
                    <h3>{{ __('landing.how_it_works.step2_title', [], app()->getLocale()) }}</h3>
                    <p>{{ __('landing.how_it_works.step2_desc', [], app()->getLocale()) }}</p>
                </div>
            </div>
            <div class="l2-step">
                <div class="l2-step-num">3</div>
                <div class="l2-step-body">
                    <h3>{{ __('landing.how_it_works.step4_title', [], app()->getLocale()) }}</h3>
                    <p>{{ __('landing.how_it_works.step4_desc', [], app()->getLocale()) }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('public.how-it-works') }}" class="l2-steps-link">
            {{ __('landing.l2_how_walkthrough', [], app()->getLocale()) }} <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
        </a>
    </div>
</section>

{{-- ═══════════════════════════════════════
     6. CONSENT SIMULATOR
     ═══════════════════════════════════════ --}}
<section class="l2-section">
    <div class="l2-container">
        <div class="l2-consent-inner">
            <div>
                <span class="l2-section-label">{{ __('landing.l2_section_consent', [], app()->getLocale()) }}</span>
                <h2 class="l2-section-title">{{ __('landing.consent.title', [], app()->getLocale()) }}</h2>
                <p class="l2-section-sub">{{ __('landing.consent.desc', [], app()->getLocale()) }}</p>
                <ul class="l2-solution-list">
                    <li><i data-lucide="check-circle"></i> <div><strong>{{ __('landing.consent.requests_title', [], app()->getLocale()) }}</strong> — {{ __('landing.consent.requests_desc', [], app()->getLocale()) }}</div></li>
                    <li><i data-lucide="check-circle"></i> <div><strong>{{ __('landing.consent.logs_title', [], app()->getLocale()) }}</strong> — {{ __('landing.consent.logs_desc', [], app()->getLocale()) }}</div></li>
                    <li><i data-lucide="check-circle"></i> <div><strong>{{ __('landing.consent.scoped_title', [], app()->getLocale()) }}</strong> — {{ __('landing.consent.scoped_desc', [], app()->getLocale()) }}</div></li>
                </ul>
            </div>
            <div class="l2-consent-wrap">
                <div class="l2-sim-card">
                    <div class="l2-sim-bar">
                        <span class="l2-sim-dot r"></span>
                        <span class="l2-sim-dot y"></span>
                        <span class="l2-sim-dot g"></span>
                        <span class="l2-sim-title">{{ __('landing.consent_sim.window_title', [], app()->getLocale()) }}</span>
                    </div>
                    <div class="l2-sim-body">
                        <div class="l2-sim-alert">
                            <i data-lucide="alert-triangle"></i>
                            <div>
                                <strong>{{ __('landing.consent_sim.alert_title', [], app()->getLocale()) }}</strong>
                                <span>{{ __('landing.consent_sim.alert_desc', [], app()->getLocale()) }}</span>
                            </div>
                        </div>
                        <div class="l2-scope-row">
                            <span>{{ __('landing.consent_sim.scope_demographics', [], app()->getLocale()) }}</span>
                            <span class="l2-toggle on"></span>
                        </div>
                        <div class="l2-scope-row">
                            <span>{{ __('landing.consent_sim.scope_prescriptions', [], app()->getLocale()) }}</span>
                            <span class="l2-toggle on"></span>
                        </div>
                        <div class="l2-scope-row">
                            <span>{{ __('landing.consent_sim.scope_lab', [], app()->getLocale()) }}</span>
                            <span class="l2-toggle off"></span>
                        </div>
                        <div class="l2-sim-actions">
                            <button class="l2-sim-btn deny">{{ __('landing.consent_sim.btn_deny', [], app()->getLocale()) }}</button>
                            <button class="l2-sim-btn approve">{{ __('landing.consent_sim.btn_approve', [], app()->getLocale()) }}</button>
                        </div>
                    </div>
                </div>
                <p class="l2-consent-note">{{ __('landing.l2_consent_sim_note', [], app()->getLocale()) }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════
     7. EMERGENCY ACCESS (dark)
     ═══════════════════════════════════════ --}}
<section class="l2-section l2-em">
    <div class="l2-container">
        <div class="l2-em-inner">
            <div>
                <span class="l2-section-label" style="color:#fca5a5;">{{ __('landing.l2_section_emergency', [], app()->getLocale()) }}</span>
                <h2 class="l2-section-title l2-text-white">{{ __('landing.emergency.title', [], app()->getLocale()) }}</h2>
                <p class="l2-section-sub l2-text-white-70">{{ __('landing.emergency.desc', [], app()->getLocale()) }}</p>
                <div class="l2-em-points">
                    <div class="l2-em-point"><i data-lucide="check-circle"></i> {{ __('landing.emergency.blood_group', [], app()->getLocale()) }}</div>
                    <div class="l2-em-point"><i data-lucide="check-circle"></i> {{ __('landing.emergency.allergies', [], app()->getLocale()) }}</div>
                    <div class="l2-em-point"><i data-lucide="check-circle"></i> {{ __('landing.emergency.conditions', [], app()->getLocale()) }}</div>
                    <div class="l2-em-point"><i data-lucide="check-circle"></i> {{ __('landing.emergency.contacts', [], app()->getLocale()) }}</div>
                </div>
                <a href="{{ route('public.contact') }}" class="l2-btn l2-btn--outline-white">
                    <i data-lucide="handshake" style="width:16px;height:16px;"></i>
                    {{ __('landing.hero.cta_primary', [], app()->getLocale()) }}
                </a>
            </div>
            <div>
                <div class="l2-em-card">
                    <div class="l2-em-card-header">
                        <i data-lucide="alert-octagon"></i>
                        <span>{{ __('landing.emergency_sim.override_label', [], app()->getLocale()) }}</span>
                    </div>
                    <div class="l2-em-row">
                        <span class="l2-em-lbl"><i data-lucide="droplets"></i> {{ __('landing.emergency.blood_group', [], app()->getLocale()) }}</span>
                        <span class="l2-em-val danger">{{ __('landing.emergency_sim.demo_blood', [], app()->getLocale()) }}</span>
                    </div>
                    <div class="l2-em-row">
                        <span class="l2-em-lbl"><i data-lucide="triangle-alert"></i> {{ __('landing.emergency.allergies', [], app()->getLocale()) }}</span>
                        <span class="l2-em-val warn">{{ __('landing.emergency_sim.demo_allergies', [], app()->getLocale()) }}</span>
                    </div>
                    <div class="l2-em-row">
                        <span class="l2-em-lbl"><i data-lucide="activity"></i> {{ __('landing.emergency.conditions', [], app()->getLocale()) }}</span>
                        <span class="l2-em-val">{{ __('landing.emergency_sim.demo_conditions', [], app()->getLocale()) }}</span>
                    </div>
                    <div class="l2-em-row">
                        <span class="l2-em-lbl"><i data-lucide="phone"></i> {{ __('landing.emergency.contacts', [], app()->getLocale()) }}</span>
                        <span class="l2-em-val">{{ __('landing.emergency_sim.demo_contact', [], app()->getLocale()) }}</span>
                    </div>
                    <div class="l2-em-audit">
                        <i data-lucide="shield-alert"></i>
                        <span>{{ __('landing.emergency.audit_notice', [], app()->getLocale()) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════
     8. WHO IT'S FOR (patient / hospital / dev)
     ═══════════════════════════════════════ --}}
<section class="l2-section l2-section--muted">
    <div class="l2-container">
        <span class="l2-section-label">{{ __('landing.l2_section_who', [], app()->getLocale()) }}</span>
        <h2 class="l2-section-title">{{ __('landing.roles.title', [], app()->getLocale()) }}</h2>
        <p class="l2-section-sub">{{ __('landing.roles.subtitle', [], app()->getLocale()) }}</p>

        {{-- Mobile: native select --}}
        <label for="l2RolesSelect" class="sr-only">{{ __('landing.l2_roles_select_label', [], app()->getLocale()) }}</label>
        <select class="l2-roles-select" id="l2RolesSelect" onchange="l2SwitchRole(this.value)">
            <option value="patient">{{ __('landing.roles.patients_title', [], app()->getLocale()) }}</option>
            <option value="hospital">{{ __('landing.roles.hospitals_title', [], app()->getLocale()) }}</option>
            <option value="dev">{{ __('landing.roles.developers_title', [], app()->getLocale()) }}</option>
        </select>

        {{-- Desktop: tab buttons --}}
        <div class="l2-roles-tabs" id="l2RolesTabs">
            <button class="l2-role-tab active" onclick="l2SwitchRole('patient')" data-role="patient">
                <i data-lucide="user"></i> {{ __('landing.roles.patients_title', [], app()->getLocale()) }}
            </button>
            <button class="l2-role-tab" onclick="l2SwitchRole('hospital')" data-role="hospital">
                <i data-lucide="hospital"></i> {{ __('landing.roles.hospitals_title', [], app()->getLocale()) }}
            </button>
            <button class="l2-role-tab" onclick="l2SwitchRole('dev')" data-role="dev">
                <i data-lucide="code-2"></i> {{ __('landing.roles.developers_title', [], app()->getLocale()) }}
            </button>
        </div>

        {{-- Panels --}}
        <div class="l2-roles-panel active" id="l2-role-patient">
            <i class="l2-role-icon" data-lucide="user"></i>
            <h3>{{ __('landing.roles.patients_title', [], app()->getLocale()) }}</h3>
            <p>{{ __('landing.roles.patients_desc', [], app()->getLocale()) }}</p>
            <ul class="l2-roles-benefits">
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_patient_benefit1', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_patient_benefit2', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_patient_benefit3', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_patient_benefit4', [], app()->getLocale()) }}</li>
            </ul>
            <a href="{{ route('public.contact') }}" class="l2-roles-cta">
                {{ __('landing.l2_patient_cta', [], app()->getLocale()) }} <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>

        <div class="l2-roles-panel" id="l2-role-hospital">
            <i class="l2-role-icon" data-lucide="hospital"></i>
            <h3>{{ __('landing.roles.hospitals_title', [], app()->getLocale()) }}</h3>
            <p>{{ __('landing.roles.hospitals_desc', [], app()->getLocale()) }}</p>
            <ul class="l2-roles-benefits">
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_hospital_benefit1', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_hospital_benefit2', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_hospital_benefit3', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_hospital_benefit4', [], app()->getLocale()) }}</li>
            </ul>
            <a href="{{ route('public.contact') }}" class="l2-roles-cta">
                {{ __('landing.l2_hospital_cta', [], app()->getLocale()) }} <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>

        <div class="l2-roles-panel" id="l2-role-dev">
            <i class="l2-role-icon" data-lucide="code-2"></i>
            <h3>{{ __('landing.roles.developers_title', [], app()->getLocale()) }}</h3>
            <p>{{ __('landing.roles.developers_desc', [], app()->getLocale()) }}</p>
            <ul class="l2-roles-benefits">
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_dev_benefit1', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_dev_benefit2', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_dev_benefit3', [], app()->getLocale()) }}</li>
                <li><i data-lucide="check-circle"></i> {{ __('landing.l2_dev_benefit4', [], app()->getLocale()) }}</li>
            </ul>
            <a href="{{ route('public.interoperability') }}" class="l2-roles-cta">
                {{ __('landing.l2_dev_cta', [], app()->getLocale()) }} <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>

        <p class="l2-solutions-link">
            {{ __('landing.l2_all_org_types', [], app()->getLocale()) }}
            <a href="{{ route('public.solutions.patients') }}">{{ __('landing.nav.solutions', [], app()->getLocale()) }}</a>
        </p>
    </div>
</section>

{{-- ═══════════════════════════════════════
     9. PARTNER CTA FORM
     ═══════════════════════════════════════ --}}
<section class="l2-section">
    <div class="l2-container">
        <div class="l2-partner-inner">
            <div>
                <span class="l2-section-label">{{ __('landing.l2_section_get_started', [], app()->getLocale()) }}</span>
                <h2 class="l2-section-title">{{ __('landing.partner_cta.title', [], app()->getLocale()) }}</h2>
                <p class="l2-section-sub">{{ __('landing.partner_cta.desc', [], app()->getLocale()) }}</p>
                <ul class="l2-solution-list">
                    <li><i data-lucide="check-circle"></i> {{ __('landing.l2_partner_benefit1', [], app()->getLocale()) }}</li>
                    <li><i data-lucide="check-circle"></i> {{ __('landing.l2_partner_benefit2', [], app()->getLocale()) }}</li>
                    <li><i data-lucide="check-circle"></i> {{ __('landing.l2_partner_benefit3', [], app()->getLocale()) }}</li>
                </ul>
            </div>
            <div class="l2-form-card">
                <h3>{{ __('landing.partner_cta.form.title', [], app()->getLocale()) }}</h3>
                <div id="l2FormMessages"></div>
                <form id="l2PartnerForm" novalidate>
                    @csrf
                    <div class="l2-form-row">
                        <div class="l2-form-group">
                            <label for="l2Name">{{ __('landing.partner_cta.form.name', [], app()->getLocale()) }} *</label>
                            <input type="text" id="l2Name" name="name" required autocomplete="name">
                        </div>
                        <div class="l2-form-group">
                            <label for="l2Org">{{ __('landing.partner_cta.form.org', [], app()->getLocale()) }} *</label>
                            <input type="text" id="l2Org" name="organization" required autocomplete="organization">
                        </div>
                        <div class="l2-form-group">
                            <label for="l2Email">{{ __('landing.partner_cta.form.email', [], app()->getLocale()) }} *</label>
                            <input type="email" id="l2Email" name="email" required autocomplete="email">
                        </div>
                        <div class="l2-form-group">
                            <label for="l2Type">{{ __('landing.partner_cta.form.type', [], app()->getLocale()) }}</label>
                            <select id="l2Type" name="org_type">
                                <option value="">{{ __('landing.l2_form_select_placeholder', [], app()->getLocale()) }}</option>
                                <option value="hospital">{{ __('landing.partner_cta.form.options.hospital', [], app()->getLocale()) }}</option>
                                <option value="clinic">{{ __('landing.partner_cta.form.options.clinic', [], app()->getLocale()) }}</option>
                                <option value="pharmacy">{{ __('landing.partner_cta.form.options.pharmacy', [], app()->getLocale()) }}</option>
                                <option value="lab">{{ __('landing.partner_cta.form.options.lab', [], app()->getLocale()) }}</option>
                                <option value="insurance">{{ __('landing.partner_cta.form.options.insurance', [], app()->getLocale()) }}</option>
                                <option value="public">{{ __('landing.partner_cta.form.options.public', [], app()->getLocale()) }}</option>
                                <option value="tech">{{ __('landing.partner_cta.form.options.tech', [], app()->getLocale()) }}</option>
                                <option value="other">{{ __('landing.partner_cta.form.options.other', [], app()->getLocale()) }}</option>
                            </select>
                        </div>
                        <div class="l2-form-group full">
                            <label for="l2Message">{{ __('landing.partner_cta.form.message', [], app()->getLocale()) }}</label>
                            <textarea id="l2Message" name="message"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="l2-btn l2-btn--primary l2-form-submit">
                        <i data-lucide="send" style="width:16px;height:16px;"></i>
                        {{ __('landing.partner_cta.form.submit', [], app()->getLocale()) }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════
     10. FAQ (3 questions)
     ═══════════════════════════════════════ --}}
<section class="l2-section l2-section--muted">
    <div class="l2-container" style="max-width:780px;">
        <span class="l2-section-label">{{ __('landing.l2_section_faq', [], app()->getLocale()) }}</span>
        <h2 class="l2-section-title">{{ __('landing.faq.title', [], app()->getLocale()) }}</h2>
        <p class="l2-section-sub">{{ __('landing.faq.subtitle', [], app()->getLocale()) }}</p>
        <div class="l2-faq">
            <details class="l2-faq-item">
                <summary>
                    {{ __('landing.faq.q1', [], app()->getLocale()) }}
                    <i class="l2-chevron" data-lucide="chevron-down"></i>
                </summary>
                <div class="l2-faq-answer">{{ __('landing.faq.a1', [], app()->getLocale()) }}</div>
            </details>
            <details class="l2-faq-item">
                <summary>
                    {{ __('landing.faq.q3', [], app()->getLocale()) }}
                    <i class="l2-chevron" data-lucide="chevron-down"></i>
                </summary>
                <div class="l2-faq-answer">{{ __('landing.faq.a3', [], app()->getLocale()) }}</div>
            </details>
            <details class="l2-faq-item">
                <summary>
                    {{ __('landing.faq.q4', [], app()->getLocale()) }}
                    <i class="l2-chevron" data-lucide="chevron-down"></i>
                </summary>
                <div class="l2-faq-answer">{{ __('landing.faq.a4', [], app()->getLocale()) }}</div>
            </details>
        </div>
        <a href="{{ route('public.faq') }}" class="l2-faq-link">
            {{ __('landing.l2_faq_all_link', [], app()->getLocale()) }} <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
        </a>
    </div>
</section>

{{-- ═══════════════════════════════════════
     11. FINAL CTA
     ═══════════════════════════════════════ --}}
<section class="l2-cta">
    <div class="l2-container">
        <h2>{{ __('landing.footer_cta.title', [], app()->getLocale()) }}</h2>
        <p>{{ __('landing.footer_cta.subtitle', [], app()->getLocale()) }}</p>
        <div class="l2-cta-btns">
            <a href="{{ route('public.contact') }}" class="l2-btn l2-btn--white">
                <i data-lucide="handshake" style="width:16px;height:16px;"></i>
                {{ __('landing.footer_cta.cta_primary', [], app()->getLocale()) }}
            </a>
            <a href="{{ route('public.contact') }}" class="l2-btn l2-btn--outline-white">
                <i data-lucide="mail" style="width:16px;height:16px;"></i>
                {{ __('landing.footer_cta.cta_secondary', [], app()->getLocale()) }}
            </a>
        </div>
    </div>
</section>

</div>{{-- /.l2 --}}

{{-- Theme toggle — outside .l2 so position:fixed is not trapped --}}
<button class="l2-theme-toggle" id="l2ThemeToggle" aria-label="{{ __('landing.l2_theme_toggle_aria', [], app()->getLocale()) }}">
    <i data-lucide="sun"  class="l2-icon-sun"></i>
    <i data-lucide="moon" class="l2-icon-moon"></i>
</button>
@endsection

@section('footer_scripts')
{{-- Hero slider v2 --}}
<script>
(function(){
    var cur = 0, total = 5;
    var labels = {!! json_encode([
        __('landing.l2_slide_label_default', [], app()->getLocale()),
        __('landing.l2_consent_badge', [], app()->getLocale()),
        __('landing.l2_em_badge', [], app()->getLocale()),
        __('landing.l2_net_badge', [], app()->getLocale()),
        __('landing.l2_journey_badge', [], app()->getLocale()),
    ]) !!};
    function upd(){
        var el = document.getElementById('l2hSlides');
        if (el) el.style.transform = 'translateX(-' + (cur * 100) + '%)';
        // Dots — active class + aria-current
        document.querySelectorAll('.l2h-dot').forEach(function(d,i){
            d.classList.toggle('l2h-dot-active', i === cur);
            if (i === cur) d.setAttribute('aria-current','true');
            else d.removeAttribute('aria-current');
        });
        // Slides — aria-hidden on inactive
        document.querySelectorAll('.l2h-slide').forEach(function(s,i){
            if (i === cur) s.removeAttribute('aria-hidden');
            else s.setAttribute('aria-hidden','true');
        });
        // Slide label
        var lbl = document.getElementById('l2hLabel');
        if (lbl) lbl.textContent = labels[cur];
        // Screen reader announcement
        var announce = document.getElementById('l2hAnnounce');
        if (announce) announce.textContent = 'Slide ' + (cur+1) + ' of ' + total + ': ' + labels[cur];
        // Arrow disabled state
        var prev = document.getElementById('l2hPrev');
        var next = document.getElementById('l2hNext');
        if (prev) prev.disabled = cur === 0;
        if (next) next.disabled = cur === total - 1;
    }
    window.l2hPrev = function(){ if(cur > 0){ cur--; upd(); } };
    window.l2hNext = function(){ if(cur < total - 1){ cur++; upd(); } };
    window.l2hGo   = function(i){ cur = i; upd(); };
    var sx = 0, el = document.getElementById('l2hSlides');
    if (el) {
        el.addEventListener('touchstart', function(e){ sx = e.touches[0].clientX; }, {passive:true});
        el.addEventListener('touchend',   function(e){
            var d = sx - e.changedTouches[0].clientX;
            if (Math.abs(d) > 45) { d > 0 ? l2hNext() : l2hPrev(); }
        });
    }
})();
</script>

{{-- Roles switcher --}}
<script>
function l2SwitchRole(role) {
    ['patient','hospital','dev'].forEach(function(r) {
        var panel = document.getElementById('l2-role-' + r);
        if (panel) panel.classList.toggle('active', r === role);
    });
    document.querySelectorAll('.l2-role-tab').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.role === role);
    });
    var sel = document.getElementById('l2RolesSelect');
    if (sel) sel.value = role;
}
</script>

{{-- Theme toggle --}}
<script>
(function(){
    var l2  = document.querySelector('.l2');
    var btn = document.getElementById('l2ThemeToggle');
    if (!l2 || !btn) return;
    var saved = localStorage.getItem('l2-theme');
    var preferLight = saved === 'light';
    function apply(light) {
        l2.classList.toggle('l2-light', light);
        document.body.classList.toggle('l2-page-light', light);
        localStorage.setItem('l2-theme', light ? 'light' : 'dark');
        if (window.lucide) lucide.createIcons();
    }
    apply(preferLight);
    btn.addEventListener('click', function(){ apply(!l2.classList.contains('l2-light')); });
})();
</script>

{{-- Partner form --}}
<script>
(function(){
    var form = document.getElementById('l2PartnerForm');
    if (!form) return;
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var msgs = document.getElementById('l2FormMessages');
        var name  = form.querySelector('[name="name"]').value.trim();
        var org   = form.querySelector('[name="organization"]').value.trim();
        var email = form.querySelector('[name="email"]').value.trim();
        if (!name || !org || !email) {
            msgs.innerHTML = '<div class="l2-form-alert-err"><i data-lucide="alert-circle"></i> {{ __("landing.l2_js_required_fields_err", [], app()->getLocale()) }}</div>';
            if (window.lucide) lucide.createIcons();
            return;
        }
        var btn = form.querySelector('[type="submit"]');
        btn.disabled = true;
        btn.textContent = '{{ __("landing.l2_js_sending", [], app()->getLocale()) }}';
        fetch('{{ route("public.contact.submit") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
            body: JSON.stringify({
                name: name,
                organization: org,
                email: email,
                org_type: form.querySelector('[name="org_type"]').value,
                message: form.querySelector('[name="message"]').value,
            })
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            msgs.innerHTML = '<div class="l2-form-alert-ok"><i data-lucide="check-circle"></i> {{ __("landing.partner_cta.form.success", [], app()->getLocale()) }}</div>';
            form.reset();
            if (window.lucide) lucide.createIcons();
        })
        .catch(function(){
            msgs.innerHTML = '<div class="l2-form-alert-err"><i data-lucide="alert-circle"></i> {{ __("landing.l2_js_error", [], app()->getLocale()) }}</div>';
            if (window.lucide) lucide.createIcons();
        })
        .finally(function(){
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="send" style="width:16px;height:16px;"></i> {{ __("landing.partner_cta.form.submit", [], app()->getLocale()) }}';
            if (window.lucide) lucide.createIcons();
        });
    });
})();
</script>
@endsection
