@extends('layouts.auth')

@section('title', __('onboarding.forgot.reset_title'))

@section('content')
    <div class="auth-card" style="max-width: 480px; padding: 2.5rem;">
        <div class="auth-title-group">
            <h1 class="auth-headline" style="font-size: 1.65rem;">{{ __('onboarding.forgot.reset_title') }}</h1>
            <p class="auth-subheadline">Configure your new secure OpesCare credentials. Passwords must be at least 8 characters long and satisfy clinical security policies.</p>
        </div>

        <form action="{{ route('password.update', $token) }}" method="POST" class="auth-form">
            @csrf

            <!-- PasswordInput -->
            <div class="auth-form-group">
                <label for="password" class="auth-label">{{ __('onboarding.forgot.new_pass') }} *</label>
                <div class="auth-pass-wrapper">
                    <input type="password" id="password" name="password" class="auth-input" required minlength="8" placeholder="••••••••">
                    <button type="button" class="auth-pass-toggle" onclick="togglePasswordVisibility('password')">
                        <i data-lucide="eye" id="password-toggle-icon"></i>
                    </button>
                </div>
            </div>

            <!-- PasswordInput -->
            <div class="auth-form-group" style="margin-top: 0.5rem;">
                <label for="confirm_password" class="auth-label">{{ __('onboarding.forgot.confirm_new') }} *</label>
                <div class="auth-pass-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="auth-input" required minlength="8" placeholder="••••••••">
                    <button type="button" class="auth-pass-toggle" onclick="togglePasswordVisibility('confirm_password')">
                        <i data-lucide="eye" id="confirm-password-toggle-icon"></i>
                    </button>
                </div>
            </div>

            <div style="background-color: var(--auth-bg); padding: 0.85rem; border-radius: 0.5rem; border: 1px solid var(--auth-border); font-size: 0.75rem; color: var(--auth-text-secondary); line-height: 1.4; font-weight: 500;">
                <ul style="padding-left: 1.25rem;">
                    <li>Minimum length: 8 characters</li>
                    <li>Must include letters and numbers</li>
                    <li>Cannot reuse your previous password</li>
                </ul>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-btn auth-btn-primary" style="margin-top: 1rem;">
                <i data-lucide="key-round"></i>
                <span>{{ __('onboarding.forgot.reset_cta') }}</span>
            </button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-toggle-icon');
            const targetIconId = fieldId === 'password' ? 'password-toggle-icon' : 'confirm-password-toggle-icon';
            const targetIcon = document.getElementById(targetIconId);

            if (input.type === 'password') {
                input.type = 'text';
                targetIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                targetIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
@endsection
