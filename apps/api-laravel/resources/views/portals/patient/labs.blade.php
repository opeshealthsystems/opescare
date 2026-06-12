@extends('layouts.portal')

@section('title', 'Lab Results — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Lab Results')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Lab Results</h1>
        <p class="page-subtitle">View your laboratory test results from all facilities.</p>
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
@elseif($labs->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="flask-conical"></i></div>
        <h3>No Lab Results</h3>
        <p>You have no recorded lab results at this time.</p>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="flask-conical"></i> Lab Results</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--p-surface-2);font-size:0.8125rem;color:var(--p-text-muted);">
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Test</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Result</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Reference</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Flag</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">Date</th>
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
                                Normal
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
    @if(method_exists($labs, 'links') && $labs->hasPages())
    <div style="padding:var(--p-space-4);border-top:1px solid var(--p-border);">
        {{ $labs->links() }}
    </div>
    @endif
</div>
@endif

@endsection
