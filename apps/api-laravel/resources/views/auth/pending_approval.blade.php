@extends('layouts.auth')

@section('title', __('onboarding.pending.title'))

@section('content')
    <!-- PendingApprovalCard Reusable Component -->
    <div class="auth-card pending-status-card" style="max-width: 520px; padding: 2.5rem;">
        <div class="auth-title-group" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; background: #FFFBEB; color: var(--auth-warning); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                <i data-lucide="clock" style="width: 2rem; height: 2rem;"></i>
            </div>
            <h1 class="auth-headline" style="font-size: 1.55rem;">{{ __('onboarding.pending.title') }}</h1>
            <p class="auth-subheadline">{{ __('onboarding.pending.desc') }}</p>
        </div>

        <div style="background-color: var(--auth-bg); border-radius: 0.75rem; border: 1px solid var(--auth-border); padding: 1.5rem; margin-top: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--auth-primary); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">
                {{ __('onboarding.pending.card_header') }}
            </h3>

            <div class="pending-meta-row">
                <span class="pending-meta-label">{{ __('onboarding.pending.ref_number') }}</span>
                <span class="pending-meta-value" style="font-family: monospace; font-weight: 800;">{{ $ref_code }}</span>
            </div>

            <div class="pending-meta-row">
                <span class="pending-meta-label">Organization Name</span>
                <span class="pending-meta-value">{{ $org_name }}</span>
            </div>

            <div class="pending-meta-row">
                <span class="pending-meta-label">{{ __('onboarding.pending.submitted_date') }}</span>
                <span class="pending-meta-value">{{ $submitted_date }}</span>
            </div>

            <div class="pending-meta-row">
                <span class="pending-meta-label">{{ __('onboarding.pending.status_label') }}</span>
                <span class="badge-status badge-review">Under Review</span>
            </div>

            <div class="pending-meta-row" style="flex-direction: column; align-items: flex-start; gap: 0.5rem; border-bottom: none;">
                <span class="pending-meta-label">{{ __('onboarding.pending.admin_notes') }}</span>
                <div style="font-size: 0.8125rem; line-height: 1.4; color: var(--auth-text-secondary); background: white; border: 1px dashed var(--auth-border); border-radius: 0.5rem; padding: 0.75rem; width: 100%; font-weight: 500;">
                    Document audit check passed. Pending clinical facility registration authority verification. Expected completion within 24 hours.
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="{{ route('public.contact') }}" class="auth-btn auth-btn-primary">
                <i data-lucide="help-circle"></i>
                <span>{{ __('onboarding.pending.cta_support') }}</span>
            </a>
            
            <a href="{{ route('public.landing') }}" class="auth-btn auth-btn-secondary">
                <i data-lucide="home"></i>
                <span>{{ __('onboarding.common.back_to_home') }}</span>
            </a>
        </div>
    </div>
@endsection
