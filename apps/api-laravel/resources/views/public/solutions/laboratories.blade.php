@extends('layouts.public')

@section('title', 'OpesCare for Laboratories | Verified Results Integration')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">For Laboratories</div>
            <h1>Verified lab results connected to the patient’s medical history.</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                OpesCare helps laboratories receive orders, track samples, validate results, release reports, and connect verified results to the right patient timeline.
            </p>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="flask-conical"></i></div>
                    <h3>Lab Orders</h3>
                    <p>Receive digital lab orders directly from hospitals and clinics, reducing manual data entry errors and sample confusion.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="barcode"></i></div>
                    <h3>Sample Tracking</h3>
                    <p>Track samples from collection to validation with unique institutional IDs and timestamps.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="badge-check"></i></div>
                    <h3>Result Validation</h3>
                    <p>Maintain high clinical standards with built-in validation workflows for senior lab technicians and pathologists.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="history"></i></div>
                    <h3>Timeline Integration</h3>
                    <p>Automatically push verified results to the patient's global medical timeline for authorized providers to see.</p>
                </div>
            </div>
            
            <div class="feature-list mt-12" style="margin-top: 3rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div class="feature-item"><i data-lucide="triangle-alert"></i> Critical Result Alerts</div>
                <div class="feature-item"><i data-lucide="file-pen-line"></i> Result Amendment Logs</div>
                <div class="feature-item"><i data-lucide="cable"></i> External Lab Integration</div>
            </div>
        </div>
    </section>
@endsection
