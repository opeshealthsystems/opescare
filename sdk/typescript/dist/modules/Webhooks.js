"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Webhooks = void 0;
const crypto_1 = require("crypto");
const errors_1 = require("../errors");
const TIMESTAMP_TOLERANCE = 300; // 5 minutes
class Webhooks {
    constructor(client) {
        this.client = client;
    }
    subscribe(callbackUrl, events, description = '') {
        return this.client.post('api/v1/connect/webhooks/subscriptions', {
            callback_url: callbackUrl,
            subscribed_events: events,
            description,
        });
    }
    replay(eventId) {
        return this.client.post(`api/v1/connect/webhooks/events/${eventId}/replay`);
    }
    /**
     * Verify the HMAC-SHA256 signature of an incoming webhook delivery.
     *
     * @param rawPayload       Raw request body as a string (do NOT JSON.parse first)
     * @param signatureHeader  Value of X-OpesCare-Signature header ("t=...,v1=...")
     * @param secret           Your webhook_secret (whsec_...)
     * @throws WebhookSignatureError
     */
    verifySignature(rawPayload, signatureHeader, secret) {
        const parts = {};
        for (const segment of signatureHeader.split(',')) {
            const [k, v] = segment.split('=', 2);
            if (k && v !== undefined)
                parts[k] = v;
        }
        if (!parts['t'] || !parts['v1']) {
            throw new errors_1.WebhookSignatureError('Webhook signature header is malformed or missing t/v1 fields.');
        }
        const timestamp = parseInt(parts['t'], 10);
        const received = parts['v1'];
        const age = Math.abs(Math.floor(Date.now() / 1000) - timestamp);
        if (age > TIMESTAMP_TOLERANCE) {
            throw new errors_1.WebhookSignatureError(`Webhook timestamp is ${age}s old (tolerance: ${TIMESTAMP_TOLERANCE}s). Possible replay attack.`);
        }
        const signedPayload = `${timestamp}.${rawPayload}`;
        const expected = (0, crypto_1.createHmac)('sha256', secret).update(signedPayload).digest('hex');
        const expectedBuf = Buffer.from(expected, 'hex');
        const receivedBuf = Buffer.from(received, 'hex');
        if (expectedBuf.length !== receivedBuf.length || !(0, crypto_1.timingSafeEqual)(expectedBuf, receivedBuf)) {
            throw new errors_1.WebhookSignatureError('Webhook HMAC signature does not match. Payload may have been tampered.');
        }
    }
    parseEvent(rawPayload) {
        let event;
        try {
            event = JSON.parse(rawPayload);
        }
        catch {
            throw new Error('Webhook payload is not valid JSON.');
        }
        if (!event || typeof event !== 'object' || !('type' in event)) {
            throw new Error('Webhook payload missing required "type" field.');
        }
        return event;
    }
}
exports.Webhooks = Webhooks;
//# sourceMappingURL=Webhooks.js.map