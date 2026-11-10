<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Contracts\ExchangeRateProvider;
use App\Domains\Currency\DTOs\ProviderRates;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Services\ExchangeRateUpdater;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Pricing\Models\PricingOverrideLog;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Pricing\Services\VariantPriceRecalculator;
use App\Domains\Shared\ValueObjects\Rate;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Carbon\CarbonImmutable;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Artisan;

beforeEach(fn () => CurrencyFactory::ensure('USD'));

describe('explicit prices below the floor', function (): void {
    /**
     * @return array<string, mixed>
     */
    function explicitPayload(ProductVariant $variant, string $price = '5', array $overrides = []): array
    {
        return ['product_variant_id' => $variant->id, 'country_code' => '*', 'currency' => 'USD', 'price' => $price, ...$overrides];
    }

    it('accepts a price at or above the floor without authorization', function (): void {
        actingAsStaffWith(['pricing.manage']);

        $this->postJson('/admin/api/variant-prices', explicitPayload(variantCosting('5'), '5'))->assertCreated();

        expect(PricingOverrideLog::query()->count())->toBe(0);
    });

    it('refuses a below-floor price from staff without the override permission', function (): void {
        actingAsStaffWith(['pricing.manage']);

        $this->postJson('/admin/api/variant-prices', explicitPayload(variantCosting('10'), '5', ['override_reason' => 'please']))
            ->assertForbidden()
            ->assertJsonPath('code', 'margin_override_forbidden');

        expect(VariantPrice::query()->count())->toBe(0);
    });

    it('requires a reason even with the permission', function (): void {
        actingAsStaffWith(['pricing.manage', 'pricing.override_margin']);

        $this->postJson('/admin/api/variant-prices', explicitPayload(variantCosting('10')))
            ->assertUnprocessable()
            ->assertJsonPath('code', 'below_margin')
            ->assertJsonStructure(['errors' => ['override_reason']]);

        expect(VariantPrice::query()->count())->toBe(0);
    });

    it('accepts an authorized override and records who, why and the floor', function (): void {
        $staff = actingAsStaffWith(['pricing.manage', 'pricing.override_margin']);
        $variant = variantCosting('10');

        $this->postJson('/admin/api/variant-prices', explicitPayload($variant, '5', ['override_reason' => 'Clearance']))->assertCreated();

        $log = PricingOverrideLog::query()->sole();

        expect($log->actor_id)->toBe($staff->id)
            ->and($log->new_price_base)->toBe('5.000000')
            ->and($log->floor_price_base)->toBe('10.000000')
            ->and($log->reason)->toBe('Clearance')
            ->and(AuditLog::query()->where('action', 'pricing.margin_override')->sole()->reason)->toBe('Clearance');
    });

    it('refuses a price whose margin cannot be verified', function (): void {
        actingAsStaffWith(['pricing.manage']);

        $this->postJson('/admin/api/variant-prices', explicitPayload(ProductVariant::factory()->create(), '5'))
            ->assertForbidden();
    });

    it('checks a foreign currency price in base', function (): void {
        CurrencyFactory::ensure('EGP');
        givenRate('EGP', '50');
        actingAsStaffWith(['pricing.manage']);
        $variant = variantCosting('10');

        $this->postJson('/admin/api/variant-prices', explicitPayload($variant, '400', ['currency' => 'EGP']))->assertForbidden();
        $this->postJson('/admin/api/variant-prices', explicitPayload($variant, '500', ['currency' => 'EGP']))->assertCreated();
    });

    it('reports a missing rate rather than guessing', function (): void {
        CurrencyFactory::ensure('EGP');
        actingAsStaffWith(['pricing.manage']);

        $this->postJson('/admin/api/variant-prices', explicitPayload(variantCosting('10'), '500', ['currency' => 'EGP']))
            ->assertUnprocessable()
            ->assertJsonPath('code', 'rate_missing');
    });

    it('guards updates too', function (): void {
        actingAsStaffWith(['pricing.manage']);
        $price = VariantPrice::factory()->create(['product_variant_id' => variantCosting('10')->id, 'country_code' => '*', 'currency' => 'USD', 'price' => '20']);

        $this->patchJson("/admin/api/variant-prices/{$price->id}", ['price' => '5'])->assertForbidden();
        $this->patchJson("/admin/api/variant-prices/{$price->id}", ['compare_at' => '25'])->assertOk();
    });

    it('rejects a duplicate price with no start date', function (): void {
        actingAsStaffWith(['pricing.manage']);
        $variant = variantCosting('1');

        $this->postJson('/admin/api/variant-prices', explicitPayload($variant, '5'))->assertCreated();
        $this->postJson('/admin/api/variant-prices', explicitPayload($variant, '6'))->assertUnprocessable()->assertJsonValidationErrors(['currency'], 'errors');
    });
});

