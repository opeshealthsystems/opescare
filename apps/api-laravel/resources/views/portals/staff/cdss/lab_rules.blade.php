@extends('layouts.portal')
@section('title', 'Lab Alert Ranges — CDSS')
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
                <i data-lucide="test-tube" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Lab Alert Reference Ranges
            </h1>
            <p class="portal-page-subtitle">Normal and critical thresholds for laboratory tests</p>
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
                        <th>Test Code</th>
                        <th>Test Name</th>
                        <th>Unit</th>
                        <th style="color:#dc2626;">Critical Low</th>
                        <th style="color:#d97706;">Normal Low</th>
                        <th style="color:#d97706;">Normal High</th>
                        <th style="color:#dc2626;">Critical High</th>
                        <th>Filters</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labRules as $rule)
                        <tr>
                            <td><code style="font-size:0.76rem;background:#f9fafb;padding:2px 6px;border-radius:4px;">{{ $rule->lab_test_code }}</code></td>
                            <td style="font-size:0.85rem;font-weight:600;">{{ $rule->lab_test_name }}</td>
                            <td style="font-size:0.82rem;color:#6b7280;">{{ $rule->unit ?? '—' }}</td>
                            <td style="font-size:0.82rem;color:#dc2626;">{{ $rule->critical_low !== null ? $rule->critical_low : '—' }}</td>
                            <td style="font-size:0.82rem;color:#d97706;">{{ $rule->normal_low !== null ? $rule->normal_low : '—' }}</td>
                            <td style="font-size:0.82rem;color:#d97706;">{{ $rule->normal_high !== null ? $rule->normal_high : '—' }}</td>
                            <td style="font-size:0.82rem;color:#dc2626;">{{ $rule->critical_high !== null ? $rule->critical_high : '—' }}</td>
                            <td style="font-size:0.79rem;color:#6b7280;">
                                @if($rule->gender_filter) {{ $rule->gender_filter === 'M' ? 'Male only' : 'Female only' }} @endif
                                @if($rule->age_min || $rule->age_max)
                                    <div>Age: {{ $rule->age_min ?? '0' }}–{{ $rule->age_max ?? '∞' }} yrs</div>
                                @endif
                                @if(!$rule->gender_filter && !$rule->age_min && !$rule->age_max)
                                    All patients
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--{{ $rule->is_active ? 'success' : 'default' }}" style="font-size:0.72rem;">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#9ca3af;">
                                No lab alert ranges configured. Seed lab ranges to enable critical value alerts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($labRules->hasPages())<div class="portal-card__footer">{{ $labRules->links() }}</div>@endif
    </div>

</div>
@endsection
