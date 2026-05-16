@extends('layouts.public')

@section('title', __('auth.register.title'))

@section('content')
    <div class="content-header">
        <div class="container">
            <h1>{{ __('auth.register.title') }}</h1>
            <p class="text-muted">{{ __('auth.register.subtitle') }}</p>
        </div>
    </div>

    <div class="content-body">
        <div class="container">
            <div class="selector-grid">
                <a href="{{ route('register.patient') }}" class="selector-card">
                    <div class="selector-icon">
                        <i data-lucide="user-round"></i>
                    </div>
                    <h2>{{ __('auth.register.patient_title') }}</h2>
                    <p class="text-muted">{{ __('auth.register.patient_desc') }}</p>
                    <span class="btn btn-primary" style="margin-top: 2rem;">{{ __('auth.register.submit_patient') }}</span>
                </a>

                <a href="{{ route('register.hospital') }}" class="selector-card">
                    <div class="selector-icon">
                        <i data-lucide="hospital"></i>
                    </div>
                    <h2>{{ __('auth.register.hospital_title') }}</h2>
                    <p class="text-muted">{{ __('auth.register.hospital_desc') }}</p>
                    <span class="btn btn-secondary" style="margin-top: 2rem;">{{ __('auth.register.submit_hospital') }}</span>
                </a>
            </div>
            
            <div class="text-center" style="margin-top: 3rem;">
                <p class="text-muted">{{ __('auth.register.already_have') }} <a href="{{ route('login') }}" class="font-bold" style="color: var(--color-primary);">{{ __('auth.register.login') }}</a></p>
            </div>
        </div>
    </div>
@endsection
