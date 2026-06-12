@extends('layouts.portal')

@section('title', $plan->name . ' — OpesCare Patient Portal')

@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Health Insurance')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div style="margin-bottom:var(--p-space-4);">
    <a href="{{ route('portals.patient.insurance') }}"
       style="font-size:.85rem;color:var(--p-primary,#1565C0);display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
        <i data-lucide="arrow-left" style="width:.85rem;height:.85rem;"></i>
        Back to Insurance Plans
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:var(--p-space-5);align-items:start;">

    {{-- ── Left: Plan details ─────────────────────────────────────────── --}}
    <div>

        {{-- Header banner --}}
        <div style="background:linear-gradient(135deg,#1565C0,#1044A0);border-radius:var(--p-radius-lg,12px);padding:var(--p-space-6);color:#fff;margin-bottom:var(--p-space-5);">
            <div style="font-size:.8rem;opacity:.75;margin-bottom:4px;">{{ $plan->provider?->name }}</div>
            <h1 style="font-size:1.3rem;font-weight:700;margin:0 0 var(--p-space-3);">{{ $plan->name }}</h1>
            @if($plan->plan_type)
            <span style="background:rgba(255,255,255,.2);border-radius:20px;padding:3px 10px;font-size:.72rem;font-weight:600;letter-spacing:.05em;">
                {{ strtoupper($plan->plan_type) }}
            </span>
            @endif
        </div>

        {{-- Pricing --}}
        <div class="panel" style="margin-bottom:var(--p-space-4);">
            <div class="panel-header">
                <h2 class="panel-title"><i data-lucide="receipt"></i> Pricing</h2>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:var(--p-space-3);">
                @if($plan->monthly_premium)
                <div style="background:var(--p-primary-50,#EFF6FF);border-radius:10px;padding:var(--p-space-4);">
                    <div style="font-size:.75rem;color:var(--p-text-muted);margin-bottom:4px;">Monthly Premium</div>
                    <div style="font-size:1.4rem;font-weight:800;color:var(--p-primary,#1565C0);">
                        XAF {{ number_format($plan->monthly_premium, 0) }}
                    </div>
                </div>
                @endif
                @if($plan->annual_premium)
                <div style="background:var(--p-bg-muted,#F9FAFB);border:1px solid var(--p-divider);border-radius:10px;padding:var(--p-space-4);">
                    <div style="font-size:.75rem;color:var(--p-text-muted);margin-bottom:4px;">Annual Premium</div>
                    <div style="font-size:1.4rem;font-weight:800;">
                        XAF {{ number_format($plan->annual_premium, 0) }}
                    </div>
                </div>
                @endif
                @if($plan->deductible)
                <div style="background:var(--p-bg-muted,#F9FAFB);border:1px solid var(--p-divider);border-radius:10px;padding:var(--p-space-4);">
                    <div style="font-size:.75rem;color:var(--p-text-muted);margin-bottom:4px;">Deductible</div>
                    <div style="font-size:1.4rem;font-weight:800;">
                        XAF {{ number_format($plan->deductible, 0) }}
                    </div>
                </div>
                @endif
                @if($plan->copay_percentage)
                <div style="background:var(--p-bg-muted,#F9FAFB);border:1px solid var(--p-divider);border-radius:10px;padding:var(--p-space-4);">
                    <div style="font-size:.75rem;color:var(--p-text-muted);margin-bottom:4px;">Co-pay</div>
                    <div style="font-size:1.4rem;font-weight:800;">
                        {{ number_format($plan->copay_percentage, 0) }}%
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Benefits --}}
        <div class="panel" style="margin-bottom:var(--p-space-4);">
            <div class="panel-header">
                <h2 class="panel-title"><i data-lucide="shield-check"></i> Benefits</h2>
            </div>
            <div style="display:flex;flex-direction:column;gap:var(--p-space-3);">
                <div style="display:flex;align-items:center;gap:var(--p-space-3);">
                    <i data-lucide="{{ $plan->cashless_available ? 'check-circle-2' : 'x-circle' }}"
                       style="width:1.1rem;height:1.1rem;color:{{ $plan->cashless_available ? '#10B981' : '#D1D5DB' }};flex-shrink:0;"></i>
                    <span style="font-size:.9rem;">Cashless Treatment</span>
                </div>
                <div style="display:flex;align-items:center;gap:var(--p-space-3);">
                    <i data-lucide="{{ $plan->requires_preauthorization ? 'check-circle-2' : 'x-circle' }}"
                       style="width:1.1rem;height:1.1rem;color:{{ $plan->requires_preauthorization ? '#F59E0B' : '#D1D5DB' }};flex-shrink:0;"></i>
                    <span style="font-size:.9rem;">Requires Pre-authorization</span>
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($plan->description)
        <div class="panel" style="margin-bottom:var(--p-space-4);">
            <div class="panel-header">
                <h2 class="panel-title"><i data-lucide="file-text"></i> About this Plan</h2>
            </div>
            <p style="font-size:.9rem;color:var(--p-text-muted);line-height:1.7;margin:0;">
                {{ $plan->description }}
            </p>
        </div>
        @endif

        {{-- Provider contact --}}
        @if($plan->provider && ($plan->provider->contact_phone || $plan->provider->contact_email))
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title"><i data-lucide="phone"></i> Provider Contact</h2>
            </div>
            <div style="display:flex;flex-direction:column;gap:var(--p-space-2);">
                @if($plan->provider->contact_phone)
                <div style="display:flex;align-items:center;gap:var(--p-space-2);font-size:.9rem;">
                    <i data-lucide="phone" style="width:.85rem;height:.85rem;color:var(--p-text-muted);"></i>
                    {{ $plan->provider->contact_phone }}
                </div>
                @endif
                @if($plan->provider->contact_email)
                <div style="display:flex;align-items:center;gap:var(--p-space-2);font-size:.9rem;">
                    <i data-lucide="mail" style="width:.85rem;height:.85rem;color:var(--p-text-muted);"></i>
                    <a href="mailto:{{ $plan->provider->contact_email }}"
                       style="color:var(--p-primary,#1565C0);text-decoration:none;">
                        {{ $plan->provider->contact_email }}
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right: Enroll form ──────────────────────────────────────────── --}}
    <div>
        <div class="panel" style="position:sticky;top:var(--p-space-6);">
            @if($alreadyEnrolled)
            <div style="text-align:center;padding:var(--p-space-4);">
                <i data-lucide="shield-check" style="width:3rem;height:3rem;color:#10B981;display:block;margin:0 auto var(--p-space-3);"></i>
                <div style="font-weight:700;font-size:.95rem;margin-bottom:var(--p-space-2);">Already Enrolled</div>
                <p style="font-size:.85rem;color:var(--p-text-muted);">
                    You already have an active policy for this plan.
                </p>
                <a href="{{ route('portals.patient.insurance') }}" class="btn btn-outline btn-sm">
                    View My Policies
                </a>
            </div>
            @else
            <div class="panel-header" style="border-bottom:1px solid var(--p-divider);padding-bottom:var(--p-space-3);margin-bottom:var(--p-space-4);">
                <h2 class="panel-title"><i data-lucide="shield-plus"></i> Enroll Now</h2>
            </div>

            {{-- Summary --}}
            <div style="background:var(--p-bg-muted,#F9FAFB);border-radius:var(--p-radius);padding:var(--p-space-3);margin-bottom:var(--p-space-4);font-size:.85rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="color:var(--p-text-muted);">Plan</span>
                    <span style="font-weight:600;">{{ $plan->name }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="color:var(--p-text-muted);">Provider</span>
                    <span style="font-weight:600;">{{ $plan->provider?->name ?? '—' }}</span>
                </div>
                @if($plan->monthly_premium)
                <div style="display:flex;justify-content:space-between;padding-top:6px;border-top:1px solid var(--p-divider);">
                    <span style="color:var(--p-text-muted);">Monthly</span>
                    <span style="font-weight:700;color:var(--p-primary,#1565C0);">
                        XAF {{ number_format($plan->monthly_premium, 0) }}
                    </span>
                </div>
                @endif
            </div>

            <form method="POST" action="{{ route('portals.patient.insurance.purchase', $plan->id) }}">
                @csrf

                <div style="margin-bottom:var(--p-space-4);">
                    <label style="font-size:.8rem;font-weight:600;color:var(--p-text-muted);display:block;margin-bottom:var(--p-space-2);">
                        Payment Method
                    </label>

                    @foreach([
                        ['mobile_money', 'Mobile Money', 'smartphone'],
                        ['card', 'Debit / Credit Card', 'credit-card'],
                        ['bank_transfer', 'Bank Transfer', 'landmark'],
                    ] as [$val, $label, $icon])
                    <label style="display:flex;align-items:center;gap:var(--p-space-3);padding:10px 12px;border:1px solid var(--p-divider);border-radius:10px;cursor:pointer;margin-bottom:6px;transition:border-color .15s,background .15s;"
                           onclick="this.closest('form').querySelectorAll('label[data-pm]').forEach(l=>l.style.cssText=l.dataset.off);this.style.cssText=this.dataset.on;"
                           data-pm
                           data-off="display:flex;align-items:center;gap:var(--p-space-3);padding:10px 12px;border:1px solid var(--p-divider);border-radius:10px;cursor:pointer;margin-bottom:6px;transition:border-color .15s,background .15s;"
                           data-on="display:flex;align-items:center;gap:var(--p-space-3);padding:10px 12px;border:1px solid #1565C0;border-radius:10px;cursor:pointer;margin-bottom:6px;background:#EFF6FF;transition:border-color .15s,background .15s;">
                        <input type="radio" name="payment_method" value="{{ $val }}"
                               {{ $val === 'mobile_money' ? 'checked' : '' }}
                               style="accent-color:#1565C0;">
                        <i data-lucide="{{ $icon }}" style="width:.9rem;height:.9rem;color:#9CA3AF;"></i>
                        <span style="font-size:.88rem;">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                    <i data-lucide="shield-plus" style="width:.9rem;height:.9rem;"></i>
                    Confirm Enrollment
                </button>

                <p style="font-size:.75rem;color:var(--p-text-muted);margin-top:var(--p-space-3);text-align:center;line-height:1.5;">
                    Your policy will be activated within 1–2 business days after verification.
                </p>
            </form>
            @endif
        </div>
    </div>

</div>

@endsection
