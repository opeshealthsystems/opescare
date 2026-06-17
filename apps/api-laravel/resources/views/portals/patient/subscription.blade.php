@extends('layouts.portal')

@section('title', __('public.pat_sub_title') . ' — OpesCare')

@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', __('public.pat_sub_title'))

@section('content')
<div class="portal-section">
    <header class="section-head">
        <h1>{{ __('public.pat_sub_title') }}</h1>
        <p class="muted">{{ __('public.pat_sub_subtitle') }}</p>
    </header>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div class="alert alert-info" role="status">{{ session('info') }}</div>
    @endif

    {{-- Current plan summary --}}
    <div class="card current-plan">
        <div class="current-plan__main">
            <span class="muted">{{ __('public.pat_sub_current_plan') }}</span>
            <h2>{{ $currentPlan?->name ?? __('public.pat_sub_free_label') }}</h2>
            @if ($active)
                <span class="badge badge-{{ $active->statusColor() }}">{{ ucfirst($active->status) }}</span>
            @endif
        </div>
        <div class="current-plan__meta">
            @if ($active && !$active->auto_renew && $active->current_period_end)
                <p class="muted">{{ __('public.pat_sub_autorenew_off') }} {{ $active->current_period_end->isoFormat('LL') }}</p>
            @elseif ($active && $active->current_period_end)
                <p class="muted">{{ __('public.pat_sub_renews') }} {{ $active->current_period_end->isoFormat('LL') }}</p>
            @endif

            @if ($active && $active->auto_renew && !($currentPlan?->isFree()))
                <form method="POST" action="{{ route('portals.patient.subscription.cancel') }}"
                      onsubmit="return confirm('{{ __('public.pat_sub_cancel_confirm') }}');">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-danger">{{ __('public.pat_sub_cancel') }}</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Plan catalog --}}
    <h3 class="section-subhead">{{ __('public.pat_sub_available_plans') }}</h3>
    <div class="plan-grid">
        @foreach ($plans as $plan)
            @php $isCurrent = $currentPlan && $currentPlan->id === $plan->id; @endphp
            <div class="card plan-card {{ $isCurrent ? 'plan-card--current' : '' }}">
                <h4>{{ $plan->name }}</h4>
                <p class="plan-price">
                    @if ($plan->isFree())
                        <strong>{{ __('public.pat_sub_free_label') }}</strong>
                    @else
                        <strong>{{ $plan->priceFormatted() }}</strong><span class="muted">{{ __('public.pat_sub_monthly') }}</span>
                        @if ($plan->annualPriceFormatted())
                            <br><span class="muted small">{{ $plan->annualPriceFormatted() }}{{ __('public.pat_sub_annual') }}</span>
                        @endif
                    @endif
                </p>
                @if ($plan->description)
                    <p class="muted small">{{ $plan->description }}</p>
                @endif

                <ul class="feature-list">
                    @foreach ($plan->planFeatures as $feature)
                        <li>{{ $feature->feature_label }}</li>
                    @endforeach
                </ul>

                @if ($isCurrent)
                    <span class="badge badge-info">{{ __('public.pat_sub_current') }}</span>
                @else
                    <form method="POST" action="{{ route('portals.patient.subscription.subscribe') }}" class="plan-actions">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        @if ($plan->isFree())
                            <input type="hidden" name="interval" value="monthly">
                            <button type="submit" class="btn btn-secondary">{{ __('public.pat_sub_choose') }}</button>
                        @else
                            <label class="sr-only" for="interval-{{ $plan->id }}">{{ __('public.pat_sub_choose_cadence') }}</label>
                            <select id="interval-{{ $plan->id }}" name="interval" class="form-select">
                                <option value="monthly">{{ __('public.pat_sub_monthly_opt') }}</option>
                                @if ($plan->annual_price_kobo)
                                    <option value="annual">{{ __('public.pat_sub_annual_opt') }}</option>
                                @endif
                            </select>
                            <button type="submit" class="btn btn-primary">{{ __('public.pat_sub_choose') }}</button>
                        @endif
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Invoices --}}
    <h3 class="section-subhead">{{ __('public.pat_sub_invoices') }}</h3>
    @if ($invoices->isEmpty())
        <p class="muted">{{ __('public.pat_sub_no_invoices') }}</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('public.pat_sub_invoice_number') }}</th>
                    <th>{{ __('public.pat_sub_invoice_date') }}</th>
                    <th>{{ __('public.pat_sub_invoice_amount') }}</th>
                    <th>{{ __('public.pat_sub_invoice_status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->invoice_date?->isoFormat('LL') }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($invoice->total_kobo / 100, 0) }}</td>
                        <td><span class="badge">{{ ucfirst($invoice->status) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
