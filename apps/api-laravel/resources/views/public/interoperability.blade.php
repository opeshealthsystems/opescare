@extends('layouts.public')

@section('title', 'OpesCare Interoperability | Connect Healthcare Systems')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">Core Infrastructure</div>
            <h1>Built to connect healthcare systems, not replace them all.</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                OpesCare allows approved systems to push and pull patient data through secure APIs, SDKs, widgets, bridge agents, and webhooks.
            </p>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <h2>Push and Pull Model</h2>
                    <p class="text-muted">Our architecture is designed for bi-directional data flow while maintaining strict consent controls.</p>
                    
                    <div style="margin-top: 2rem; space-y-4">
                        <div style="padding: 1.5rem; background: white; border: 1px solid var(--color-border); border-radius: 1rem; margin-bottom: 1rem;">
                            <h4 style="margin: 0; color: var(--color-primary);">Push</h4>
                            <p class="text-sm" style="margin: 0.5rem 0 0;">A facility sends new patient data (visits, results, prescriptions) to OpesCare.</p>
                        </div>
                        <div style="padding: 1.5rem; background: white; border: 1px solid var(--color-border); border-radius: 1rem;">
                            <h4 style="margin: 0; color: var(--color-teal);">Pull</h4>
                            <p class="text-sm" style="margin: 0.5rem 0 0;">A facility requests approved patient information from OpesCare for clinical care.</p>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <img src="{{ asset('images/push_pull_diagram.png') }}" alt="Push Pull Model" class="hero-image">
                </div>
            </div>

            <div class="section-header" style="margin-top: 6rem;">
                <h2>Integration Methods</h2>
            </div>
            
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="braces"></i></div>
                    <h3>Connect API</h3>
                    <p>Direct system-to-system RESTful API for modern healthcare platforms and enterprise vendors.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="code-2"></i></div>
                    <h3>Connect SDK</h3>
                    <p>Developer libraries in PHP, JS, and Python to accelerate integration for development teams.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="panel-top"></i></div>
                    <h3>Connect Widget</h3>
                    <p>A secure, pre-built web component for patient search and consent that can be embedded in any web app.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="network"></i></div>
                    <h3>Bridge Agent</h3>
                    <p>A lightweight local service for legacy systems, file exports, and semi-offline environments.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
