<?php

namespace App\Domains\Payments\Services;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Orders\DTOs\CartOwner;
use App\Domains\Orders\Services\CartPricingService;
use App\Domains\Orders\Services\CheckoutService;
use App\Domains\Payments\DTOs\GatewayEligibility;
use App\Domains\Payments\DTOs\PaymentCurrencyQuery;
use App\Domains\Payments\Models\PaymentMethod;

/**
 * Checkout step 2 (architecture 13): the gateways this cart may use, each with the display
 * amount, the actual payment amount and the actual payment currency. Where the gateway charges
 * another currency, the cart is priced again with that payment currency (`PricingContext
 * .paymentCurrency`), so the conversion cost is inside the margin floor; a gateway whose
 * currency would put a unit below its floor is listed as unavailable rather than at another
 * price. Place-order re-evaluates all of this server side; this list is advisory.
 */
class PaymentOptionsService
{
    public function __construct(
        private CheckoutService $checkout,
        private CartPricingService $pricing,
        private PaymentEligibilityService $eligibility,
        private EligibilityContextFactory $contexts,
        private CurrencyConverter $converter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function options(CartOwner $owner, string $displayCurrency, string $country, ?string $ipCountry, ?string $email): array
    {
        ['cart' => $cart, 'quote' => $quote] = $this->checkout->validate($owner, $displayCurrency, $country, $email);
        $email ??= $owner->user?->email;

        $context = $this->contexts->fromQuery(new PaymentCurrencyQuery(
            gatewayKey: '',
            methodCode: null,
            displayCurrency: $quote->displayCurrency,
            totalBase: $quote->totalBase,
            countryCode: $country,
            ipCountry: $ipCountry,
            isGuest: $owner->isGuest(),
            userId: $owner->user?->id,
            productIds: array_values(array_unique(array_map(static fn ($line): int => $line->item->productVariant->product_id, $quote->lines))),
        ));

        $eligible = array_values(array_filter($this->eligibility->evaluateAll($context), static fn (GatewayEligibility $result): bool => $result->isEligible()));
        $methods = PaymentMethod::query()->whereIn('payment_gateway_id', array_map(static fn (GatewayEligibility $result): int => $result->gateway->id, $eligible))
            ->where('is_active', true)->orderBy('id')->get()->groupBy('payment_gateway_id');

        $options = [];

        foreach ($eligible as $result) {
            $currency = (string) $result->paymentCurrency;
            $available = true;
            $notice = null;

            if ($currency === $quote->displayCurrency) {
                $amount = $quote->totalDisplay;
            } else {
                $withPayment = $this->pricing->price($cart, $displayCurrency, $country, forCheckout: true, email: $email, paymentCurrency: $currency);
                $available = $withPayment->totalDisplay->equals($quote->totalDisplay) && count($withPayment->purchasableItems()) === count($quote->purchasableItems());
                $notice = $available ? 'charged_in_payment_currency' : 'payment_currency_below_margin';
                $amount = $currency === $quote->baseCurrency
                    ? $this->converter->roundForCurrency($quote->totalBase)
                    : $this->converter->toCheckout($quote->totalBase, $currency)->money;
            }

            $options[] = [
                'gateway' => $result->gateway->key,
                'label' => $result->gateway->name,
                'methods' => ($methods[$result->gateway->id] ?? collect())->map(static fn (PaymentMethod $method): array => ['code' => $method->code, 'label' => $method->label])->values()->all(),
                'available' => $available,
                'payment_currency' => $currency,
                'payment_amount' => $this->converter->present($amount),
                'display_currency' => $quote->displayCurrency,
                'display_amount' => $this->converter->present($quote->totalDisplay),
                'notice' => $notice,
            ];
        }

        return $options;
    }
}
