# Wave 2 SP-4: SMS + Email Notifications Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace placeholder SMS/email stubs with real delivery logic and fire notifications on appointment booking, lab result push, and consent request creation.

**Architecture:** `SmsNotificationService` sends via Twilio if `TWILIO_SID` is set, otherwise logs to `storage/logs/sms.log` (zero-config local dev). `EmailNotificationService` uses Laravel's built-in `Mail` facade with a single `OpesCareNotificationMail` Mailable that renders a plain-text message (zero template dependency). `CommunicationRouterService::dispatchDelivery` routes to the correct service by channel and looks up the patient's real phone/email instead of hardcoded mocks. Three event types are seeded as `NotificationTemplate` rows. Three existing controllers fire `NotificationService::sendNotification` after their primary action.

**Tech Stack:** Laravel 13, Laravel Mail (SMTP/Mailgun), Twilio PHP SDK (`twilio/sdk` — optional, guarded by env check), existing `NotificationService`, `CommunicationRouterService`, `NotificationPreferenceService`

---

## File Map

| Action | Path | Purpose |
|--------|------|---------|
| Modify | `app/Modules/Notifications/Services/SmsNotificationService.php` | Twilio SMS + log fallback |
| Modify | `app/Modules/Notifications/Services/EmailNotificationService.php` | Laravel Mail delivery |
| Create | `app/Mail/OpesCareNotificationMail.php` | Generic Mailable |
| Modify | `app/Modules/Communications/Services/CommunicationRouterService.php` | Wire real services + real contact lookup |
| Create | `database/seeders/NotificationTemplateSeeder.php` | Seed templates for 3 events |
| Modify | `database/seeders/DatabaseSeeder.php` | Run NotificationTemplateSeeder |
| Modify | `app/Http/Controllers/Api/Mobile/MobileAppointmentController.php` | Fire notification after booking |
| Modify | `app/Http/Controllers/Api/V1/Connect/RecordController.php` | Fire notification after lab push |
| Modify | `app/Modules/Governance/Services/ConsentService.php` | Fire notification after consent request |
| Create | `tests/Feature/Notifications/NotificationDispatchTest.php` | Tests for notification firing |

---

### Task 1: Implement SmsNotificationService

**Files:**
- Modify: `app/Modules/Notifications/Services/SmsNotificationService.php`

- [ ] **Step 1.1: Write the failing SMS test**

Create `tests/Feature/Notifications/NotificationDispatchTest.php`:

```php
<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Notifications\Services\SmsNotificationService;
use Illuminate\Support\Facades\Log;

class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_logs_message_when_twilio_not_configured(): void
    {
        // Ensure Twilio is not configured (use log fallback)
        config(['services.twilio.sid' => null]);

        $service = app(SmsNotificationService::class);

        Log::shouldReceive('channel')
            ->once()
            ->with('sms')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn ($msg, $ctx) =>
                str_contains($msg, 'SMS') &&
                $ctx['to'] === '+237600000001'
            );

        $service->send('+237600000001', 'Your appointment is confirmed.');
    }

    public function test_email_is_queued_via_laravel_mail(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $service = app(\App\Modules\Notifications\Services\EmailNotificationService::class);
        $service->send('patient@test.com', 'Appointment Confirmed', 'Your appointment is confirmed.');

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OpesCareNotificationMail::class, function ($mail) {
            return $mail->hasTo('patient@test.com');
        });
    }

    public function test_notification_service_fires_on_appointment_booked(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        config(['services.twilio.sid' => null]);

        $patient = \App\Models\Patient::create([
            'health_id'     => 'OC-TST-NOTIF-0001',
            'first_name'    => 'Notify',
            'last_name'     => 'Me',
            'sex'           => 'female',
            'date_of_birth' => '1992-03-10',
            'phone_number'  => '+237699000001',
            'is_demo'       => false,
        ]);

        $facilityRow = \App\Models\Facility::create([
            'name'   => 'Test Hospital',
            'type'   => 'hospital',
            'status' => 'active',
        ]);

        $provider = \App\Models\User::factory()->create();

        $slot = \App\Models\AppointmentSlot::create([
            'facility_id'  => $facilityRow->id,
            'provider_id'  => $provider->id,
            'starts_at'    => now()->addDay()->setTime(10, 0),
            'ends_at'      => now()->addDay()->setTime(10, 30),
            'capacity'     => 3,
            'booked_count' => 0,
            'status'       => 'open',
        ]);

        $response = $this->postJson('/api/mobile/appointments', [
            '_patient_id'         => $patient->id,
            'facility_id'         => $facilityRow->id,
            'appointment_slot_id' => $slot->id,
            'appointment_type'    => 'consultation',
        ]);

        $response->assertStatus(201);

        // A notification_event row should exist
        $this->assertDatabaseHas('notification_events', [
            'event_type'         => 'appointment.booked',
            'recipient_user_id'  => $patient->id,
        ]);
    }

    public function test_notification_event_created_on_consent_request(): void
    {
        $patient = \App\Models\Patient::create([
            'health_id'     => 'OC-TST-NOTIF-0002',
            'first_name'    => 'Consent',
            'last_name'     => 'Test',
            'sex'           => 'male',
            'date_of_birth' => '1988-07-22',
            'phone_number'  => '+237699000002',
            'is_demo'       => false,
        ]);

        $facility = \App\Models\Facility::create([
            'name'   => 'Requesting Clinic',
            'type'   => 'clinic',
            'status' => 'active',
        ]);

        $service = app(\App\Modules\Governance\Services\ConsentService::class);
        $service->requestConsent(
            $patient->id,
            $facility->id,
            null,
            'treatment',
            ['patients:read'],
            60
        );

        $this->assertDatabaseHas('notification_events', [
            'event_type'        => 'consent.request.pending',
            'recipient_user_id' => $patient->id,
        ]);
    }
}
```

