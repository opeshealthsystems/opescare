@extends('layouts.portal')

@section('title', __('public.portal.immunizations_title', [], app()->getLocale()) ?: 'My Immunizations')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', __('public.portal.immunizations_breadcrumb', [], app()->getLocale()) ?: 'Immunizations')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.immunizations_title', [], $l) ?: 'My Immunizations' }}</h1>
        <p class="page-subtitle">{{ __('public.portal.immunizations_subtitle', [], $l) ?: 'Vaccination history recorded by your healthcare providers.' }}</p>
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
@elseif($immunizations->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="syringe"></i></div>
        <h3>{{ __('public.portal.no_immunizations_title', [], $l) ?: 'No Immunizations on Record' }}</h3>
        <p>{{ __('public.portal.no_immunizations_desc', [], $l) ?: 'No vaccinations have been recorded for your profile. Ask your healthcare provider to record your vaccination history.' }}</p>
    </div>
</div>
@else

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="syringe"></i> {{ __('public.portal.panel_vaccination_history', [], $l) ?: 'Vaccination History' }} ({{ $immunizations->count() }} {{ $immunizations->count() !== 1 ? __('public.portal.lbl_records', [], $l) ?: 'records' : __('public.portal.lbl_record', [], $l) ?: 'record' }})</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--p-surface-2);border-bottom:1px solid var(--p-border);">
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_vaccine', [], $l) ?: 'Vaccine' }}</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_lot_number', [], $l) ?: 'Lot Number' }}</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_dose', [], $l) ?: 'Dose' }}</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_administered', [], $l) ?: 'Administered' }}</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">{{ __('public.portal.col_status', [], $l) ?: 'Status' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($immunizations as $imm)
                    <tr style="border-bottom:1px solid var(--p-border);">
                        <td style="padding:var(--p-space-3) var(--p-space-4);font-weight:600;color:var(--p-text);">
                            {{ $imm->vaccine_name }}
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-size:0.8125rem;font-family:monospace;">
                            {{ $imm->lot_number ?? '—' }}
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">
                            {{ $imm->dose_number ?? '—' }}
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-size:0.8125rem;">
                            {{ $imm->administered_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);">
                            <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.75rem;font-weight:600;background:#16A34A20;color:#16A34A;">
                                {{ ucfirst($imm->status ?? 'completed') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endif

@endsection
