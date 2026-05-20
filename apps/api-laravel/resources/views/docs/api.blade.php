@extends('layouts.docs')
@section('title', 'Connect API')
@section('content')

<h1>Connect API</h1>
<p class="docs-lead">
    The Connect API is OpesCare's primary REST interface for healthcare system integration.
    Use OAuth 2.0 client credentials to authenticate, then access patient records, consent,
    inventory, and operational data.
</p>

<div class="docs-callout info">
    <i data-lucide="info" style="width:1rem;height:1rem;flex-shrink:0;margin-top:2px;"></i>
    <div>All Connect API endpoints require a Bearer token in the <code>Authorization</code> header. See <a href="{{ route('docs.authentication') }}">Authentication</a> for how to obtain one.</div>
</div>

<h2 id="base">Base URL</h2>
<div class="docs-code-block">
    <div class="docs-code-tabs"><button class="docs-code-tab active" data-lang="text">URL</button></div>
    <div class="docs-code-pane active" data-lang="text"><pre>https://opescare.test/api/v1/connect</pre></div>
</div>

<h2 id="auth-endpoint">Step 1 — Get a Token</h2>

<div class="endpoint-pill">
    <span class="method-badge method-post">POST</span>
    <span class="endpoint-path">/connect/auth/token</span>
</div>
<p>Issues an OAuth 2.0 Bearer token using <code>client_credentials</code> grant. See <a href="{{ route('docs.authentication') }}">Authentication</a> for full code examples and available scopes.</p>

<h2 id="patient-search">Patient Search</h2>

<div class="endpoint-pill">
    <span class="method-badge method-get">GET</span>
    <span class="endpoint-path">/connect/patient/search?health_id={health_id}</span>
</div>
<p>Look up a patient by their OpesCare Health ID. Requires <code>patient:profile:read</code> scope.</p>

<table class="docs-table">
    <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><code>health_id</code></td><td>string</td><td>Yes</td><td>OpesCare Health ID (e.g. <code>OPC-2024-XK7T9</code>)</td></tr>
    </tbody>
</table>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="curl">cURL</button>
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
        <button class="docs-code-tab" data-lang="java">Java</button>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>curl "https://opescare.test/api/v1/connect/patient/search?health_id=OPC-2024-XK7T9" \
  -H "Authorization: Bearer {access_token}"</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>$patient = Http::withToken($accessToken)
    ->get('https://opescare.test/api/v1/connect/patient/search', [
        'health_id' =&gt; 'OPC-2024-XK7T9',
    ])->json();</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>const patient = await fetch(
  'https://opescare.test/api/v1/connect/patient/search?health_id=OPC-2024-XK7T9',
  { headers: { Authorization: `Bearer ${accessToken}` } }
).then(r => r.json());</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>patient = requests.get(
    'https://opescare.test/api/v1/connect/patient/search',
    params={'health_id': 'OPC-2024-XK7T9'},
    headers={'Authorization': f'Bearer {access_token}'}
).json()</pre>
    </div>
    <div class="docs-code-pane" data-lang="java">
<pre>HttpRequest req = HttpRequest.newBuilder()
    .uri(URI.create("https://opescare.test/api/v1/connect/patient/search?health_id=OPC-2024-XK7T9"))
    .header("Authorization", "Bearer " + accessToken)
    .GET().build();</pre>
    </div>
</div>

<h2 id="consent">Consent Endpoints</h2>

<div class="endpoint-pill">
    <span class="method-badge method-get">GET</span>
    <span class="endpoint-path">/connect/patient/{health_id}/consent</span>
</div>
<p>Returns the patient's current consent status for your integration client. Check this before requesting records.</p>

<div class="endpoint-pill">
    <span class="method-badge method-post">POST</span>
    <span class="endpoint-path">/connect/patient/{health_id}/consent/grant</span>