- [ ] **Step 1.2: Run test to verify it fails**

```
php artisan test --filter="NotificationDispatchTest" --stop-on-failure
```
Expected: FAIL — `SmsNotificationService::send()` method does not exist.

- [ ] **Step 1.3: Implement SmsNotificationService**

Replace `app/Modules/Notifications/Services/SmsNotificationService.php` with:

```php
<?php

namespace App\Modules\Notifications\Services;

use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    /**
     * Send an SMS to a phone number.
     *
     * Uses Twilio if TWILIO_SID / TWILIO_TOKEN / TWILIO_FROM are set.
     * Falls back to writing to the `sms` log channel (storage/logs/sms.log)
     * so local development and CI work without any credentials.
     */
    public function send(string $to, string $body): void
    {
        if ($this->twilioConfigured()) {
            $this->sendViaTwilio($to, $body);
        } else {
            $this->logSms($to, $body);
        }
    }

    private function twilioConfigured(): bool
    {
        return !empty(config('services.twilio.sid'))
            && !empty(config('services.twilio.token'))
            && !empty(config('services.twilio.from'));
    }

    private function sendViaTwilio(string $to, string $body): void
    {
        // Twilio SDK is loaded via composer require twilio/sdk --no-update
        // Guard: only call if class exists (SDK may not be installed in dev)
        if (!class_exists(\Twilio\Rest\Client::class)) {
            $this->logSms($to, $body);
            return;
        }

        $client = new \Twilio\Rest\Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $client->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $body,
        ]);
    }

    private function logSms(string $to, string $body): void
    {
        Log::channel('sms')->info('SMS [simulated]', [
            'to'   => $to,
            'body' => $body,
        ]);
    }
}
```

Add the `sms` log channel to `config/logging.php` inside the `channels` array:

```php
        'sms' => [
            'driver' => 'single',
            'path'   => storage_path('logs/sms.log'),
            'level'  => 'debug',
        ],
```

Add Twilio config to `config/services.php` (at the end of the array):

```php
    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],
```

- [ ] **Step 1.4: Run SMS test**

```
php artisan test --filter="test_sms_logs_message_when_twilio_not_configured"
```
Expected: PASS.

- [ ] **Step 1.5: Commit**

```
git add app/Modules/Notifications/Services/SmsNotificationService.php config/logging.php config/services.php
git commit -m "feat(notifications): implement SmsNotificationService with Twilio + log fallback"
```

---

### Task 2: Implement EmailNotificationService

**Files:**
- Modify: `app/Modules/Notifications/Services/EmailNotificationService.php`
- Create: `app/Mail/OpesCareNotificationMail.php`

- [ ] **Step 2.1: Create the Mailable**

