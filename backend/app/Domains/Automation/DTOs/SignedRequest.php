<?php

namespace App\Domains\Automation\DTOs;

/**
 * The parts of an automation request that the HMAC covers.
 */
final readonly class SignedRequest
{
    public function __construct(
        public string $timestamp,
        public string $nonce,
        public string $method,
        public string $uri,
        public string $workflowId,
        public string $executionKey,
        public string $body,
    ) {}

    /**
     * Newline-joined: timestamp, nonce, upper-case method, path with query exactly as sent,
     * workflow id, execution key, raw body.
     */
    public function canonical(): string
    {
        return implode("\n", [$this->timestamp, $this->nonce, strtoupper($this->method), $this->uri, $this->workflowId, $this->executionKey, $this->body]);
    }
}
