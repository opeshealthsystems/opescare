@extends('layouts.auth')

@section('title', __('onboarding.otp.title'))

@section('content')
    <div class="auth-card" style="max-width: 480px; padding: 2.5rem;">
        <div class="auth-title-group" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; background: var(--auth-primary-light); color: var(--auth-primary); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                <i data-lucide="shield-ellipsis" style="width: 2rem; height: 2rem;"></i>
            </div>
            <h1 class="auth-headline" style="font-size: 1.65rem;">{{ __('onboarding.otp.title') }}</h1>
            <p class="auth-subheadline">{{ __('onboarding.otp.subtitle') }}</p>
        </div>

        @if(session('error'))
            <div class="auth-alert auth-alert-danger" style="margin-top: 1rem;">
                <i data-lucide="triangle-alert"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- OTPInput Reusable Component -->
        <form action="{{ route('otp.verify.submit') }}" method="POST" class="auth-form" style="margin-top: 1rem;" id="otp-form">
            @csrf

            <div class="otp-input-container">
                <input type="text" class="otp-box" name="otp[]" maxlength="1" required autofocus oninput="moveNext(this, 'otp-box-2')" id="otp-box-1" onkeydown="handleBackspace(event, null)">
                <input type="text" class="otp-box" name="otp[]" maxlength="1" required oninput="moveNext(this, 'otp-box-3')" id="otp-box-2" onkeydown="handleBackspace(event, 'otp-box-1')">
                <input type="text" class="otp-box" name="otp[]" maxlength="1" required oninput="moveNext(this, 'otp-box-4')" id="otp-box-3" onkeydown="handleBackspace(event, 'otp-box-2')">
                <input type="text" class="otp-box" name="otp[]" maxlength="1" required oninput="moveNext(this, 'otp-box-5')" id="otp-box-4" onkeydown="handleBackspace(event, 'otp-box-3')">
                <input type="text" class="otp-box" name="otp[]" maxlength="1" required oninput="moveNext(this, 'otp-box-6')" id="otp-box-5" onkeydown="handleBackspace(event, 'otp-box-4')">
                <input type="text" class="otp-box" name="otp[]" maxlength="1" required oninput="submitOtpForm(this)" id="otp-box-6" onkeydown="handleBackspace(event, 'otp-box-5')">
            </div>

            <div style="text-align: center; margin: 1.5rem 0; font-size: 0.875rem; color: var(--auth-text-secondary); font-weight: 600;">
                {{ __('onboarding.otp.timer_hint') }} <span style="color: var(--auth-danger);" id="otp-timer">03:00</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-btn auth-btn-primary">
                <i data-lucide="shield-check"></i>
                <span>{{ __('onboarding.otp.submit_btn') }}</span>
            </button>
        </form>

        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1.5rem; text-align: center;">
            <a href="#" class="auth-btn auth-btn-secondary" style="font-size: 0.85rem;" onclick="resendCode()">
                <i data-lucide="rotate-cw" style="width: 1rem; height: 1rem;"></i>
                <span>{{ __('onboarding.otp.resend_btn') }}</span>
            </a>
            
            <a href="{{ route('login') }}" style="font-size: 0.8125rem; font-weight: 700; color: var(--auth-text-muted); text-decoration: none;">
                {{ __('onboarding.otp.change_info') }}
            </a>
        </div>

        <div class="auth-security-block" style="margin-top: 2rem;">
            <i data-lucide="shield-check"></i>
            <p>{{ __('onboarding.otp.security_note') }}</p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var msgExpired = @json(__('onboarding.otp.errors.expired', [], app()->getLocale()) ?: 'The code has expired. Please request a new verification code.');
        var msgResent  = @json(__('onboarding.otp.resent_notice', [], app()->getLocale()) ?: 'A new 6-digit verification code has been transmitted to your secure inbox.');

        // Automatic keypad movement
        function moveNext(current, nextId) {
            current.value = current.value.replace(/[^0-9]/g, ''); // Numeric only
            if (current.value.length >= 1) {
                document.getElementById(nextId).focus();
            }
        }

        function handleBackspace(event, prevId) {
            if (event.key === 'Backspace' && event.target.value.length === 0 && prevId) {
                document.getElementById(prevId).focus();
            }
        }

        function submitOtpForm(current) {
            current.value = current.value.replace(/[^0-9]/g, '');
            if (current.value.length >= 1) {
                current.blur();
                setTimeout(() => {
                    document.getElementById('otp-form').submit();
                }, 100);
            }
        }

        // 3 minute countdown timer
        let timeLeft = 180;
        const timerSpan = document.getElementById('otp-timer');

        const countdown = setInterval(function() {
            timeLeft--;
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;

            if (seconds < 10) seconds = '0' + seconds;
            if (minutes < 10) minutes = '0' + minutes;

            timerSpan.innerText = minutes + ':' + seconds;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerSpan.innerText = "EXPIRED";
                alert(msgExpired);
            }
        }, 1000);

        function resendCode() {
            fetch('{{ route('otp.resend') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            }).then(function(res) {
                if (res.ok) {
                    timeLeft = 180;
                    alert(msgResent);
                }
            }).catch(function() {});
        }
    </script>
@endsection
