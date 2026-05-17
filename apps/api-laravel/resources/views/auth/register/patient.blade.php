@extends('layouts.auth')

@section('title', __('onboarding.patient.title'))

@section('content')
    @if(isset($success_profile))
        <!-- Success Screen -->
        <div class="auth-card" style="text-align: center;">
            <div style="width: 4.5rem; height: 4.5rem; background: var(--auth-teal-light); color: var(--auth-teal); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                <i data-lucide="shield-check" style="width: 2.25rem; height: 2.25rem;"></i>
            </div>
            
            <h1 class="auth-headline" style="font-size: 1.65rem;">{{ __('onboarding.patient.success.title') }}</h1>
            <p class="auth-subheadline" style="margin-top: 0.5rem; margin-bottom: 2rem;">
                {{ __('onboarding.patient.success.desc') }}
            </p>

            <!-- Floating high fidelity Digital Health ID card mock -->
            <div class="digital-health-id-card" style="background: linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-primary-dark) 100%); color: white; border-radius: 1rem; padding: 1.75rem; text-align: left; box-shadow: 0 10px 20px rgba(15, 76, 129, 0.2); position: relative; overflow: hidden; margin: 0 auto 2rem; max-width: 380px;">
                <div style="position: absolute; top: -10%; right: -10%; width: 150px; height: 150px; background: rgba(255, 255, 255, 0.05); border-radius: 50%; pointer-events: none;"></div>
                
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
                    <div>
                        <div style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8;">OpesCare Health ID Network</div>
                        <div style="font-size: 1.1rem; font-weight: 900; letter-spacing: -0.01em; margin-top: 0.2rem;">DIGITAL HEALTH IDENTITY</div>
                    </div>
                    <div style="padding: 0.25rem 0.5rem; background: rgba(16, 185, 129, 0.2); border: 1px solid #10B981; border-radius: 0.35rem; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; color: #10B981; display: flex; align-items: center; gap: 0.25rem;">
                        <span style="width: 4px; height: 4px; background: #10B981; border-radius: 50%;"></span>
                        PROVISIONAL
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.6rem; font-weight: 700; text-transform: uppercase; opacity: 0.7; letter-spacing: 0.05em;">Registry Number</div>
                    <div style="font-size: 1.25rem; font-weight: 800; font-family: monospace; letter-spacing: 0.05em; margin-top: 0.1rem;">OPC-892-{{ rand(1000, 9999) }}-PROV</div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <div style="font-size: 0.6rem; font-weight: 700; text-transform: uppercase; opacity: 0.7; letter-spacing: 0.05em;">Patient Identity</div>
                        <div style="font-size: 0.95rem; font-weight: 700; margin-top: 0.1rem;">Alexander Fleming</div>
                    </div>
                    <div style="background: white; padding: 0.25rem; border-radius: 0.35rem; display: flex; align-items: center; justify-content: center; width: 3rem; height: 3rem;">
                        <!-- QR Code mock -->
                        <i data-lucide="qr-code" style="width: 2.5rem; height: 2.5rem; color: var(--auth-text-primary);"></i>
                    </div>
                </div>
            </div>

            <a href="{{ route('public.landing') }}" class="auth-btn auth-btn-primary">
                <i data-lucide="arrow-right"></i>
                <span>{{ __('onboarding.patient.success.cta') }}</span>
            </a>
        </div>
    @else
        <!-- Patient Registration Form Card -->
        <div class="auth-card" style="max-width: 650px; padding: 2.5rem;">
            <div class="auth-title-group">
                <h1 class="auth-headline">{{ __('onboarding.patient.title') }}</h1>
                <p class="auth-subheadline">{{ __('onboarding.patient.subtitle') }}</p>
            </div>

            @if(session('error'))
                <!-- Duplicate Match Warning -->
                <div class="auth-alert auth-alert-danger">
                    <i data-lucide="triangle-alert" style="width: 1.5rem; height: 1.5rem;"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <!--PatientSignupForm Component -->
            <form action="{{ route('register.patient.submit') }}" method="POST" class="auth-form">
                @csrf

                <!-- Section 1: Basic Information -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.patient.sec_basic') }}
                    </h3>
                    
                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="first_name" class="auth-label">{{ __('onboarding.patient.first_name') }} *</label>
                            <input type="text" id="first_name" name="first_name" class="auth-input" required value="{{ old('first_name') }}">
                        </div>
                        <div class="auth-form-group">
                            <label for="last_name" class="auth-label">{{ __('onboarding.patient.last_name') }} *</label>
                            <input type="text" id="last_name" name="last_name" class="auth-input" required value="{{ old('last_name') }}">
                        </div>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="middle_name" class="auth-label">{{ __('onboarding.patient.middle_name') }}</label>
                        <input type="text" id="middle_name" name="middle_name" class="auth-input" value="{{ old('middle_name') }}">
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="dob" class="auth-label">{{ __('onboarding.patient.dob') }} *</label>
                            <input type="date" id="dob" name="dob" class="auth-input" required value="{{ old('dob') }}">
                        </div>
                        <div class="auth-form-group">
                            <label for="sex" class="auth-label">{{ __('onboarding.patient.sex') }} *</label>
                            <select id="sex" name="sex" class="auth-input" style="padding-top: 0.65rem; padding-bottom: 0.65rem;" required>
                                <option value="" disabled selected>{{ __('onboarding.common.select_option') }}</option>
                                <option value="M" {{ old('sex') == 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ old('sex') == 'F' ? 'selected' : '' }}>Female</option>
                                <option value="O" {{ old('sex') == 'O' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="phone" class="auth-label">{{ __('onboarding.common.phone') }} *</label>
                            <input type="tel" id="phone" name="phone" class="auth-input" placeholder="+123..." required value="{{ old('phone') }}">
                        </div>
                        <div class="auth-form-group">
                            <label for="email" class="auth-label">{{ __('onboarding.common.email') }}</label>
                            <input type="email" id="email" name="email" class="auth-input" placeholder="optional@email.com" value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="preferred_language" class="auth-label">{{ __('onboarding.patient.preferred_lang') }} *</label>
                            <select id="preferred_language" name="preferred_language" class="auth-input" style="padding-top: 0.65rem; padding-bottom: 0.65rem;" required>
                                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                                <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>Français</option>
                            </select>
                        </div>
                        <div class="auth-form-group">
                            <label for="country" class="auth-label">{{ __('onboarding.patient.country') }} *</label>
                            <input type="text" id="country" name="country" class="auth-input" required value="{{ old('country', 'Canada') }}">
                        </div>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="city" class="auth-label">{{ __('onboarding.patient.city') }} *</label>
                        <input type="text" id="city" name="city" class="auth-input" required value="{{ old('city') }}">
                    </div>
                </div>

                <!-- Section 2: Identity Check -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.patient.sec_identity') }}
                    </h3>

                    <div class="auth-form-group">
                        <label class="auth-label">{{ __('onboarding.patient.has_id_label') }}</label>
                        <div style="display: flex; gap: 1.5rem; margin: 0.5rem 0;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="radio" name="has_health_id" value="yes" onclick="toggleHealthIdField(true)"> Yes
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                                <input type="radio" name="has_health_id" value="no" checked onclick="toggleHealthIdField(false)"> No
                            </label>
                        </div>
                    </div>

                    <div class="auth-form-group" id="health-id-wrapper" style="display: none; margin-bottom: 1rem;">
                        <label for="health_id" class="auth-label">{{ __('onboarding.patient.health_id') }}</label>
                        <input type="text" id="health_id" name="health_id" class="auth-input" placeholder="OPC-XXX-XXXX-XXX">
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="national_id" class="auth-label">{{ __('onboarding.patient.national_id') }}</label>
                            <input type="text" id="national_id" name="national_id" class="auth-input" placeholder="ID Card or SSN">
                        </div>
                        <div class="auth-form-group">
                            <label for="insurance_number" class="auth-label">{{ __('onboarding.patient.insurance_num') }}</label>
                            <input type="text" id="insurance_number" name="insurance_number" class="auth-input" placeholder="Policy Card">
                        </div>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="prev_hosp_number" class="auth-label">{{ __('onboarding.patient.prev_hosp_num') }}</label>
                        <input type="text" id="prev_hosp_number" name="prev_hosp_number" class="auth-input" placeholder="Medical Record Card Number">
                    </div>
                </div>

                <!-- Section 3: Emergency Contact -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.patient.sec_emergency') }}
                    </h3>

                    <div class="auth-form-group">
                        <label for="emergency_name" class="auth-label">{{ __('onboarding.patient.emerg_name') }} *</label>
                        <input type="text" id="emergency_name" name="emergency_name" class="auth-input" required>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="emergency_relationship" class="auth-label">{{ __('onboarding.patient.emerg_rel') }} *</label>
                            <input type="text" id="emergency_relationship" name="emergency_relationship" class="auth-input" required placeholder="Spouse, Parent, Sibling...">
                        </div>
                        <div class="auth-form-group">
                            <label for="emergency_phone" class="auth-label">{{ __('onboarding.patient.emerg_phone') }} *</label>
                            <input type="tel" id="emergency_phone" name="emergency_phone" class="auth-input" required>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Account Security -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.patient.sec_security') }}
                    </h3>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="password" class="auth-label">{{ __('onboarding.common.password') }} *</label>
                            <input type="password" id="password" name="password" class="auth-input" required minlength="8" placeholder="Min. 8 characters">
                        </div>
                        <div class="auth-form-group">
                            <label for="confirm_password" class="auth-label">{{ __('onboarding.common.confirm_password') }} *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="auth-input" required minlength="8" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="auth-checkbox-group" style="margin-top: 1.25rem;">
                        <input type="checkbox" id="accept_terms" name="accept_terms" required>
                        <label for="accept_terms" class="auth-checkbox-label">{{ __('onboarding.common.accept_terms') }} *</label>
                    </div>

                    <div class="auth-checkbox-group" style="margin-top: 0.75rem;">
                        <input type="checkbox" id="accept_privacy" name="accept_privacy" required>
                        <label for="accept_privacy" class="auth-checkbox-label">{{ __('onboarding.common.accept_privacy') }} *</label>
                    </div>
                </div>

                <!-- Section 5: Consent Notice -->
                <div style="margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.patient.sec_consent') }}
                    </h3>
                    <div style="background-color: var(--auth-primary-light); border-left: 4px solid var(--auth-primary); padding: 1.25rem; border-radius: 0.5rem; font-size: 0.85rem; line-height: 1.5; color: var(--auth-text-secondary); font-weight: 600;">
                        {{ __('onboarding.patient.consent_notice') }}
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-btn auth-btn-primary" style="margin-top: 1.5rem;">
                    <i data-lucide="shield-check"></i>
                    <span>{{ __('onboarding.patient.cta_btn') }}</span>
                </button>
            </form>
        </div>

        <div class="auth-footer-links" style="margin-top: 2rem;">
            <a href="{{ route('register') }}" class="back-link">
                <i data-lucide="arrow-left" style="width: 1rem; height: 1rem; vertical-align: middle;"></i> 
                {{ __('onboarding.common.back') }}
            </a>
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
