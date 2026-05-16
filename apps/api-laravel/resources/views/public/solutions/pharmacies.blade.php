@extends('layouts.public')

@section('title', 'OpesCare for Pharmacies | Connected Medication Availability')

@section('content')
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background-color: var(--color-primary-light); color: var(--color-primary); margin-bottom: 1rem;">For Pharmacies</div>
            <h1>Help patients find medicines faster and dispense prescriptions more safely.</h1>
            <p class="text-muted" style="max-width: 800px; margin: 0 auto; font-size: 1.25rem;">
                OpesCare helps verified pharmacies connect prescriptions, dispensing records, medicine availability, and stock updates with the patient’s approved health record.
            </p>
        </div>
    </header>

    <section class="content-body">
        <div class="container">
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="file-plus-2"></i></div>
                    <h3>Prescription Support</h3>
                    <p>Pharmacies can verify active prescriptions and dispensing status directly through the OpesCare platform, reducing fraud and clinical errors.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="store"></i></div>
                    <h3>Medicine Availability</h3>
                    <p>Synchronize your available stock so patients and providers know where a medicine is available, saving lives in critical situations.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="bookmark-check"></i></div>
                    <h3>Reservation Option</h3>
                    <p>Allow patients to request temporary medicine reservations while they travel to your location.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="shield-alert"></i></div>
                    <h3>Stock Safety</h3>
                    <p>Ensure that expired, recalled, or quarantined stock does not appear as available to the public or providers.</p>
                </div>
            </div>
            
            <div style="margin-top: 4rem; padding: 2rem; background-color: var(--color-primary-light); border-radius: 1rem;">
                <p class="text-sm font-bold uppercase tracking-widest text-primary" style="margin-bottom: 0.5rem;">Integration Options</p>
                <p>Pharmacies can connect by API, SDK, Bridge Agent, or the OpesCare Lite portal.</p>
            </div>
        </div>
    </section>
@endsection