</div>
<p>Records that the patient has granted consent. The patient must be present or verified through another channel. This action is audited.</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="curl">cURL</button>
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>curl -X POST "https://opescare.test/api/v1/connect/patient/OPC-2024-XK7T9/consent/grant" \
  -H "Authorization: Bearer {access_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "consent_type": "full_record",
    "expires_at": "2026-12-31T23:59:59Z"
  }'</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>Http::withToken($accessToken)
    ->post('https://opescare.test/api/v1/connect/patient/OPC-2024-XK7T9/consent/grant', [
        'consent_type' =&gt; 'full_record',
        'expires_at'   =&gt; '2026-12-31T23:59:59Z',
    ]);</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>requests.post(
    'https://opescare.test/api/v1/connect/patient/OPC-2024-XK7T9/consent/grant',
    headers={'Authorization': f'Bearer {access_token}'},
    json={'consent_type': 'full_record', 'expires_at': '2026-12-31T23:59:59Z'}
)</pre>
    </div>
</div>

<h2 id="records">Records Endpoints</h2>

<div class="endpoint-pill">
    <span class="method-badge method-get">GET</span>
    <span class="endpoint-path">/connect/patient/{health_id}/records</span>
</div>
<p>Retrieve patient medical records. Requires active consent and <code>patient:diagnostics:read</code> scope.</p>

<table class="docs-table">
    <thead><tr><th>Parameter</th><th>Values</th><th>Default</th></tr></thead>
    <tbody>
        <tr><td><code>type</code></td><td><code>diagnoses</code> <code>prescriptions</code> <code>lab_results</code> <code>vitals</code> <code>all</code></td><td><code>all</code></td></tr>
    </tbody>
</table>

<h2 id="inventory">Inventory Endpoints</h2>

<div class="endpoint-pill">
    <span class="method-badge method-get">GET</span>
    <span class="endpoint-path">/connect/inventory/pharmacy/{facility_id}</span>
</div>
<p>Pharmacy stock levels. Requires <code>pharmacy:stock:read</code> scope.</p>

<div class="endpoint-pill">
    <span class="method-badge method-get">GET</span>
    <span class="endpoint-path">/connect/inventory/blood/{facility_id}</span>
</div>
<p>Blood bank inventory by blood group and component. Requires <code>blood:inventory:read</code> scope.</p>

<div class="docs-callout warning">
    <i data-lucide="alert-triangle" style="width:1rem;height:1rem;flex-shrink:0;margin-top:2px;"></i>
    <div>Blood and medicine availability shown via the API is indicative only and does not guarantee supply. Always confirm directly with the facility before making clinical decisions.</div>
</div>

<h2 id="webhooks-sub">Webhook Subscription Endpoints</h2>

<div class="endpoint-pill">
    <span class="method-badge method-post">POST</span>
    <span class="endpoint-path">/connect/webhooks/subscriptions</span>
</div>
<p>Subscribe your endpoint to receive push events. See <a href="{{ route('docs.webhooks') }}">Webhooks</a> for the full guide including signature verification.</p>

<div class="endpoint-pill">
    <span class="method-badge method-get">GET</span>
    <span class="endpoint-path">/connect/webhooks/subscriptions</span>
</div>
<p>List your active webhook subscriptions.</p>

<div class="endpoint-pill">
    <span class="method-badge method-delete">DELETE</span>
    <span class="endpoint-path">/connect/webhooks/subscriptions/{id}</span>
</div>
<p>Unsubscribe an endpoint. Returns <code>204 No Content</code>.</p>

<h2 id="reconciliation">Reconciliation</h2>

<div class="endpoint-pill">
    <span class="method-badge method-get">GET</span>
    <span class="endpoint-path">/connect/reconciliation?date={date}</span>
</div>
<p>Retrieve payment reconciliation records for a specific date.</p>

<h2 id="try-it">Try It</h2>
<p>Use the <a href="{{ route('docs.playground') }}">Interactive Playground</a> to try all 16 Connect API endpoints live in your browser with the full OpenAPI spec.</p>

<div class="docs-page-nav">
    <a href="{{ route('docs.authentication') }}">← Authentication</a>
    <a href="{{ route('docs.sdk') }}">SDK →</a>
</div>

@endsection
