@extends('layouts.auth')

@section('title', __('onboarding.patient.title'))

@push('styles')
<style>
.reg-success-card{text-align:center}
.reg-success-icon{width:4.5rem;height:4.5rem;background:var(--auth-teal-light);color:var(--auth-teal);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.5rem}
.reg-success-icon__i{width:2.25rem;height:2.25rem}
.reg-success-title{font-size:1.65rem}
.reg-success-sub{margin-top:0.5rem;margin-bottom:2rem}
.digital-health-id-card{background:linear-gradient(135deg,var(--auth-primary,#0F4C81) 0%,var(--auth-primary-dark,#0a3560) 100%);color:#fff;border-radius:1rem;padding:1.75rem;text-align:left;box-shadow:0 10px 20px rgba(15,76,129,.2);position:relative;overflow:hidden;margin:0 auto 2rem;max-width:380px}
.hid-bg-circle{position:absolute;top:-10%;right:-10%;width:150px;height:150px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none}
.hid-top-row{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem}
.hid-label{font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;opacity:.8}
.hid-title{font-size:1.1rem;font-weight:900;letter-spacing:-.01em;margin-top:.2rem}
.hid-badge{padding:.25rem .5rem;background:rgba(16,185,129,.2);border:1px solid #10B981;border-radius:.35rem;font-size:.6rem;font-weight:800;text-transform:uppercase;color:#10B981;display:flex;align-items:center;gap:.25rem}
.hid-dot{width:4px;height:4px;background:#10B981;border-radius:50%}
.hid-registry{margin-bottom:1.5rem}
.hid-field-label{font-size:.6rem;font-weight:700;text-transform:uppercase;opacity:.7;letter-spacing:.05em}
.hid-registry-num{font-size:1.25rem;font-weight:800;font-family:monospace;letter-spacing:.05em;margin-top:.1rem}
.hid-bottom-row{display:flex;justify-content:space-between;align-items:flex-end}
.hid-name{font-size:.95rem;font-weight:700;margin-top:.1rem}
.hid-qr{background:#fff;padding:.25rem;border-radius:.35rem;display:flex;align-items:center;justify-content:center;width:3rem;height:3rem}
.hid-qr i{width:2.5rem;height:2.5rem;color:#0f172a}
.auth-back-link{display:inline-flex;margin-bottom:1.5rem}
</style>
@endpush

@section('content')
    @if(isset($success_profile))
        <!-- Success Screen -->
        <div class="auth-card reg-success-card">
            <div class="reg-success-icon">
                <i data-lucide="shield-check" class="reg-success-icon__i"></i>
            </div>
            <h1 class="auth-headline reg-success-title">{{ __('onboarding.patient.success.title') }}</h1>
            <p class="auth-subheadline reg-success-sub">{{ __('onboarding.patient.success.desc') }}</p>

            <div class="digital-health-id-card">
                <div class="hid-bg-circle"></div>
                <div class="hid-top-row">
                    <div>
                        <div class="hid-label">OpesCare Health ID Network</div>
                        <div class="hid-title">DIGITAL HEALTH IDENTITY</div>
                    </div>
                    <div class="hid-badge">
                        <span class="hid-dot"></span>PROVISIONAL
                    </div>
                </div>
                <div class="hid-registry">
                    <div class="hid-field-label">Registry Number</div>
                    <div class="hid-registry-num">OPC-892-{{ rand(1000,9999) }}-PROV</div>
                </div>
                <div class="hid-bottom-row">
                    <div>
                        <div class="hid-field-label">Patient Identity</div>
                        <div class="hid-name">Alexander Fleming</div>
                    </div>
                    <div class="hid-qr">
                        <i data-lucide="qr-code"></i>
                    </div>
                </div>
            </div>

            <a href="{{ route('public.landing') }}" class="auth-btn auth-btn-primary">
                <i data-lucide="arrow-right"></i>
                <span>{{ __('onboarding.patient.success.cta') }}</span>
            </a>
        </div>
    @else
        <div class="auth-card">

            <a href="{{ route('register') }}" class="back-link auth-back-link">
                <i data-lucide="arrow-left"></i>
                {{ __('onboarding.common.back') }}
            </a>

            <div class="auth-step-head">
                <div class="auth-step-icon">
                    <i data-lucide="heart-pulse"></i>
                </div>
                <h1 class="auth-step-title">{{ __('onboarding.patient.title') }}</h1>
                <p class="auth-step-sub">{{ __('onboarding.patient.subtitle') }}</p>
            </div>

            @if(session('error'))
                <div class="auth-alert auth-alert-danger">
                    <i data-lucide="triangle-alert"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="auth-alert auth-alert-danger">
                    <i data-lucide="triangle-alert"></i>
                    <ul style="margin:0;padding-left:1.25rem;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.patient.submit') }}" method="POST" class="auth-form">
                @csrf
                @if(request('ref'))
                    <input type="hidden" name="ref" value="{{ request('ref') }}">
                @endif

                <!-- Section 1: Personal Information -->
                <div class="form-section">
                    <div class="form-section-title">{{ __('onboarding.patient.sec_basic') }}</div>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="first_name" class="auth-label">{{ __('onboarding.patient.first_name') }} *</label>
                            <input type="text" id="first_name" name="first_name"
                                   class="auth-input{{ $errors->has('first_name') ? ' auth-input-error' : '' }}"
                                   required value="{{ old('first_name') }}">
                            @error('first_name')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="auth-form-group">
                            <label for="last_name" class="auth-label">{{ __('onboarding.patient.last_name') }} *</label>
                            <input type="text" id="last_name" name="last_name"
                                   class="auth-input{{ $errors->has('last_name') ? ' auth-input-error' : '' }}"
                                   required value="{{ old('last_name') }}">
                            @error('last_name')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label for="middle_name" class="auth-label">{{ __('onboarding.patient.middle_name') }}</label>
                        <input type="text" id="middle_name" name="middle_name" class="auth-input" value="{{ old('middle_name') }}">
                    </div>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="dob" class="auth-label">{{ __('onboarding.patient.dob') }} *</label>
                            <input type="date" id="dob" name="dob"
                                   class="auth-input{{ $errors->has('dob') ? ' auth-input-error' : '' }}"
                                   required value="{{ old('dob') }}">
                            @error('dob')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="auth-form-group">
                            <label for="sex" class="auth-label">{{ __('onboarding.patient.sex') }} *</label>
                            <select id="sex" name="sex"
                                    class="auth-input{{ $errors->has('sex') ? ' auth-input-error' : '' }}" required>
                                <option value="" disabled selected>{{ __('onboarding.common.select_option') }}</option>
                                <option value="M" {{ old('sex') == 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ old('sex') == 'F' ? 'selected' : '' }}>Female</option>
                                <option value="O" {{ old('sex') == 'O' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('sex')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="phone" class="auth-label">{{ __('onboarding.common.phone') }} *</label>
                            <input type="tel" id="phone" name="phone"
                                   class="auth-input{{ $errors->has('phone') ? ' auth-input-error' : '' }}"
                                   placeholder="+123..." required value="{{ old('phone') }}">
                            @error('phone')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="auth-form-group">
                            <label for="email" class="auth-label">{{ __('onboarding.common.email') }}</label>
                            <input type="email" id="email" name="email"
                                   class="auth-input{{ $errors->has('email') ? ' auth-input-error' : '' }}"
                                   placeholder="optional@email.com" value="{{ old('email') }}">
                            @error('email')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="preferred_language" class="auth-label">{{ __('onboarding.patient.preferred_lang') }} *</label>
                            <select id="preferred_language" name="preferred_language" class="auth-input" required>
                                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                                <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>Français</option>
                            </select>
                        </div>
                        <div class="auth-form-group">
                            <label for="country" class="auth-label">{{ __('onboarding.patient.country') }} *</label>
                            <input type="text" id="country" name="country" class="auth-input" required value="{{ old('country', 'Canada') }}">
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label for="city" class="auth-label">{{ __('onboarding.patient.city') }} *</label>
                        <input type="text" id="city" name="city" class="auth-input" required value="{{ old('city') }}">
                    </div>
                </div>

                <!-- Section 2: Identity Check -->
                <div class="form-section">
                    <div class="form-section-title">{{ __('onboarding.patient.sec_identity') }}</div>

                    <div class="auth-form-group">
                        <label class="auth-label">{{ __('onboarding.patient.has_id_label') }}</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="has_health_id" value="yes" onclick="toggleHealthIdField(true)"> Yes
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="has_health_id" value="no" checked onclick="toggleHealthIdField(false)"> No
                            </label>
                        </div>
                    </div>

                    <div class="auth-form-group" id="health-id-wrapper" style="display:none;">
                        <label for="health_id" class="auth-label">{{ __('onboarding.patient.health_id') }}</label>
                        <input type="text" id="health_id" name="health_id" class="auth-input" placeholder="OPC-XXX-XXXX-XXX">
                    </div>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="national_id" class="auth-label">{{ __('onboarding.patient.national_id') }}</label>
                            <input type="text" id="national_id" name="national_id" class="auth-input" placeholder="ID Card or SSN">
                        </div>
                        <div class="auth-form-group">
                            <label for="insurance_number" class="auth-label">{{ __('onboarding.patient.insurance_num') }}</label>
                            <input type="text" id="insurance_number" name="insurance_number" class="auth-input" placeholder="Policy Card">
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label for="prev_hosp_number" class="auth-label">{{ __('onboarding.patient.prev_hosp_num') }}</label>
                        <input type="text" id="prev_hosp_number" name="prev_hosp_number" class="auth-input" placeholder="Medical Record Card Number">
                    </div>
                </div>

                <!-- Section 3: Emergency Contact -->
                <div class="form-section">
                    <div class="form-section-title">{{ __('onboarding.patient.sec_emergency') }}</div>

                    <div class="auth-form-group">
                        <label for="emergency_name" class="auth-label">{{ __('onboarding.patient.emerg_name') }} *</label>
                        <input type="text" id="emergency_name" name="emergency_name"
                               class="auth-input{{ $errors->has('emergency_name') ? ' auth-input-error' : '' }}" required>
                        @error('emergency_name')<div class="auth-field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="emergency_relationship" class="auth-label">{{ __('onboarding.patient.emerg_rel') }} *</label>
                            <input type="text" id="emergency_relationship" name="emergency_relationship"
                                   class="auth-input{{ $errors->has('emergency_relationship') ? ' auth-input-error' : '' }}"
                                   required placeholder="Spouse, Parent, Sibling...">
                            @error('emergency_relationship')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="auth-form-group">
                            <label for="emergency_phone" class="auth-label">{{ __('onboarding.patient.emerg_phone') }} *</label>
                            <input type="tel" id="emergency_phone" name="emergency_phone"
                                   class="auth-input{{ $errors->has('emergency_phone') ? ' auth-input-error' : '' }}" required>
                            @error('emergency_phone')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <!-- Section 4: Account Credentials -->
                <div class="form-section">
                    <div class="form-section-title">{{ __('onboarding.patient.sec_security') }}</div>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="password" class="auth-label">{{ __('onboarding.common.password') }} *</label>
                            <input type="password" id="password" name="password"
                                   class="auth-input{{ $errors->has('password') ? ' auth-input-error' : '' }}"
                                   required minlength="8" placeholder="Min. 8 characters">
                            @error('password')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="auth-form-group">
                            <label for="confirm_password" class="auth-label">{{ __('onboarding.common.confirm_password') }} *</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="auth-input{{ $errors->has('confirm_password') ? ' auth-input-error' : '' }}"
                                   required minlength="8" placeholder="••••••••">
                            @error('confirm_password')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="auth-checkbox-group">
                        <input type="checkbox" id="accept_terms" name="accept_terms" required>
                        <label for="accept_terms" class="auth-checkbox-label">{{ __('onboarding.common.accept_terms') }} *</label>
                    </div>
                    @error('accept_terms')<div class="auth-field-error">{{ $message }}</div>@enderror

                    <div class="auth-checkbox-group">
                        <input type="checkbox" id="accept_privacy" name="accept_privacy" required>
                        <label for="accept_privacy" class="auth-checkbox-label">{{ __('onboarding.common.accept_privacy') }} *</label>
                    </div>
                    @error('accept_privacy')<div class="auth-field-error">{{ $message }}</div>@enderror
                </div>

                <!-- Section 5: Consent Notice -->
                <div class="form-section">
                    <div class="form-section-title">{{ __('onboarding.patient.sec_consent') }}</div>
                    <div class="notice-info">
                        {{ __('onboarding.patient.consent_notice') }}
                    </div>
                </div>

                <button type="submit" class="auth-btn auth-btn-primary">
                    <i data-lucide="shield-check"></i>
                    <span>{{ __('onboarding.patient.cta_btn') }}</span>
                </button>
            </form>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        function toggleHealthIdField(show) {
            const wrapper = document.getElementById('health-id-wrapper');
            const field = document.getElementById('health_id');
            if (show) {
                wrapper.style.display = 'block';
                field.setAttribute('required', 'required');
            } else {
                wrapper.style.display = 'none';
                field.removeAttribute('required');
                field.value = '';
            }
        }
    </script>
@endsection
