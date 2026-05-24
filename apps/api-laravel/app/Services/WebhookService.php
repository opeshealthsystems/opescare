<?php

namespace App\Services;

use App\Jobs\SendWebhookJob;
use App\Models\WebhookEvent;
use App\Models\WebhookSubscription;
use Illuminate\Support\Str;

class WebhookService
{
    /**
     * Dispatch an event to all active matching webhook subscriptions.
     *
     * Persists to webhook_events for replay and audit, then queues
     * SendWebhookJob per matching subscription.
     * Falls back to synchronous dispatch when QUEUE_CONNECTION=sync.
     *
     * @return WebhookEvent The persisted event record.
     */
    public static function dispatch(string $eventType, array $resourceData): WebhookEvent
    {
        $payload = [
            'event_type'  => $eventType,
            'occurred_at' => now()->toIso8601String(),
            'resource'    => $resourceData,
        ];

        // Persist the event so it can be looked up for replay / audit
        $event = WebhookEvent::create([
            'event_type' => $eventType,
            'payload'    => $payload,
        ]);

        // Include the stable DB id in every delivery payload
        $payload['event_id'] = $event->id;

        self::dispatchToSubscriptions($event->id, $eventType, $payload);

        return $event;
    }

    /**
     * Re-dispatch a previously persisted event to all currently active
     * matching subscriptions and record a WebhookReplay row.
     */
    public static function replay(WebhookEvent $event, ?string $replayedBy = null): void
    {
        $payload             = $event->payload;
        $payload['event_id'] = $event->id; // ensure id is present even on legacy events

        self::dispatchToSubscriptions($event->id, $event->event_type, $payload, $replayedBy);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private static function dispatchToSubscriptions(
        string  $eventId,
        string  $eventType,
        array   $payload,
        ?string $replayedBy = null
    ): void {
        $subscriptions = WebhookSubscription::where('status', 'active')->get();

        foreach ($subscriptions as $subscription) {
            if (!in_array($eventType, (array) $subscription->subscribed_events)) {
                continue;
            }

            if ($replayedBy !== null) {
                // Record the replay attempt before queuing
                \App\Models\WebhookReplay::create([
                    'webhook_event_id'    => $eventId,
                    'webhook_endpoint_id' => $subscription->id,
                    'replayed_by'         => $replayedBy,
                    'status'              => 'pending',
                ]);
            }

            SendWebhookJob::dispatch($subscription->id, $payload)
                ->onQueue('webhooks');
        }
    }
}
