@extends('layouts.portal')
@section('title', 'Clinical Rules — CDSS')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')

{{-- CDSS Disclaimer --}}
<div class="alert alert-warning mb-6">
    <i data-lucide="shield-alert"></i>
    <div>
        <strong>{{ __('public.staff_portal.cdss_disclaimer_title') }}</strong>
        {{ __('public.staff_portal.cdss_disclaimer_body') }}
    </div>
</div>

<div class="page-head">
    <h2><i data-lucide="list-checks"></i> {{ __('public.staff_portal.cdss_rules_title') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="arrow-left"></i> {{ __('public.staff_portal.cdss_ddi_btn_back') }}
    </a>
</div>
<p class="page-subtitle mb-4">{{ __('public.staff_portal.cdss_rules_subtitle') }}</p>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('public.staff_portal.cdss_rules_col_code') }}</th>
                        <th>{{ __('public.staff_portal.cdss_rules_col_type') }}</th>
                        <th>{{ __('public.staff_portal.cdss_rules_col_name') }}</th>
                        <th>{{ __('public.staff_portal.cdss_rules_col_severity') }}</th>
                        <th>{{ __('public.staff_portal.cdss_rules_col_overridable') }}</th>
                        <th>{{ __('public.staff_portal.cdss_rules_col_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td data-label="{{ __('public.staff_portal.cdss_rules_col_code') }}"><code class="mono">{{ $rule->rule_code }}</code></td>
                            <td data-label="{{ __('public.staff_portal.cdss_rules_col_type') }}">
                                <span class="badge badge-info">{{ str_replace('_',' ', $rule->rule_type) }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_rules_col_name') }}">
                                <div class="td-strong">{{ $rule->name }}</div>
                                @if($rule->description)
                                    <div class="td-muted">{{ Str::limit($rule->description, 60) }}</div>
                                @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_rules_col_severity') }}">
                                <span class="badge badge-{{ match($rule->severity) {
                                    'critical' => 'danger',
                                    'warning'  => 'warning',
                                    default    => 'info',
                                } }}">{{ ucfirst($rule->severity) }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_rules_col_overridable') }}">
                                @if($rule->is_overridable)
                                    <span class="badge badge-success"><i data-lucide="check"></i> {{ __('public.staff_portal.cdss_rules_yes') }}</span>
                                @else
                                    <span class="badge badge-danger"><i data-lucide="x"></i> {{ __('public.staff_portal.cdss_rules_no') }}</span>
                                @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_rules_col_status') }}">
                                <span class="badge badge-{{ $rule->is_active ? 'success' : 'neutral' }}">
                                    {{ $rule->is_active ? __('public.staff_portal.cdss_rules_active') : __('public.staff_portal.cdss_rules_inactive') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i data-lucide="list-checks"></i></div>
                                    <p>{{ __('public.staff_portal.cdss_rules_empty') }}</p>
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
