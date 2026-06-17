@extends('layouts.portal')

@section('title', __('public.stf_tele_create_title'))

@section('content')
<div class="breadcrumb">
    <a href="{{ route('portals.staff.telemedicine.index') }}">{{ __('public.stf_tele_index_heading') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ __('public.stf_tele_create_breadcrumb') }}</span>
</div>

<div class="page-head">
    <h2>{{ __('public.stf_tele_create_heading') }}</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> {{ __('public.stf_tele_create_btn_back') }}
    </a>
</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title">{{ __('public.stf_tele_create_panel_title') }}</h3>
    </div>
    <div class="panel-body">
        @if($errors->any())
            <div class="alert alert-danger mb-4">
                <i data-lucide="triangle-alert"></i>
                <div>
                    <ul class="alert-list">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('portals.staff.telemedicine.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label form-label-required" for="patient_id">{{ __('public.stf_tele_create_label_patient') }}</label>
                <select name="patient_id" id="patient_id" class="form-control" required>
                    <option value="">{{ __('public.stf_tele_create_patient_placeholder') }}</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->first_name }} {{ $p->last_name }}
                            @if($p->health_id) ({{ $p->health_id }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label form-label-required" for="scheduled_at">{{ __('public.stf_tele_create_label_scheduled_at') }}</label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                       class="form-control" value="{{ old('scheduled_at') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="platform">{{ __('public.stf_tele_create_label_platform') }}</label>
                <select name="platform" id="platform" class="form-control">
                    <option value="own" {{ old('platform') == 'own' ? 'selected' : '' }}>{{ __('public.stf_tele_create_platform_own') }}</option>
                    <option value="zoom" {{ old('platform') == 'zoom' ? 'selected' : '' }}>{{ __('public.stf_tele_create_platform_zoom') }}</option>
                    <option value="meet" {{ old('platform') == 'meet' ? 'selected' : '' }}>{{ __('public.stf_tele_create_platform_meet') }}</option>
                    <option value="teams" {{ old('platform') == 'teams' ? 'selected' : '' }}>{{ __('public.stf_tele_create_platform_teams') }}</option>
                </select>
            </div>

            <div class="alert alert-info mb-4">
                <i data-lucide="info"></i>
                <div>
                    <strong>{{ __('public.stf_tele_create_consent_title') }}</strong> {{ __('public.stf_tele_create_consent_body') }}
                </div>
            </div>

            <div class="row-actions">
                <button type="submit" class="btn btn-primary">{{ __('public.stf_tele_create_btn_submit') }}</button>
                <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn-secondary">{{ __('public.stf_tele_create_btn_cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
