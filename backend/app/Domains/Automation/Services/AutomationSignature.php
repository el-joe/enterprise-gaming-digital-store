<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\DTOs\SignedRequest;

/**
 * HMAC-SHA256 signatures for both directions between Laravel and n8n. Inbound requests sign the
 * canonical form of SignedRequest with the service account's secret; outbound webhooks sign
 * `timestamp + "." + raw_body` with N8N_OUTBOUND_SECRET. Comparisons are constant-time.
 */
class AutomationSignature
{
    public function sign(SignedRequest $request, string $secret): string
    {
        return hash_hmac('sha256', $request->canonical(), $secret);
    }

    public function verify(SignedRequest $request, string $secret, string $signature): bool
    {
        return $secret !== '' && hash_equals($this->sign($request, $secret), strtolower($signature));
    }

    public function signOutbound(string $timestamp, string $body, string $secret): string
    {
        return hash_hmac('sha256', $timestamp.'.'.$body, $secret);
    }

    /**
     * A new random secret for a service account: 64 hex characters.
     */
    public function newSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
}
