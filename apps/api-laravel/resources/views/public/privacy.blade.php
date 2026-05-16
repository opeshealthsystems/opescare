@extends('layouts.public')

@section('title', 'Privacy Policy | OpesCare Patient Data Processing')

@section('content')
    <header class="content-header">
        <div class="container">
            <h1>Privacy and Patient Data Processing</h1>
            <p class="text-muted">How we handle your health information with care and transparency.</p>
        </div>
    </header>

    <section class="content-body">
        <div class="container rich-text">
            <p class="lead">At OpesCare, we believe that your health data belongs to you. Our platform is designed to give you control over who can see your records and for what purpose.</p>

            <h2>1. Information We Collect</h2>
            <p>We process information necessary to provide a secure digital Health ID and connected medical records, including:</p>
            <ul>
                <li>Basic identity information (Name, Date of Birth, Gender)</li>
                <li>Clinical history provided by your healthcare facilities</li>
                <li>Consent preferences and access logs</li>
            </ul>

            <h2>2. How We Use Data</h2>
            <p>Your data is used exclusively for:</p>
            <ul>
                <li>Clinical care coordination between approved providers</li>
                <li>Emergency access in critical situations</li>
                <li>Verifying medication and blood availability</li>
                <li>System auditing and security monitoring</li>
            </ul>

            <h2>3. Who Can Access Your Data</h2>
            <p>Only healthcare providers and institutions that you have explicitly authorized can access your medical records. In rare, life-threatening emergencies, approved providers may access a limited emergency profile; this action is strictly audited and reviewed.</p>

            <h2>4. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access your medical timeline at any time</li>
                <li>See who has requested or accessed your records</li>
                <li>Grant or revoke consent for specific institutions</li>
                <li>Request corrections to your identity information</li>
            </ul>

            <div style="margin-top: 4rem; padding: 2rem; background-color: var(--color-background); border-radius: 1rem; font-size: 0.875rem;">
                <p>Last Updated: May 16, 2026. For detailed legal terms, please refer to our <a href="{{ route('public.terms') }}" class="text-primary font-bold">Terms and Conditions</a>.</p>
            </div>
        </div>
    </section>
@endsection
