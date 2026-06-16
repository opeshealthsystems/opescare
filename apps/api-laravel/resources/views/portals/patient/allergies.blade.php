@extends('layouts.portal')

@section('title', __('public.portal.allergies_title', [], app()->getLocale()) ?: 'My Allergies')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', __('public.portal.allergies_breadcrumb', [], app()->getLocale()) ?: 'Allergies')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.allergies_title', [], $l) ?: 'My Allergies' }}</h1>
        <p class="page-subtitle">{{ __('public.portal.allergies_subtitle', [], $l) ?: 'All known allergies and adverse reactions on your health record.' }}</p>
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
@elseif($allergies->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
        <h3>{{ __('public.portal.no_allergies_title', [], $l) ?: 'No Allergies on Record' }}</h3>
        <p>{{ __('public.portal.no_allergies_desc', [], $l) ?: 'No known allergies have been recorded for your profile. If you have allergies, please inform your healthcare provider.' }}</p>
    </div>
</div>
@else

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="zap"></i> {{ __('public.portal.allergy_list_panel', [], $l) ?: 'Allergy List' }} ({{ $allergies->count() }})</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--p-surface-2);border-bottom:1px solid var(--p-border);">
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_substance', [], $l) ?: 'Substance' }}</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_severity', [], $l) ?: 'Severity' }}</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_status', [], $l) ?: 'Status' }}</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_recorded', [], $l) ?: 'Recorded' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allergies as $allergy)
                    @php
                        $severityColor = match(strtolower($allergy->severity ?? '')) {
                            'life-threatening', 'severe', 'high' => '#DC2626',
                            'moderate', 'medium'                 => '#D97706',
                            default                              => '#6B7280',
                        };
                    @endphp
                    <tr style="border-bottom:1px solid var(--p-border);">
                        <td style="padding:var(--p-space-3) var(--p-space-4);font-weight:600;color:var(--p-text);">
                            @if(in_array(strtolower($allergy->severity ?? ''), ['life-threatening', 'severe', 'high']))
                                <i data-lucide="alert-triangle" style="width:0.875rem;height:0.875rem;color:#DC2626;vertical-align:middle;margin-right:4px;"></i>
                            @endif
                            {{ $allergy->substance }}
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);">
                            <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.75rem;font-weight:700;background:{{ $severityColor }}20;color:{{ $severityColor }};">
                                {{ ucfirst($allergy->severity ?? 'unknown') }}
                            </span>
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);">
                            <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.75rem;font-weight:600;background:{{ $allergy->status === 'active' ? '#16A34A20' : '#6B728020' }};color:{{ $allergy->status === 'active' ? '#16A34A' : '#6B7280' }};">
                                {{ ucfirst($allergy->status ?? 'active') }}
                            </span>
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-size:0.8125rem;">
                            {{ $allergy->created_at?->format('d M Y') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-warning" style="margin-top:var(--p-space-5);">
    <i data-lucide="info"></i>
    <div style="font-size:0.8125rem;">{{ __('public.portal.allergy_readonly_hint', [], $l) ?: 'Allergy records are maintained by your healthcare providers. To add or update an allergy, please contact the facility that manages your record.' }}</div>
</div>

@endif

@endsection
