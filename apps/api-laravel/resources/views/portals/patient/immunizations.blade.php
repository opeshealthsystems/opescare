@extends('layouts.portal')

@section('title', 'My Immunizations — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Immunizations')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Immunizations</h1>
        <p class="page-subtitle">Vaccination history recorded by your healthcare providers.</p>
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
@elseif($immunizations->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="syringe"></i></div>
        <h3>No Immunizations on Record</h3>
        <p>No vaccinations have been recorded for your profile. Ask your healthcare provider to record your vaccination history.</p>
    </div>
</div>
@else

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="syringe"></i> Vaccination History ({{ $immunizations->count() }} record{{ $immunizations->count() !== 1 ? 's' : '' }})</h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="Vaccination history">
            <thead>
                <tr>
                    <th>Vaccine</th>
                    <th>Lot Number</th>
                    <th>Dose</th>
                    <th>Administered</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($immunizations as $imm)
                <tr>
                    <td data-label="Vaccine"><span class="td-strong">{{ $imm->vaccine_name }}</span></td>
                    <td data-label="Lot Number"><span class="td-mono">{{ $imm->lot_number ?? '—' }}</span></td>
                    <td data-label="Dose"><span class="td-muted">{{ $imm->dose_number ?? '—' }}</span></td>
                    <td data-label="Administered"><span class="td-muted">{{ $imm->administered_at?->format('d M Y') ?? '—' }}</span></td>
                    <td data-label="Status">
                        <span class="badge badge-success">{{ ucfirst($imm->status ?? 'completed') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif

@endsection
