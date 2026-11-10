<?php

/**
 * Fixtures for the supplier engine and fulfilment tests (M8, M9). Reloadly is always mocked with
 * Http::fake: these tests are "mocked; awaiting sandbox verification".
 */

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Suppliers\Enums\SupplierAvailability;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierCredential;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierProductPrice;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

const RELOADLY_API = 'https://giftcards-sandbox.reloadly.com';

/**
 * An active Reloadly supplier in sandbox mode with test credentials and a cached balance.
 *
 * @param  array<string, mixed>  $attributes
 */
function reloadlySupplier(array $attributes = []): Supplier
{
    CurrencyFactory::ensure('USD');

    $supplier = Supplier::factory()->create(['name' => 'Reloadly '.Str::random(6), 'adapter_key' => 'reloadly_giftcards']);
    $supplier->forceFill([
        'status' => SupplierStatus::Active,
        'mode' => EnvironmentMode::Sandbox,
        'last_balance' => '1000',
        'balance_currency' => 'USD',
        'last_balance_at' => now(),
        'fallback_enabled' => true,
        ...$attributes,
    ])->save();

    (new SupplierCredential)->forceFill([
        'supplier_id' => $supplier->id,
        'mode' => EnvironmentMode::Sandbox,
        'credentials' => ['client_id' => 'test-client-id', 'client_secret' => 'test-client-secret-value'],
    ])->save();

    return $supplier->fresh();
}

/**
 * @param  array<string, mixed>  $attributes
 */
function stockSupplier(array $attributes = []): Supplier
{
    $supplier = Supplier::factory()->create(['name' => 'Stock '.Str::random(6), 'adapter_key' => 'internal_stock']);
    $supplier->forceFill(['status' => SupplierStatus::Active, 'fallback_enabled' => true, ...$attributes])->save();

    return $supplier->fresh();
}

/**
 * Map a variant to a supplier product with a fresh synced cost.
 *
 * @param  array<string, mixed>  $attributes
 */
function mapToSupplier(ProductVariant $variant, Supplier $supplier, string $cost = '9', array $attributes = [], string $externalId = '1234', string $unitPrice = '10'): SupplierProductMapping
{
    $product = SupplierProduct::factory()->create([
        'supplier_id' => $supplier->id,
        'external_id' => $externalId,
        'sender_currency' => 'USD',
        'availability' => SupplierAvailability::Available,
    ]);

    (new SupplierProductPrice)->forceFill([
        'supplier_product_id' => $product->id,
        'denomination' => $unitPrice,
        'cost' => $cost,
        'cost_currency' => 'USD',
        'captured_at' => now(),
    ])->save();

    return SupplierProductMapping::factory()->create([
        'product_variant_id' => $variant->id,
        'supplier_id' => $supplier->id,
        'supplier_product_id' => $product->id,
        'role' => SupplierProductMappingRole::Primary,
        'supplier_unit_price' => $unitPrice,
        'supplier_unit_currency' => 'USD',
        'is_active' => true,
        ...$attributes,
    ]);
}

/**
 * A paid order fixture (M10 is not built: nothing in the application marks an order paid), with
 * `$quantity` pending units sold at `$unitPrice` base each.
 *
 * @param  array<string, mixed>  $orderAttributes
 */
function paidOrderFor(ProductVariant $variant, int $quantity = 1, DeliveryType $type = DeliveryType::InstantCode, string $unitPrice = '20', array $orderAttributes = [], OrderStatus $status = OrderStatus::Paid): Order
{
    $total = bcmul($unitPrice, (string) $quantity, 6);
    $order = Order::factory()->create(['status' => $status, 'paid_at' => now(), 'subtotal_base' => $total, 'total_base' => $total, 'display_total' => $total, 'payment_currency' => 'USD', ...$orderAttributes]);
    $payment = Payment::factory()->create(['order_id' => $order->id, 'status' => PaymentStatus::Succeeded, 'amount' => $total, 'base_amount' => $total]);
    $order->forceFill(['primary_payment_id' => $payment->id])->save();

    $publicIds = array_map(static fn (): string => (string) Str::ulid(), range(1, $quantity));

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'delivery_type' => $type,
        'quantity' => $quantity,
        'unit_price_base' => $unitPrice,
        'line_total_base' => $total,
        'pricing_breakdown' => ['units' => array_map(static fn (string $id): array => ['unit_public_id' => $id, 'gross_base' => $unitPrice, 'discount_base' => '0', 'net_base' => $unitPrice, 'net_display' => $unitPrice], $publicIds)],
    ]);

    foreach ($publicIds as $publicId) {
        (new OrderItemUnit)->forceFill(['public_id' => $publicId, 'order_id' => $order->id, 'order_item_id' => $item->id, 'status' => OrderItemUnitStatus::Pending])->save();
    }

    return $order->fresh();
}

function reloadlyTransaction(string $status = 'SUCCESSFUL', string $transactionId = '9001', ?string $customIdentifier = null): array
{
    return [
        'transactionId' => (int) $transactionId,
        'amount' => 10.5,
        'discount' => 0,
        'currencyCode' => 'USD',
        'fee' => 0.5,
        'customIdentifier' => $customIdentifier,
        'status' => $status,
        'transactionCreatedTime' => '2026-11-10 10:00:00',
        'balanceInfo' => ['oldBalance' => 1000, 'newBalance' => 990.25, 'cost' => 9.75, 'currencyCode' => 'USD', 'currencyName' => 'US Dollar'],
    ];
}

/**
 * Fake the Reloadly API. `$orders` answers POST /orders (a sequence or a closure); the rest have
 * sensible defaults. Card numbers are written as raw JSON numbers, as the API types them.
 *
 * @param  array<string, mixed>  $overrides  extra or replacing URL patterns
 */
function fakeReloadly(mixed $orders = null, string $cardNumber = '12345678901234567890', array $overrides = []): void
{
    Http::fake([
        'auth.reloadly.com/oauth/token' => Http::response(['access_token' => 'test-access-token', 'expires_in' => 86400, 'token_type' => 'Bearer']),
        RELOADLY_API.'/orders' => $orders ?? Http::response(reloadlyTransaction()),
        RELOADLY_API.'/orders/transactions/*/cards' => Http::response('[{"cardNumber":'.$cardNumber.',"pinCode":4321}]', 200, ['Content-Type' => 'application/json']),
        RELOADLY_API.'/reports/transactions*' => Http::response(['content' => [], 'last' => true]),
        RELOADLY_API.'/accounts/balance' => Http::response(['balance' => 500.12345, 'currencyCode' => 'USD', 'currencyName' => 'US Dollar']),
        ...$overrides,
    ]);
}

/**
 * @return list<Request>
 */
function reloadlyOrderPosts(): array
{
    return Http::recorded(static fn (Request $request): bool => $request->method() === 'POST' && str_ends_with($request->url(), '/orders'))
        ->map(static fn (array $pair): Request => $pair[0])->values()->all();
}
