@extends('layouts.auth')

@section('title', __('onboarding.forgot.title'))

@section('content')
    <div class="auth-card">
        <div class="auth-card__head">
            <h1 class="auth-card__title">{{ __('onboarding.forgot.title') }}</h1>
            <p class="auth-card__sub">{{ __('onboarding.forgot.desc') }}</p>
        </div>

        @if(session('success'))
            <div class="auth-alert auth-alert-success">
                <i data-lucide="badge-check"></i>
                <div>{{ session('success') }}</div>
            </div>

            <div class="auth-back-action">
                <a href="{{ route('login') }}" class="auth-btn auth-btn-secondary">
                    <i data-lucide="arrow-left"></i>
                    <span>{{ __('onboarding.selector.signin') }}</span>
                </a>
            </div>
        @else
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

            <form action="{{ route('password.email') }}" method="POST" class="auth-form">
                @csrf

                <div class="auth-form-group">
                    <label for="email" class="auth-label">{{ __('onboarding.common.email') }} {{ __('onboarding.common.or') }} {{ __('onboarding.common.phone') }} *</label>
                    <div class="auth-input-icon-wrap">
                        <i data-lucide="mail" class="auth-input-icon"></i>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            class="auth-input auth-input--icon{{ $errors->has('email') ? ' auth-input-error' : '' }}"
                            required
                            autofocus
                            placeholder="name@email.com or +123..."
                            value="{{ old('email') }}"
                        >
                    </div>
                    @error('email')<div class="auth-field-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-privacy-note">
                    {{ __('onboarding.forgot.privacy_note') }}
                </div>

                <button type="submit" class="auth-btn auth-btn-primary auth-btn--mt">
                    <i data-lucide="send"></i>
                    <span>{{ __('onboarding.forgot.cta') }}</span>
                </button>
            </form>

            <div class="auth-footer-links auth-footer-links--mt">
                <a href="{{ route('login') }}" class="auth-back-link">
                    <i data-lucide="arrow-left"></i>
                    {{ __('onboarding.selector.signin') }}
                </a>
            </div>
        @endif
    </div>
@endsection
