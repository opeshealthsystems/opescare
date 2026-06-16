@extends('layouts.public')

@section('title', __('landing.hero.title', [], app()->getLocale()))

@section('head_scripts')
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing2.css') }}">
@endsection

@section('content')
<div class="l2">

{{-- ═══════════════════════════════════════
     1. HERO SLIDER  (reuses .hs-* CSS)
     ═══════════════════════════════════════ --}}
<section class="hs-section">
    <div class="hs-track">
        <div id="hs-slides" class="hs-slides">

            {{-- Slide 1: Health ID --}}
            <div class="hs-slide hs-s1">
                <div class="hs-inner">
                    <div class="hs-copy">
                        <span class="hs-badge">
                            <i data-lucide="shield-check" style="width:13px;height:13px;"></i>
                            {{ __('landing.hero.badge', [], app()->getLocale()) }}
                        </span>
                        <h1>{{ __('landing.hero.title', [], app()->getLocale()) }}</h1>
                        <p class="hs-sub">{{ __('landing.hero.subtitle', [], app()->getLocale()) }}</p>
                        <div class="hs-btn-grid">
                            <a href="{{ route('public.contact') }}" class="hs-btn hs-btn-primary">
                                <i data-lucide="handshake" style="width:16px;height:16px;"></i>
                                {{ __('landing.hero.cta_primary', [], app()->getLocale()) }}
                            </a>
                            <a href="{{ route('public.how-it-works') }}" class="hs-btn hs-btn-outline">
                                <i data-lucide="play-circle" style="width:16px;height:16px;"></i>
                                {{ __('landing.hero.cta_secondary', [], app()->getLocale()) }}
                            </a>
                        </div>
                        <div class="hs-trust-row">
                            <span><i data-lucide="check-circle" style="width:13px;height:13px;color:#22c55e;"></i> {{ __('landing.hero.trust1', [], app()->getLocale()) }}</span>
                            <span><i data-lucide="check-circle" style="width:13px;height:13px;color:#22c55e;"></i> {{ __('landing.hero.trust2', [], app()->getLocale()) }}</span>
                        </div>
                    </div>
                    <div class="hs-visual">
                        <div class="hs-card hs-card-id">
                            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                                <i data-lucide="credit-card" style="width:1rem;height:1rem;color:#60a5fa;"></i>
                                <span style="font-size:.65rem;font-weight:700;color:rgba(255,255,255,.6);letter-spacing:.08em;text-transform:uppercase;">{{ __('landing.hero_card.label_health_id', [], app()->getLocale()) }}</span>
                            </div>
                            <div style="font-size:.85rem;font-weight:700;color:#fff;letter-spacing:.06em;font-family:monospace;">{{ __('landing.hero_card.demo_id', [], app()->getLocale()) }}</div>
                            <div style="font-size:.65rem;color:rgba(255,255,255,.45);margin-top:.3rem;">{{ __('landing.hero_card.secure_label', [], app()->getLocale()) }}</div>
                            <div style="margin-top:.75rem;background:rgba(255,255,255,.08);border-radius:.4rem;padding:.45rem .65rem;font-size:.7rem;color:rgba(255,255,255,.75);">
                                <i data-lucide="check-circle" style="width:.75rem;height:.75rem;color:#22c55e;vertical-align:-1px;"></i>
                                {{ __('landing.hero_card.label_verified', [], app()->getLocale()) }} · {{ __('landing.hero_card.consent_approved', [], app()->getLocale()) }}
                            </div>
                        </div>
                        <div class="hs-card hs-card-timeline">
                            <div style="font-size:.65rem;color:rgba(255,255,255,.45);margin-bottom:.35rem;">{{ __('landing.hero_card.timeline_ago', [], app()->getLocale()) }}</div>
                            <div style="font-size:.8rem;font-weight:600;color:#fff;">{{ __('landing.hero_card.timeline_title', [], app()->getLocale()) }}</div>
                            <div style="font-size:.7rem;color:rgba(255,255,255,.55);margin-top:.2rem;">{{ __('landing.hero_card.timeline_desc', [], app()->getLocale()) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 2: Consent & Access --}}
            <div class="hs-slide hs-s2">
                <div class="hs-inner">
                    <div class="hs-copy">
                        <span class="hs-badge">
                            <i data-lucide="lock" style="width:13px;height:13px;"></i>
                            Consent &amp; Access
                        </span>
                        <h1>Patients decide who sees their records. Every time.</h1>
                        <p class="hs-sub">OpesCare gives patients granular control over their sensitive health data — approving, denying, or revoking access from one secure consent center.</p>
                        <div class="hs-btn-grid">
                            <a href="{{ route('public.contact') }}" class="hs-btn hs-btn-primary">
                                <i data-lucide="handshake" style="width:16px;height:16px;"></i>
                                Request Demo
                            </a>
                            <a href="{{ route('public.how-it-works') }}" class="hs-btn hs-btn-outline">
                                <i data-lucide="shield" style="width:16px;height:16px;"></i>
                                How Consent Works
                            </a>
                        </div>
                    </div>
                    <div class="hs-visual" style="display:flex;align-items:center;justify-content:center;">
                        <div class="hs-card" style="width:100%;max-width:260px;">
                            <div style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:.6rem;">Consent Request</div>
                            <div style="background:rgba(253,224,71,.12);border:1px solid rgba(253,224,71,.3);border-radius:.4rem;padding:.5rem .7rem;font-size:.72rem;color:rgba(255,255,255,.8);margin-bottom:.75rem;">
                                <i data-lucide="alert-triangle" style="width:.75rem;height:.75rem;color:#fde047;vertical-align:-1px;margin-right:4px;"></i>
                                City General Hospital · Clinical Notes
                            </div>
                            <div style="font-size:.7rem;color:rgba(255,255,255,.55);margin-bottom:.5rem;">Access scope:</div>
                            @foreach(['Demographics','Prescriptions','Lab Results'] as $scope)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.35rem 0;border-bottom:1px solid rgba(255,255,255,.07);font-size:.72rem;color:rgba(255,255,255,.75);">
                                <span>{{ $scope }}</span>
                                <span style="width:1.5rem;height:.9rem;border-radius:99px;background:{{ $loop->last ? 'rgba(255,255,255,.2)' : '#0F4C81' }};display:inline-block;"></span>
                            </div>
                            @endforeach
                            <div style="display:flex;gap:.5rem;margin-top:.85rem;">
                                <button style="flex:1;padding:.4rem;border-radius:.35rem;background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);border:none;font-size:.72rem;font-weight:600;min-height:36px;">Deny</button>
                                <button style="flex:1;padding:.4rem;border-radius:.35rem;background:#0F4C81;color:#fff;border:none;font-size:.72rem;font-weight:600;min-height:36px;">Approve</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 3: Emergency Access --}}
            <div class="hs-slide hs-s3">
                <div class="hs-inner">
                    <div class="hs-copy">
                        <span class="hs-badge">
                            <i data-lucide="siren" style="width:13px;height:13px;"></i>
                            Emergency Access
                        </span>
                        <h1>Critical information when seconds matter — fully audited.</h1>
                        <p class="hs-sub">When a patient cannot give consent, approved providers access a limited emergency profile. Every access is reason-based, logged, and reviewed after the emergency.</p>
                        <div class="hs-btn-grid">
                            <a href="{{ route('public.contact') }}" class="hs-btn hs-btn-primary">
                                <i data-lucide="handshake" style="width:16px;height:16px;"></i>
                                Request Demo
                            </a>
                            <a href="{{ route('public.how-it-works') }}" class="hs-btn hs-btn-outline">
                                <i data-lucide="file-text" style="width:16px;height:16px;"></i>
                                Learn More
                            </a>
                        </div>
                    </div>
                    <div class="hs-visual" style="display:flex;align-items:center;justify-content:center;">
                        <div class="hs-card" style="width:100%;max-width:260px;border:1px solid rgba(239,68,68,.35);">
                            <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.75rem;">
                                <i data-lucide="alert-octagon" style="width:.9rem;height:.9rem;color:#ef4444;"></i>
                                <span style="font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#ef4444;">Emergency Override Active</span>
                            </div>
                            @foreach([['Blood Group','O Positive (O+)','danger'],['Critical Allergies','Penicillin, Peanuts','warn'],['Conditions','Chronic Asthma','normal']] as [$lbl,$val,$cls])
                            <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid rgba(255,255,255,.07);font-size:.72rem;">
                                <span style="color:rgba(255,255,255,.45);">{{ $lbl }}</span>
                                <span style="color:{{ $cls==='danger'?'#fca5a5':($cls==='warn'?'#fcd34d':'#fff') }};font-weight:600;">{{ $val }}</span>
                            </div>
                            @endforeach
                            <div style="margin-top:.75rem;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:.35rem;padding:.5rem .65rem;font-size:.68rem;color:rgba(255,255,255,.55);">
                                <i data-lucide="shield-alert" style="width:.75rem;height:.75rem;color:#ef4444;vertical-align:-1px;margin-right:3px;"></i>
                                Access logged · Compliance hub notified
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slide 4: Connected Care Network --}}
            <div class="hs-slide hs-s4">
                <div class="hs-inner">
                    <div class="hs-copy">
                        <span class="hs-badge">
                            <i data-lucide="network" style="width:13px;height:13px;"></i>
                            Connected Care Network
                        </span>
                        <h1>Connect your hospital, clinic, lab, or pharmacy today.</h1>
                        <p class="hs-sub">OpesCare works with the systems healthcare facilities already use — through APIs, SDKs, widgets, bridge agents, or OpesCare Lite for facilities starting from zero.</p>
                        <div class="hs-btn-grid">
                            <a href="{{ route('public.contact') }}" class="hs-btn hs-btn-primary">
                                <i data-lucide="handshake" style="width:16px;height:16px;"></i>
                                Request Partnership Demo
                            </a>
                            <a href="{{ route('public.interoperability') }}" class="hs-btn hs-btn-outline">
                                <i data-lucide="plug" style="width:16px;height:16px;"></i>
                                Interoperability
                            </a>
                        </div>
                    </div>
                    <div class="hs-visual" style="display:flex;align-items:center;justify-content:center;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;width:100%;max-width:240px;">
                            @foreach([['hospital','Hospital','Connect API'],['flask-conical','Lab','SDK'],['pill','Pharmacy','Widget'],['building-2','Insurer','Bridge Agent']] as [$icon,$label,$method])
                            <div class="hs-card" style="padding:.75rem;text-align:center;">
                                <i data-lucide="{{ $icon }}" style="width:1.1rem;height:1.1rem;color:#60a5fa;margin-bottom:.35rem;"></i>
                                <div style="font-size:.72rem;font-weight:600;color:#fff;">{{ $label }}</div>
                                <div style="font-size:.6rem;color:rgba(255,255,255,.4);margin-top:.15rem;">{{ $method }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /.hs-slides --}}
    </div>{{-- /.hs-track --}}

    {{-- Controls --}}
    <div class="hs-controls">
        <button class="hs-arrow" id="hs-prev" onclick="hsPrev()" disabled aria-label="Previous">
            <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
        </button>
        <div class="hs-dots">
            <button class="hs-dot hs-dot-active" onclick="hsGo(0)" aria-label="Slide 1"></button>
            <button class="hs-dot" onclick="hsGo(1)" aria-label="Slide 2"></button>
            <button class="hs-dot" onclick="hsGo(2)" aria-label="Slide 3"></button>
            <button class="hs-dot" onclick="hsGo(3)" aria-label="Slide 4"></button>
        </div>
        <span id="hs-label" class="hs-label">Health ID</span>
        <button class="hs-arrow" id="hs-next" onclick="hsNext()" aria-label="Next">
            <i data-lucide="chevron-right" style="width:18px;height:18px;"></i>
        </button>
    </div>
