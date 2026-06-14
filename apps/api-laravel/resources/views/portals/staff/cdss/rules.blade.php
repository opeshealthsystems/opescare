@extends('layouts.portal')
@section('title', 'Clinical Rules — CDSS')
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
                <i data-lucide="list-checks" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Clinical Rules
            </h1>
            <p class="portal-page-subtitle">Configured decision-support rules for this facility</p>
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
                            <td><code style="font-size:0.76rem;background:#f9fafb;padding:2px 6px;border-radius:4px;">{{ $rule->rule_code }}</code></td>
                            <td>
                                <span class="badge badge--info" style="font-size:0.72rem;">{{ str_replace('_',' ', $rule->rule_type) }}</span>
                            </td>
                            <td>
                                <div style="font-size:0.85rem;font-weight:600;">{{ $rule->name }}</div>
                                @if($rule->description)
                                    <div style="font-size:0.73rem;color:#9ca3af;">{{ Str::limit($rule->description, 60) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--{{ match($rule->severity) {
                                    'critical' => 'danger',
                                    'warning'  => 'warning',
                                    default    => 'info',
                                } }}" style="font-size:0.72rem;">{{ ucfirst($rule->severity) }}</span>
                            </td>
                            <td style="font-size:0.82rem;">
                                @if($rule->is_overridable)
                                    <span style="color:#16a34a;"><i data-lucide="check" style="width:14px;height:14px;vertical-align:-2px;"></i> Yes</span>
                                @else
                                    <span style="color:#dc2626;"><i data-lucide="x" style="width:14px;height:14px;vertical-align:-2px;"></i> No</span>
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
                            <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">
                                No clinical rules configured. Rules are seeded from system defaults.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rules->hasPages())<div class="portal-card__footer">{{ $rules->links() }}</div>@endif
    </div>

</div>
@endsection
