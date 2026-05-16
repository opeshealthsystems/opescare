@extends('layouts.public')

@section('title', 'OpesCare for Patients | Your Health ID and Medical History')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">For Individuals</div>
            <h1>{{ __('public.solutions.patients.hero_title') }}</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                {{ __('public.solutions.patients.hero_subtitle') }}
            </p>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="card-icon" style="background-color: var(--color-primary-light); color: var(--color-primary);"><i data-lucide="id-card"></i></div>
                    <h2>Your Health ID</h2>
                    <p class="text-muted">Your OpesCare Health ID helps healthcare providers identify your record safely and reduce duplicate records.</p>
                    
                    <div class="mt-8 p-6 bg-white border border-border rounded-2xl shadow-sm" style="margin-top: 2rem; padding: 1.5rem; background: white; border: 1px solid var(--color-border); border-radius: 1rem;">
                        <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 1rem;">
                            <div class="h-12 w-12 bg-primary rounded-full flex items-center justify-center text-white" style="width: 3rem; height: 3rem; background: var(--color-primary); border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center;">JD</div>
                            <div>
                                <h4 style="margin: 0;">John Doe</h4>
                                <p class="text-xs text-muted" style="font-size: 0.75rem; margin: 0;">ID: OPES-123-456</p>
                            </div>
                            <div style="margin-left: auto;">
                                <i data-lucide="qr-code" class="icon-md"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="card">
                        <div class="card-icon"><i data-lucide="history"></i></div>
                        <h3>{{ __('public.solutions.patients.history_title') }}</h3>
                        <p>{{ __('public.solutions.patients.history_desc') }}</p>
                    </div>
                    <div class="card mt-6" style="margin-top: 1.5rem;">
                        <div class="card-icon"><i data-lucide="shield-check"></i></div>
                        <h3>{{ __('public.solutions.patients.control_title') }}</h3>
                        <p>{{ __('public.solutions.patients.control_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="background-color: var(--color-background);">
        <div class="container">
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="siren"></i></div>
                    <h3>Emergency support</h3>
                    <p>In an emergency, approved providers may access a limited emergency profile when normal consent is not possible. This access is recorded and reviewed.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="map-pin-check"></i></div>
                    <h3>Find medication</h3>
                    <p>Where available, you can search for verified pharmacies that recently confirmed medicine stock.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="droplets"></i></div>
                    <h3>Find blood help</h3>
                    <p>If blood is urgently needed, OpesCare can help guide patients and providers toward verified hospitals and blood banks.</p>
                </div>
            </div>
            
            <div style="margin-top: 4rem; padding: 2rem; background-color: var(--color-primary-light); border-radius: 1rem; text-align: center;">
                <p class="text-sm font-bold uppercase tracking-widest text-primary" style="margin-bottom: 0.5rem;">Safety Note</p>
                <p class="text-muted" style="font-size: 0.875rem;">OpesCare does not replace doctors or medical advice. Always follow guidance from qualified healthcare professionals.</p>
            </div>
        </div>
    </section>
@endsection
