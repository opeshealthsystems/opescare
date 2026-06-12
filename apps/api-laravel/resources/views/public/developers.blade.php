@extends('layouts.public')

@section('title', 'OpesCare Developers | Connect API, SDK, Widget & Integration Tools')
@section('meta_description', 'Developer resources for the OpesCare platform: REST API, PHP/JS/Python SDK, embeddable widget, Bridge Agent, OpesCare Lite, and webhooks.')

@section('head_scripts')
<style>
.webhooks-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start; }
@media (max-width: 768px) { .webhooks-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

    {{-- Hero --}}
    <header class="content-header">
        <div class="container">
            <div class="badge" style="background:rgba(20,184,166,.15);color:#0F766E;margin-bottom:1rem;">Developer Portal</div>
            <h1>Tools built for healthcare interoperability.</h1>
            <p class="text-muted" style="max-width:760px;margin:0 auto;font-size:1.2rem;">
                Connect your hospital, clinic, lab, or pharmacy to the OpesCare network using the integration method that fits your existing infrastructure.
            </p>
            <div style="margin-top:2.5rem;display:flex;justify-content:center;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('register.developer') }}" class="btn btn-primary">Request API Access</a>
                <a href="{{ route('public.status') }}" class="btn btn-secondary">API Status</a>
            </div>
        </div>
    </header>

    {{-- Quicknav tabs --}}
    <nav style="background:#fff;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:100;" aria-label="Section navigation">
        <div class="container" style="display:flex;gap:0;overflow-x:auto;scrollbar-width:none;">
            @foreach([['api','API'],['sdk','SDK'],['widget','Widget'],['bridge','Bridge Agent'],['lite','OpesCare Lite'],['webhooks','Webhooks']] as [$id,$label])
            <a href="#{{ $id }}"
               style="padding:.875rem 1.25rem;font-size:.875rem;font-weight:600;color:#64748b;text-decoration:none;white-space:nowrap;border-bottom:2px solid transparent;transition:all .15s;"
               onmouseover="this.style.color='#0F4C81';this.style.borderBottomColor='#0F4C81'"
               onmouseout="this.style.color='#64748b';this.style.borderBottomColor='transparent'">{{ $label }}</a>
            @endforeach
        </div>
    </nav>

    {{-- Connect API --}}
    <section id="api" class="section" style="padding-top:4rem;">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.5rem;padding:.4rem .875rem;font-size:.8125rem;font-weight:700;margin-bottom:1rem;">
                        <i data-lucide="braces" style="width:.9rem;height:.9rem;"></i> OpesCare Connect API
                    </div>
                    <h2>RESTful API — direct system-to-system integration</h2>
                    <p class="text-muted" style="margin-bottom:1.5rem;">
                        The Connect API gives authorized health systems full programmatic access to the OpesCare platform — patient identity, consent, clinical timelines, medication availability, and more. All endpoints are JSON-based and authenticated via OAuth 2.0 client credentials.
                    </p>
                    <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem;">
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> OAuth 2.0 client credentials flow</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Versioned endpoints — current stable: v1</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Sandbox environment for safe pre-production testing</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Full audit trail — every API call is logged</li>
                    </ul>
                </div>
                <div class="hero-visual">
                    <div style="background:#1e293b;border-radius:1rem;padding:1.5rem;color:#f8fafc;font-family:'Courier New',Courier,monospace;font-size:.8125rem;box-shadow:0 20px 40px rgba(0,0,0,.25);">
                        <div style="display:flex;gap:.5rem;margin-bottom:1rem;">
                            <span style="width:.75rem;height:.75rem;border-radius:50%;background:#f87171;display:inline-block;"></span>
                            <span style="width:.75rem;height:.75rem;border-radius:50%;background:#fbbf24;display:inline-block;"></span>
                            <span style="width:.75rem;height:.75rem;border-radius:50%;background:#4ade80;display:inline-block;"></span>
                        </div>
                        <div style="color:#94a3b8;">// Pull approved patient timeline</div>
                        <div style="margin:.5rem 0;"><span style="color:#f472b6;">GET</span> <span style="color:#93c5fd;">/api/v1/patients/{id}/timeline</span></div>
                        <div style="color:#94a3b8;margin-top:1rem;">Authorization: Bearer &lt;token&gt;</div>
                        <div style="color:#94a3b8;margin-bottom:1rem;">X-Purpose: clinical-review</div>
                        <div style="color:#94a3b8;">// 200 Response</div>
                        <div style="color:#fbbf24;">{</div>
                        <div style="padding-left:1.25rem;color:#f8fafc;">
                            "status": <span style="color:#4ade80;">"success"</span>,<br>
                            "patient_id": <span style="color:#4ade80;">"OPES-123-456"</span>,<br>
                            "consent_ref": <span style="color:#4ade80;">"CNS-789"</span>,<br>
                            "data": [...]
                        </div>
                        <div style="color:#fbbf24;">}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SDK --}}
    <section id="sdk" class="section" style="background:#F8FAFC;">
        <div class="container">
            <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.5rem;padding:.4rem .875rem;font-size:.8125rem;font-weight:700;margin-bottom:1rem;">
                <i data-lucide="code-2" style="width:.9rem;height:.9rem;"></i> OpesCare Connect SDK
            </div>
            <h2>Pre-built libraries in PHP, JavaScript, Python and more</h2>
            <p class="text-muted" style="max-width:680px;margin-bottom:3rem;">The SDK wraps the Connect API into idiomatic clients so your development team can integrate in days rather than weeks.</p>

            <div class="card-grid">
                @foreach([
                    ['icon'=>'code','lang'=>'PHP','badge'=>'Stable','color'=>'#4F46E5','desc'=>'Laravel/Symfony compatible. Install via Composer. Full typed request/response objects.','snippet'=>'composer require opes-health-systems/opescare-php'],
                    ['icon'=>'braces','lang'=>'JavaScript','badge'=>'Stable','color'=>'#F59E0B','desc'=>'Node.js and browser-compatible. Works with Axios and Fetch. TypeScript definitions included.','snippet'=>'npm install @opes-health-systems/opescare-js'],
                    ['icon'=>'terminal','lang'=>'Python','badge'=>'Beta','color'=>'#10B981','desc'=>'Async and sync clients. Pydantic models for type safety. FastAPI and Django ready.','snippet'=>'pip install opescare-python'],
                    ['icon'=>'layers','lang'=>'.NET / Java','badge'=>'Roadmap','color'=>'#6B7280','desc'=>'.NET 6+ and Java 17+ clients are currently in development. Register to be notified at launch.','snippet'=>'Coming soon — register interest'],
                ] as $sdk)
                <div class="card">
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                        <div style="width:2.25rem;height:2.25rem;background:{{ $sdk['color'] }}1A;color:{{ $sdk['color'] }};border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="{{ $sdk['icon'] }}" style="width:1rem;height:1rem;"></i>
                        </div>
                        <h3 style="margin:0;font-size:1.0625rem;">{{ $sdk['lang'] }}</h3>
                        <span style="margin-left:auto;font-size:.6875rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;background:{{ $sdk['badge']==='Stable'?'rgba(16,185,129,.1)':($sdk['badge']==='Beta'?'rgba(245,158,11,.1)':'rgba(107,114,128,.1)') }};color:{{ $sdk['badge']==='Stable'?'#065f46':($sdk['badge']==='Beta'?'#92400e':'#374151') }}">{{ $sdk['badge'] }}</span>
                    </div>
                    <p class="text-muted" style="font-size:.875rem;margin-bottom:1rem;">{{ $sdk['desc'] }}</p>
                    <code style="display:block;background:#1e293b;color:#93c5fd;padding:.75rem 1rem;border-radius:.5rem;font-size:.75rem;">{{ $sdk['snippet'] }}</code>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Widget --}}
    <section id="widget" class="section">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.5rem;padding:.4rem .875rem;font-size:.8125rem;font-weight:700;margin-bottom:1rem;">
                        <i data-lucide="panel-top" style="width:.9rem;height:.9rem;"></i> OpesCare Connect Widget
                    </div>
                    <h2>Embed patient search and consent in your web application</h2>
                    <p class="text-muted" style="margin-bottom:1.5rem;">
                        The Connect Widget is a secure, sandboxed web component you can embed inside your existing hospital information system or web portal. It handles patient lookup, consent request, and record pull without requiring a custom API integration.
                    </p>
                    <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem;">
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Single script tag embed — no build pipeline required</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Scoped iFrame — zero access to your parent app state</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Customizable theme and label strings</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> postMessage API for result callbacks into your app</li>
                    </ul>
                </div>
                <div class="hero-visual">
                    <div style="border:1px solid #e2e8f0;border-radius:1.25rem;overflow:hidden;box-shadow:0 20px 40px rgba(15,76,129,.08);">
                        <div style="background:#0F4C81;padding:1rem 1.5rem;display:flex;align-items:center;gap:.75rem;">
                            <img src="{{ asset('favicon.svg') }}" width="20" height="20" alt="">
                            <span style="font-weight:700;font-size:.875rem;color:#fff;">OpesCare Connect Widget</span>
                        </div>
                        <div style="padding:1.5rem;background:#F8FAFC;">
                            <label style="font-size:.8125rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Search Patient Health ID</label>
                            <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;">
                                <input type="text" value="OPES-123-456" readonly style="flex:1;height:2.5rem;padding:0 .875rem;border:1px solid #cbd5e1;border-radius:.5rem;font-size:.875rem;background:#fff;">
                                <button style="height:2.5rem;padding:0 1rem;background:#0F4C81;color:#fff;border:none;border-radius:.5rem;font-size:.875rem;font-weight:600;cursor:pointer;">Search</button>
                            </div>
                            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
                                <div style="display:flex;align-items:center;gap:.75rem;">
                                    <div style="width:2.5rem;height:2.5rem;background:#0F4C81;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;">JD</div>
                                    <div>
                                        <div style="font-weight:700;font-size:.875rem;">Jane Doe — F, 34</div>
                                        <div style="font-size:.75rem;color:#14B8A6;font-weight:600;">● Verified</div>
                                    </div>
                                </div>
                                <button style="margin-top:1rem;width:100%;height:2.25rem;background:#14B8A6;color:#fff;border:none;border-radius:.5rem;font-size:.8125rem;font-weight:600;cursor:pointer;">Request Consent</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Bridge Agent --}}
    <section id="bridge" class="section" style="background:#F8FAFC;">
        <div class="container">
            <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.5rem;padding:.4rem .875rem;font-size:.8125rem;font-weight:700;margin-bottom:1rem;">
                <i data-lucide="cpu" style="width:.9rem;height:.9rem;"></i> OpesCare Bridge Agent
            </div>
            <h2>Connecting legacy systems, local databases, and offline environments</h2>
            <p class="text-muted" style="max-width:680px;margin-bottom:3rem;">
                Not every facility has a modern API-ready system. The Bridge Agent is a lightweight local service that can connect to on-premise databases, file-export systems, or semi-offline environments, and sync records securely with OpesCare on a scheduled or event-driven basis.
            </p>
            <div class="card-grid">
                <div class="card">
                    <div class="card-icon"><i data-lucide="database"></i></div>
                    <h3>Local Database Connector</h3>
                    <p>Reads from MySQL, PostgreSQL, MSSQL, or SQLite databases on your local network and maps records to the OpesCare data model.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="file-spreadsheet"></i></div>
                    <h3>File Export Parser</h3>
                    <p>Ingests CSV, HL7, or structured PDF exports from legacy EMR systems and translates them into FHIR-compatible records.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="wifi-off"></i></div>
                    <h3>Offline Sync Queue</h3>
                    <p>Records are queued locally when the facility is offline, then synced automatically when the connection is restored.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i data-lucide="lock"></i></div>
                    <h3>Encrypted Local Storage</h3>
                    <p>All queued data is encrypted at rest on the local machine using AES-256. Credentials are stored in secure OS keychains.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- OpesCare Lite --}}
    <section id="lite" class="section">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.5rem;padding:.4rem .875rem;font-size:.8125rem;font-weight:700;margin-bottom:1rem;">
                        <i data-lucide="layout-dashboard" style="width:.9rem;height:.9rem;"></i> OpesCare Lite
                    </div>
                    <h2>A full browser portal for facilities without their own digital system</h2>
                    <p class="text-muted" style="margin-bottom:1.5rem;">
                        OpesCare Lite is a secure, hosted web portal that lets smaller clinics, pharmacies, and health posts access the OpesCare network without building any integration. Staff log in from any modern browser and work directly with patient records through a guided interface.
                    </p>
                    <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem;">
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Zero installation — fully browser-based</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Role-based access — doctors, nurses, pharmacists, admins</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Works on tablets and low-bandwidth connections</li>
                        <li style="display:flex;gap:.75rem;"><i data-lucide="check-circle" style="width:1.1rem;height:1.1rem;color:#14B8A6;flex-shrink:0;margin-top:.15rem;"></i> Same security and audit standards as API integrations</li>
                    </ul>
                    <div style="margin-top:2rem;">
                        <a href="{{ route('register.organization') }}" class="btn btn-primary">Apply for OpesCare Lite Access</a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div style="background:#0F2744;border-radius:1.25rem;padding:2rem;color:#fff;">
                        <div style="font-size:.75rem;color:#94a3b8;margin-bottom:1rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">OpesCare Lite — Clinic View</div>
                        <div style="display:grid;gap:.75rem;">
                            @foreach(['Patient Search','Consent Requests','Clinical Timeline','Prescriptions','Lab Results'] as $item)
                            <div style="display:flex;align-items:center;gap:.75rem;background:rgba(255,255,255,.07);border-radius:.75rem;padding:.875rem 1rem;">
                                <span style="width:.5rem;height:.5rem;border-radius:50%;background:#14B8A6;flex-shrink:0;"></span>
                                <span style="font-size:.875rem;">{{ $item }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Webhooks --}}
    <section id="webhooks" class="section" style="background:#F8FAFC;">
        <div class="container">
            <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(15,76,129,.08);color:#0F4C81;border-radius:.5rem;padding:.4rem .875rem;font-size:.8125rem;font-weight:700;margin-bottom:1rem;">
                <i data-lucide="radio-tower" style="width:.9rem;height:.9rem;"></i> Webhooks &amp; Sync Dashboard
            </div>
            <h2>Stay in sync with real-time platform events</h2>
            <p class="text-muted" style="max-width:680px;margin-bottom:3rem;">Subscribe your endpoint to OpesCare events and keep your local system updated without polling the API.</p>

            <div class="webhooks-grid">
                <div>
                    <h3 style="font-size:1.0625rem;margin-bottom:1.25rem;">Available event topics</h3>
                    <div style="display:grid;gap:.75rem;">
                        @foreach([
                            ['visit.recorded','A new clinical visit has been pushed to the timeline.'],
                            ['consent.granted','A patient has approved a consent request.'],
                            ['consent.revoked','A patient has revoked an active consent grant.'],
                            ['lab.result.released','A validated lab result has been released.'],
                            ['prescription.dispensed','A pharmacy has marked a prescription as dispensed.'],
                            ['emergency.access','An emergency access event has been recorded.'],
                            ['sync.failed','A record push or pull sync has failed.'],
                        ] as [$event,$desc])
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
                            <code style="font-size:.8125rem;color:#0F4C81;font-weight:700;">{{ $event }}</code>
                            <p style="font-size:.8125rem;color:#64748b;margin:.25rem 0 0;">{{ $desc }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h3 style="font-size:1.0625rem;margin-bottom:1.25rem;">Example webhook payload</h3>
                    <div style="background:#1e293b;border-radius:1rem;padding:1.5rem;font-family:'Courier New',Courier,monospace;font-size:.75rem;color:#f8fafc;">
                        <div style="color:#94a3b8;">POST https://your-endpoint.com/opescare-hook</div>
                        <div style="color:#94a3b8;margin-bottom:1rem;">X-OpesCare-Signature: sha256=...</div>
                        <div style="color:#fbbf24;">{</div>
                        <div style="padding-left:1.25rem;">
                            "event": <span style="color:#4ade80;">"visit.recorded"</span>,<br>
                            "timestamp": <span style="color:#4ade80;">"2026-05-18T08:42:00Z"</span>,<br>
                            "facility_id": <span style="color:#4ade80;">"FAC-001"</span>,<br>
                            "patient_id": <span style="color:#4ade80;">"OPES-123-456"</span>,<br>
                            "consent_ref": <span style="color:#4ade80;">"CNS-789"</span>,<br>
                            "data": <span style="color:#fbbf24;">{...}</span>
                        </div>
                        <div style="color:#fbbf24;">}</div>
                    </div>
                    <div style="margin-top:1.5rem;padding:1.25rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;">
                        <p style="font-size:.875rem;font-weight:600;margin:0 0 .5rem;">Signature verification</p>
                        <p style="font-size:.8125rem;color:#64748b;margin:0;">Every webhook delivery includes an HMAC-SHA256 signature over the raw request body using your endpoint secret. Always verify before processing.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="section" style="background:#0F2744;color:#fff;text-align:center;">
        <div class="container" style="max-width:640px;">
            <h2 style="color:#fff;margin-bottom:1rem;">Ready to connect your system?</h2>
            <p style="color:rgba(255,255,255,.75);margin-bottom:2rem;">Submit a developer or integration request and our interoperability team will review your use case and access scopes.</p>
            <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('register.developer') }}" class="btn btn-primary">Request API Access</a>
                <a href="{{ route('public.interoperability') }}" class="btn" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);">Integration Overview</a>
            </div>
        </div>
    </section>

@endsection
