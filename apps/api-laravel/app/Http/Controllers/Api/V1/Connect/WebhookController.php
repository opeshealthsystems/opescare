<?php

namespace App\Http\Controllers\Api\V1\Connect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\OpesCareErrorCode;
use App\Models\WebhookSubscription;
use App\Services\AuditLogger;

class WebhookController extends Controller
{
    public function createSubscription(Request $request)
    {
        $callbackUrl = $request->input('callback_url');
        $events = $request->input('subscribed_events');
        $clientId = $request->attributes->get('integration_client_id', 'unknown_client');

        if (!$callbackUrl || !is_array($events)) {
            return response()->json([
                'status' => 'rejected',
                'error_code' => OpesCareErrorCode::VALIDATION_FAILED->value,
                'message' => 'Missing callback_url or subscribed_events array.'
            ], 400);
        }

        $secret = 'whsec_' . bin2hex(random_bytes(16));

        // Persist to actual database
        $sub = WebhookSubscription::create([
            'client_id' => $clientId,
            'callback_url' => $callbackUrl,
            'webhook_secret' => $secret,
            'subscribed_events' => $events,
            'status' => 'active'
        ]);

        AuditLogger::log(
            $request,
            'webhook_subscription_created',
            'webhook_subscription',
            $sub->id
        );

        return response()->json([
            'status' => 'active',
            'subscription_id' => $sub->id,
            'callback_url' => $callbackUrl,
            'subscribed_events' => $events,
            'webhook_secret' => $secret,
            'created_at' => $sub->created_at ? $sub->created_at->toIso8601String() : date('Y-m-d\TH:i:s\Z')
        ], 201);
    }
}
