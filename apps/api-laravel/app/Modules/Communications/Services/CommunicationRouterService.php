<?php

namespace App\Modules\Communications\Services;

use App\Modules\Notifications\Models\NotificationEvent;
use App\Modules\Notifications\Models\NotificationDelivery;
use App\Modules\Notifications\Services\NotificationPreferenceService;
use Illuminate\Support\Str;

class CommunicationRouterService
{
    private NotificationPreferenceService $preferenceService;

    public function __construct(NotificationPreferenceService $preferenceService)
    {
        $this->preferenceService = $preferenceService;
    }

    public function route(
        string $eventType,
        string $recipientUserId,
        array $payload,
        string $priority = 'normal',
        string $category = 'health_updates'
    ): array {
        // 1. Enforce Privacy Rule: Mask sensitive clinical information for external channels
        $securePayload = $this->enforcePrivacy($eventType, $payload);

        // 2. Anti-Spam & Deduplication: Prevent spamming duplicates of non-critical events
        if ($priority !== 'critical' && $priority !== 'urgent') {
            if ($this->isDuplicateEvent($recipientUserId, $eventType)) {
                return ['status' => 'suppressed', 'reason' => 'DEDUPLICATION_LIMIT'];
            }
        }

                // 3. Create NotificationEvent
        $event = NotificationEvent::create([
            'uuid' => Str::uuid(),
            'event_type' => $eventType,
            'communication_type' => $category,
            'recipient_user_id' => $recipientUserId,
            'recipient_type' => 'user',
            'payload_json' => json_encode($securePayload),
            'priority' => $priority,
            'status' => 'routed',
            'requires_acknowledgement' => ($priority === 'critical' || $priority === 'urgent')
        ]);

        $deliveries = [];
        $channels = ['email', 'whatsapp', 'sms', 'push', 'voice', 'dashboard'];

        foreach ($channels as $channel) {
            // Check preference & quiet hours
            if ($this->preferenceService->isChannelEnabled($recipientUserId, $category, $channel, $priority)) {
                $delivery = NotificationDelivery::create([
                    'uuid' => Str::uuid(),
                    'notification_event_id' => $event->id,
                    'channel' => $channel,
                    'recipient' => $this->getRecipientContact($recipientUserId, $channel),
                    'provider' => 'demo_provider',
                    'status' => 'pending'
                ]);

                // Trigger mock sending
                $this->dispatchDelivery($delivery, $securePayload);
                $deliveries[] = $delivery;
            }
        }

        return [
            'status' => 'success',
            'event' => $event,
            'deliveries' => $deliveries
        ];
    }

    private function enforcePrivacy(string $eventType, array $payload): array
    {
        // Absolute masking of protected health information for external routing
        if (isset($payload['diagnosis']) || isset($payload['result_value']) || isset($payload['sensitive'])) {
            $payload['body'] = "A new health update is available in OpesCare. Log in securely to view it.";
            unset($payload['diagnosis']);
            unset($payload['result_value']);
            unset($payload['sensitive']);
        }
        return $payload;
    }

    private function isDuplicateEvent(string $recipientUserId, string $eventType): bool
    {
        // Simple dedupe within last 5 minutes
        return NotificationEvent::where('recipient_user_id', $recipientUserId)
            ->where('event_type', $eventType)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
    }

    private function getRecipientContact(string $userId, string $channel): string
    {
        // Returns safe dummy/mock address/number
        if ($channel === 'email') {
            return "user_{$userId}@opescare.org";
        }
        return "+243810000000"; // Mock French/African phone number
    }

    private function dispatchDelivery(NotificationDelivery $delivery, array $payload)
    {
        // Local/Demo Mode: simulate successful transmission
        $delivery->status = 'delivered';
        $delivery->delivered_at = now();
        $delivery->save();
    }
}
