import { createHmac, timingSafeEqual } from 'crypto';
import { ApiClient } from '../http/ApiClient';
import { WebhookSignatureError } from '../errors';

const TIMESTAMP_TOLERANCE = 300; // 5 minutes

export interface WebhookEvent {
  id: string;
  type: string;
  version: string;
  created_at: string;
  data: Record<string, unknown>;
  meta: Record<string, unknown>;
}

export class Webhooks {
  constructor(private readonly client: ApiClient) {}

  subscribe(callbackUrl: string, events: string[], description = ''): Promise<Record<string, unknown>> {
    return this.client.post('api/v1/connect/webhooks/subscriptions', {
      callback_url:      callbackUrl,
      subscribed_events: events,
      description,
    });
  }

  replay(eventId: string): Promise<Record<string, unknown>> {
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
  verifySignature(rawPayload: string, signatureHeader: string, secret: string): void {
    const parts: Record<string, string> = {};
    for (const segment of signatureHeader.split(',')) {
      const [k, v] = segment.split('=', 2);
      if (k && v !== undefined) parts[k] = v;
    }

    if (!parts['t'] || !parts['v1']) {
      throw new WebhookSignatureError('Webhook signature header is malformed or missing t/v1 fields.');
    }

    const timestamp = parseInt(parts['t'], 10);
    const received  = parts['v1'];

    const age = Math.abs(Math.floor(Date.now() / 1000) - timestamp);
    if (age > TIMESTAMP_TOLERANCE) {
      throw new WebhookSignatureError(
        `Webhook timestamp is ${age}s old (tolerance: ${TIMESTAMP_TOLERANCE}s). Possible replay attack.`
      );
    }

    const signedPayload = `${timestamp}.${rawPayload}`;
    const expected      = createHmac('sha256', secret).update(signedPayload).digest('hex');

    const expectedBuf = Buffer.from(expected, 'hex');
    const receivedBuf = Buffer.from(received,  'hex');

    if (expectedBuf.length !== receivedBuf.length || !timingSafeEqual(expectedBuf, receivedBuf)) {
      throw new WebhookSignatureError('Webhook HMAC signature does not match. Payload may have been tampered.');
    }
  }

  parseEvent(rawPayload: string): WebhookEvent {
    let event: unknown;
    try {
      event = JSON.parse(rawPayload);
    } catch {
      throw new Error('Webhook payload is not valid JSON.');
    }
    if (!event || typeof event !== 'object' || !('type' in event)) {
      throw new Error('Webhook payload missing required "type" field.');
    }
    return event as WebhookEvent;
  }
}
