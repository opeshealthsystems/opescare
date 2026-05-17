@extends('layouts.auth')

@section('title', __('onboarding.suspended.title'))

@section('content')
    <div class="auth-card" style="max-width: 480px; padding: 2.5rem; border-top: 4px solid var(--auth-danger);">
        <div style="text-align: center;">
            <div style="width: 4.5rem; height: 4.5rem; background: #FEE2E2; color: var(--auth-danger); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i data-lucide="shield-alert" style="width: 2.25rem; height: 2.25rem;"></i>
            </div>
            
            <h1 class="auth-headline" style="font-size: 1.55rem; color: var(--auth-danger);">
                {{ __('onboarding.suspended.title') }}
            </h1>
            
            <p class="auth-subheadline" style="margin-top: 0.5rem; margin-bottom: 2rem;">
                {{ __('onboarding.suspended.desc') }}
            </p>
        </div>

        <div style="background-color: var(--auth-bg); border: 1px solid var(--auth-border); border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 2rem;">
            <div style="display: flex; gap: 0.5rem; align-items: center; font-size: 0.8125rem; font-weight: 800; color: var(--auth-text-primary); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                <i data-lucide="lock" style="width: 1rem; height: 1rem; color: var(--auth-danger);"></i>
                <span>{{ __('onboarding.suspended.security_warning') }}</span>
            </div>
            <p style="font-size: 0.75rem; color: var(--auth-text-secondary); line-height: 1.45; font-weight: 500;">
                Access attempt has been logged under audit tag <code>AUDIT-ERR-SUSPEND-{{ rand(1000, 9999) }}</code> along with connection headers. Direct API and clinical system bridges associated with this credential profile are temporarily frozen.
            </p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="{{ route('public.contact') }}" class="auth-btn auth-btn-primary" style="background-color: var(--auth-danger);">
                <i data-lucide="message-square"></i>
                <span>{{ __('onboarding.brand.contact_support') }}</span>
            </a>
            
            <a href="{{ route('login') }}" class="auth-btn auth-btn-secondary">
                <i data-lucide="arrow-left"></i>
                <span>{{ __('onboarding.selector.signin') }}</span>
            </a>
        </div>
    </div>
@endsection
