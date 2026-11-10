<?php

namespace App\Domains\Orders\Actions;

use App\Domains\Currency\DTOs\ResolvedRate;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Customers\Enums\CustomerConsentChannel;
use App\Domains\Customers\Models\CustomerConsent;
use App\Domains\Orders\DTOs\CartQuote;
use App\Domains\Orders\DTOs\IdempotencyClaim;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\DTOs\PlaceOrderInput;
use App\Domains\Orders\DTOs\PlaceOrderResult;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Orders\Exceptions\CartEmpty;
use App\Domains\Orders\Exceptions\PaymentCurrencyBelowMargin;
use App\Domains\Orders\Exceptions\PriceChanged;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Repositories\OrderRepository;
use App\Domains\Orders\Services\CartPricingService;
use App\Domains\Orders\Services\CartService;
use App\Domains\Orders\Services\CheckoutGuard;
use App\Domains\Orders\Services\IdempotencyStore;
use App\Domains\Orders\Services\OrderItemWriter;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Orders\Services\PlacementHasher;
use App\Domains\Payments\Contracts\PaymentCurrencyResolver;
use App\Domains\Payments\DTOs\PaymentCurrencyQuery;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Checkout step 3 (architecture 13): turn the cart into a pending_payment order exactly once.
 *
 * Duplicate protection, outermost first: the Idempotency-Key claim; the cart row lock and its
 * `converted` status; the placement hash looked up in the current and previous bucket; and the
 * unique `uq_o_placement` index for two carts with the same contents racing. Every price, rate
 * and amount is computed here once, inside the transaction, and stored; nothing is recomputed
 * later. No external call happens inside the transaction.
 */
class PlaceOrderAction
{
    public const string SCOPE = 'checkout.place_order';

    private const int DEFAULT_QUOTE_TTL_MINUTES = 30;

    public function __construct(
        private IdempotencyStore $idempotency,
        private CartService $carts,
        private CartPricingService $pricing,
        private CheckoutGuard $guard,
        private OrderItemWriter $items,
        private PlacementHasher $hasher,
        private OrderRepository $orders,
        private OrderStateMachine $machine,
        private PaymentCurrencyResolver $paymentCurrencies,
        private CheckoutCoupons $coupons,
        private CurrencyConverter $converter,
        private SettingsService $settings,
    ) {}

    public function handle(PlaceOrderInput $input): PlaceOrderResult
    {
        $claim = $this->idempotency->claim(self::SCOPE, $input->idempotencyKey, $input->owner->key(), $input->requestHash);

        if ($claim->isReplay()) {
            return new PlaceOrderResult($claim->replayStatus, $claim->replayBody, replayed: true);
        }

        try {
            return $this->place($input, $claim);
        } catch (Throwable $exception) {
            $this->idempotency->release($claim);

            throw $exception;
        }
    }

    private function place(PlaceOrderInput $input, IdempotencyClaim $claim): PlaceOrderResult
    {
        $preview = $this->carts->find($input->owner)?->load(CartService::RELATIONS) ?? throw new CartEmpty;
        $email = $this->contactEmail($input);
        $buyer = PlacementHasher::buyer($input->owner->user?->id, $email);
        $hashes = $this->hasher->hashes($buyer, $preview, $preview->coupon?->code);

        try {
            return DB::transaction(fn (): PlaceOrderResult => $this->placeLocked($input, $claim, $preview->id, $buyer, $email, array_values($hashes)), 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains($exception->getMessage(), 'uq_o_placement')) {
                throw $exception;
            }

            $existing = $this->orders->findByPlacement(array_values($hashes)) ?? throw $exception;

            return DB::transaction(fn (): PlaceOrderResult => $this->duplicate($claim, $existing));
        }
    }

