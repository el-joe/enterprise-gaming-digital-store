<?php

/**
 * MCP_SERVER.md sections 2, 5 and 8: read-only default, write scopes, the pending-action lifecycle
 * (confirmation token, expiry, reuse, another token, tampering), admin approval, and the business
 * rules the write tools inherit from the existing services.
 */

use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Mcp\Enums\McpAuditLogOutcome;
use App\Domains\Mcp\Enums\McpPendingActionApprovedVia;
use App\Domains\Mcp\Enums\McpPendingActionStatus;
use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Mcp\Models\McpAuditLog;
use App\Domains\Mcp\Models\McpPendingAction;
use App\Domains\Mcp\Services\McpTokenService;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Pricing\Models\PricingOverrideLog;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Promotions\Enums\CouponCreatedByType;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Jobs\SyncSupplierCatalog;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

/**
 * A draft product that could be published (one active variant, code delivery).
 */
function mcpDraftProduct(string $sku = 'DRAFT-SKU'): Product
{
    $variant = sellableVariant('5', product: ['sku' => $sku, 'status' => ProductStatus::Draft]);

    return $variant->product->fresh();
}

/**
 * A variant with cost 5 and an explicit base price of 10.
 */
function mcpPricedVariant(string $sku = 'PRICED-10'): ProductVariant
{
    $variant = sellableVariant('5', ['sku' => $sku]);
    VariantPrice::factory()->create(['product_variant_id' => $variant->id, 'country_code' => '*', 'currency' => 'USD', 'price' => '10.000000']);

    return $variant;
}

/**
 * An order in review whose single unit's latest supplier attempt has the given status.
 *
 * @return array{order: Order, unit: OrderItemUnit}
 */
function mcpOrderAwaitingRetry(?SupplierTransactionStatus $latest = SupplierTransactionStatus::Rejected): array
{
    $variant = sellableVariant('8');
    $supplier = stockSupplier();
    $order = paidOrderFor($variant, status: OrderStatus::ManualReview);
    $unit = OrderItemUnit::query()->where('order_id', $order->id)->sole();
    $unit->forceFill(['status' => OrderItemUnitStatus::ManualReview])->save();

    if ($latest !== null) {
        SupplierTransaction::factory()->create(['order_item_unit_id' => $unit->id, 'supplier_id' => $supplier->id, 'status' => $latest, 'attempt_no' => 1]);
    }

    return ['order' => $order, 'unit' => $unit->fresh()];
}

describe('read-only by default', function (): void {
    it('refuses writes from a read-only account even when the token holds the write scope', function (): void {
        $product = mcpDraftProduct();
        ['token' => $token] = mcpToken([McpScope::ProductsWrite, McpScope::ActionsConfirm]);

        mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])
            ->assertForbidden()
            ->assertJson(['success' => false, 'code' => 'read_only']);

        expect(McpPendingAction::query()->count())->toBe(0)
            ->and($product->fresh()->status)->toBe(ProductStatus::Draft)
            ->and(McpAuditLog::query()->sole()->outcome)->toBe(McpAuditLogOutcome::Denied);
    });

    it('refuses each write tool without its scope', function (string $path, McpScope $scope): void {
        $others = array_values(array_filter(McpScope::cases(), static fn (McpScope $case): bool => $case !== $scope));
        ['token' => $token] = mcpToken($others, writable: true);

        mcpPost($token, $path, ['reason' => 'Any reason'])->assertForbidden()->assertJson(['code' => 'scope_missing']);
    })->with([
        'update_product_price' => ['actions/update-product-price', McpScope::ProductsWrite],
        'activate_product' => ['actions/activate-product', McpScope::ProductsWrite],
        'deactivate_product' => ['actions/deactivate-product', McpScope::ProductsWrite],
        'create_coupon' => ['actions/create-coupon', McpScope::CouponsWrite],
        'trigger_catalog_sync' => ['actions/trigger-catalog-sync', McpScope::CatalogSync],
        'retry_verified_failed_order' => ['actions/retry-verified-failed-order', McpScope::OrdersRetry],
    ]);

    it('issues read-only tokens unless writes are allowed explicitly', function (): void {
        $this->artisan('mcp:token', ['action' => 'issue', 'account' => 'claude-desktop'])->assertSuccessful();
        $this->artisan('mcp:token', ['action' => 'issue', 'account' => 'claude-writer', '--scope' => ['products:write']])->assertFailed();
        $this->artisan('mcp:token', ['action' => 'issue', 'account' => 'claude-writer', '--scope' => ['products:read', 'products:write'], '--allow-writes' => true])->assertSuccessful();

        $tokens = collect(app(McpTokenService::class)->list())->keyBy('account');

        expect($tokens['claude-desktop']['is_read_only'])->toBeTrue()
            ->and($tokens['claude-desktop']['scopes'])->toBe(array_map(static fn (McpScope $scope): string => $scope->value, McpScope::readScopes()))
            ->and($tokens['claude-writer']['is_read_only'])->toBeFalse()
            ->and($tokens['claude-writer']['scopes'])->toBe(['products:read', 'products:write']);
    });
});

