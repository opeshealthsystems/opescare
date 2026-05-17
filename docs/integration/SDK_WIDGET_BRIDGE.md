# OpesCare Connect: SDK, Widget, & Bridge Agent

We provide a complete suite of developer toolkits and connectors to simplify clinical integration.

---

## 1. SDK Design & Usage (PHP, TS, Python)

Our SDK libraries handle authentication token refreshes, automate idempotency-key generation, verify webhook signatures, and enforce standard clinical error mapping.

### PHP SDK Example
```php
use OpesCare\Client;

$client = new Client([
    'client_id' => getenv('OPESCARE_CLIENT_ID'),
    'client_secret' => getenv('OPESCARE_CLIENT_SECRET'),
    'environment' => 'sandbox'
]);

// Search patient securely
$result = $client->patients()->search([
    'search_type' => 'health_id',
    'query' => 'OC-CMR-7KQ9-MP42-X8D1',
    'purpose' => 'treatment'
]);
```

### TypeScript SDK Example
```ts
import { OpesCareClient } from '@opescare/connect-sdk';

const client = new OpesCareClient({
  clientId: process.env.OPESCARE_CLIENT_ID,
  clientSecret: process.env.OPESCARE_CLIENT_SECRET,
  environment: 'sandbox'
});

const result = await client.records.pushEncounter({
  healthId: 'OC-CMR-7KQ9-MP42-X8D1',
  externalEncounterId: 'ENC-9001',
  encounter: {
    type: 'outpatient',
    startedAt: new Date().toISOString()
  }
});
```

---

## 2. Secure Front-End Embeddable Widgets

For clinics or provider software that cannot manage deep database integrations, OpesCare provides an embeddable HTML **Connect Widget**.

### Widget Session Flow
1. **Hospital Backend**: Calls `POST /api/v1/connect/widget/sessions` to get a short-lived widget token.
2. **Web Browser**: Loads OpesCare widget in a secure iframe utilizing the widget token.

```html
<!-- Secure Iframe embed -->
<iframe 
  src="https://widget.connect.opescare.com/v1/embed?session_token=wgt_session_123xyz" 
  width="100%" 
  height="600px" 
  frameborder="0" 
  allow="camera; geolocation">
</iframe>
```

---

## 3. OpesCare Bridge Agent

The **Bridge Agent** is a lightweight, local daemon installed on the clinic network to integrate legacy database platforms, CSV watch folders, or SFTP dispatches.

### Watching Folders for CSV Imports
Legacy LIS or HIS systems typically output patient files as static CSV files. The Bridge Agent watches a designated directory and queues files locally.

```json
{
  "agent_id": "brg_agent_st_jude_01",
  "environment": "sandbox",
  "folder_watch": {
    "directory": "C:\\LegacyHIS\\exports",
    "file_pattern": "*.csv",
    "field_mapping": {
      "patient_id": "MRN",
      "first_name": "FNAME",
      "last_name": "LNAME",
      "encounter_notes": "NOTES"
    }
  }
}
```
*   **Offline Queue**: If local internet cuts, the Bridge Agent caches items in an encrypted SQLite database and pushes them when connectivity recovers.
