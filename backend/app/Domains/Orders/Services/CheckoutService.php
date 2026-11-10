<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\DTOs\CartOwner;
use App\Domains\Orders\DTOs\CartQuote;
use App\Domains\Orders\Exceptions\CartEmpty;
use App\Domains\Orders\Models\Cart;

/**
 * Checkout step 1: reprice with checkout rules (a stale rate blocks) and revalidate everything.
 */
class CheckoutService
{
    public function __construct(
        private CartService $carts,
        private CartPricingService $pricing,
        private CheckoutGuard $guard,
    ) {}

    /**
     * @return array{cart: Cart, quote: CartQuote}
     */
    public function validate(CartOwner $owner, string $displayCurrency, string $country, ?string $email): array
    {
        $cart = $this->carts->current($owner, $displayCurrency) ?? throw new CartEmpty;

        if ($cart->country_code !== strtoupper($country)) {
            $cart->forceFill(['country_code' => strtoupper($country)])->save();
        }

        $quote = $this->pricing->price($cart, $displayCurrency, $country, forCheckout: true, email: $email ?? $owner->user?->email);

        $this->guard->assertReady($cart, $quote);

        return ['cart' => $cart, 'quote' => $quote];
    }
}
