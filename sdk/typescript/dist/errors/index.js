"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.RateLimitError = exports.WebhookSignatureError = exports.ServerError = exports.IdempotencyConflictError = exports.NotFoundException = exports.ValidationError = exports.ConsentRequiredError = exports.AuthorizationError = exports.AuthenticationError = exports.OpesCareError = void 0;
class OpesCareError extends Error {
    constructor(message, statusCode = 0, responseBody) {
        super(message);
        this.statusCode = statusCode;
        this.responseBody = responseBody;
        this.name = this.constructor.name;
        Object.setPrototypeOf(this, new.target.prototype);
    }
}
exports.OpesCareError = OpesCareError;
class AuthenticationError extends OpesCareError {
}
exports.AuthenticationError = AuthenticationError;
class AuthorizationError extends OpesCareError {
}
exports.AuthorizationError = AuthorizationError;
class ConsentRequiredError extends OpesCareError {
}
exports.ConsentRequiredError = ConsentRequiredError;
class ValidationError extends OpesCareError {
}
exports.ValidationError = ValidationError;
class NotFoundException extends OpesCareError {
}
exports.NotFoundException = NotFoundException;
class IdempotencyConflictError extends OpesCareError {
}
exports.IdempotencyConflictError = IdempotencyConflictError;
class ServerError extends OpesCareError {
}
exports.ServerError = ServerError;
class WebhookSignatureError extends OpesCareError {
}
exports.WebhookSignatureError = WebhookSignatureError;
class RateLimitError extends OpesCareError {
    constructor(message, retryAfter = 60, responseBody) {
        super(message, 429, responseBody);
        this.retryAfter = retryAfter;
    }
}
exports.RateLimitError = RateLimitError;
//# sourceMappingURL=index.js.map