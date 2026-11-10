<?php

/**
 * Fixtures for the payment tests (M10). Every provider is mocked with Http::fake (Stripe through
 * the official SDK on Laravel's HTTP client): these tests are "mocked; awaiting sandbox
 * verification". No credential here is real.
 */

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Adapters\Paymob\PaymobHmac;
use App\Domains\Payments\Enums\PaymentEligibilityRuleEffect;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Payments\Models\PaymentGatewayCurrency;
use App\Domains\Payments\Models\PaymentMethod;
use App\Domains\Shared\Enums\EnvironmentMode;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

const STRIPE_WEBHOOK_SECRET = 'whsec_test_signing_secret';
const PAYPAL_API = 'https://api-m.sandbox.paypal.com';
const PAYMOB_API = 'https://accept.paymob.com';
const PAYMOB_HMAC_SECRET = 'paymob-test-hmac-secret';

/**
 * An active gateway in sandbox mode with test credentials, the given merchant currencies, and by
 * default one allow-all eligibility rule (pass `rules: []` to test default deny, `keepRules`
 * to keep the seeded rules).
 *
 * @param  list<string>  $currencies
 * @param  list<array<string, mixed>>|null  $rules
 * @param  array<string, mixed>  $attributes
 */
function gatewayFor(string $key, array $currencies = ['USD'], ?array $rules = null, array $attributes = [], ?string $defaultCurrency = null, bool $keepRules = false): PaymentGateway
{
    foreach ([...$currencies, 'USD'] as $currency) {
        CurrencyFactory::ensure($currency);
    }

    $gateway = PaymentGateway::query()->where('key', $key)->first() ?? PaymentGateway::factory()->create(['key' => $key]);
    $gateway->forceFill([
        'name' => ucfirst($key),
        'adapter_key' => $key,
        'is_active' => true,
        'mode' => EnvironmentMode::Sandbox,
        'default_currency' => $defaultCurrency ?? $currencies[0],
        ...$attributes,
    ])->save();

    [$credentials, $secret] = match ($key) {
        'stripe' => [['secret_key' => 'sk_test_fixture', 'publishable_key' => 'pk_test_fixture'], STRIPE_WEBHOOK_SECRET],
        'paypal' => [['client_id' => 'paypal-client-id', 'client_secret' => 'paypal-client-secret', 'webhook_id' => 'WH-FIXTURE'], null],
        'paymob' => [['secret_key' => 'egy_sk_test_fixture', 'public_key' => 'egy_pk_test_fixture', 'api_key' => 'paymob-api-key'], PAYMOB_HMAC_SECRET],
        default => [['token' => 'x'], null],
    };

    PaymentGatewayCredential::query()->updateOrCreate(
        ['payment_gateway_id' => $gateway->id, 'mode' => EnvironmentMode::Sandbox],
        ['credentials' => $credentials, 'webhook_secret' => $secret],
    );

    foreach ($currencies as $currency) {
        PaymentGatewayCurrency::query()->updateOrCreate(
            ['payment_gateway_id' => $gateway->id, 'currency' => $currency],
            ['minor_exponent' => 2, 'is_enabled' => true, 'integration_ref' => $key === 'paymob' ? '4500001' : null],
        );
    }

    if ($key === 'paymob') {
        PaymentMethod::query()->updateOrCreate(['payment_gateway_id' => $gateway->id, 'code' => 'card'], ['label' => 'Card', 'integration_ref' => '4500001', 'supports_refund' => true, 'is_active' => true]);
    }

    if ($keepRules) {
        return $gateway->fresh();
    }

    if ($rules !== null) {
        PaymentEligibilityRule::query()->where('payment_gateway_id', $gateway->id)->delete();
    }

    foreach ($rules ?? [['priority' => 100, 'effect' => PaymentEligibilityRuleEffect::Allow]] as $rule) {
        PaymentEligibilityRule::query()->create(['payment_gateway_id' => $gateway->id, 'priority' => 100, ...$rule]);
    }

    return $gateway->fresh();
}

/**
 * A pending_payment order charged through `$gateway` with one open attempt known to the provider,
 * and `$units` pending units of one item, each worth an equal share.
 *
 * @param  array<string, mixed>  $orderAttributes
 * @return array{0: Order, 1: Payment}
 */
