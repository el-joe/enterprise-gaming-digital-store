<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Contracts\SupplierAdapter;
use App\Domains\Suppliers\DTOs\AvailabilityQuery;
use App\Domains\Suppliers\DTOs\SelectionResult;
use App\Domains\Suppliers\DTOs\SupplierCandidate;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Enums\SupplierCapability;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Exceptions\SupplierNotConfigured;
use App\Domains\Suppliers\Exceptions\SupplierRequestFailed;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierProductPrice;
use Illuminate\Support\Collection;

/**
 * Ranks the mappings that may supply one unit (architecture 7.3). Every filter must pass; the
 * survivors are sorted by `settings.suppliers.selection_strategy`. Excluded mappings are kept
 * with their reason for the unit's selection snapshot. Reads only the database: no supplier call
 * is made here, except an availability check an adapter declares `full` (internal stock).
 */
class SupplierSelector
{
    public function __construct(
        private SupplierAdapterRegistry $registry,
        private ExchangeRateRegistry $rates,
        private CurrencyConverter $converter,
        private PricingEngine $engine,
        private SettingsService $settings,
    ) {}

    /**
     * @param  list<int>  $excludedSupplierIds  suppliers that already refused this unit
     */
    public function rank(OrderItemUnit $unit, Order $order, array $excludedSupplierIds = []): SelectionResult
    {
        $item = OrderItem::query()->with('productVariant')->findOrFail($unit->order_item_id);
        $variant = $item->productVariant;
        $strategy = (string) $this->settings->get('suppliers', 'selection_strategy', 'priority');

        $mappings = SupplierProductMapping::query()
            ->with(['supplier', 'supplierProduct'])
            ->where('product_variant_id', $variant->id)
            ->orderBy('id')
            ->get();

        $prices = $this->latestPrices($mappings);
        $netBase = $this->unitNetBase($unit, $item);
        $candidates = [];
        $excluded = [];

        foreach ($mappings as $mapping) {
            [$candidate, $reason] = $this->evaluate($mapping, $item, $variant, $order, $netBase, $prices, $excludedSupplierIds);

            if ($candidate !== null) {
                $candidates[] = $candidate;
            } else {
                $excluded[] = ['mapping_id' => $mapping->id, 'supplier_id' => $mapping->supplier_id, 'reason' => $reason];
            }
        }

        usort($candidates, static function (SupplierCandidate $a, SupplierCandidate $b) use ($strategy): int {
            $byCost = $a->baseCost->compareTo($b->baseCost);
            $byPriority = $a->priority <=> $b->priority;

            $order = $strategy === 'lowest_cost' ? [$byCost, $byPriority] : [$byPriority, $byCost];

            return $order[0] ?: ($order[1] ?: $a->mappingId <=> $b->mappingId);
        });

        return new SelectionResult($candidates, $excluded, $strategy);
    }

    /**
     * @param  array<string, SupplierProductPrice>  $prices
     * @param  list<int>  $excludedSupplierIds
     * @return array{0: ?SupplierCandidate, 1: string}
     */
    private function evaluate(SupplierProductMapping $mapping, OrderItem $item, ProductVariant $variant, Order $order, Money $netBase, array $prices, array $excludedSupplierIds): array
    {
        $supplier = $mapping->supplier;

        $reason = match (true) {
            ! $mapping->is_active => 'mapping_inactive',
            in_array($mapping->supplier_id, $excludedSupplierIds, true) => 'already_refused',
            $supplier === null || $supplier->status !== SupplierStatus::Active => 'supplier_inactive',
            $supplier->circuit_open_until !== null && $supplier->circuit_open_until->isFuture() => 'circuit_open',
            ! $this->registry->has($supplier->adapter_key) => 'no_adapter',
            default => null,
        };

        if ($reason !== null) {
            return [null, $reason];
        }

        $adapter = $this->registry->for($supplier);
        $capabilities = $adapter->capabilities();
        $country = strtoupper($order->country_code);

        $reason = match (true) {
            ! $capabilities->supports(SupplierCapability::CreateOrder) => 'cannot_order',
            ! $capabilities->supportsDeliveryType($item->delivery_type) => 'delivery_type_unsupported',
            is_array($mapping->allowed_countries) && $mapping->allowed_countries !== [] && ! in_array($country, array_map('strtoupper', $mapping->allowed_countries), true) => 'country_not_allowed',
            is_array($mapping->denied_countries) && in_array($country, array_map('strtoupper', $mapping->denied_countries), true) => 'country_denied',
            $mapping->availability === SupplierAvailability::Unavailable,
            $mapping->supplierProduct?->availability === SupplierAvailability::Unavailable,
            $mapping->supplierProduct?->removed_at !== null => 'unavailable',
            default => null,
        };

        if ($reason !== null) {
            return [null, $reason];
        }

        if ($capabilities->supports(SupplierCapability::Availability) && ! $this->inStock($adapter, $mapping)) {
            return [null, 'out_of_stock'];
        }

        [$cost, $reason] = $this->cost($mapping, $prices);

        if ($cost === null) {
            return [null, $reason];
        }

        [$baseCost, $reason] = $this->toBase($cost);

        if ($baseCost === null) {
            return [null, $reason];
        }

        $reason = $this->fundsAndCap($mapping, $capabilities->supports(SupplierCapability::Balance), $capabilities->supports(SupplierCapability::PerRequestAmountCap) ? $capabilities->defaultPerRequestCap : null, $cost, $baseCost)
            ?? $this->margin($variant, $baseCost, $cost->currency, $order, $netBase);

        if ($reason !== null) {
            return [null, $reason];
        }

        return [new SupplierCandidate(
            $mapping->id,
            $mapping->supplier_id,
            (string) $mapping->supplierProduct?->external_id,
            $mapping->supplier_unit_price,
            $mapping->supplier_unit_currency,
            $baseCost,
            $mapping->priority,
        ), 'selected'];
    }

