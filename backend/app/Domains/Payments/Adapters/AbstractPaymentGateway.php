<?php

namespace App\Domains\Payments\Adapters;

use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Exceptions\GatewayCapabilityNotSupported;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway as GatewayAccount;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Payments\Models\PaymentGatewayCurrency;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Services\HttpFailureClassifier;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Shared plumbing for gateway adapters: credentials for the gateway's current mode (decrypted
 * only here, never returned), per-gateway minor units, HTTP timeouts and redacted call logs.
 * Logs carry the operation, method, host and path, status and duration; never a body, a query
 * string or a header value.
 */
abstract class AbstractPaymentGateway implements PaymentGateway
{
    /** @var array<string, mixed>|null */
    private ?array $credentialCache = null;

    private ?string $webhookSecretCache = null;

    public function __construct(protected GatewayAccount $account) {}

    public function key(): string
    {
        return $this->account->key;
    }

    public function capture(Payment $payment): VerifiedPaymentState
    {
        throw new GatewayCapabilityNotSupported($this->key(), 'capture');
    }

    /**
     * Default minor exponent when the merchant currency row does not state one.
     */
    protected function defaultExponent(string $currency): int
    {
        return 2;
    }

    public function exponent(string $currency): int
    {
        $row = PaymentGatewayCurrency::query()
            ->where('payment_gateway_id', $this->account->id)
            ->where('currency', strtoupper($currency))
            ->first();

        return $row?->minor_exponent ?? $this->defaultExponent(strtoupper($currency));
    }

    /**
     * Money to an integer count of the provider's minor units. The amount must already be
     * rounded for the currency: a fractional residue is refused rather than rounded away.
     */
    public function toMinorUnits(Money $money): int
    {
        $exponent = $this->exponent($money->currency);
        $scaled = bcmul($money->amount, bcpow('10', (string) $exponent), Money::SCALE);

        if (bccomp($scaled, bcadd($scaled, '0', 0), Money::SCALE) !== 0) {
            throw new InvalidArgumentException("{$money} has more decimals than {$this->key()} accepts for {$money->currency}.");
        }

        return (int) bcadd($scaled, '0', 0);
    }

    public function toGatewayAmount(Money $money): int|string
    {
        return $this->toMinorUnits($money);
    }

    public function fromMinorUnits(int|string $minor, string $currency): Money
    {
        $currency = strtoupper($currency);

        return Money::of(bcdiv((string) $minor, bcpow('10', (string) $this->exponent($currency)), Money::SCALE), $currency);
    }

    /**
     * Money as a decimal string with exactly the provider's number of decimals (PayPal).
     */
    public function toDecimalString(Money $money): string
    {
        $exponent = $this->exponent($money->currency);
        $value = bcadd($money->amount, '0', $exponent);

        if (bccomp($value, $money->amount, Money::SCALE) !== 0) {
            throw new InvalidArgumentException("{$money} has more decimals than {$this->key()} accepts for {$money->currency}.");
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GatewayNotConfigured
     */
    protected function credentials(): array
    {
        if ($this->credentialCache !== null) {
            return $this->credentialCache;
        }

        $row = $this->credentialRow();
        $values = $row?->credentials;

        if (! is_array($values) || $values === []) {
            throw new GatewayNotConfigured("{$this->account->name} has no credentials for {$this->account->mode->value} mode.");
        }

        $this->credentialCache = $values;
        $this->webhookSecretCache = $row->webhook_secret;

        return $values;
    }

    /**
     * @throws GatewayNotConfigured
     */
    protected function credential(string $name): string
    {
        $value = $this->credentials()[$name] ?? null;

        if (! is_string($value) || $value === '') {
            throw new GatewayNotConfigured("{$this->account->name} is missing the {$name} credential.");
        }

        return $value;
    }

    /**
     * @throws GatewayNotConfigured
     */
    protected function webhookSecret(): string
    {
        $this->credentials();

        if ($this->webhookSecretCache === null || $this->webhookSecretCache === '') {
            throw new GatewayNotConfigured("{$this->account->name} has no webhook secret for {$this->account->mode->value} mode.");
        }

        return $this->webhookSecretCache;
    }

    protected function isProduction(): bool
    {
        return $this->account->mode === EnvironmentMode::Production;
    }

    protected function http(): PendingRequest
    {
        return Http::connectTimeout((int) config('payments.http.connect_timeout'))
            ->timeout((int) config('payments.http.timeout'))
            ->acceptJson();
    }

    /**
     * Send and translate transport failures. `ambiguous` is false only when the request
     * provably never left (DNS, refused, TLS, connect timeout).
     *
     * @throws GatewayRequestFailed
     */
    protected function sendLogged(string $operation, PendingRequest $request, string $method, string $url, mixed $body = null): Response
    {
        $started = hrtime(true);

        try {
            $response = $body === null ? $request->send($method, $url) : $request->send($method, $url, $body);
        } catch (ConnectionException $exception) {
            $this->log($operation, $method, $url, null, $started, HttpFailureClassifier::label($exception));

            throw new GatewayRequestFailed("{$this->key()} {$operation}: ".HttpFailureClassifier::label($exception), ambiguous: ! HttpFailureClassifier::wasNotSent($exception));
        }

        $this->log($operation, $method, $url, $response->status(), $started, null);

        return $response;
    }

    /**
     * A non-2xx response as a failure: 5xx and 429 may have run and are ambiguous; 4xx did not.
     */
    protected function failure(string $operation, Response $response, ?string $providerCode = null): GatewayRequestFailed
    {
        $status = $response->status();

        return new GatewayRequestFailed(
            "{$this->key()} {$operation}: HTTP {$status}",
            ambiguous: $status >= 500 || $status === 429,
            httpStatus: $status,
            providerCode: $providerCode,
        );
    }

    protected function log(string $operation, string $method, string $url, ?int $status, int|float $started, ?string $error): void
    {
        $parts = parse_url($url);

        Log::info('payments.api', [
            'gateway' => $this->key(),
            'operation' => $operation,
            'method' => strtoupper($method),
            'endpoint' => ($parts['host'] ?? '').($parts['path'] ?? ''),
            'status' => $status,
            'error' => $error,
            'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
        ]);
    }

    private function credentialRow(): ?PaymentGatewayCredential
    {
        return PaymentGatewayCredential::query()
            ->where('payment_gateway_id', $this->account->id)
            ->where('mode', $this->account->mode)
            ->first();
    }
}
