<?php

namespace App\Domains\Orders\Services;

use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Orders\DTOs\CartLineQuote;
use App\Domains\Orders\DTOs\CartQuote;
use App\Domains\Orders\DTOs\UnitPrice;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use App\Domains\Pricing\DTOs\DiscountSlot;
use App\Domains\Pricing\DTOs\PriceQuote;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\Services\CartDiscountAllocator;
use App\Domains\Pricing\Services\DiscountCalculator;
use App\Domains\Pricing\Services\MarginGuard;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\DTOs\CouponLookup;
use App\Domains\Promotions\DTOs\ResolvedCoupon;
use App\Domains\Promotions\Exceptions\CouponInvalid;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Prices a whole cart (ADR-025). Each variant is quoted once by the pricing engine without a
 * coupon; the coupon is then computed once on the cart, allocated over units within each unit's
 * margin room in whole minor units of the base currency, and every unit is converted to the
 * display currency after its discount.
 */
class CartPricingService
{
    public function __construct(
        private PricingEngine $engine,
        private CartLineValidator $validator,
        private CheckoutCoupons $coupons,
        private DiscountCalculator $discounts,
        private MarginGuard $guard,
        private CartDiscountAllocator $allocator,
        private ExchangeRateRegistry $registry,
    ) {}

    /**
     * The cart must be loaded with CartService::RELATIONS.
     */
    public function price(Cart $cart, string $currency, ?string $country, bool $forCheckout, ?string $email = null): CartQuote
    {
        $base = $this->registry->baseCurrency();
        $items = $cart->cartItems->sortBy('id')->values();
        $reasons = $this->validator->reasons($cart, $country);

        $pricedVariants = new EloquentCollection($items
            ->reject(static fn (CartItem $item): bool => isset($reasons[$item->id]))
            ->map(static fn (CartItem $item) => $item->productVariant)
            ->unique('id')
            ->values()
            ->all());

        $quotes = $pricedVariants->isEmpty()
            ? collect()
            : $this->engine->quoteMany($pricedVariants, new PricingContext($country, $currency, null, $forCheckout));

        /** @var list<array{item: CartItem, quote: PriceQuote, reason: ?string}> $rows */
        $rows = $items->map(static function (CartItem $item) use ($reasons, $quotes): array {
            $quote = $quotes[$item->product_variant_id] ?? PriceQuote::blocked($reasons[$item->id] ?? 'unavailable');

            return ['item' => $item, 'quote' => $quote, 'reason' => $reasons[$item->id] ?? ($quote->purchasable ? null : $quote->blockReason)];
        })->all();

        $purchasable = array_values(array_filter($rows, static fn (array $row): bool => $row['reason'] === null));
        $subtotalBase = array_reduce($purchasable, static fn (Money $carry, array $row): Money => $carry->plus($row['quote']->baseAmount->multipliedBy($row['item']->quantity)), Money::zero($base));

        [$coupon, $couponError] = $this->resolveCoupon($cart, $purchasable, $subtotalBase, $email);
        [$allocations, $limitedByMargin] = $this->allocate($purchasable, $coupon, $base);

        $lines = [];
        $slot = 0;
        $discountBase = Money::zero($base);
        $subtotalDisplay = Money::zero($currency);
        $totalDisplay = Money::zero($currency);

        foreach ($rows as $row) {
            if ($row['reason'] !== null) {
                $lines[] = new CartLineQuote($row['item'], $row['quote'], $row['reason']);

                continue;
            }

            $units = [];

            for ($index = 0; $index < $row['item']->quantity; $index++) {
                $gross = $row['quote']->baseAmount;
                $discount = $allocations[$slot++] ?? Money::zero($base);
                $net = $gross->minus($discount);

                $units[] = new UnitPrice($gross, $discount, $net, $this->engine->displayAmountFor($row['quote'], $net));
            }

            $line = new CartLineQuote($row['item'], $row['quote'], null, $units);
            $lines[] = $line;
            $discountBase = $discountBase->plus($line->discountBase($base));
            $subtotalDisplay = $subtotalDisplay->plus($line->subtotalDisplay($currency));
            $totalDisplay = $totalDisplay->plus($line->totalDisplay($currency));
        }

        return new CartQuote(
            cart: $cart,
            lines: $lines,
            baseCurrency: $base,
            displayCurrency: $currency,
            displayRate: $purchasable === [] ? null : $purchasable[0]['quote']->rate,
            subtotalBase: $subtotalBase,
            discountBase: $discountBase,
            totalBase: $subtotalBase->minus($discountBase),
            subtotalDisplay: $subtotalDisplay,
            totalDisplay: $totalDisplay,
            coupon: $coupon,
            couponError: $couponError,
            couponLimitedByMargin: $limitedByMargin,
        );
    }

