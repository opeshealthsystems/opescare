@extends('layouts.public')

@section('title', 'Consent and Patient Rights | OpesCare')

@section('content')
    <header class="content-header">
        <div class="container">
            <h1>Consent and Patient Rights</h1>
            <p class="text-muted">Understanding your control over your medical information.</p>
        </div>
    </header>

    <section class="content-body">
        <div class="container rich-text">
            <h2>The Consent Model</h2>
            <p>OpesCare is built on a "Consent-First" model. This means that, by default, your medical information is private and inaccessible to any facility until you explicitly grant them permission.</p>

            <h2>Your Rights</h2>
            <ul>
                <li>The right to know who has accessed your record.</li>
                <li>The right to revoke access at any time.</li>
                <li>The right to define what specific information is shared.</li>
                <li>The right to access your clinical timeline and records.</li>
            </ul>

            <h2>Emergency Access</h2>
            <p>In life-threatening emergencies where you cannot provide consent, approved providers may access a limited emergency profile. This action triggers an institutional audit to ensure the access was justified.</p>
        </div>
    </section>
@endsection
