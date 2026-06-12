<?php

namespace OpesCare\Exceptions;

class OpesCareException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?array $responseBody = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
