@extends('layouts.auth')

@section('title', __('onboarding.forgot.reset_title'))

@section('content')
    <div class="auth-card">
        <div class="auth-card__head">
            <h1 class="auth-card__title">{{ __('onboarding.forgot.reset_title') }}</h1>
            <p class="auth-card__sub">Configure your new secure OpesCare credentials. Passwords must be at least 8 characters long and satisfy clinical security policies.</p>
        </div>

        @if(session('error'))
            <div class="auth-alert auth-alert-danger">
                <i data-lucide="alert-circle"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="auth-alert auth-alert-danger">
                <i data-lucide="alert-circle"></i>
                <ul class="auth-alert-list">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if(session('status'))
            <div class="auth-alert auth-alert-success">
                <i data-lucide="badge-check"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        <form action="{{ route('password.update', $token) }}" method="POST" class="auth-form">
            @csrf

            <div class="auth-form-group">
                <label for="password" class="auth-label">{{ __('onboarding.forgot.new_pass') }} *</label>
                <div class="auth-pass-wrapper auth-input-icon-wrap">
                    <i data-lucide="lock" class="auth-input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="auth-input auth-input--icon{{ $errors->has('password') ? ' auth-input-error' : '' }}"
                        required
                        minlength="8"
                        placeholder="••••••••"
                    >
                    <button type="button" class="auth-pass-toggle" data-toggle-password="password">
                        <i data-lucide="eye" id="password-toggle-icon"></i>
                    </button>
                </div>
                @error('password')<div class="auth-field-error">{{ $message }}</div>@enderror
            </div>

            <div class="auth-form-group">
                <label for="confirm_password" class="auth-label">{{ __('onboarding.forgot.confirm_new') }} *</label>
                <div class="auth-pass-wrapper auth-input-icon-wrap">
                    <i data-lucide="lock" class="auth-input-icon"></i>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="auth-input auth-input--icon{{ $errors->has('confirm_password') || $errors->has('password_confirmation') ? ' auth-input-error' : '' }}"
                        required
                        minlength="8"
                        placeholder="••••••••"
                    >
                    <button type="button" class="auth-pass-toggle" data-toggle-password="confirm_password">
                        <i data-lucide="eye" id="confirm_password-toggle-icon"></i>
                    </button>
                </div>
                @error('confirm_password')<div class="auth-field-error">{{ $message }}</div>@enderror
                @error('password_confirmation')<div class="auth-field-error">{{ $message }}</div>@enderror
            </div>

            <div class="auth-privacy-note">
                <ul class="auth-policy-list">
                    <li>Minimum length: 8 characters</li>
                    <li>Must include letters and numbers</li>
                    <li>Cannot reuse your previous password</li>
                </ul>
            </div>

            <button type="submit" class="auth-btn auth-btn-primary auth-btn--mt">
                <i data-lucide="check-circle"></i>
                <span>{{ __('onboarding.forgot.reset_cta') }}</span>
            </button>
        </form>

        <div class="auth-footer-links auth-footer-links--mt">
            <a href="{{ route('login') }}" class="auth-back-link">
                <i data-lucide="arrow-left"></i>
                {{ __('onboarding.selector.signin') }}
            </a>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Password toggle is handled by auth.js via data-toggle-password attributes.
        // This inline script is kept only as a legacy fallback for the confirm field
        // which previously used onclick= handlers directly on buttons.
    </script>
@endsection
