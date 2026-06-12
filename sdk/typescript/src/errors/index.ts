export class OpesCareError extends Error {
  constructor(
    message: string,
    public readonly statusCode: number = 0,
    public readonly responseBody?: Record<string, unknown>
  ) {
    super(message);
    this.name = this.constructor.name;
    Object.setPrototypeOf(this, new.target.prototype);
  }
}

export class AuthenticationError     extends OpesCareError {}
export class AuthorizationError      extends OpesCareError {}
export class ConsentRequiredError    extends OpesCareError {}
export class ValidationError         extends OpesCareError {}
export class NotFoundException       extends OpesCareError {}
export class IdempotencyConflictError extends OpesCareError {}
export class ServerError             extends OpesCareError {}
export class WebhookSignatureError   extends OpesCareError {}

export class RateLimitError extends OpesCareError {
  constructor(
    message: string,
    public readonly retryAfter: number = 60,
    responseBody?: Record<string, unknown>
  ) {
    super(message, 429, responseBody);
  }
}
