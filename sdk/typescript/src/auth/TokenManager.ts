import axios from 'axios';
import { AuthenticationError } from '../errors';

/**
 * Manages OAuth2 client-credentials token lifecycle.
 * Caches token in memory, refreshes 60s before expiry.
 */
export class TokenManager {
  private cachedToken: string | null = null;
  private expiresAt: number = 0;
  private static readonly EXPIRY_BUFFER = 60;

  constructor(
    private readonly baseUrl: string,
    private readonly clientId: string,
    private readonly clientSecret: string
  ) {}

  async getToken(): Promise<string> {
    if (this.cachedToken && Date.now() / 1000 < this.expiresAt - TokenManager.EXPIRY_BUFFER) {
      return this.cachedToken;
    }
    return this.refresh();
  }

  async refresh(): Promise<string> {
    try {
      const response = await axios.post(
        `${this.baseUrl.replace(/\/$/, '')}/api/v1/connect/auth/token`,
        {
          client_id:     this.clientId,
          client_secret: this.clientSecret,
          grant_type:    'client_credentials',
        },
        { timeout: 15000, headers: { 'Content-Type': 'application/json' } }
      );

      const { access_token, expires_in = 3600 } = response.data;

      if (!access_token) {
        throw new AuthenticationError('Token response missing access_token field.');
      }

      this.cachedToken = access_token as string;
      this.expiresAt   = Math.floor(Date.now() / 1000) + (expires_in as number);

      return this.cachedToken;
    } catch (err: unknown) {
      if (axios.isAxiosError(err) && err.response) {
        const msg = (err.response.data as Record<string, unknown>)?.message as string ?? err.message;
        throw new AuthenticationError(`Failed to obtain access token: ${msg}`, err.response.status);
      }
      throw new AuthenticationError(`Failed to obtain access token: ${String(err)}`);
    }
  }

  revoke(): void {
    this.cachedToken = null;
    this.expiresAt   = 0;
  }
}
