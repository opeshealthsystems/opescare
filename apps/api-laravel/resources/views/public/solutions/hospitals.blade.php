@extends('layouts.public')

@section('title', 'OpesCare for Hospitals and Clinics | Connected Patient Records')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">For Institutions</div>
            <h1>{{ __('public.solutions.hospitals.hero_title') }}</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                {{ __('public.solutions.hospitals.hero_subtitle') }}
            </p>
            <div style="margin-top: 2.5rem;">
                <a href="{{ route('public.contact') }}" class="btn btn-primary">{{ __('landing.nav.demo') }}</a>
            </div>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="section-header">
                <h2>Institutional Benefits</h2>
                <p>OpesCare provides the infrastructure needed for modern, connected healthcare delivery.</p>
            </div>
            
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="users"></i></div>
                    <h3>Reduce Duplicate Records</h3>
                    <p>Use the global Health ID to uniquely identify patients across your network, reducing clinical errors and administrative overhead.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="history"></i></div>
                    <h3>See Approved History</h3>
                    <p>Access patient visits, allergies, and lab results from other facilities when authorized, leading to faster diagnosis.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="share-2"></i></div>
                    <h3>Improved Referrals</h3>
                    <p>Seamlessly share clinical documentation and results with referral partners through secure, consent-based workflows.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="shield-check"></i></div>
                    <h3>Audit and Compliance</h3>
                    <p>Every access is logged and recorded, providing a robust trail for clinical accountability and data protection compliance.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="background-color: var(--color-primary-dark); color: white;">
        <div class="container">
            <div class="section-header">
                <h2 style="color: white;">Standard Institutional Workflow</h2>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-icon-wrapper" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">1</div>
                    <h4 style="color: white;">Register Patient</h4>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.875rem;">Identify or create a secure Health ID for the patient.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon-wrapper" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">2</div>
                    <h4 style="color: white;">Request Consent</h4>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.875rem;">Request specific access to historical medical records.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon-wrapper" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">3</div>
                    <h4 style="color: white;">Pull Records</h4>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.875rem;">Retrieve approved clinical history for current care.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon-wrapper" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">4</div>
                    <h4 style="color: white;">Push Updates</h4>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.875rem;">Send new care events and results back to the timeline.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container text-center">
            <h2>Ready to transform your facility?</h2>
            <p class="text-muted" style="margin-bottom: 2rem;">Join the growing network of hospitals and clinics using OpesCare.</p>
            <a href="{{ route('public.contact') }}" class="btn btn-primary btn-lg">Request Hospital Partnership Demo</a>
        </div>
    </section>
@endsection