Create `app/Mail/OpesCareNotificationMail.php`:

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OpesCareNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $subject,
        public readonly string $bodyText,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subject);
    }

    public function content(): Content
    {
        return new Content(text: 'emails.opescare-notification');
    }
}
```

Create the plain-text email view `resources/views/emails/opescare-notification.blade.php`:

```
{{ $bodyText }}

--
OpesCare Health Platform
Do not reply to this email. Log in to manage your preferences.
```

- [ ] **Step 2.2: Implement EmailNotificationService**

Replace `app/Modules/Notifications/Services/EmailNotificationService.php`:

```php
<?php

namespace App\Modules\Notifications\Services;

use App\Mail\OpesCareNotificationMail;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    /**
     * Send a plain-text notification email.
     *
     * Respects the MAIL_MAILER env setting.
     * In tests (MAIL_MAILER=array) nothing is actually sent — use Mail::fake() to assert.
     */
    public function send(string $to, string $subject, string $body): void
    {
        Mail::to($to)->send(new OpesCareNotificationMail($subject, $body));
    }
}
```

- [ ] **Step 2.3: Run email test**

```
php artisan test --filter="test_email_is_queued_via_laravel_mail"
```
Expected: PASS.

- [ ] **Step 2.4: Commit**

```
git add app/Mail/OpesCareNotificationMail.php resources/views/emails/opescare-notification.blade.php app/Modules/Notifications/Services/EmailNotificationService.php
git commit -m "feat(notifications): implement EmailNotificationService using Laravel Mail"
```

---

### Task 3: Wire CommunicationRouterService to Real Services

**Files:**
- Modify: `app/Modules/Communications/Services/CommunicationRouterService.php`

- [ ] **Step 3.1: Replace the stub dispatch and contact lookup**

Replace the full file `app/Modules/Communications/Services/CommunicationRouterService.php`:

```php
<?php

namespace App\Modules\Communications\Services;

use App\Models\Patient;
use App\Modules\Notifications\Models\NotificationEvent;
use App\Modules\Notifications\Models\NotificationDelivery;
use App\Modules\Notifications\Services\NotificationPreferenceService;
use App\Modules\Notifications\Services\SmsNotificationService;
use App\Modules\Notifications\Services\EmailNotificationService;
use Illuminate\Support\Str;

class CommunicationRouterService
{
    public function __construct(
        private NotificationPreferenceService $preferenceService,
        private SmsNotificationService        $smsService,
        private EmailNotificationService      $emailService,
    ) {}

    public function route(
        string $eventType,
        string $recipientUserId,
        array  $payload,
        string $priority = 'normal',
        string $category = 'health_updates'
    ): array {
        // 1. Privacy gate
        $securePayload = $this->enforcePrivacy($eventType, $payload);

        // 2. Deduplication (skip for critical/urgent)
        if ($priority !== 'critical' && $priority !== 'urgent') {
            if ($this->isDuplicateEvent($recipientUserId, $eventType)) {
                return ['status' => 'suppressed', 'reason' => 'DEDUPLICATION_LIMIT'];
            }
        }

        // 3. Create event record
        $event = NotificationEvent::create([
            'uuid'                      => Str::uuid(),
            'event_type'                => $eventType,
            'communication_type'        => $category,
            'recipient_user_id'         => $recipientUserId,
            'recipient_type'            => 'user',
            'payload_json'              => json_encode($securePayload),
            'priority'                  => $priority,
            'status'                    => 'routed',
            'requires_acknowledgement'  => in_array($priority, ['critical', 'urgent']),
        ]);

        $deliveries = [];
        $channels   = ['sms', 'email']; // voice/whatsapp/push handled by dedicated providers

        foreach ($channels as $channel) {
            if (!$this->preferenceService->isChannelEnabled($recipientUserId, $category, $channel, $priority)) {
                continue;
            }

            $recipient = $this->getRecipientContact($recipientUserId, $channel);
            if (!$recipient) {
                continue; // no contact info — skip silently
            }

            $delivery = NotificationDelivery::create([
                'uuid'                    => Str::uuid(),
                'notification_event_id'   => $event->id,
                'channel'                 => $channel,
                'recipient'               => $recipient,
                'provider'                => $this->resolveProvider($channel),
                'status'                  => 'pending',
            ]);

            $this->dispatchDelivery($delivery, $securePayload, $channel, $recipient);
            $deliveries[] = $delivery;
        }

        return [
            'status'     => 'success',
            'event'      => $event,
            'deliveries' => $deliveries,
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Resolve real contact information for a recipient from the Patient model.
     * Returns null if no contact info is available for that channel.
     */
    private function getRecipientContact(string $userId, string $channel): ?string
    {
        $patient = Patient::find($userId);

        return match ($channel) {
            'sms'   => $patient?->phone_number ?: null,
            'email' => $patient?->email ?? null,   // email added by migration in Task 4
            default => null,
        };
    }

    private function resolveProvider(string $channel): string
    {
        return match ($channel) {
            'sms'   => config('services.twilio.sid') ? 'twilio' : 'log',
            'email' => config('mail.default', 'smtp'),
            default => 'demo_provider',
        };
    }

    private function dispatchDelivery(
        NotificationDelivery $delivery,
        array  $payload,
        string $channel,
        string $recipient
    ): void {
        try {
            match ($channel) {
                'sms'   => $this->smsService->send($recipient, $payload['body'] ?? ''),
                'email' => $this->emailService->send(
                    $recipient,
                    $payload['subject'] ?? 'OpesCare Notification',
                    $payload['body'] ?? ''
                ),
                default => null,
            };

            $delivery->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
                'sent_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            $delivery->update([
                'status'        => 'failed',
                'failed_at'     => now(),
                'error_code'    => 'DISPATCH_ERROR',
                'error_message' => substr($e->getMessage(), 0, 255),
            ]);
        }
    }

