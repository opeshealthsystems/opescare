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

            <div style="margin-top: 6rem; padding: 3rem; background-color: var(--color-background); border-radius: 1.5rem; border: 1px solid var(--color-border);">
                <div class="hero-grid">
                    <div class="hero-content">
                        <h2>Infrastructure Integrity</h2>
                        <p class="text-muted">Our platform is hosted in secure, highly-available data centres with 24/7 monitoring and automated threat detection.</p>
                        <ul style="list-style:none;padding:0;margin:1.5rem 0 0;display:grid;gap:.75rem;">
                            <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Regular penetration testing and vulnerability scanning</li>
                            <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Zero-trust network architecture — no implicit trust</li>
                            <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Multi-factor authentication (MFA) enforced for all staff</li>
                            <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Automatic failover and high-availability deployment</li>
                            <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Immutable audit logs — cannot be altered after creation</li>
                        </ul>
                    </div>
                    <div class="hero-visual" style="display:flex;justify-content:center;align-items:center;">
                        <div style="position:relative;width:14rem;height:14rem;">
                            <div style="position:absolute;inset:0;border-radius:50%;background:rgba(15,76,129,.06);display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="shield-check" style="width:6rem;height:6rem;color:#0F4C81;opacity:.15;"></i>
                            </div>
                            <div style="position:absolute;inset:1.5rem;border-radius:50%;background:rgba(15,76,129,.08);display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="shield-check" style="width:4rem;height:4rem;color:#0F4C81;opacity:.3;"></i>
                            </div>
                            <div style="position:absolute;inset:3rem;border-radius:50%;background:#0F4C81;display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="shield-check" style="width:2.5rem;height:2.5rem;color:#fff;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Technical controls --}}
    <section class="section" style="background:#F8FAFC;">
        <div class="container">
            <div class="section-header">
                <h2>Technical security controls</h2>
                <p class="text-muted">A layered security model protects patient data at every stage of processing and transmission.</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;max-width:960px;margin:0 auto;">
                @foreach([
                    ['icon'=>'lock','title'=>'AES-256 Encryption at Rest','desc'=>'All stored patient data, audit logs, and access tokens are encrypted using AES-256.'],
                    ['icon'=>'shield','title'=>'TLS 1.3 in Transit','desc'=>'All API communications are encrypted using TLS 1.3. Older protocol versions are rejected.'],
                    ['icon'=>'key-round','title'=>'OAuth 2.0 with Short-Lived Tokens','desc'=>'API access tokens expire quickly. Refresh tokens are rotated on use.'],
                    ['icon'=>'fingerprint','title'=>'Biometric OTP Support','desc'=>'Patient portal supports TOTP-based 2FA as an additional authentication layer.'],
                    ['icon'=>'scan','title'=>'OWASP Top 10 Mitigations','desc'=>'The platform is developed with OWASP Top 10 mitigations applied at every endpoint.'],
                    ['icon'=>'file-search','title'=>'Immutable Audit Logs','desc'=>'Every access event is written to an append-only audit store and cannot be modified.'],
                ] as $ctrl)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.75rem;">
                    <div style="width:2.5rem;height:2.5rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.875rem;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                        <i data-lucide="{{ $ctrl['icon'] }}" style="width:1.1rem;height:1.1rem;"></i>
                    </div>
                    <h4 style="margin:0 0 .4rem;font-size:1rem;">{{ $ctrl['title'] }}</h4>
                    <p class="text-muted" style="font-size:.875rem;margin:0;">{{ $ctrl['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Responsible disclosure --}}
    <section class="section">
        <div class="container" style="max-width:760px;">
            <div style="background:#0F2744;border-radius:1.5rem;padding:3rem;color:#fff;text-align:center;">
                <i data-lucide="bug" style="width:2.5rem;height:2.5rem;color:#14B8A6;margin-bottom:1.5rem;"></i>
                <h2 style="color:#fff;margin-bottom:1rem;">Responsible Disclosure</h2>
                <p style="color:rgba(255,255,255,.75);margin-bottom:2rem;">If you believe you have found a security vulnerability in the OpesCare platform, please contact our security team directly. We commit to acknowledging receipt within 48 hours and providing a timeline for resolution.</p>
                <a href="mailto:security@opesware.com" class="btn btn-primary">Report a Vulnerability</a>
            </div>
        </div>
    </section>
@endsection
