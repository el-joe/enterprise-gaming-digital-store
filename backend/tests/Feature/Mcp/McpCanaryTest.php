<?php

/**
 * Hard denials (MCP_SERVER.md section 3), enforced whatever the scope: canary digital codes,
 * supplier and payment credentials, webhook and HMAC secrets, delivery values and unmasked
 * customer identifiers must not appear in any MCP response or in mcp_audit_logs, even for a
 * token holding every scope.
 */

use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Services\DigitalCodeVault;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\Models\McpAuditLog;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\OrderItemDeliveryValue;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\SupplierCredential;
use App\Domains\Suppliers\Models\SupplierTransaction;

const MCP_CANARY_CODE = 'MCPCANARY-CODE-5512-9087';

const MCP_CANARY_PIN = '90817263';

const MCP_CANARY_SECRET = 'mcp-canary-secret-7d1f0a';

const MCP_CANARY_PLAYER_ID = 'player-canary-4417';

const MCP_CANARY_EMAIL = 'canary.customer@example.org';

const MCP_CANARY_PHONE = '+44 7700 900123';

it('keeps codes, credentials, secrets, delivery values and raw customer data out of every MCP response', function (): void {
    $variant = sellableVariant('8', ['sku' => 'CANARY-V']);
    $variant->product->forceFill(['sku' => 'CANARY-P'])->save();
    $supplier = reloadlySupplier(['name' => 'Canary supplier']);
    (new SupplierCredential)->forceFill(['supplier_id' => $supplier->id, 'mode' => EnvironmentMode::Production, 'credentials' => ['client_id' => MCP_CANARY_SECRET, 'client_secret' => MCP_CANARY_SECRET], 'webhook_secret' => MCP_CANARY_SECRET])->save();
    mapToSupplier($variant, $supplier);
    PaymentGatewayCredential::factory()->create(['credentials' => ['api_key' => MCP_CANARY_SECRET], 'webhook_secret' => MCP_CANARY_SECRET]);
    ServiceAccount::factory()->create(['name' => 'n8n-canary', 'hmac_secret' => MCP_CANARY_SECRET]);

    $order = paidOrderFor($variant, orderAttributes: ['contact_email' => MCP_CANARY_EMAIL, 'guest_email' => MCP_CANARY_EMAIL, 'contact_phone' => MCP_CANARY_PHONE, 'customer_name' => 'Canary Person'], status: OrderStatus::Completed);
    $unit = OrderItemUnit::query()->where('order_id', $order->id)->sole();
    $unit->forceFill(['status' => OrderItemUnitStatus::Delivered, 'delivered_at' => now()])->save();
    $transaction = SupplierTransaction::factory()->create(['order_item_unit_id' => $unit->id, 'supplier_id' => $supplier->id, 'status' => SupplierTransactionStatus::Succeeded, 'external_transaction_id' => MCP_CANARY_SECRET]);
    $stored = app(DigitalCodeVault::class)->store(new DeliverableItem(MCP_CANARY_CODE, MCP_CANARY_PIN, 'https://redeem.example.com/'.MCP_CANARY_CODE), DigitalCodeSource::Supplier, [
        'order_item_unit_id' => $unit->id,
        'supplier_transaction_id' => $transaction->id,
        'product_variant_id' => $variant->id,
    ]);
    OrderItemDeliveryValue::factory()->create(['order_item_id' => $order->orderItems()->sole()->id, 'field_code' => 'player_id', 'value' => MCP_CANARY_PLAYER_ID]);

    ['token' => $token] = mcpWriter();
    $range = ['from' => now()->subDay()->toDateString(), 'to' => now()->toDateString()];

    $responses = [];

    foreach (mcpReadTools() as $tool => [$path, $query]) {
        $path = str_replace(['products/NO-SUCH-SKU', 'variants/NO-SUCH-SKU', 'orders/NOSUCHORDER'], ['products/CANARY-P', 'variants/CANARY-V', "orders/{$order->order_number}"], $path);
        $query = $tool === 'search_customer_orders' ? ['email' => MCP_CANARY_EMAIL] : [...$query, ...(isset($query['from']) ? $range : [])];

        $response = mcpGet($token, $path, $query);
        expect($response->status())->toBe(200, "{$tool} returned {$response->status()}");
        $responses[$tool] = $response->getContent();
    }

    $request = mcpPost($token, 'actions/deactivate-product', ['product_sku' => 'CANARY-P', 'reason' => 'Canary check'])->assertStatus(202);
    $responses['get_action_status'] = mcpGet($token, 'actions/'.$request->json('data.action_id'))->getContent();
    $responses['orders/search by unknown email'] = mcpGet($token, 'orders/search', ['email' => MCP_CANARY_EMAIL.'x'])->getContent();

    $responses['mcp_audit_logs'] = McpAuditLog::query()->get()->toJson();

    $forbidden = [
        'digital code' => MCP_CANARY_CODE,
        'code digits' => '55129087',
        'code mask' => $stored->masked_code,
        'pin' => MCP_CANARY_PIN,
        'credential or secret' => MCP_CANARY_SECRET,
        'reloadly test secret' => 'test-client-secret-value',
        'delivery value' => MCP_CANARY_PLAYER_ID,
        'customer email' => MCP_CANARY_EMAIL,
        'customer email local part' => 'canary.customer',
        'customer phone' => '900123',
        'customer name' => 'Canary Person',
    ];

    foreach ($responses as $tool => $body) {
        foreach ($forbidden as $label => $needle) {
            expect(str_contains($body, $needle))->toBeFalse("{$label} leaked through {$tool}");
        }
    }

    expect($responses['get_order_details'])->toContain('c***@example.org');
});
