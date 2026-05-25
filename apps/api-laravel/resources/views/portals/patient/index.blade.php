@extends('layouts.portal')

@section('title', __('public.medical_id.health_id', [], app()->getLocale()) . ' — OpesCare Patient Portal')

@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))

@section('content')

@if($patient)

<!-- Health ID Card -->
<div class="health-id-card mb-8" style="margin-bottom:var(--p-space-8);">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--p-space-5);">
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:var(--p-space-3);margin-bottom:var(--p-space-5);">
                <div style="padding:var(--p-space-2);background:rgba(255,255,255,.15);border-radius:var(--p-radius);">
                    <i data-lucide="fingerprint" style="width:1.25rem;height:1.25rem;"></i>
                </div>
                <span class="health-id-label">{{ __('public.medical_id.health_id', [], app()->getLocale()) ?: 'OpesCare Health ID' }}</span>
            </div>

            <div class="health-id-value">{{ $patient->health_id }}</div>

            <div class="health-id-meta">
                <div>
                    <div class="health-id-meta-label">{{ __('public.portal.status', [], app()->getLocale()) ?: 'Status' }}</div>
                    <div class="health-id-meta-value" style="display:flex;align-items:center;gap:0.35rem;">
                        <span style="width:7px;height:7px;background:#34D399;border-radius:50%;display:inline-block;"></span>
                        {{ ucfirst($patient->verification_status ?? 'Active') }}
                    </div>
                </div>
                <div>
                    <div class="health-id-meta-label">{{ __('public.portal.country', [], app()->getLocale()) ?: 'Country' }}</div>
                    <div class="health-id-meta-value">{{ $patient->country_code ?? '—' }}</div>
                </div>
                <div>
                    <div class="health-id-meta-label">{{ __('public.portal.registered', [], app()->getLocale()) ?: 'Registered' }}</div>
                    <div class="health-id-meta-value">{{ $patient->created_at?->format('M Y') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <!-- Static QR (server-rendered) -->
        <div class="health-id-qr" aria-label="{{ __('public.medical_id.scan_qr', [], app()->getLocale()) ?: 'Scan QR' }}">
            @if($staticQrDataUri)
                <img src="{{ $staticQrDataUri }}"
                     alt="{{ __('public.medical_id.health_id_qr_alt', [], app()->getLocale()) ?: 'Health ID QR Code' }}"
                     style="width:5rem;height:5rem;border-radius:var(--p-radius-sm);" />
            @else
                <div style="width:5rem;height:5rem;display:flex;align-items:center;justify-content:center;background:#F1F5F9;border-radius:var(--p-radius-sm);">
                    <i data-lucide="qr-code" style="width:3rem;height:3rem;color:var(--p-text);"></i>
                </div>
            @endif
            <span>{{ __('public.medical_id.scan_qr', [], app()->getLocale()) ?: 'Scan QR' }}</span>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions mb-8" style="margin-bottom:var(--p-space-8);">
    <a href="{{ route('portals.patient.appointments') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="calendar-check-2"></i></div>
        <span class="quick-action-label">{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.patient.labs') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="flask-conical"></i></div>
        <span class="quick-action-label">{{ __('public.portal.nav_labs', [], app()->getLocale()) ?: 'Lab Results' }}</span>
    </a>
    <a href="{{ route('portals.patient.prescriptions') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="pill"></i></div>
        <span class="quick-action-label">{{ __('public.portal.nav_prescriptions', [], app()->getLocale()) ?: 'Prescriptions' }}</span>
    </a>
    <a href="{{ route('portals.patient.consent') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="shield-check"></i></div>
        <span class="quick-action-label">{{ __('public.portal.nav_consent', [], app()->getLocale()) ?: 'Consent' }}</span>
    </a>
    <a href="{{ route('portals.patient.documents') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="file-text"></i></div>
        <span class="quick-action-label">{{ __('public.portal.nav_documents', [], app()->getLocale()) ?: 'Documents' }}</span>
    </a>
    <a href="{{ route('portals.patient.logs') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="history"></i></div>
        <span class="quick-action-label">{{ __('public.medical_id.access_logs', [], app()->getLocale()) ?: 'Access Logs' }}</span>
    </a>
    <a href="{{ route('portals.patient.profile') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="user-cog"></i></div>
        <span class="quick-action-label">{{ __('public.portal.nav_profile', [], app()->getLocale()) ?: 'My Profile' }}</span>
    </a>
    <a href="{{ route('public.care-map') }}" class="quick-action-btn">
        <div class="quick-action-icon"><i data-lucide="map-pin"></i></div>
        <span class="quick-action-label">{{ __('public.portal.nav_care_map', [], app()->getLocale()) ?: 'Care Map' }}</span>
    </a>
</div>

<!-- Main Two-Column Layout -->
<div class="grid-main-side">

    <!-- Left: Temp QR Generator -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="qr-code"></i>
                {{ __('public.medical_id.temporary_access_qr', [], app()->getLocale()) ?: 'Temporary Access QR' }}
            </h2>
        </div>
        <div class="panel-body" style="text-align:center;">
            <p style="font-size:0.9rem;color:var(--p-text-muted);margin-bottom:var(--p-space-6);">
                {{ __('public.portal.temp_qr_desc', [], app()->getLocale()) ?: 'Generate a secure, time-limited QR code to share with a new healthcare provider. Expires in 1 hour.' }}
            </p>

            <button id="generate-temp-qr" class="btn btn-primary" style="margin:0 auto;">
                <i data-lucide="refresh-cw"></i>
                {{ __('public.portal.generate_qr', [], app()->getLocale()) ?: 'Generate Temporary QR' }}
            </button>

            <div id="temp-qr-container" style="margin-top:var(--p-space-6);display:none;flex-direction:column;align-items:center;" aria-live="polite">
                <div style="background:white;padding:var(--p-space-3);border-radius:var(--p-radius);border:1px solid var(--p-border);">
                    <img id="temp-qr-img" src="" alt="{{ __('public.portal.temp_qr_alt', [], app()->getLocale()) ?: 'Temporary Access QR Code' }}"
                         style="width:8rem;height:8rem;border-radius:var(--p-radius-sm);display:none;" />
                    <div id="temp-qr-placeholder" style="width:8rem;height:8rem;display:flex;align-items:center;justify-content:center;background:#F1F5F9;border-radius:var(--p-radius-sm);">
                        <i data-lucide="qr-code" style="width:4rem;height:4rem;color:var(--p-text);"></i>
                    </div>
                </div>
                <p style="margin-top:var(--p-space-3);font-size:0.8125rem;color:var(--p-warning);font-weight:700;display:flex;align-items:center;gap:var(--p-space-2);">
                    <i data-lucide="clock" style="width:0.9rem;height:0.9rem;"></i>
                    {{ __('public.portal.expires_in', [], app()->getLocale()) ?: 'Expires in' }}: <span id="countdown">60:00</span>
                </p>
            </div>

            <div class="alert alert-info mt-6" style="margin-top:var(--p-space-6);text-align:left;">
                <i data-lucide="info"></i>
                <div style="font-size:0.8125rem;">{{ __('public.portal.qr_audit_notice', [], app()->getLocale()) ?: 'Each QR scan is audited. You can view access history in your Access Logs.' }}</div>
            </div>
        </div>
    </div>

    <!-- Right: Profile prompt + Disclaimer -->
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">

        <!-- Profile prompt -->
        <div class="panel">
            <div class="panel-body" style="text-align:center;">
                <p style="font-size:0.8125rem;color:var(--p-text-muted);margin-bottom:var(--p-space-3);">{{ __('public.portal.profile_prompt_desc', [], app()->getLocale()) ?: 'Manage your privacy preferences, contact details and emergency contacts in your profile.' }}</p>
                <a href="{{ route('portals.patient.profile') }}" class="btn btn-primary" style="font-size:0.8125rem;">
                    <i data-lucide="user-cog"></i> {{ __('public.portal.profile_prompt_btn', [], app()->getLocale()) ?: 'Go to Profile' }}
                </a>
            </div>
        </div>

        <!-- Clinical Safety Disclaimer -->
        <div class="panel">
            <div class="panel-body">
                <div class="alert alert-warning">
                    <i data-lucide="alert-triangle"></i>
                    <div style="font-size:0.8125rem;">
                        {{ __('onboarding.brand.clinical_disclaimer', [], app()->getLocale()) ?: 'OpesCare facilitates access to your health records but is not a substitute for clinical advice. Always consult a licensed healthcare provider for medical decisions.' }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@else
<!-- No patient profile -->
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);">
            <i data-lucide="alert-circle"></i>
        </div>
        <h3>{{ __('public.portal.no_profile_title', [], app()->getLocale()) ?: 'No Patient Profile Found' }}</h3>
        <p>{{ __('public.portal.no_profile_desc', [], app()->getLocale()) ?: 'Your patient profile could not be loaded. Please contact support if this problem persists.' }}</p>
        <a href="{{ route('public.help') }}" class="btn btn-primary">
            <i data-lucide="help-circle"></i>
            {{ __('public.portal.nav_help', [], app()->getLocale()) ?: 'Get Help' }}
        </a>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lblGenerating   = @json(__('public.portal.generating', [], app()->getLocale()) ?: 'Generating…');
    var lblRegenerateQr = @json(__('public.portal.regenerate_qr', [], app()->getLocale()) ?: 'Regenerate QR');
    var countdownInterval = null;

    var btnGen = document.getElementById('generate-temp-qr');
    if (btnGen) {
        btnGen.addEventListener('click', async function () {
            btnGen.disabled = true;
            btnGen.innerHTML = '<i data-lucide="loader" style="width:1rem;height:1rem;"></i> ' + lblGenerating;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            try {
                var response = await fetch("{{ route('portals.patient.qr') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (!response.ok) throw new Error('QR generation failed (' + response.status + ')');
                var data = await response.json();
                if (data.qr_image) {
                    var img         = document.getElementById('temp-qr-img');
                    var placeholder = document.getElementById('temp-qr-placeholder');
                    var container   = document.getElementById('temp-qr-container');
                    img.src         = data.qr_image;
                    img.style.display       = 'block';
                    placeholder.style.display = 'none';
                    container.style.display = 'flex';
                    startCountdown(data.expires_in || 3600);
                }
            } catch (e) {
                console.error('Temp QR error:', e);
            } finally {
                btnGen.disabled = false;
                btnGen.innerHTML = '<i data-lucide="refresh-cw" style="width:1rem;height:1rem;"></i> ' + lblRegenerateQr;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    }

    function startCountdown(seconds) {
        if (countdownInterval) clearInterval(countdownInterval);
        var el = document.getElementById('countdown');
        countdownInterval = setInterval(function () {
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                countdownInterval = null;
                if (el) el.textContent = 'Expired';
                return;
            }
            seconds--;
            var m = Math.floor(seconds / 60).toString().padStart(2, '0');
            var s = (seconds % 60).toString().padStart(2, '0');
            if (el) el.textContent = m + ':' + s;
        }, 1000);
    }
});
</script>
@endsection
