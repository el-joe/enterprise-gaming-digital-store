<?php

use App\Domains\Currency\Adapters\CurrencyApiProvider;
use App\Domains\Currency\Adapters\OpenExchangeRatesProvider;
use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Currency\Exceptions\ExchangeRateProviderFailed;
use Illuminate\Support\Facades\Http;

/*
| Free daily rates from the currency-api (jsDelivr, with the Cloudflare Pages mirror as fallback).
*/

const CURRENCY_API_PRIMARY = 'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/usd.json';
const CURRENCY_API_MIRROR = 'https://latest.currency-api.pages.dev/v1/currencies/usd.json';

function currencyApiBody(?string $date = null): string
{
    $date ??= now()->toDateString();

    return '{"date":"'.$date.'","usd":{"1inch":13.41132458,"aed":3.6725,"egp":48.53,"sar":3.75,"eur":0.92876543210123,"btc":1.2e-05}}';
}

it('parses the requested rates as exact decimals from the primary URL', function (): void {
    Http::fake([CURRENCY_API_PRIMARY => Http::response(currencyApiBody())]);

    $rates = app(CurrencyApiProvider::class)->latest('USD', ['EGP', 'SAR', 'AED', 'EUR', 'BTC']);

    expect($rates->source)->toBe('currency_api')
        ->and($rates->base)->toBe('USD')
        ->and($rates->rates['EGP']->value)->toStartWith('48.53')
        ->and($rates->rates['EUR']->value)->toStartWith('0.928765432101')
        ->and($rates->rates['BTC']->value)->toStartWith('0.000012')
        ->and(array_keys($rates->rates))->not->toContain('1INCH');
});

it('falls back to the mirror when jsDelivr fails', function (): void {
    Http::fake([
        CURRENCY_API_PRIMARY => Http::response('', 503),
        CURRENCY_API_MIRROR => Http::response(currencyApiBody()),
    ]);

    expect(array_keys(app(CurrencyApiProvider::class)->latest('USD', ['SAR'])->rates))->toBe(['SAR']);
    Http::assertSentCount(2);
});

it('refuses data older than the staleness limit', function (): void {
    Http::fake(['*' => Http::response(currencyApiBody(now()->subDays(10)->toDateString()))]);

    expect(fn () => app(CurrencyApiProvider::class)->latest('USD', ['EGP']))->toThrow(ExchangeRateProviderFailed::class, 'out of date');
});

it('refuses a malformed response', function (): void {
    Http::fake(['*' => Http::response('{"unexpected":true}')]);

    expect(fn () => app(CurrencyApiProvider::class)->latest('USD', ['EGP']))->toThrow(ExchangeRateProviderFailed::class, 'unexpected');
});

it('reports when every URL fails', function (): void {
    Http::fake(['*' => Http::response('', 500)]);

    expect(fn () => app(CurrencyApiProvider::class)->latest('USD', ['EGP']))->toThrow(ExchangeRateProviderFailed::class, 'could not be reached');
});

it('uses the provider chosen in the currency settings, currency_api by default', function (): void {
    expect(app(ExchangeRateProvider::class))->toBeInstanceOf(CurrencyApiProvider::class);

    config(['services.exchange_rates.provider' => 'open_exchange_rates']);
    expect(app(ExchangeRateProvider::class))->toBeInstanceOf(OpenExchangeRatesProvider::class);
});
