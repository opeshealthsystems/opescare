@extends('layouts.auth')

@section('title', __('onboarding.forgot.title'))

@section('content')
    <div class="auth-card" style="max-width: 480px; padding: 2.5rem;">
        <div class="auth-title-group">
            <h1 class="auth-headline" style="font-size: 1.65rem;">{{ __('onboarding.forgot.title') }}</h1>
            <p class="auth-subheadline">{{ __('onboarding.forgot.desc') }}</p>
        </div>

        @if(session('success'))
            <div class="auth-alert auth-alert-success">
                <i data-lucide="badge-check"></i>
                <div>{{ session('success') }}</div>
            </div>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="{{ route('login') }}" class="auth-btn auth-btn-secondary">
                    <i data-lucide="arrow-left"></i>
                    <span>{{ __('onboarding.selector.signin') }}</span>
                </a>
            </div>
        @else
            <form action="{{ route('password.email') }}" method="POST" class="auth-form">
                @csrf

                <div class="auth-form-group">
                    <label for="email" class="auth-label">{{ __('onboarding.common.email') }} or {{ __('onboarding.common.phone') }} *</label>
                    <input type="text" id="email" name="email" class="auth-input" required autofocus placeholder="name@email.com or +123...">
                </div>

                <div style="background-color: var(--auth-bg); padding: 0.85rem; border-radius: 0.5rem; border: 1px solid var(--auth-border); font-size: 0.75rem; color: var(--auth-text-secondary); line-height: 1.4; font-weight: 500;">
                    To protect patient record privacy, we do not reveal whether a matching profile is found in our registry.
                </div>

                <!-- Submit Button -->
                <button type="submit" class="auth-btn auth-btn-primary" style="margin-top: 1rem;">
                    <i data-lucide="send"></i>
                    <span>{{ __('onboarding.forgot.cta') }}</span>
                </button>
            </form>

            <div class="auth-footer-links" style="margin-top: 1.5rem;">
                <a href="{{ route('login') }}" class="back-link">
                    <i data-lucide="arrow-left" style="width: 1rem; height: 1rem; vertical-align: middle;"></i> 
                    {{ __('onboarding.selector.signin') }}
                </a>
            </div>
        @endif
    </div>
@endsection
