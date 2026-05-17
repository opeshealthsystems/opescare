@extends('layouts.auth')

@section('title', __('onboarding.invite.title'))

@section('content')
    <div class="auth-card" style="max-width: 600px; padding: 2.5rem;">
        
        @if(isset($error))
            <!-- Invitation Error States (Failure States) -->
            <div style="text-align: center;">
                <div style="width: 4rem; height: 4rem; background: #FEE2E2; color: var(--auth-danger); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                    <i data-lucide="shield-alert" style="width: 2rem; height: 2rem;"></i>
                </div>
                
                @if($error === 'expired')
                    <h1 class="auth-headline" style="font-size: 1.5rem;">{{ __('onboarding.invite.errors.expired') }}</h1>
                @elseif($error === 'used')
                    <h1 class="auth-headline" style="font-size: 1.5rem;">{{ __('onboarding.invite.errors.used') }}</h1>
                @elseif($error === 'revoked')
                    <h1 class="auth-headline" style="font-size: 1.5rem;">{{ __('onboarding.invite.errors.revoked') }}</h1>
                @endif
                
                <p class="auth-subheadline" style="margin-top: 0.5rem; margin-bottom: 2rem;">
                    If you believe this is an error, please coordinate with your clinical branch administrator to issue a new OpesCare invitation.
                </p>

                <a href="{{ route('login') }}" class="auth-btn auth-btn-secondary">
                    <i data-lucide="arrow-left"></i>
                    <span>{{ __('onboarding.selector.signin') }}</span>
                </a>
            </div>
        @else
            <!-- Active Invitation View -->
            <div class="auth-title-group">
                <h1 class="auth-headline">{{ __('onboarding.invite.title') }}</h1>
                <p class="auth-subheadline">{{ __('onboarding.invite.subtitle') }}</p>
            </div>

            <!-- Invitation context metadata card -->
            <div style="background-color: var(--auth-bg); border-radius: 0.75rem; border: 1px solid var(--auth-border); padding: 1.25rem; margin-bottom: 1.75rem;">
                <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ __('onboarding.invite.sec_details') }}
                </h3>
                
                <div class="pending-meta-row" style="padding: 0.5rem 0;">
                    <span class="pending-meta-label" style="font-size: 0.75rem;">{{ __('onboarding.invite.org_lbl') }}</span>
                    <span class="pending-meta-value" style="font-size: 0.8125rem;">{{ $org_name }}</span>
                </div>
                <div class="pending-meta-row" style="padding: 0.5rem 0;">
                    <span class="pending-meta-label" style="font-size: 0.75rem;">{{ __('onboarding.invite.facility_lbl') }}</span>
                    <span class="pending-meta-value" style="font-size: 0.8125rem;">{{ $facility_name }}</span>
                </div>
                <div class="pending-meta-row" style="padding: 0.5rem 0;">
                    <span class="pending-meta-label" style="font-size: 0.75rem;">{{ __('onboarding.invite.role_lbl') }}</span>
                    <span class="pending-meta-value" style="font-size: 0.8125rem; color: var(--auth-teal); font-weight: 800;">{{ $role_name }}</span>
                </div>
                <div class="pending-meta-row" style="padding: 0.5rem 0;">
                    <span class="pending-meta-label" style="font-size: 0.75rem;">{{ __('onboarding.invite.invited_by_lbl') }}</span>
                    <span class="pending-meta-value" style="font-size: 0.8125rem;">{{ $invited_by }}</span>
                </div>
                <div class="pending-meta-row" style="padding: 0.5rem 0;">
                    <span class="pending-meta-label" style="font-size: 0.75rem;">{{ __('onboarding.invite.expiry_lbl') }}</span>
                    <span class="pending-meta-value" style="font-size: 0.8125rem; color: var(--auth-warning);">{{ $expiry }}</span>
                </div>
            </div>

            <!--StaffInviteAcceptForm Component -->
            <form action="{{ route('invite.accept.submit', $token) }}" method="POST" class="auth-form">
                @csrf

                <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ __('onboarding.invite.sec_profile') }}
                </h3>

                <div class="auth-form-group">
                    <label for="name" class="auth-label">Full Name *</label>
                    <input type="text" id="name" name="name" class="auth-input" required value="{{ old('name') }}">
                </div>

                <div class="auth-form-group" style="margin-top: 0.5rem;">
                    <label for="phone" class="auth-label">{{ __('onboarding.common.phone') }} *</label>
                    <input type="tel" id="phone" name="phone" class="auth-input" required value="{{ old('phone') }}">
                </div>

                <div class="auth-form-row" style="margin-top: 0.5rem;">
                    <div class="auth-form-group">
                        <label for="password" class="auth-label">{{ __('onboarding.common.password') }} *</label>
                        <input type="password" id="password" name="password" class="auth-input" required minlength="8" placeholder="Min. 8 characters">
                    </div>
                    <div class="auth-form-group">
                        <label for="confirm_password" class="auth-label">{{ __('onboarding.common.confirm_password') }} *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="auth-input" required placeholder="••••••••">
                    </div>
                </div>

                <div class="auth-checkbox-group" style="margin-top: 1rem;">
                    <input type="checkbox" id="accept_terms" name="accept_terms" required>
                    <label for="accept_terms" class="auth-checkbox-label">I accept the OpesCare terms of service and patient records access audits *</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-btn auth-btn-primary" style="margin-top: 1.25rem;">
                    <i data-lucide="check"></i>
                    <span>{{ __('onboarding.invite.cta_btn') }}</span>
                </button>
            </form>
        @endif
    </div>
@endsection
