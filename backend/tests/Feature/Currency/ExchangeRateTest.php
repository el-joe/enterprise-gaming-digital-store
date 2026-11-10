<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Currency\Adapters\OpenExchangeRatesProvider;
use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Currency\DTOs\ProviderRates;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Exceptions\ExchangeRateProviderFailed;
use App\Domains\Currency\Exceptions\ExchangeRateStale;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Models\ExchangeRateHistory;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Currency\Services\ExchangeRateUpdater;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Shared\ValueObjects\Rate;
use Carbon\CarbonImmutable;
use Database\Factories\Currency\CurrencyFactory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function (): void {
    CurrencyFactory::ensure('USD');
    CurrencyFactory::ensure('EGP');
});

/**
 * Bind a provider that returns these rates.
 *
 * @param  array<string, string>  $rates
 */
function providerReturning(array $rates, ?CarbonImmutable $at = null): void
{
    $at ??= CarbonImmutable::now();

    app()->bind(ExchangeRateProvider::class, fn () => new class($rates, $at) implements ExchangeRateProvider
    {
        public function __construct(private array $rates, private CarbonImmutable $at) {}

        public function name(): string
        {
            return 'fake';
        }

        public function latest(string $base, array $symbols): ProviderRates
        {
            return new ProviderRates($base, 'fake', $this->at, array_map(Rate::of(...), array_intersect_key($this->rates, array_flip($symbols))));
        }
    });
}

function refreshRates(): void
{
    app(ExchangeRateUpdater::class)->refresh();
}

function effectiveRate(string $currency = 'EGP'): string
{
    return app(ExchangeRateRegistry::class)->resolve($currency)->rate->value;
}

describe('fetching', function (): void {
    it('stores a first rate with markup and appends history', function (): void {
        Currency::query()->where('code', 'EGP')->update(['markup_pct' => '2.5']);
        providerReturning(['EGP' => '50']);

        refreshRates();

        $row = ExchangeRate::query()->where('quote_currency', 'EGP')->sole();

        expect($row->provider_rate)->toBe('50.000000000000')
            ->and($row->effective_rate)->toBe('51.250000000000')
            ->and($row->source)->toBe('fake')
            ->and(ExchangeRateHistory::query()->where('quote_currency', 'EGP')->count())->toBe(1);
    });

    it('does not switch a currency on when a rate arrives', function (): void {
        Currency::query()->where('code', 'EGP')->update(['is_active' => false]);
        providerReturning(['EGP' => '50']);

        refreshRates();

        expect(Currency::query()->find('EGP')->is_active)->toBeFalse();
    });

    it('treats a repeat of the same provider timestamp as a duplicate', function (): void {
        $at = CarbonImmutable::now();
        providerReturning(['EGP' => '50'], $at);

        refreshRates();
        refreshRates();

        expect(ExchangeRateHistory::query()->count())->toBe(1);
    });

    it('applies an ordinary change', function (): void {
        givenRate('EGP', '50', ['fetched_at' => now()->subHour()]);
        providerReturning(['EGP' => '52']);

        refreshRates();

        expect(effectiveRate())->toBe('52.000000000000');
    });

    it('leaves rates untouched and reports failure on a provider timeout', function (): void {
        givenRate('EGP', '50');
        app()->bind(ExchangeRateProvider::class, fn () => new class implements ExchangeRateProvider
        {
            public function name(): string
            {
                return 'fake';
            }

            public function latest(string $base, array $symbols): ProviderRates
            {
                throw new ExchangeRateProviderFailed('timed out');
            }
        });

        expect(Artisan::call('currency:fetch-rates'))->toBe(1)
            ->and(effectiveRate())->toBe('50.000000000000');
    });

    it('reports a currency the provider did not return', function (): void {
        providerReturning([]);

        expect(app(ExchangeRateUpdater::class)->refresh()->missing)->toBe(['EGP']);
    });

    it('never stores a rate for the base currency', function (): void {
        providerReturning(['USD' => '2', 'EGP' => '50']);

        refreshRates();

        expect(ExchangeRate::query()->where('quote_currency', 'USD')->exists())->toBeFalse();
    });

    it('is scheduled hourly', function (): void {
        Artisan::call('schedule:list');

        expect(Artisan::output())->toContain('currency:fetch-rates')->toContain('pricing:recalculate-dirty');
    });
});

