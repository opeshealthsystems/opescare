<?php

namespace OpesCare\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use OpesCare\Exceptions\AuthenticationException;
use OpesCare\Exceptions\AuthorizationException;
use OpesCare\Exceptions\ConsentRequiredException;
use OpesCare\Exceptions\IdempotencyConflictException;
use OpesCare\Exceptions\NotFoundException;
use OpesCare\Exceptions\OpesCareException;
use OpesCare\Exceptions\RateLimitException;
use OpesCare\Exceptions\ServerException;
use OpesCare\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;

/**
 * Low-level HTTP client wrapping Guzzle.
 *
 * Handles:
 *  - Bearer token injection
 *  - Idempotency-Key header on writes
 *  - Exponential backoff retry for 429 / 5xx
 *  - Typed exception mapping
 */
class ApiClient
{
    private GuzzleClient $http;

    private const MAX_RETRIES = 4;
    private const RETRY_DELAYS = [1, 2, 4, 8]; // seconds, exponential

    public function __construct(
        private string $baseUrl,
        private readonly string $accessToken,
        private readonly float $timeout = 30.0
    ) {
        $this->http = new GuzzleClient([
            'base_uri'               => rtrim($baseUrl, '/') . '/',
            RequestOptions::TIMEOUT  => $timeout,
            RequestOptions::HEADERS  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent'   => 'opescare-php-sdk/1.0',
            ],
        ]);
    }

    // ── Public HTTP methods ────────────────────────────────────────────────

    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, [
            RequestOptions::QUERY => $query,
        ]);
    }

    public function post(string $path, array $body = [], ?string $idempotencyKey = null): array
    {
        return $this->request('POST', $path, [
            RequestOptions::JSON => $body,
        ], $idempotencyKey);
    }

    public function put(string $path, array $body = [], ?string $idempotencyKey = null): array
    {
        return $this->request('PUT', $path, [
            RequestOptions::JSON => $body,
        ], $idempotencyKey);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    // ── Internal ──────────────────────────────────────────────────────────

    private function request(string $method, string $path, array $options = [], ?string $idempotencyKey = null): array
    {
        $options[RequestOptions::HEADERS] = array_merge(
            $options[RequestOptions::HEADERS] ?? [],
            ['Authorization' => "Bearer {$this->accessToken}"]
        );

        if ($idempotencyKey && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options[RequestOptions::HEADERS]['Idempotency-Key'] = $idempotencyKey;
        }

        $attempt = 0;

        while (true) {
            try {
                $response = $this->http->request($method, ltrim($path, '/'), $options);
                return $this->decode($response);
            } catch (RequestException $e) {
                $statusCode  = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
                $body        = $e->hasResponse() ? $this->decode($e->getResponse()) : [];
                $errorCode   = $body['error_code'] ?? $body['error'] ?? '';

                // Rate limited — retry after delay
                if ($statusCode === 429) {
                    $retryAfter = (int) ($e->getResponse()?->getHeaderLine('Retry-After') ?? 60);
                    if ($attempt < self::MAX_RETRIES) {
                        sleep(max($retryAfter, self::RETRY_DELAYS[$attempt] ?? 8));
                        $attempt++;
                        continue;
                    }
                    throw new RateLimitException($body['message'] ?? 'Rate limit exceeded.', $retryAfter, $body);
                }

                // Server errors — exponential backoff retry
                if ($statusCode >= 500 && $attempt < self::MAX_RETRIES) {
                    sleep(self::RETRY_DELAYS[$attempt] ?? 8);
                    $attempt++;
                    continue;
                }

                throw $this->mapException($statusCode, $errorCode, $body, $e);
            }
        }
    }

    private function mapException(int $status, string $errorCode, array $body, \Throwable $prev): OpesCareException
    {
        $message = $body['message'] ?? "HTTP {$status} error.";

        return match(true) {
            $status === 401                             => new AuthenticationException($message, $body, $status, $prev),
            $status === 403 && $errorCode === 'CONSENT_REQUIRED'   => new ConsentRequiredException($message, $body, $status, $prev),
            $status === 403                             => new AuthorizationException($message, $body, $status, $prev),
            $status === 404                             => new NotFoundException($message, $body, $status, $prev),
            $status === 409                             => new IdempotencyConflictException($message, $body, $status, $prev),
            $status === 422                             => new ValidationException($message, $body, $status, $prev),
            $status >= 500                              => new ServerException($message, $body, $status, $prev),
            default                                    => new OpesCareException($message, $body, $status, $prev),
        };
    }

    private function decode(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        if (empty($body)) {
            return [];
        }
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : ['raw' => $body];
    }
}
