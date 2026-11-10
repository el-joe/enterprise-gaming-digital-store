<?php

namespace App\Http\Requests\Storefront\Concerns;

use App\Domains\Payments\Enums\PaymentClient;
use Illuminate\Validation\Rule;

/**
 * The optional `client` field of place-order and retry-payment (M-A6, ADR-039). It names a
 * channel only (`web` or `app`); the return URLs are built by the server from configuration, so
 * a URL sent by the client is never read. Without the field the route group decides: the
 * session (browser) routes mean `web`, the stateless `/api/v1/mobile` routes mean `app`.
 */
trait ResolvesPaymentClient
{
    /**
     * @return array<string, mixed>
     */
    protected function paymentClientRules(): array
    {
        return ['client' => ['sometimes', 'nullable', 'string', Rule::enum(PaymentClient::class)]];
    }

    public function paymentClient(): PaymentClient
    {
        $client = $this->validated('client');

        if (is_string($client)) {
            return PaymentClient::from($client);
        }

        return $this->hasSession() ? PaymentClient::Web : PaymentClient::App;
    }
}
