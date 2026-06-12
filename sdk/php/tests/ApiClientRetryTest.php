<?php

namespace OpesCare\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use OpesCare\Exceptions\AuthenticationException;
use OpesCare\Exceptions\RateLimitException;
use OpesCare\Exceptions\ServerException;
use OpesCare\Http\ApiClient;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ApiClient retry logic and exception mapping.
 * Uses Guzzle's MockHandler to simulate server responses without a real HTTP connection.
 */
class ApiClientRetryTest extends TestCase
{
    private function makeClient(array $responses): ApiClient
    {
        $mock    = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $guzzle  = new GuzzleClient(['handler' => $handler]);

        // Use reflection to inject mock Guzzle into ApiClient
        $client = new ApiClient('http://localhost', 'test_token');
        $ref    = new \ReflectionProperty(ApiClient::class, 'http');
        $ref->setAccessible(true);
        $ref->setValue($client, $guzzle);

        return $client;
    }

    public function test_successful_get_returns_array(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode(['status' => 'ok', 'data' => ['health_id' => 'CM-HID-0001']])),
        ]);

        $result = $client->get('/test');
        $this->assertSame('ok', $result['status']);
    }

    public function test_401_throws_authentication_exception(): void
    {
        $client = $this->makeClient([
            new Response(401, [], json_encode(['message' => 'Invalid token.', 'error_code' => 'AUTH_FAILED'])),
        ]);

        $this->expectException(AuthenticationException::class);
        $client->get('/protected');
    }

    public function test_rate_limit_exception_carries_retry_after(): void
    {
        $client = $this->makeClient([
            new Response(429, ['Retry-After' => '45'], json_encode(['message' => 'Rate limited.'])),
            new Response(429, ['Retry-After' => '45'], json_encode(['message' => 'Rate limited.'])),
            new Response(429, ['Retry-After' => '45'], json_encode(['message' => 'Rate limited.'])),
            new Response(429, ['Retry-After' => '45'], json_encode(['message' => 'Rate limited.'])),
            new Response(429, ['Retry-After' => '45'], json_encode(['message' => 'Rate limited.'])),
        ]);

        try {
            $client->get('/limited');
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertSame(45, $e->retryAfter);
        }
    }

    public function test_retry_after_header_respected_over_backoff_delay(): void
    {
        // Server says wait 30s; backoff schedule starts at 1s.
        // With max(), we wait 30s (respecting server). With min() we'd ignore it.
        // We verify the retry_after value is preserved correctly on the thrown exception.
        $client = $this->makeClient([
            new Response(429, ['Retry-After' => '30'], json_encode(['message' => 'Limited.'])),
            new Response(429, ['Retry-After' => '30'], json_encode(['message' => 'Limited.'])),
            new Response(429, ['Retry-After' => '30'], json_encode(['message' => 'Limited.'])),
            new Response(429, ['Retry-After' => '30'], json_encode(['message' => 'Limited.'])),
            new Response(429, ['Retry-After' => '30'], json_encode(['message' => 'Limited.'])),
        ]);

        try {
            $client->get('/endpoint');
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            // retryAfter must match what the server sent, not the backoff floor
            $this->assertSame(30, $e->retryAfter);
        }
    }
}
