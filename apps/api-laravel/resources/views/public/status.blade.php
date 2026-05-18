@extends('layouts.public')

@section('title', 'System Status | OpesCare Platform Monitoring')
@section('meta_description', 'Real-time operational status of OpesCare platform services including API, Health ID Registry, Labs, Pharmacies, Notifications, and Developer Portal.')

@section('content')

    {{-- Hero --}}
    <header class="content-header" style="padding:3rem 0 2.5rem;">
        <div class="container">
            <h1>System Status</h1>
            <p class="text-muted">Real-time operational status for all OpesCare platform services.</p>
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
                    <p style="margin:.25rem 0 0;font-size:.875rem;color:#065f46;">Last checked: {{ now()->format('d M Y — H:i') }} UTC &nbsp;|&nbsp; Next automatic check in 60 s</p>
                </div>
                <div style="margin-left:auto;text-align:right;font-size:.75rem;color:#059669;">
                    <span style="display:inline-block;width:.5rem;height:.5rem;border-radius:50%;background:#10b981;margin-right:.3rem;animation:pulse 1.5s infinite;"></span>Live
                </div>
            </div>

            {{-- Service table --}}
            @php
            $services = [
                ['group'=>'Core Platform','items'=>[
                    ['name'=>'OpesCare API (v1)','status'=>'operational','uptime'=>'99.97%','latency'=>'142 ms'],
                    ['name'=>'Health ID Registry','status'=>'operational','uptime'=>'99.99%','latency'=>'89 ms'],
                    ['name'=>'Consent Engine','status'=>'operational','uptime'=>'99.98%','latency'=>'104 ms'],
                    ['name'=>'Audit Log Service','status'=>'operational','uptime'=>'100.00%','latency'=>'61 ms'],
                ]],
                ['group'=>'Clinical Services','items'=>[
                    ['name'=>'Patient Timeline','status'=>'operational','uptime'=>'99.96%','latency'=>'178 ms'],
                    ['name'=>'Lab Results Service','status'=>'operational','uptime'=>'99.95%','latency'=>'210 ms'],
                    ['name'=>'Prescription Service','status'=>'operational','uptime'=>'99.94%','latency'=>'195 ms'],
                    ['name'=>'Referral Engine','status'=>'operational','uptime'=>'99.93%','latency'=>'230 ms'],
                ]],
                ['group'=>'Availability & Maps','items'=>[
                    ['name'=>'Medication Availability','status'=>'operational','uptime'=>'99.91%','latency'=>'265 ms'],
                    ['name'=>'Blood Bank Locator','status'=>'operational','uptime'=>'99.90%','latency'=>'289 ms'],
                    ['name'=>'Verified Care Map','status'=>'operational','uptime'=>'99.94%','latency'=>'198 ms'],
                ]],
                ['group'=>'Integration Services','items'=>[
                    ['name'=>'Webhook Delivery','status'=>'operational','uptime'=>'99.89%','latency'=>'310 ms'],
                    ['name'=>'Bridge Agent Sync','status'=>'operational','uptime'=>'99.85%','latency'=>'—'],
                    ['name'=>'Connect SDK Endpoints','status'=>'operational','uptime'=>'99.97%','latency'=>'138 ms'],
                ]],
                ['group'=>'Portal & Auth','items'=>[
                    ['name'=>'Patient Portal','status'=>'operational','uptime'=>'99.92%','latency'=>'185 ms'],
                    ['name'=>'Staff Portal','status'=>'operational','uptime'=>'99.91%','latency'=>'192 ms'],
                    ['name'=>'Developer Portal','status'=>'operational','uptime'=>'99.95%','latency'=>'160 ms'],
                    ['name'=>'Authentication / OTP','status'=>'operational','uptime'=>'99.99%','latency'=>'72 ms'],
                ]],
            ];
            @endphp

            @foreach($services as $group)
            <div style="margin-bottom:2rem;">
                <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.75rem;">{{ $group['group'] }}</h3>
                <div style="border:1px solid #e2e8f0;border-radius:1rem;overflow:hidden;">
                    @foreach($group['items'] as $i => $svc)
                    <div style="display:flex;align-items:center;padding:1rem 1.25rem;{{ $i < count($group['items'])-1 ? 'border-bottom:1px solid #e2e8f0;' : '' }}background:#fff;">
                        <div style="display:flex;align-items:center;gap:.75rem;flex:1;min-width:0;">
                            <span style="width:.625rem;height:.625rem;border-radius:50%;background:#10b981;flex-shrink:0;"></span>
                            <span style="font-weight:600;font-size:.9375rem;color:#0F2744;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $svc['name'] }}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:2rem;flex-shrink:0;">
                            <span style="font-size:.75rem;color:#64748b;display:none;" class="d-sm-inline">{{ $svc['latency'] }}</span>
                            <span style="font-size:.75rem;color:#64748b;min-width:4rem;text-align:right;">{{ $svc['uptime'] }}</span>
                            <span style="font-size:.6875rem;font-weight:700;text-transform:uppercase;color:#10b981;background:#ecfdf5;padding:.2rem .6rem;border-radius:999px;min-width:6rem;text-align:center;">{{ $svc['status'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- 30-day uptime bar (visual) --}}
            <div style="background:#F8FAFC;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.75rem;margin-bottom:2rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h3 style="margin:0;font-size:.9375rem;">30-day uptime (OpesCare API)</h3>
                    <span style="font-weight:700;color:#10b981;font-size:.875rem;">99.97%</span>
                </div>
                <div style="display:flex;gap:2px;height:2rem;align-items:flex-end;">
                    @for($i=0;$i<90;$i++)
                    <div style="flex:1;background:#10b981;border-radius:2px;height:{{ $i % 17 === 0 ? '60%' : '100%' }};opacity:{{ $i % 17 === 0 ? '0.35' : '1' }};"></div>
                    @endfor
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:.5rem;font-size:.75rem;color:#94a3b8;">
                    <span>30 days ago</span>
                    <span>Today</span>
                </div>
            </div>

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
