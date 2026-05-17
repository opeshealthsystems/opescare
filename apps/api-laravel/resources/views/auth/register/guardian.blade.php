@extends('layouts.auth')

@section('title', __('onboarding.guardian.title'))

@section('content')
    <div class="auth-card" style="max-width: 650px; padding: 2.5rem;">
        <div class="auth-title-group">
            <h1 class="auth-headline">{{ __('onboarding.guardian.title') }}</h1>
            <p class="auth-subheadline">{{ __('onboarding.guardian.subtitle') }}</p>
        </div>

        @if(session('success'))
            <div class="auth-alert auth-alert-success">
                <i data-lucide="badge-check" style="width: 1.5rem; height: 1.5rem; flex-shrink: 0;"></i>
                <div>{{ session('success') }}</div>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="{{ route('public.landing') }}" class="auth-btn auth-btn-primary">
                    <i data-lucide="arrow-left"></i>
                    <span>{{ __('onboarding.common.back_to_home') }}</span>
                </a>
            </div>
        @else
            <!--GuardianSignupForm Component -->
            <form action="{{ route('register.guardian.submit') }}" method="POST" class="auth-form">
                @csrf

                <!-- Section 1: Guardian Information -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.guardian.sec_guardian') }}
                    </h3>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="first_name" class="auth-label">{{ __('onboarding.patient.first_name') }} *</label>
                            <input type="text" id="first_name" name="first_name" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="last_name" class="auth-label">{{ __('onboarding.patient.last_name') }} *</label>
                            <input type="text" id="last_name" name="last_name" class="auth-input" required>
                        </div>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="phone" class="auth-label">{{ __('onboarding.common.phone') }} *</label>
                            <input type="tel" id="phone" name="phone" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="email" class="auth-label">{{ __('onboarding.common.email') }}</label>
                            <input type="email" id="email" name="email" class="auth-input" placeholder="optional@email.com">
                        </div>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="dob" class="auth-label">{{ __('onboarding.patient.dob') }} *</label>
                            <input type="date" id="dob" name="dob" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="preferred_language" class="auth-label">{{ __('onboarding.patient.preferred_lang') }} *</label>
                            <select id="preferred_language" name="preferred_language" class="auth-input" style="padding-top: 0.65rem; padding-bottom: 0.65rem;" required>
                                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                                <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>Français</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Dependent Information -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.guardian.sec_dependent') }}
                    </h3>

                    <div class="auth-form-group">
                        <label for="dep_name" class="auth-label">Patient / Dependent Full Name *</label>
                        <input type="text" id="dep_name" name="dep_name" class="auth-input" required placeholder="Full Name of the dependent">
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="dep_dob" class="auth-label">Patient Date of Birth *</label>
                            <input type="date" id="dep_dob" name="dep_dob" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="dep_sex" class="auth-label">Patient Biological Sex *</label>
                            <select id="dep_sex" name="dep_sex" class="auth-input" style="padding-top: 0.65rem; padding-bottom: 0.65rem;" required>
                                <option value="" disabled selected>{{ __('onboarding.common.select_option') }}</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                                <option value="O">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="dep_health_id" class="auth-label">{{ __('onboarding.patient.health_id') }}</label>
                            <input type="text" id="dep_health_id" name="dep_health_id" class="auth-input" placeholder="OPC-XXX-XXXX-XXX (If known)">
                        </div>
                        <div class="auth-form-group">
                            <label for="dep_relationship" class="auth-label">{{ __('onboarding.guardian.relationship_lbl') }} *</label>
                            <input type="text" id="dep_relationship" name="dep_relationship" class="auth-input" placeholder="Parent, Legal Guardian, Caregiver" required>
                        </div>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="access_reason" class="auth-label">{{ __('onboarding.guardian.reason_lbl') }} *</label>
                        <textarea id="access_reason" name="access_reason" class="auth-input" style="min-height: 80px; resize: vertical;" required placeholder="{{ __('onboarding.guardian.reason_desc') }}"></textarea>
                    </div>
                </div>

                <!-- Section 3: Security & Terms -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.patient.sec_security') }}
                    </h3>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="password" class="auth-label">{{ __('onboarding.common.password') }} *</label>
                            <input type="password" id="password" name="password" class="auth-input" required minlength="8" placeholder="••••••••">
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

                <!-- Consent Safety Banner -->
                <div style="margin-bottom: 1.5rem; background-color: var(--auth-teal-light); border-left: 4px solid var(--auth-teal); padding: 1rem 1.25rem; border-radius: 0.5rem; font-size: 0.8125rem; line-height: 1.45; color: var(--auth-text-secondary); font-weight: 600; display: flex; gap: 0.5rem; align-items: flex-start;">
                    <i data-lucide="shield-check" style="color: var(--auth-teal); width: 1.25rem; height: 1.25rem; flex-shrink: 0;"></i>
                    <p>Guardian access is fully audited. Actions taken on behalf of the dependent will require identification and verification in clinical contexts.</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-btn auth-btn-primary">
                    <i data-lucide="users"></i>
                    <span>{{ __('onboarding.guardian.cta_btn') }}</span>
                </button>
            </form>
        @endif
    </div>

    <div class="auth-footer-links" style="margin-top: 2rem;">
        <a href="{{ route('register') }}" class="back-link">
            <i data-lucide="arrow-left" style="width: 1rem; height: 1rem; vertical-align: middle;"></i> 
            {{ __('onboarding.common.back') }}
        </a>
    </div>
@endsection
