@extends('layouts.auth')

@section('title', __('onboarding.forgot.reset_title'))

@section('content')
    <div class="auth-card" style="max-width: 480px; padding: 2.5rem;">
        <div class="auth-title-group">
            <h1 class="auth-headline" style="font-size: 1.65rem;">{{ __('onboarding.forgot.reset_title') }}</h1>
            <p class="auth-subheadline">Configure your new secure OpesCare credentials. Passwords must be at least 8 characters long and satisfy clinical security policies.</p>
        </div>

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

        @if(session('status'))
            <div class="auth-alert auth-alert-success">
                <i data-lucide="badge-check"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        <form action="{{ route('password.update', $token) }}" method="POST" class="auth-form">
            @csrf

            <!-- PasswordInput -->
            <div class="auth-form-group">
                <label for="password" class="auth-label">{{ __('onboarding.forgot.new_pass') }} *</label>
                <div class="auth-pass-wrapper">
                    <input type="password" id="password" name="password" class="auth-input{{ $errors->has('password') ? ' auth-input-error' : '' }}" required minlength="8" placeholder="••••••••">
                    <button type="button" class="auth-pass-toggle" onclick="togglePasswordVisibility('password')">
                        <i data-lucide="eye" id="password-toggle-icon"></i>
                    </button>
                </div>
                @error('password')<div class="auth-field-error">{{ $message }}</div>@enderror
            </div>

            <!-- PasswordInput -->
            <div class="auth-form-group" style="margin-top: 0.5rem;">
                <label for="confirm_password" class="auth-label">{{ __('onboarding.forgot.confirm_new') }} *</label>
                <div class="auth-pass-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="auth-input{{ $errors->has('confirm_password') || $errors->has('password_confirmation') ? ' auth-input-error' : '' }}" required minlength="8" placeholder="••••••••">
                    <button type="button" class="auth-pass-toggle" onclick="togglePasswordVisibility('confirm_password')">
                        <i data-lucide="eye" id="confirm-password-toggle-icon"></i>
                    </button>
                </div>
                @error('confirm_password')<div class="auth-field-error">{{ $message }}</div>@enderror
                @error('password_confirmation')<div class="auth-field-error">{{ $message }}</div>@enderror
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
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    </script>
@endsection
