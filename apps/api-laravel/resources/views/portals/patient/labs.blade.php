@extends('layouts.portal')

@section('title', __('public.portal.labs_title', [], app()->getLocale()) ?: 'Lab Results')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', __('public.portal.labs_breadcrumb', [], app()->getLocale()) ?: 'Lab Results')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.labs_title', [], $l) ?: 'My Lab Results' }}</h1>
        <p class="page-subtitle">{{ __('public.portal.labs_subtitle', [], $l) ?: 'View your laboratory test results from all facilities.' }}</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>{{ __('public.portal.no_profile_found_title', [], $l) ?: 'No Patient Profile Found' }}</h3>
        <p>{{ __('public.portal.no_profile_found_desc', [], $l) ?: 'Your patient profile could not be loaded. Please contact support.' }}</p>
    </div>
</div>
@elseif($labs->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="flask-conical"></i></div>
        <h3>{{ __('public.portal.no_labs_title', [], $l) ?: 'No Lab Results' }}</h3>
        <p>{{ __('public.portal.no_labs_desc', [], $l) ?: 'You have no recorded lab results at this time.' }}</p>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="flask-conical"></i> {{ __('public.portal.panel_lab_results', [], $l) ?: 'Lab Results' }}</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--p-surface-2);font-size:0.8125rem;color:var(--p-text-muted);">
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_test', [], $l) ?: 'Test' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_result', [], $l) ?: 'Result' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_reference', [], $l) ?: 'Reference' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_flag', [], $l) ?: 'Flag' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_date', [], $l) ?: 'Date' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($labs as $lab)
                <tr style="border-top:1px solid var(--p-border);font-size:0.875rem;">
                    <td style="padding:var(--p-space-3) var(--p-space-4);font-weight:600;">{{ $lab->parameter_name }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);">{{ $lab->value }} {{ $lab->unit }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ $lab->reference_range ?? '—' }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);">
                        @if($lab->isAbnormal())
                            <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#FEE2E2;color:#DC2626;">
                                {{ $lab->flagLabel() }}
                            </span>
                        @else
                            <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#D1FAE5;color:#059669;">
                                {{ __('public.portal.flag_normal', [], $l) ?: 'Normal' }}
                            </span>
                        @endif
                    </td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">
                        {{ $lab->resulted_at?->format('d M Y') ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
