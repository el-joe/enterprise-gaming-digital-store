<?php

namespace App\Domains\Orders\Services;

use App\Domains\Catalog\Services\DeliveryFieldValidator;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Exceptions\ExchangeRateStale;
use App\Domains\Orders\DTOs\CartQuote;
use App\Domains\Orders\Exceptions\CartEmpty;
use App\Domains\Orders\Exceptions\CartItemUnavailable;
use App\Domains\Orders\Exceptions\OrderTotalInvalid;
use App\Domains\Orders\Models\Cart;
use App\Domains\Promotions\Exceptions\CouponInvalid;
use Illuminate\Validation\ValidationException;

/**
 * Refuses a checkout quote that cannot become an order. A stale or missing rate in the display
 * currency refuses the whole checkout in that currency (the customer may switch to the base
 * currency); a coupon that no longer validates is refused rather than silently dropped.
 */
class CheckoutGuard
{
    private const array MESSAGES = [
        'unavailable' => 'This product is no longer available.',
        'out_of_stock' => 'This product is out of stock.',
        'custom_amount_unsupported' => 'Custom amounts cannot be bought yet.',
        'quantity_limit' => 'You have more of this product than one order allows.',
        'not_sold_in_country' => 'This product is not sold in your country.',
    ];

    public function __construct(private DeliveryFieldValidator $deliveryFields) {}

    /**
     * @throws CartEmpty|ExchangeRateStale|ExchangeRateMissing|CartItemUnavailable|CouponInvalid|ValidationException|OrderTotalInvalid
     */
    public function assertReady(Cart $cart, CartQuote $quote): void
    {
        if ($quote->lines === []) {
            throw new CartEmpty;
        }

        $errors = [];

        foreach ($quote->lines as $line) {
            if ($line->blockReason === 'rate_stale') {
                throw new ExchangeRateStale($quote->displayCurrency);
            }

            if ($line->blockReason === 'rate_missing') {
                throw new ExchangeRateMissing($quote->displayCurrency);
            }

            if ($line->blockReason !== null) {
                $errors["items.{$line->item->id}"] = [self::MESSAGES[$line->blockReason] ?? 'This product cannot be bought right now.'];
            }
        }

        if ($errors !== []) {
            throw new CartItemUnavailable($errors);
        }

        $this->assertDeliveryValues($cart);

        if ($cart->coupon_id !== null && $quote->coupon === null) {
            throw new CouponInvalid($quote->couponError ?? 'This coupon cannot be used.');
        }

        if (! $quote->totalDisplay->isPositive()) {
            throw new OrderTotalInvalid;
        }
    }

    /**
     * Fields may have changed since the item was added, so values are validated again.
     */
    private function assertDeliveryValues(Cart $cart): void
    {
        $errors = [];

        foreach ($cart->cartItems as $item) {
            try {
                $this->deliveryFields->validate($item->productVariant->product, $item->delivery_values ?? []);
            } catch (ValidationException $exception) {
                foreach ($exception->errors() as $key => $messages) {
                    $errors["items.{$item->id}.{$key}"] = $messages;
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
