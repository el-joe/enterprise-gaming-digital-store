<?php

namespace App\Domains\Currency\Adapters;

use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Currency\DTOs\ProviderRates;
use App\Domains\Currency\Exceptions\ExchangeRateProviderFailed;
use App\Domains\Shared\ValueObjects\Rate;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Free, keyless daily rates from the open-source currency-api (fawazahmed0), published on
 * jsDelivr with an official Cloudflare Pages mirror tried when the first URL fails. Covers about
 * 200 currencies including EGP, SAR and AED. The response is
 * `{"date":"YYYY-MM-DD","usd":{"aed":3.6725,...}}` with lower-case codes.
 *
 * Rates are read from the raw body as decimal text (no floats). The publication date is checked:
 * data older than `max_staleness_days` is refused, so a frozen feed never looks fresh. The rate
 * is recorded as captured at fetch time because the feed carries only a date; the existing
 * change-limit and approval rules in ExchangeRateUpdater still apply.
 */
class CurrencyApiProvider implements ExchangeRateProvider
{
    public function name(): string
    {
        return 'currency_api';
    }

    public function latest(string $base, array $symbols): ProviderRates
    {
        $base = strtoupper($base);
        $key = strtolower($base);
        $errors = [];

        foreach ((array) config('services.currency_api.urls', []) as $template) {
            $url = str_replace('{base}', $key, (string) $template);

            try {
                $response = Http::connectTimeout((int) config('services.currency_api.connect_timeout', 5))
                    ->timeout((int) config('services.currency_api.timeout', 15))
                    ->acceptJson()
                    ->get($url);
            } catch (ConnectionException) {
                $errors[] = 'unreachable';

                continue;
            }

            if ($response->failed()) {
                $errors[] = "HTTP {$response->status()}";

                continue;
            }

            return $this->parse($response->body(), $base, $key, $symbols);
        }

        throw new ExchangeRateProviderFailed('The exchange rate provider could not be reached ('.implode(', ', $errors ?: ['no URL configured']).').');
    }

    /**
     * @param  list<string>  $symbols
     */
    private function parse(string $body, string $base, string $key, array $symbols): ProviderRates
    {
        if (preg_match('/"date"\s*:\s*"(\d{4}-\d{2}-\d{2})"/', $body, $date) !== 1
            || preg_match('/"'.preg_quote($key, '/').'"\s*:\s*\{([^}]*)\}/', $body, $section) !== 1) {
            throw new ExchangeRateProviderFailed('The exchange rate provider returned an unexpected response.');
        }

        $published = CarbonImmutable::createFromFormat('Y-m-d', $date[1], 'UTC')->startOfDay();

        if ($published->isAfter(now()->addDay()) || $published->lt(now()->subDays((int) config('services.currency_api.max_staleness_days', 3))->startOfDay())) {
            throw new ExchangeRateProviderFailed("The exchange rate provider's data is out of date ({$date[1]}).");
        }

        preg_match_all('/"([a-z]{3})"\s*:\s*([0-9.eE+-]+)/', $section[1], $matches, PREG_SET_ORDER);

        $wanted = array_flip($symbols);
        $rates = [];

        foreach ($matches as [, $code, $literal]) {
            $code = strtoupper($code);

            if (! isset($wanted[$code])) {
                continue;
            }

            try {
                $rates[$code] = Rate::of($this->expandExponent($literal));
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return new ProviderRates($base, $this->name(), CarbonImmutable::now(), $rates);
    }

    /**
     * Convert exponent notation such as 1.2e-05 to plain decimal text without using floats.
     */
    private function expandExponent(string $literal): string
    {
        if (preg_match('/^(\d+(?:\.\d+)?)[eE]([+-]?\d{1,2})$/', $literal, $parts) !== 1) {
            return $literal;
        }

        $exponent = (int) $parts[2];
        $scaled = $exponent >= 0
            ? bcmul($parts[1], bcpow('10', (string) $exponent, 0), 30)
            : bcdiv($parts[1], bcpow('10', (string) -$exponent, 0), 30);

        return rtrim(rtrim($scaled, '0'), '.');
    }
}
