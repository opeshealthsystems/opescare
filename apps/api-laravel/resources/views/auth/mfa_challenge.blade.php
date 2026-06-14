@extends('layouts.auth')

@section('title', 'Two-factor verification')

@section('content')
    <div class="auth-card">
        <div class="auth-title-group">
            <h1 class="auth-headline">Two-factor verification</h1>
            <p class="auth-subheadline">Enter the six-digit code from your authenticator app.</p>
        </div>

        @if(session('error'))
            <div class="auth-alert auth-alert-danger">
                <i data-lucide="triangle-alert"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form action="{{ route('mfa.challenge.submit') }}" method="POST" class="auth-form">
            @csrf

            <div class="auth-form-group">
                <label for="code" class="auth-label">Authentication code</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    class="auth-input{{ $errors->has('code') ? ' auth-input-error' : '' }}"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    required
                    autofocus
                >
                @error('code')<div class="auth-field-error">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="auth-btn auth-btn-primary" style="margin-top: 1rem;">
                <i data-lucide="shield-check"></i>
                <span>Verify</span>
            </button>
        </form>
    </div>
@endsection
