<?php

use App\Domains\Currency\Adapters\OpenExchangeRatesProvider;
use App\Domains\Currency\Exceptions\ExchangeRateProviderFailed;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const OXR_TEST_APP_ID = 'canary-app-id';

beforeEach(fn () => config(['services.open_exchange_rates.app_id' => OXR_TEST_APP_ID]));

function oxrBody(array $overrides = [], string $rates = '"EGP": 48.123456789012, "EUR": 0.92'): string
{
    $meta = ['timestamp' => now()->getTimestamp(), 'base' => 'USD', ...$overrides];

    return substr(json_encode($meta), 0, -1).', "rates": {'.$rates.'}}';
}

it('parses rates from the raw body without losing precision', function (): void {
    Http::fake(['openexchangerates.org/*' => Http::response(oxrBody())]);

    $rates = app(OpenExchangeRatesProvider::class)->latest('USD', ['EGP', 'EUR']);

    expect($rates->rates['EGP']->value)->toBe('48.123456789012')
        ->and($rates->rates['EUR']->value)->toBe('0.920000000000')
        ->and($rates->base)->toBe('USD')
        ->and($rates->source)->toBe('open_exchange_rates');
});

it('sends the app id and symbols to the documented endpoint', function (): void {
    Http::fake(['openexchangerates.org/*' => Http::response(oxrBody())]);

    app(OpenExchangeRatesProvider::class)->latest('USD', ['EGP', 'EUR']);

    Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://openexchangerates.org/api/latest.json')
        && $request['app_id'] === OXR_TEST_APP_ID
        && $request['symbols'] === 'EGP,EUR');
});

it('prefers the encrypted setting over the environment for credentials', function (): void {
    app(SettingsService::class)->set('currency', 'oxr_app_id', 'setting-id', SettingType::String, encrypted: true);
    Http::fake(['openexchangerates.org/*' => Http::response(oxrBody())]);

    app(OpenExchangeRatesProvider::class)->latest('USD', ['EGP']);

    Http::assertSent(fn (Request $request): bool => $request['app_id'] === 'setting-id');
});

it('expands exponent notation and ignores symbols not requested', function (): void {
    Http::fake(['openexchangerates.org/*' => Http::response(oxrBody(rates: '"BTC": 1.5e-05, "EGP": 48, "XXX": 3'))]);

    $rates = app(OpenExchangeRatesProvider::class)->latest('USD', ['BTC', 'EGP']);

    expect($rates->rates['BTC']->value)->toBe('0.000015000000')
        ->and($rates->rates)->not->toHaveKey('XXX');
});

it('skips invalid rates', function (): void {
    Http::fake(['openexchangerates.org/*' => Http::response(oxrBody(rates: '"EGP": 0, "EUR": 0.9'))]);

    expect(app(OpenExchangeRatesProvider::class)->latest('USD', ['EGP', 'EUR'])->rates)->toHaveKeys(['EUR'])->not->toHaveKey('EGP');
});

it('fails cleanly, without leaking the credential', function (Closure $fake, string $message): void {
    Http::fake(['openexchangerates.org/*' => $fake]);

    try {
        app(OpenExchangeRatesProvider::class)->latest('USD', ['EGP']);
        $this->fail('Expected the provider call to fail.');
    } catch (ExchangeRateProviderFailed $exception) {
        expect($exception->getMessage())->toContain($message)->not->toContain(OXR_TEST_APP_ID)
            ->and($exception->getPrevious())->toBeNull();
    }
})->with([
    'timeout' => [fn () => throw new ConnectionException('cURL error 28: timed out for https://openexchangerates.org/api/latest.json?app_id='.OXR_TEST_APP_ID), 'timed out'],
    'unauthorized' => [fn () => Http::response(['error' => true], 401), 'HTTP 401'],
    'server error' => [fn () => Http::response('oops', 500), 'HTTP 500'],
    'malformed body' => [fn () => Http::response('<html>'), 'unexpected response'],
    'wrong base' => [fn () => Http::response(oxrBody(['base' => 'EUR'])), 'different base'],
    'future timestamp' => [fn () => Http::response(oxrBody(['timestamp' => now()->addHour()->getTimestamp()])), 'future'],
    'no rates object' => [fn () => Http::response(['timestamp' => now()->getTimestamp(), 'base' => 'USD']), 'unexpected response'],
]);

it('refuses to call the provider without credentials', function (): void {
    config(['services.open_exchange_rates.app_id' => null]);
    Http::fake();

    expect(fn () => app(OpenExchangeRatesProvider::class)->latest('USD', ['EGP']))
        ->toThrow(ExchangeRateProviderFailed::class, 'no credentials');

    Http::assertNothingSent();
});
