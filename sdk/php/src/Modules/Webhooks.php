<?php

namespace OpesCare\Modules;

use OpesCare\Exceptions\WebhookSignatureException;
use OpesCare\Http\ApiClient;

/**
 * Webhook subscription management and incoming payload verification.
 */
class Webhooks
{
    private const TIMESTAMP_TOLERANCE = 300; // 5 minutes — reject replays older than this

    public function __construct(private readonly ApiClient $client) {}

    // ── Subscription management ───────────────────────────────────────────

    /**
     * Create a webhook subscription. Store the returned webhook_secret immediately — it is shown once.
     *
     * @param  string    $callbackUrl     HTTPS URL that receives webhook deliveries
     * @param  string[]  $events          Event types to subscribe to
     * @param  string    $description     Optional description
     */
    public function subscribe(string $callbackUrl, array $events, string $description = ''): array
    {
        return $this->client->post('api/v1/connect/webhooks/subscriptions', [
            'callback_url'      => $callbackUrl,
            'subscribed_events' => $events,
            'description'       => $description,
        ]);
    }

    /**
     * Manually replay a persisted webhook event to all matching active subscriptions.
     */
    public function replay(string $eventId): array
    {
        return $this->client->post("api/v1/connect/webhooks/events/{$eventId}/replay");
    }

    // ── Incoming payload verification ─────────────────────────────────────

    /**
     * Verify the HMAC-SHA256 signature of an incoming webhook delivery.
     *
     * Call this BEFORE processing any webhook payload.
     *
     * @param  string  $rawPayload        Raw request body (do not JSON-decode before calling)
     * @param  string  $signatureHeader   Value of X-OpesCare-Signature header
     * @param  string  $secret            Your webhook_secret (whsec_...)
     *
     * @throws WebhookSignatureException  If signature is invalid, timestamp missing, or replay detected
     */
    public function verifySignature(string $rawPayload, string $signatureHeader, string $secret): void
    {
        // Parse: "t=1717228800,v1=abc123..."
        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            [$k, $v] = array_pad(explode('=', $segment, 2), 2, '');
            $parts[$k] = $v;
        }

        if (empty($parts['t']) || empty($parts['v1'])) {
            throw new WebhookSignatureException('Webhook signature header is malformed or missing t/v1 fields.');
        }

        $timestamp = (int) $parts['t'];
        $received  = $parts['v1'];

        // Replay protection
        if (abs(time() - $timestamp) > self::TIMESTAMP_TOLERANCE) {
            throw new WebhookSignatureException(
                sprintf(
                    'Webhook timestamp is out of tolerance (%d seconds old). Possible replay attack.',
                    abs(time() - $timestamp)
                )
            );
        }

        // Compute expected signature: HMAC-SHA256("timestamp.rawPayload")
        $signedPayload = $timestamp . '.' . $rawPayload;
        $expected      = hash_hmac('sha256', $signedPayload, $secret);

        // Constant-time comparison to prevent timing attacks
        if (!hash_equals($expected, $received)) {
            throw new WebhookSignatureException('Webhook HMAC signature does not match. Payload may have been tampered.');
        }
    }

    /**
     * Parse a verified webhook payload into an event array.
     *
     * Always call verifySignature() before this method.
     *
     * @param  string  $rawPayload  Raw request body
     * @return array{id: string, type: string, version: string, created_at: string, data: array, meta: array}
     */
    public function parseEvent(string $rawPayload): array
    {
        $event = json_decode($rawPayload, true);

        if (!is_array($event) || empty($event['type'])) {
            throw new \InvalidArgumentException('Webhook payload is not valid JSON or missing required "type" field.');
        }

        return $event;
    }
}