</section>

{{-- ═══════════════════════════════════════
     2. TRUST STRIP
     ═══════════════════════════════════════ --}}
<div class="l2-trust">
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
        <span class="l2-section-label">The Challenge</span>
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
                <span class="l2-section-label">The Solution</span>
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
                    <span>Health ID</span>
                </div>
                <div class="l2-hub-pulse"></div>
                <div class="l2-hub-line ll1"></div>
                <div class="l2-hub-line ll2"></div>
                <div class="l2-hub-line ll3"></div>
                <div class="l2-hub-line ll4"></div>
                <div class="l2-hub-line ll5"></div>
                <div class="l2-hub-line ll6"></div>
                <div class="l2-hub-node n1"><i data-lucide="hospital"></i>Hospital</div>
                <div class="l2-hub-node n2"><i data-lucide="flask-conical"></i>Lab</div>
                <div class="l2-hub-node n3"><i data-lucide="pill"></i>Pharmacy</div>
                <div class="l2-hub-node n4"><i data-lucide="building-2"></i>Insurer</div>
                <div class="l2-hub-node n5"><i data-lucide="heart-pulse"></i>Clinic</div>
                <div class="l2-hub-node n6"><i data-lucide="user"></i>Patient</div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════
     5. HOW IT WORKS (3 steps)
     ═══════════════════════════════════════ --}}
