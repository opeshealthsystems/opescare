@extends('layouts.public')

@section('title', 'OpesCare for Insurers | Controlled Access & Claims Support')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">For Insurers</div>
            <h1>Insurance workflows with controlled access to necessary information.</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                OpesCare can support eligibility checks, claims, preauthorization, and documentation while protecting patients from unnecessary exposure of their full medical history.
            </p>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="shield-check"></i></div>
                    <h3>Coverage Verification</h3>
                    <p>Hospitals can verify patient eligibility and coverage limits in real-time, reducing rejected claims and financial delays.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="file-text"></i></div>
                    <h3>Claim Submission</h3>
                    <p>Streamline claim processing with digital documentation and automated submission workflows directly from clinical events.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="lock"></i></div>
                    <h3>Minimum Necessary Access</h3>
                    <p>Insurers receive only the specific clinical data required for a claim, upholding the highest standards of patient privacy.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="file-search"></i></div>
                    <h3>Audit & Accountability</h3>
                    <p>Full transparency into who requested data, what was shared, and the legal basis for the exchange.</p>
                </div>
            </div>
            
            <div style="margin-top: 4rem; padding: 3rem; background-color: var(--color-primary-dark); color: white; border-radius: 2rem;">
                <h3 style="color: white; margin-top: 0;">Policy on Data minimization</h3>
                <p style="color: rgba(255,255,255,0.8); font-size: 1.125rem;">
                    Insurers do not automatically receive full patient history. OpesCare supports scoped access based on role, purpose, policy, and patient consent where required. This ensures that sensitive medical data is never exposed unnecessarily.
                </p>
            </div>
        </div>
    </section>
@endsection