    /**
     * @param  list<array{item: CartItem, quote: PriceQuote, reason: ?string}>  $purchasable
     * @return array{0: ?ResolvedCoupon, 1: ?string}
     */
    private function resolveCoupon(Cart $cart, array $purchasable, Money $subtotalBase, ?string $email): array
    {
        if ($cart->coupon_id === null) {
            return [null, null];
        }

        if ($cart->coupon === null || $purchasable === []) {
            return [null, 'This coupon cannot be used.'];
        }

        try {
            $items = array_map(static fn (array $row): CartItem => $row['item'], $purchasable);

            return [$this->coupons->resolve(self::couponLookup($cart, $cart->coupon->code, $items, $subtotalBase, $email)), null];
        } catch (CouponInvalid $exception) {
            return [null, $exception->getMessage()];
        }
    }

    /**
     * What the coupon contract validates against: the purchasable items before any coupon.
     *
     * @param  list<CartItem>  $items
     */
    public static function couponLookup(Cart $cart, string $code, array $items, Money $subtotalBase, ?string $email): CouponLookup
    {
        return new CouponLookup(
            code: $code,
            userId: $cart->user_id,
            email: $email,
            subtotalBase: $subtotalBase,
            productVariantIds: array_values(array_unique(array_map(static fn (CartItem $item): int => $item->product_variant_id, $items))),
            productIds: array_values(array_unique(array_map(static fn (CartItem $item): int => $item->productVariant->product_id, $items))),
        );
    }

    /**
     * One slot per unit in cart order. Ineligible units have no weight and no room.
     *
     * @param  list<array{item: CartItem, quote: PriceQuote, reason: ?string}>  $purchasable
     * @return array{0: list<Money>, 1: bool}
     */
    private function allocate(array $purchasable, ?ResolvedCoupon $coupon, string $base): array
    {
        if ($coupon === null) {
            return [[], false];
        }

        $slots = [];
        $eligibleSubtotal = Money::zero($base);

        foreach ($purchasable as $row) {
            $quote = $row['quote'];
            $eligible = $coupon->appliesTo($row['item']->product_variant_id);
            $room = $eligible ? $this->guard->discountRoom($quote->baseAmount, $quote->floor, $coupon->terms->allowBelowMargin) : Money::zero($base);

            for ($index = 0; $index < $row['item']->quantity; $index++) {
                $slots[] = new DiscountSlot($eligible ? $quote->baseAmount : Money::zero($base), $room);

                if ($eligible) {
                    $eligibleSubtotal = $eligibleSubtotal->plus($quote->baseAmount);
                }
            }
        }

        if (! $eligibleSubtotal->isPositive()) {
            return [array_fill(0, count($slots), Money::zero($base)), false];
        }

        $exponent = $this->registry->currency($base)['exponent'] ?? 2;
        $intended = $this->discounts->couponDiscount($eligibleSubtotal, $coupon->terms)->roundToCurrency($exponent);
        $allocations = $this->allocator->allocate($intended, $slots, $exponent);
        $placed = array_reduce($allocations, static fn (Money $carry, Money $amount): Money => $carry->plus($amount), Money::zero($base));

        return [$allocations, $placed->compareTo($intended) === -1];
    }
}
