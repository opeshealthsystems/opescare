@extends('layouts.portal')
@section('title', 'CDSS Rules')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Clinical Decision Support Rules</h1>
        <p class="page-subtitle">Manage drug interaction, allergy, and lab alert rules.</p>
    </div>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

<div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:var(--p-radius);padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.75rem;">
    <i data-lucide="alert-triangle" style="width:18px;height:18px;color:var(--p-warning);flex-shrink:0;margin-top:.1rem;"></i>
    <div style="font-size:.875rem;"><strong>Caution:</strong> These rules directly affect clinical decision support alerts shown to clinicians. Review carefully before adding or removing any rule.</div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;">
    <div class="panel" style="display:flex;flex-direction:column;">
        <div style="padding:1.5rem;">
            <div style="width:52px;height:52px;border-radius:var(--p-radius-lg);background:rgba(239,68,68,.1);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                <i data-lucide="zap" style="width:24px;height:24px;color:var(--p-danger);"></i>
            </div>
            <div style="color:var(--p-text-muted);font-size:.82rem;margin-bottom:.25rem;">Drug Interaction Rules</div>
            <div style="font-size:2.25rem;font-weight:700;line-height:1;margin-bottom:.75rem;">{{ $drugInteractionCount ?? 0 }}</div>
            <p style="font-size:.825rem;color:var(--p-text-muted);margin-bottom:1.25rem;">Rules that flag dangerous drug-drug interactions and require clinical review or override.</p>
            <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="btn btn-danger btn-sm">
                <i data-lucide="arrow-right"></i> Manage Rules
            </a>
        </div>
    </div>

    <div class="panel" style="display:flex;flex-direction:column;">
        <div style="padding:1.5rem;">
            <div style="width:52px;height:52px;border-radius:var(--p-radius-lg);background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                <i data-lucide="shield-alert" style="width:24px;height:24px;color:var(--p-warning);"></i>
            </div>
            <div style="color:var(--p-text-muted);font-size:.82rem;margin-bottom:.25rem;">Allergy Alert Rules</div>
            <div style="font-size:2.25rem;font-weight:700;line-height:1;margin-bottom:.75rem;">{{ $allergyAlertCount ?? 0 }}</div>
            <p style="font-size:.825rem;color:var(--p-text-muted);margin-bottom:1.25rem;">Rules that trigger allergy alerts when a drug matches a known allergen class for a patient.</p>
            <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="btn btn-warning btn-sm">
                <i data-lucide="arrow-right"></i> Manage Rules
            </a>
        </div>
    </div>

    <div class="panel" style="display:flex;flex-direction:column;">
        <div style="padding:1.5rem;">
            <div style="width:52px;height:52px;border-radius:var(--p-radius-lg);background:rgba(14,165,233,.1);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                <i data-lucide="flask-conical" style="width:24px;height:24px;color:var(--p-primary);"></i>
            </div>
            <div style="color:var(--p-text-muted);font-size:.82rem;margin-bottom:.25rem;">Lab Alert Rules</div>
            <div style="font-size:2.25rem;font-weight:700;line-height:1;margin-bottom:.75rem;">{{ $labAlertCount ?? 0 }}</div>
            <p style="font-size:.825rem;color:var(--p-text-muted);margin-bottom:1.25rem;">Rules that alert clinicians when lab values fall outside defined thresholds for a given context.</p>
            <span class="btn btn-ghost btn-sm" style="opacity:.5;pointer-events:none;">Coming Soon</span>
        </div>
    </div>
</div>
@endsection
