@extends('layouts.portal')

@section('title', 'My Allergies — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Allergies')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Allergies</h1>
        <p class="page-subtitle">All known allergies and adverse reactions on your health record.</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@elseif($allergies->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
        <h3>No Allergies on Record</h3>
        <p>No known allergies have been recorded for your profile. If you have allergies, please inform your healthcare provider.</p>
    </div>
</div>
@else

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="zap"></i> Allergy List ({{ $allergies->count() }} record{{ $allergies->count() !== 1 ? 's' : '' }})</h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="Allergy list">
            <thead>
                <tr>
                    <th>Substance</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Recorded</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allergies as $allergy)
                @php
                    $sev = strtolower($allergy->severity ?? '');
                    $sevCls = match($sev) {
                        'life-threatening', 'severe', 'high' => 'badge-danger',
                        'moderate', 'medium'                 => 'badge-warning',
                        default                              => 'badge-neutral',
                    };
                    $isCritical = in_array($sev, ['life-threatening', 'severe', 'high']);
                @endphp
                <tr>
                    <td data-label="Substance">
                        <span class="cell-with-icon">
                            @if($isCritical)
                                <i data-lucide="alert-triangle"></i>
                            @endif
                            <span class="td-strong">{{ $allergy->substance }}</span>
                        </span>
                    </td>
                    <td data-label="Severity">
                        <span class="badge {{ $sevCls }}">{{ ucfirst($allergy->severity ?? 'unknown') }}</span>
                    </td>
                    <td data-label="Status">
                        <span class="badge {{ $allergy->status === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($allergy->status ?? 'active') }}</span>
                    </td>
                    <td data-label="Recorded">
                        <span class="td-muted">{{ $allergy->created_at?->format('d M Y') ?? '—' }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-warning mt-6">
    <i data-lucide="info"></i>
    <div>Allergy records are maintained by your healthcare providers. To add or update an allergy, please contact the facility that manages your record.</div>
</div>

@endif

@endsection
