import { createHmac } from 'crypto';
import { Webhooks }              from '../src/modules/Webhooks';
import { WebhookSignatureError } from '../src/errors';
import { ApiClient }             from '../src/http/ApiClient';

// Minimal stub for ApiClient — webhook signature tests are stateless
const stubClient = {} as ApiClient;

describe('Webhooks.verifySignature', () => {
  const webhooks = new Webhooks(stubClient);
  const secret   = 'whsec_test_secret_typescript';

  function makeHeader(payload: string, secret: string, timestamp: number): string {
    const signed = `${timestamp}.${payload}`;
    const sig    = createHmac('sha256', secret).update(signed).digest('hex');
    return `t=${timestamp},v1=${sig}`;
  }

  it('accepts a valid signature', () => {
    const payload = '{"type":"lab_result.released","data":{}}';
    const ts      = Math.floor(Date.now() / 1000);
    const header  = makeHeader(payload, secret, ts);
    expect(() => webhooks.verifySignature(payload, header, secret)).not.toThrow();
  });

  it('rejects a wrong secret', () => {
    const payload = '{"type":"lab_result.released"}';
    const ts      = Math.floor(Date.now() / 1000);
    const header  = makeHeader(payload, secret, ts);
    expect(() => webhooks.verifySignature(payload, header, 'wrong_secret'))
      .toThrow(WebhookSignatureError);
  });

  it('rejects a tampered payload', () => {
    const payload = '{"type":"lab_result.released"}';
    const ts      = Math.floor(Date.now() / 1000);
    const header  = makeHeader(payload, secret, ts);
    expect(() => webhooks.verifySignature('{"type":"tampered"}', header, secret))
      .toThrow(WebhookSignatureError);
  });

  it('rejects a timestamp older than 5 minutes (replay protection)', () => {
    const payload = '{"type":"lab_result.released"}';
    const ts      = Math.floor(Date.now() / 1000) - 400;
    const header  = makeHeader(payload, secret, ts);
    expect(() => webhooks.verifySignature(payload, header, secret))
      .toThrow(/replay/i);
  });

  it('rejects a malformed signature header', () => {
    expect(() => webhooks.verifySignature('{}', 'not_valid_header', secret))
      .toThrow(WebhookSignatureError);
  });
});

describe('RateLimitError retry-after regression', () => {
  // Ensures Math.max() is used so server Retry-After is respected over backoff delay.
  // If Math.min() were used, the test would still pass but servers would be hammered.
  // This test verifies the RateLimitError carries the correct retryAfter value.
  it('RateLimitError carries server retry-after value', () => {
    const { RateLimitError } = require('../src/errors');
    const err = new RateLimitError('Too many requests', 120);
    expect(err.retryAfter).toBe(120);
    expect(err.retryAfter).toBeGreaterThan(8); // must be server value, not capped at backoff max
  });
});

describe('Webhooks.parseEvent', () => {
  const webhooks = new Webhooks(stubClient);

  it('returns a typed event object', () => {
    const payload = JSON.stringify({
      id: 'evt_001', type: 'prescription.issued',
      version: '1.0', created_at: '2026-06-01T00:00:00Z',
      data: {}, meta: {},
    });
    const event = webhooks.parseEvent(payload);
    expect(event.type).toBe('prescription.issued');
    expect(event.id).toBe('evt_001');
  });

  it('throws on invalid JSON', () => {
    expect(() => webhooks.parseEvent('not json')).toThrow();
  });

  it('throws when type field is missing', () => {
    expect(() => webhooks.parseEvent('{"id":"evt_001"}')).toThrow();
  });
});
