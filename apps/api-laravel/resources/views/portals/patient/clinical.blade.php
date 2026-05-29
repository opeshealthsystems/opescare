@extends('layouts.portal')

@section('title', 'My Conditions — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Conditions')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Conditions</h1>
        <p class="page-subtitle">Diagnoses and clinical conditions recorded by your healthcare providers.</p>
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
@elseif($conditions->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
        <h3>No Conditions on Record</h3>
        <p>No diagnoses or clinical conditions have been recorded for your profile.</p>
    </div>
</div>
@else

@php
    $active  = $conditions->whereIn('status', ['active', 'chronic']);
    $resolved = $conditions->where('status', 'resolved');
@endphp

@if($active->isNotEmpty())
<div class="panel" style="margin-bottom:var(--p-space-5);">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="stethoscope"></i> Active & Chronic Conditions ({{ $active->count() }})</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--p-surface-2);border-bottom:1px solid var(--p-border);">
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">Condition</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">Code</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">Status</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">Recorded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($active as $condition)
                    <tr style="border-bottom:1px solid var(--p-border);">
                        <td style="padding:var(--p-space-3) var(--p-space-4);font-weight:600;color:var(--p-text);">
                            {{ $condition->display_name ?? $condition->code ?? '—' }}
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-size:0.8125rem;font-family:monospace;">
                            {{ $condition->code ?? '—' }}
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);">
                            <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.75rem;font-weight:700;background:{{ $condition->status === 'chronic' ? '#7C3AED20' : '#2563EB20' }};color:{{ $condition->status === 'chronic' ? '#7C3AED' : '#2563EB' }};">
                                {{ ucfirst($condition->status) }}
                            </span>
                        </td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-size:0.8125rem;">
                            {{ $condition->created_at?->format('d M Y') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($resolved->isNotEmpty())
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title" style="color:var(--p-text-muted);"><i data-lucide="check-circle"></i> Resolved Conditions ({{ $resolved->count() }})</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--p-surface-2);border-bottom:1px solid var(--p-border);">
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">Condition</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">Code</th>
                        <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;font-size:0.75rem;font-weight:700;color:var(--p-text-muted);text-transform:uppercase;letter-spacing:.04em;">Recorded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resolved as $condition)
                    <tr style="border-bottom:1px solid var(--p-border);opacity:0.7;">
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ $condition->display_name ?? $condition->code ?? '—' }}</td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-size:0.8125rem;font-family:monospace;">{{ $condition->code ?? '—' }}</td>
                        <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-size:0.8125rem;">{{ $condition->created_at?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endif

@endsection
