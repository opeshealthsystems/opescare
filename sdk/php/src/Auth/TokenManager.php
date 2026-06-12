<?php

namespace OpesCare\Auth;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use OpesCare\Exceptions\AuthenticationException;

/**
 * Manages OAuth2 client-credentials token lifecycle.
 *
 * - Fetches a token from /api/v1/connect/auth/token
 * - Caches it in memory until 60 seconds before expiry
 * - Thread-safe for single-process use (typical for PHP-FPM)
 */
class TokenManager
{
    private ?string $cachedToken  = null;
    private int     $expiresAt    = 0;

    private const EXPIRY_BUFFER = 60; // refresh 60s before actual expiry

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret
    ) {}

    /**
     * Return a valid Bearer token, refreshing if expired or near expiry.
     */
    public function getToken(): string
    {
        if ($this->cachedToken && time() < ($this->expiresAt - self::EXPIRY_BUFFER)) {
            return $this->cachedToken;
        }

        return $this->refresh();
    }

    /**
     * Force a token refresh (e.g., after receiving a 401 mid-session).
     */
    public function refresh(): string
    {
        $http = new GuzzleClient(['base_uri' => rtrim($this->baseUrl, '/') . '/']);

        try {
            $response = $http->post('api/v1/connect/auth/token', [
                'json' => [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type'    => 'client_credentials',
                ],
                'timeout' => 15,
            ]);

            $data = json_decode((string) $response->getBody(), true);

            $this->cachedToken = $data['access_token']
                ?? throw new AuthenticationException('Token response missing access_token field.');

            $this->expiresAt = time() + ($data['expires_in'] ?? 3600);

            return $this->cachedToken;
        } catch (RequestException $e) {
            $msg = $e->hasResponse()
                ? (json_decode((string) $e->getResponse()->getBody(), true)['message'] ?? $e->getMessage())
                : $e->getMessage();

            throw new AuthenticationException("Failed to obtain access token: {$msg}", null, 0, $e);
        }
    }

    public function revoke(): void
    {
        $this->cachedToken = null;
        $this->expiresAt   = 0;
    }
}
