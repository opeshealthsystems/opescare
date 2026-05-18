@extends('layouts.public')

@section('title', 'OpesCare Interoperability | Connect Healthcare Systems')
@section('meta_description', 'OpesCare supports REST API, SDK, embeddable widget, Bridge Agent, and OpesCare Lite to connect hospitals, labs, pharmacies, and insurers without replacing existing systems.')

@section('content')

    {{-- Hero --}}
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background:rgba(20,184,166,.15);color:#0F766E;margin-bottom:1rem;">Core Infrastructure</div>
            <h1>Built to connect healthcare systems, not replace them all.</h1>
            <p class="text-muted" style="max-width:760px;margin:0 auto;font-size:1.2rem;">
                OpesCare allows approved systems to push and pull patient data through secure APIs, SDKs, widgets, bridge agents, and webhooks — working with whatever infrastructure a facility already has in place.
            </p>
            <div style="margin-top:2.5rem;display:flex;justify-content:center;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('public.developers') }}" class="btn btn-primary">Developer Docs</a>
                <a href="{{ route('public.contact') }}" class="btn btn-secondary">Request Integration Call</a>
            </div>
        </div>
    </header>

    {{-- Push / Pull model --}}
    <section class="content-body">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <h2>Bi-directional push and pull architecture</h2>
                    <p class="text-muted" style="margin-bottom:2rem;">
                        OpesCare is not a one-way data store. The platform supports a fully bi-directional model so facilities can contribute information <strong>to</strong> the patient's timeline (push) and retrieve approved information <strong>from</strong> it (pull) — all within the bounds of consent and role-based access.
                    </p>
                    <div style="display:grid;gap:1rem;">
                        <div style="padding:1.5rem;background:#F0F9FF;border-left:4px solid #0F4C81;border-radius:.75rem;">
                            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
                                <i data-lucide="upload" style="width:1.1rem;height:1.1rem;color:#0F4C81;"></i>
                                <h4 style="margin:0;color:#0F4C81;">Push</h4>
                            </div>
                            <p style="margin:0;font-size:.9375rem;color:#1e293b;">A facility sends new patient data — visits, diagnoses, prescriptions, lab results, referrals — to OpesCare to update the patient's global medical timeline.</p>
                        </div>
                        <div style="padding:1.5rem;background:#F0FDF4;border-left:4px solid #14B8A6;border-radius:.75rem;">
                            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
                                <i data-lucide="download" style="width:1.1rem;height:1.1rem;color:#0F766E;"></i>
                                <h4 style="margin:0;color:#0F766E;">Pull</h4>
                            </div>
                            <p style="margin:0;font-size:.9375rem;color:#1e293b;">A facility requests approved patient information from OpesCare to inform clinical care — seeing only what the patient has consented to share for the stated purpose.</p>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    {{-- Diagram: Hospital ↔ OpesCare ↔ Lab/Pharmacy --}}
                    <div style="background:#0F2744;border-radius:1.5rem;padding:2rem;color:#fff;">
                        <div style="text-align:center;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:1.5rem;">Interop Flow</div>
                        <div style="display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:1rem;">
                            {{-- Left: Facility --}}
                            <div style="background:rgba(255,255,255,.07);border-radius:1rem;padding:1.25rem;text-align:center;">
                                <i data-lucide="hospital" style="width:2rem;height:2rem;color:#93c5fd;margin-bottom:.5rem;display:block;margin:0 auto .5rem;"></i>
                                <div style="font-size:.8125rem;font-weight:600;">Hospital / Clinic</div>
                                <div style="font-size:.6875rem;color:#94a3b8;margin-top:.25rem;">Authorized Provider</div>
                            </div>
                            {{-- Arrows --}}
                            <div style="text-align:center;">
                                <div style="font-size:.75rem;color:#14B8A6;margin-bottom:.5rem;">Push →</div>
                                <div style="width:2rem;height:2rem;background:#0F4C81;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                                    <i data-lucide="shield-check" style="width:1rem;height:1rem;color:#fff;"></i>
                                </div>
                                <div style="font-size:.75rem;color:#93c5fd;margin-top:.5rem;">← Pull</div>
                            </div>
                            {{-- Right: OpesCare --}}
                            <div style="background:rgba(15,76,129,.4);border:1px solid rgba(99,179,237,.3);border-radius:1rem;padding:1.25rem;text-align:center;">
                                <img src="{{ asset('favicon.svg') }}" width="28" height="28" style="display:block;margin:0 auto .5rem;" alt="">
                                <div style="font-size:.8125rem;font-weight:600;">OpesCare</div>
                                <div style="font-size:.6875rem;color:#94a3b8;margin-top:.25rem;">Consent + Audit Layer</div>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-top:1.25rem;">
                            @foreach(['Laboratory','Pharmacy','Insurer'] as $node)
                            <div style="background:rgba(255,255,255,.07);border-radius:.75rem;padding:.75rem;text-align:center;font-size:.75rem;color:#cbd5e1;">{{ $node }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Integration methods --}}
    <section class="section" style="background:#F8FAFC;">
        <div class="container">
            <div class="section-header">
                <h2>Five integration pathways — one for every facility type</h2>
                <p class="text-muted">From enterprise API integrations to zero-install browser portals, OpesCare has a pathway for every technology maturity level.</p>
            </div>
            <div class="card-grid">
                @php
                $methods = [
                    ['icon'=>'braces','title'=>'Connect API','badge'=>'Enterprise','color'=>'#0F4C81','desc'=>'Direct RESTful API integration for modern hospital information systems and enterprise health vendors. OAuth 2.0, versioned endpoints, full sandbox access.','link'=>route('public.developers').'#api'],
                    ['icon'=>'code-2','title'=>'Connect SDK','badge'=>'Developer','color'=>'#6D28D9','desc'=>'Pre-built client libraries for PHP, JavaScript, and Python. Typed request/response objects, full test coverage, Composer/npm/pip distribution.','link'=>route('public.developers').'#sdk'],
                    ['icon'=>'panel-top','title'=>'Connect Widget','badge'=>'Web-Embed','color'=>'#0F766E','desc'=>'A secure, sandboxed web component for facilities with an existing web portal. Embed patient search, consent, and pull in a single script tag.','link'=>route('public.developers').'#widget'],
                    ['icon'=>'cpu','title'=>'Bridge Agent','badge'=>'Legacy','color'=>'#C2410C','desc'=>'A lightweight local service for facilities running legacy EMR systems, on-premise databases, or file-based record exports. Works offline with encrypted sync queues.','link'=>route('public.developers').'#bridge'],
                    ['icon'=>'layout-dashboard','title'=>'OpesCare Lite','badge'=>'No-Code','color'=>'#065F46','desc'=>'A fully hosted browser portal for facilities that do not yet have their own digital system. Staff log in from any modern browser — zero installation.','link'=>route('public.developers').'#lite'],
                ];
                @endphp
                @foreach($methods as $method)
                <div class="card">
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                        <div style="width:2.25rem;height:2.25rem;background:{{ $method['color'] }}1A;color:{{ $method['color'] }};border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="{{ $method['icon'] }}" style="width:1rem;height:1rem;"></i>
                        </div>
                        <h3 style="margin:0;font-size:1rem;">{{ $method['title'] }}</h3>
                        <span style="margin-left:auto;font-size:.6875rem;font-weight:700;padding:.2rem .6rem;background:{{ $method['color'] }}1A;color:{{ $method['color'] }};border-radius:999px;">{{ $method['badge'] }}</span>
                    </div>
                    <p class="text-muted" style="font-size:.875rem;margin-bottom:1.25rem;">{{ $method['desc'] }}</p>
                    <a href="{{ $method['link'] }}" style="font-size:.875rem;font-weight:600;color:{{ $method['color'] }};text-decoration:none;">Learn more →</a>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Standards & compliance --}}
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Standards and data model</h2>
                <p class="text-muted">OpesCare data structures align with internationally recognised healthcare information standards.</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.5rem;max-width:960px;margin:0 auto;">
                @foreach([
                    ['title'=>'HL7 FHIR R4','desc'=>'Patient, Encounter, Observation, MedicationRequest, DiagnosticReport resources mapped to the FHIR R4 standard.'],
                    ['title'=>'RESTful JSON','desc'=>'All API endpoints return structured JSON. OpenAPI 3.0 specification available to registered partners.'],
                    ['title'=>'ISO 8583 for Billing','desc'=>'Financial transaction messages for insurance claims and billing reconciliation follow ISO messaging standards.'],
                    ['title'=>'HL7 v2 Import','desc'=>'The Bridge Agent can parse and translate legacy HL7 v2.x message files from older EMR systems into FHIR-compatible records.'],
                ] as $std)
                <div style="background:#F8FAFC;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.75rem;">
                    <h4 style="margin:0 0 .5rem;color:#0F4C81;">{{ $std['title'] }}</h4>
                    <p class="text-muted" style="font-size:.875rem;margin:0;">{{ $std['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="section" style="background:#0F2744;color:#fff;text-align:center;">
        <div class="container" style="max-width:640px;">
            <h2 style="color:#fff;margin-bottom:1rem;">Connect your facility to the OpesCare network</h2>
            <p style="color:rgba(255,255,255,.75);margin-bottom:2rem;">Our integration team will help you choose the right pathway and guide you through the onboarding process.</p>
            <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('register.organization') }}" class="btn btn-primary">Register Your Facility</a>
                <a href="{{ route('public.developers') }}" class="btn" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);">Developer Portal</a>
            </div>
        </div>
    </section>

@endsection
