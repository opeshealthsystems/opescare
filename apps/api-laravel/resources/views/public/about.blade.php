@extends('layouts.public')

@section('title', 'About OpesCare | Digital Health ID and Connected Medical Records')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">Institutional Identity</div>
            <h1>{{ __('public.about.hero_title') }}</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                {{ __('public.about.hero_subtitle') }}
            </p>
            <div style="margin-top: 2.5rem; display: flex; justify-content: center; gap: 1rem;">
                <a href="{{ route('public.contact') }}" class="btn btn-primary">{{ __('landing.hero.cta_demo') }}</a>
                <a href="{{ route('public.how-it-works') }}" class="btn btn-secondary">{{ __('landing.nav.how_it_works') }}</a>
            </div>
        </div>
    </header>

    <section class="content-body">
        <div class="container rich-text">
            <h2>{{ __('public.about.why_title') }}</h2>
            <p>{{ __('public.about.why_content') }}</p>

            <div style="margin: 4rem 0; padding: 3rem; background-color: var(--color-primary); color: white; border-radius: 2rem;">
                <h2 style="color: white; margin-top: 0;">{{ __('public.about.mission_title') }}</h2>
                <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.5rem; line-height: 1.4;">
                    "{{ __('public.about.mission_content') }}"
                </p>
            </div>

            <h2>What OpesCare Is</h2>
            <ul>
                <li>A digital Health ID platform</li>
                <li>A patient medical history platform</li>
                <li>A consent and access control system</li>
                <li>An interoperability layer for hospitals and health systems</li>
                <li>A medicine and blood availability coordination platform</li>
            </ul>

            <h2 style="margin-top: 4rem;">{{ __('public.about.built_by_title') }}</h2>
            <p>{{ __('public.about.built_by_content') }}</p>
            
            <div style="margin-top: 4rem; padding-top: 4rem; border-top: 1px solid var(--color-border); text-align: center;">
                <h3>{{ __('public.about.footer_cta_title') }}</h3>
                <a href="https://opesware.com" class="btn btn-primary" style="margin-top: 1.5rem;">{{ __('public.about.footer_cta_btn') }}</a>
            </div>
        </div>
    </section>
@endsection