<section class="l2-section l2-section--muted">
    <div class="l2-container">
        <span class="l2-section-label">Process</span>
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
            Full walkthrough <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
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
                <span class="l2-section-label">Consent</span>
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
                <p class="l2-consent-note">Interactive simulation — not connected to real patient data.</p>
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
                <span class="l2-section-label" style="color:#fca5a5;">Emergency</span>
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
        <span class="l2-section-label">Who It Serves</span>
        <h2 class="l2-section-title">{{ __('landing.roles.title', [], app()->getLocale()) }}</h2>
        <p class="l2-section-sub">{{ __('landing.roles.subtitle', [], app()->getLocale()) }}</p>

        {{-- Mobile: native select --}}
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
                <li><i data-lucide="check-circle"></i> One Health ID across all facilities</li>
                <li><i data-lucide="check-circle"></i> Control who accesses your records</li>
                <li><i data-lucide="check-circle"></i> View access logs and revoke permissions</li>
                <li><i data-lucide="check-circle"></i> Emergency profile always accessible to approved providers</li>
            </ul>
            <a href="{{ route('public.contact') }}" class="l2-roles-cta">
                Get your Health ID <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>

        <div class="l2-roles-panel" id="l2-role-hospital">
            <i class="l2-role-icon" data-lucide="hospital"></i>
            <h3>{{ __('landing.roles.hospitals_title', [], app()->getLocale()) }}</h3>
            <p>{{ __('landing.roles.hospitals_desc', [], app()->getLocale()) }}</p>
            <ul class="l2-roles-benefits">
                <li><i data-lucide="check-circle"></i> Register patients and assign Health IDs</li>
                <li><i data-lucide="check-circle"></i> Connect labs, pharmacy, billing in one platform</li>
                <li><i data-lucide="check-circle"></i> Pull approved patient records from other facilities</li>
                <li><i data-lucide="check-circle"></i> Full audit trail on every record access</li>
            </ul>
            <a href="{{ route('public.contact') }}" class="l2-roles-cta">
                Request Partnership Demo <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>

        <div class="l2-roles-panel" id="l2-role-dev">
            <i class="l2-role-icon" data-lucide="code-2"></i>
            <h3>{{ __('landing.roles.developers_title', [], app()->getLocale()) }}</h3>
            <p>{{ __('landing.roles.developers_desc', [], app()->getLocale()) }}</p>
            <ul class="l2-roles-benefits">
                <li><i data-lucide="check-circle"></i> REST API, SDK, widget, webhooks, bridge agent</li>
                <li><i data-lucide="check-circle"></i> FHIR-aligned interoperability layer</li>
                <li><i data-lucide="check-circle"></i> Sandbox environment for testing</li>
                <li><i data-lucide="check-circle"></i> Full developer documentation</li>
            </ul>
            <a href="{{ route('public.interoperability') }}" class="l2-roles-cta">
                View Connect Suite <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>

        <p class="l2-solutions-link">
            See all organization types →
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
                <span class="l2-section-label">Get Started</span>
                <h2 class="l2-section-title">{{ __('landing.partner_cta.title', [], app()->getLocale()) }}</h2>
                <p class="l2-section-sub">{{ __('landing.partner_cta.desc', [], app()->getLocale()) }}</p>
                <ul class="l2-solution-list">
                    <li><i data-lucide="check-circle"></i> No commitment required for the demo</li>
                    <li><i data-lucide="check-circle"></i> Our team speaks English and French</li>
                    <li><i data-lucide="check-circle"></i> Integration support included</li>
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
                                <option value="">— Select —</option>
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
        <span class="l2-section-label">FAQ</span>
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
            All frequently asked questions <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
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
@endsection

