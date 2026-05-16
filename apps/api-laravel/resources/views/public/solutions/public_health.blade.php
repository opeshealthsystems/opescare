@extends('layouts.public')

@section('title', 'For Public Health and Research | OpesCare Insights')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">Population Health</div>
            <h1>Aggregated insights for safer, healthier communities.</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                OpesCare provides anonymized and aggregated data tools to help public health authorities and researchers understand disease trends while protecting individual privacy.
            </p>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="line-chart"></i></div>
                    <h3>Disease Surveillance</h3>
                    <p>Real-time detection of disease outbreaks through aggregated clinical encounter data across multiple facilities.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="microscope"></i></div>
                    <h3>Clinical Research</h3>
                    <p>Access anonymized longitudinal data sets for clinical trials and public health studies with institutional approval.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="shield-check"></i></div>
                    <h3>Ethical Compliance</h3>
                    <p>Built-in protocols for de-identification and ethical data usage in accordance with international health research standards.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
