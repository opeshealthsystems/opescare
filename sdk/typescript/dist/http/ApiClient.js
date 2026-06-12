"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ApiClient = void 0;
const axios_1 = __importDefault(require("axios"));
const errors_1 = require("../errors");
const MAX_RETRIES = 4;
const RETRY_DELAYS = [1000, 2000, 4000, 8000]; // ms, exponential
class ApiClient {
    constructor(baseUrl, accessToken, timeout = 30000) {
        this.accessToken = accessToken;
        this.http = axios_1.default.create({
            baseURL: baseUrl.replace(/\/$/, '') + '/',
            timeout,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'User-Agent': 'opescare-ts-sdk/1.0',
            },
        });
    }
    async get(path, params) {
        return this.request('GET', path, { params });
    }
    async post(path, data, idempotencyKey) {
        return this.request('POST', path, { data }, idempotencyKey);
    }
    async delete(path) {
        return this.request('DELETE', path);
    }
    async request(method, path, config = {}, idempotencyKey) {
        const headers = {
            Authorization: `Bearer ${this.accessToken}`,
        };
        if (idempotencyKey && ['POST', 'PUT', 'PATCH'].includes(method)) {
            headers['Idempotency-Key'] = idempotencyKey;
        }
        let attempt = 0;
        while (true) {
            try {
                const response = await this.http.request({
                    method,
                    url: path.replace(/^\//, ''),
                    ...config,
                    headers: { ...headers, ...(config.headers ?? {}) },
                });
                return response.data;
            }
            catch (err) {
                if (!axios_1.default.isAxiosError(err))
                    throw err;
                const status = err.response?.status ?? 0;
                const body = err.response?.data ?? {};
                const errorCode = (body.error_code ?? body.error ?? '');
                const message = (body.message ?? `HTTP ${status}`);
                if (status === 429) {
                    const retryAfter = parseInt(err.response?.headers['retry-after'] ?? '60', 10);
                    if (attempt < MAX_RETRIES) {
                        await sleep(Math.min(retryAfter * 1000, RETRY_DELAYS[attempt] ?? 8000));
                        attempt++;
                        continue;
                    }
                    throw new errors_1.RateLimitError(message, retryAfter, body);
                }
                if (status >= 500 && attempt < MAX_RETRIES) {
                    await sleep(RETRY_DELAYS[attempt] ?? 8000);
                    attempt++;
                    continue;
                }
                throw this.mapError(status, errorCode, message, body);
            }
        }
    }
    mapError(status, errorCode, message, body) {
        if (status === 401)
            return new errors_1.AuthenticationError(message, status, body);
        if (status === 403 && errorCode === 'CONSENT_REQUIRED')
            return new errors_1.ConsentRequiredError(message, status, body);
        if (status === 403)
            return new errors_1.AuthorizationError(message, status, body);
        if (status === 404)
            return new errors_1.NotFoundException(message, status, body);
        if (status === 409)
            return new errors_1.IdempotencyConflictError(message, status, body);
        if (status === 422)
            return new errors_1.ValidationError(message, status, body);
        if (status >= 500)
            return new errors_1.ServerError(message, status, body);
        return new errors_1.OpesCareError(message, status, body);
    }
}
exports.ApiClient = ApiClient;
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
//# sourceMappingURL=ApiClient.js.map