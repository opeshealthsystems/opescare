@extends('layouts.portal')

@section('title', __('public.stf_tele_wr_title'))

@section('content')
<div class="page-head">
    <h2>{{ __('public.stf_tele_wr_heading') }}</h2>
    <div class="page-head__spacer"></div>
    @if($waiting->isNotEmpty())
        <form action="{{ route('portals.staff.telemedicine.call_next') }}" method="POST" class="inline-form">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">{{ __('public.stf_tele_wr_btn_call_next') }}</button>
        </form>
    @endif
    <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> {{ __('public.stf_tele_wr_btn_back') }}
    </a>
</div>

<div class="stat-grid">
    <div class="stat-card stat-card--warning">
        <div class="stat-card__value">{{ $waiting->count() }}</div>
        <div class="stat-card__label">{{ __('public.stf_tele_wr_stat_waiting') }}</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__value">~{{ $estimated }} min</div>
        <div class="stat-card__label">{{ __('public.stf_tele_wr_stat_est_wait') }}</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mt-3 mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('info'))
    <div class="alert alert-info mt-3 mb-4"><i data-lucide="info"></i><div>{{ session('info') }}</div></div>
@endif

<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title">{{ __('public.stf_tele_wr_panel_title') }}</h3></div>
    <div class="panel-body panel-body--flush">
        @if($waiting->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="users"></i></div>
                <p>{{ __('public.stf_tele_wr_empty') }}</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.stf_tele_wr_col_num') }}</th>
                            <th>{{ __('public.stf_tele_wr_col_patient') }}</th>
                            <th>{{ __('public.stf_tele_wr_col_joined_at') }}</th>
                            <th>{{ __('public.stf_tele_wr_col_wait_time') }}</th>
                            <th>{{ __('public.stf_tele_wr_col_status') }}</th>
                            <th class="row-actions">{{ __('public.stf_tele_wr_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($waiting as $i => $entry)
                        <tr>
                            <td data-label="{{ __('public.stf_tele_wr_col_num') }}">{{ $i + 1 }}</td>
                            <td data-label="{{ __('public.stf_tele_wr_col_patient') }}">
                                @if($entry->patient)
                                    {{ $entry->patient->first_name }} {{ $entry->patient->last_name }}
                                @else — @endif
                            </td>
                            <td data-label="{{ __('public.stf_tele_wr_col_joined_at') }}">{{ $entry->joined_at?->format('H:i') }}</td>
                            <td data-label="{{ __('public.stf_tele_wr_col_wait_time') }}">{{ $entry->waitMinutes() !== null ? $entry->waitMinutes() . ' min' : '—' }}</td>
                            <td data-label="{{ __('public.stf_tele_wr_col_status') }}"><span class="badge badge-info">{{ $entry->status }}</span></td>
                            <td class="row-actions" data-label="{{ __('public.stf_tele_wr_col_actions') }}">
                                <a href="{{ route('portals.staff.telemedicine.show', $entry->teleconsultation_id) }}" class="btn btn-ghost btn-sm">{{ __('public.stf_tele_wr_btn_view') }}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
