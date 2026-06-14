@extends('layouts.portal')
@section('title', 'Clinical Rules — CDSS')
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
    <h2><i data-lucide="list-checks"></i> Clinical Rules</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="arrow-left"></i> Alerts
    </a>
</div>
<p class="page-subtitle mb-4">Configured decision-support rules for this facility</p>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rule Code</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Severity</th>
                        <th>Overridable</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td data-label="Rule Code"><code class="mono">{{ $rule->rule_code }}</code></td>
                            <td data-label="Type">
                                <span class="badge badge-info">{{ str_replace('_',' ', $rule->rule_type) }}</span>
                            </td>
                            <td data-label="Name">
                                <div class="td-strong">{{ $rule->name }}</div>
                                @if($rule->description)
                                    <div class="td-muted">{{ Str::limit($rule->description, 60) }}</div>
                                @endif
                            </td>
                            <td data-label="Severity">
                                <span class="badge badge-{{ match($rule->severity) {
                                    'critical' => 'danger',
                                    'warning'  => 'warning',
                                    default    => 'info',
                                } }}">{{ ucfirst($rule->severity) }}</span>
                            </td>
                            <td data-label="Overridable">
                                @if($rule->is_overridable)
                                    <span class="badge badge-success"><i data-lucide="check"></i> Yes</span>
                                @else
                                    <span class="badge badge-danger"><i data-lucide="x"></i> No</span>
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
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i data-lucide="list-checks"></i></div>
                                    <p>No clinical rules configured. Rules are seeded from system defaults.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($rules->hasPages())<div class="panel-body">{{ $rules->links() }}</div>@endif
</div>

@endsection
