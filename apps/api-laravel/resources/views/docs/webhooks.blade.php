@extends('layouts.docs')
@section('title', 'Webhooks')
@section('content')

<h1>Webhooks</h1>
<p class="docs-lead">
    OpesCare pushes real-time events to your HTTPS endpoint whenever something meaningful
    happens — a new appointment, a lab result ready, a consent granted. Each delivery is
    signed with HMAC-SHA256 so you can verify it came from OpesCare.
</p>

<h2 id="subscribe">Subscribe to Events</h2>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="curl">cURL</button>
        <button class="docs-code-tab" data-lang="php">PHP</button>
        <button class="docs-code-tab" data-lang="js">JavaScript</button>
        <button class="docs-code-tab" data-lang="python">Python</button>
        <button class="docs-code-tab" data-lang="java">Java</button>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>curl -X POST https://opescare.test/api/v1/connect/webhooks/subscriptions \
  -H "Authorization: Bearer {access_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "endpoint_url": "https://your-system.example.com/opescare/webhook",
    "events": ["appointment.created", "lab_result.ready", "consent.granted"],
    "secret": "your-webhook-signing-secret"
  }'</pre>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>Http::withToken($accessToken)
    ->post('https://opescare.test/api/v1/connect/webhooks/subscriptions', [
        'endpoint_url' =&gt; 'https://your-system.example.com/opescare/webhook',
        'events'       =&gt; ['appointment.created', 'lab_result.ready', 'consent.granted'],
        'secret'       =&gt; env('OPESCARE_WEBHOOK_SECRET'),
    ]);</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>await fetch('https://opescare.test/api/v1/connect/webhooks/subscriptions', {
  method: 'POST',
  headers: {
    Authorization: `Bearer ${accessToken}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    endpoint_url: 'https://your-system.example.com/opescare/webhook',
    events: ['appointment.created', 'lab_result.ready', 'consent.granted'],
    secret: process.env.OPESCARE_WEBHOOK_SECRET,
  }),
});</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>requests.post(
    'https://opescare.test/api/v1/connect/webhooks/subscriptions',
    headers={'Authorization': f'Bearer {access_token}'},
    json={
        'endpoint_url': 'https://your-system.example.com/opescare/webhook',
        'events': ['appointment.created', 'lab_result.ready', 'consent.granted'],
        'secret': os.environ['OPESCARE_WEBHOOK_SECRET'],
    }
)</pre>
    </div>
    <div class="docs-code-pane" data-lang="java">
<pre>String body = """
    {
      "endpoint_url": "https://your-system.example.com/opescare/webhook",
      "events": ["appointment.created", "lab_result.ready"],
      "secret": "your-webhook-signing-secret"
    }""";
HttpRequest req = HttpRequest.newBuilder()
    .uri(URI.create("https://opescare.test/api/v1/connect/webhooks/subscriptions"))
    .header("Authorization", "Bearer " + accessToken)
    .header("Content-Type", "application/json")
    .POST(HttpRequest.BodyPublishers.ofString(body))
    .build();</pre>
    </div>
</div>

<h2 id="events-list">Event Types</h2>

<table class="docs-table">
    <thead><tr><th>Event</th><th>Triggered When</th></tr></thead>
    <tbody>
        <tr><td><code>appointment.created</code></td><td>A new appointment is booked</td></tr>
        <tr><td><code>appointment.updated</code></td><td>Appointment time, status, or doctor changes</td></tr>
        <tr><td><code>lab_result.ready</code></td><td>Lab results are finalised and available</td></tr>
        <tr><td><code>prescription.ready</code></td><td>Prescription is ready for collection at the pharmacy</td></tr>
        <tr><td><code>consent.granted</code></td><td>Patient grants consent to a provider</td></tr>
        <tr><td><code>payment.completed</code></td><td>Invoice is marked paid</td></tr>
        <tr><td><code>patient.registered</code></td><td>New patient registers at a facility</td></tr>
    </tbody>
</table>

<h2 id="payload">Payload Schema</h2>

<div class="docs-code-block">
    <div class="docs-code-tabs"><button class="docs-code-tab active" data-lang="json">JSON</button></div>
    <div class="docs-code-pane active" data-lang="json">
<pre>{
  "id": "evt_01HX9K2ABCD",
  "event": "appointment.created",
  "facility_id": "00000000-0000-0000-0000-100000000001",
  "timestamp": "2026-05-20T10:30:00Z",
  "data": {
    "appointment_id": "APT-202605200001",
    "patient_health_id": "OPC-2024-DEMO1",
    "scheduled_at": "2026-05-21T09:00:00Z",
    "doctor": "Dr. Amara Nwosu",
    "department": "Cardiology"
  }
}</pre>
    </div>
</div>

<h2 id="verification">Signature Verification</h2>

<p>
    Every delivery includes an <code>X-OpesCare-Signature</code> header.
    It is an HMAC-SHA256 digest of the raw request body using your webhook secret,
    prefixed with <code>sha256=</code>. <strong>Always verify this before processing.</strong>
</p>

<div class="docs-code-block">
    <div class="docs-code-tabs">
        <button class="docs-code-tab" data-lang="php">PHP (Laravel)</button>
        <button class="docs-code-tab" data-lang="js">JavaScript (Node/Express)</button>
        <button class="docs-code-tab" data-lang="python">Python (Flask)</button>
        <button class="docs-code-tab" data-lang="curl">Go</button>
        <button class="docs-code-tab" data-lang="java">Java</button>
    </div>
    <div class="docs-code-pane" data-lang="php">
<pre>// routes/api.php
Route::post('/opescare/webhook', function (\Illuminate\Http\Request $request) {
    $signature = $request->header('X-OpesCare-Signature');
    $body      = $request->getContent();
    $secret    = env('OPESCARE_WEBHOOK_SECRET');
    $expected  = 'sha256=' . hash_hmac('sha256', $body, $secret);

    if (!hash_equals($expected, $signature)) {
        abort(401, 'Invalid signature');
    }

    $payload = $request->json()->all();
    // Process $payload['event'] ...

    return response()->json(['received' => true]);
});</pre>
    </div>
    <div class="docs-code-pane" data-lang="js">
<pre>const crypto = require('crypto');
const express = require('express');
const app = express();

// IMPORTANT: use raw body for signature check
app.post('/opescare/webhook',
  express.raw({ type: 'application/json' }),
  (req, res) => {
    const sig      = req.headers['x-opescare-signature'];
    const secret   = process.env.OPESCARE_WEBHOOK_SECRET;
    const expected = 'sha256=' + crypto
      .createHmac('sha256', secret)
      .update(req.body)
      .digest('hex');

    if (!crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(sig))) {
      return res.status(401).send('Invalid signature');
    }

    const payload = JSON.parse(req.body);
    // Handle payload.event ...

    res.status(200).json({ received: true });
  }
);</pre>
    </div>
    <div class="docs-code-pane" data-lang="python">
<pre>import hmac, hashlib, os
from flask import Flask, request, abort, jsonify

app = Flask(__name__)

@app.route('/opescare/webhook', methods=['POST'])
def webhook():
    sig    = request.headers.get('X-OpesCare-Signature', '')
    secret = os.environ['OPESCARE_WEBHOOK_SECRET'].encode()
    body   = request.get_data()  # raw bytes — do not call request.json first

    expected = 'sha256=' + hmac.new(secret, body, hashlib.sha256).hexdigest()

    if not hmac.compare_digest(expected, sig):
        abort(401, 'Invalid signature')

    payload = request.json
    # Handle payload['event'] ...

    return jsonify({'received': True})</pre>
    </div>
    <div class="docs-code-pane" data-lang="curl">
<pre>// Go example
package main

import (
    "crypto/hmac"
    "crypto/sha256"
    "encoding/hex"
    "fmt"
    "io"
    "net/http"
    "os"
)

func verifyWebhook(body []byte, signature, secret string) bool {
    mac := hmac.New(sha256.New, []byte(secret))
    mac.Write(body)
    expected := "sha256=" + hex.EncodeToString(mac.Sum(nil))
    return hmac.Equal([]byte(expected), []byte(signature))
}

func webhookHandler(w http.ResponseWriter, r *http.Request) {
    body, _ := io.ReadAll(r.Body)
    sig := r.Header.Get("X-OpesCare-Signature")
    if !verifyWebhook(body, sig, os.Getenv("OPESCARE_WEBHOOK_SECRET")) {
        http.Error(w, "Invalid signature", http.StatusUnauthorized)
        return
    }
    fmt.Fprintln(w, `{"received":true}`)
}</pre>
    </div>
    <div class="docs-code-pane" data-lang="java">
<pre>import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.util.HexFormat;

public class WebhookVerifier {
    public static boolean verify(byte[] body, String signature, String secret)
            throws Exception {
        Mac mac = Mac.getInstance("HmacSHA256");
        mac.init(new SecretKeySpec(secret.getBytes(), "HmacSHA256"));
        String expected = "sha256=" + HexFormat.of().formatHex(mac.doFinal(body));
        return MessageDigest.isEqual(expected.getBytes(), signature.getBytes());
    }
}

// In your servlet / controller:
byte[] body = request.getInputStream().readAllBytes();
String sig   = request.getHeader("X-OpesCare-Signature");
if (!WebhookVerifier.verify(body, sig, System.getenv("OPESCARE_WEBHOOK_SECRET"))) {
    response.sendError(401, "Invalid signature");
    return;
}</pre>
    </div>
</div>

<h2 id="retry">Retry Policy</h2>

<p>
    OpesCare retries failed deliveries with exponential back-off.
    Your endpoint must return a <code>2xx</code> status within <strong>10 seconds</strong> to be considered successful.
</p>

<table class="docs-table">
    <thead><tr><th>Attempt</th><th>Delay Before Retry</th></tr></thead>
    <tbody>
        <tr><td>1 (initial)</td><td>Immediate</td></tr>
        <tr><td>2</td><td>1 second</td></tr>
        <tr><td>3</td><td>5 seconds</td></tr>
        <tr><td>4</td><td>30 seconds</td></tr>
        <tr><td>5 (final)</td><td>2 minutes</td></tr>
    </tbody>
</table>

<p>After 5 failed attempts the delivery is marked <code>exhausted</code>. No further retries occur. View delivery logs in the developer portal under <strong>Apps → Webhook Deliveries</strong>.</p>

<h2 id="testing">Testing Your Endpoint</h2>

<p>During development, use a tunnel tool like <a href="https://ngrok.com" rel="noopener noreferrer" target="_blank">ngrok</a> or <a href="https://expose.dev" rel="noopener noreferrer" target="_blank">expose</a> to receive webhooks on <code>localhost</code>:</p>

<div class="docs-code-block">
    <div class="docs-code-tabs"><button class="docs-code-tab active" data-lang="curl">Shell</button></div>
    <div class="docs-code-pane active" data-lang="curl">
<pre># Start a tunnel to your local server on port 8000
ngrok http 8000

# Then subscribe using the ngrok URL:
curl -X POST https://opescare.test/api/v1/connect/webhooks/subscriptions \
  -H "Authorization: Bearer {access_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "endpoint_url": "https://abc123.ngrok.io/opescare/webhook",
    "events": ["appointment.created"],
    "secret": "test-secret-123"
  }'</pre>
    </div>
</div>

<div class="docs-page-nav">
    <a href="{{ route('docs.widget') }}">← Widget</a>
    <a href="{{ route('docs.errors') }}">Errors →</a>
</div>

@endsection
