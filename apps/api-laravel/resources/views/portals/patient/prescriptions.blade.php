@extends('layouts.portal')

@section('title', 'Prescriptions — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Prescriptions')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Prescriptions</h1>
        <p class="page-subtitle">All medications prescribed to you across your care history.</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@elseif($prescriptions->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="pill"></i></div>
        <h3>No Prescriptions</h3>
        <p>You have no recorded prescriptions at this time.</p>
    </div>
</div>
@else
@foreach($prescriptions as $rx)
<div class="panel" style="margin-bottom:var(--p-space-4);">
    <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2 class="panel-title" style="font-size:0.9375rem;">
                <i data-lucide="pill"></i>
                Prescription — {{ $rx->prescribed_at?->format('d M Y') ?? 'Unknown date' }}
            </h2>
            @if($rx->facility)
            <p style="font-size:0.8125rem;color:var(--p-text-muted);margin-top:2px;">{{ $rx->facility->name }}</p>
            @endif
        </div>
        @php
            $bgColor = match($rx->statusColor()) {
                'success' => '#D1FAE5', 'info' => '#DBEAFE', default => 'var(--p-surface-2)'
            };
            $textColor = match($rx->statusColor()) {
                'success' => '#059669', 'info' => '#2563EB', default => 'var(--p-text-muted)'
            };
        @endphp
        <span style="padding:3px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:{{ $bgColor }};color:{{ $textColor }};">
            {{ ucfirst($rx->status) }}
        </span>
    </div>
    @if($rx->items->isNotEmpty())
    <div class="panel-body" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--p-surface-2);font-size:0.8125rem;color:var(--p-text-muted);">
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Medication</th>
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Dose</th>
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Frequency</th>
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Duration</th>
                    <th style="padding:var(--p-space-2) var(--p-space-4);text-align:left;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rx->items as $item)
                <tr style="border-top:1px solid var(--p-border);font-size:0.875rem;">
                    <td style="padding:var(--p-space-2) var(--p-space-4);font-weight:600;">{{ $item->drug_name }}</td>
                    <td style="padding:var(--p-space-2) var(--p-space-4);">{{ $item->dose }} ({{ $item->route }})</td>
                    <td style="padding:var(--p-space-2) var(--p-space-4);">{{ $item->frequency }}</td>
                    <td style="padding:var(--p-space-2) var(--p-space-4);">{{ $item->duration_days ? $item->duration_days . ' days' : '—' }}</td>
                    <td style="padding:var(--p-space-2) var(--p-space-4);">
                        <span style="font-size:0.75rem;color:{{ $item->isDispensed() ? '#059669' : 'var(--p-text-muted)' }};">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endforeach
@endif

@endsection
