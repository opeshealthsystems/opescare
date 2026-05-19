@extends('layouts.portal')
@section('title', 'Widget Embed — Connect Suite')
@section('sidebar') @include('portals.admin.connect._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Widget Embed Generator</h1>
            <p class="portal-page-subtitle">Generate embed snippets for the OpesCare Connect Widget</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

        {{-- Configuration Panel --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title"><i data-lucide="settings" style="width:15px;height:15px;"></i> Widget Configuration</h2>
            </div>
            <div class="portal-card__body">
                <div class="form-group">
                    <label class="form-label">Widget Type</label>
                    <select id="widgetType" class="form-control" onchange="regenerate()">
                        <option value="appointment">Appointment Booking</option>
                        <option value="health_id_verify">Health ID Verification</option>
                        <option value="patient_summary">Patient Summary</option>
                        <option value="queue_status">Queue Status</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">API Client</label>
                    <select id="clientId" class="form-control" onchange="regenerate()">
                        <option value="">— Select Active Client —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->client_id }}">{{ $client->name }} ({{ $client->environment }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Facility ID</label>
                    <input type="text" id="facilityId" class="form-control" placeholder="UUID of the facility" oninput="regenerate()">
                </div>
                <div class="form-group">
                    <label class="form-label">Theme</label>
                    <select id="widgetTheme" class="form-control" onchange="regenerate()">
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                        <option value="auto">Auto (system preference)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Language</label>
                    <select id="widgetLang" class="form-control" onchange="regenerate()">
                        <option value="en">English</option>
                        <option value="fr">French</option>
                        <option value="ha">Hausa</option>
                        <option value="yo">Yoruba</option>
                        <option value="ig">Igbo</option>
                        <option value="sw">Swahili</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Primary Colour</label>
                    <input type="color" id="widgetColor" class="form-control" value="#0891b2" style="height:40px;padding:4px;" onchange="regenerate()">
                </div>
            </div>
        </div>

        {{-- Preview & Code --}}
        <div>
            <div class="portal-card" style="margin-bottom:16px;">
                <div class="portal-card__header">
                    <h2 class="portal-card__title"><i data-lucide="code-2" style="width:15px;height:15px;"></i> Script Tag</h2>
                    <button class="btn btn--sm btn--outline" onclick="copyCode('scriptCode')">
                        <i data-lucide="copy" style="width:13px;height:13px;"></i> Copy
                    </button>
                </div>
                <div class="portal-card__body" style="padding:0;">
                    <pre id="scriptCode" style="background:#0f172a;color:#e2e8f0;padding:16px;font-size:0.76rem;overflow-x:auto;margin:0;border-radius:0 0 8px 8px;white-space:pre-wrap;word-break:break-all;"></pre>
                </div>
            </div>

            <div class="portal-card" style="margin-bottom:16px;">
                <div class="portal-card__header">
                    <h2 class="portal-card__title"><i data-lucide="code" style="width:15px;height:15px;"></i> HTML Embed Tag</h2>
                    <button class="btn btn--sm btn--outline" onclick="copyCode('embedCode')">
                        <i data-lucide="copy" style="width:13px;height:13px;"></i> Copy
                    </button>
                </div>
                <div class="portal-card__body" style="padding:0;">
                    <pre id="embedCode" style="background:#0f172a;color:#e2e8f0;padding:16px;font-size:0.76rem;overflow-x:auto;margin:0;border-radius:0 0 8px 8px;white-space:pre-wrap;word-break:break-all;"></pre>
                </div>
            </div>

            <div class="portal-card">
                <div class="portal-card__header">
                    <h2 class="portal-card__title"><i data-lucide="braces" style="width:15px;height:15px;"></i> JavaScript Init</h2>
                    <button class="btn btn--sm btn--outline" onclick="copyCode('jsCode')">
                        <i data-lucide="copy" style="width:13px;height:13px;"></i> Copy
                    </button>
                </div>
                <div class="portal-card__body" style="padding:0;">
                    <pre id="jsCode" style="background:#0f172a;color:#e2e8f0;padding:16px;font-size:0.76rem;overflow-x:auto;margin:0;border-radius:0 0 8px 8px;white-space:pre-wrap;word-break:break-all;"></pre>
                </div>
            </div>
        </div>

    </div>

    {{-- Documentation --}}
    <div class="portal-card" style="margin-top:24px;">
        <div class="portal-card__header">
            <h2 class="portal-card__title"><i data-lucide="book-open" style="width:15px;height:15px;"></i> Integration Guide</h2>
        </div>
        <div class="portal-card__body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                <div style="background:#f0fdf4;border-radius:8px;padding:16px;">
                    <div style="font-weight:600;font-size:0.88rem;color:#166534;margin-bottom:6px;">
                        <i data-lucide="check-circle" style="width:14px;height:14px;"></i> Step 1 — Add Script
                    </div>
                    <div style="font-size:0.8rem;color:#4b5563;">Place the script tag in the <code>&lt;head&gt;</code> of your page. The widget loads asynchronously and will not block rendering.</div>
                </div>
                <div style="background:#eff6ff;border-radius:8px;padding:16px;">
                    <div style="font-weight:600;font-size:0.88rem;color:#1e40af;margin-bottom:6px;">
                        <i data-lucide="code" style="width:14px;height:14px;"></i> Step 2 — Place Embed Tag
                    </div>
                    <div style="font-size:0.8rem;color:#4b5563;">Drop the <code>&lt;opescare-widget&gt;</code> tag anywhere in your HTML where you want the widget to render.</div>
                </div>
                <div style="background:#fefce8;border-radius:8px;padding:16px;">
                    <div style="font-weight:600;font-size:0.88rem;color:#854d0e;margin-bottom:6px;">
                        <i data-lucide="key" style="width:14px;height:14px;"></i> Step 3 — Authenticate
                    </div>
                    <div style="font-size:0.8rem;color:#4b5563;">Pass a short-lived session token from your server via <code>OpesCareWidget.setToken(token)</code> after the user logs in.</div>
                </div>
                <div style="background:#fdf4ff;border-radius:8px;padding:16px;">
                    <div style="font-weight:600;font-size:0.88rem;color:#7e22ce;margin-bottom:6px;">
                        <i data-lucide="shield-check" style="width:14px;height:14px;"></i> Security
                    </div>
                    <div style="font-size:0.8rem;color:#4b5563;">Never embed your API secret in front-end code. Widget sessions are scoped and expire. All traffic is HTTPS.</div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const BASE_URL = '{{ $baseUrl }}';

function regenerate() {
    const type      = document.getElementById('widgetType').value;
    const clientId  = document.getElementById('clientId').value || 'YOUR_CLIENT_ID';
    const facility  = document.getElementById('facilityId').value || 'YOUR_FACILITY_ID';
    const theme     = document.getElementById('widgetTheme').value;
    const lang      = document.getElementById('widgetLang').value;
    const color     = document.getElementById('widgetColor').value;

    // Script tag
    document.getElementById('scriptCode').textContent =
`<script src="${BASE_URL}/widget/v1/loader.js"
  data-client="${clientId}"
  data-env="${clientId.startsWith('sk_live') ? 'production' : 'sandbox'}"
  async><\/script>`;

    // Embed tag
    document.getElementById('embedCode').textContent =
`<opescare-widget
  type="${type}"
  facility-id="${facility}"
  theme="${theme}"
  lang="${lang}"
  primary-color="${color}">
</opescare-widget>`;

    // JS init
    document.getElementById('jsCode').textContent =
`// Called after user authenticates on your platform
window.OpesCareWidget?.init({
  clientId:   '${clientId}',
  facilityId: '${facility}',
  widgetType: '${type}',
  theme:      '${theme}',
  lang:       '${lang}',
  primaryColor: '${color}',
  onReady:    () => console.log('OpesCare widget ready'),
  onEvent:    (e) => console.log('Widget event:', e),
});

// Pass a server-generated session token for authenticated actions
// OpesCareWidget.setToken('<SESSION_TOKEN_FROM_YOUR_SERVER>');`;
}

function copyCode(id) {
    const text = document.getElementById(id).textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.currentTarget;
        const orig = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" style="width:13px;height:13px;"></i> Copied!';
        lucide.createIcons();
        setTimeout(() => { btn.innerHTML = orig; lucide.createIcons(); }, 2000);
    });
}

// Init on load
document.addEventListener('DOMContentLoaded', regenerate);
</script>
@endsection
