<?php

namespace App\Services;

use App\Jobs\SendWebhookJob;
use App\Models\WebhookSubscription;

class WebhookService
{
    /**
     * Dispatch an event to all active matching webhook subscriptions.
     *
     * Delivery is handled asynchronously via SendWebhookJob (queue-backed).
     * Falls back to synchronous dispatch when QUEUE_CONNECTION=sync.
     */
    public static function dispatch(string $eventType, array $resourceData): void
    {
        $eventId = 'evt_' . bin2hex(random_bytes(8));

        $payload = [
            'event_id'    => $eventId,
            'event_type'  => $eventType,
            'occurred_at' => now()->toIso8601String(),
            'resource'    => $resourceData,
        ];

        $subscriptions = WebhookSubscription::where('status', 'active')->get();

        foreach ($subscriptions as $subscription) {
            if (!in_array($eventType, (array) $subscription->subscribed_events)) {
                continue;
            }

            SendWebhookJob::dispatch($subscription->id, $payload)
                ->onQueue('webhooks');
        }
    }
}
