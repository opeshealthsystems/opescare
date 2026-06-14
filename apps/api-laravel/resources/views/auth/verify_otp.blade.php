@extends('layouts.auth')

@section('title', __('onboarding.otp.title'))

@section('content')
<style>
*,*::before,*::after{box-sizing:border-box}
body{margin:0;min-height:100vh;background:var(--p-bg,#f0f4f8);font-family:var(--p-font,'Inter',system-ui,sans-serif);display:flex;align-items:center;justify-content:center;padding:1.5rem}
.auth-card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(15,76,129,.1);padding:2.5rem 2rem;width:100%;max-width:480px}
.auth-card__icon{width:56px;height:56px;border-radius:14px;background:rgba(15,76,129,.08);display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem}
.auth-card__icon i{width:26px;height:26px;color:#0F4C81}
.auth-card__title{font-size:1.375rem;font-weight:700;color:#0f172a;margin:0 0 .375rem}
.auth-card__sub{font-size:.875rem;color:#64748b;margin:0 0 1.75rem;line-height:1.5}
.btn-primary-full{width:100%;padding:.75rem;background:#0F4C81;color:#fff;border:none;border-radius:8px;font-size:.9375rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:background .15s;margin-top:.5rem}
.btn-primary-full:hover{background:#0a3560}
.alert-error{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:.875rem 1rem;margin-bottom:1.25rem;color:#dc2626;font-size:.875rem;display:flex;gap:.5rem}
.otp-inputs{display:flex;gap:.625rem;justify-content:center;margin:1.25rem 0}
.otp-input{width:48px;height:56px;text-align:center;font-size:1.375rem;font-weight:700;border:1.5px solid #e2e8f0;border-radius:8px;outline:none;color:#0f172a;transition:border-color .15s}
.otp-input:focus{border-color:#0F4C81;box-shadow:0 0 0 3px rgba(15,76,129,.12)}
.otp-timer{text-align:center;font-size:.875rem;color:#64748b;font-weight:600;margin-bottom:1rem}
.otp-timer span{color:#dc2626}
.auth-link{text-align:center;margin-top:1rem;font-size:.875rem;color:#64748b}
.auth-link a{color:#0F4C81;font-weight:600;text-decoration:none}
.auth-link--muted{text-align:center;margin-top:.75rem;font-size:.8125rem}
.auth-link--muted a{color:#64748b;font-weight:700;text-decoration:none}
.security-note{display:flex;align-items:flex-start;gap:.5rem;margin-top:1.75rem;font-size:.8125rem;color:#64748b;line-height:1.5}
.security-note i{width:14px;height:14px;color:#0F4C81;flex-shrink:0;margin-top:2px}
</style>

<div class="auth-card">
    <div class="auth-card__icon">
        <i data-lucide="message-square"></i>
    </div>
    <h1 class="auth-card__title">Enter verification code</h1>
    <p class="auth-card__sub">{{ __('onboarding.otp.subtitle') }}</p>

    @if(session('error'))
        <div class="alert-error">
            <i data-lucide="triangle-alert" style="width:16px;height:16px;flex-shrink:0;margin-top:2px"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <form action="{{ route('otp.verify.submit') }}" method="POST" id="otp-form">
        @csrf

        <div class="otp-inputs">
            <input type="text" class="otp-input" name="otp[]" maxlength="1" required autofocus inputmode="numeric" oninput="moveNext(this, 'otp-box-2')" id="otp-box-1" onkeydown="handleBackspace(event, null)">
            <input type="text" class="otp-input" name="otp[]" maxlength="1" required inputmode="numeric" oninput="moveNext(this, 'otp-box-3')" id="otp-box-2" onkeydown="handleBackspace(event, 'otp-box-1')">
            <input type="text" class="otp-input" name="otp[]" maxlength="1" required inputmode="numeric" oninput="moveNext(this, 'otp-box-4')" id="otp-box-3" onkeydown="handleBackspace(event, 'otp-box-2')">
            <input type="text" class="otp-input" name="otp[]" maxlength="1" required inputmode="numeric" oninput="moveNext(this, 'otp-box-5')" id="otp-box-4" onkeydown="handleBackspace(event, 'otp-box-3')">
            <input type="text" class="otp-input" name="otp[]" maxlength="1" required inputmode="numeric" oninput="moveNext(this, 'otp-box-6')" id="otp-box-5" onkeydown="handleBackspace(event, 'otp-box-4')">
            <input type="text" class="otp-input" name="otp[]" maxlength="1" required inputmode="numeric" oninput="submitOtpForm(this)" id="otp-box-6" onkeydown="handleBackspace(event, 'otp-box-5')">
        </div>

        <div class="otp-timer">
            {{ __('onboarding.otp.timer_hint') }} <span id="otp-timer">03:00</span>
        </div>

        <button type="submit" class="btn-primary-full">
            <i data-lucide="check-circle"></i>
            <span>{{ __('onboarding.otp.submit_btn') }}</span>
        </button>
    </form>

    <div class="auth-link">
        <a href="#" onclick="resendCode(); return false;">
            <i data-lucide="rotate-cw" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>
            {{ __('onboarding.otp.resend_btn') }}
        </a>
    </div>

    <div class="auth-link--muted">
        <a href="{{ route('login') }}">{{ __('onboarding.otp.change_info') }}</a>
    </div>

    <div class="security-note">
        <i data-lucide="shield-check"></i>
        <p style="margin:0">{{ __('onboarding.otp.security_note') }}</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var msgExpired = @json(__('onboarding.otp.errors.expired', [], app()->getLocale()) ?: 'The code has expired. Please request a new verification code.');
    var msgResent  = @json(__('onboarding.otp.resent_notice', [], app()->getLocale()) ?: 'A new 6-digit verification code has been transmitted to your secure inbox.');

    function moveNext(current, nextId) {
        current.value = current.value.replace(/[^0-9]/g, '');
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
            setTimeout(function() {
                document.getElementById('otp-form').submit();
            }, 100);
        }
    }

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
            timerSpan.innerText = 'EXPIRED';
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
