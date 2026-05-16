@extends('layouts.public')

@section('title', 'OpesCare Developers | Connect API & Integration Tools')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">Developer Portal</div>
            <h1>Tools built for healthcare interoperability.</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                Explore our API documentation, SDKs, and integration guides to connect your health system to the OpesCare network.
            </p>
            <div style="margin-top: 2.5rem; display: flex; justify-content: center; gap: 1rem;">
                <a href="#" class="btn btn-primary">API Documentation</a>
                <a href="{{ route('public.status') }}" class="btn btn-secondary">System Status</a>
            </div>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <h2>API First Architecture</h2>
                    <p>OpesCare is built with an API-first philosophy, ensuring that every function available in our UI is also accessible programmatically.</p>
                    <ul class="check-list" style="margin-top: 2rem;">
                        <li style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                            <i data-lucide="check-circle" class="text-primary icon-xs"></i>
                            <span>RESTful JSON API with OAuth2 authentication.</span>
                        </li>
                        <li style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                            <i data-lucide="check-circle" class="text-primary icon-xs"></i>
                            <span>Webhooks for real-time event synchronization.</span>
                        </li>
                        <li style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                            <i data-lucide="check-circle" class="text-primary icon-xs"></i>
                            <span>Sandbox environment for safe testing.</span>
                        </li>
                    </ul>
                </div>
                <div class="hero-visual">
                    <div style="background-color: #1e293b; border-radius: 1rem; padding: 1.5rem; color: #f8fafc; font-family: 'Courier New', Courier, monospace; font-size: 0.875rem; box-shadow: var(--shadow-lg);">
                        <div style="margin-bottom: 1rem; color: #94a3b8;">// Get Patient Timeline</div>
                        <div><span style="color: #f472b6;">GET</span> /api/v1/patients/{id}/timeline</div>
                        <div style="margin-top: 1rem; color: #94a3b8;">// Response</div>
                        <div style="color: #fbbf24;">{</div>
                        <div style="padding-left: 1.5rem;">
                            "status": <span style="color: #4ade80;">"success"</span>,<br>
                            "data": [<br>
                            <span style="padding-left: 1.5rem;">{ "type": "visit", "date": "2026-05-15" }</span><br>
                            ]
                        </div>
                        <div style="color: #fbbf24;">}</div>
                    </div>
                </div>
            </div>

            <div class="section-header" style="margin-top: 6rem;">
                <h2>Integration Resources</h2>
            </div>

            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="book-open"></i></div>
                    <h3>Integration Guides</h3>
                    <p>Step-by-step tutorials on how to register facilities, request consent, and exchange medical records.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="code"></i></div>
                    <h3>SDKs</h3>
                    <p>Pre-built libraries for PHP, JavaScript, Python, and .NET to get you connected in minutes.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="webhook"></i></div>
                    <h3>Webhooks</h3>
                    <p>Subscribe to events like 'new_visit_recorded' or 'consent_granted' to keep your local system in sync.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
