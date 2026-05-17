@extends('layouts.public')

@section('title', 'OpesCare | Secure Digital Health ID and Connected Medical Records')
@section('meta_description', 'OpesCare is a secure digital Health ID and healthcare interoperability platform that helps patients carry approved medical history across hospitals, clinics, labs, pharmacies, and insurers.')

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-content">
                <div class="badge">{{ __('landing.hero.badge') }}</div>
                <h1>{{ __('landing.hero.title') }}</h1>
                <p class="hero-subtitle">{{ __('landing.hero.subtitle') }}</p>
                <p class="hero-desc">{{ __('landing.hero.desc') }}</p>
                <div class="hero-actions">
                    <a href="#partner-form" class="btn btn-primary btn-lg">{{ __('landing.hero.cta_primary') }}</a>
                    <a href="#how-it-works" class="btn btn-secondary btn-lg">{{ __('landing.hero.cta_secondary') }}</a>
                </div>
                <div class="hero-trust">
                    <div class="trust-item"><i data-lucide="shield-check"></i> <span>{{ __('landing.hero.trust1') }}</span></div>
                    <div class="trust-item"><i data-lucide="siren"></i> <span>{{ __('landing.hero.trust2') }}</span></div>
                    <div class="trust-item"><i data-lucide="cable"></i> <span>{{ __('landing.hero.trust3') }}</span></div>
                    <div class="trust-item"><i data-lucide="languages"></i> <span>{{ __('landing.hero.trust4') }}</span></div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="mockup-container">
                    <!-- Health ID Card Mockup -->
                    <div class="mockup-card card-health-id animate-float">
                        <div class="card-header">
                            <div class="logo-group">
                                <div class="mini-logo"></div>
                                <span class="logo-text">OpesCare</span>
                            </div>
                            <div class="status-indicator">Verified</div>
                        </div>
                        <div class="card-body">
                            <div class="qr-code-area">
                                <i data-lucide="qr-code"></i>
                            </div>
                            <div class="patient-details">
                                <div class="detail-label">Health ID Token</div>
                                <div class="detail-value font-mono">OPC-8849-DX9</div>
                                <div class="detail-bars">
                                    <div class="bar bar-lg"></div>
                                    <div class="bar bar-sm"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="footer-msg"><i data-lucide="lock-keyhole"></i> Secure Encrypted Identifier</span>
                        </div>
                    </div>

                    <!-- Consent Approved Card -->
                    <div class="mockup-card card-consent animate-slide-up-delay-1">
                        <div class="consent-badge">
                            <i data-lucide="shield-check" class="text-success"></i>
                        </div>
                        <div class="consent-info">
                            <strong>{{ __('landing.consent.requests_title') }}</strong>
                            <span>Approved by Patient</span>
                        </div>
                    </div>

                    <!-- Timeline Event -->
                    <div class="mockup-card card-timeline animate-slide-up-delay-2">
                        <div class="timeline-dot"></div>
                        <div class="timeline-details">
                            <div class="timeline-time">10 mins ago</div>
                            <div class="timeline-title">Lab Result Synchronized</div>
                            <div class="timeline-desc text-muted">Hematology Profile • City Lab</div>
                        </div>
                    </div>

                    <!-- Pharmacy Stock Alert -->
                    <div class="mockup-card card-stock animate-slide-up-delay-3">
                        <i data-lucide="pill" class="text-teal"></i>
                        <div class="stock-details">
                            <strong>Pharmacy Sync</strong>
                            <span class="text-success">Medicine Found</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Strip / Key Outcome Bar -->
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
            <div class="card card-problem">
                <div class="problem-header">
                    <i data-lucide="file-x"></i>
                    <h3>{{ __('landing.problem.lost_books_title') }}</h3>
                </div>
                <p>{{ __('landing.problem.lost_books_desc') }}</p>
            </div>
            <div class="card card-problem">
                <div class="problem-header">
                    <i data-lucide="flask-conical"></i>
                    <h3>{{ __('landing.problem.repeated_tests_title') }}</h3>
                </div>
                <p>{{ __('landing.problem.repeated_tests_desc') }}</p>
            </div>
            <div class="card card-problem">
                <div class="problem-header">
                    <i data-lucide="badge-alert"></i>
                    <h3>{{ __('landing.problem.blind_treatment_title') }}</h3>
                </div>
                <p>{{ __('landing.problem.blind_treatment_desc') }}</p>
            </div>
            <div class="card card-problem">
                <div class="problem-header">
                    <i data-lucide="cable"></i>
                    <h3>{{ __('landing.problem.disconnected_title') }}</h3>
                </div>
                <p>{{ __('landing.problem.disconnected_desc') }}</p>
            </div>
            <div class="card card-problem">
                <div class="problem-header">
                    <i data-lucide="siren"></i>
                    <h3>{{ __('landing.problem.availability_title') }}</h3>
                </div>
                <p>{{ __('landing.problem.availability_desc') }}</p>
            </div>
            <div class="card card-problem">
                <div class="problem-header">
                    <i data-lucide="clipboard-check"></i>
                    <h3>{{ __('landing.problem.weak_audit_title') }}</h3>
                </div>
                <p>{{ __('landing.problem.weak_audit_desc') }}</p>
            </div>
        </div>
    </section>

    <!-- Solution Section -->
    <section class="section">
        <div class="container grid-2 items-center">
            <div>
                <h2>{{ __('landing.solution.title') }}</h2>
                <p class="text-lg text-muted mb-8">{{ __('landing.solution.desc') }}</p>
                <ul class="solution-list">
                    <li><i data-lucide="check-circle-2"></i> <span>{{ __('landing.solution.pill1') }}</span></li>
                    <li><i data-lucide="check-circle-2"></i> <span>{{ __('landing.solution.pill2') }}</span></li>
                    <li><i data-lucide="check-circle-2"></i> <span>{{ __('landing.solution.pill3') }}</span></li>
                    <li><i data-lucide="check-circle-2"></i> <span>{{ __('landing.solution.pill4') }}</span></li>
                </ul>
            </div>
            <div class="solution-visual">
                <!-- Connected Hub Diagram -->
                <div class="hub-container">
                    <div class="hub-center">
                        <div class="hub-pulse"></div>
                        <i data-lucide="id-card"></i>
                        <span>OpesCare</span>
                    </div>
                    <div class="hub-line l1"></div>
                    <div class="hub-line l2"></div>
                    <div class="hub-line l3"></div>
                    <div class="hub-line l4"></div>
                    <div class="hub-line l5"></div>
                    <div class="hub-line l6"></div>
                    
                    <div class="hub-node node-hosp" data-label="Hospital"><i data-lucide="hospital"></i></div>
                    <div class="hub-node node-clinic" data-label="Clinic"><i data-lucide="activity"></i></div>
                    <div class="hub-node node-lab" data-label="Laboratory"><i data-lucide="flask-conical"></i></div>
                    <div class="hub-node node-pharma" data-label="Pharmacy"><i data-lucide="pill"></i></div>
                    <div class="hub-node node-insure" data-label="Insurer"><i data-lucide="shield-check"></i></div>
                    <div class="hub-node node-public" data-label="Public Health"><i data-lucide="globe"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- How OpesCare Works -->
    <section class="section section-muted" id="how-it-works">
        <div class="container text-center mb-12">
            <h2>{{ __('landing.how_it_works.title') }}</h2>
            <p class="section-subtitle">{{ __('landing.how_it_works.subtitle') }}</p>
        </div>
        <div class="container stepper-container">
            <div class="stepper">
                <div class="step-card">
                    <div class="step-num">1</div>
                    <div class="step-icon"><i data-lucide="user-plus"></i></div>
                    <h3>{{ __('landing.how_it_works.step1_title') }}</h3>
                    <p>{{ __('landing.how_it_works.step1_desc') }}</p>
                </div>
                <div class="step-card">
                    <div class="step-num">2</div>
                    <div class="step-icon"><i data-lucide="clipboard-list"></i></div>
                    <h3>{{ __('landing.how_it_works.step2_title') }}</h3>
                    <p>{{ __('landing.how_it_works.step2_desc') }}</p>
                </div>
                <div class="step-card">
                    <div class="step-num">3</div>
                    <div class="step-icon"><i data-lucide="shield-question"></i></div>
                    <h3>{{ __('landing.how_it_works.step3_title') }}</h3>
                    <p>{{ __('landing.how_it_works.step3_desc') }}</p>
                </div>
                <div class="step-card">
                    <div class="step-num">4</div>
                    <div class="step-icon"><i data-lucide="lock-keyhole"></i></div>
                    <h3>{{ __('landing.how_it_works.step4_title') }}</h3>
                    <p>{{ __('landing.how_it_works.step4_desc') }}</p>
                </div>
                <div class="step-card">
                    <div class="step-num">5</div>
                    <div class="step-icon"><i data-lucide="refresh-cw"></i></div>
                    <h3>{{ __('landing.how_it_works.step5_title') }}</h3>
                    <p>{{ __('landing.how_it_works.step5_desc') }}</p>
                </div>
            </div>
            <div class="stepper-footer">
                <p class="text-sm"><i data-lucide="info"></i> {{ __('landing.how_it_works.footer_note') }}</p>
            </div>
        </div>
    </section>

    <!-- Core Platform Modules -->
    <section class="section">
        <div class="container text-center mb-12">
            <h2>{{ __('landing.modules.title') }}</h2>
            <p class="section-subtitle">{{ __('landing.modules.subtitle') }}</p>
        </div>
        <div class="container grid-3">
            @foreach([
                'identity' => 'id-card',
                'consent' => 'shield-check',
                'timeline' => 'history',
                'lab' => 'flask-conical',
                'pharmacy' => 'pill',
                'billing' => 'receipt',
                'referrals' => 'send',
                'integrations' => 'cable',
                'availability' => 'droplets'
            ] as $key => $icon)
                <div class="card module-card">
                    <div class="module-icon"><i data-lucide="{{ $icon }}"></i></div>
                    <h3>{{ __("landing.modules.{$key}_title") }}</h3>
                    <p>{{ __("landing.modules.{$key}_desc") }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Interoperability Section -->
    <section class="section section-muted">
        <div class="container grid-2 items-center">
            <div class="visual-container">
                <!-- Interop System Grid -->
                <div class="interop-grid">
                    <div class="interop-circle main-circle">
                        <i data-lucide="network"></i>
                        <span>Interoperability Core</span>
                    </div>
                    <div class="interop-circle c1" data-label="Direct API"><i data-lucide="braces"></i></div>
                    <div class="interop-circle c2" data-label="Connect Widget"><i data-lucide="panel-top"></i></div>
                    <div class="interop-circle c3" data-label="Bridge Agent"><i data-lucide="cpu"></i></div>
                    <div class="interop-circle c4" data-label="Connect SDK"><i data-lucide="code-2"></i></div>
                </div>
            </div>
            <div>
                <h2>{{ __('landing.interop.title') }}</h2>
                <p class="text-lg text-muted mb-8">{{ __('landing.interop.subtitle') }}</p>
                <div class="interop-list">
                    <div class="interop-item">
                        <i data-lucide="braces"></i>
                        <div>
                            <h4>{{ __('landing.interop.api_title') }}</h4>
                            <p>{{ __('landing.interop.api_desc') }}</p>
                        </div>
                    </div>
                    <div class="interop-item">
                        <i data-lucide="code-2"></i>
                        <div>
                            <h4>{{ __('landing.interop.sdk_title') }}</h4>
                            <p>{{ __('landing.interop.sdk_desc') }}</p>
                        </div>
                    </div>
                    <div class="interop-item">
                        <i data-lucide="panel-top"></i>
                        <div>
                            <h4>{{ __('landing.interop.widget_title') }}</h4>
                            <p>{{ __('landing.interop.widget_desc') }}</p>
                        </div>
                    </div>
                    <div class="interop-item">
                        <i data-lucide="cpu"></i>
                        <div>
                            <h4>{{ __('landing.interop.bridge_title') }}</h4>
                            <p>{{ __('landing.interop.bridge_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Patient Control and Consent -->
    <section class="section">
        <div class="container grid-2 items-center">
            <div>
                <h2>{{ __('landing.consent.title') }}</h2>
                <p class="text-lg text-muted mb-8">{{ __('landing.consent.desc') }}</p>
                <div class="consent-grid-mini">
                    <div class="card mini-card">
                        <i data-lucide="shield-question"></i>
                        <h4>{{ __('landing.consent.requests_title') }}</h4>
                        <p>{{ __('landing.consent.requests_desc') }}</p>
                    </div>
                    <div class="card mini-card">
                        <i data-lucide="list-checks"></i>
                        <h4>{{ __('landing.consent.logs_title') }}</h4>
                        <p>{{ __('landing.consent.logs_desc') }}</p>
                    </div>
                    <div class="card mini-card">
                        <i data-lucide="lock"></i>
                        <h4>{{ __('landing.consent.scoped_title') }}</h4>
                        <p>{{ __('landing.consent.scoped_desc') }}</p>
                    </div>
                    <div class="card mini-card">
                        <i data-lucide="shield-x"></i>
                        <h4>{{ __('landing.consent.revocation_title') }}</h4>
                        <p>{{ __('landing.consent.revocation_desc') }}</p>
                    </div>
                </div>
            </div>
            <div class="solution-visual">
                <!-- Consent Security Interface Simulator -->
                <div class="simulator-card">
                    <div class="sim-header">
                        <div class="sim-dot red"></div>
                        <div class="sim-dot yellow"></div>
                        <div class="sim-dot green"></div>
                        <span class="sim-title">Security & Consent Center</span>
                    </div>
                    <div class="sim-body">
                        <div class="sim-alert">
                            <i data-lucide="shield-alert" class="text-warning"></i>
                            <div>
                                <strong>Active Consent Request</strong>
                                <span>City General Hospital requests Clinical Notes access</span>
                            </div>
                        </div>
                        <div class="sim-scope-box">
                            <div class="scope-row">
                                <span>Demographic Details</span>
                                <div class="toggle active"></div>
                            </div>
                            <div class="scope-row">
                                <span>Prescription Records</span>
                                <div class="toggle active"></div>
                            </div>
                            <div class="scope-row">
                                <span>Lab Results</span>
                                <div class="toggle inactive"></div>
                            </div>
                        </div>
                        <div class="sim-actions">
                            <button class="btn btn-secondary btn-sm">Deny</button>
                            <button class="btn btn-primary btn-sm">Approve Access</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Emergency Access Section -->
    <section class="section section-dark bg-dark-elite">
        <div class="container grid-2 items-center">
            <div class="emergency-visual">
                <!-- Simulated Emergency Profile View -->
                <div class="simulator-card emergency-sim">
                    <div class="sim-header">
                        <i data-lucide="siren" class="text-danger animate-pulse"></i>
                        <span class="sim-title text-danger uppercase tracking-widest font-black">Emergency Override Active</span>
                    </div>
                    <div class="sim-body">
                        <div class="emergency-profile-box">
                            <div class="profile-header-sim">
                                <div class="profile-avatar-sim"><i data-lucide="user"></i></div>
                                <div>
                                    <h3>John Doe</h3>
                                    <span class="font-mono text-muted">ID: OPC-8849-DX9</span>
                                </div>
                            </div>
                            <div class="profile-details-grid">
                                <div class="detail-sim-item">
                                    <span class="lbl"><i data-lucide="droplet" class="text-danger"></i> Blood Group</span>
                                    <strong class="text-danger">O Positive (O+)</strong>
                                </div>
                                <div class="detail-sim-item">
                                    <span class="lbl"><i data-lucide="shield-alert" class="text-warning"></i> Critical Allergies</span>
                                    <strong class="text-warning">Penicillin, Peanuts</strong>
                                </div>
                                <div class="detail-sim-item">
                                    <span class="lbl"><i data-lucide="heart-pulse"></i> Conditions</span>
                                    <strong>Chronic Asthma</strong>
                                </div>
                                <div class="detail-sim-item">
                                    <span class="lbl"><i data-lucide="phone"></i> Emerg. Contact</span>
                                    <strong>Jane Doe (+234-800-EMER)</strong>
                                </div>
                            </div>
                        </div>
                        <div class="emergency-audit-warn">
                            <i data-lucide="info"></i>
                            <span>{{ __('landing.emergency.audit_notice') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h2 class="text-white">{{ __('landing.emergency.title') }}</h2>
                <p class="text-lg text-muted-light mb-8">{{ __('landing.emergency.desc') }}</p>
                <div class="emergency-points text-muted-light">
                    <div class="pt"><i data-lucide="check-circle-2" class="text-danger"></i> <span>{{ __('landing.emergency.patient_identity') }}</span></div>
                    <div class="pt"><i data-lucide="check-circle-2" class="text-danger"></i> <span>{{ __('landing.emergency.blood_group') }}</span></div>
                    <div class="pt"><i data-lucide="check-circle-2" class="text-danger"></i> <span>{{ __('landing.emergency.allergies') }}</span></div>
                    <div class="pt"><i data-lucide="check-circle-2" class="text-danger"></i> <span>{{ __('landing.emergency.conditions') }}</span></div>
                    <div class="pt"><i data-lucide="check-circle-2" class="text-danger"></i> <span>{{ __('landing.emergency.meds') }}</span></div>
                    <div class="pt"><i data-lucide="check-circle-2" class="text-danger"></i> <span>{{ __('landing.emergency.contacts') }}</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Medication and Blood Availability Section -->
    <section class="section">
        <div class="container text-center mb-12">
            <h2>{{ __('landing.med_blood.title') }}</h2>
            <p class="section-subtitle">{{ __('landing.med_blood.subtitle') }}</p>
        </div>
        <div class="container grid-2">
            <!-- Medication Column -->
            <div class="card avail-card">
                <div class="avail-header">
                    <i data-lucide="map-pin"></i>
                    <h3>{{ __('landing.med_blood.med.title') }}</h3>
                </div>
                <p class="avail-desc">{{ __('landing.med_blood.med.desc') }}</p>
                <ul class="avail-list">
                    <li><i data-lucide="check" class="text-teal"></i> <span>{{ __('landing.med_blood.med.b1') }}</span></li>
                    <li><i data-lucide="check" class="text-teal"></i> <span>{{ __('landing.med_blood.med.b2') }}</span></li>
                    <li><i data-lucide="check" class="text-teal"></i> <span>{{ __('landing.med_blood.med.b3') }}</span></li>
                    <li><i data-lucide="check" class="text-teal"></i> <span>{{ __('landing.med_blood.med.b4') }}</span></li>
                    <li><i data-lucide="check" class="text-teal"></i> <span>{{ __('landing.med_blood.med.b5') }}</span></li>
                    <li><i data-lucide="check" class="text-teal"></i> <span>{{ __('landing.med_blood.med.b6') }}</span></li>
                </ul>
            </div>

            <!-- Blood Column -->
            <div class="card avail-card">
                <div class="avail-header">
                    <i data-lucide="droplet" class="text-danger"></i>
                    <h3>{{ __('landing.med_blood.blood.title') }}</h3>
                </div>
                <p class="avail-desc">{{ __('landing.med_blood.blood.desc') }}</p>
                <ul class="avail-list">
                    <li><i data-lucide="check" class="text-danger"></i> <span>{{ __('landing.med_blood.blood.b1') }}</span></li>
                    <li><i data-lucide="check" class="text-danger"></i> <span>{{ __('landing.med_blood.blood.b2') }}</span></li>
                    <li><i data-lucide="check" class="text-danger"></i> <span>{{ __('landing.med_blood.blood.b3') }}</span></li>
                    <li><i data-lucide="check" class="text-danger"></i> <span>{{ __('landing.med_blood.blood.b4') }}</span></li>
                    <li><i data-lucide="check" class="text-danger"></i> <span>{{ __('landing.med_blood.blood.b5') }}</span></li>
                    <li><i data-lucide="check" class="text-danger"></i> <span>{{ __('landing.med_blood.blood.b6') }}</span></li>
                </ul>
            </div>
        </div>
        <div class="container text-center mt-8">
            <div class="safety-banner">
                <strong><i data-lucide="alert-triangle" class="text-warning"></i> {{ __('landing.med_blood.safety_title') }}:</strong>
                <span>{{ __('landing.med_blood.safety_desc') }}</span>
            </div>
        </div>
    </section>

    <!-- Role-Based Benefits Section -->
    <section class="section section-muted" id="audience-benefits">
        <div class="container text-center mb-12">
            <h2>{{ __('landing.roles.title') }}</h2>
            <p class="section-subtitle">{{ __('landing.roles.subtitle') }}</p>
        </div>
        
        <div class="container roles-tab-container">
            <!-- Tabs Navigation -->
            <div class="roles-tabs" id="rolesTabList">
                <button class="role-tab active" data-target="patients"><i data-lucide="user"></i> {{ __('landing.roles.patients_title') }}</button>
                <button class="role-tab" data-target="hospitals"><i data-lucide="hospital"></i> {{ __('landing.roles.hospitals_title') }}</button>
                <button class="role-tab" data-target="doctors"><i data-lucide="stethoscope"></i> {{ __('landing.roles.doctors_title') }}</button>
                <button class="role-tab" data-target="labs"><i data-lucide="flask-conical"></i> {{ __('landing.roles.labs_title') }}</button>
                <button class="role-tab" data-target="pharmacies"><i data-lucide="pill"></i> {{ __('landing.roles.pharmacies_title') }}</button>
                <button class="role-tab" data-target="insurers"><i data-lucide="shield-check"></i> {{ __('landing.roles.insurers_title') }}</button>
                <button class="role-tab" data-target="public-health"><i data-lucide="globe"></i> {{ __('landing.roles.public_health_title') }}</button>
                <button class="role-tab" data-target="developers"><i data-lucide="code-2"></i> {{ __('landing.roles.developers_title') }}</button>
            </div>

            <!-- Tab Contents -->
            <div class="roles-tab-content" id="rolesTabContent">
                <div class="role-panel active" id="panel-patients">
                    <div class="panel-inner">
                        <i data-lucide="user"></i>
                        <h3>{{ __('landing.roles.patients_title') }}</h3>
                        <p>{{ __('landing.roles.patients_desc') }}</p>
                    </div>
                </div>
                <div class="role-panel" id="panel-hospitals">
                    <div class="panel-inner">
                        <i data-lucide="hospital"></i>
                        <h3>{{ __('landing.roles.hospitals_title') }}</h3>
                        <p>{{ __('landing.roles.hospitals_desc') }}</p>
                    </div>
                </div>
                <div class="role-panel" id="panel-doctors">
                    <div class="panel-inner">
                        <i data-lucide="stethoscope"></i>
                        <h3>{{ __('landing.roles.doctors_title') }}</h3>
                        <p>{{ __('landing.roles.doctors_desc') }}</p>
                    </div>
                </div>
                <div class="role-panel" id="panel-labs">
                    <div class="panel-inner">
                        <i data-lucide="flask-conical"></i>
                        <h3>{{ __('landing.roles.labs_title') }}</h3>
                        <p>{{ __('landing.roles.labs_desc') }}</p>
                    </div>
                </div>
                <div class="role-panel" id="panel-pharmacies">
                    <div class="panel-inner">
                        <i data-lucide="pill"></i>
                        <h3>{{ __('landing.roles.pharmacies_title') }}</h3>
                        <p>{{ __('landing.roles.pharmacies_desc') }}</p>
                    </div>
                </div>
                <div class="role-panel" id="panel-insurers">
                    <div class="panel-inner">
                        <i data-lucide="shield-check"></i>
                        <h3>{{ __('landing.roles.insurers_title') }}</h3>
                        <p>{{ __('landing.roles.insurers_desc') }}</p>
                    </div>
                </div>
                <div class="role-panel" id="panel-public-health">
                    <div class="panel-inner">
                        <i data-lucide="globe"></i>
                        <h3>{{ __('landing.roles.public_health_title') }}</h3>
                        <p>{{ __('landing.roles.public_health_desc') }}</p>
                    </div>
                </div>
                <div class="role-panel" id="panel-developers">
                    <div class="panel-inner">
                        <i data-lucide="code-2"></i>
                        <h3>{{ __('landing.roles.developers_title') }}</h3>
                        <p>{{ __('landing.roles.developers_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security, Audit, and Privacy Section -->
    <section class="section">
        <div class="container text-center mb-12">
            <h2>{{ __('landing.security_section.title') }}</h2>
            <p class="section-subtitle">{{ __('landing.security_section.subtitle') }}</p>
        </div>
        <div class="container grid-3">
            <div class="card security-card">
                <i data-lucide="key-round"></i>
                <h3>{{ __('landing.security_section.role_title') }}</h3>
                <p>{{ __('landing.security_section.role_desc') }}</p>
            </div>
            <div class="card security-card">
                <i data-lucide="shield-check"></i>
                <h3>{{ __('landing.security_section.consent_title') }}</h3>
                <p>{{ __('landing.security_section.consent_desc') }}</p>
            </div>
            <div class="card security-card">
                <i data-lucide="clipboard-check"></i>
                <h3>{{ __('landing.security_section.audit_title') }}</h3>
                <p>{{ __('landing.security_section.audit_desc') }}</p>
            </div>
            <div class="card security-card">
                <i data-lucide="file-search"></i>
                <h3>{{ __('landing.security_section.source_title') }}</h3>
                <p>{{ __('landing.security_section.source_desc') }}</p>
            </div>
            <div class="card security-card">
                <i data-lucide="siren"></i>
                <h3>{{ __('landing.security_section.emergency_title') }}</h3>
                <p>{{ __('landing.security_section.emergency_desc') }}</p>
            </div>
            <div class="card security-card">
                <i data-lucide="lock-keyhole"></i>
                <h3>{{ __('landing.security_section.integrations_title') }}</h3>
                <p>{{ __('landing.security_section.integrations_desc') }}</p>
            </div>
        </div>
    </section>

    <!-- Integration Products Section -->
    <section class="section section-muted" id="integrations">
        <div class="container text-center mb-12">
            <h2>{{ __('landing.integration_products.title') }}</h2>
            <p class="section-subtitle">{{ __('landing.integration_products.desc') }}</p>
        </div>
        <div class="container grid-3">
            <div class="card integration-card">
                <i data-lucide="braces"></i>
                <h3>{{ __('landing.integration_products.api_title') }}</h3>
                <p>{{ __('landing.integration_products.api_desc') }}</p>
            </div>
            <div class="card integration-card">
                <i data-lucide="code-2"></i>
                <h3>{{ __('landing.integration_products.sdk_title') }}</h3>
                <p>{{ __('landing.integration_products.sdk_desc') }}</p>
            </div>
            <div class="card integration-card">
                <i data-lucide="panel-top"></i>
                <h3>{{ __('landing.integration_products.widget_title') }}</h3>
                <p>{{ __('landing.integration_products.widget_desc') }}</p>
            </div>
            <div class="card integration-card">
                <i data-lucide="cpu"></i>
                <h3>{{ __('landing.integration_products.bridge_title') }}</h3>
                <p>{{ __('landing.integration_products.bridge_desc') }}</p>
            </div>
            <div class="card integration-card">
                <i data-lucide="layout-dashboard"></i>
                <h3>{{ __('landing.integration_products.lite_title') }}</h3>
                <p>{{ __('landing.integration_products.lite_desc') }}</p>
            </div>
            <div class="card integration-card">
                <i data-lucide="radio-tower"></i>
                <h3>{{ __('landing.integration_products.webhooks_title') }}</h3>
                <p>{{ __('landing.integration_products.webhooks_desc') }}</p>
            </div>
        </div>
    </section>

    <!-- Bilingual Platform Section -->
    <section class="section">
        <div class="container grid-2 items-center">
            <div>
                <h2>{{ __('landing.bilingual.title') }}</h2>
                <p class="text-lg text-muted mb-8">{{ __('landing.bilingual.desc') }}</p>
                <div class="bilingual-checklist">
                    <div class="chk-item"><i data-lucide="languages" class="text-teal"></i> <span>{{ __('landing.bilingual.b1') }}</span></div>
                    <div class="chk-item"><i data-lucide="message-square" class="text-teal"></i> <span>{{ __('landing.bilingual.b2') }}</span></div>
                    <div class="chk-item"><i data-lucide="smartphone" class="text-teal"></i> <span>{{ __('landing.bilingual.b3') }}</span></div>
                    <div class="chk-item"><i data-lucide="monitor" class="text-teal"></i> <span>{{ __('landing.bilingual.b4') }}</span></div>
                </div>
            </div>
            <div class="bilingual-visual">
                <!-- Dual Language Interface Visualizer -->
                <div class="lang-visualizer-box">
                    <div class="visual-panel en-panel">
                        <div class="panel-header-mini">English Interface</div>
                        <div class="panel-body-mini">
                            <strong class="text-teal">Secure Health ID</strong>
                            <p class="text-sm text-muted">Carry your medical records everywhere.</p>
                        </div>
                    </div>
                    <div class="visual-panel fr-panel">
                        <div class="panel-header-mini">Interface Française</div>
                        <div class="panel-body-mini">
                            <strong class="text-teal">ID Santé Sécurisé</strong>
                            <p class="text-sm text-muted">Transportez vos dossiers médicaux partout.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Implementation / Partner CTA Section -->
    <section class="section section-muted" id="partner-form">
        <div class="container grid-2">
            <div>
                <h2>{{ __('landing.partner_cta.title') }}</h2>
                <p class="text-lg text-muted mb-8">{{ __('landing.partner_cta.desc') }}</p>
                <div class="cta-direct-actions">
                    <a href="#integrations" class="btn btn-secondary">{{ __('landing.partner_cta.cta_secondary') }}</a>
                </div>
            </div>
            <div class="card partner-form-card">
                <h3>{{ __('landing.partner_cta.form.title') }}</h3>
                @if (session('success'))
                    <div class="alert alert-success mt-4">
                        <i data-lucide="check-circle-2"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @else
                    <form action="#" method="POST" class="partner-inquiry-form mt-4">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="form-name">{{ __('landing.partner_cta.form.name') }} *</label>
                                <input type="text" id="form-name" name="name" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="form-org">{{ __('landing.partner_cta.form.org') }} *</label>
                                <input type="text" id="form-org" name="organization" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="form-role">{{ __('landing.partner_cta.form.role') }} *</label>
                                <input type="text" id="form-role" name="role" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="form-email">{{ __('landing.partner_cta.form.email') }} *</label>
                                <input type="email" id="form-email" name="email" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="form-phone">{{ __('landing.partner_cta.form.phone') }}</label>
                                <input type="text" id="form-phone" name="phone" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="form-type">{{ __('landing.partner_cta.form.type') }} *</label>
                                <select id="form-type" name="organization_type" required class="form-control">
                                    <option value="">-- Select Type --</option>
                                    @foreach(__('landing.partner_cta.form.options') as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label for="form-country">{{ __('landing.partner_cta.form.country') }} *</label>
                                <input type="text" id="form-country" name="country" required class="form-control">
                            </div>
                            <div class="form-group full-width">
                                <label for="form-message">{{ __('landing.partner_cta.form.message') }} *</label>
                                <textarea id="form-message" name="message" required rows="4" class="form-control"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-full mt-4">{{ __('landing.partner_cta.form.submit') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section" id="faq">
        <div class="container text-center mb-12">
            <h2>{{ __('landing.faq.title') }}</h2>
            <p class="section-subtitle">{{ __('landing.faq.subtitle') }}</p>
        </div>
        <div class="container faq-container">
            <div class="faq-accordion">
                @for ($i = 1; $i <= 8; $i++)
                    <details class="faq-item">
                        <summary class="faq-question">
                            <span>{{ __("landing.faq.q{$i}") }}</span>
                            <i data-lucide="chevron-down" class="icon-chevron"></i>
                        </summary>
                        <div class="faq-answer">
                            <p>{{ __("landing.faq.a{$i}") }}</p>
                        </div>
                    </details>
                @endfor
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="section section-dark text-center">
        <div class="container">
            <h2 class="text-white">{{ __('landing.footer_cta.title') }}</h2>
            <p class="text-muted-light mb-8 text-lg" style="max-width: 800px; margin: 0 auto 2.5rem;">{{ __('landing.footer_cta.subtitle') }}</p>
            <div style="display: flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap;">
                <a href="#partner-form" class="btn btn-primary btn-lg">{{ __('landing.footer_cta.cta_primary') }}</a>
                <a href="#integrations" class="btn btn-secondary btn-lg">{{ __('landing.footer_cta.cta_secondary') }}</a>
            </div>
        </div>
    </section>
@endsection
