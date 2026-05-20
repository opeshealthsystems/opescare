@extends('layouts.docs')
@section('title', 'Bridge Agent')
@section('content')

<h1>Bridge Agent</h1>
<p class="docs-lead">
    The Bridge Agent is a lightweight daemon you deploy on-premise next to your Hospital
    Information System (HIS). It continuously syncs visits, vitals, diagnoses, and lab results
    to OpesCare — without requiring your HIS to be internet-accessible.
</p>

<h2 id="how-it-works">How it Works</h2>
<ol>
    <li>Register a Bridge Agent in the developer portal — receive a <code>X-Bridge-Token</code>.</li>
    <li>Deploy the agent on any server with access to your HIS and the internet.</li>
    <li>The agent reads from your HIS and POSTs records to <code>/api/v1/bridge/sync</code>.</li>
    <li>Every 5 minutes the agent sends a heartbeat so OpesCare knows it is alive.</li>
    <li>Query agent status at any time from <code>/api/v1/bridge/status</code>.</li>
</ol>

<h2 id="setup">Setup & Registration</h2>

<p>In the developer portal, navigate to <strong>Apps → Bridge Agents → New Agent</strong>. Provide:</p>
<ul>
    <li><strong>Agent name</strong> — e.g. <code>City Hospital HIS Sync</code></li>
    <li><strong>HIS type</strong> — e.g. <code>OpenMRS</code>, <code>DHIS2</code>, <code>Custom</code></li>
    <li><strong>Facility ID</strong> — the OpesCare facility this agent belongs to</li>
</ul>

<p>After registration you will receive a <code>bridge_token</code>. Store it in an environment variable — it cannot be recovered after the portal closes it.</p>

<h2 id="sync">Sync Endpoint</h2>

<div class="endpoint-pill">
    <span class="method-badge method-post">POST</span>
    <span class="endpoint-path">/api/v1/bridge/sync</span>
</div>

<p>Push a batch of health records. Use <code>sync_type: delta</code> for incremental pushes (only new/changed records), <code>full</code> for a complete resync.</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="curl">cURL</button>
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
        <button class="docs-code-tab" data-lang="java">Java</button>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>curl -X POST https://opescare.test/api/v1/bridge/sync \
  -H "X-Bridge-Token: YOUR_BRIDGE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "agent_id": "BRIDGE-2024-DEMO-001",
    "sync_type": "delta",
    "records": [
      {
        "type": "visit",
        "data": {
          "patient_health_id": "OPC-2024-DEMO1",
          "visit_date": "2026-05-20",
          "chief_complaint": "Chest pain",
          "attending_doctor": "Dr. Nguyen"
        }
      },
      {
        "type": "vital",
        "data": {
          "patient_health_id": "OPC-2024-DEMO1",
          "recorded_at": "2026-05-20T09:15:00Z",
          "systolic_bp": 120,
          "diastolic_bp": 80,
          "heart_rate": 72,
          "temperature": 36.8
        }
      }
    ]
  }'</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>Http::withHeaders(['X-Bridge-Token' =&gt; env('BRIDGE_TOKEN')])
    ->post('https://opescare.test/api/v1/bridge/sync', [
        'agent_id'  =&gt; 'BRIDGE-2024-DEMO-001',
        'sync_type' =&gt; 'delta',
        'records'   =&gt; [
            [
                'type' =&gt; 'visit',
                'data' =&gt; [
                    'patient_health_id' =&gt; 'OPC-2024-DEMO1',
                    'visit_date'        =&gt; '2026-05-20',
                    'chief_complaint'   =&gt; 'Chest pain',
                    'attending_doctor'  =&gt; 'Dr. Nguyen',
                ],
            ],
            [
                'type' =&gt; 'vital',
                'data' =&gt; [
                    'patient_health_id' =&gt; 'OPC-2024-DEMO1',
                    'recorded_at'       =&gt; '2026-05-20T09:15:00Z',
                    'systolic_bp'       =&gt; 120,
                    'diastolic_bp'      =&gt; 80,
                    'heart_rate'        =&gt; 72,
                    'temperature'       =&gt; 36.8,
                ],
            ],
        ],
    ]);</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>await fetch('https://opescare.test/api/v1/bridge/sync', {
  method: 'POST',
  headers: {
    'X-Bridge-Token': process.env.BRIDGE_TOKEN,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    agent_id: 'BRIDGE-2024-DEMO-001',
    sync_type: 'delta',
    records: [
      {
        type: 'visit',
        data: {
          patient_health_id: 'OPC-2024-DEMO1',
          visit_date: '2026-05-20',
          chief_complaint: 'Chest pain',
          attending_doctor: 'Dr. Nguyen',
        },
      },
      {
        type: 'vital',
        data: {
          patient_health_id: 'OPC-2024-DEMO1',
          recorded_at: '2026-05-20T09:15:00Z',
          systolic_bp: 120,
          diastolic_bp: 80,
          heart_rate: 72,
          temperature: 36.8,
        },
      },
    ],
  }),
});</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>import requests, os

