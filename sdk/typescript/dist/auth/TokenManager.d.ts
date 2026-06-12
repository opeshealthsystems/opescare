/**
 * Manages OAuth2 client-credentials token lifecycle.
 * Caches token in memory, refreshes 60s before expiry.
 */
export declare class TokenManager {
    private readonly baseUrl;
    private readonly clientId;
    private readonly clientSecret;
    private cachedToken;
    private expiresAt;
    private static readonly EXPIRY_BUFFER;
    constructor(baseUrl: string, clientId: string, clientSecret: string);
    getToken(): Promise<string>;
    refresh(): Promise<string>;
    revoke(): void;
}
//# sourceMappingURL=TokenManager.d.ts.map