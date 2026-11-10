<?php

namespace App\Domains\Orders\Services;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Services\DeliveryFieldValidator;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\CartOwner;
use App\Domains\Orders\DTOs\CartQuote;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Orders\Exceptions\CartEmpty;
use App\Domains\Orders\Exceptions\CartItemUnavailable;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cart reads and writes. Every write locks the cart row (and, for a signed-in customer, the user
 * row first, so concurrent first writes cannot create two carts).
 */
class CartService
{
    public const int MAX_LINES = 20;

    /** Relations the pricing service and line validator read. */
    public const array RELATIONS = [
        'coupon',
        'cartItems.productVariant.product.productCountryRestrictions',
        'cartItems.productVariant.product.productDeliveryFields.deliveryFieldDefinition',
    ];

    public function __construct(
        private CartPricingService $pricing,
        private DeliveryFieldValidator $deliveryFields,
        private CheckoutCoupons $coupons,
    ) {}

    /**
     * The owner's active cart with pricing relations loaded, or null.
     */
    public function current(CartOwner $owner, string $displayCurrency): ?Cart
    {
        $cart = $this->find($owner);

        if ($cart === null) {
            return null;
        }

        if ($cart->display_currency !== $displayCurrency) {
            $cart->forceFill(['display_currency' => $displayCurrency])->save();
        }

        return $cart->load(self::RELATIONS);
    }

    /**
     * Units in the owner's active cart, for the storefront header (M12). One query, no pricing.
     */
    public function unitCount(CartOwner $owner): int
    {
        return (int) $this->find($owner)?->cartItems()->sum('quantity');
    }

    public function quote(Cart $cart, ?string $country = null): CartQuote
    {
        return $this->pricing->price($cart, $cart->display_currency, $country ?? $cart->country_code, forCheckout: false);
    }

    /**
     * @param  array<string, mixed>  $deliveryValues
     *
     * @throws CartItemUnavailable
     * @throws ValidationException
     */
    public function addItem(CartOwner $owner, string $variantPublicId, int $quantity, array $deliveryValues, string $displayCurrency): Cart
    {
        $variant = ProductVariant::query()
            ->where('public_id', $variantPublicId)
            ->with('product.productDeliveryFields.deliveryFieldDefinition')
            ->first();

        if ($variant === null || $variant->product === null || ! $variant->is_active || $variant->product->status !== ProductStatus::Active) {
            throw new CartItemUnavailable(['variant_id' => ['This product is not available.']], 'This product is not available.');
        }

        if ($variant->is_custom_amount) {
            throw new CartItemUnavailable(['variant_id' => ['Custom amounts cannot be bought yet.']], 'Custom amounts cannot be bought yet.');
        }

        if ($variant->availability === ProductVariantAvailability::OutOfStock) {
            throw new CartItemUnavailable(['variant_id' => ['This product is out of stock.']], 'This product is out of stock.');
        }

        $accepted = $this->deliveryFields->validate($variant->product, $deliveryValues);

        return DB::transaction(function () use ($owner, $variant, $quantity, $accepted, $displayCurrency): Cart {
            $cart = $this->lockedOrNew($owner, $displayCurrency);
            $cart->load('cartItems.productVariant');

            $existing = $cart->cartItems->first(static fn (CartItem $item): bool => $item->product_variant_id === $variant->id
                && $item->custom_amount === null
                && self::sameValues($item->delivery_values ?? [], $accepted));

            if ($existing === null && $cart->cartItems->count() >= self::MAX_LINES) {
                throw new CartItemUnavailable(['variant_id' => ['Your cart is full.']], 'Your cart is full.');
            }

            $productQuantity = $cart->cartItems
                ->filter(static fn (CartItem $item): bool => $item->productVariant?->product_id === $variant->product_id)
                ->sum('quantity');

            if ($productQuantity + $quantity > $variant->product->max_quantity_per_order) {
                throw ValidationException::withMessages(['quantity' => ["You can buy at most {$variant->product->max_quantity_per_order} of this product per order."]]);
            }

            if ($existing !== null) {
                $existing->forceFill(['quantity' => $existing->quantity + $quantity])->save();
            } else {
                (new CartItem)->forceFill([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'delivery_values' => $accepted === [] ? null : $accepted,
                ])->save();
            }

            return $this->touched($cart);
        }, 3);
    }

