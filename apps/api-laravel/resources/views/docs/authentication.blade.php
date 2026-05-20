@extends('layouts.docs')
@section('title', 'Authentication')
@section('content')

<h1>Authentication</h1>
<p class="docs-lead">
    OpesCare uses three separate authentication mechanisms depending on which integration type
    you are using. All tokens should be kept secret and never exposed in client-side code.
</p>

<h2 id="connect-auth">Connect API — OAuth 2.0 Client Credentials</h2>

<p>
    The Connect API uses the <strong>OAuth 2.0 client_credentials</strong> grant.
    Your integration client has a <code>client_id</code> and <code>client_secret</code>
    (created in the developer portal). Exchange them for a short-lived Bearer token (1 hour TTL).
</p>

<h3>Token Request</h3>
<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="curl">cURL</button>
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
        <button class="docs-code-tab" data-lang="java">Java</button>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>curl -X POST https://opescare.test/api/v1/connect/auth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET",
    "scope": "patient:profile:read pharmacy:stock:read"
  }'</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>use Illuminate\Support\Facades\Http;

$response = Http::post('https://opescare.test/api/v1/connect/auth/token', [
    'grant_type'    =&gt; 'client_credentials',
    'client_id'     =&gt; env('OPESCARE_CLIENT_ID'),
    'client_secret' =&gt; env('OPESCARE_CLIENT_SECRET'),
    'scope'         =&gt; 'patient:profile:read pharmacy:stock:read',
]);

$accessToken = $response->json('access_token');
$expiresIn   = $response->json('expires_in'); // 3600 seconds</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>async function getToken() {
  const res = await fetch('https://opescare.test/api/v1/connect/auth/token', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      grant_type:    'client_credentials',
      client_id:     process.env.OPESCARE_CLIENT_ID,
      client_secret: process.env.OPESCARE_CLIENT_SECRET,
      scope:         'patient:profile:read pharmacy:stock:read',
    }),
  });
  const { access_token, expires_in } = await res.json();
  return access_token;
}</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>import requests, os

def get_token():
    resp = requests.post('https://opescare.test/api/v1/connect/auth/token', json={
        'grant_type':    'client_credentials',
        'client_id':     os.environ['OPESCARE_CLIENT_ID'],
        'client_secret': os.environ['OPESCARE_CLIENT_SECRET'],
        'scope':         'patient:profile:read pharmacy:stock:read',
    })
    resp.raise_for_status()
    return resp.json()['access_token']</pre>
    </div>
    <div class="docs-code-pane" data-lang="java">
<pre>import java.net.http.*;
import java.net.URI;

String body = String.format(
    "{\"grant_type\":\"client_credentials\",\"client_id\":\"%s\",\"client_secret\":\"%s\",\"scope\":\"patient:profile:read\"}",
    System.getenv("OPESCARE_CLIENT_ID"),
    System.getenv("OPESCARE_CLIENT_SECRET")
);
HttpRequest request = HttpRequest.newBuilder()
    .uri(URI.create("https://opescare.test/api/v1/connect/auth/token"))
    .header("Content-Type", "application/json")
    .POST(HttpRequest.BodyPublishers.ofString(body))
    .build();
var response = HttpClient.newHttpClient().send(request, HttpResponse.BodyHandlers.ofString());</pre>
    </div>
</div>

<h3>Available Scopes</h3>
<table class="docs-table">
    <thead><tr><th>Scope</th><th>Description</th></tr></thead>
    <tbody>
        <tr><td><code>patient:profile:read</code></td><td>Read patient demographics and health ID</td></tr>
        <tr><td><code>patient:diagnostics:read</code></td><td>Read diagnoses, clinical notes</td></tr>
        <tr><td><code>pharmacy:stock:read</code></td><td>Read pharmacy inventory levels</td></tr>
        <tr><td><code>blood:inventory:read</code></td><td>Read blood bank inventory</td></tr>
        <tr><td><code>lab:results:read</code></td><td>Read laboratory results</td></tr>
    </tbody>
</table>

<div class="docs-callout info">
    <i data-lucide="info" style="width:1rem;height:1rem;flex-shrink:0;margin-top:2px;"></i>
    <div>Tokens expire after <strong>3600 seconds (1 hour)</strong>. Cache them and re-request when they expire. Never request a new token for every API call.</div>
</div>

<h2 id="sdk-auth">SDK — Bearer Token</h2>

<p>
    SDK tokens are long-lived static tokens generated in your developer portal.
    Pass them as an <code>Authorization: Bearer</code> header on SDK API calls.
    Rotate tokens periodically via the portal.
</p>

<div class="docs-callout warning">
    <i data-lucide="alert-triangle" style="width:1rem;height:1rem;flex-shrink:0;margin-top:2px;"></i>
    <div>Never include SDK tokens in client-side JavaScript or mobile apps. Use server-side rendered pages only.</div>
</div>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="curl">cURL</button>
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript (server-side)</button>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>curl https://opescare.test/api/v1/sdk/patient/OPC-2024-DEMO1 \
  -H "Authorization: Bearer YOUR_SDK_TOKEN"</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>$patient = Http::withToken(config('opescare.sdk_token'))
    ->get('https://opescare.test/api/v1/sdk/patient/OPC-2024-DEMO1')
    ->json();</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>// Node.js server-side only
const patient = await fetch('https://opescare.test/api/v1/sdk/patient/OPC-2024-DEMO1', {
  headers: { 'Authorization': `Bearer ${process.env.OPESCARE_SDK_TOKEN}` }
}).then(r => r.json());</pre>
    </div>
</div>

<h2 id="bridge-auth">Bridge Agent — X-Bridge-Token</h2>

<p>
    Bridge Agents authenticate with a static token passed in the <code>X-Bridge-Token</code>
    header. The token is generated when you register the agent in the developer portal
    under <strong>Apps → Bridge Agents</strong>.
</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="curl">cURL</button>
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>curl -X POST https://opescare.test/api/v1/bridge/heartbeat \
  -H "X-Bridge-Token: YOUR_BRIDGE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"agent_id":"BRIDGE-2024-DEMO","status":"online","queue_depth":0}'</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>Http::withHeaders(['X-Bridge-Token' =&gt; env('BRIDGE_TOKEN')])
    ->post('https://opescare.test/api/v1/bridge/heartbeat', [
        'agent_id'    =&gt; 'BRIDGE-2024-DEMO',
        'status'      =&gt; 'online',
        'queue_depth' =&gt; 0,
    ]);</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>import requests, os

requests.post('https://opescare.test/api/v1/bridge/heartbeat',
    headers={'X-Bridge-Token': os.environ['BRIDGE_TOKEN']},
    json={'agent_id': 'BRIDGE-2024-DEMO', 'status': 'online', 'queue_depth': 0}
)</pre>
    </div>
</div>

<div class="docs-page-nav">
    <a href="{{ route('docs.index') }}">← Getting Started</a>
    <a href="{{ route('docs.api') }}">Connect API →</a>
</div>

@endsection
