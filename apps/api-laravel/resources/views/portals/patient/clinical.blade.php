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
        <div class="empty-state-icon"><i data-lucide="alert-circle"></i></div>
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
<div class="panel mb-6">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="stethoscope"></i> Active & Chronic Conditions ({{ $active->count() }})</h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="Active and chronic conditions">
            <thead>
                <tr>
                    <th>Condition</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Recorded</th>
                </tr>
            </thead>
            <tbody>
                @foreach($active as $condition)
                <tr>
                    <td data-label="Condition"><span class="td-strong">{{ $condition->display_name ?? $condition->code ?? '—' }}</span></td>
                    <td data-label="Code"><span class="td-mono">{{ $condition->code ?? '—' }}</span></td>
                    <td data-label="Status">
                        <span class="badge {{ $condition->status === 'chronic' ? 'badge-teal' : 'badge-primary' }}">{{ ucfirst($condition->status) }}</span>
                    </td>
                    <td data-label="Recorded"><span class="td-muted">{{ $condition->created_at?->format('d M Y') ?? '—' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($resolved->isNotEmpty())
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="check-circle"></i> Resolved Conditions ({{ $resolved->count() }})</h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="Resolved conditions">
            <thead>
                <tr>
                    <th>Condition</th>
                    <th>Code</th>
                    <th>Recorded</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resolved as $condition)
                <tr>
                    <td data-label="Condition"><span class="td-muted">{{ $condition->display_name ?? $condition->code ?? '—' }}</span></td>
                    <td data-label="Code"><span class="td-mono">{{ $condition->code ?? '—' }}</span></td>
                    <td data-label="Recorded"><span class="td-muted">{{ $condition->created_at?->format('d M Y') ?? '—' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endif

@endsection