describe('pending action and confirmation', function (): void {
    it('creates a pending action and changes nothing until it is confirmed', function (): void {
        $product = mcpDraftProduct();
        ['token' => $token, 'account' => $account] = mcpWriter();

        $response = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product', 'unexpected' => 'dropped'])
            ->assertStatus(202)
            ->assertJsonPath('data.status', 'pending_approval')
            ->assertJsonPath('data.risk_tier', 1)
            ->assertJsonPath('data.approval', ['admin_panel' => true, 'elicitation' => true]);

        $action = McpPendingAction::query()->sole();
        expect($product->fresh()->status)->toBe(ProductStatus::Draft)
            ->and($action->status)->toBe(McpPendingActionStatus::Pending)
            ->and($action->arguments)->toEqual(['product_sku' => $product->sku, 'reason' => 'Launch the product'])
            ->and($action->expires_at->diffInMinutes(now(), true))->toBeGreaterThan(14)->toBeLessThanOrEqual(15)
            ->and($action->confirmation_hash)->toBe(hash('sha256', $response->json('data.confirmation_token')))
            ->and(McpAuditLog::query()->sole()->outcome)->toBe(McpAuditLogOutcome::ConfirmationRequired);

        mcpPost($token, "actions/{$action->public_id}/confirm", ['confirmation_token' => $response->json('data.confirmation_token')])
            ->assertOk()
            ->assertJsonPath('data.status', 'executed')
            ->assertJsonPath('data.approved_via', 'elicitation')
            ->assertJsonPath('data.result.status', 'active');

        expect($product->fresh()->status)->toBe(ProductStatus::Active)
            ->and(AuditLog::query()->where('action', 'mcp.action_executed')->sole())
            ->actor_type->toBe(AuditLogActorType::Mcp)
            ->actor_id->toBe($account->id)
            ->and(AuditLog::query()->where('action', 'catalog.product.status_changed')->sole()->actor_type)->toBe(AuditLogActorType::Mcp)
            ->and(AuditLog::query()->pluck('action')->all())->toContain('mcp.action_requested', 'mcp.action_approved', 'mcp.action_executed');
    });

    it('accepts a confirmation token once only', function (): void {
        $product = mcpDraftProduct();
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])->assertStatus(202);
        $confirm = ['confirmation_token' => $request->json('data.confirmation_token')];

        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", $confirm)->assertOk();
        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", $confirm)
            ->assertStatus(409)
            ->assertJson(['code' => 'action_not_pending']);

        expect(AuditLog::query()->where('action', 'mcp.action_executed')->count())->toBe(1)
            ->and(McpPendingAction::query()->sole()->confirmation_hash)->toBeNull();
    });

    it('refuses confirmation after expiry and marks the action expired', function (): void {
        $product = mcpDraftProduct();
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])->assertStatus(202);

        $this->travel(16)->minutes();

        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", ['confirmation_token' => $request->json('data.confirmation_token')])
            ->assertStatus(410)
            ->assertJson(['code' => 'confirmation_expired']);

        expect(McpPendingAction::query()->sole()->status)->toBe(McpPendingActionStatus::Expired)
            ->and($product->fresh()->status)->toBe(ProductStatus::Draft);

        mcpGet($token, "actions/{$request->json('data.action_id')}")->assertOk()->assertJsonPath('data.status', 'expired');
    });

    it('expires due actions on schedule', function (): void {
        ['token' => $token] = mcpWriter();
        mcpPost($token, 'actions/activate-product', ['product_sku' => mcpDraftProduct()->sku, 'reason' => 'Launch the product'])->assertStatus(202);

        $this->artisan('mcp:expire-pending-actions')->assertSuccessful();
        expect(McpPendingAction::query()->sole()->status)->toBe(McpPendingActionStatus::Pending);

        $this->travel(16)->minutes();
        $this->artisan('mcp:expire-pending-actions')->assertSuccessful();
        expect(McpPendingAction::query()->sole()->status)->toBe(McpPendingActionStatus::Expired);
    });

    it('accepts confirmation only from the token that requested the action', function (): void {
        $product = mcpDraftProduct();
        ['token' => $token, 'account' => $account] = mcpWriter();
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])->assertStatus(202);
        $body = ['confirmation_token' => $request->json('data.confirmation_token')];
        $path = "actions/{$request->json('data.action_id')}/confirm";

        $sameAccountOtherToken = $account->createToken('second', [McpScope::ProductsWrite->ability(), McpScope::ActionsConfirm->ability()])->plainTextToken;
        mcpPost($sameAccountOtherToken, $path, $body)->assertForbidden()->assertJson(['code' => 'forbidden']);

        ['token' => $otherAccount] = mcpWriter();
        mcpPost($otherAccount, $path, $body)->assertNotFound();

        expect($product->fresh()->status)->toBe(ProductStatus::Draft);
        mcpPost($token, $path, $body)->assertOk();
    });

    it('rejects a wrong confirmation token without consuming the right one', function (): void {
        $product = mcpDraftProduct();
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])->assertStatus(202);
        $path = "actions/{$request->json('data.action_id')}/confirm";

        mcpPost($token, $path, ['confirmation_token' => str_repeat('x', 48)])->assertForbidden()->assertJson(['code' => 'confirmation_invalid']);
        mcpPost($token, $path, ['confirmation_token' => 'short'])->assertUnprocessable();
        expect($product->fresh()->status)->toBe(ProductStatus::Draft);

        mcpPost($token, $path, ['confirmation_token' => $request->json('data.confirmation_token')])->assertOk();
    });

    it('gives no confirmation token without actions:confirm, and refuses MCP confirmation of tier 2', function (): void {
        ['token' => $noConfirm] = mcpWriter(canConfirm: false);
        mcpPost($noConfirm, 'actions/activate-product', ['product_sku' => mcpDraftProduct()->sku, 'reason' => 'Launch the product'])
            ->assertStatus(202)
            ->assertJsonPath('data.approval.elicitation', false)
            ->assertJsonMissingPath('data.confirmation_token');

        ['order' => $order, 'unit' => $unit] = mcpOrderAwaitingRetry();
        ['token' => $token] = mcpWriter();
        $tier2 = mcpPost($token, 'actions/retry-verified-failed-order', ['order_number' => $order->order_number, 'unit_public_id' => $unit->public_id, 'reason' => 'Supplier rejected it'])
            ->assertStatus(202)
            ->assertJsonPath('data.risk_tier', 2)
            ->assertJsonMissingPath('data.confirmation_token');

        mcpPost($token, "actions/{$tier2->json('data.action_id')}/confirm", ['confirmation_token' => str_repeat('a', 48)])
            ->assertForbidden()
            ->assertJson(['code' => 'admin_approval_required']);
    });

    it('refuses a second identical pending action', function (): void {
        $sku = mcpDraftProduct()->sku;
        ['token' => $token] = mcpWriter();

        mcpPost($token, 'actions/activate-product', ['product_sku' => $sku, 'reason' => 'Launch the product'])->assertStatus(202);
        mcpPost($token, 'actions/activate-product', ['product_sku' => $sku, 'reason' => 'Launch the product'])->assertStatus(409)->assertJson(['code' => 'duplicate_action']);
    });

    it('fails an action whose stored arguments were tampered with', function (): void {
        $product = mcpDraftProduct();
        $other = mcpDraftProduct('OTHER-SKU');
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])->assertStatus(202);

        DB::table('mcp_pending_actions')->update(['arguments' => json_encode(['product_sku' => 'OTHER-SKU', 'reason' => 'Launch the product'])]);

        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", ['confirmation_token' => $request->json('data.confirmation_token')])
            ->assertOk()
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.result.error.code', 'arguments_tampered');

        expect($other->fresh()->status)->toBe(ProductStatus::Draft)
            ->and($product->fresh()->status)->toBe(ProductStatus::Draft);
    });

    it('reports action status to the owning account with a write scope only', function (): void {
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => mcpDraftProduct()->sku, 'reason' => 'Launch the product'])->assertStatus(202);
        $path = "actions/{$request->json('data.action_id')}";

        mcpGet($token, $path)->assertOk()->assertJsonPath('data.status', 'pending_approval')->assertJsonMissingPath('data.confirmation_token');

        ['token' => $stranger] = mcpWriter();
        mcpGet($stranger, $path)->assertNotFound();
    });

    it('validates write arguments before storing anything', function (): void {
        ['token' => $token] = mcpWriter();

        mcpPost($token, 'actions/update-product-price', ['variant_sku' => 'X', 'new_price_base' => 12.5, 'reason' => 'Cheaper'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['new_price_base']);
        mcpPost($token, 'actions/activate-product', ['product_sku' => 'NO-SUCH', 'reason' => 'Launch the product'])->assertNotFound();

        expect(McpPendingAction::query()->count())->toBe(0);
    });
});