describe('spike guard', function (): void {
    beforeEach(fn () => givenRate('EGP', '50', ['fetched_at' => now()->subHour()]));

    it('holds a change above the threshold and keeps the old rate', function (): void {
        providerReturning(['EGP' => '60']);

        refreshRates();

        $row = ExchangeRate::query()->where('quote_currency', 'EGP')->sole();

        expect($row->effective_rate)->toBe('50.000000000000')
            ->and($row->pending_rate)->toBe('60.000000000000')
            ->and($row->pending_since)->not->toBeNull()
            ->and(ExchangeRateHistory::query()->count())->toBe(0);
        $this->assertDatabaseHas('audit_logs', ['action' => 'currency.rate_held']);
    });

    it('allows a change exactly at the threshold', function (): void {
        providerReturning(['EGP' => '55']);

        refreshRates();

        expect(effectiveRate())->toBe('55.000000000000');
    });

    it('holds a spike downward too', function (): void {
        providerReturning(['EGP' => '30']);

        refreshRates();

        expect(effectiveRate())->toBe('50.000000000000');
    });

    it('does not refresh freshness while a rate is held, so it eventually goes stale', function (): void {
        $this->travel(2)->days();
        providerReturning(['EGP' => '60']);

        refreshRates();

        expect(app(ExchangeRateRegistry::class)->resolve('EGP')->isStale)->toBeTrue();
    });

    it('clears the hold when the provider returns to normal', function (): void {
        providerReturning(['EGP' => '60'], CarbonImmutable::now()->subMinutes(10));
        refreshRates();

        providerReturning(['EGP' => '51']);
        refreshRates();

        expect(ExchangeRate::query()->sole()->pending_rate)->toBeNull()
            ->and(effectiveRate())->toBe('51.000000000000');
    });

    describe('decisions', function (): void {
        beforeEach(function (): void {
            providerReturning(['EGP' => '60']);
            refreshRates();
        });

        it('needs currency.manage', function (): void {
            actingAsStaffWith(['pricing.view']);

            $this->postJson('/admin/api/exchange-rates/EGP/approve-pending', ['reason' => 'ok'])->assertForbidden();
            expect(effectiveRate())->toBe('50.000000000000');
        });

        it('approves a held rate with a reason, and audits it', function (): void {
            actingAsStaffWith(['currency.manage']);

            $this->postJson('/admin/api/exchange-rates/EGP/approve-pending', ['reason' => 'Confirmed devaluation'])
                ->assertOk()
                ->assertJsonPath('data.effective_rate', '60.000000000000');

            expect(effectiveRate())->toBe('60.000000000000')
                ->and(AuditLog::query()->where('action', 'currency.rate_approved')->sole()->reason)->toBe('Confirmed devaluation');
        });

        it('requires a reason', function (): void {
            actingAsStaffWith(['currency.manage']);

            $this->postJson('/admin/api/exchange-rates/EGP/approve-pending', [])->assertUnprocessable();
        });

        it('refuses a duplicate approval', function (): void {
            actingAsStaffWith(['currency.manage']);

            $this->postJson('/admin/api/exchange-rates/EGP/approve-pending', ['reason' => 'x'])->assertOk();
            $this->postJson('/admin/api/exchange-rates/EGP/approve-pending', ['reason' => 'x'])->assertStatus(409);

            expect(ExchangeRateHistory::query()->count())->toBe(1);
        });

        it('rejects a held rate and keeps the old one', function (): void {
            actingAsStaffWith(['currency.manage']);

            $this->postJson('/admin/api/exchange-rates/EGP/reject-pending', ['reason' => 'Bad data'])->assertOk();

            expect(ExchangeRate::query()->sole()->pending_rate)->toBeNull()
                ->and(effectiveRate())->toBe('50.000000000000');
        });
    });
});