describe('pricing rules', function (): void {
    /**
     * @return array<string, mixed>
     */
    function rulePayload(array $overrides = []): array
    {
        return ['name' => 'R', 'scope' => 'global', 'kind' => 'markup', 'percent_markup' => '10', ...$overrides];
    }

    it('refuses the allow policy without the override permission', function (): void {
        actingAsStaffWith(['pricing.manage']);

        $this->postJson('/admin/api/pricing-rules', rulePayload(['below_margin_policy' => 'allow', 'override_reason' => 'x']))->assertForbidden();

        expect(PricingRule::query()->count())->toBe(0);
    });

    it('needs a reason for the allow policy and audits it', function (): void {
        actingAsStaffWith(['pricing.manage', 'pricing.override_margin']);

        $this->postJson('/admin/api/pricing-rules', rulePayload(['below_margin_policy' => 'allow']))->assertUnprocessable()->assertJsonPath('code', 'below_margin');

        $this->postJson('/admin/api/pricing-rules', rulePayload(['below_margin_policy' => 'allow', 'override_reason' => 'Loss leader']))->assertCreated();

        expect(AuditLog::query()->where('action', 'pricing.margin_policy_allowed')->sole()->reason)->toBe('Loss leader');
    });

    it('needs authorization to change an existing rule to allow', function (): void {
        actingAsStaffWith(['pricing.manage']);
        $rule = PricingRule::factory()->create(['below_margin_policy' => PricingRuleBelowMarginPolicy::Block]);

        $this->patchJson("/admin/api/pricing-rules/{$rule->id}", ['below_margin_policy' => 'allow'])->assertForbidden();
    });

    it('accepts floor and block with no extra permission', function (string $policy): void {
        actingAsStaffWith(['pricing.manage']);

        $this->postJson('/admin/api/pricing-rules', rulePayload(['below_margin_policy' => $policy]))->assertCreated();
    })->with(['floor', 'block']);

    it('validates the scope target, percentages and dynamic conditions', function (array $overrides, string $field): void {
        actingAsStaffWith(['pricing.manage']);

        $this->postJson('/admin/api/pricing-rules', rulePayload($overrides))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'errors');
    })->with([
        'missing target' => [['scope' => 'product', 'scope_id' => 999999], 'scope_id'],
        'float percent' => [['percent_markup' => 12.5], 'percent_markup'],
        'dynamic without conditions' => [['kind' => 'dynamic'], 'conditions'],
        'unknown condition' => [['kind' => 'dynamic', 'conditions' => ['moon' => 'full']], 'conditions'],
        'margin on platform' => [['scope' => 'platform', 'min_margin_pct' => '5'], 'min_margin_pct'],
    ]);

    it('needs pricing.manage to write and pricing.view to read', function (): void {
        actingAsStaffWith(['pricing.view']);

        $this->getJson('/admin/api/pricing-rules')->assertOk();
        $this->postJson('/admin/api/pricing-rules', rulePayload())->assertForbidden();
    });

    it('marks only the variants in the rule scope dirty', function (): void {
        actingAsStaffWith(['pricing.manage']);
        $inScope = variantCosting('1');
        $outOfScope = variantCosting('1');

        $this->postJson('/admin/api/pricing-rules', rulePayload(['scope' => 'product', 'scope_id' => $inScope->product_id]))->assertCreated();

        expect($inScope->fresh()->pricing_dirty_at)->not->toBeNull()
            ->and($outOfScope->fresh()->pricing_dirty_at)->toBeNull();
    });

    it('marks every variant dirty for a global rule and on delete', function (): void {
        actingAsStaffWith(['pricing.manage']);
        $variant = variantCosting('1');
        $rule = PricingRule::factory()->create();

        $this->deleteJson("/admin/api/pricing-rules/{$rule->id}")->assertOk();

        expect($variant->fresh()->pricing_dirty_at)->not->toBeNull();
    });
});

