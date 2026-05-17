<?php

namespace App\Services;

use App\Models\WebhookSubscription;
use App\Models\WebhookDeliveryLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch event webhooks to active subscriptions.
     */
    public static function dispatch(string $eventType, array $resourceData): void
    {
        $eventId = 'evt_' . bin2hex(random_bytes(8));
        
        $payload = [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'occurred_at' => now()->toIso8601String(),
            'resource' => $resourceData
        ];

        // Find active subscriptions matching event type
        $subscriptions = WebhookSubscription::where('status', 'active')->get();

        foreach ($subscriptions as $subscription) {
            if (in_array($eventType, $subscription->subscribed_events)) {
                self::deliver($subscription, $payload);
            }
        }
    }

    /**
     * Deliver the signed webhook payload synchronously.
     */
    protected static function deliver(WebhookSubscription $subscription, array $payload): void
    {
        $timestamp = time();
        $payloadJson = json_encode($payload);
        
        // Cryptographic HMAC-SHA256 Signature calculation
        $signature = hash_hmac('sha256', $timestamp . '.' . $payloadJson, $subscription->webhook_secret);

        $log = WebhookDeliveryLog::create([
            'event_id' => $payload['event_id'],
            'event_type' => $payload['event_type'],
            'payload' => $payload,
            'status' => 'pending'
        ]);

        try {
            // Synchronous delivery with short timeout for sandbox/testing
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-OpesCare-Signature' => 't=' . $timestamp . ',v1=' . $signature,
                'X-OpesCare-Timestamp' => $timestamp,
                'X-OpesCare-Event-Id' => $payload['event_id']
            ])->timeout(3)->post($subscription->callback_url, $payload);

            if ($response->successful()) {
                $log->update(['status' => 'delivered']);
            } else {
                $log->update([
                    'status' => 'failed',
                    'retry_count' => $log->retry_count + 1
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Webhook dispatch failure: ' . $e->getMessage());
            $log->update([
                'status' => 'failed',
                'retry_count' => $log->retry_count + 1
            ]);
        }
    }
}