@section('footer_scripts')
{{-- Hero slider (shared with landing.blade.php) --}}
<script>
(function(){
    var cur = 0; var total = 4;
    var labels = ['Health ID','Consent & Access','Emergency Access','Connected Care Network'];
    function hsUpdate(){
        document.getElementById('hs-slides').style.transform = 'translateX(-' + (cur * 100) + '%)';
        document.querySelectorAll('.hs-dot').forEach(function(d,i){ d.classList.toggle('hs-dot-active', i === cur); });
        document.getElementById('hs-label').textContent = labels[cur];
        document.getElementById('hs-prev').disabled = cur === 0;
        document.getElementById('hs-next').disabled = cur === total - 1;
    }
    window.hsPrev = function(){ if(cur > 0){ cur--; hsUpdate(); } };
    window.hsNext = function(){ if(cur < total-1){ cur++; hsUpdate(); } };
    window.hsGo   = function(i){ cur = i; hsUpdate(); };
    var startX = 0;
    var el = document.getElementById('hs-slides');
    el.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; }, {passive:true});
    el.addEventListener('touchend',   function(e){
        var diff = startX - e.changedTouches[0].clientX;
        if(Math.abs(diff) > 50){ diff > 0 ? hsNext() : hsPrev(); }
    });
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
            msgs.innerHTML = '<div class="l2-form-alert-err"><i data-lucide="alert-circle"></i> Please fill in all required fields.</div>';
            if (window.lucide) lucide.createIcons();
            return;
        }
        var btn = form.querySelector('[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Sending…';
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
            msgs.innerHTML = '<div class="l2-form-alert-err"><i data-lucide="alert-circle"></i> Something went wrong. Please try again.</div>';
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
