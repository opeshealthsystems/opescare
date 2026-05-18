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
            
            <div style="margin-top: 4rem; padding: 1.5rem 2rem; background-color: var(--color-primary-light); border-radius: 1rem; display:flex; align-items:flex-start; gap:1rem;">
                <i data-lucide="plug-zap" style="width:1.5rem;height:1.5rem;color:#0F4C81;flex-shrink:0;margin-top:.1rem;"></i>
                <div>
                    <p class="text-sm font-bold uppercase tracking-widest text-primary" style="margin-bottom: 0.5rem;">Integration Options</p>
                    <p style="margin:0;">Pharmacies can connect by <a href="{{ route('public.developers') }}#api" style="color:#0F4C81;font-weight:600;">Connect API</a>, <a href="{{ route('public.developers') }}#sdk" style="color:#0F4C81;font-weight:600;">SDK</a>, <a href="{{ route('public.developers') }}#bridge" style="color:#0F4C81;font-weight:600;">Bridge Agent</a>, or the <a href="{{ route('public.developers') }}#lite" style="color:#0F4C81;font-weight:600;">OpesCare Lite</a> browser portal.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="background:#0F2744;color:#fff;text-align:center;">
        <div class="container" style="max-width:640px;">
            <h2 style="color:#fff;margin-bottom:1rem;">Connect your pharmacy to the OpesCare network</h2>
            <p style="color:rgba(255,255,255,.75);margin-bottom:2rem;">Register your pharmacy to start receiving verified prescriptions, publishing medicine availability, and contributing to safer patient care.</p>
            <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('register.organization') }}" class="btn btn-primary">Register Your Pharmacy</a>
                <a href="{{ route('public.contact') }}" class="btn" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);">Contact Us</a>
            </div>
        </div>
    </section>
@endsection