describe('manual override', function (): void {
    it('needs currency.manage', function (): void {
        givenRate('EGP', '50');
        actingAsStaffWith(['pricing.manage', 'pricing.view']);

        $this->postJson('/admin/api/exchange-rates/EGP/override', ['rate' => '70', 'reason' => 'x'])->assertForbidden();
    });

    it('replaces the provider rate and ignores markup, with audit and history', function (): void {
        Currency::query()->where('code', 'EGP')->update(['markup_pct' => '5']);
        givenRate('EGP', '50');
        $staff = actingAsStaffWith(['currency.manage']);

        $this->postJson('/admin/api/exchange-rates/EGP/override', ['rate' => '70', 'reason' => 'Bank rate'])
            ->assertOk()
            ->assertJsonPath('data.is_manual_active', true);

        $resolved = app(ExchangeRateRegistry::class)->resolve('EGP');

        expect($resolved->rate->value)->toBe('70.000000000000')
            ->and($resolved->isManual)->toBeTrue()
            ->and($resolved->source)->toBe('manual')
            ->and(ExchangeRateHistory::query()->sole()->changed_by)->toBe($staff->id)
            ->and(AuditLog::query()->where('action', 'currency.override_set')->sole()->reason)->toBe('Bank rate');
    });

    it('requires a positive decimal string rate', function (mixed $rate): void {
        givenRate('EGP', '50');
        actingAsStaffWith(['currency.manage']);

        $this->postJson('/admin/api/exchange-rates/EGP/override', ['rate' => $rate, 'reason' => 'x'])->assertUnprocessable();
    })->with([0, '0', -1, 12.5, 'abc']);

    it('can supply the first rate for a currency', function (): void {
        actingAsStaffWith(['currency.manage']);

        $this->postJson('/admin/api/exchange-rates/EGP/override', ['rate' => '70', 'reason' => 'First rate'])->assertOk();

        expect(effectiveRate())->toBe('70.000000000000');
    });

    it('takes precedence over later provider fetches, while the provider rate keeps updating', function (): void {
        givenRate('EGP', '50', ['fetched_at' => now()->subHour()]);
        actingAsStaffWith(['currency.manage']);
        $this->postJson('/admin/api/exchange-rates/EGP/override', ['rate' => '70', 'reason' => 'x'])->assertOk();

        providerReturning(['EGP' => '51']);
        refreshRates();

        $row = ExchangeRate::query()->sole();

        expect(effectiveRate())->toBe('70.000000000000')
            ->and($row->provider_rate)->toBe('51.000000000000');
    });

    it('is not treated as stale while active', function (): void {
        givenRate('EGP', '50', ['fetched_at' => now()->subDays(30), 'manual_rate' => '70', 'is_manual_active' => true, 'effective_rate' => '70']);

        expect(app(ExchangeRateRegistry::class)->resolve('EGP')->isStale)->toBeFalse();
    });

    it('expires by itself and restores the provider rate with history', function (): void {
        givenRate('EGP', '50', ['manual_rate' => '70', 'is_manual_active' => true, 'effective_rate' => '70', 'manual_expires_at' => now()->addMinute()]);

        expect(effectiveRate())->toBe('70.000000000000');

        $this->travel(2)->minutes();

        expect(effectiveRate())->toBe('50.000000000000')
            ->and(ExchangeRate::query()->sole()->is_manual_active)->toBeFalse()
            ->and(ExchangeRateHistory::query()->sole()->is_manual)->toBeFalse();
        $this->assertDatabaseHas('audit_logs', ['action' => 'currency.override_expired']);
    });

    it('can be cleared with a reason', function (): void {
        givenRate('EGP', '50', ['manual_rate' => '70', 'is_manual_active' => true, 'effective_rate' => '70']);
        actingAsStaffWith(['currency.manage']);

        $this->deleteJson('/admin/api/exchange-rates/EGP/override', ['reason' => 'Done'])->assertOk();

        expect(effectiveRate())->toBe('50.000000000000');
        $this->deleteJson('/admin/api/exchange-rates/EGP/override', ['reason' => 'Done'])->assertStatus(409);
    });

    it('removes a rate that only ever had a manual source when cleared', function (): void {
        actingAsStaffWith(['currency.manage']);
        $this->postJson('/admin/api/exchange-rates/EGP/override', ['rate' => '70', 'reason' => 'x'])->assertOk();

        $this->deleteJson('/admin/api/exchange-rates/EGP/override', ['reason' => 'Done'])->assertOk();

        expect(fn () => app(ExchangeRateRegistry::class)->resolve('EGP'))->toThrow(ExchangeRateMissing::class);
    });

    it('refuses an override on the base currency', function (): void {
        actingAsStaffWith(['currency.manage']);

        $this->postJson('/admin/api/exchange-rates/USD/override', ['rate' => '2', 'reason' => 'x'])->assertUnprocessable();
    });
});

