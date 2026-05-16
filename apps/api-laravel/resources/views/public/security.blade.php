@extends('layouts.public')

@section('title', 'Security and Trust | OpesCare Data Protection')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">Core Priority</div>
            <h1>Security and responsibility built into every layer.</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                We prioritize the protection of sensitive health data through institutional-grade security protocols, comprehensive auditing, and a compliance-first architecture.
            </p>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="lock"></i></div>
                    <h3>End-to-End Encryption</h3>
                    <p>All patient data is encrypted both at rest and in transit using industry-standard AES-256 and TLS 1.3 protocols.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="file-search"></i></div>
                    <h3>Comprehensive Auditing</h3>
                    <p>Every single access request, data exchange, and consent change is recorded in an immutable audit log, viewable by patients and facility admins.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="shield-check"></i></div>
                    <h3>Consent-Based Access</h3>
                    <p>Access to sensitive clinical information is strictly controlled by patient consent, except in verified and audited emergency scenarios.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="globe"></i></div>
                    <h3>Compliance First</h3>
                    <p>Designed to align with global health data protection standards (GDPR, HIPAA principles) and local Cameroonian regulations.</p>
                </div>
            </div>

            <div style="margin-top: 6rem; padding: 4rem; background-color: var(--color-background); border-radius: 2rem; border: 1px solid var(--color-border);">
                <div class="hero-grid">
                    <div class="hero-content">
                        <h2>Infrastructure Integrity</h2>
                        <p class="text-muted">Our platform is hosted in secure, highly-available data centers with 24/7 monitoring and automated threat detection.</p>
                        <ul class="check-list mt-6" style="margin-top: 1.5rem;">
                            <li style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;"><i data-lucide="check" class="text-teal icon-xs"></i> Regular penetration testing</li>
                            <li style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;"><i data-lucide="check" class="text-teal icon-xs"></i> Zero-trust network architecture</li>
                            <li style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;"><i data-lucide="check" class="text-teal icon-xs"></i> Multi-factor authentication (MFA)</li>
                        </ul>
                    </div>
                    <div class="hero-visual" style="display: flex; justify-content: center;">
                        <i data-lucide="shield-check" style="width: 12rem; height: 12rem; color: var(--color-primary); opacity: 0.1;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
