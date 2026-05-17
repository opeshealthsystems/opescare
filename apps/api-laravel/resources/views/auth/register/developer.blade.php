@extends('layouts.auth')

@section('title', __('onboarding.developer.title'))

@section('content')
    <div class="auth-card" style="max-width: 650px; padding: 2.5rem;">
        <div class="auth-title-group">
            <h1 class="auth-headline">{{ __('onboarding.developer.title') }}</h1>
            <p class="auth-subheadline">{{ __('onboarding.developer.subtitle') }}</p>
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
            <!--DeveloperAccessRequestForm Component -->
            <form action="{{ route('register.developer.submit') }}" method="POST" class="auth-form">
                @csrf

                <!-- Section 1: Contact Details -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.org.contact_sec') }}
                    </h3>

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="name" class="auth-label">Full Name *</label>
                            <input type="text" id="name" name="name" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="organization" class="auth-label">Software Vendor / Organization *</label>
                            <input type="text" id="organization" name="organization" class="auth-input" required>
                        </div>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="role" class="auth-label">Role / Job Title *</label>
                            <input type="text" id="role" name="role" class="auth-input" required placeholder="Lead Interoperability Engineer, Product Owner...">
                        </div>
                        <div class="auth-form-group">
                            <label for="country" class="auth-label">{{ __('onboarding.patient.country') }} *</label>
                            <input type="text" id="country" name="country" class="auth-input" required value="Canada">
                        </div>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label for="email" class="auth-label">{{ __('onboarding.common.email') }} *</label>
                            <input type="email" id="email" name="email" class="auth-input" required>
                        </div>
                        <div class="auth-form-group">
                            <label for="phone" class="auth-label">{{ __('onboarding.common.phone') }} *</label>
                            <input type="tel" id="phone" name="phone" class="auth-input" required>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Technical Parameters -->
                <div style="border-bottom: 1px solid var(--auth-border); padding-bottom: 1.5rem; margin-bottom: 1rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ __('onboarding.developer.sec_vendor') }}
                    </h3>

                    <div class="auth-form-group">
                        <label for="system_type" class="auth-label">{{ __('onboarding.developer.system_type_lbl') }} *</label>
                        <select id="system_type" name="system_type" class="auth-input" style="padding-top: 0.65rem; padding-bottom: 0.65rem;" required>
                            <option value="" disabled selected>{{ __('onboarding.common.select_option') }}</option>
                            <option value="HIS">Hospital Information System (HIS)</option>
                            <option value="LIS">Laboratory Information System (LIS)</option>
                            <option value="PHARMA">Pharmacy Stock / Dispensing System</option>
                            <option value="INSURANCE">Insurance Claims Management Engine</option>
                            <option value="BLOOD">Blood Bank Registry Platform</option>
                            <option value="MOBILE">Consumer Health Mobile App</option>
                            <option value="OTHER">Other Health Interoperability System</option>
                        </select>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="data_flow" class="auth-label">{{ __('onboarding.developer.expected_flow_lbl') }} *</label>
                        <select id="data_flow" name="data_flow" class="auth-input" style="padding-top: 0.65rem; padding-bottom: 0.65rem;" required>
                            <option value="" disabled selected>{{ __('onboarding.common.select_option') }}</option>
                            <option value="PULL_SUMMARY">Pull patient CCDA/FHIR summaries</option>
                            <option value="PUSH_ENCOUNTERS">Push clinical encounters</option>
                            <option value="PUSH_RESULTS">Push validated lab results</option>
                            <option value="PUSH_PRESCRIPTIONS">Push validated prescriptions</option>
                            <option value="SYNC_INVENTORY">Sync pharmacy/medicine stock</option>
                            <option value="SYNC_BLOOD">Sync blood bank availability logs</option>
                            <option value="WEBHOOKS">Receive webhook dispatch events</option>
                        </select>
                    </div>

                    <div class="auth-form-group" style="margin-top: 1rem;">
                        <label for="integration_purpose" class="auth-label">Integration Purpose / Clinical Value *</label>
                        <textarea id="integration_purpose" name="integration_purpose" class="auth-input" style="min-height: 80px; resize: vertical;" required placeholder="Briefly describe the clinical use case or data synchronizations required..."></textarea>
                    </div>

                    <div class="auth-form-row" style="margin-top: 1rem;">
                        <div class="auth-form-group">
                            <label class="auth-label">{{ __('onboarding.developer.sandbox_lbl') }}</label>
                            <div style="display: flex; gap: 1.5rem; margin-top: 0.25rem;">
                                <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="sandbox" value="yes" checked> Yes
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="sandbox" value="no"> No
                                </label>
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label class="auth-label">{{ __('onboarding.developer.production_lbl') }}</label>
                            <div style="display: flex; gap: 1.5rem; margin-top: 0.25rem;">
                                <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="production" value="yes"> Yes
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="production" value="no" checked> No
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API Security Notice -->
                <div style="margin-bottom: 1.5rem; background-color: var(--auth-purple); background-color: #F5F3FF; border: 1px solid rgba(109, 40, 217, 0.15); padding: 1rem 1.25rem; border-radius: 0.5rem; font-size: 0.8125rem; line-height: 1.45; color: #5B21B6; font-weight: 600; display: flex; gap: 0.5rem; align-items: flex-start;">
                    <i data-lucide="shield-check" style="color: var(--auth-purple); width: 1.25rem; height: 1.25rem; flex-shrink: 0;"></i>
                    <p>{{ __('onboarding.developer.safety_notice') }}</p>
                </div>

                <div class="auth-checkbox-group">
                    <input type="checkbox" id="accept_vendor_terms" name="accept_vendor_terms" required>
                    <label for="accept_vendor_terms" class="auth-checkbox-label">I agree to OpesCare connect developer terms and sandboxing policies *</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-btn auth-btn-primary" style="margin-top: 1.5rem;">
                    <i data-lucide="code-2"></i>
                    <span>{{ __('onboarding.developer.cta_btn') }}</span>
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