    private function inStock(SupplierAdapter $adapter, SupplierProductMapping $mapping): bool
    {
        try {
            return $adapter->checkAvailability(new AvailabilityQuery((string) $mapping->supplierProduct?->external_id, $mapping->product_variant_id, $mapping->supplier_unit_price))->availability !== SupplierAvailability::Unavailable;
        } catch (SupplierRequestFailed|SupplierNotConfigured) {
            return false;
        }
    }

    /**
     * The mapping's override cost, or the latest synced effective cost for its denomination if it
     * is younger than `max_price_age_minutes`.
     *
     * @param  array<string, SupplierProductPrice>  $prices
     * @return array{0: ?Money, 1: string}
     */
    private function cost(SupplierProductMapping $mapping, array $prices): array
    {
        if ($mapping->override_cost !== null && $mapping->override_cost_currency !== null) {
            return [Money::of($mapping->override_cost, $mapping->override_cost_currency), 'override'];
        }

        $price = $prices[$mapping->supplier_product_id.'|'.$mapping->supplier_unit_price] ?? null;

        if ($price === null) {
            return [null, 'cost_unknown'];
        }

        if ($price->captured_at->lt(now()->subMinutes((int) config('suppliers.max_price_age_minutes')))) {
            return [null, 'cost_stale'];
        }

        return [Money::of($price->cost, $price->cost_currency), 'synced'];
    }

    /**
     * @return array{0: ?Money, 1: string}
     */
    private function toBase(Money $cost): array
    {
        if ($cost->currency === $this->rates->baseCurrency()) {
            return [$cost, 'base'];
        }

        try {
            $rate = $this->rates->resolve($cost->currency);
        } catch (ExchangeRateMissing) {
            return [null, 'cost_rate_missing'];
        }

        return $rate->isStale ? [null, 'cost_rate_stale'] : [$this->converter->toBase($cost, $rate, withoutMarkup: true), 'converted'];
    }

    private function fundsAndCap(SupplierProductMapping $mapping, bool $tracksBalance, ?Money $defaultCap, Money $cost, Money $baseCost): ?string
    {
        $supplier = $mapping->supplier;

        if ($tracksBalance) {
            if ($supplier->last_balance === null || $supplier->balance_currency === null) {
                return 'balance_unknown';
            }

            $balance = Money::of($supplier->last_balance, $supplier->balance_currency);

            if ($balance->currency === $cost->currency) {
                if ($balance->compareTo($cost) === -1) {
                    return 'insufficient_balance';
                }
            } else {
                [$balanceBase] = $this->toBase($balance);

                if ($balanceBase === null || $balanceBase->compareTo($baseCost) === -1) {
                    return 'insufficient_balance';
                }
            }
        }

        if ($supplier->per_request_cap_base === null && $defaultCap === null) {
            return null;
        }

        $cap = $supplier->per_request_cap_base !== null
            ? Money::of($supplier->per_request_cap_base, $this->rates->baseCurrency())
            : $this->toBase($defaultCap)[0];

        return $cap === null || $baseCost->compareTo($cap) === 1 ? 'over_request_cap' : null;
    }

    private function margin(ProductVariant $variant, Money $baseCost, string $costCurrency, Order $order, Money $netBase): ?string
    {
        $guard = $this->engine->floorAtCost($variant, $baseCost, $costCurrency, $order->country_code, $order->display_currency, $order->payment_currency);

        if ($guard['allows_below_margin']) {
            return null;
        }

        return $guard['floor'] === null || $netBase->compareTo($guard['floor']) === -1 ? SelectionResult::BELOW_MARGIN : null;
    }

    /**
     * What this unit actually sold for in base currency, after its share of any coupon.
     */
    private function unitNetBase(OrderItemUnit $unit, OrderItem $item): Money
    {
        $base = $this->rates->baseCurrency();

        foreach ($item->pricing_breakdown['units'] ?? [] as $row) {
            if (is_array($row) && ($row['unit_public_id'] ?? null) === $unit->public_id && is_string($row['net_base'] ?? null)) {
                return Money::of($row['net_base'], $base);
            }
        }

        $discount = $item->quantity > 0 ? Money::of($item->discount_base, $base)->dividedBy((string) $item->quantity) : Money::zero($base);

        return Money::of($item->unit_price_base, $base)->minus($discount);
    }

    /**
     * @param  Collection<int, SupplierProductMapping>  $mappings
     * @return array<string, SupplierProductPrice> keyed by "supplierProductId|denomination"
     */
    private function latestPrices(Collection $mappings): array
    {
        if ($mappings->isEmpty()) {
            return [];
        }

        $latest = SupplierProductPrice::query()
            ->selectRaw('MAX(id) AS id')
            ->whereIn('supplier_product_id', $mappings->pluck('supplier_product_id')->unique())
            ->groupBy('supplier_product_id', 'denomination');

        return SupplierProductPrice::query()
            ->whereIn('id', $latest->pluck('id'))
            ->get()
            ->keyBy(static fn (SupplierProductPrice $price): string => $price->supplier_product_id.'|'.$price->denomination)
            ->all();
    }
}
