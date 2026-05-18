@extends('layouts.public')

@section('title', 'Help Center | OpesCare Support')
@section('meta_description', 'Find guides, tutorials, and support resources for patients, healthcare institutions, and developers using the OpesCare platform.')

@section('content')

    {{-- Hero --}}
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background:rgba(20,184,166,.15);color:#0F766E;margin-bottom:1rem;">Help &amp; Support</div>
            <h1>How can we help you?</h1>
            <p class="text-muted" style="max-width:680px;margin:0 auto;font-size:1.2rem;">
                Browse our guides and support resources for patients, healthcare institutions, and developers.
            </p>
            {{-- Search bar --}}
            <div style="margin-top:2.5rem;max-width:560px;margin-left:auto;margin-right:auto;">
                <form action="{{ route('public.contact') }}" method="GET" style="display:flex;gap:.5rem;">
                    <input type="text" name="q" placeholder="Search help articles…"
                           style="flex:1;height:3rem;padding:0 1.25rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9375rem;outline:none;background:#fff;"
                           aria-label="Search help articles">
                    <button type="submit" class="btn btn-primary" style="height:3rem;padding:0 1.5rem;">Search</button>
                </form>
            </div>
        </div>
    </header>

    {{-- Audience cards --}}
    <section class="content-body">
        <div class="container">
            <div class="section-header">
                <h2>Browse by audience</h2>
                <p class="text-muted">Find the guides that are right for your role on the OpesCare platform.</p>
            </div>
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon" style="background:rgba(15,76,129,.1);color:#0F4C81;"><i data-lucide="user"></i></div>
                    <h3>For Patients</h3>
                    <p>Learn how to register for a Health ID, review your clinical timeline, manage consent requests, and understand your privacy rights.</p>
                    <ul style="list-style:none;padding:0;margin:1.25rem 0 0;display:grid;gap:.5rem;font-size:.875rem;">
                        <li><a href="{{ route('public.solutions.patients') }}" style="color:#0F4C81;text-decoration:none;">→ What is a Health ID?</a></li>
                        <li><a href="{{ route('register.patient') }}" style="color:#0F4C81;text-decoration:none;">→ How to register</a></li>
                        <li><a href="{{ route('public.consent') }}" style="color:#0F4C81;text-decoration:none;">→ Managing consent</a></li>
                        <li><a href="{{ route('public.privacy') }}" style="color:#0F4C81;text-decoration:none;">→ Your data and privacy</a></li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-icon" style="background:rgba(15,76,129,.1);color:#0F4C81;"><i data-lucide="hospital"></i></div>
                    <h3>For Institutions</h3>
                    <p>Training resources for hospital admins, doctors, nurses, lab technicians, and pharmacy staff on clinical workflows and integration.</p>
                    <ul style="list-style:none;padding:0;margin:1.25rem 0 0;display:grid;gap:.5rem;font-size:.875rem;">
                        <li><a href="{{ route('public.solutions.hospitals') }}" style="color:#0F4C81;text-decoration:none;">→ Hospital onboarding guide</a></li>
                        <li><a href="{{ route('public.solutions.laboratories') }}" style="color:#0F4C81;text-decoration:none;">→ Lab integration guide</a></li>
                        <li><a href="{{ route('public.solutions.pharmacies') }}" style="color:#0F4C81;text-decoration:none;">→ Pharmacy guide</a></li>
                        <li><a href="{{ route('register.organization') }}" style="color:#0F4C81;text-decoration:none;">→ Register your facility</a></li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-icon" style="background:rgba(15,76,129,.1);color:#0F4C81;"><i data-lucide="code-2"></i></div>
                    <h3>For Developers</h3>
                    <p>Technical documentation, API references, SDK guides, and integration tutorials for building on the OpesCare platform.</p>
                    <ul style="list-style:none;padding:0;margin:1.25rem 0 0;display:grid;gap:.5rem;font-size:.875rem;">
                        <li><a href="{{ route('public.developers') }}" style="color:#0F4C81;text-decoration:none;">→ Developer portal</a></li>
                        <li><a href="{{ route('public.developers') }}#sdk" style="color:#0F4C81;text-decoration:none;">→ SDK documentation</a></li>
                        <li><a href="{{ route('public.interoperability') }}" style="color:#0F4C81;text-decoration:none;">→ Interoperability overview</a></li>
                        <li><a href="{{ route('public.status') }}" style="color:#0F4C81;text-decoration:none;">→ API status and uptime</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Popular topics --}}
    <section class="section" style="background:#F0F9FF;">
        <div class="container">
            <div class="section-header">
                <h2>Popular help topics</h2>
                <p class="text-muted">Frequently referenced guides across all user types.</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;max-width:960px;margin:0 auto;">
                @php
                $topics = [
                    ['icon'=>'id-card','title'=>'Getting a Health ID','desc'=>'Step-by-step guide for patients registering on OpesCare for the first time.','link'=>route('register.patient')],
                    ['icon'=>'qr-code','title'=>'Using Your QR Code','desc'=>'How to present your Health ID QR code at a clinic, lab, or pharmacy.','link'=>route('portals.patient')],
                    ['icon'=>'shield-check','title'=>'Reviewing Consent Requests','desc'=>'How to approve, deny, or modify access requests from healthcare providers.','link'=>route('public.consent')],
                    ['icon'=>'history','title'=>'Viewing Your Timeline','desc'=>'How to read and understand your clinical visit, prescription, and lab history.','link'=>route('portals.patient')],
                    ['icon'=>'building-2','title'=>'Registering a Facility','desc'=>'Guide for hospitals, clinics, labs, and pharmacies applying to join the network.','link'=>route('register.organization')],
                    ['icon'=>'key','title'=>'Lost or Forgotten Password','desc'=>'How to recover access to your account securely.','link'=>route('password.request')],
                    ['icon'=>'webhook','title'=>'Webhooks and Events','desc'=>'Developer guide to subscribing to OpesCare real-time event streams.','link'=>route('public.developers').'#webhooks'],
                    ['icon'=>'phone','title'=>'Contact Support','desc'=>'Reach our support team for issues not covered in the help center.','link'=>route('public.contact')],
                ];
                @endphp
                @foreach($topics as $topic)
                <a href="{{ $topic['link'] }}" style="display:flex;gap:1rem;align-items:flex-start;background:#fff;border:1px solid #e2e8f0;border-radius:1rem;padding:1.5rem;text-decoration:none;transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(15,76,129,.12)'" onmouseout="this.style.boxShadow='none'">
                    <div style="width:2.25rem;height:2.25rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="{{ $topic['icon'] }}" style="width:1rem;height:1rem;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;color:#0F2744;font-size:.9375rem;margin-bottom:.25rem;">{{ $topic['title'] }}</div>
                        <div style="font-size:.8125rem;color:#64748b;">{{ $topic['desc'] }}</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Still need help --}}
    <section class="section" style="background:#0F2744;color:#fff;text-align:center;">
        <div class="container" style="max-width:640px;">
            <i data-lucide="headset" style="width:3rem;height:3rem;color:#14B8A6;margin-bottom:1.5rem;"></i>
            <h2 style="color:#fff;margin-bottom:1rem;">Still need help?</h2>
            <p style="color:rgba(255,255,255,.75);margin-bottom:2rem;">Our support team is available to assist patients, healthcare facilities, and developers. We typically respond within one business day.</p>
            <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('public.contact') }}" class="btn btn-primary">Contact Support</a>
                <a href="{{ route('public.faq') }}" class="btn" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);">Browse FAQ</a>
            </div>
        </div>
    </section>

@endsection
