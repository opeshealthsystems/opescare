@extends('layouts.portal')
@section('title', 'Widget Embed — Connect Suite')
@section('sidebar') @include('portals.admin.connect._sidebar') @endsection

@section('content')

<div class="page-head">
    <h2><i data-lucide="layout-panel-left"></i> Widget Embed Generator</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">Generate embed snippets for the OpesCare Connect Widget</p>

<div class="field-grid">

    {{-- Configuration Panel --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="settings"></i> Widget Configuration</h3></div>
        <div class="panel-body">
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
                <input type="color" id="widgetColor" class="form-control" value="#0891b2" onchange="regenerate()">
            </div>
        </div>
    </div>

    {{-- Preview & Code --}}
    <div>
        <div class="panel mb-6">
            <div class="panel-header">
                <h3 class="panel-title"><i data-lucide="code-2"></i> Script Tag</h3>
                <button class="btn btn-ghost btn-sm" onclick="copyCode('scriptCode')"><i data-lucide="copy"></i> Copy</button>
            </div>
            <pre id="scriptCode" class="code-block"></pre>
        </div>

        <div class="panel mb-6">
            <div class="panel-header">
                <h3 class="panel-title"><i data-lucide="code"></i> HTML Embed Tag</h3>
                <button class="btn btn-ghost btn-sm" onclick="copyCode('embedCode')"><i data-lucide="copy"></i> Copy</button>
            </div>
            <pre id="embedCode" class="code-block"></pre>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h3 class="panel-title"><i data-lucide="braces"></i> JavaScript Init</h3>
                <button class="btn btn-ghost btn-sm" onclick="copyCode('jsCode')"><i data-lucide="copy"></i> Copy</button>
            </div>
            <pre id="jsCode" class="code-block"></pre>
        </div>
    </div>

</div>

{{-- Documentation --}}
<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="book-open"></i> Integration Guide</h3></div>
    <div class="panel-body">
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-card__head"><i data-lucide="check-circle"></i> <span class="kv-strong">Step 1 — Add Script</span></div>
                <p class="td-muted">Place the script tag in the <code class="code-token">&lt;head&gt;</code> of your page. The widget loads asynchronously and will not block rendering.</p>
            </div>
            <div class="stat-card">
                <div class="stat-card__head"><i data-lucide="code"></i> <span class="kv-strong">Step 2 — Place Embed Tag</span></div>
                <p class="td-muted">Drop the <code class="code-token">&lt;opescare-widget&gt;</code> tag anywhere in your HTML where you want the widget to render.</p>
            </div>
            <div class="stat-card">
                <div class="stat-card__head"><i data-lucide="key"></i> <span class="kv-strong">Step 3 — Authenticate</span></div>
                <p class="td-muted">Pass a short-lived session token from your server via <code class="code-token">OpesCareWidget.setToken(token)</code> after the user logs in.</p>
            </div>
            <div class="stat-card">
                <div class="stat-card__head"><i data-lucide="shield-check"></i> <span class="kv-strong">Security</span></div>
                <p class="td-muted">Never embed your API secret in front-end code. Widget sessions are scoped and expire. All traffic is HTTPS.</p>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
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
        btn.innerHTML = '<i data-lucide="check"></i> Copied!';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        setTimeout(() => { btn.innerHTML = orig; if (typeof lucide !== 'undefined') lucide.createIcons(); }, 2000);
    });
}

document.addEventListener('DOMContentLoaded', regenerate);
</script>
@endsection
