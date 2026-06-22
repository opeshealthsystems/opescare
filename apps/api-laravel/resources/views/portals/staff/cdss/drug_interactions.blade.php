@extends('layouts.portal')
@section('title', __('staff_analytics.cdss_title_ddi', [], app()->getLocale()) ?: 'Drug Interactions — CDSS')
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
    <h2><i data-lucide="git-merge"></i> {{ __('public.staff_portal.cdss_ddi_title') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.cdss') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="arrow-left"></i> {{ __('public.staff_portal.cdss_ddi_btn_back') }}
    </a>
</div>
<p class="page-subtitle mb-4">{{ __('public.staff_portal.cdss_ddi_subtitle') }}</p>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('public.staff_portal.cdss_ddi_col_drug_a') }}</th>
                        <th>{{ __('public.staff_portal.cdss_ddi_col_drug_b') }}</th>
                        <th>{{ __('public.staff_portal.cdss_ddi_col_severity') }}</th>
                        <th>{{ __('public.staff_portal.cdss_ddi_col_interaction') }}</th>
                        <th>{{ __('public.staff_portal.cdss_ddi_col_management') }}</th>
                        <th>{{ __('public.staff_portal.cdss_ddi_col_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($interactions as $rule)
                        <tr>
                            <td data-label="{{ __('public.staff_portal.cdss_ddi_col_drug_a') }}">
                                <div class="td-strong">{{ $rule->drug_a_name }}</div>
                                <code class="mono td-muted">{{ $rule->drug_a_code }}</code>
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_ddi_col_drug_b') }}">
                                <div class="td-strong">{{ $rule->drug_b_name }}</div>
                                <code class="mono td-muted">{{ $rule->drug_b_code }}</code>
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_ddi_col_severity') }}">
                                <span class="badge badge-{{ match($rule->severity) {
                                    'contraindicated' => 'danger',
                                    'major'           => 'danger',
                                    'moderate'        => 'warning',
                                    default           => 'info',
                                } }}">@enum($rule->severity, 'severity')</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_ddi_col_interaction') }}">
                                {{ Str::limit($rule->interaction_description, 100) }}
                                @if($rule->clinical_effect)
                                    <div class="td-muted">{{ Str::limit($rule->clinical_effect, 60) }}</div>
                                @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_ddi_col_management') }}" class="td-muted">
                                {{ $rule->management ? Str::limit($rule->management, 80) : '—' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.cdss_ddi_col_status') }}">
                                <span class="badge badge-{{ $rule->is_active ? 'success' : 'neutral' }}">
                                    {{ $rule->is_active ? __('public.staff_portal.cdss_ddi_status_active') : __('public.staff_portal.cdss_ddi_status_inactive') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i data-lucide="git-merge"></i></div>
                                    <p>{{ __('public.staff_portal.cdss_ddi_empty') }}</p>
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
