@extends('layouts.portal')
@section('title', 'Clinical Alert History — ' . ($patient->full_name ?? $patient->name ?? 'Patient'))
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
    <h2>Alert History</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="arrow-left"></i> All Alerts
    </a>
</div>
<p class="page-subtitle mb-4">
    {{ $patient->full_name ?? $patient->name ?? '—' }}
    @if(isset($patient->health_id))
        · <code class="mono">{{ $patient->health_id }}</code>
    @endif
</p>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Severity</th>
                        <th>Type</th>
                        <th>Alert Message</th>
                        <th>Triggered</th>
                        <th>Status</th>
                        <th>Override Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                        <tr>
                            <td data-label="Severity">
                                <span class="badge badge-{{ $alert->severityColor() }}">{{ ucfirst($alert->severity) }}</span>
                            </td>
                            <td data-label="Type">
                                <span class="badge badge-neutral">{{ str_replace('_',' ', $alert->alert_type) }}</span>
                            </td>
                            <td data-label="Alert Message">
                                {{ Str::limit($alert->alert_message, 120) }}
                            </td>
                            <td data-label="Triggered" class="td-muted">
                                {{ $alert->triggered_at->format('d M Y H:i') }}
                            </td>
                            <td data-label="Status">
                                <span class="badge badge-{{ match($alert->status) {
                                    'active'       => 'danger',
                                    'acknowledged' => 'warning',
                                    'overridden'   => 'success',
                                    'dismissed'    => 'neutral',
                                    default        => 'neutral',
                                } }}">{{ $alert->status }}</span>
                            </td>
                            <td data-label="Override Reason" class="td-muted">
                                @if($alert->latestOverride)
                                    <div>{{ Str::limit($alert->latestOverride->override_reason, 60) }}</div>
                                    <div>by {{ $alert->latestOverride->overridden_by }}</div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i data-lucide="shield-check"></i></div>
                                    <p>No clinical alerts found for this patient.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
