@extends('layouts.auth')

@section('title', __('onboarding.selector.title'))

@section('content')
    <div class="auth-card" style="max-width: 800px; margin: 0 auto; padding: 2.5rem;">
        <div class="auth-title-group" style="text-align: center; margin-bottom: 2.5rem;">
            <h1 class="auth-headline">{{ __('onboarding.selector.title') }}</h1>
            <p class="auth-subheadline" style="max-width: 600px; margin: 0 auto;">{{ __('onboarding.selector.subtitle') }}</p>
        </div>

        <!-- Onboarding Cards Grid (SignupTypeCard Reusable Components) -->
        <div class="onboarding-grid">
            
            <!-- Patient Onboarding -->
            <a href="{{ route('register.patient') }}" class="onboarding-card">
                <div>
                    <div class="onboarding-card-header">
                        <div class="onboarding-card-icon">
                            <i data-lucide="user-round"></i>
                        </div>
                        <h3>{{ __('onboarding.selector.cards.patient_title') }}</h3>
                    </div>
                    <p>{{ __('onboarding.selector.cards.patient_desc') }}</p>
                </div>
                <div class="onboarding-card-cta">
                    <span>{{ __('onboarding.selector.cards.patient_cta') }}</span>
                    <i data-lucide="arrow-right"></i>
                </div>
            </a>

            <!-- Guardian / Caregiver Onboarding -->
            <a href="{{ route('register.guardian') }}" class="onboarding-card">
                <div>
                    <div class="onboarding-card-header">
                        <div class="onboarding-card-icon">
                            <i data-lucide="users"></i>
                        </div>
                        <h3>{{ __('onboarding.selector.cards.guardian_title') }}</h3>
                    </div>
                    <p>{{ __('onboarding.selector.cards.guardian_desc') }}</p>
                </div>
                <div class="onboarding-card-cta">
                    <span>{{ __('onboarding.selector.cards.guardian_cta') }}</span>
                    <i data-lucide="arrow-right"></i>
                </div>
            </a>

            <!-- Hospital / Clinic Onboarding -->
            <a href="{{ route('register.organization', ['type' => 'hospital']) }}" class="onboarding-card">
                <div>
                    <div class="onboarding-card-header">
                        <div class="onboarding-card-icon">
                            <i data-lucide="hospital"></i>
                        </div>
                        <h3>{{ __('onboarding.selector.cards.hospital_title') }}</h3>
                    </div>
                    <p>{{ __('onboarding.selector.cards.hospital_desc') }}</p>
                </div>
                <div class="onboarding-card-cta">
                    <span>{{ __('onboarding.selector.cards.hospital_cta') }}</span>
                    <i data-lucide="arrow-right"></i>
                </div>
            </a>

            <!-- Pharmacy Onboarding -->
            <a href="{{ route('register.organization', ['type' => 'pharmacy']) }}" class="onboarding-card">
                <div>
                    <div class="onboarding-card-header">
                        <div class="onboarding-card-icon">
                            <i data-lucide="pill"></i>
                        </div>
                        <h3>{{ __('onboarding.selector.cards.pharmacy_title') }}</h3>
                    </div>
                    <p>{{ __('onboarding.selector.cards.pharmacy_desc') }}</p>
                </div>
                <div class="onboarding-card-cta">
                    <span>{{ __('onboarding.selector.cards.pharmacy_cta') }}</span>
                    <i data-lucide="arrow-right"></i>
                </div>
            </a>

            <!-- Laboratory Onboarding -->
            <a href="{{ route('register.organization', ['type' => 'laboratory']) }}" class="onboarding-card">
                <div>
                    <div class="onboarding-card-header">
                        <div class="onboarding-card-icon">
                            <i data-lucide="microscope"></i>
                        </div>
                        <h3>{{ __('onboarding.selector.cards.laboratory_title') }}</h3>
                    </div>
                    <p>{{ __('onboarding.selector.cards.laboratory_desc') }}</p>
                </div>
                <div class="onboarding-card-cta">
                    <span>{{ __('onboarding.selector.cards.laboratory_cta') }}</span>
                    <i data-lucide="arrow-right"></i>
                </div>
            </a>

            <!-- Insurer Onboarding -->
            <a href="{{ route('register.organization', ['type' => 'insurance_company']) }}" class="onboarding-card">
                <div>
                    <div class="onboarding-card-header">
                        <div class="onboarding-card-icon">
                            <i data-lucide="heart-handshake"></i>
                        </div>
                        <h3>{{ __('onboarding.selector.cards.insurer_title') }}</h3>
                    </div>
                    <p>{{ __('onboarding.selector.cards.insurer_desc') }}</p>
                </div>
                <div class="onboarding-card-cta">
                    <span>{{ __('onboarding.selector.cards.insurer_cta') }}</span>
                    <i data-lucide="arrow-right"></i>
                </div>
            </a>

            <!-- Developer API Access -->
            <a href="{{ route('register.developer') }}" class="onboarding-card">
                <div>
                    <div class="onboarding-card-header">
                        <div class="onboarding-card-icon">
                            <i data-lucide="code-2"></i>
                        </div>
                        <h3>{{ __('onboarding.selector.cards.developer_title') }}</h3>
                    </div>
                    <p>{{ __('onboarding.selector.cards.developer_desc') }}</p>
                </div>
                <div class="onboarding-card-cta">
                    <span>{{ __('onboarding.selector.cards.developer_cta') }}</span>
                    <i data-lucide="arrow-right"></i>
                </div>
            </a>

            <!-- Public Health / Research Onboarding -->
            <a href="{{ route('register.organization', ['type' => 'public_health']) }}" class="onboarding-card">
                <div>
                    <div class="onboarding-card-header">
                        <div class="onboarding-card-icon">
                            <i data-lucide="globe"></i>
                        </div>
                        <h3>{{ __('onboarding.selector.cards.public_health_title') }}</h3>
                    </div>
                    <p>{{ __('onboarding.selector.cards.public_health_desc') }}</p>
                </div>
                <div class="onboarding-card-cta">
                    <span>{{ __('onboarding.selector.cards.public_health_cta') }}</span>
                    <i data-lucide="arrow-right"></i>
                </div>
            </a>
            
        </div>

        <div class="auth-footer-links" style="border-top: 1px solid var(--auth-border); padding-top: 1.5rem; margin-top: 0.5rem;">
            <p>{{ __('onboarding.selector.already_have') }} 
                <a href="{{ route('login') }}" style="font-weight: 800;">
                    {{ __('onboarding.selector.signin') }}
                </a>
            </p>
        </div>
    </div>
@endsection