describe('recalculation', function (): void {
    function dirtyVariant(string $cost = '10', array $attributes = []): ProductVariant
    {
        $variant = variantCosting($cost, $attributes);
        $variant->forceFill(['pricing_dirty_at' => now()->subMinute()])->save();

        return $variant;
    }

    it('stores the listing price and clears the mark', function (): void {
        PricingRule::factory()->create(['percent_markup' => '50', 'is_active' => true]);
        $variant = dirtyVariant();

        expect(app(VariantPriceRecalculator::class)->run()['recalculated'])->toBe(1);

        $fresh = $variant->fresh();

        expect($fresh->price_base)->toBe('15.000000')->and($fresh->pricing_dirty_at)->toBeNull();
    });

    it('is safe to run twice', function (): void {
        $variant = dirtyVariant();
        $recalculator = app(VariantPriceRecalculator::class);

        $recalculator->run();
        $updatedAt = $variant->fresh()->updated_at;

        $this->travel(5)->minutes();

        expect($recalculator->run())->toBe(['recalculated' => 0, 'skipped' => 0])
            ->and($variant->fresh()->updated_at->equalTo($updatedAt))->toBeTrue();
    });

    it('clears the listing price when a variant cannot be sold', function (): void {
        PricingRule::factory()->create(['min_margin_pct' => '20', 'below_margin_policy' => PricingRuleBelowMarginPolicy::Block, 'is_active' => true]);
        $variant = dirtyVariant('10');
        $variant->forceFill(['price_base' => '12'])->save();

        app(VariantPriceRecalculator::class)->run();

        expect($variant->fresh()->price_base)->toBeNull();
    });

    it('runs from the scheduled command through the queue', function (): void {
        $variant = dirtyVariant();

        Artisan::call('pricing:recalculate-dirty');

        expect($variant->fresh()->price_base)->toBe('10.000000');
    });

    it('leaves a variant dirty if it was marked again after being read', function (): void {
        $variant = dirtyVariant();
        $marker = app(PricingDirtyMarker::class);

        // The re-mark happens between reading the variant and taking its lock.
        $recalculator = app(VariantPriceRecalculator::class);
        DB::beforeExecuting(function (string $sql) use ($marker, $variant): void {
            static $done = false;

            if (! $done && str_contains(strtolower($sql), 'skip locked')) {
                $done = true;
                $marker->forVariants([$variant->id]);
            }
        });

        expect($recalculator->run())->toBe(['recalculated' => 0, 'skipped' => 1])
            ->and($variant->fresh()->pricing_dirty_at)->not->toBeNull();
    });

    it('moves the mark forward even twice in one second', function (): void {
        $this->freezeTime();
        $variant = variantCosting('1');
        $marker = app(PricingDirtyMarker::class);

        $marker->forVariants([$variant->id]);
        $first = $variant->fresh()->pricing_dirty_at;
        $marker->forVariants([$variant->id]);

        expect($variant->fresh()->pricing_dirty_at->greaterThan($first))->toBeTrue();
    });

    it('marks rules, prices and promotions that started or ended since the last sweep', function (): void {
        $target = variantCosting('1');
        $bystander = variantCosting('1');
        VariantPrice::factory()->create(['product_variant_id' => $target->id, 'country_code' => '*', 'currency' => 'USD', 'price' => '9', 'ends_at' => now()->subMinutes(2)]);

        app(VariantPriceRecalculator::class)->run();

        expect($target->fresh()->price_base)->not->toBeNull()
            ->and($bystander->fresh()->price_base)->toBeNull();
    });

    it('marks variants whose supplier cost currency rate changed', function (): void {
        CurrencyFactory::ensure('EGP');
        $variant = variantCosting('1');
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'supplier_unit_currency' => 'EGP', 'is_active' => true]);
        $other = variantCosting('1');

        app(PricingDirtyMarker::class)->forCostCurrencies(['EGP']);

        expect($variant->fresh()->pricing_dirty_at)->not->toBeNull()->and($other->fresh()->pricing_dirty_at)->toBeNull();
    });

    describe('with another connection holding a row lock', function (): void {
        beforeEach(function (): void {
            commitTestTransaction();
            config(['database.connections.mysql_second' => config('database.connections.mysql')]);
        });

        afterEach(function (): void {
            DB::purge('mysql_second');

            foreach (['product_variants', 'products', 'gaming_platforms', 'categories', 'currencies'] as $table) {
                DB::table($table)->delete();
            }

            reopenTestTransaction();
        });

        it('skips the locked row and picks it up on the next run', function (): void {
            CurrencyFactory::ensure('USD');
            $variant = dirtyVariant();
            $recalculator = app(VariantPriceRecalculator::class);

            $second = DB::connection('mysql_second');
            $second->beginTransaction();
            $second->select('select id from product_variants where id = ? for update', [$variant->id]);

            expect($recalculator->run())->toBe(['recalculated' => 0, 'skipped' => 1])
                ->and($variant->fresh()->pricing_dirty_at)->not->toBeNull();

            $second->rollBack();

            expect($recalculator->run())->toBe(['recalculated' => 1, 'skipped' => 0]);
        });
    });
});

