@extends('layouts.portal')
@section('title', __('public.adm_cdss_page_title'))
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', __('public.adm_breadcrumb_home'))
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="page-head">
    <h2>{{ __('public.adm_cdss_page_h2') }}</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">{{ __('public.adm_cdss_subtitle') }}</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

<div class="alert alert-warning mb-6">
    <i data-lucide="alert-triangle"></i>
    <div><strong>Caution:</strong> {{ __('public.adm_cdss_caution_full_msg') }}</div>
</div>

<div class="tabs mb-6">
    <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="tab">{{ __('public.adm_cdss_tab_drug') }}</a>
    <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="tab">{{ __('public.adm_cdss_tab_allergy') }}</a>
    <a href="{{ route('portals.admin.cdss.lab-alerts') }}" class="tab">{{ __('public.adm_cdss_tab_lab') }}</a>
</div>

<div class="field-grid">
    <div class="panel">
        <div class="panel-body">
            <div class="stat-card stat-card--danger mb-6">
                <div class="stat-card__head"><i data-lucide="zap"></i></div>
                <div class="stat-card__label">{{ __('public.adm_cdss_kpi_drug') }}</div>
                <div class="stat-card__value">{{ $drugInteractionCount ?? 0 }}</div>
            </div>
            <p class="td-muted mb-6">{{ __('public.adm_cdss_desc_drug') }}</p>
            <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="btn btn-danger btn-sm"><i data-lucide="arrow-right"></i> {{ __('public.adm_cdss_btn_manage_rules') }}</a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="stat-card stat-card--warning mb-6">
                <div class="stat-card__head"><i data-lucide="shield-alert"></i></div>
                <div class="stat-card__label">{{ __('public.adm_cdss_kpi_allergy') }}</div>
                <div class="stat-card__value">{{ $allergyAlertCount ?? 0 }}</div>
            </div>
            <p class="td-muted mb-6">{{ __('public.adm_cdss_desc_allergy') }}</p>
            <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="btn btn-warning btn-sm"><i data-lucide="arrow-right"></i> {{ __('public.adm_cdss_btn_manage_rules') }}</a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="stat-card stat-card--primary mb-6">
                <div class="stat-card__head"><i data-lucide="flask-conical"></i></div>
                <div class="stat-card__label">{{ __('public.adm_cdss_kpi_lab') }}</div>
                <div class="stat-card__value">{{ $labAlertCount ?? 0 }}</div>
            </div>
            <p class="td-muted mb-6">{{ __('public.adm_cdss_desc_lab') }}</p>
            <a href="{{ route('portals.admin.cdss.lab-alerts') }}" class="btn btn-primary btn-sm"><i data-lucide="arrow-right"></i> {{ __('public.adm_cdss_btn_manage_rules') }}</a>
        </div>
    </div>
</div>
@endsection
