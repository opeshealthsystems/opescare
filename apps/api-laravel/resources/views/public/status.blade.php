@extends('layouts.public')

@section('title', 'System Status | OpesCare Platform Services')
@section('meta_description', 'Operational status overview of OpesCare platform services including the Connect API, Health ID Registry, clinical services, integrations, and portals.')

@section('content')

    {{-- Hero --}}
    <header class="content-header" style="padding:3rem 0 2.5rem;">
        <div class="container">
            <h1>System Status</h1>
            <p class="text-muted">Operational status overview for OpesCare platform services.</p>
        </div>
    </header>

    <section class="content-body">
        <div class="container" style="max-width:800px;">

            {{-- Overall banner --}}
            <div style="background:#ecfdf5;border:1.5px solid #10b981;border-radius:1.25rem;display:flex;align-items:center;gap:1.25rem;padding:1.5rem;margin-bottom:2.5rem;">
                <div style="width:3rem;height:3rem;background:#10b981;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="check" style="width:1.5rem;height:1.5rem;color:#fff;"></i>
                </div>
                <div>
                    <h3 style="margin:0;color:#065f46;font-size:1.125rem;">All Systems Operational</h3>
                    <p style="margin:.25rem 0 0;font-size:.875rem;color:#065f46;">Status last updated {{ now()->format('d M Y — H:i') }} UTC</p>
                </div>
            </div>

            {{-- Service table --}}
            @php
            $services = [
                ['group'=>'Core Platform','items'=>[
                    'OpesCare Connect API (v1)',
                    'Health ID Registry',
                    'Consent Engine',
                    'Audit Log Service',
                ]],
                ['group'=>'Clinical Services','items'=>[
                    'Patient Timeline',
                    'Lab Results Service',
                    'Prescription Service',
                    'Referral Engine',
                ]],
                ['group'=>'Availability & Maps','items'=>[
                    'Medication Availability',
                    'Blood Bank Locator',
                    'Verified Care Map',
                ]],
                ['group'=>'Integration Services','items'=>[
                    'Webhook Delivery',
                    'Bridge Agent Sync',
                    'Connect SDK Endpoints',
                ]],
                ['group'=>'Portal & Auth','items'=>[
                    'Patient Portal',
                    'Staff Portal',
                    'Developer Portal',
                    'Authentication / OTP',
                ]],
            ];
            @endphp

            @foreach($services as $group)
            <div style="margin-bottom:2rem;">
                <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.75rem;">{{ $group['group'] }}</h3>
                <div style="border:1px solid #e2e8f0;border-radius:1rem;overflow:hidden;">
                    @foreach($group['items'] as $i => $name)
                    <div style="display:flex;align-items:center;padding:1rem 1.25rem;{{ $i < count($group['items'])-1 ? 'border-bottom:1px solid #e2e8f0;' : '' }}background:#fff;">
                        <div style="display:flex;align-items:center;gap:.75rem;flex:1;min-width:0;">
                            <span style="width:.625rem;height:.625rem;border-radius:50%;background:#10b981;flex-shrink:0;"></span>
                            <span style="font-weight:600;font-size:.9375rem;color:#0F2744;overflow:hidden;text-overflow:ellipsis;">{{ $name }}</span>
                        </div>
                        <span style="font-size:.6875rem;font-weight:700;text-transform:uppercase;color:#10b981;background:#ecfdf5;padding:.2rem .6rem;border-radius:999px;min-width:6rem;text-align:center;flex-shrink:0;">Operational</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Incident history --}}
            <div style="margin-bottom:2rem;">
                <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.75rem;">Recent Incidents</h3>
                <div style="border:1px solid #e2e8f0;border-radius:1rem;overflow:hidden;">
                    <div style="padding:1.25rem 1.5rem;background:#fff;">
                        <div style="display:flex;align-items:flex-start;gap:1rem;">
                            <span style="width:.625rem;height:.625rem;border-radius:50%;background:#10b981;flex-shrink:0;margin-top:.35rem;"></span>
                            <div>
                                <div style="font-weight:700;font-size:.9375rem;margin-bottom:.25rem;">No incidents in the last 30 days</div>
                                <div style="font-size:.8125rem;color:#64748b;">All services have been operating within normal parameters.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subscribe notice --}}
            <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:1.25rem;padding:1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                <i data-lucide="bell" style="width:1.5rem;height:1.5rem;color:#2563EB;flex-shrink:0;"></i>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;color:#1E40AF;margin-bottom:.2rem;">Subscribe to status updates</div>
                    <div style="font-size:.8125rem;color:#3B82F6;">Get notified instantly by email when an incident is opened or resolved.</div>
                </div>
                <a href="{{ route('public.contact') }}" class="btn btn-primary" style="font-size:.875rem;padding:.625rem 1.25rem;flex-shrink:0;">Subscribe</a>
            </div>

        </div>
    </section>

    <style>
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.4} }
        @media(min-width:640px){ .d-sm-inline{display:inline!important;} }
    </style>

@endsection
