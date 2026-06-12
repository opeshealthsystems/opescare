@extends('layouts.portal')

@section('title', 'Health Insurance — OpesCare Patient Portal')

@section('breadcrumb_home', 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Health Insurance')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-success" style="margin-bottom:var(--p-space-5);padding:var(--p-space-4);background:#D1FAE5;border:1px solid #6EE7B7;border-radius:var(--p-radius);color:#065F46;display:flex;align-items:center;gap:var(--p-space-3);">
    <i data-lucide="check-circle-2" style="width:1.1rem;height:1.1rem;flex-shrink:0;"></i>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('warning'))
<div class="alert alert-warning" style="margin-bottom:var(--p-space-5);padding:var(--p-space-4);background:#FEF3C7;border:1px solid #FCD34D;border-radius:var(--p-radius);color:#92400E;display:flex;align-items:center;gap:var(--p-space-3);">
    <i data-lucide="alert-triangle" style="width:1.1rem;height:1.1rem;flex-shrink:0;"></i>
    <span>{{ session('warning') }}</span>
</div>
@endif

<div class="page-header">
    <div>
        <h1 class="page-title">Health Insurance</h1>
        <p class="page-subtitle">Browse and purchase health insurance plans from top providers.</p>
    </div>
</div>

{{-- ── My Policies ──────────────────────────────────────────────────────────── --}}
@if($myPolicies->isNotEmpty())
<div class="panel" style="margin-bottom:var(--p-space-6);">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="shield-check"></i> My Active Policies</h2>
        <span class="badge badge-primary">{{ $myPolicies->count() }}</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="My insurance policies">
            <thead>
                <tr>
                    <th>Provider</th>
                    <th>Plan</th>
                    <th>Policy Number</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($myPolicies as $policy)
                <tr>
                    <td data-label="Provider">
                        <span class="td-strong">{{ $policy->plan?->provider?->name ?? '—' }}</span>
                    </td>
                    <td data-label="Plan">{{ $policy->plan?->name ?? '—' }}</td>
                    <td data-label="Policy Number">
                        <code style="font-size:.8rem;">{{ $policy->policy_number }}</code>
                    </td>
                    <td data-label="Valid Until">
                        {{ $policy->expiry_date ? $policy->expiry_date->format('d M Y') : '—' }}
                    </td>
                    <td data-label="Status">
                        @php $s = $policy->status; @endphp
                        <span class="badge {{ $s === 'active' ? 'badge-success' : ($s === 'pending' ? 'badge-warning' : 'badge-neutral') }}">
                            {{ ucfirst($s) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Marketplace ───────────────────────────────────────────────────────────── --}}
<h2 style="font-size:1.1rem;font-weight:700;margin-bottom:var(--p-space-4);">Available Plans</h2>

@if($providers->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="shield-off"></i></div>
        <h3>No Plans Available</h3>
        <p>There are no purchasable insurance plans listed at this time. Please check back later.</p>
    </div>
</div>
@else

@foreach($providers as $provider)
<div class="panel" style="margin-bottom:var(--p-space-5);">
    {{-- Provider header --}}
    <div class="panel-header" style="border-bottom:1px solid var(--p-divider);padding-bottom:var(--p-space-4);margin-bottom:var(--p-space-4);">
        <div style="display:flex;align-items:center;gap:var(--p-space-3);">
            <div style="width:40px;height:40px;border-radius:10px;background:var(--p-primary-50,#EFF6FF);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @if($provider->logo_url)
                    <img src="{{ $provider->logo_url }}" alt="{{ $provider->name }}"
                         style="width:28px;height:28px;object-fit:contain;border-radius:6px;">
                @else
                    <i data-lucide="building-2" style="width:1.1rem;height:1.1rem;color:var(--p-primary,#1565C0);"></i>
                @endif
            </div>
            <div>
                <div style="font-weight:700;font-size:.95rem;">{{ $provider->name }}</div>
                @if($provider->contact_phone)
                <div style="font-size:.78rem;color:var(--p-text-muted);">
                    <i data-lucide="phone" style="width:.7rem;height:.7rem;display:inline;vertical-align:middle;margin-right:3px;"></i>
                    {{ $provider->contact_phone }}
                </div>
                @endif
            </div>
        </div>
        <span class="badge badge-neutral">{{ $provider->activePlans->count() }} {{ Str::plural('plan', $provider->activePlans->count()) }}</span>
    </div>

    {{-- Plan cards grid --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:var(--p-space-4);">
        @foreach($provider->activePlans as $plan)
        <div style="border:1px solid var(--p-divider);border-radius:var(--p-radius);padding:var(--p-space-4);display:flex;flex-direction:column;gap:var(--p-space-3);">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--p-space-2);">
                <div style="font-weight:700;font-size:.9rem;line-height:1.3;">{{ $plan->name }}</div>
                @if($plan->plan_type)
                <span class="badge badge-info" style="font-size:.7rem;flex-shrink:0;">{{ strtoupper($plan->plan_type) }}</span>
                @endif
            </div>

            @if($plan->description)
            <p style="font-size:.8rem;color:var(--p-text-muted);margin:0;line-height:1.5;">
                {{ Str::limit($plan->description, 100) }}
            </p>
            @endif

            {{-- Pricing --}}
            <div style="display:flex;gap:var(--p-space-2);flex-wrap:wrap;">
                @if($plan->monthly_premium)
                <div style="background:var(--p-primary-50,#EFF6FF);border-radius:8px;padding:6px 10px;flex:1;min-width:110px;">
                    <div style="font-size:.7rem;color:var(--p-text-muted);">Monthly</div>
                    <div style="font-weight:700;font-size:.95rem;color:var(--p-primary,#1565C0);">
                        XAF {{ number_format($plan->monthly_premium, 0) }}
                    </div>
                </div>
                @endif
                @if($plan->annual_premium)
                <div style="background:var(--p-bg-muted,#F9FAFB);border-radius:8px;padding:6px 10px;flex:1;min-width:110px;">
                    <div style="font-size:.7rem;color:var(--p-text-muted);">Annual</div>
                    <div style="font-weight:700;font-size:.95rem;">
                        XAF {{ number_format($plan->annual_premium, 0) }}
                    </div>
                </div>
                @endif
            </div>

            {{-- Quick benefits --}}
            <div style="display:flex;gap:var(--p-space-3);font-size:.78rem;flex-wrap:wrap;">
                @if($plan->cashless_available)
                <span style="display:flex;align-items:center;gap:4px;color:#065F46;">
                    <i data-lucide="check" style="width:.75rem;height:.75rem;"></i> Cashless
                </span>
                @endif
                @if($plan->copay_percentage)
                <span style="display:flex;align-items:center;gap:4px;color:var(--p-text-muted);">
                    <i data-lucide="percent" style="width:.75rem;height:.75rem;"></i>
                    {{ number_format($plan->copay_percentage, 0) }}% co-pay
                </span>
                @endif
            </div>

            <a href="{{ route('portals.patient.insurance.plan', $plan->id) }}"
               class="btn btn-primary btn-sm" style="margin-top:auto;text-align:center;">
                <i data-lucide="info" style="width:.85rem;height:.85rem;"></i>
                View &amp; Enroll
            </a>
        </div>
        @endforeach
    </div>
</div>
@endforeach

@endif

@endsection
