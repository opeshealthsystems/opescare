@extends('layouts.portal')
@section('title', 'Drug Interactions — CDSS')
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
    <h2><i data-lucide="git-merge"></i> Drug Interaction Rules</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="arrow-left"></i> Alerts
    </a>
</div>
<p class="page-subtitle mb-4">Bidirectional drug-drug interaction database</p>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Drug A</th>
                        <th>Drug B</th>
                        <th>Severity</th>
                        <th>Interaction</th>
                        <th>Management</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($interactions as $rule)
                        <tr>
                            <td data-label="Drug A">
                                <div class="td-strong">{{ $rule->drug_a_name }}</div>
                                <code class="mono td-muted">{{ $rule->drug_a_code }}</code>
                            </td>
                            <td data-label="Drug B">
                                <div class="td-strong">{{ $rule->drug_b_name }}</div>
                                <code class="mono td-muted">{{ $rule->drug_b_code }}</code>
                            </td>
                            <td data-label="Severity">
                                <span class="badge badge-{{ match($rule->severity) {
                                    'contraindicated' => 'danger',
                                    'major'           => 'danger',
                                    'moderate'        => 'warning',
                                    default           => 'info',
                                } }}">{{ ucfirst($rule->severity) }}</span>
                            </td>
                            <td data-label="Interaction">
                                {{ Str::limit($rule->interaction_description, 100) }}
                                @if($rule->clinical_effect)
                                    <div class="td-muted">{{ Str::limit($rule->clinical_effect, 60) }}</div>
                                @endif
                            </td>
                            <td data-label="Management" class="td-muted">
                                {{ $rule->management ? Str::limit($rule->management, 80) : '—' }}
                            </td>
                            <td data-label="Status">
                                <span class="badge badge-{{ $rule->is_active ? 'success' : 'neutral' }}">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i data-lucide="git-merge"></i></div>
                                    <p>No drug interaction rules loaded. Seed interactions to enable DDI alerts.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($interactions->hasPages())<div class="panel-body">{{ $interactions->links() }}</div>@endif
</div>

@endsection