describe('admin approval', function (): void {
    it('lets staff with mcp_actions.approve and the tool permission approve, and runs it as them', function (): void {
        $product = mcpDraftProduct();
        ['token' => $token] = mcpWriter(canConfirm: false);
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])->assertStatus(202);

        actingAsStaffWith(['mcp_actions.approve']);
        $this->postJson("/admin/api/mcp-actions/{$request->json('data.action_id')}/approve")->assertForbidden()->assertJson(['code' => 'forbidden']);
        expect($product->fresh()->status)->toBe(ProductStatus::Draft);

        $staff = actingAsStaffWith(['mcp_actions.approve', 'catalog.publish']);
        $this->getJson('/admin/api/mcp-actions?status=pending')->assertOk()->assertJsonPath('data.0.tool', 'activate_product')->assertJsonPath('data.0.requested_by', McpPendingAction::query()->sole()->serviceAccount->name);
        $this->postJson("/admin/api/mcp-actions/{$request->json('data.action_id')}/approve", ['note' => 'Looks right'])
            ->assertOk()
            ->assertJsonPath('data.status', 'executed')
            ->assertJsonPath('data.approved_via', 'admin_panel');

        $action = McpPendingAction::query()->sole();
        expect($product->fresh()->status)->toBe(ProductStatus::Active)
            ->and($action->approved_via)->toBe(McpPendingActionApprovedVia::AdminPanel)
            ->and($action->approved_by)->toBe($staff->id)
            ->and(AuditLog::query()->where('action', 'catalog.product.status_changed')->sole()->actor_id)->toBe($staff->id);
    });

    it('needs the permission to see the queue, and rejects with a note', function (): void {
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => mcpDraftProduct()->sku, 'reason' => 'Launch the product'])->assertStatus(202);

        actingAsStaffWith(['catalog.publish']);
        $this->getJson('/admin/api/mcp-actions')->assertForbidden();

        actingAsStaffWith(['mcp_actions.approve']);
        $this->postJson("/admin/api/mcp-actions/{$request->json('data.action_id')}/reject")->assertUnprocessable()->assertJsonValidationErrors(['note']);
        $this->postJson("/admin/api/mcp-actions/{$request->json('data.action_id')}/reject", ['note' => 'Not this week'])->assertOk()->assertJsonPath('data.status', 'rejected');

        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", ['confirmation_token' => $request->json('data.confirmation_token')])
            ->assertStatus(409)
            ->assertJson(['code' => 'action_not_pending']);
    });

    it('fails an approved action whose requesting token was revoked', function (): void {
        $product = mcpDraftProduct();
        ['token' => $token, 'model' => $model] = mcpWriter(canConfirm: false);
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])->assertStatus(202);
        $model->delete();

        actingAsStaffWith(['mcp_actions.approve', 'catalog.publish']);
        $this->postJson("/admin/api/mcp-actions/{$request->json('data.action_id')}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.result.error.code', 'forbidden');

        expect($product->fresh()->status)->toBe(ProductStatus::Draft);
    });
});

