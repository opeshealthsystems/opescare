@extends('layouts.auth')

@section('title', __('onboarding.facility_selector.title'))

@section('content')
    <!-- FacilitySelectorCard Reusable Component -->
    <div class="auth-card" style="max-width: 550px; padding: 2.5rem;">
        <div class="auth-title-group" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; background: var(--auth-teal-light); color: var(--auth-teal); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                <i data-lucide="building-2" style="width: 2rem; height: 2rem;"></i>
            </div>
            <h1 class="auth-headline" style="font-size: 1.6rem;">{{ __('onboarding.facility_selector.title') }}</h1>
            <p class="auth-subheadline">{{ __('onboarding.facility_selector.subtitle') }}</p>
        </div>

        @if(session('error'))
            <div class="auth-alert auth-alert-danger" style="margin-top: 1rem;">
                <i data-lucide="triangle-alert" style="width: 1.25rem; height: 1.25rem;"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form action="{{ route('select-facility.submit') }}" method="POST" class="auth-form" style="margin-top: 1rem;" id="facility-form">
            @csrf
            
            <input type="hidden" name="facility" id="selected-facility" value="">

            <div class="facility-select-grid">

                @foreach($facilities as $index => $facility)
                    @php
                        $cardId    = 'card-fac-' . $index;
                        $isSuspended = ($facility['status'] ?? '') === 'suspended';
                        $facilityVal = $isSuspended ? 'suspended' : $facility['id'];
                    @endphp
                    <div class="facility-select-card{{ $isSuspended ? ' disabled' : '' }}"
                         id="{{ $cardId }}"
                         onclick="selectFacility('{{ $facilityVal }}', '{{ $cardId }}')">
                        <div class="facility-select-info">
                            <h4>{{ $facility['name'] }}</h4>
                            <span style="display: block; margin-bottom: 0.15rem;">{{ $facility['branch'] ?? '' }}</span>
                            <span style="color: {{ $isSuspended ? 'var(--auth-text-muted)' : 'var(--auth-primary)' }};">
                                {{ __('onboarding.facility_selector.role_label') }}: {{ $facility['role'] }}
                            </span>
                        </div>
                        @if($isSuspended)
                            <span class="facility-select-badge suspended" style="background-color: #FEE2E2; color: var(--auth-danger);">{{ __('onboarding.facility_selector.status_suspended') }}</span>
                        @else
                            <span class="facility-select-badge active">{{ __('onboarding.facility_selector.status_active') }}</span>
                        @endif
                    </div>
                @endforeach

            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-btn auth-btn-primary" id="submit-btn" disabled style="opacity: 0.6; cursor: not-allowed;">
                <i data-lucide="shield-check"></i>
                <span>{{ __('onboarding.facility_selector.cta_btn') }}</span>
            </button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        function selectFacility(facilityVal, cardId) {
            // Set hidden field
            document.getElementById('selected-facility').value = facilityVal;

            // Remove selected classes from all active cards
            const cards = document.querySelectorAll('.facility-select-card');
            cards.forEach(card => {
                card.style.borderColor = 'var(--auth-border)';
                card.style.backgroundColor = 'white';
            });

            // Mark this card selected
            const selectedCard = document.getElementById(cardId);
            selectedCard.style.borderColor = 'var(--auth-primary)';
            selectedCard.style.backgroundColor = 'var(--auth-primary-light)';

            // Enable submit button
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.removeAttribute('disabled');
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
    </script>
@endsection