function orderAwaitingPayment(PaymentGateway $gateway, string $amount = '20.00', string $currency = 'USD', ?string $providerPaymentId = 'pi_fixture', int $units = 1, array $orderAttributes = [], string $rate = '1'): array
{
    CurrencyFactory::ensure($currency);
    CountryFactory::ensure('US');
    $base = bcdiv($amount, $rate, 6);

    $order = Order::factory()->create([
        'status' => OrderStatus::PendingPayment,
        'subtotal_base' => $base,
        'total_base' => $base,
        'display_currency' => $currency,
        'display_rate' => $rate,
        'display_total' => $amount,
        'payment_currency' => $currency,
        'payment_rate' => $rate,
        'payment_total' => $amount,
        'payment_gateway_id' => $gateway->id,
        'expires_at' => now()->addMinutes(30),
        ...$orderAttributes,
    ]);

    $variant = ProductVariant::factory()->create();
    $unitBase = bcdiv($base, (string) $units, 6);
    $unitDisplay = bcdiv($amount, (string) $units, 6);
    $publicIds = array_map(static fn (): string => (string) Str::ulid(), range(1, $units));

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => $units,
        'unit_price_base' => $unitBase,
        'line_total_base' => $base,
        'estimated_unit_cost_base' => bcmul($unitBase, '0.8', 6),
        'pricing_breakdown' => ['units' => array_map(static fn (string $id): array => ['unit_public_id' => $id, 'gross_base' => $unitBase, 'discount_base' => '0', 'net_base' => $unitBase, 'net_display' => $unitDisplay], $publicIds)],
    ]);

    foreach ($publicIds as $publicId) {
        (new OrderItemUnit)->forceFill(['public_id' => $publicId, 'order_id' => $order->id, 'order_item_id' => $item->id, 'status' => OrderItemUnitStatus::Pending])->save();
    }

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'payment_gateway_id' => $gateway->id,
        'status' => PaymentStatus::RequiresAction,
        'provider_payment_id' => $providerPaymentId,
        'base_amount' => $base,
        'rate' => $rate,
        'amount' => $amount,
        'currency' => $currency,
    ]);

    return [$order->fresh(), $payment->fresh()];
}

/**
 * Mark the units of an order with the given statuses, in id order.
 *
 * @param  list<OrderItemUnitStatus>  $statuses
 */
function setUnitStatuses(Order $order, array $statuses): void
{
    foreach (OrderItemUnit::query()->where('order_id', $order->id)->orderBy('id')->get() as $index => $unit) {
        $status = $statuses[$index];
        $unit->forceFill(['status' => $status, 'delivered_at' => $status === OrderItemUnitStatus::Delivered ? now() : null])->save();
    }
}

/**
 * Drop every earlier Http::fake stub (the first matching stub wins, so a later fake cannot
 * replace an earlier one) and keep stray requests forbidden.
 */
function resetHttpFakes(): void
{
    Http::swap(new HttpFactory);
    Http::preventStrayRequests();
}

// --- Stripe ---------------------------------------------------------------------------------

/**
 * @param  array<string, mixed>  $extra
 * @return array<string, mixed>
 */
function stripeIntent(string $id = 'pi_fixture', string $status = 'succeeded', int $amountReceived = 2000, string $currency = 'usd', array $extra = []): array
{
    return [
        'id' => $id,
        'object' => 'payment_intent',
        'amount' => $amountReceived,
        'amount_received' => $status === 'succeeded' ? $amountReceived : 0,
        'currency' => $currency,
        'status' => $status,
        'client_secret' => $id.'_secret_fixture',
        'last_payment_error' => null,
        'latest_charge' => [
            'id' => 'ch_'.$id,
            'object' => 'charge',
            'amount_refunded' => 0,
            'payment_method_details' => ['card' => ['country' => 'US', 'fingerprint' => 'fp_fixture']],
        ],
        ...$extra,
    ];
}

/**
 * @param  array<string, mixed>  $overrides  extra or replacing URL patterns
 */
function fakeStripe(mixed $intent = null, array $overrides = []): void
{
    Http::fake($overrides + [
        'api.stripe.com/v1/payment_intents/*' => $intent ?? Http::response(stripeIntent()),
        'api.stripe.com/v1/payment_intents' => Http::response(stripeIntent(status: 'requires_payment_method')),
        'api.stripe.com/v1/refunds*' => Http::response(['id' => 're_fixture', 'object' => 'refund', 'amount' => 2000, 'currency' => 'usd', 'status' => 'succeeded', 'metadata' => []]),
    ]);
}

/**
 * @param  array<string, mixed>  $object
 */
function stripeEvent(string $type, array $object, ?string $id = null): string
{
    return json_encode(['id' => $id ?? 'evt_'.Str::random(12), 'object' => 'event', 'type' => $type, 'created' => time(), 'data' => ['object' => $object]], JSON_THROW_ON_ERROR);
}

function stripeSignature(string $payload, ?int $timestamp = null, string $secret = STRIPE_WEBHOOK_SECRET): string
{
    $timestamp ??= time();

    return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
}

function postStripeWebhook(string $payload, ?string $signature = null): TestResponse
{
    $server = ['CONTENT_TYPE' => 'application/json'];

    if ($signature !== null) {
        $server['HTTP_STRIPE_SIGNATURE'] = $signature;
    }

    return test()->call('POST', '/webhooks/stripe', [], [], [], $server, $payload);
}

// --- PayPal ---------------------------------------------------------------------------------

/**
 * @return array<string, mixed>
 */
function paypalOrder(string $id = 'PAYPAL-ORDER-1', string $status = 'COMPLETED', ?string $captureStatus = 'COMPLETED', string $value = '20.00', string $currency = 'USD'): array
{
    $order = ['id' => $id, 'status' => $status, 'purchase_units' => [['reference_id' => 'x', 'payments' => []]]];

    if ($captureStatus !== null) {
        $order['purchase_units'][0]['payments']['captures'] = [[
            'id' => 'CAPTURE-1',
            'status' => $captureStatus,
            'amount' => ['currency_code' => $currency, 'value' => $value],
            'seller_receivable_breakdown' => ['paypal_fee' => ['currency_code' => $currency, 'value' => '0.88']],
        ]];
    }

    return $order;
}

