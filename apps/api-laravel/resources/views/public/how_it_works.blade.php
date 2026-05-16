@extends('layouts.public')

@section('title', 'How OpesCare Works | Health ID, Consent, and Records')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-teal-light); color: var(--color-teal); margin-bottom: 1rem;">Operational Model</div>
            <h1>{{ __('public.how_it_works.hero_title') }}</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                {{ __('public.how_it_works.hero_subtitle') }}
            </p>
        </div>
    </header>

    <section class="content-body" style="background-color: var(--color-background);">
        <div class="container">
            <div class="card-grid" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));">
                <!-- Step 1 -->
                <div class="card">
                    <div class="card-icon" style="background-color: var(--color-primary); color: white;">1</div>
                    <h3>{{ __('public.how_it_works.step1_title') }}</h3>
                    <p>{{ __('public.how_it_works.step1_desc') }}</p>
                </div>
                <!-- Step 2 -->
                <div class="card">
                    <div class="card-icon" style="background-color: var(--color-primary); color: white;">2</div>
                    <h3>{{ __('public.how_it_works.step2_title') }}</h3>
                    <p>{{ __('public.how_it_works.step2_desc') }}</p>
                </div>
                <!-- Step 3 -->
                <div class="card">
                    <div class="card-icon" style="background-color: var(--color-primary); color: white;">3</div>
                    <h3>{{ __('public.how_it_works.step3_title') }}</h3>
                    <p>{{ __('public.how_it_works.step3_desc') }}</p>
                </div>
                <!-- Step 4 -->
                <div class="card">
                    <div class="card-icon" style="background-color: var(--color-primary); color: white;">4</div>
                    <h3>{{ __('public.how_it_works.step4_title') }}</h3>
                    <p>{{ __('public.how_it_works.step4_desc') }}</p>
                </div>
                <!-- Step 5 -->
                <div class="card">
                    <div class="card-icon" style="background-color: var(--color-primary); color: white;">5</div>
                    <h3>{{ __('public.how_it_works.step5_title') }}</h3>
                    <p>{{ __('public.how_it_works.step5_desc') }}</p>
                </div>
                <!-- Step 6 -->
                <div class="card">
                    <div class="card-icon" style="background-color: var(--color-primary); color: white;">6</div>
                    <h3>{{ __('public.how_it_works.step6_title') }}</h3>
                    <p>{{ __('public.how_it_works.step6_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <h2>Works With Existing Systems</h2>
                    <p class="text-muted">OpesCare allows approved systems to push and pull patient data through secure integration methods.</p>
                    <div class="feature-list mt-6" style="margin-top: 1.5rem; display: grid; gap: 1rem;">
                        <div class="feature-item"><i data-lucide="braces"></i> Direct API Integration</div>
                        <div class="feature-item"><i data-lucide="code"></i> Connect SDK</div>
                        <div class="feature-item"><i data-lucide="panel-top"></i> Connect Widget</div>
                        <div class="feature-item"><i data-lucide="network"></i> Bridge Agent</div>
                    </div>
                </div>
                <div class="hero-visual">
                    <img src="{{ asset('images/interop_diagram.png') }}" alt="Interoperability Diagram" class="hero-image">
                </div>
            </div>
        </div>
    </section>
@endsection
