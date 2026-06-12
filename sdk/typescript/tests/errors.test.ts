import {
  AuthenticationError, AuthorizationError, ConsentRequiredError,
  RateLimitError, WebhookSignatureError, OpesCareError,
} from '../src/errors';

describe('Error types', () => {
  it('AuthenticationError is instance of OpesCareError', () => {
    const err = new AuthenticationError('Invalid token', 401);
    expect(err).toBeInstanceOf(OpesCareError);
    expect(err).toBeInstanceOf(AuthenticationError);
    expect(err.statusCode).toBe(401);
    expect(err.name).toBe('AuthenticationError');
  });

  it('RateLimitError carries retryAfter', () => {
    const err = new RateLimitError('Too many requests', 45);
    expect(err.retryAfter).toBe(45);
    expect(err.statusCode).toBe(429);
  });

  it('WebhookSignatureError is catchable as OpesCareError', () => {
    const err = new WebhookSignatureError('Signature mismatch');
    expect(err).toBeInstanceOf(OpesCareError);
  });

  it('ConsentRequiredError is distinct from AuthorizationError', () => {
    const consent = new ConsentRequiredError('Consent needed');
    const authz   = new AuthorizationError('Forbidden');
    expect(consent).not.toBeInstanceOf(AuthorizationError);
    expect(authz).not.toBeInstanceOf(ConsentRequiredError);
  });

  it('errors have correct stack traces', () => {
    const err = new AuthenticationError('test');
    expect(err.stack).toContain('AuthenticationError');
  });
});
