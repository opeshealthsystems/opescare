@extends('layouts.portal')
@section('title', 'CDSS Rules')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="page-head">
    <h2>Clinical Decision Support Rules</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">Manage drug interaction, allergy, and lab alert rules.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

<div class="alert alert-warning mb-6">
    <i data-lucide="alert-triangle"></i>
    <div><strong>Caution:</strong> These rules directly affect clinical decision support alerts shown to clinicians. Review carefully before adding or removing any rule.</div>
</div>

<div class="tabs mb-6">
    <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="tab">Drug Interactions</a>
    <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="tab">Allergy Alerts</a>
    <a href="{{ route('portals.admin.cdss.lab-alerts') }}" class="tab">Lab Alerts</a>
</div>

<div class="field-grid">
    <div class="panel">
        <div class="panel-body">
            <div class="stat-card stat-card--danger mb-6">
                <div class="stat-card__head"><i data-lucide="zap"></i></div>
                <div class="stat-card__label">Drug Interaction Rules</div>
                <div class="stat-card__value">{{ $drugInteractionCount ?? 0 }}</div>
            </div>
            <p class="td-muted mb-6">Rules that flag dangerous drug-drug interactions and require clinical review or override.</p>
            <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="btn btn-danger btn-sm"><i data-lucide="arrow-right"></i> Manage Rules</a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="stat-card stat-card--warning mb-6">
                <div class="stat-card__head"><i data-lucide="shield-alert"></i></div>
                <div class="stat-card__label">Allergy Alert Rules</div>
                <div class="stat-card__value">{{ $allergyAlertCount ?? 0 }}</div>
            </div>
            <p class="td-muted mb-6">Rules that trigger allergy alerts when a drug matches a known allergen class for a patient.</p>
            <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="btn btn-warning btn-sm"><i data-lucide="arrow-right"></i> Manage Rules</a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="stat-card stat-card--primary mb-6">
                <div class="stat-card__head"><i data-lucide="flask-conical"></i></div>
                <div class="stat-card__label">Lab Alert Rules</div>
                <div class="stat-card__value">{{ $labAlertCount ?? 0 }}</div>
            </div>
            <p class="td-muted mb-6">Rules that alert clinicians when lab values fall outside defined thresholds for a given context.</p>
            <a href="{{ route('portals.admin.cdss.lab-alerts') }}" class="btn btn-primary btn-sm"><i data-lucide="arrow-right"></i> Manage Rules</a>
        </div>
    </div>
</div>
@endsection