    /**
     * @throws ModelNotFoundException when the item is not in the owner's cart
     * @throws ValidationException
     */
    public function updateQuantity(CartOwner $owner, int $cartItemId, int $quantity): Cart
    {
        return DB::transaction(function () use ($owner, $cartItemId, $quantity): Cart {
            $cart = $this->lockedOrFail($owner);
            $cart->load('cartItems.productVariant.product');
            $item = $cart->cartItems->firstWhere('id', $cartItemId) ?? throw (new ModelNotFoundException)->setModel(CartItem::class);

            $product = $item->productVariant?->product;

            if ($product !== null) {
                $others = $cart->cartItems
                    ->filter(static fn (CartItem $other): bool => $other->id !== $item->id && $other->productVariant?->product_id === $product->id)
                    ->sum('quantity');

                if ($others + $quantity > $product->max_quantity_per_order) {
                    throw ValidationException::withMessages(['quantity' => ["You can buy at most {$product->max_quantity_per_order} of this product per order."]]);
                }
            }

            $item->forceFill(['quantity' => $quantity])->save();

            return $this->touched($cart);
        }, 3);
    }

    /**
     * @throws ModelNotFoundException when the item is not in the owner's cart
     */
    public function removeItem(CartOwner $owner, int $cartItemId): Cart
    {
        return DB::transaction(function () use ($owner, $cartItemId): Cart {
            $cart = $this->lockedOrFail($owner);
            $deleted = CartItem::query()->where('cart_id', $cart->id)->whereKey($cartItemId)->delete();

            if ($deleted === 0) {
                throw (new ModelNotFoundException)->setModel(CartItem::class);
            }

            return $this->touched($cart);
        }, 3);
    }

    /**
     * Validate a code through the coupon contract and remember it on the cart.
     */
    public function applyCoupon(CartOwner $owner, string $code): Cart
    {
        return DB::transaction(function () use ($owner, $code): Cart {
            $cart = $this->lockedOrFail($owner)->load(self::RELATIONS);
            $quote = $this->pricing->price($cart, $cart->display_currency, $cart->country_code, forCheckout: false);
            $items = $quote->purchasableItems();

            if ($items === []) {
                throw new CartEmpty;
            }

            $resolved = $this->coupons->resolve(CartPricingService::couponLookup($cart, $code, $items, $quote->subtotalBase, null));

            $cart->forceFill(['coupon_id' => $resolved->couponId])->save();

            return $this->touched($cart);
        }, 3);
    }

    public function removeCoupon(CartOwner $owner): Cart
    {
        return DB::transaction(function () use ($owner): Cart {
            $cart = $this->lockedOrFail($owner);
            $cart->forceFill(['coupon_id' => null])->save();

            return $this->touched($cart);
        }, 3);
    }

    /**
     * The owner's active cart, locked for the rest of the surrounding transaction.
     *
     * @throws CartEmpty
     */
    public function lockedOrFail(CartOwner $owner): Cart
    {
        $this->lockUser($owner->user);

        return $this->find($owner, lock: true) ?? throw new CartEmpty;
    }

    /**
     * Find the active cart. A signed-in customer without a cart adopts the guest cart they
     * filled before signing in.
     */
    public function find(CartOwner $owner, bool $lock = false): ?Cart
    {
        if ($owner->user !== null) {
            $cart = Cart::query()->where('user_id', $owner->user->id)->where('status', CartStatus::Active)
                ->when($lock, static fn ($query) => $query->lockForUpdate())
                ->latest('id')
                ->first();

            if ($cart !== null || $owner->guestToken === null) {
                return $cart;
            }

            $guestCart = $this->guestCart($owner->guestTokenHash(), $lock);
            $guestCart?->forceFill(['user_id' => $owner->user->id, 'session_token' => null])->save();

            return $guestCart;
        }

        return $owner->guestToken === null ? null : $this->guestCart($owner->guestTokenHash(), $lock);
    }

    private function guestCart(?string $tokenHash, bool $lock): ?Cart
    {
        return Cart::query()->where('session_token', $tokenHash)->whereNull('user_id')->where('status', CartStatus::Active)
            ->when($lock, static fn ($query) => $query->lockForUpdate())
            ->latest('id')
            ->first();
    }

    private function lockedOrNew(CartOwner $owner, string $displayCurrency): Cart
    {
        $this->lockUser($owner->user);

        $cart = $this->find($owner, lock: true);

        if ($cart !== null) {
            return $cart;
        }

        $cart = (new Cart)->forceFill([
            'user_id' => $owner->user?->id,
            'session_token' => $owner->user === null ? $owner->guestTokenHash() : null,
            'display_currency' => $displayCurrency,
            'status' => CartStatus::Active,
            'last_activity_at' => now(),
        ]);
        $cart->save();

        return $cart;
    }

    private function lockUser(?User $user): void
    {
        if ($user !== null) {
            User::query()->whereKey($user->id)->lockForUpdate()->value('id');
        }
    }

    private function touched(Cart $cart): Cart
    {
        $cart->forceFill(['last_activity_at' => now()])->save();

        return $cart->fresh(self::RELATIONS) ?? $cart;
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    private static function sameValues(array $left, array $right): bool
    {
        ksort($left);
        ksort($right);

        return $left === $right;
    }
}
