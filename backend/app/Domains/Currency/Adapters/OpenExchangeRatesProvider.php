<?php

namespace App\Domains\Currency\Adapters;

use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Currency\DTOs\ProviderRates;
use App\Domains\Currency\Exceptions\ExchangeRateProviderFailed;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Rate;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Open Exchange Rates adapter (INTEGRATIONS.md section 5). Status: built and tested with mocks
 * only, awaiting credentials; never exercised against the live API.
 *
 * Rates are read from the raw response body as decimal text, because decoding JSON would turn
 * them into floats.
 */
class OpenExchangeRatesProvider implements ExchangeRateProvider
{
    private const int MAX_FUTURE_SKEW_SECONDS = 300;

    public function __construct(private SettingsService $settings) {}

    public function name(): string
    {
        return 'open_exchange_rates';
    }

    public function latest(string $base, array $symbols): ProviderRates
    {
        $appId = $this->appId();

        try {
            $response = $this->client()->get('latest.json', ['app_id' => $appId, 'symbols' => implode(',', $symbols)]);
        } catch (ConnectionException) {
            throw new ExchangeRateProviderFailed('The exchange rate provider timed out or could not be reached.');
        }

        if ($response->failed()) {
            throw new ExchangeRateProviderFailed("The exchange rate provider returned HTTP {$response->status()}.");
        }

        $payload = $response->json();

        if (! is_array($payload) || ! is_int($payload['timestamp'] ?? null) || ! is_string($payload['base'] ?? null)) {
            throw new ExchangeRateProviderFailed('The exchange rate provider returned an unexpected response.');
        }

        if (strtoupper($payload['base']) !== $base) {
            throw new ExchangeRateProviderFailed('The exchange rate provider returned rates for a different base currency.');
        }

        $capturedAt = CarbonImmutable::createFromTimestampUTC($payload['timestamp']);

        if ($capturedAt->isAfter(now()->addSeconds(self::MAX_FUTURE_SKEW_SECONDS))) {
            throw new ExchangeRateProviderFailed('The exchange rate provider returned a timestamp in the future.');
        }

        return new ProviderRates($base, $this->name(), $capturedAt, $this->parseRates($response->body(), $symbols));
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(config('services.open_exchange_rates.base_url'))
            ->connectTimeout((int) config('services.open_exchange_rates.connect_timeout'))
            ->timeout((int) config('services.open_exchange_rates.timeout'))
            ->acceptJson();
    }

    private function appId(): string
    {
        $appId = $this->settings->get('currency', 'oxr_app_id') ?? config('services.open_exchange_rates.app_id');

        if (! is_string($appId) || $appId === '') {
            throw new ExchangeRateProviderFailed('The exchange rate provider has no credentials configured.');
        }

        return $appId;
    }

    /**
     * @param  list<string>  $symbols
     * @return array<string, Rate>
     */
    private function parseRates(string $body, array $symbols): array
    {
        if (preg_match('/"rates"\s*:\s*\{([^}]*)\}/', $body, $section) !== 1) {
            throw new ExchangeRateProviderFailed('The exchange rate provider returned an unexpected response.');
        }

        preg_match_all('/"([A-Z]{3})"\s*:\s*([0-9.eE+-]+)/', $section[1], $matches, PREG_SET_ORDER);

        $rates = [];

        foreach ($matches as [, $code, $literal]) {
            if (! in_array($code, $symbols, true)) {
                continue;
            }

            try {
                $rates[$code] = Rate::of($this->expandExponent($literal));
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return $rates;
    }

    /**
     * Convert exponent notation such as 1.2e-05 to plain decimal text without using floats.
     */
    private function expandExponent(string $literal): string
    {
        if (preg_match('/^(\d+(?:\.\d+)?)[eE]([+-]?\d{1,2})$/', $literal, $parts) !== 1) {
            return $literal;
        }

        $power = bcpow('10', ltrim($parts[2], '+'), 30);

        return rtrim(rtrim(bcmul($parts[1], $power, 30), '0'), '.');
    }
}