requests.post(
    'https://opescare.test/api/v1/bridge/sync',
    headers={'X-Bridge-Token': os.environ['BRIDGE_TOKEN']},
    json={
        'agent_id': 'BRIDGE-2024-DEMO-001',
        'sync_type': 'delta',
        'records': [
            {
                'type': 'visit',
                'data': {
                    'patient_health_id': 'OPC-2024-DEMO1',
                    'visit_date': '2026-05-20',
                    'chief_complaint': 'Chest pain',
                    'attending_doctor': 'Dr. Nguyen',
                },
            },
            {
                'type': 'vital',
                'data': {
                    'patient_health_id': 'OPC-2024-DEMO1',
                    'recorded_at': '2026-05-20T09:15:00Z',
                    'systolic_bp': 120,
                    'diastolic_bp': 80,
                    'heart_rate': 72,
                    'temperature': 36.8,
                },
            },
        ],
    }
)</pre>
    </div>
    <div class="docs-code-pane" data-lang="java">
<pre>String body = """
    {
      "agent_id": "BRIDGE-2024-DEMO-001",
      "sync_type": "delta",
      "records": [
        {"type": "visit", "data": {
          "patient_health_id": "OPC-2024-DEMO1",
          "visit_date": "2026-05-20",
          "chief_complaint": "Chest pain"
        }}
      ]
    }""";

HttpRequest req = HttpRequest.newBuilder()
    .uri(URI.create("https://opescare.test/api/v1/bridge/sync"))
    .header("X-Bridge-Token", System.getenv("BRIDGE_TOKEN"))
    .header("Content-Type", "application/json")
    .POST(HttpRequest.BodyPublishers.ofString(body))
    .build();
HttpResponse&lt;String&gt; res = HttpClient.newHttpClient()
    .send(req, HttpResponse.BodyHandlers.ofString());</pre>
    </div>
</div>

<p><strong>Response:</strong></p>
<div class="docs-code-block">
    <div class="docs-code-tabs"><button class="docs-code-tab active" data-lang="json">JSON</button></div>
    <div class="docs-code-pane active" data-lang="json">
<pre>{
  "accepted": 2,
  "rejected": 0,
  "sync_id": "sync_a8f3k12x"
}</pre>
    </div>
</div>

<h2 id="supported-record-types">Supported Record Types</h2>

<table class="docs-table">
    <thead><tr><th>Type</th><th>Description</th><th>Key Fields</th></tr></thead>
    <tbody>
        <tr><td><code>visit</code></td><td>Outpatient or inpatient visit</td><td>patient_health_id, visit_date, chief_complaint</td></tr>
        <tr><td><code>vital</code></td><td>Vital signs measurement</td><td>patient_health_id, recorded_at, systolic_bp, heart_rate, temperature</td></tr>
        <tr><td><code>diagnosis</code></td><td>Clinical diagnosis (ICD-10 code)</td><td>patient_health_id, icd10_code, description</td></tr>
        <tr><td><code>lab_result</code></td><td>Laboratory test result</td><td>patient_health_id, test_name, value, unit, reference_range</td></tr>
        <tr><td><code>prescription</code></td><td>Prescribed medication</td><td>patient_health_id, drug_name, dose, frequency, duration_days</td></tr>
    </tbody>
</table>

<h2 id="heartbeat">Heartbeat</h2>

<div class="endpoint-pill">
    <span class="method-badge method-post">POST</span>
    <span class="endpoint-path">/api/v1/bridge/heartbeat</span>
</div>

<p>Send every 5 minutes. Agents silent for 15+ minutes are marked <code>offline</code> and the portal generates an alert.</p>

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
  -d '{"agent_id":"BRIDGE-2024-DEMO-001","status":"online","queue_depth":0}'</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>Http::withHeaders(['X-Bridge-Token' =&gt; env('BRIDGE_TOKEN')])
    ->post('https://opescare.test/api/v1/bridge/heartbeat', [
        'agent_id'    =&gt; 'BRIDGE-2024-DEMO-001',
        'status'      =&gt; 'online',
        'queue_depth' =&gt; 0,
    ]);</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>requests.post('https://opescare.test/api/v1/bridge/heartbeat',
    headers={'X-Bridge-Token': os.environ['BRIDGE_TOKEN']},
    json={'agent_id': 'BRIDGE-2024-DEMO-001', 'status': 'online', 'queue_depth': 0}
)</pre>
    </div>
</div>

<h2 id="status">Status</h2>

<div class="endpoint-pill">
    <span class="method-badge method-get">GET</span>
    <span class="endpoint-path">/api/v1/bridge/status?agent_id={agent_id}</span>
</div>

<div class="docs-code-block">
    <div class="docs-code-tabs"><button class="docs-code-tab active" data-lang="json">Response</button></div>
    <div class="docs-code-pane active" data-lang="json">
<pre>{
  "agent_id": "BRIDGE-2024-DEMO-001",
  "status": "online",
  "last_sync_at": "2026-05-20T09:30:00Z",
  "last_heartbeat_at": "2026-05-20T09:32:00Z",
  "records_synced_today": 127
}</pre>
    </div>
</div>

<div class="docs-page-nav">
    <a href="{{ route('docs.sdk') }}">← SDK</a>
    <a href="{{ route('docs.widget') }}">Widget →</a>
</div>

@endsection
