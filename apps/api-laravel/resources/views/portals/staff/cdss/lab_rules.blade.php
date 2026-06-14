@extends('layouts.portal')
@section('title', 'Lab Alert Ranges — CDSS')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')

{{-- CDSS Disclaimer --}}
<div class="alert alert-warning mb-6">
    <i data-lucide="shield-alert"></i>
    <div>
        <strong>Clinical Decision Support:</strong>
        Clinical alerts are decision-support tools only. They do not replace professional clinical judgment.
    </div>
</div>

<div class="page-head">
    <h2><i data-lucide="test-tube"></i> Lab Alert Reference Ranges</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="arrow-left"></i> Alerts
    </a>
</div>
<p class="page-subtitle mb-4">Normal and critical thresholds for laboratory tests</p>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Test Code</th>
                        <th>Test Name</th>
                        <th>Unit</th>
                        <th>Critical Low</th>
                        <th>Normal Low</th>
                        <th>Normal High</th>
                        <th>Critical High</th>
                        <th>Filters</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labRules as $rule)
                        <tr>
                            <td data-label="Test Code"><code class="mono">{{ $rule->lab_test_code }}</code></td>
                            <td data-label="Test Name" class="td-strong">{{ $rule->lab_test_name }}</td>
                            <td data-label="Unit" class="td-muted">{{ $rule->unit ?? '—' }}</td>
                            <td data-label="Critical Low">
                                @if($rule->critical_low !== null)<span class="badge badge-danger">{{ $rule->critical_low }}</span>@else — @endif
                            </td>
                            <td data-label="Normal Low">
                                @if($rule->normal_low !== null)<span class="badge badge-warning">{{ $rule->normal_low }}</span>@else — @endif
                            </td>
                            <td data-label="Normal High">
                                @if($rule->normal_high !== null)<span class="badge badge-warning">{{ $rule->normal_high }}</span>@else — @endif
                            </td>
                            <td data-label="Critical High">
                                @if($rule->critical_high !== null)<span class="badge badge-danger">{{ $rule->critical_high }}</span>@else — @endif
                            </td>
                            <td data-label="Filters" class="td-muted">
                                @if($rule->gender_filter) {{ $rule->gender_filter === 'M' ? 'Male only' : 'Female only' }} @endif
                                @if($rule->age_min || $rule->age_max)
                                    <div>Age: {{ $rule->age_min ?? '0' }}–{{ $rule->age_max ?? '∞' }} yrs</div>
                                @endif
                                @if(!$rule->gender_filter && !$rule->age_min && !$rule->age_max)
                                    All patients
                                @endif
                            </td>
                            <td data-label="Status">
                                <span class="badge badge-{{ $rule->is_active ? 'success' : 'neutral' }}">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i data-lucide="test-tube"></i></div>
                                    <p>No lab alert ranges configured. Seed lab ranges to enable critical value alerts.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($labRules->hasPages())<div class="panel-body">{{ $labRules->links() }}</div>@endif
</div>

@endsection
