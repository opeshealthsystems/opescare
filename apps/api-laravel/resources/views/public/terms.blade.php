@extends('layouts.public')

@section('title', 'Terms and Conditions | OpesCare Legal')

@section('content')
    <header class="content-header">
        <div class="container">
            <h1>Terms and Conditions</h1>
            <p class="text-muted">The legal framework for using the OpesCare platform.</p>
        </div>
    </header>

    <section class="content-body">
        <div class="container rich-text">
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing or using the OpesCare platform, you agree to be bound by these Terms and Conditions. If you are using the platform on behalf of an institution (hospital, clinic, pharmacy), you represent that you have the authority to bind that entity to these terms.</p>

            <h2>2. Purpose of the Platform</h2>
            <p>OpesCare is an interoperability and health identity platform. We facilitate the secure exchange of medical records between authorized healthcare providers with patient consent. OpesCare is not a medical provider and does not provide clinical advice.</p>

            <h2>3. User Responsibilities</h2>
            <ul>
                <li><strong>Individuals:</strong> You are responsible for maintaining the security of your Health ID key and QR code.</li>
                <li><strong>Institutions:</strong> You must ensure that all staff accessing the platform are properly trained and authorized, and that access is only sought for valid clinical or administrative purposes.</li>
            </ul>

            <h2>4. Data Protection & Privacy</h2>
            <p>Your use of the platform is also governed by our <a href="{{ route('public.privacy') }}" class="text-primary font-bold">Privacy Policy</a>, which details how we process health information in accordance with applicable laws.</p>

            <h2>5. Limitation of Liability</h2>
            <p>OpesCare provides the infrastructure for data exchange but is not responsible for the clinical accuracy of records provided by third-party facilities. Healthcare decisions must always be made by qualified professionals based on their clinical judgment.</p>

            <div style="margin-top: 4rem; padding: 2rem; background-color: var(--color-bg); border-radius: 1rem; font-size: 0.875rem;">
                <p>Last Updated: May 16, 2026. For questions regarding these terms, contact legal@opesware.com.</p>
            </div>
        </div>
    </section>
@endsection
