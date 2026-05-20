@extends('layouts.docs')
@section('title', 'SDK')
@section('content')

<h1>OpesCare SDK</h1>
<p class="docs-lead">
    The OpesCare SDK provides typed wrappers around the API for the most common operations.
    Install via Composer (PHP) or npm (JavaScript/Node.js) and start making calls in seconds.
</p>

<h2 id="installation">Installation</h2>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="php">PHP (Composer)</button>
        <button class="docs-code-tab" data-lang="js">JavaScript (npm)</button>
        <button class="docs-code-tab" data-lang="python">Python (pip)</button>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>composer require opescare/php-sdk</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>npm install @opescare/sdk
# or
yarn add @opescare/sdk</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>pip install opescare-sdk</pre>
    </div>
</div>

<h2 id="initialisation">Initialisation</h2>

<p>Create your SDK token in the developer portal under <strong>Apps → SDK Tokens</strong>. Pass it to the client.</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>use OpesCare\Sdk\OpesCareSDK;

$sdk = OpesCareSDK::init(token: env('OPESCARE_SDK_TOKEN'));</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>import { OpesCareSDK } from '@opescare/sdk';

const sdk = new OpesCareSDK({ token: process.env.OPESCARE_SDK_TOKEN });</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>from opescare import OpesCareSDK
import os

sdk = OpesCareSDK(token=os.environ['OPESCARE_SDK_TOKEN'])</pre>
    </div>
</div>

<h2 id="patient-methods">Patient Methods</h2>

<h3><code>patients().get(healthId)</code></h3>
<p>Calls <code>GET /sdk/patient/{health_id}</code>. Returns the patient profile.</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>$patient = $sdk->patients()->get('OPC-2024-DEMO1');

echo $patient->name;       // "Jean Dupont"
echo $patient->blood_type; // "O+"
echo $patient->health_id;  // "OPC-2024-DEMO1"</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>const patient = await sdk.patients.get('OPC-2024-DEMO1');

console.log(patient.name);        // "Jean Dupont"
console.log(patient.blood_type);  // "O+"
console.log(patient.health_id);   // "OPC-2024-DEMO1"</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>patient = sdk.patients.get('OPC-2024-DEMO1')

print(patient.name)        # "Jean Dupont"
print(patient.blood_type)  # "O+"</pre>
    </div>
</div>

<h2 id="facility-methods">Facility Methods</h2>

<h3><code>facility(facilityId).get()</code></h3>
<p>Calls <code>GET /sdk/facility/{facility_id}</code>.</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>$facility = $sdk->facility('FAC-001')->get();
echo $facility->name;     // "City General Hospital"
echo $facility->location; // "Yaoundé, Cameroon"</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>const facility = await sdk.facility('FAC-001').get();
console.log(facility.name);     // "City General Hospital"
console.log(facility.location); // "Yaoundé, Cameroon"</pre>
    </div>
</div>

<h2 id="appointment-methods">Appointments</h2>

<h3><code>appointments().list({ facilityId, date })</code></h3>
<p>Calls <code>GET /sdk/appointments</code> with query parameters.</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>$appointments = $sdk->appointments()->list(
    facilityId: 'FAC-001',
    date: '2026-05-20'
);
foreach ($appointments as $appt) {
    echo "{$appt->patient_name} at {$appt->time}\n";
}</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>const appointments = await sdk.appointments.list({
  facilityId: 'FAC-001',
  date: '2026-05-20',
});
appointments.forEach(a => console.log(`${a.patient_name} at ${a.time}`));</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>appointments = sdk.appointments.list(facility_id='FAC-001', date='2026-05-20')
for appt in appointments:
    print(f"{appt.patient_name} at {appt.time}")</pre>
    </div>
</div>

<h2 id="webhook-methods">Webhooks via SDK</h2>

<h3><code>webhooks().subscribe({ endpointUrl, events })</code></h3>
<p>Calls <code>POST /sdk/webhooks</code>.</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>$sdk->webhooks()->subscribe(
    endpointUrl: 'https://your-system.example.com/opescare/webhook',
    events: ['appointment.created', 'lab_result.ready']
);</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>await sdk.webhooks.subscribe({
  endpointUrl: 'https://your-system.example.com/opescare/webhook',
  events: ['appointment.created', 'lab_result.ready'],
});</pre>
    </div>
</div>

<h2 id="introspect">Token Introspection</h2>

<p>Check if your SDK token is valid and see its scopes and expiry:</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="curl">cURL</button>
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>curl https://opescare.test/api/v1/sdk/introspect \
  -H "Authorization: Bearer YOUR_SDK_TOKEN"</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>$info = $sdk->introspect();
// {
//   "active": true,
//   "scopes": ["patient:profile:read", "pharmacy:stock:read"],
//   "expires_at": "2027-01-01T00:00:00Z"
// }</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>const info = await sdk.introspect();
// { active: true, scopes: ['patient:profile:read'], expires_at: '...' }</pre>
    </div>
</div>

<div class="docs-page-nav">
    <a href="{{ route('docs.api') }}">← Connect API</a>
    <a href="{{ route('docs.bridge') }}">Bridge Agent →</a>
</div>

@endsection
