@extends('layouts.public')

@section('title', __('public.status_page.page_title'))
@section('meta_description', __('public.status_page.meta_description'))

@section('content')

    {{-- Hero --}}
    <header class="content-header" style="padding:3rem 0 2.5rem;">
        <div class="container">
            <h1>{{ __('public.status_page.hero_title') }}</h1>
            <p class="text-muted">{{ __('public.status_page.hero_subtitle') }}</p>
        </div>
    </header>

    <section class="content-body">
        <div class="container" style="max-width:800px;">

            {{-- Overall banner --}}
            <div style="background:#ecfdf5;border:1.5px solid #10b981;border-radius:1.25rem;display:flex;align-items:center;gap:1.25rem;padding:1.5rem;margin-bottom:2.5rem;">
                <div style="width:3rem;height:3rem;background:#10b981;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="check" style="width:1.5rem;height:1.5rem;color:#fff;"></i>
                </div>
                <div>
                    <h3 style="margin:0;color:#065f46;font-size:1.125rem;">{{ __('public.status_page.all_operational') }}</h3>
                    <p style="margin:.25rem 0 0;font-size:.875rem;color:#065f46;">{{ __('public.status_page.last_updated') }} {{ now()->format('d M Y — H:i') }} {{ __('public.status_page.utc') }}</p>
                </div>
            </div>

            {{-- Service table --}}
            @php
            $services = [
                ['group'=>__('public.status_page.group_core'),'items'=>[
                    __('public.status_page.svc_connect_api'),
                    __('public.status_page.svc_health_id'),
                    __('public.status_page.svc_consent'),
                    __('public.status_page.svc_audit'),
                ]],
                ['group'=>__('public.status_page.group_clinical'),'items'=>[
                    __('public.status_page.svc_timeline'),
                    __('public.status_page.svc_lab'),
                    __('public.status_page.svc_prescription'),
                    __('public.status_page.svc_referral'),
                ]],
                ['group'=>__('public.status_page.group_availability'),'items'=>[
                    __('public.status_page.svc_medication'),
                    __('public.status_page.svc_blood'),
                    __('public.status_page.svc_care_map'),
                ]],
                ['group'=>__('public.status_page.group_integration'),'items'=>[
                    __('public.status_page.svc_webhooks'),
                    __('public.status_page.svc_bridge'),
                    __('public.status_page.svc_sdk'),
                ]],
                ['group'=>__('public.status_page.group_portal'),'items'=>[
                    __('public.status_page.svc_patient_portal'),
                    __('public.status_page.svc_staff_portal'),
                    __('public.status_page.svc_dev_portal'),
                    __('public.status_page.svc_auth'),
                ]],
            ];
            @endphp

            @foreach($services as $group)
            <div style="margin-bottom:2rem;">
                <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.75rem;">{{ $group['group'] }}</h3>
                <div style="border:1px solid #e2e8f0;border-radius:1rem;overflow:hidden;">
                    @foreach($group['items'] as $i => $name)
                    <div style="display:flex;align-items:center;padding:1rem 1.25rem;{{ $i < count($group['items'])-1 ? 'border-bottom:1px solid #e2e8f0;' : '' }}background:#fff;">
                        <div style="display:flex;align-items:center;gap:.75rem;flex:1;min-width:0;">
                            <span style="width:.625rem;height:.625rem;border-radius:50%;background:#10b981;flex-shrink:0;"></span>
                            <span style="font-weight:600;font-size:.9375rem;color:#0F2744;overflow:hidden;text-overflow:ellipsis;">{{ $name }}</span>
                        </div>
                        <span style="font-size:.6875rem;font-weight:700;text-transform:uppercase;color:#10b981;background:#ecfdf5;padding:.2rem .6rem;border-radius:999px;min-width:6rem;text-align:center;flex-shrink:0;">{{ __('public.status_page.operational') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Incident history --}}
            <div style="margin-bottom:2rem;">
                <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.75rem;">{{ __('public.status_page.incidents_title') }}</h3>
                <div style="border:1px solid #e2e8f0;border-radius:1rem;overflow:hidden;">
                    <div style="padding:1.25rem 1.5rem;background:#fff;">
                        <div style="display:flex;align-items:flex-start;gap:1rem;">
                            <span style="width:.625rem;height:.625rem;border-radius:50%;background:#10b981;flex-shrink:0;margin-top:.35rem;"></span>
                            <div>
                                <div style="font-weight:700;font-size:.9375rem;margin-bottom:.25rem;">{{ __('public.status_page.no_incidents') }}</div>
                                <div style="font-size:.8125rem;color:#64748b;">{{ __('public.status_page.no_incidents_desc') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Subscribe notice --}}
            <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:1.25rem;padding:1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                <i data-lucide="bell" style="width:1.5rem;height:1.5rem;color:#2563EB;flex-shrink:0;"></i>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;color:#1E40AF;margin-bottom:.2rem;">{{ __('public.status_page.subscribe_title') }}</div>
                    <div style="font-size:.8125rem;color:#3B82F6;">{{ __('public.status_page.subscribe_desc') }}</div>
                </div>
                <a href="{{ route('public.contact') }}" class="btn btn-primary" style="font-size:.875rem;padding:.625rem 1.25rem;flex-shrink:0;">{{ __('public.status_page.btn_subscribe') }}</a>
            </div>

        </div>
    </section>

    <style>
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.4} }
        @media(min-width:640px){ .d-sm-inline{display:inline!important;} }
    </style>

@endsection
