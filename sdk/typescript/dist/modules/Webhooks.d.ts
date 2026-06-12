import { ApiClient } from '../http/ApiClient';
export interface WebhookEvent {
    id: string;
    type: string;
    version: string;
    created_at: string;
    data: Record<string, unknown>;
    meta: Record<string, unknown>;
}
export declare class Webhooks {
    private readonly client;
    constructor(client: ApiClient);
    subscribe(callbackUrl: string, events: string[], description?: string): Promise<Record<string, unknown>>;
    replay(eventId: string): Promise<Record<string, unknown>>;
    /**
     * Verify the HMAC-SHA256 signature of an incoming webhook delivery.
     *
     * @param rawPayload       Raw request body as a string (do NOT JSON.parse first)
     * @param signatureHeader  Value of X-OpesCare-Signature header ("t=...,v1=...")
     * @param secret           Your webhook_secret (whsec_...)
     * @throws WebhookSignatureError
     */
    verifySignature(rawPayload: string, signatureHeader: string, secret: string): void;
    parseEvent(rawPayload: string): WebhookEvent;
}
//# sourceMappingURL=Webhooks.d.ts.map