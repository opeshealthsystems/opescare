@extends('layouts.portal')
@section('title', 'Lab Alert Ranges — CDSS')
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
    <h2><i data-lucide="test-tube"></i> {{ __('public.staff_portal.cdss_lab_title') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="arrow-left"></i> {{ __('public.staff_portal.cdss_ddi_btn_back') }}
    </a>
</div>
<p class="page-subtitle mb-4">{{ __('public.staff_portal.cdss_lab_subtitle') }}</p>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('public.staff_portal.cdss_lab_col_code') }}</th>
                        <th>{{ __('public.staff_portal.cdss_lab_col_name') }}</th>
                        <th>{{ __('public.staff_portal.cdss_lab_col_unit') }}</th>
                        <th>{{ __('public.staff_portal.cdss_lab_col_crit_low') }}</th>
                        <th>{{ __('public.staff_portal.cdss_lab_col_norm_low') }}</th>
                        <th>{{ __('public.staff_portal.cdss_lab_col_norm_high') }}</th>
                        <th>{{ __('public.staff_portal.cdss_lab_col_crit_high') }}</th>
                        <th>{{ __('public.staff_portal.cdss_lab_col_filters') }}</th>
                        <th>{{ __('public.staff_portal.col_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labRules as $rule)
                        <tr>
                            <td data-label="{{ __('public.staff_portal.cdss_lab_col_code') }}"><code class="mono">{{ $rule->lab_test_code }}</code></td>
                            <td data-label="{{ __('public.staff_portal.cdss_lab_col_name') }}" class="td-strong">{{ $rule->lab_test_name }}</td>
                            <td data-label="{{ __('public.staff_portal.cdss_lab_col_unit') }}" class="td-muted">{{ $rule->unit ?? '—' }}</td>
                            <td data-label="{{ __('public.staff_portal.cdss_lab_col_crit_low') }}">
                                @if($rule->critical_low !== null)<span class="badge badge-danger">{{ $rule->critical_low }}</span>@else — @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_lab_col_norm_low') }}">
                                @if($rule->normal_low !== null)<span class="badge badge-warning">{{ $rule->normal_low }}</span>@else — @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_lab_col_norm_high') }}">
                                @if($rule->normal_high !== null)<span class="badge badge-warning">{{ $rule->normal_high }}</span>@else — @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_lab_col_crit_high') }}">
                                @if($rule->critical_high !== null)<span class="badge badge-danger">{{ $rule->critical_high }}</span>@else — @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_lab_col_filters') }}" class="td-muted">
                                @if($rule->gender_filter) {{ $rule->gender_filter === 'M' ? __('public.staff_portal.cdss_lab_filter_male') : __('public.staff_portal.cdss_lab_filter_female') }} @endif
                                @if($rule->age_min || $rule->age_max)
                                    <div>Age: {{ $rule->age_min ?? '0' }}–{{ $rule->age_max ?? '∞' }} {{ __('public.staff_portal.cdss_lab_filter_yrs') }}</div>
                                @endif
                                @if(!$rule->gender_filter && !$rule->age_min && !$rule->age_max)
                                    {{ __('public.staff_portal.cdss_lab_filter_all') }}
                                @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_status') }}">
                                <span class="badge badge-{{ $rule->is_active ? 'success' : 'neutral' }}">
                                    {{ $rule->is_active ? __('public.staff_portal.cdss_ddi_status_active') : __('public.staff_portal.cdss_ddi_status_inactive') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i data-lucide="test-tube"></i></div>
                                    <p>{{ __('public.staff_portal.cdss_lab_empty') }}</p>
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