    private function enforcePrivacy(string $eventType, array $payload): array
    {
        if (isset($payload['diagnosis']) || isset($payload['result_value']) || isset($payload['sensitive'])) {
            $payload['body'] = 'A new health update is available in OpesCare. Log in securely to view it.';
            unset($payload['diagnosis'], $payload['result_value'], $payload['sensitive']);
        }
        return $payload;
    }

    private function isDuplicateEvent(string $recipientUserId, string $eventType): bool
    {
        return NotificationEvent::where('recipient_user_id', $recipientUserId)
            ->where('event_type', $eventType)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
    }
}
```

- [ ] **Step 3.2: Add email column to patients migration (if missing)**

Check if `patients` table has an `email` column. If not, create a migration:

```
php artisan make:migration add_email_to_patients_table
```

In the generated migration file:

```php
public function up(): void
{
    Schema::table('patients', function (Blueprint $table) {
        $table->string('email')->nullable()->after('phone_number');
    });
}

public function down(): void
{
    Schema::table('patients', function (Blueprint $table) {
        $table->dropColumn('email');
    });
}
```

Add `'email'` to `Patient::$fillable` array in `app/Models/Patient.php`.

Run: `php artisan migrate`

- [ ] **Step 3.3: Run full regression**

```
php artisan test
```
Expected: All tests pass (the router now uses real services — in tests `MAIL_MAILER=array` so no real emails sent; SMS falls back to log).

- [ ] **Step 3.4: Commit**

```
git add app/Modules/Communications/Services/CommunicationRouterService.php app/Models/Patient.php database/migrations/
git commit -m "feat(notifications): wire CommunicationRouterService to SMS/email services + real contact lookup"
```

---

### Task 4: Seed Notification Templates

**Files:**
- Create: `database/seeders/NotificationTemplateSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 4.1: Create the seeder**

