import axios, { AxiosInstance, AxiosRequestConfig } from 'axios';
import {
  AuthenticationError, AuthorizationError, ConsentRequiredError,
  IdempotencyConflictError, NotFoundException, OpesCareError,
  RateLimitError, ServerError, ValidationError,
} from '../errors';

const MAX_RETRIES   = 4;
const RETRY_DELAYS  = [1000, 2000, 4000, 8000]; // ms, exponential

export class ApiClient {
  private readonly http: AxiosInstance;

  constructor(baseUrl: string, private accessToken: string, timeout = 30000) {
    this.http = axios.create({
      baseURL: baseUrl.replace(/\/$/, '') + '/',
      timeout,
      headers: {
        Accept:         'application/json',
        'Content-Type': 'application/json',
        'User-Agent':   'opescare-ts-sdk/1.0',
      },
    });
  }

  async get<T = Record<string, unknown>>(path: string, params?: Record<string, unknown>): Promise<T> {
    return this.request<T>('GET', path, { params });
  }

  async post<T = Record<string, unknown>>(
    path: string,
    data?: Record<string, unknown>,
    idempotencyKey?: string
  ): Promise<T> {
    return this.request<T>('POST', path, { data }, idempotencyKey);
  }

  async delete<T = Record<string, unknown>>(path: string): Promise<T> {
    return this.request<T>('DELETE', path);
  }

  private async request<T>(
    method: string,
    path: string,
    config: AxiosRequestConfig = {},
    idempotencyKey?: string
  ): Promise<T> {
    const headers: Record<string, string> = {
      Authorization: `Bearer ${this.accessToken}`,
    };

    if (idempotencyKey && ['POST', 'PUT', 'PATCH'].includes(method)) {
      headers['Idempotency-Key'] = idempotencyKey;
    }

    let attempt = 0;

    while (true) {
      try {
        const response = await this.http.request<T>({
          method,
          url: path.replace(/^\//, ''),
          ...config,
          headers: { ...headers, ...(config.headers ?? {}) },
        });
        return response.data;
      } catch (err: unknown) {
        if (!axios.isAxiosError(err)) throw err;

        const status      = err.response?.status ?? 0;
        const body        = err.response?.data as Record<string, unknown> ?? {};
        const errorCode   = (body.error_code ?? body.error ?? '') as string;
        const message     = (body.message ?? `HTTP ${status}`) as string;

        if (status === 429) {
          const retryAfter = parseInt(err.response?.headers['retry-after'] ?? '60', 10);
          if (attempt < MAX_RETRIES) {
            await sleep(Math.max(retryAfter * 1000, RETRY_DELAYS[attempt] ?? 8000));
            attempt++;
            continue;
          }
          throw new RateLimitError(message, retryAfter, body);
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

  private mapError(
    status: number,
    errorCode: string,
    message: string,
    body: Record<string, unknown>
  ): OpesCareError {
    if (status === 401) return new AuthenticationError(message, status, body);
    if (status === 403 && errorCode === 'CONSENT_REQUIRED') return new ConsentRequiredError(message, status, body);
    if (status === 403) return new AuthorizationError(message, status, body);
    if (status === 404) return new NotFoundException(message, status, body);
    if (status === 409) return new IdempotencyConflictError(message, status, body);
    if (status === 422) return new ValidationError(message, status, body);
    if (status >= 500)  return new ServerError(message, status, body);
    return new OpesCareError(message, status, body);
  }
}

function sleep(ms: number): Promise<void> {
  return new Promise(resolve => setTimeout(resolve, ms));
}
