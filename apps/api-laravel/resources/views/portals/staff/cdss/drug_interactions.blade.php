@extends('layouts.portal')
@section('title', 'Drug Interactions — CDSS')
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
            <h1 class="portal-page-title">
                <i data-lucide="git-merge" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Drug Interaction Rules
            </h1>
            <p class="portal-page-subtitle">Bidirectional drug-drug interaction database</p>
        </div>
        <a href="{{ route('portals.staff.cdss') }}" class="btn btn--outline btn--sm">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Alerts
        </a>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
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
                            <td>
                                <div style="font-size:0.84rem;font-weight:600;">{{ $rule->drug_a_name }}</div>
                                <code style="font-size:0.72rem;color:#9ca3af;">{{ $rule->drug_a_code }}</code>
                            </td>
                            <td>
                                <div style="font-size:0.84rem;font-weight:600;">{{ $rule->drug_b_name }}</div>
                                <code style="font-size:0.72rem;color:#9ca3af;">{{ $rule->drug_b_code }}</code>
                            </td>
                            <td>
                                <span class="badge badge--{{ match($rule->severity) {
                                    'contraindicated' => 'danger',
                                    'major'           => 'danger',
                                    'moderate'        => 'warning',
                                    default           => 'info',
                                } }}" style="font-size:0.72rem;">{{ ucfirst($rule->severity) }}</span>
                            </td>
                            <td style="max-width:220px;font-size:0.82rem;">
                                {{ Str::limit($rule->interaction_description, 100) }}
                                @if($rule->clinical_effect)
                                    <div style="font-size:0.73rem;color:#9ca3af;margin-top:2px;">{{ Str::limit($rule->clinical_effect, 60) }}</div>
                                @endif
                            </td>
                            <td style="max-width:180px;font-size:0.82rem;color:#6b7280;">
                                {{ $rule->management ? Str::limit($rule->management, 80) : '—' }}
                            </td>
                            <td>
                                <span class="badge badge--{{ $rule->is_active ? 'success' : 'default' }}" style="font-size:0.72rem;">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">
                                No drug interaction rules loaded. Seed interactions to enable DDI alerts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($interactions->hasPages())<div class="portal-card__footer">{{ $interactions->links() }}</div>@endif
    </div>

</div>
@endsection
