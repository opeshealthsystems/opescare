<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use App\Enums\OpesCareErrorCode;
use App\Models\WebhookEvent;
use App\Models\WebhookSubscription;
use App\Services\AuditLogger;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * POST /api/v1/connect/webhooks/subscriptions
     */
    public function createSubscription(Request $request): JsonResponse
    {
        $callbackUrl = $request->input('callback_url');
        $events      = $request->input('subscribed_events');
        $clientId    = $request->attributes->get('integration_client_id', 'unknown_client');

        if (!$callbackUrl || !is_array($events)) {
            return response()->json([
                'status'     => 'rejected',
                'error_code' => OpesCareErrorCode::VALIDATION_FAILED->value,
                'message'    => 'Missing callback_url or subscribed_events array.',
            ], 400);
        }

        $secret = 'whsec_' . bin2hex(random_bytes(16));

        $sub = WebhookSubscription::create([
            'client_id'          => $clientId,
            'callback_url'       => $callbackUrl,
            'webhook_secret'     => $secret,
            'subscribed_events'  => $events,
            'status'             => 'active',
        ]);

        AuditLogger::log($request, 'webhook_subscription_created', 'webhook_subscription', $sub->id);

        return response()->json([
            'status'             => 'active',
            'subscription_id'    => $sub->id,
            'callback_url'       => $callbackUrl,
            'subscribed_events'  => $events,
            'webhook_secret'     => $secret,
            'created_at'         => $sub->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ], 201);
    }

    /**
     * POST /api/v1/connect/webhooks/events/{eventId}/replay
     *
     * Re-dispatches a persisted webhook event to all currently active
     * matching subscriptions. Creates WebhookReplay rows for audit.
     * Idempotent — safe to call multiple times.
     */
    public function replayEvent(Request $request, string $eventId): JsonResponse
    {
        $event = WebhookEvent::find($eventId);

        if (!$event) {
            return response()->json([
                'status'     => 'not_found',
                'error_code' => OpesCareErrorCode::RESOURCE_NOT_FOUND->value,
                'message'    => 'Webhook event not found.',
            ], 404);
        }

        $replayedBy = $request->attributes->get('integration_client_id', 'api');

        WebhookService::replay($event, $replayedBy);

        AuditLogger::log($request, 'webhook_event_replayed', 'webhook_event', $event->id);

        return response()->json([
            'status'     => 'replayed',
            'event_id'   => $event->id,
            'event_type' => $event->event_type,
            'replayed_at'=> now()->toIso8601String(),
        ]);
    }
}