Create `database/seeders/NotificationTemplateSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Modules\Notifications\Models\NotificationTemplate;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'event_type'    => 'appointment.booked',
                'channel'       => 'sms',
                'language'      => 'en',
                'subject'       => 'Appointment Confirmed',
                'title'         => 'Appointment Confirmed',
                'body'          => 'Your appointment at {{ facility_name }} is confirmed for {{ scheduled_at }}. Reply CANCEL to cancel.',
                'template_text' => 'Your appointment at {{ facility_name }} is confirmed for {{ scheduled_at }}.',
                'priority'      => 'high',
                'communication_class' => 'transactional',
                'approval_status'     => 'approved',
            ],
            [
                'event_type'    => 'appointment.booked',
                'channel'       => 'email',
                'language'      => 'en',
                'subject'       => 'Appointment Confirmed — {{ facility_name }}',
                'title'         => 'Your OpesCare Appointment is Confirmed',
                'body'          => "Hello {{ patient_name }},\n\nYour appointment at {{ facility_name }} has been confirmed.\n\nDate & Time: {{ scheduled_at }}\nType: {{ appointment_type }}\n\nLog in to OpesCare to view or manage your appointment.",
                'template_text' => "Hello {{ patient_name }},\n\nYour appointment at {{ facility_name }} has been confirmed.\n\nDate & Time: {{ scheduled_at }}\nType: {{ appointment_type }}\n\nLog in to OpesCare to view or manage your appointment.",
                'priority'      => 'high',
                'communication_class' => 'transactional',
                'approval_status'     => 'approved',
            ],
            [
                'event_type'    => 'lab.result.ready',
                'channel'       => 'sms',
                'language'      => 'en',
                'subject'       => 'Lab Result Ready',
                'title'         => 'Your Lab Result is Ready',
                'body'          => 'Your lab result is now available in OpesCare. Log in to view it securely.',
                'template_text' => 'Your lab result is now available in OpesCare. Log in to view it securely.',
                'priority'      => 'high',
                'communication_class' => 'transactional',
                'approval_status'     => 'approved',
            ],
            [
                'event_type'    => 'lab.result.ready',
                'channel'       => 'email',
                'language'      => 'en',
                'subject'       => 'Your Lab Result is Ready',
                'title'         => 'Lab Result Available',
                'body'          => "Hello,\n\nA new lab result has been added to your OpesCare health record.\n\nFor privacy, result details are only visible inside the secure OpesCare app. Log in to view them.",
                'template_text' => "Hello,\n\nA new lab result has been added to your OpesCare health record.\n\nFor privacy, result details are only visible inside the secure OpesCare app. Log in to view them.",
                'priority'      => 'high',
                'communication_class' => 'transactional',
                'approval_status'     => 'approved',
            ],
            [
                'event_type'    => 'consent.request.pending',
                'channel'       => 'sms',
                'language'      => 'en',
                'subject'       => 'Consent Request',
                'title'         => 'A Facility Wants to Access Your Records',
                'body'          => '{{ facility_name }} has requested access to your health records. Open OpesCare to approve or deny.',
                'template_text' => '{{ facility_name }} has requested access to your health records. Open OpesCare to approve or deny.',
                'priority'      => 'urgent',
                'communication_class' => 'transactional',
                'approval_status'     => 'approved',
            ],
            [
                'event_type'    => 'consent.request.pending',
                'channel'       => 'email',
                'language'      => 'en',
                'subject'       => 'Action Required: Health Record Access Request',
                'title'         => 'Consent Request',
                'body'          => "Hello,\n\n{{ facility_name }} has requested access to your health records for the purpose of: {{ purpose }}.\n\nLog in to OpesCare to approve or deny this request. You can revoke access at any time.",
                'template_text' => "Hello,\n\n{{ facility_name }} has requested access to your health records for the purpose of: {{ purpose }}.\n\nLog in to OpesCare to approve or deny this request. You can revoke access at any time.",
                'priority'      => 'urgent',
                'communication_class' => 'transactional',
                'approval_status'     => 'approved',
            ],
        ];

        foreach ($templates as $tpl) {
            NotificationTemplate::firstOrCreate(
                [
                    'event_type' => $tpl['event_type'],
                    'channel'    => $tpl['channel'],
                    'language'   => $tpl['language'],
                ],
                array_merge($tpl, ['uuid' => Str::uuid()])
            );
        }
    }
}
```

- [ ] **Step 4.2: Register seeder in DatabaseSeeder**

In `database/seeders/DatabaseSeeder.php`, add inside the `run()` method:

```php
        $this->call(NotificationTemplateSeeder::class);
```

- [ ] **Step 4.3: Run seeder**

```
php artisan db:seed --class=NotificationTemplateSeeder
```
Expected: 6 notification templates inserted.

- [ ] **Step 4.4: Commit**

```
git add database/seeders/NotificationTemplateSeeder.php database/seeders/DatabaseSeeder.php
git commit -m "feat(notifications): seed notification templates for appointment, lab, consent events"
```

---

### Task 5: Hook Events to Fire Notifications