    /**
     * @param  list<string>  $previewHashes
     */
    private function placeLocked(PlaceOrderInput $input, IdempotencyClaim $claim, int $cartId, string $buyer, string $email, array $previewHashes): PlaceOrderResult
    {
        $cart = Cart::query()->lockForUpdate()->find($cartId);

        if ($cart === null || $cart->status !== CartStatus::Active) {
            $existing = $this->orders->findByPlacement($previewHashes) ?? throw new CartEmpty;

            return $this->duplicate($claim, $existing);
        }

        $cart->load(CartService::RELATIONS);
        $hashes = $this->hasher->hashes($buyer, $cart, $cart->coupon?->code);
        $existing = $this->orders->findByPlacement(array_values(array_unique([...$previewHashes, ...array_values($hashes)])));

        if ($existing !== null) {
            return $this->duplicate($claim, $existing);
        }

        $quote = $this->pricing->price($cart, $input->displayCurrency, $input->country, forCheckout: true, email: $email);
        $this->guard->assertReady($cart, $quote);
        $this->assertExpectedTotal($input, $quote);

        $resolved = $this->paymentCurrencies->resolve(new PaymentCurrencyQuery(
            gatewayKey: $input->gateway,
            methodCode: $input->method,
            displayCurrency: $quote->displayCurrency,
            totalBase: $quote->totalBase,
            countryCode: strtoupper($input->country),
            ipCountry: $input->ipCountry,
            isGuest: $input->owner->isGuest(),
            userId: $input->owner->user?->id,
            productIds: array_values(array_unique(array_map(static fn ($line): int => $line->item->productVariant->product_id, $quote->lines))),
        ));

        $this->assertMarginInPaymentCurrency($cart, $input, $quote, $email, $resolved->currency);

        [$paymentRate, $paymentTotal] = $this->paymentAmount($quote, $resolved->currency);
        $snapshotRate = $paymentRate->quote === $quote->baseCurrency ? $quote->displayRate : $paymentRate;

        $order = $this->machine->open([
            'order_number' => $this->orderNumber(),
            'user_id' => $input->owner->user?->id,
            'guest_email' => $input->owner->isGuest() ? $email : null,
            'contact_email' => $email,
            'customer_name' => $input->owner->user?->name,
            'country_code' => strtoupper($input->country),
            'ip_country' => $input->ipCountry,
            'ip_address' => $input->ip,
            'user_agent' => $input->userAgent === null ? null : Str::limit($input->userAgent, 252),
            'locale' => $input->locale,
            'ga_client_id' => $input->gaClientId,
            'base_currency' => $quote->baseCurrency,
            'subtotal_base' => $quote->subtotalBase->amount,
            'discount_base' => $quote->discountBase->amount,
            'tax_base' => '0',
            'total_base' => $quote->totalBase->amount,
            'display_currency' => $quote->displayCurrency,
            'display_rate' => $quote->displayRate->rate->value,
            'display_total' => $quote->totalDisplay->amount,
            'payment_currency' => $resolved->currency,
            'payment_rate' => $paymentRate->rate->value,
            'payment_total' => $paymentTotal->amount,
            'payment_gateway_id' => $resolved->paymentGatewayId,
            'exchange_rate_history_id' => $snapshotRate->historyId,
            'rate_source' => $snapshotRate->source,
            'rate_fetched_at' => $snapshotRate->fetchedAt,
            'coupon_id' => $quote->coupon?->couponId,
            'coupon_code' => $quote->coupon?->code,
            'expires_at' => now()->addMinutes((int) $this->settings->get('orders', 'quote_ttl_minutes', self::DEFAULT_QUOTE_TTL_MINUTES)),
            'placement_hash' => $hashes['current'],
        ], OrderActor::customer($input->owner->user), [
            'terms_accepted' => true,
            'unit_count' => $quote->unitCount(),
            'coupon_limited_by_margin' => $quote->couponLimitedByMargin,
        ]);

        $this->items->write($order, $quote);

        if ($quote->coupon !== null) {
            $lookup = CartPricingService::couponLookup($cart, $quote->coupon->code, $quote->purchasableItems(), $quote->subtotalBase, $email);
            $this->coupons->reserve($quote->coupon, $lookup, $order->id, $quote->discountBase);
        }

        if ($input->marketingConsent) {
            $this->recordMarketingConsent($input, $email);
        }

        $cart->forceFill(['status' => CartStatus::Converted, 'last_activity_at' => now()])->save();

        $body = $this->body($order, duplicate: false);
        $this->idempotency->complete($claim, 201, $body);

        return new PlaceOrderResult(201, $body, createdOrderPublicId: $order->public_id);
    }