describe('staleness and missing rates', function (): void {
    it('marks a rate stale past the maximum age and blocks checkout in it', function (): void {
        givenRate('EGP', '50', ['fetched_at' => now()->subMinutes(1441)]);
        $converter = app(CurrencyConverter::class);

        expect(app(ExchangeRateRegistry::class)->resolve('EGP')->isStale)->toBeTrue()
            ->and(fn () => $converter->toCheckout(Money::of('10', 'USD'), 'EGP'))->toThrow(ExchangeRateStale::class);
    });

    it('accepts a rate just inside the maximum age', function (): void {
        givenRate('EGP', '50', ['fetched_at' => now()->subMinutes(1439)]);

        expect(app(CurrencyConverter::class)->toCheckout(Money::of('10', 'USD'), 'EGP')->money->amount)->toBe('500.000000');
    });

    it('notices staleness even when the rate table is cached', function (): void {
        givenRate('EGP', '50');
        $registry = app(ExchangeRateRegistry::class);
        expect($registry->resolve('EGP')->isStale)->toBeFalse();

        $this->travel(25)->hours();

        expect($registry->resolve('EGP')->isStale)->toBeTrue();
    });

    it('still converts for display on a stale rate, flagged', function (): void {
        givenRate('EGP', '50', ['fetched_at' => now()->subDays(5)]);

        $converted = app(CurrencyConverter::class)->toDisplay(Money::of('10', 'USD'), 'EGP');

        expect($converted->money->amount)->toBe('500.000000')->and($converted->rate->isStale)->toBeTrue();
    });

    it('throws for a missing rate', function (): void {
        expect(fn () => app(ExchangeRateRegistry::class)->resolve('EGP'))->toThrow(ExchangeRateMissing::class);
    });

    it('falls back to base at checkout for stale, missing and inactive currencies', function (): void {
        Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
        $converter = app(CurrencyConverter::class);

        expect($converter->resolveCheckoutCurrency('EGP')->reason)->toBe('rate_missing');

        givenRate('EGP', '50', ['fetched_at' => now()->subDays(2)]);
        $decision = $converter->resolveCheckoutCurrency('EGP');
        expect($decision->currency)->toBe('USD')->and($decision->fellBackToBase)->toBeTrue()->and($decision->reason)->toBe('rate_stale');

        Currency::query()->where('code', 'EGP')->update(['is_active' => false]);
        Cache::flush();
        expect($converter->resolveCheckoutCurrency('EGP')->reason)->toBe('currency_inactive');
    });

    it('uses a fresh active currency at checkout', function (): void {
        givenRate('EGP', '50');

        expect(app(CurrencyConverter::class)->resolveCheckoutCurrency('EGP')->fellBackToBase)->toBeFalse();
    });

    it('never treats the base currency as stale or missing', function (): void {
        expect(app(CurrencyConverter::class)->toCheckout(Money::of('10', 'USD'), 'USD')->money->amount)->toBe('10.000000');
    });
});

