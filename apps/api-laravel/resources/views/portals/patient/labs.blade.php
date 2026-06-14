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
        <div class="empty-state-icon"><i data-lucide="alert-circle"></i></div>
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
    <div class="table-wrapper">
        <table class="data-table" aria-label="Lab results">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Result</th>
                    <th>Reference</th>
                    <th>Flag</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($labs as $lab)
                <tr>
                    <td data-label="Test"><span class="td-strong">{{ $lab->parameter_name }}</span></td>
                    <td data-label="Result">{{ $lab->value }} {{ $lab->unit }}</td>
                    <td data-label="Reference"><span class="td-muted">{{ $lab->reference_range ?? '—' }}</span></td>
                    <td data-label="Flag">
                        @if($lab->isAbnormal())
                            <span class="badge badge-danger">{{ $lab->flagLabel() }}</span>
                        @else
                            <span class="badge badge-success">Normal</span>
                        @endif
                    </td>
                    <td data-label="Date"><span class="td-muted">{{ $lab->resulted_at?->format('d M Y') ?? '—' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($labs, 'links') && $labs->hasPages())
    <div class="panel-body">
        {{ $labs->links() }}
    </div>
    @endif
</div>
@endif

@endsection
