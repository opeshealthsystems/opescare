@extends('layouts.portal')
@section('title', 'Clinical Alert History — ' . ($patient->full_name ?? $patient->name ?? 'Patient'))
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    {{-- CDSS Disclaimer --}}
    <div style="background:#fffbeb;border:1px solid #d97706;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
        <i data-lucide="shield-alert" style="width:18px;height:18px;flex-shrink:0;color:#d97706;"></i>
        <p style="margin:0;font-size:0.82rem;color:#92400e;font-weight:500;">
            <strong>Clinical Decision Support:</strong>
            Clinical alerts are decision-support tools only. They do not replace professional clinical judgment.
        </p>
    </div>

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Alert History</h1>
            <p class="portal-page-subtitle">
                {{ $patient->full_name ?? $patient->name ?? '—' }}
                @if(isset($patient->health_id))
                    · <code style="font-size:0.8rem;">{{ $patient->health_id }}</code>
                @endif
            </p>
        </div>
        <a href="{{ route('portals.staff.cdss') }}" class="btn btn--outline btn--sm">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> All Alerts
        </a>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
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
                            <td>
                                <span class="badge badge--{{ $alert->severityColor() }}" style="font-size:0.72rem;">
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge--default" style="font-size:0.72rem;">
                                    {{ str_replace('_',' ', $alert->alert_type) }}
                                </span>
                            </td>
                            <td style="max-width:300px;font-size:0.83rem;">
                                {{ Str::limit($alert->alert_message, 120) }}
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;white-space:nowrap;">
                                {{ $alert->triggered_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <span class="badge badge--{{ match($alert->status) {
                                    'active'       => 'danger',
                                    'acknowledged' => 'warning',
                                    'overridden'   => 'success',
                                    'dismissed'    => 'default',
                                    default        => 'default',
                                } }}" style="font-size:0.72rem;">{{ $alert->status }}</span>
                            </td>
                            <td style="font-size:0.8rem;color:#6b7280;">
                                @if($alert->latestOverride)
                                    <div>{{ Str::limit($alert->latestOverride->override_reason, 60) }}</div>
                                    <div style="font-size:0.72rem;">by {{ $alert->latestOverride->overridden_by }}</div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">
                                No clinical alerts found for this patient.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
