<?php

namespace OpesCare\Tests;

use OpesCare\Exceptions\WebhookSignatureException;
use OpesCare\Modules\Webhooks;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Webhooks::verifySignature() — the most security-critical SDK method.
 *
 * These tests do NOT need a running OpesCare server.
 */
class WebhookVerificationTest extends TestCase
{
    private Webhooks $webhooks;
    private string   $secret = 'whsec_test_secret_for_unit_tests';

    protected function setUp(): void
    {
        // Webhooks signature verification is stateless — no HTTP client needed
        $this->webhooks = new class extends Webhooks {
            public function __construct() {} // bypass ApiClient requirement for unit tests
        };
    }

    private function makeSignature(string $payload, string $secret, int $timestamp): string
    {
        $signed = $timestamp . '.' . $payload;
        $sig    = hash_hmac('sha256', $signed, $secret);
        return "t={$timestamp},v1={$sig}";
    }

    public function test_valid_signature_passes(): void
    {
        $payload   = '{"type":"lab_result.released","data":{"health_id":"CM-HID-0001-0001-0001"}}';
        $timestamp = time();
        $header    = $this->makeSignature($payload, $this->secret, $timestamp);

        // No exception = pass
        $this->webhooks->verifySignature($payload, $header, $this->secret);
        $this->addToAssertionCount(1);
    }

    public function test_wrong_secret_throws(): void
    {
        $payload   = '{"type":"lab_result.released"}';
        $timestamp = time();
        $header    = $this->makeSignature($payload, $this->secret, $timestamp);

        $this->expectException(WebhookSignatureException::class);
        $this->webhooks->verifySignature($payload, $header, 'wrong_secret');
    }

    public function test_tampered_payload_throws(): void
    {
        $payload   = '{"type":"lab_result.released"}';
        $timestamp = time();
        $header    = $this->makeSignature($payload, $this->secret, $timestamp);

        $this->expectException(WebhookSignatureException::class);
        $this->webhooks->verifySignature('{"type":"tampered_payload"}', $header, $this->secret);
    }

    public function test_expired_timestamp_throws_replay_protection(): void
    {
        $payload   = '{"type":"lab_result.released"}';
        $timestamp = time() - 400; // 400 seconds ago — beyond 300s tolerance
        $header    = $this->makeSignature($payload, $this->secret, $timestamp);

        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessageMatches('/replay/i');
        $this->webhooks->verifySignature($payload, $header, $this->secret);
    }

    public function test_malformed_signature_header_throws(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->webhooks->verifySignature('{}', 'not_a_valid_signature_header', $this->secret);
    }

    public function test_parse_event_returns_array(): void
    {
        $payload = '{"id":"evt_123","type":"prescription.issued","version":"1.0","created_at":"2026-06-01T00:00:00Z","data":{},"meta":{}}';
        $event   = $this->webhooks->parseEvent($payload);

        $this->assertSame('evt_123', $event['id']);
        $this->assertSame('prescription.issued', $event['type']);
    }

    public function test_parse_event_throws_on_invalid_json(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->webhooks->parseEvent('not_json_at_all');
    }
}
