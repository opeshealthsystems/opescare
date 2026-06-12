@extends('layouts.portal')

@section('title', __('public.portal.nav_appointments', [], app()->getLocale()) . ' — OpesCare Patient Portal')

@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments')


@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'My Appointments' }}</h1>
        <p class="page-subtitle">{{ __('public.portal.appointments_subtitle', [], app()->getLocale()) ?: 'View your upcoming and past appointments.' }}</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-info" style="margin-bottom:var(--p-space-4);"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif

@php $apptCount = method_exists($appointments, 'total') ? $appointments->total() : $appointments->count(); @endphp

@if($apptCount === 0)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="calendar-check-2"></i></div>
        <h3>{{ __('public.portal.no_appointments_title', [], app()->getLocale()) ?: 'No Appointments' }}</h3>
        <p>{{ __('public.portal.no_appointments_desc', [], app()->getLocale()) ?: 'You don\'t have any appointments scheduled at this time.' }}</p>
        <a href="{{ route('public.care-map') }}" class="btn btn-primary">
            <i data-lucide="map-pin"></i>
            {{ __('public.portal.nav_care_map', [], app()->getLocale()) ?: 'Find a Provider' }}
        </a>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="calendar-check-2"></i> {{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</h2>
        <span class="badge badge-primary">{{ $apptCount }}</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="Appointments list">
            <thead>
                <tr>
                    <th>{{ __('public.portal.date_time', [], app()->getLocale()) ?: 'Date & Time' }}</th>
                    <th>{{ __('public.portal.provider', [], app()->getLocale()) ?: 'Provider' }}</th>
                    <th>{{ __('public.portal.appointment_type', [], app()->getLocale()) ?: 'Type' }}</th>
                    <th>{{ __('public.portal.facility', [], app()->getLocale()) ?: 'Facility' }}</th>
                    <th>{{ __('public.portal.status', [], app()->getLocale()) ?: 'Status' }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $appt)
                <tr>
                    <td data-label="{{ __('public.portal.date_time', [], app()->getLocale()) ?: 'Date & Time' }}">
                        <span class="td-strong">
                            {{ $appt->scheduled_at?->format('d M Y') ?? '—' }}
                        </span>
                        @if($appt->scheduled_at)
                        <div class="td-muted">{{ $appt->scheduled_at->format('H:i') }}</div>
                        @endif
                    </td>
                    <td data-label="{{ __('public.portal.provider', [], app()->getLocale()) ?: 'Provider' }}">
                        @php
                            $providerName = $appt->provider?->name
                                ?? (($appt->provider?->first_name ?? '') . ' ' . ($appt->provider?->last_name ?? ''))
                                ?: '—';
                        @endphp
                        <span class="td-strong">{{ trim($providerName) ?: '—' }}</span>
                    </td>
                    <td data-label="{{ __('public.portal.appointment_type', [], app()->getLocale()) ?: 'Type' }}">
                        <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $appt->appointment_type ?? 'General')) }}</span>
                    </td>
                    <td data-label="{{ __('public.portal.facility', [], app()->getLocale()) ?: 'Facility' }}">
                        <span class="td-muted">{{ $appt->facility?->name ?? 'Unknown Facility' }}</span>
                    </td>
                    <td data-label="{{ __('public.portal.status', [], app()->getLocale()) ?: 'Status' }}">
                        @php
                            $stCls = match($appt->status ?? 'scheduled') {
                                'completed'  => 'badge-success',
                                'cancelled'  => 'badge-danger',
                                'no_show'    => 'badge-warning',
                                'checked_in' => 'badge-teal',
                                default      => 'badge-primary',
                            };
                        @endphp
                        <span class="badge {{ $stCls }}">{{ ucfirst(str_replace('_', ' ', $appt->status ?? 'Scheduled')) }}</span>
                    </td>
                    <td>
                        @if(in_array($appt->status, ['scheduled', 'confirmed']))
                        <form method="POST" action="{{ route('portals.patient.appointments.cancel', $appt->id) }}"
                              onsubmit="return confirm('Cancel this appointment?')">
                            @csrf
                            <button type="submit" class="btn btn-sm"
                                style="font-size:0.75rem;background:var(--p-surface-2);color:#DC2626;border:1px solid #FECACA;padding:3px 10px;border-radius:var(--p-radius-sm);">
                                <i data-lucide="x-circle" style="width:0.75rem;height:0.75rem;vertical-align:middle;margin-right:2px;"></i>Cancel
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($appointments, 'links'))
    <div style="padding:var(--p-space-4);border-top:1px solid var(--p-border);">
        {{ $appointments->links() }}
    </div>
    @endif
</div>
@endif

@endsection
