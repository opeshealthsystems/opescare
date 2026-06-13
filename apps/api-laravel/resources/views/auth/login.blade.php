@extends('layouts.auth')

@section('title', __('onboarding.login.head_title'))

@section('content')
    <div class="auth-card">
        <div class="auth-title-group">
            <h1 class="auth-headline">{{ __('onboarding.login.welcome_back') }}</h1>
            <p class="auth-subheadline">{{ __('onboarding.login.subheadline') }}</p>
        </div>

        @if(session('success'))
            <div class="auth-alert auth-alert-success">
                <i data-lucide="badge-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="auth-alert auth-alert-danger">
                <i data-lucide="triangle-alert"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!--LoginForm Reusable Component -->
        <form action="{{ route('login.submit') }}" method="POST" class="auth-form">
            @csrf

            <!-- TextInput -->
            <div class="auth-form-group">
                <label for="email" class="auth-label">{{ __('onboarding.login.email_or_phone') }}</label>
                <input type="text" id="email" name="email" class="auth-input{{ $errors->has('email') ? ' auth-input-error' : '' }}" placeholder="name@facility.org or +123..." required autofocus value="{{ old('email') }}">
                @error('email')<div class="auth-field-error">{{ $message }}</div>@enderror
            </div>

            <!-- PasswordInput -->
            <div class="auth-form-group">
                <div class="auth-label">
                    <span>{{ __('onboarding.common.password') }}</span>
                    <a href="{{ route('password.request') }}" class="auth-label-link" style="color: var(--auth-primary); text-decoration: none; font-weight: 700;">
                        {{ __('onboarding.login.forgot') }}
                    </a>
                </div>
                <div class="auth-pass-wrapper">
                    <input type="password" id="password" name="password" class="auth-input{{ $errors->has('password') ? ' auth-input-error' : '' }}" required placeholder="••••••••">
                    <button type="button" class="auth-pass-toggle" data-toggle-password="password">
                        <i data-lucide="eye" id="password-toggle-icon"></i>
                    </button>
                </div>
                @error('password')<div class="auth-field-error">{{ $message }}</div>@enderror
            </div>

            <!-- Checkbox -->
            <div class="auth-checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" class="auth-checkbox-label">{{ __('onboarding.login.remember') }}</label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-btn auth-btn-primary" style="margin-top: 1rem;">
                <i data-lucide="log-in"></i>
                <span>{{ __('onboarding.login.submit_signin') }}</span>
            </button>
        </form>

        <div class="auth-security-block">
            <i data-lucide="shield-check"></i>
            <p>{{ __('onboarding.login.security_note') }}</p>
        </div>
    </div>

    <!-- Onboarding Path Switcher Fallback -->
    <div class="auth-footer-links" style="margin-top: 2rem;">
        <p>{{ __('onboarding.login.no_account') }}
            <a href="{{ route('register') }}" style="font-weight: 800;">
                {{ __('onboarding.login.create_account') }}
            </a>
        </p>
    </div>

    {{-- Demo One-Click Login Panel — visible only when OPESCARE_DEMO_MODE=true --}}
    @if(config('demo.enabled'))
    {{-- Hidden form — JS fills role + email before submit --}}
    <form id="demoLoginForm" method="POST" action="{{ route('demo.login-as') }}" style="display:none;">
        @csrf
        <input type="hidden" id="demoRoleInput"  name="role"  value="">
        <input type="hidden" id="demoEmailInput" name="email" value="">
        <input type="hidden"                      name="mode"  value="public">
    </form>

    <div class="demo-panel" id="demoPanel">
        <div class="demo-panel-header open" id="demoPanelHeader" data-demo-panel-toggle>
            <div class="demo-panel-header-left">
                <span class="demo-badge">DEMO</span>
                <div>
                    <strong>Try the Demo</strong>
                    <span>One-click access — no password needed</span>
                </div>
            </div>
            <i data-lucide="chevron-up" id="demoPanelChevron"></i>
        </div>

        <div class="demo-panel-body open" id="demoPanelBody">
            <div class="demo-notice">
                <i data-lucide="info"></i>
                <span>Sandbox environment — demo data only. Resets periodically. Do not enter real patient data.</span>
            </div>

            {{-- Portal Tab Strip --}}
            <div class="demo-tabs" role="tablist">
                <button class="demo-tab-btn active" type="button" data-demo-tab="clinical">
                    <i data-lucide="stethoscope"></i> Clinical
                </button>
                <button class="demo-tab-btn" type="button" data-demo-tab="facility">
                    <i data-lucide="building-2"></i> Facility
                </button>
                <button class="demo-tab-btn" type="button" data-demo-tab="insurance">
                    <i data-lucide="shield-check"></i> Insurance
                </button>
                <button class="demo-tab-btn" type="button" data-demo-tab="patient">
                    <i data-lucide="user"></i> Patient
                </button>
                <button class="demo-tab-btn" type="button" data-demo-tab="admin">
                    <i data-lucide="settings-2"></i> Admin
                </button>
                <button class="demo-tab-btn" type="button" data-demo-tab="developer">
                    <i data-lucide="code-2"></i> Developer
                </button>
            </div>

            {{-- Clinical Portal --}}
            <div class="demo-tab-pane active" id="demo-tab-clinical" role="tabpanel">
                <div class="demo-btn-grid">
                    <button type="button" class="demo-login-btn demo-btn-clinical" data-demo-role="doctor" data-demo-email="demo.doctor@opescare.test">
                        <i data-lucide="stethoscope"></i>
                        <span>Dr. Amara Diallo</span>
                        <small>General Practitioner</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-clinical" data-demo-role="multi_doctor" data-demo-email="demo.multi.doctor@opescare.test">
                        <i data-lucide="network"></i>
                        <span>Dr. Kofi Mensah</span>
                        <small>Multi-Facility Doctor</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-clinical" data-demo-role="nurse" data-demo-email="demo.nurse@opescare.test">
                        <i data-lucide="heart-pulse"></i>
                        <span>Nurse Fatou Traoré</span>
                        <small>Clinical Nurse</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-clinical" data-demo-role="specialist" data-demo-email="demo.specialist@opescare.test">
                        <i data-lucide="microscope"></i>
                        <span>Dr. Ibrahim Sow</span>
                        <small>Specialist</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-clinical" data-demo-role="pharmacist" data-demo-email="demo.pharmacist@opescare.test">
                        <i data-lucide="pill"></i>
                        <span>Aïcha Coulibaly</span>
                        <small>Pharmacist</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-clinical" data-demo-role="labtech" data-demo-email="demo.labtech@opescare.test">
                        <i data-lucide="flask-conical"></i>
                        <span>Boubacar Keïta</span>
                        <small>Lab Technician</small>
                    </button>
                </div>
            </div>

            {{-- Facility Portal --}}
            <div class="demo-tab-pane" id="demo-tab-facility" role="tabpanel">
                <div class="demo-btn-grid">
                    <button type="button" class="demo-login-btn demo-btn-facility" data-demo-role="facility_admin" data-demo-email="demo.facility.admin@opescare.test">
                        <i data-lucide="building-2"></i>
                        <span>Admin Mariam Touré</span>
                        <small>Facility Administrator</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-facility" data-demo-role="facility_ceo" data-demo-email="demo.facility.ceo@opescare.test">
                        <i data-lucide="briefcase"></i>
                        <span>CEO Seydou Ouédraogo</span>
                        <small>Chief Executive Officer</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-facility" data-demo-role="finance" data-demo-email="demo.finance@opescare.test">
                        <i data-lucide="bar-chart-2"></i>
                        <span>Finance Officer Kadiatou</span>
                        <small>Finance & Billing</small>
                    </button>
                </div>
            </div>

            {{-- Insurance Portal --}}
            <div class="demo-tab-pane" id="demo-tab-insurance" role="tabpanel">
                <div class="demo-btn-grid">
                    <button type="button" class="demo-login-btn demo-btn-insurance" data-demo-role="insurance_claims" data-demo-email="demo.insurance@opescare.test">
                        <i data-lucide="file-check-2"></i>
                        <span>Oumar Ba</span>
                        <small>Claims Officer</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-insurance" data-demo-role="insurance_preauth" data-demo-email="demo.preauth@opescare.test">
                        <i data-lucide="clipboard-check"></i>
                        <span>Awa (Preauth)</span>
                        <small>Pre-Auth Reviewer</small>
                    </button>
                </div>
            </div>

            {{-- Patient Portal --}}
            <div class="demo-tab-pane" id="demo-tab-patient" role="tabpanel">
                <div class="demo-btn-grid">
                    <button type="button" class="demo-login-btn demo-btn-patient" data-demo-role="patient" data-demo-email="demo.patient@opescare.test">
                        <i data-lucide="user"></i>
                        <span>Jean Dupont</span>
                        <small>Patient</small>
                    </button>
                    <button type="button" class="demo-login-btn demo-btn-patient" data-demo-role="guardian" data-demo-email="demo.guardian@opescare.test">
                        <i data-lucide="users"></i>
                        <span>Marie Dupont</span>
                        <small>Guardian / Family</small>
                    </button>
                </div>
            </div>

            {{-- Platform Admin --}}
            <div class="demo-tab-pane" id="demo-tab-admin" role="tabpanel">
                <div class="demo-btn-grid">
                    <button type="button" class="demo-login-btn demo-btn-admin" data-demo-role="platform_admin" data-demo-email="demo.admin@opescare.test">
                        <i data-lucide="settings-2"></i>
                        <span>Platform Admin</span>
                        <small>Super Administrator</small>
                    </button>
                </div>
            </div>

            {{-- Developer / API --}}
            <div class="demo-tab-pane" id="demo-tab-developer" role="tabpanel">
                <div class="demo-btn-grid">
                    <button type="button" class="demo-login-btn" style="background:#0284c7;" data-demo-role="developer" data-demo-email="demo.developer@opescare.test">
                        <i data-lucide="code-2"></i>
                        <span>API Developer</span>
                        <small>Developer Portal</small>
                    </button>
                </div>
                <div style="margin-top:0.875rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:0.75rem 1rem;font-size:0.8rem;color:#0c4a6e;line-height:1.6;">
                    <strong>Sandbox API Credentials</strong><br>
                    <code>client_id: demo_dev_sandbox</code><br>
                    <code>client_secret: demo_secret_sandbox_2026</code><br>
                    <code>POST /api/v1/connect/auth/token</code>
                </div>
            </div>
        </div>{{-- /demo-panel-body --}}
    </div>{{-- /demo-panel --}}
    @endif
@endsection

{{-- Behaviour (password toggle, demo panel/tabs/login, icon rendering) lives in
     public/js/auth.js, loaded by layouts.auth. Inline scripts are blocked by the
     strict CSP (script-src 'self'), so handlers use data-* attributes instead. --}}