it('never touches historical order prices when rates and prices change', function (): void {
    CurrencyFactory::ensure('EGP');
    givenRate('EGP', '50', ['fetched_at' => now()->subHour()]);
    $variant = variantCosting('10');
    $variant->forceFill(['pricing_dirty_at' => now()->subMinute()])->save();
    $order = Order::factory()->create(['display_currency' => 'EGP', 'display_rate' => '50', 'display_total' => '500', 'total_base' => '10', 'payment_currency' => 'EGP', 'payment_rate' => '50', 'payment_total' => '500']);
    $item = OrderItem::factory()->create(['order_id' => $order->id, 'product_variant_id' => $variant->id]);
    $before = [DB::table('orders')->where('id', $order->id)->first(), DB::table('order_items')->where('id', $item->id)->first()];

    app()->bind(ExchangeRateProvider::class, fn () => new class implements ExchangeRateProvider
    {
        public function name(): string
        {
            return 'fake';
        }

        public function latest(string $base, array $symbols): ProviderRates
        {
            return new ProviderRates($base, 'fake', CarbonImmutable::now(), ['EGP' => Rate::of('53')]);
        }
    });

    app(ExchangeRateUpdater::class)->refresh();
    PricingRule::factory()->create(['percent_markup' => '50', 'is_active' => true]);
    app(VariantPriceRecalculator::class)->run();

    expect($variant->fresh()->price_base)->toBe('15.000000')
        ->and(ExchangeRate::query()->where('quote_currency', 'EGP')->sole()->effective_rate)->toBe('53.000000000000')
        ->and(DB::table('orders')->where('id', $order->id)->first())->toEqual($before[0])
        ->and(DB::table('order_items')->where('id', $item->id)->first())->toEqual($before[1]);
});
