export declare class OpesCareError extends Error {
    readonly statusCode: number;
    readonly responseBody?: Record<string, unknown> | undefined;
    constructor(message: string, statusCode?: number, responseBody?: Record<string, unknown> | undefined);
}
export declare class AuthenticationError extends OpesCareError {
}
export declare class AuthorizationError extends OpesCareError {
}
export declare class ConsentRequiredError extends OpesCareError {
}
export declare class ValidationError extends OpesCareError {
}
export declare class NotFoundException extends OpesCareError {
}
export declare class IdempotencyConflictError extends OpesCareError {
}
export declare class ServerError extends OpesCareError {
}
export declare class WebhookSignatureError extends OpesCareError {
}
export declare class RateLimitError extends OpesCareError {
    readonly retryAfter: number;
    constructor(message: string, retryAfter?: number, responseBody?: Record<string, unknown>);
}
//# sourceMappingURL=index.d.ts.map