describe('conversion rounding', function (): void {
    function currencyWith(string $code, int $exponent, ?array $rule = null): void
    {
        Currency::factory()->create(['code' => $code, 'exponent' => $exponent, 'rounding_rule' => $rule, 'is_active' => true]);
    }

    function convertedTen(string $code, string $rate, string $base = '10'): string
    {
        givenRate($code, $rate);

        return app(CurrencyConverter::class)->toDisplay(Money::of($base, 'USD'), $code)->money->amount;
    }

    it('rounds to the currency precision, half up', function (string $code, int $exponent, string $rate, string $expected): void {
        currencyWith($code, $exponent);

        expect(convertedTen($code, $rate))->toBe($expected);
    })->with([
        'two decimals up' => ['AAA', 2, '1.2345', '12.350000'],
        'two decimals down' => ['BBB', 2, '1.2344', '12.340000'],
        'no decimals' => ['JPY', 0, '149.55', '1496.000000'],
        'three decimals' => ['KWD', 3, '0.30745', '3.075000'],
    ]);

    it('applies a step rule', function (): void {
        currencyWith('CHF', 2, ['mode' => 'step', 'step' => '0.05']);

        expect(convertedTen('CHF', '1.1234'))->toBe('11.250000');
    });

    it('applies an ends-with rule, never below the converted amount', function (string $code, string $rate, string $expected): void {
        currencyWith($code, 2, ['mode' => 'ends_with', 'value' => '0.99']);

        expect(convertedTen($code, $rate))->toBe($expected);
    })->with([
        'rounds up within the unit' => ['AUD', '1.25', '12.990000'],
        'moves to the next unit when already past' => ['CAD', '1.2995', '13.990000'],
        'stays when exact' => ['NZD', '1.299', '12.990000'],
    ]);

    it('rejects a rounding rule finer than the precision', function (): void {
        currencyWith('JPY', 0);
        actingAsStaffWith(['currency.manage']);

        $this->patchJson('/admin/api/currencies/JPY', ['rounding_rule' => ['mode' => 'step', 'step' => '0.05']])->assertUnprocessable();
        $this->patchJson('/admin/api/currencies/JPY', ['rounding_rule' => ['mode' => 'step', 'step' => '10']])->assertOk();
    });

    it('computes display and payment identically when the currency matches', function (): void {
        currencyWith('SAR', 2, ['mode' => 'step', 'step' => '0.05']);
        givenRate('SAR', '3.7523');
        $converter = app(CurrencyConverter::class);
        $base = Money::of('17.33', 'USD');

        expect($converter->toDisplay($base, 'SAR')->money->equals($converter->toCheckout($base, 'SAR')->money))->toBeTrue();
    });

    it('computes display and payment independently from the same base when they differ', function (): void {
        givenRate('EGP', '50');
        CurrencyFactory::ensure('EUR');
        givenRate('EUR', '0.9');
        $converter = app(CurrencyConverter::class);
        $base = Money::of('10', 'USD');

        expect($converter->toDisplay($base, 'EGP')->money->amount)->toBe('500.000000')
            ->and($converter->toCheckout($base, 'EUR')->money->amount)->toBe('9.000000')
            ->and($base->amount)->toBe('10.000000');
    });
});

describe('admin currencies', function (): void {
    it('refuses to activate a currency with no rate', function (): void {
        Currency::query()->where('code', 'EGP')->update(['is_active' => false]);
        actingAsStaffWith(['currency.manage']);

        $this->patchJson('/admin/api/currencies/EGP', ['is_active' => true])->assertUnprocessable()->assertJsonPath('code', 'rate_missing');
    });

    it('refuses to activate a currency with a stale rate, even with a loosely typed flag', function (): void {
        Currency::query()->where('code', 'EGP')->update(['is_active' => false]);
        givenRate('EGP', '50', ['fetched_at' => now()->subDays(3)]);
        actingAsStaffWith(['currency.manage']);

        $this->patchJson('/admin/api/currencies/EGP', ['is_active' => 1])->assertUnprocessable()->assertJsonPath('code', 'rate_stale');
    });

    it('activates a currency once a real rate exists', function (): void {
        Currency::query()->where('code', 'EGP')->update(['is_active' => false]);
        givenRate('EGP', '50');
        actingAsStaffWith(['currency.manage']);

        $this->patchJson('/admin/api/currencies/EGP', ['is_active' => true])->assertOk()->assertJsonPath('data.is_active', true);
    });

    it('refuses to deactivate the base currency', function (): void {
        actingAsStaffWith(['currency.manage']);

        $this->patchJson('/admin/api/currencies/USD', ['is_active' => false])->assertUnprocessable();
    });

    it('recomputes the effective rate when markup changes, with history', function (): void {
        givenRate('EGP', '50');
        actingAsStaffWith(['currency.manage']);

        $this->patchJson('/admin/api/currencies/EGP', ['markup_pct' => '4'])->assertOk();

        expect(effectiveRate())->toBe('52.000000000000')
            ->and(ExchangeRateHistory::query()->where('quote_currency', 'EGP')->sole()->effective_rate)->toBe('52.000000000000');
    });

    it('lists history and rates for pricing.view', function (): void {
        givenRate('EGP', '50');
        ExchangeRateHistory::factory()->create(['quote_currency' => 'EGP', 'base_currency' => 'USD', 'effective_rate' => '50']);
        actingAsStaffWith(['pricing.view']);

        $this->getJson('/admin/api/exchange-rates')->assertOk()->assertJsonPath('data.0.quote_currency', 'EGP');
        $this->getJson('/admin/api/exchange-rates/EGP/history')->assertOk()->assertJsonPath('meta.total', 1);
    });

    it('fetches on demand and reports a provider failure in the envelope', function (): void {
        actingAsStaffWith(['currency.manage']);
        providerReturning(['EGP' => '50']);

        $this->postJson('/admin/api/exchange-rates/fetch-now')->assertOk()->assertJsonPath('data.updated', 1);

        config(['services.open_exchange_rates.app_id' => null]);
        app()->offsetUnset(ExchangeRateProvider::class);
        $this->app->bind(ExchangeRateProvider::class, OpenExchangeRatesProvider::class);

        $this->postJson('/admin/api/exchange-rates/fetch-now')->assertStatus(503)->assertJsonPath('code', 'rates_provider_unavailable');
    });
});

