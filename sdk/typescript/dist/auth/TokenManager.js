"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TokenManager = void 0;
const axios_1 = __importDefault(require("axios"));
const errors_1 = require("../errors");
/**
 * Manages OAuth2 client-credentials token lifecycle.
 * Caches token in memory, refreshes 60s before expiry.
 */
class TokenManager {
    constructor(baseUrl, clientId, clientSecret) {
        this.baseUrl = baseUrl;
        this.clientId = clientId;
        this.clientSecret = clientSecret;
        this.cachedToken = null;
        this.expiresAt = 0;
    }
    async getToken() {
        if (this.cachedToken && Date.now() / 1000 < this.expiresAt - TokenManager.EXPIRY_BUFFER) {
            return this.cachedToken;
        }
        return this.refresh();
    }
    async refresh() {
        try {
            const response = await axios_1.default.post(`${this.baseUrl.replace(/\/$/, '')}/api/v1/connect/auth/token`, {
                client_id: this.clientId,
                client_secret: this.clientSecret,
                grant_type: 'client_credentials',
            }, { timeout: 15000, headers: { 'Content-Type': 'application/json' } });
            const { access_token, expires_in = 3600 } = response.data;
            if (!access_token) {
                throw new errors_1.AuthenticationError('Token response missing access_token field.');
            }
            this.cachedToken = access_token;
            this.expiresAt = Math.floor(Date.now() / 1000) + expires_in;
            return this.cachedToken;
        }
        catch (err) {
            if (axios_1.default.isAxiosError(err) && err.response) {
                const msg = err.response.data?.message ?? err.message;
                throw new errors_1.AuthenticationError(`Failed to obtain access token: ${msg}`, err.response.status);
            }
            throw new errors_1.AuthenticationError(`Failed to obtain access token: ${String(err)}`);
        }
    }
    revoke() {
        this.cachedToken = null;
        this.expiresAt = 0;
    }
}
exports.TokenManager = TokenManager;
TokenManager.EXPIRY_BUFFER = 60;
//# sourceMappingURL=TokenManager.js.map