describe('write tools keep the business rules of the existing services', function (): void {
    it('refuses to publish a product customers could not buy', function (): void {
        $product = Product::factory()->create(['status' => ProductStatus::Draft, 'sku' => 'EMPTY-SKU']);
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/activate-product', ['product_sku' => $product->sku, 'reason' => 'Launch the product'])->assertStatus(202);

        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", ['confirmation_token' => $request->json('data.confirmation_token')])
            ->assertOk()
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.result.error.code', 'product_not_publishable');

        expect($product->fresh()->status)->toBe(ProductStatus::Draft);
    });

    it('deactivates a product and refuses a no-op', function (): void {
        $product = sellableVariant('5', product: ['sku' => 'LIVE-SKU'])->product;
        ['token' => $token] = mcpWriter();

        $request = mcpPost($token, 'actions/deactivate-product', ['product_sku' => 'LIVE-SKU', 'reason' => 'Supplier out of stock'])->assertStatus(202);
        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", ['confirmation_token' => $request->json('data.confirmation_token')])->assertOk()->assertJsonPath('data.status', 'executed');
        expect($product->fresh()->status)->toBe(ProductStatus::Inactive);

        mcpPost($token, 'actions/deactivate-product', ['product_sku' => 'LIVE-SKU', 'reason' => 'Again please'])->assertStatus(409)->assertJson(['code' => 'no_change']);
    });

    it('sets a small price change through the margin guard as tier 1', function (): void {
        $variant = mcpPricedVariant();
        ['token' => $token] = mcpWriter();

        $request = mcpPost($token, 'actions/update-product-price', ['variant_sku' => $variant->sku, 'new_price_base' => '10.50', 'reason' => 'Match the market'])
            ->assertStatus(202)
            ->assertJsonPath('data.risk_tier', 1);

        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", ['confirmation_token' => $request->json('data.confirmation_token')])
            ->assertOk()
            ->assertJsonPath('data.status', 'executed')
            ->assertJsonPath('data.result.previous_price_base', '10.00')
            ->assertJsonPath('data.result.new_price_base', '10.50');

        expect((string) VariantPrice::query()->where('product_variant_id', $variant->id)->sole()->price)->toBe('10.500000')
            ->and($variant->fresh()->pricing_dirty_at)->not->toBeNull();
    });

    it('makes a large change or a price below the floor tier 2, and the margin guard still decides', function (): void {
        $variant = mcpPricedVariant();
        ['token' => $token] = mcpWriter();

        mcpPost($token, 'actions/update-product-price', ['variant_sku' => $variant->sku, 'new_price_base' => '14.00', 'reason' => 'Raise it a lot'])
            ->assertStatus(202)
            ->assertJsonPath('data.risk_tier', 2)
            ->assertJsonMissingPath('data.confirmation_token');

        $below = mcpPost($token, 'actions/update-product-price', ['variant_sku' => $variant->sku, 'new_price_base' => '4.00', 'reason' => 'Clearance sale'])
            ->assertStatus(202)
            ->assertJsonPath('data.risk_tier', 2);
        expect($below->json('data.summary'))->toContain('margin floor');

        actingAsStaffWith(['mcp_actions.approve', 'pricing.manage']);
        $this->postJson("/admin/api/mcp-actions/{$below->json('data.action_id')}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.result.error.code', 'margin_override_forbidden');
        expect((string) VariantPrice::query()->where('product_variant_id', $variant->id)->sole()->price)->toBe('10.000000');

        $again = mcpPost($token, 'actions/update-product-price', ['variant_sku' => $variant->sku, 'new_price_base' => '4.10', 'reason' => 'Clearance sale'])->assertStatus(202);
        actingAsStaffWith(['mcp_actions.approve', 'pricing.manage', 'pricing.override_margin']);
        $this->postJson("/admin/api/mcp-actions/{$again->json('data.action_id')}/approve")->assertOk()->assertJsonPath('data.status', 'executed');

        expect(PricingOverrideLog::query()->sole()->reason)->toBe('Clearance sale');
    });

    it('creates a coupon marked as created by MCP, capped and within margin', function (): void {
        ['token' => $token] = mcpWriter();
        $body = ['code' => 'mcp-spring', 'type' => 'percent', 'value' => '10', 'starts_at' => now()->toDateString(), 'ends_at' => now()->addWeek()->toDateString(), 'usage_limit' => 50, 'reason' => 'Spring campaign'];

        $request = mcpPost($token, 'actions/create-coupon', $body)->assertStatus(202)->assertJsonPath('data.risk_tier', 1);
        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", ['confirmation_token' => $request->json('data.confirmation_token')])->assertOk()->assertJsonPath('data.status', 'executed');

        $coupon = Coupon::query()->sole();
        expect($coupon->code)->toBe('MCP-SPRING')
            ->and($coupon->created_by_type)->toBe(CouponCreatedByType::Mcp)
            ->and($coupon->allow_below_margin)->toBeFalse()
            ->and($coupon->usage_limit)->toBe(50)
            ->and($coupon->usage_limit_per_user)->toBe(1);

        mcpPost($token, 'actions/create-coupon', [...$body, 'code' => 'BIG-ONE', 'value' => '50'])->assertStatus(202)->assertJsonPath('data.risk_tier', 2);
        mcpPost($token, 'actions/create-coupon', [...$body, 'code' => 'HUGE', 'value' => '150'])->assertUnprocessable()->assertJsonValidationErrors(['value']);
        mcpPost($token, 'actions/create-coupon', [...$body, 'code' => 'MCP-SPRING'])->assertUnprocessable()->assertJsonValidationErrors(['code']);
    });

    it('queues a catalog sync recorded as triggered by the MCP account', function (): void {
        Queue::fake();
        $supplier = stockSupplier(['name' => 'Stock main']);
        ['token' => $token, 'account' => $account] = mcpWriter();

        $request = mcpPost($token, 'actions/trigger-catalog-sync', ['supplier' => 'Stock main', 'reason' => 'New products listed'])->assertStatus(202);
        mcpPost($token, "actions/{$request->json('data.action_id')}/confirm", ['confirmation_token' => $request->json('data.confirmation_token')])->assertOk()->assertJsonPath('data.status', 'executed');

        Queue::assertPushed(SyncSupplierCatalog::class, static fn (SyncSupplierCatalog $job): bool => $job->supplierId === $supplier->id && $job->type === 'catalog' && $job->triggeredBy === "mcp:{$account->id}");
    });
});

describe('retry_verified_failed_order', function (): void {
    it('refuses a unit whose latest attempt is not verified as not accepted', function (?SupplierTransactionStatus $status): void {
        ['order' => $order, 'unit' => $unit] = mcpOrderAwaitingRetry($status);
        ['token' => $token] = mcpWriter();

        mcpPost($token, 'actions/retry-verified-failed-order', ['order_number' => $order->order_number, 'unit_public_id' => $unit->public_id, 'reason' => 'Try again please'])
            ->assertStatus(409)
            ->assertJson(['code' => 'retry_not_allowed']);

        expect(McpPendingAction::query()->count())->toBe(0);
    })->with([
        'pending_verification' => [SupplierTransactionStatus::PendingVerification],
        'accepted' => [SupplierTransactionStatus::Accepted],
        'submitting' => [SupplierTransactionStatus::Submitting],
        'succeeded' => [SupplierTransactionStatus::Succeeded],
        'no attempt at all' => [null],
    ]);

    it('retries a verified-rejected unit after staff approval, through the state machine', function (SupplierTransactionStatus $status): void {
        Queue::fake();
        ['order' => $order, 'unit' => $unit] = mcpOrderAwaitingRetry($status);
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/retry-verified-failed-order', ['order_number' => $order->order_number, 'unit_public_id' => $unit->public_id, 'reason' => 'Supplier rejected it'])->assertStatus(202);

        actingAsStaffWith(['mcp_actions.approve']);
        $this->postJson("/admin/api/mcp-actions/{$request->json('data.action_id')}/approve")->assertForbidden();

        actingAsStaffWith(['mcp_actions.approve', 'orders.review']);
        $this->postJson("/admin/api/mcp-actions/{$request->json('data.action_id')}/approve")->assertOk()->assertJsonPath('data.status', 'executed');

        expect($order->fresh()->status)->toBe(OrderStatus::Processing)
            ->and($unit->fresh()->status)->toBe(OrderItemUnitStatus::Pending);
        Queue::assertPushed(FulfilOrder::class);
    })->with([
        'rejected' => [SupplierTransactionStatus::Rejected],
        'not_accepted_verified' => [SupplierTransactionStatus::NotAcceptedVerified],
    ]);

    it('re-checks at execution and blocks a unit that moved to pending_verification after the request', function (): void {
        Queue::fake();
        ['order' => $order, 'unit' => $unit] = mcpOrderAwaitingRetry();
        ['token' => $token] = mcpWriter();
        $request = mcpPost($token, 'actions/retry-verified-failed-order', ['order_number' => $order->order_number, 'unit_public_id' => $unit->public_id, 'reason' => 'Supplier rejected it'])->assertStatus(202);

        SupplierTransaction::factory()->create(['order_item_unit_id' => $unit->id, 'supplier_id' => SupplierTransaction::query()->value('supplier_id'), 'status' => SupplierTransactionStatus::PendingVerification, 'attempt_no' => 2]);

        actingAsStaffWith(['mcp_actions.approve', 'orders.review']);
        $this->postJson("/admin/api/mcp-actions/{$request->json('data.action_id')}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.result.error.code', 'retry_not_allowed');

        expect($order->fresh()->status)->toBe(OrderStatus::ManualReview)
            ->and($unit->fresh()->status)->toBe(OrderItemUnitStatus::ManualReview);
        Queue::assertNotPushed(FulfilOrder::class);
    });
});
