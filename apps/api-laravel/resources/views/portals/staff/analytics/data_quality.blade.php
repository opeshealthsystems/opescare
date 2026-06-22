@extends('layouts.portal')
@section('title', __('staff_analytics.title_data_quality', [], app()->getLocale()) ?: 'Data Quality Analytics')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">{{ __('public.stf_analytics_dq_title') }}</h1>
            <p class="portal-page-subtitle">{{ __('public.stf_analytics_dq_subtitle') }}</p>
        </div>
    </div>

    {{-- Patient Record Completeness --}}
    <div class="portal-card mb-6">
        <div class="portal-card__header">
            <h2 class="portal-card__title">{{ __('public.stf_analytics_dq_card_completeness') }}</h2>
            <span class="td-muted">{{ __('public.stf_analytics_dq_total_patients', ['count' => number_format($totalPatients)]) }}</span>
        </div>
        <div class="portal-card__body">
            @php
                $fields = [
                    __('public.stf_analytics_dq_field_phone')   => $withPhone,
                    __('public.stf_analytics_dq_field_dob')     => $withDob,
                    __('public.stf_analytics_dq_field_address') => $withAddress,
                    __('public.stf_analytics_dq_field_nok')     => $withNextOfKin,
                    __('public.stf_analytics_dq_field_nhis')    => $withNhis,
                ];
            @endphp
            <div class="breakdown">
            @foreach($fields as $label => $count)
                @php
                    $pct = $totalPatients > 0 ? round($count / $totalPatients * 100) : 0;
                    $fillMod = $pct >= 80 ? '' : ($pct >= 50 ? 'breakdown__fill--warning' : 'breakdown__fill--danger');
                @endphp
                <div class="breakdown__row">
                    <span class="breakdown__label">{{ $label }}</span>
                    <div class="breakdown__track"><div class="breakdown__fill {{ $fillMod }}" style="width:{{ $pct }}%;"></div></div>
                    <span class="breakdown__value">{{ number_format($count) }} / {{ number_format($totalPatients) }} ({{ $pct }}%)</span>
                </div>
            @endforeach
            </div>
        </div>
    </div>

    <div class="grid-2 mb-6">

        {{-- Import History --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_dq_card_import') }}</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th>{{ __('public.stf_analytics_dq_col_status') }}</th>
                        <th>{{ __('public.stf_analytics_dq_col_batches') }}</th>
                        <th>{{ __('public.stf_analytics_dq_col_records') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($importStats as $status => $row)
                            <tr>
                                <td data-label="{{ __('public.stf_analytics_dq_col_status') }}">
                                    <span class="badge badge--{{ match($status) {
                                        'completed' => 'success',
                                        'failed'    => 'danger',
                                        'pending'   => 'warning',
                                        'processing'=> 'info',
                                        default     => 'default',
                                    } }}">@enum($status)</span>
                                </td>
                                <td data-label="{{ __('public.stf_analytics_dq_col_batches') }}" class="td-strong">{{ number_format($row->cnt) }}</td>
                                <td data-label="{{ __('public.stf_analytics_dq_col_records') }}" class="td-muted">{{ number_format($row->records ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">{{ __('public.stf_analytics_dq_no_imports') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- CDSS Alert Quality --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title">{{ __('public.stf_analytics_dq_card_cdss') }}</h2>
            </div>
            <div class="portal-card__body">
                @if(!empty($alertsByType))
                    @foreach($alertsByType as $type => $cnt)
                        <div class="list-row">
                            <span class="list-row__main">@enum($type)</span>
                            <span class="list-row__value">{{ number_format($cnt) }}</span>
                        </div>
                    @endforeach
                    <div class="kv-table mt-6">
                        <div class="flex-between">
                            <span class="td-muted">{{ __('public.stf_analytics_dq_override_rate') }}</span>
                            <span class="badge {{ ($overrideRate ?? 0) > 50 ? 'badge-danger' : 'badge-success' }}">
                                {{ $overrideRate ?? 0 }}%
                            </span>
                        </div>
                        @if(($overrideRate ?? 0) > 50)
                            <p class="td-muted mt-6">{{ __('public.stf_analytics_dq_high_override') }}</p>
                        @endif
                    </div>
                @else
                    <p class="td-muted">{{ __('public.stf_analytics_dq_no_alerts') }}</p>
                @endif
            </div>
        </div>

    </div>

    {{-- Recent Imports --}}
    @if(!empty($recentImports))
    <div class="portal-card">
        <div class="portal-card__header"><h2 class="portal-card__title">{{ __('public.stf_analytics_dq_card_recent') }}</h2></div>
        <div class="portal-card__body panel-body--flush">
            <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('public.stf_analytics_dq_col_batch') }}</th>
                        <th>{{ __('public.stf_analytics_dq_col_type') }}</th>
                        <th>{{ __('public.stf_analytics_dq_col_records') }}</th>
                        <th>{{ __('public.stf_analytics_dq_col_errors') }}</th>
                        <th>{{ __('public.stf_analytics_dq_col_status') }}</th>
                        <th>{{ __('public.stf_analytics_dq_col_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentImports as $imp)
                        <tr>
                            <td data-label="{{ __('public.stf_analytics_dq_col_batch') }}"><span class="mono">{{ substr($imp->id ?? '', 0, 8) }}…</span></td>
                            <td data-label="{{ __('public.stf_analytics_dq_col_type') }}">@enum($imp->import_type ?? '—')</td>
                            <td data-label="{{ __('public.stf_analytics_dq_col_records') }}">{{ number_format($imp->total_records ?? 0) }}</td>
                            <td data-label="{{ __('public.stf_analytics_dq_col_errors') }}">
                                <span class="badge {{ ($imp->error_count ?? 0) > 0 ? 'badge-danger' : 'badge-success' }}">{{ $imp->error_count ?? 0 }}</span>
                            </td>
                            <td data-label="{{ __('public.stf_analytics_dq_col_status') }}">
                                <span class="badge badge--{{ match($imp->status ?? '') {
                                    'completed' => 'success',
                                    'failed'    => 'danger',
                                    'pending'   => 'warning',
                                    default     => 'default',
                                } }}">@enum($imp->status ?? '—')</span>
                            </td>
                            <td data-label="{{ __('public.stf_analytics_dq_col_date') }}" class="td-muted">
                                {{ isset($imp->created_at) ? \Carbon\Carbon::parse($imp->created_at)->format('d M Y') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
