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

            <form action="{{ route('password.email') }}" method="POST" class="auth-form">
                @csrf

                <div class="auth-form-group">
                    <label for="email" class="auth-label">{{ __('onboarding.common.email') }} {{ __('onboarding.common.or') }} {{ __('onboarding.common.phone') }} *</label>
                    <input type="text" id="email" name="email" class="auth-input{{ $errors->has('email') ? ' auth-input-error' : '' }}" required autofocus placeholder="name@email.com or +123..." value="{{ old('email') }}">
                    @error('email')<div class="auth-field-error">{{ $message }}</div>@enderror
                </div>

                <div style="background-color: var(--auth-bg); padding: 0.85rem; border-radius: 0.5rem; border: 1px solid var(--auth-border); font-size: 0.75rem; color: var(--auth-text-secondary); line-height: 1.4; font-weight: 500;">
                    {{ __('onboarding.forgot.privacy_note') }}
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
