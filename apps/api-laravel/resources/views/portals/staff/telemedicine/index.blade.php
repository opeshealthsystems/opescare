@extends('layouts.portal')

@section('title', __('public.stf_tele_index_title'))

@section('content')
<div class="page-head">
    <h2>{{ __('public.stf_tele_index_heading') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.telemedicine.waiting_room') }}" class="btn btn-secondary btn-sm">
        {{ __('public.stf_tele_index_btn_waiting_room') }}
        @if($waiting > 0)
            <span class="badge badge-danger">{{ $waiting }}</span>
        @endif
    </a>
    <a href="{{ route('portals.staff.telemedicine.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> {{ __('public.stf_tele_index_btn_schedule') }}
    </a>
</div>
<p class="page-subtitle mb-4">{{ __('public.stf_tele_index_subtitle') }}</p>

{{-- CDSS Disclaimer --}}
<div class="alert alert-info mb-4">
    <i data-lucide="info"></i>
    <div>
        <strong>{{ __('public.stf_tele_index_clinical_note_title') }}:</strong> {{ __('public.stf_tele_index_clinical_note_body') }}
    </div>
</div>

{{-- Stats strip --}}
<div class="stat-grid">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__value">{{ $today }}</div>
        <div class="stat-card__label">{{ __('public.stf_tele_index_stat_today') }}</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__value">{{ $waiting }}</div>
        <div class="stat-card__label">{{ __('public.stf_tele_index_stat_waiting') }}</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__value">{{ $completed }}</div>
        <div class="stat-card__label">{{ __('public.stf_tele_index_stat_completed') }}</div>
    </div>
    <div class="stat-card stat-card--teal">
        <div class="stat-card__value">{{ $scheduled->total() }}</div>
        <div class="stat-card__label">{{ __('public.stf_tele_index_stat_scheduled') }}</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mt-3 mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mt-3 mb-4"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Consultations table --}}
<div class="panel mt-6">
    <div class="panel-header">
        <h3 class="panel-title">{{ __('public.stf_tele_index_panel_title') }}</h3>
    </div>
    <div class="panel-body panel-body--flush">
        @if($scheduled->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="video"></i></div>
                <p>{{ __('public.stf_tele_index_empty') }}</p>
                <a href="{{ route('portals.staff.telemedicine.create') }}" class="btn btn-primary btn-sm mt-3">
                    {{ __('public.stf_tele_index_btn_schedule') }}
                </a>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.stf_tele_index_col_patient') }}</th>
                            <th>{{ __('public.stf_tele_index_col_scheduled') }}</th>
                            <th>{{ __('public.stf_tele_index_col_platform') }}</th>
                            <th>{{ __('public.stf_tele_index_col_status') }}</th>
                            <th class="row-actions">{{ __('public.stf_tele_index_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduled as $c)
                        @php
                            $tcBadge = match($c->status) {
                                'scheduled' => 'badge-info',
                                'waiting'   => 'badge-warning',
                                'active'    => 'badge-success',
                                'completed' => 'badge-neutral',
                                'cancelled', 'failed' => 'badge-danger',
                                default     => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.stf_tele_index_col_patient') }}">
                                @if($c->patient)
                                    {{ $c->patient->first_name }} {{ $c->patient->last_name }}
                                @else
                                    <span class="td-muted">—</span>
                                @endif
                            </td>
                            <td data-label="{{ __('public.stf_tele_index_col_scheduled') }}">{{ $c->scheduled_at ? $c->scheduled_at->format('d M Y H:i') : '—' }}</td>
                            <td data-label="{{ __('public.stf_tele_index_col_platform') }}">{{ ucfirst($c->platform ?? 'own') }}</td>
                            <td data-label="{{ __('public.stf_tele_index_col_status') }}"><span class="badge {{ $tcBadge }}">{{ $c->status }}</span></td>
                            <td class="row-actions" data-label="{{ __('public.stf_tele_index_col_actions') }}">
                                <a href="{{ route('portals.staff.telemedicine.show', $c->id) }}" class="btn btn-ghost btn-sm">{{ __('public.stf_tele_index_btn_view') }}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-body">
                {{ $scheduled->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