describe('storefront currency selection', function (): void {
    it('sets a usable currency and falls back for an unusable one', function (): void {
        givenRate('EGP', '50');

        $this->postJson('/api/v1/storefront/currency', ['currency' => 'EGP'])->assertOk()->assertJsonPath('data.currency', 'EGP')->assertCookie('currency', 'EGP', false);
        $this->postJson('/api/v1/storefront/currency', ['currency' => 'ZZZ'])->assertOk()->assertJsonPath('data.currency', 'USD');
    });

    it('lists only active currencies', function (): void {
        Currency::query()->where('code', 'EGP')->update(['is_active' => false]);

        $this->getJson('/api/v1/storefront/currencies')->assertOk()->assertJsonPath('data.currencies.0.code', 'USD')->assertJsonCount(1, 'data.currencies');
    });
});

it('has no exchange rate literals in application code', function (): void {
    $forbidden = [
        // A three-letter currency code paired with a number: ['EGP' => '48.5'] or 'USD/EGP' => 48.5
        '/[\'"](?:[A-Z]{3}\/)?[A-Z]{3}[\'"]\s*(?:=>|:)\s*[\'"]?\d+(?:\.\d+)?[\'"]?/',
        // A rate-named key or variable assigned a number other than the identity rate.
        '/(?:[a-z_]*rate)[\'"]?\s*(?:=>|=)\s*[\'"]?(?!1[\'"]?\s*[,;\])])\d/i',
        // Rate-like decimals passed to a Rate constructor.
        '/Rate::of\(\s*[\'"](?!1[\'"])\d/',
    ];

    $root = base_path();
    $files = [
        ...array_filter(iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/app", FilesystemIterator::SKIP_DOTS))), static fn (SplFileInfo $file): bool => $file->getExtension() === 'php'),
        ...array_filter(iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/database/seeders", FilesystemIterator::SKIP_DOTS))), static fn (SplFileInfo $file): bool => $file->getExtension() === 'php'),
        ...array_filter(iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/config", FilesystemIterator::SKIP_DOTS))), static fn (SplFileInfo $file): bool => $file->getExtension() === 'php'),
    ];

    expect(count($files))->toBeGreaterThan(100);

    foreach ($files as $file) {
        $source = (string) file_get_contents($file->getPathname());
        // Currency reference data maps codes to names and exponents, not rates.
        if (str_ends_with($file->getPathname(), 'seeders/data/currencies.php')) {
            continue;
        }

        foreach ($forbidden as $pattern) {
            expect(preg_match($pattern, $source))->toBe(0, "Rate literal in {$file->getPathname()} matching {$pattern}");
        }
    }

    // The patterns must actually catch the mistakes they are for.
    expect(preg_match($forbidden[0], "['EGP' => '48.5']"))->toBe(1)
        ->and(preg_match($forbidden[1], "'effective_rate' => '48.5'"))->toBe(1)
        ->and(preg_match($forbidden[2], "Rate::of('48.5')"))->toBe(1);
});

it('seeds no exchange rates', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(ExchangeRate::query()->count())->toBe(0)
        ->and(Currency::query()->find('USD')->is_active)->toBeTrue()
        ->and(Currency::query()->find('GBP')->is_active)->toBeFalse();
});