/**
 * @param  array<string, mixed>  $overrides
 */
function fakePayPal(mixed $order = null, mixed $verify = null, array $overrides = []): void
{
    Http::fake($overrides + [
        PAYPAL_API.'/v1/oauth2/token' => Http::response(['access_token' => 'paypal-access-token', 'expires_in' => 32400]),
        PAYPAL_API.'/v1/notifications/verify-webhook-signature' => $verify ?? Http::response(['verification_status' => 'SUCCESS']),
        PAYPAL_API.'/v2/checkout/orders/*/capture' => Http::response(paypalOrder()),
        PAYPAL_API.'/v2/checkout/orders/*' => $order ?? Http::response(paypalOrder()),
        PAYPAL_API.'/v2/checkout/orders' => Http::response(['id' => 'PAYPAL-ORDER-1', 'status' => 'CREATED', 'links' => [['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1']]]),
        PAYPAL_API.'/v2/payments/captures/*/refund' => Http::response(['id' => 'REFUND-1', 'status' => 'COMPLETED', 'amount' => ['currency_code' => 'USD', 'value' => '20.00']]),
    ]);
}

/**
 * @param  array<string, mixed>  $resource
 */
function paypalEvent(string $type, array $resource, ?string $id = null): string
{
    return json_encode(['id' => $id ?? 'WH-EVT-'.Str::random(10), 'event_type' => $type, 'resource_type' => 'capture', 'resource' => $resource], JSON_THROW_ON_ERROR);
}

/**
 * @param  array<string, string>|null  $headers  null sends a complete set of transmission headers
 */
function postPayPalWebhook(string $payload, ?array $headers = null): TestResponse
{
    $headers ??= [
        'PAYPAL-TRANSMISSION-ID' => 'tx-1',
        'PAYPAL-TRANSMISSION-TIME' => now()->toIso8601String(),
        'PAYPAL-CERT-URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-1',
        'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
        'PAYPAL-TRANSMISSION-SIG' => 'c2lnbmF0dXJl',
    ];

    $server = ['CONTENT_TYPE' => 'application/json'];

    foreach ($headers as $name => $value) {
        $server['HTTP_'.str_replace('-', '_', strtoupper($name))] = $value;
    }

    return test()->call('POST', '/webhooks/paypal', [], [], [], $server, $payload);
}

// --- Paymob ---------------------------------------------------------------------------------

/**
 * A processed-callback transaction object shaped like Paymob's sample: real JSON booleans,
 * `error_occured` spelled as Paymob spells it, nested order and source data.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function paymobTransaction(array $overrides = []): array
{
    return array_replace_recursive([
        'id' => 192036465,
        'pending' => false,
        'amount_cents' => 100000,
        'success' => true,
        'is_auth' => false,
        'is_capture' => false,
        'is_standalone_payment' => true,
        'is_voided' => false,
        'is_refunded' => false,
        'is_3d_secure' => true,
        'integration_id' => 4500001,
        'has_parent_transaction' => false,
        'order' => ['id' => 217503754, 'merchant_order_id' => null],
        'created_at' => '2026-11-10T12:00:00.123456',
        'currency' => 'EGP',
        'source_data' => ['pan' => '2346', 'type' => 'card', 'sub_type' => 'MasterCard'],
        'error_occured' => false,
        'owner' => 302852,
        'refunded_amount_cents' => 0,
        'captured_amount' => 0,
        'updated_at' => '2026-11-10T12:00:05.000000',
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $transaction
 */
function postPaymobWebhook(array $transaction, ?string $hmac = null, bool $signed = true): TestResponse
{
    $body = json_encode(['type' => 'TRANSACTION', 'obj' => $transaction], JSON_THROW_ON_ERROR);
    $hmac ??= PaymobHmac::sign($transaction, PAYMOB_HMAC_SECRET);
    $uri = '/webhooks/paymob'.($signed ? '?hmac='.$hmac : '');

    return test()->call('POST', $uri, [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function fakePaymob(mixed $transaction = null, array $overrides = []): void
{
    Http::fake($overrides + [
        PAYMOB_API.'/api/auth/tokens' => Http::response(['token' => 'paymob-auth-token']),
        PAYMOB_API.'/api/acceptance/transactions/*' => $transaction ?? Http::response(paymobTransaction()),
        PAYMOB_API.'/api/ecommerce/orders/transaction_inquiry' => $transaction ?? Http::response(paymobTransaction()),
        PAYMOB_API.'/v1/intention/' => Http::response(['id' => 'pi_paymob_1', 'intention_order_id' => 217503754, 'client_secret' => 'egy_csk_test_fixture', 'status' => 'intended']),
        PAYMOB_API.'/api/acceptance/void_refund/refund' => Http::response(['id' => 192036999, 'success' => true, 'pending' => false]),
    ]);
}
