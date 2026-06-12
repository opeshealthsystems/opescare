@extends('layouts.public')

@section('title', 'Consent and Patient Rights | OpesCare')
@section('meta_description', 'Understand how OpesCare puts patients in control of their health information through consent, scoped access, audit logs, and the right to revoke.')

@section('content')

    {{-- Hero --}}
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color:rgba(20,184,166,.15);color:#0F766E;margin-bottom:1rem;">Consent-First by Design</div>
            <h1>You stay in control of your health information.</h1>
            <p class="text-muted" style="max-width:760px;margin:0 auto;font-size:1.2rem;">
                OpesCare uses a consent-first model. By default your medical records are private and inaccessible until you explicitly authorise access. Every request, approval, and access event is logged and available to you.
            </p>
            <div style="margin-top:2.5rem;display:flex;justify-content:center;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('register.patient') }}" class="btn btn-primary">Get Your Health ID</a>
                <a href="{{ route('public.security') }}" class="btn btn-secondary">Security Architecture</a>
            </div>
        </div>
    </header>

    {{-- Core pillars --}}
    <section class="content-body">
        <div class="container">
            <div class="section-header">
                <h2>The four pillars of patient data control</h2>
                <p class="text-muted">Every access request flows through these layers before any information is shared.</p>
            </div>

            <div class="card-grid">
                <div class="card">
                    <div class="card-icon" style="background:rgba(15,76,129,.1);color:#0F4C81;"><i data-lucide="clipboard-list"></i></div>
                    <h3>Consent Requests</h3>
                    <p>When a healthcare provider needs access to your records, they submit a structured consent request showing who is asking, why they need the information, exactly what they want to see, and how long the access will last.</p>
                </div>
                <div class="card">
                    <div class="card-icon" style="background:rgba(15,76,129,.1);color:#0F4C81;"><i data-lucide="sliders-horizontal"></i></div>
                    <h3>Scoped Access</h3>
                    <p>Providers receive only the specific information needed for their role and purpose. A pharmacy does not automatically see your full diagnosis history. A lab receives only what is required to process the order.</p>
                </div>
                <div class="card">
                    <div class="card-icon" style="background:rgba(15,76,129,.1);color:#0F4C81;"><i data-lucide="eye"></i></div>
                    <h3>Access Logs</h3>
                    <p>Every time your record is accessed, you can see which facility or role made the request, the timestamp, the stated purpose, and which specific data was retrieved.</p>
                </div>
                <div class="card">
                    <div class="card-icon" style="background:rgba(15,76,129,.1);color:#0F4C81;"><i data-lucide="shield-off"></i></div>
                    <h3>Revocation</h3>
                    <p>You can revoke access for any approved provider where policy allows. Once revoked, that facility can no longer pull your records without submitting a new consent request.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How consent flow works --}}
    <section class="section" style="background:#F0F9FF;">
        <div class="container">
            <div class="section-header">
                <h2>How a consent request works</h2>
                <p class="text-muted">A step-by-step view of the consent workflow from provider request to access.</p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:2rem;counter-reset:step;">
                @php
                $steps = [
                    ['icon'=>'send','title'=>'Provider Submits Request','desc'=>'A doctor or facility requests access, specifying purpose, scope, and duration.'],
                    ['icon'=>'bell','title'=>'Patient Is Notified','desc'=>'You receive a notification showing all details of who is asking and why.'],
                    ['icon'=>'check-circle-2','title'=>'You Approve or Deny','desc'=>'You decide whether to approve, deny, or approve with modified scope.'],
                    ['icon'=>'download','title'=>'Access Is Granted','desc'=>'If approved, the provider pulls only the consented information — nothing more.'],
                    ['icon'=>'scroll-text','title'=>'Access Is Logged','desc'=>'A full audit entry is recorded, visible to you and your care team.'],
                    ['icon'=>'shield-off','title'=>'You Can Revoke','desc'=>'At any time you can revoke active consent from your patient timeline.'],
                ];
                @endphp
                @foreach($steps as $i => $step)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.75rem;position:relative;">
                    <div style="width:2.25rem;height:2.25rem;background:#0F4C81;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.875rem;margin-bottom:1rem;">{{ $i+1 }}</div>
                    <i data-lucide="{{ $step['icon'] }}" style="width:1.5rem;height:1.5rem;color:#14B8A6;margin-bottom:.75rem;display:block;"></i>
                    <h4 style="margin:0 0 .5rem;">{{ $step['title'] }}</h4>
                    <p class="text-muted" style="font-size:.875rem;margin:0;">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Emergency access --}}
    <section class="section">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="badge" style="background:rgba(239,68,68,.1);color:#dc2626;margin-bottom:1rem;">Emergency Access</div>
                    <h2>Emergency access when every second matters — with full accountability.</h2>
                    <p class="text-muted" style="margin-bottom:1.5rem;">In a life-threatening emergency where you cannot provide consent, an approved provider may access a limited emergency profile. This action is never silent.</p>
                    <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem;">
                        <li style="display:flex;gap:.75rem;align-items:flex-start;">
                            <i data-lucide="alert-triangle" style="width:1.1rem;height:1.1rem;color:#dc2626;flex-shrink:0;margin-top:.15rem;"></i>
                            <span>Emergency access triggers an immediate high-priority audit alert.</span>
                        </li>
                        <li style="display:flex;gap:.75rem;align-items:flex-start;">
                            <i data-lucide="file-pen-line" style="width:1.1rem;height:1.1rem;color:#dc2626;flex-shrink:0;margin-top:.15rem;"></i>
                            <span>The provider must state a documented clinical reason before access is granted.</span>
                        </li>
                        <li style="display:flex;gap:.75rem;align-items:flex-start;">
                            <i data-lucide="users" style="width:1.1rem;height:1.1rem;color:#dc2626;flex-shrink:0;margin-top:.15rem;"></i>
                            <span>Compliance officers and facility admins are notified automatically.</span>
                        </li>
                        <li style="display:flex;gap:.75rem;align-items:flex-start;">
                            <i data-lucide="eye" style="width:1.1rem;height:1.1rem;color:#dc2626;flex-shrink:0;margin-top:.15rem;"></i>
                            <span>Only a limited emergency profile is accessible — not your full history.</span>
                        </li>
                    </ul>
                </div>
                <div class="hero-visual">
                    <div style="background:#0F2744;border-radius:1.5rem;padding:2rem;color:#fff;">
                        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
                            <i data-lucide="siren" style="width:1.5rem;height:1.5rem;color:#f87171;"></i>
                            <span style="font-weight:700;font-size:.875rem;text-transform:uppercase;letter-spacing:.05em;color:#f87171;">Emergency Profile</span>
                        </div>
                        <div style="display:grid;gap:.75rem;font-size:.875rem;">
                            <div style="background:rgba(255,255,255,.07);border-radius:.75rem;padding:1rem;">
                                <div style="color:#94a3b8;font-size:.75rem;margin-bottom:.25rem;">Patient Identity</div>
                                <div style="font-weight:600;">Full Name &amp; Blood Group</div>
                            </div>
                            <div style="background:rgba(255,255,255,.07);border-radius:.75rem;padding:1rem;">
                                <div style="color:#94a3b8;font-size:.75rem;margin-bottom:.25rem;">Critical Allergies</div>
                                <div style="font-weight:600;color:#f87171;">Penicillin — Contraindicated</div>
                            </div>
                            <div style="background:rgba(255,255,255,.07);border-radius:.75rem;padding:1rem;">
                                <div style="color:#94a3b8;font-size:.75rem;margin-bottom:.25rem;">Active Chronic Conditions</div>
                                <div style="font-weight:600;">Type 2 Diabetes, Hypertension</div>
                            </div>
                            <div style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);border-radius:.75rem;padding:.75rem;font-size:.75rem;color:#fca5a5;">
                                <i data-lucide="alert-circle" style="width:.875rem;height:.875rem;display:inline;vertical-align:middle;margin-right:.25rem;"></i>
                                Emergency access logged — Compliance notified
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Rights summary --}}
    <section class="section" style="background:#F8FAFC;">
        <div class="container">
            <div class="section-header">
                <h2>Your rights under OpesCare</h2>
                <p class="text-muted">These rights apply to every patient registered on the platform.</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;max-width:960px;margin:0 auto;">
                @php
                $rights = [
                    ['icon'=>'eye','title'=>'Right to Know','desc'=>'See who has requested or accessed your records, when, why, and what data was retrieved.'],
                    ['icon'=>'hand','title'=>'Right to Refuse','desc'=>'Deny any non-emergency access request from any provider at any time.'],
                    ['icon'=>'shield-off','title'=>'Right to Revoke','desc'=>'Remove previously granted access where policy and regulations allow.'],
                    ['icon'=>'file-check','title'=>'Right to Review','desc'=>'Access your own complete medical timeline and consent history at any time.'],
                    ['icon'=>'file-edit','title'=>'Right to Correct','desc'=>'Request corrections to your identity information if it is inaccurate.'],
                    ['icon'=>'phone','title'=>'Right to Support','desc'=>'Contact our privacy team at privacy@opeshealthsystems.com for any data concern.'],
                ];
                @endphp
                @foreach($rights as $right)
                <div style="display:flex;gap:1rem;align-items:flex-start;background:#fff;border:1px solid #e2e8f0;border-radius:1rem;padding:1.5rem;">
                    <div style="width:2.5rem;height:2.5rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="{{ $right['icon'] }}" style="width:1.1rem;height:1.1rem;"></i>
                    </div>
                    <div>
                        <h4 style="margin:0 0 .4rem;font-size:1rem;">{{ $right['title'] }}</h4>
                        <p class="text-muted" style="font-size:.875rem;margin:0;">{{ $right['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="section" style="background:#0F2744;color:#fff;text-align:center;">
        <div class="container" style="max-width:640px;">
            <i data-lucide="shield-check" style="width:3rem;height:3rem;color:#14B8A6;margin-bottom:1.5rem;"></i>
            <h2 style="color:#fff;margin-bottom:1rem;">Ready to take control of your health records?</h2>
            <p style="color:rgba(255,255,255,.75);margin-bottom:2rem;">Register for a free OpesCare Health ID and manage your medical information across every facility you visit.</p>
            <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('register.patient') }}" class="btn btn-primary">Get Your Health ID</a>
                <a href="{{ route('public.privacy') }}" class="btn" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);">Privacy Policy</a>
            </div>
        </div>
    </section>

@endsection
