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
                    <button type="button" class="auth-pass-toggle" onclick="togglePasswordVisibility('password')">
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
@endsection

@section('scripts')
    <script>
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-toggle-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    </script>
@endsection