    private function duplicate(IdempotencyClaim $claim, Order $existing): PlaceOrderResult
    {
        $body = [
            'success' => true,
            'message' => 'This order was already placed.',
            'data' => ['order' => ['public_id' => $existing->public_id, 'status' => $existing->status->value], 'next' => null],
            'meta' => ['duplicate' => true],
        ];

        $this->idempotency->complete($claim, 200, $body);

        return new PlaceOrderResult(200, $body);
    }

    /**
     * The customer confirms the total and currency they were shown; anything else is refused.
     */
    private function assertExpectedTotal(PlaceOrderInput $input, CartQuote $quote): void
    {
        $presented = $this->converter->present($quote->totalDisplay);

        if ($input->confirmedCurrency !== $quote->displayCurrency || bccomp($input->expectedTotal, $presented['amount'], Money::SCALE) !== 0) {
            throw new PriceChanged($presented['amount'], $presented['currency']);
        }
    }

    /**
     * The quote the customer confirmed was priced for the display currency. When the gateway
     * charges another currency, its conversion cost belongs in the margin floor too: the cart is
     * priced again with the payment currency, and placement is refused if any unit would then be
     * below its floor (or priced differently). The customer can pay in the displayed currency or
     * choose another method; the confirmed price is never changed silently.
     */
    private function assertMarginInPaymentCurrency(Cart $cart, PlaceOrderInput $input, CartQuote $quote, string $email, string $paymentCurrency): void
    {
        if ($paymentCurrency === $quote->displayCurrency) {
            return;
        }

        $withPayment = $this->pricing->price($cart, $input->displayCurrency, $input->country, forCheckout: true, email: $email, paymentCurrency: $paymentCurrency);

        if (! $withPayment->totalDisplay->equals($quote->totalDisplay) || count($withPayment->purchasableItems()) !== count($quote->purchasableItems())) {
            throw new PaymentCurrencyBelowMargin;
        }
    }

    /**
     * The charged amount, computed once. In the display currency it is the displayed total
     * exactly; otherwise the base total converted with a rate fresh enough for checkout.
     *
     * @return array{0: ResolvedRate, 1: Money}
     */
    private function paymentAmount(CartQuote $quote, string $paymentCurrency): array
    {
        if ($paymentCurrency === $quote->displayCurrency) {
            return [$quote->displayRate, $quote->totalDisplay];
        }

        $rate = $this->converter->usableAtCheckout($paymentCurrency);

        return [$rate, $this->converter->convert($quote->totalBase, $rate)->money];
    }

    private function recordMarketingConsent(PlaceOrderInput $input, string $email): void
    {
        (new CustomerConsent)->forceFill([
            'user_id' => $input->owner->user?->id,
            'email' => $email,
            'channel' => CustomerConsentChannel::EmailMarketing,
            'granted' => true,
            'source' => 'checkout',
            'ip' => $input->ip,
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function body(Order $order, bool $duplicate): array
    {
        return [
            'success' => true,
            'message' => 'Order placed.',
            'data' => [
                'order' => [
                    'public_id' => $order->public_id,
                    'order_number' => $order->order_number,
                    'status' => $order->status->value,
                    'display_total' => $this->converter->present(Money::of($order->display_total, $order->display_currency)),
                    'payment_total' => $this->converter->present(Money::of($order->payment_total, $order->payment_currency)),
                    'expires_at' => $order->expires_at?->toIso8601String(),
                ],
                'next' => null,
            ],
            'meta' => ['duplicate' => $duplicate],
        ];
    }

    private function contactEmail(PlaceOrderInput $input): string
    {
        return $input->owner->user !== null
            ? $input->owner->user->email
            : mb_strtolower(trim((string) $input->email));
    }

    private function orderNumber(): string
    {
        return 'GS-'.now()->format('ymd').'-'.strtoupper(substr((string) Str::ulid(), -10));
    }
}