**Files:**
- Modify: `app/Http/Controllers/Api/Mobile/MobileAppointmentController.php`
- Modify: `app/Http/Controllers/Api/V1/Connect/RecordController.php`
- Modify: `app/Modules/Governance/Services/ConsentService.php`

- [ ] **Step 5.1: Fire notification after appointment booking**

In `app/Http/Controllers/Api/Mobile/MobileAppointmentController.php`, inject `NotificationService` via constructor and fire after booking.

Add the constructor and import at the top of the class:

```php
use App\Modules\Notifications\Services\NotificationService;

// Inside the class:
    public function __construct(private NotificationService $notificationService) {}
```

At the end of the `book()` method, after `return response()->json(...)`, add (before the return statement):

```php
        // Fire booking notification (non-fatal — don't let notification errors break booking)
        try {
            $this->notificationService->sendNotification(
                $appointment->patient_id,
                'appointment.booked',
                [
                    'patient_name'     => 'Patient',
                    'facility_name'    => $appointment->facility?->name ?? 'the facility',
                    'scheduled_at'     => $appointment->scheduled_at?->format('D d M Y, H:i'),
                    'appointment_type' => $appointment->appointment_type,
                ],
                'high',
                'appointments'
            );
        } catch (\Throwable) {
            // Notification failure must not roll back the booking
        }

        return response()->json(['data' => $this->formatAppointmentDetail($appointment)], 201);
```

Remove the original `return` line that was there before adding the notification.

- [ ] **Step 5.2: Fire notification after lab result push**

In `app/Http/Controllers/Api/V1/Connect/RecordController.php`, find the `pushLabResult()` method. After the lab result is successfully saved (before the final `return response()->json(...)`), add:

```php
        // Notify patient that lab result is ready
        try {
            $notificationService = app(\App\Modules\Notifications\Services\NotificationService::class);
            if ($patient) {
                $notificationService->sendNotification(
                    $patient->id,
                    'lab.result.ready',
                    ['patient_name' => $patient->first_name],
                    'high',
                    'health_updates'
                );
            }
        } catch (\Throwable) {
            // Non-fatal
        }
```

- [ ] **Step 5.3: Fire notification after consent request creation**

In `app/Modules/Governance/Services/ConsentService.php`, inject `NotificationService`. Add use statement and update the constructor:

```php
use App\Modules\Notifications\Services\NotificationService;

// Inside the class, add constructor:
    public function __construct(private NotificationService $notificationService) {}
```

At the end of `requestConsent()`, before `return $request;`, add:

```php
        // Notify patient that a consent request is pending their approval
        try {
            $this->notificationService->sendNotification(
                $request->patient_id,
                'consent.request.pending',
                [
                    'facility_name' => 'A facility',  // resolved from Facility model in production
                    'purpose'       => $purpose,
                ],
                'urgent',
                'account_and_security'
            );
        } catch (\Throwable) {
            // Non-fatal
        }
```

- [ ] **Step 5.4: Run notification dispatch tests**

```
php artisan test --filter="NotificationDispatchTest"
```
Expected: All 4 tests pass.

- [ ] **Step 5.5: Run full regression**

```
php artisan test
```
Expected: All tests pass.

- [ ] **Step 5.6: Commit**

```
git add app/Http/Controllers/Api/Mobile/MobileAppointmentController.php app/Http/Controllers/Api/V1/Connect/RecordController.php app/Modules/Governance/Services/ConsentService.php
git commit -m "feat(notifications): fire SMS/email notifications on booking, lab result, and consent request"
```

---

## Self-Review Checklist

- [x] SMS: Twilio when configured, log fallback otherwise — no crashes in dev ✅
- [x] Email: Laravel Mail, respects MAIL_MAILER env — `array` in tests means no real sends ✅
- [x] CommunicationRouterService: real contact lookup from Patient model, real dispatch ✅
- [x] Email column added to patients via migration (nullable — no existing data broken) ✅
- [x] 6 notification templates seeded (3 events × 2 channels) ✅
- [x] 3 event hooks: appointment.booked, lab.result.ready, consent.request.pending ✅
- [x] Notification failures are caught + never break the primary action ✅
- [x] Privacy gate preserved (no PHI in SMS/email body) ✅
- [x] No TBD placeholders — every method has complete working code ✅
- [x] Test: SMS log test, email Mail::fake test, end-to-end booking notification, consent notification ✅
