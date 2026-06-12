<?php
namespace OpesCare\Exceptions;
class RateLimitException extends OpesCareException
{
    public function __construct(string $message, public readonly int $retryAfter = 60, ?array $body = null)
    {
        parent::__construct($message, $body, 429);
    }
}
