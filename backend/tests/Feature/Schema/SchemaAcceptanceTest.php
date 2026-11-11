<?php

use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function attemptFor(OrderItemUnit $unit, Supplier $supplier, int $attemptNo, SupplierTransactionStatus $status): SupplierTransaction
{
    return SupplierTransaction::factory()->create([
        'order_item_unit_id' => $unit->id,
        'supplier_id' => $supplier->id,
        'client_reference' => "u{$unit->public_id}s{$supplier->id}",
        'attempt_no' => $attemptNo,
        'status' => $status,
    ]);
}

it('has every table in section 17 of the database design', function (string $table): void {
    expect(Schema::hasTable($table))->toBeTrue();
})->with([
    'users', 'roles', 'permissions', 'gaming_platforms', 'categories', 'products', 'product_variants', 'product_regions',
    'suppliers', 'supplier_products', 'supplier_product_mappings', 'supplier_transactions', 'supplier_sync_runs',
    'currencies', 'exchange_rates', 'exchange_rate_history', 'pricing_rules', 'payment_gateways', 'payments',
    'payment_transactions', 'payment_webhooks', 'orders', 'order_items', 'order_events', 'order_item_units',
    'digital_codes', 'customer_wallets', 'wallet_transactions', 'coupons', 'promotions', 'reviews', 'support_tickets',
    'wishlist_items', 'notifications', 'seo_metadata', 'blog_posts', 'audit_logs', 'automation_logs',
]);

it('creates exactly the tables in the design', function (): void {
    $expected = [
        'admin_table_states',
        'affiliate_commissions',
        'affiliate_payouts',
        'affiliate_referrals',
        'affiliates',
        'ai_suggestions',
        'analytics_event_dispatches',
        'attribute_definitions',
        'audit_logs',
        'automation_customer_messages',
        'automation_logs',
        'automation_outbox',
        'automation_workflows',
        'banners',
        'blog_categories',
        'blog_posts',
        'cache',
        'cache_locks',
        'cart_items',
        'carts',
        'categories',
        'category_product',
        'content_translations',
        'countries',
        'coupon_redemptions',
        'coupons',
        'currencies',
        'customer_consents',
        'customer_notes',
        'customer_profiles',
        'customer_wallets',
        'daily_sales_summaries',
        'delivery_field_definitions',
        'digital_code_accesses',
        'digital_codes',
        'exchange_rate_history',
        'exchange_rates',
        'export_jobs',
        'failed_jobs',
        'faq_groups',
        'faqs',
        'gaming_platforms',
        'guest_order_tokens',
        'homepage_section_items',
        'homepage_sections',
        'idempotency_keys',
        'job_batches',
        'jobs',
        'manual_fulfillment_tasks',
        'mcp_audit_logs',
        'mcp_pending_actions',
        'media',
        'menu_items',
        'menus',
        'mobile_biometric_challenges', // OD-1, ADR-040
        'mobile_biometric_keys', // OD-1, ADR-040
        'mobile_login_challenges',
        'mobile_push_tokens', // M-A18, ADR-042
        'mobile_refresh_tokens',
        'model_has_permissions',
        'model_has_roles',
        'newsletter_subscribers',
        'not_found_logs',
        'notification_preferences',
        'notification_templates',
        'notifications',
        'order_events',
        'order_item_delivery_values',
        'order_item_financials',
        'order_item_units',
        'order_items',
        'order_notes',
        'orders',
        'otp_challenges',
        'pages',
        'password_reset_tokens',
        'payment_disputes',
        'payment_eligibility_rules',
        'payment_gateway_credentials',
        'payment_gateway_currencies',
        'payment_gateway_fee_rules',
        'payment_gateway_product_exclusions',
        'payment_gateways',
        'payment_methods',
        'payment_transactions',
        'payment_webhooks',
        'payments',
        'permissions',
        'personal_access_tokens',
        'pricing_overrides_log',
        'pricing_rules',
        'product_attribute_values',
        'product_country_restrictions',
        'product_delivery_fields',
        'product_images',
        'product_regions',
        'product_relations',
        'product_variants',
        'products',
        'promotion_targets',
        'promotions',
        'reconciliation_items',
        'reconciliation_runs',
        'redirects',
        'refund_requests',
        'refund_units',
        'refunds',
        'region_country',
        'regions',
        'reviews',
        'risk_assessments',
        'role_has_permissions',
        'roles',
        'saved_filters',
        'scheduled_task_runs',
        'search_console_metrics',
        'search_documents',
        'search_queries',
        'security_events',
        'seo_metadata',
        'seo_templates',
        'service_accounts',
        'sessions',
        'settings',
        'supplier_api_logs',
        'supplier_balance_snapshots',
        'supplier_credentials',
        'supplier_health_checks',
        'supplier_product_mappings',
        'supplier_product_prices',
        'supplier_products',
        'supplier_sync_runs',
        'supplier_transactions',
        'supplier_webhooks',
        'suppliers',
        'support_ticket_attachments',
        'support_ticket_messages',
        'support_tickets',
        'users',
        'variant_prices',
        'wallet_transactions',
        'wishlist_items',
    ];

    $actual = array_values(array_diff(Schema::getTableListing(schema: DB::getDatabaseName(), schemaQualified: false), ['migrations']));

    expect($actual)->toEqualCanonicalizing($expected);
});

it('rejects a second active attempt for a unit', function (SupplierTransactionStatus $active): void {
    $unit = OrderItemUnit::factory()->create();
    $supplier = Supplier::factory()->create();

    attemptFor($unit, $supplier, 1, $active);

    expect(fn () => attemptFor($unit, $supplier, 2, SupplierTransactionStatus::Submitting))
        ->toThrow(QueryException::class, 'uq_st_active_unit');
})->with([
    SupplierTransactionStatus::Submitting,
    SupplierTransactionStatus::Accepted,
    SupplierTransactionStatus::PendingVerification,
    SupplierTransactionStatus::Succeeded,
]);

it('allows a new attempt after not_accepted_verified, then locks again', function (): void {
    $unit = OrderItemUnit::factory()->create();
    $supplier = Supplier::factory()->create();

    $first = attemptFor($unit, $supplier, 1, SupplierTransactionStatus::PendingVerification);
    $first->forceFill(['status' => SupplierTransactionStatus::NotAcceptedVerified])->save();

    $second = attemptFor($unit, $supplier, 2, SupplierTransactionStatus::Submitting);

    expect($second->fresh()->active_unit_key)->toBe($unit->id)
        ->and($first->fresh()->active_unit_key)->toBeNull()
        ->and(fn () => attemptFor($unit, $supplier, 3, SupplierTransactionStatus::Submitting))
        ->toThrow(QueryException::class, 'uq_st_active_unit');
});

it('allows a new attempt after a definitive rejection', function (): void {
    $unit = OrderItemUnit::factory()->create();
    $supplier = Supplier::factory()->create();

    attemptFor($unit, $supplier, 1, SupplierTransactionStatus::Rejected);

    expect(attemptFor($unit, $supplier, 2, SupplierTransactionStatus::Submitting)->exists)->toBeTrue();
});

it('allows active attempts on different units', function (): void {
    $supplier = Supplier::factory()->create();

    attemptFor(OrderItemUnit::factory()->create(), $supplier, 1, SupplierTransactionStatus::Submitting);

    expect(attemptFor(OrderItemUnit::factory()->create(), $supplier, 1, SupplierTransactionStatus::Submitting)->exists)->toBeTrue();
});

it('rejects a duplicate attempt number for one reference', function (): void {
    $unit = OrderItemUnit::factory()->create();
    $supplier = Supplier::factory()->create();

    attemptFor($unit, $supplier, 1, SupplierTransactionStatus::Rejected);

    expect(fn () => attemptFor($unit, $supplier, 1, SupplierTransactionStatus::Rejected))
        ->toThrow(QueryException::class, 'uq_st_reference');
});

it('cannot delete a unit that has a supplier attempt', function (): void {
    $unit = OrderItemUnit::factory()->create();
    attemptFor($unit, Supplier::factory()->create(), 1, SupplierTransactionStatus::Rejected);

    expect(fn () => $unit->delete())->toThrow(QueryException::class);
});

it('allows one order per primary payment', function (): void {
    $payment = Payment::factory()->create();

    Order::factory()->create(['primary_payment_id' => $payment->id]);

    expect(fn () => Order::factory()->create(['primary_payment_id' => $payment->id]))
        ->toThrow(QueryException::class, 'uq_o_primary_payment');
});

it('allows many orders with no primary payment yet', function (): void {
    expect(Order::factory()->count(2)->create()->pluck('primary_payment_id')->filter())->toBeEmpty();
});

it('enforces the primary payment foreign key', function (): void {
    expect(fn () => Order::factory()->create(['primary_payment_id' => 999999999]))
        ->toThrow(QueryException::class, 'foreign key constraint');
});

it('stores one webhook per provider event per gateway', function (): void {
    $gateway = PaymentGateway::factory()->create();

    PaymentWebhook::factory()->create(['payment_gateway_id' => $gateway->id, 'provider_event_id' => 'evt_1']);

    expect(fn () => PaymentWebhook::factory()->create(['payment_gateway_id' => $gateway->id, 'provider_event_id' => 'evt_1']))
        ->toThrow(QueryException::class, 'uq_pwh');
});

it('allows the same event id on a different gateway', function (): void {
    PaymentWebhook::factory()->create(['provider_event_id' => 'evt_1']);

    expect(PaymentWebhook::factory()->create(['provider_event_id' => 'evt_1'])->exists)->toBeTrue();
});

it('rejects a duplicate digital code hash', function (): void {
    DigitalCode::factory()->create(['code_hash' => str_repeat('a', 64)]);

    expect(fn () => DigitalCode::factory()->create(['code_hash' => str_repeat('a', 64)]))
        ->toThrow(QueryException::class, 'uq_dc_hash');
});

it('uses decimal 20,6 for money and never a float column', function (): void {
    $schema = DB::getDatabaseName();

    $floatColumns = DB::table('information_schema.columns')
        ->where('table_schema', $schema)
        ->whereIn('data_type', ['float', 'double'])
        ->count();

    $column = DB::table('information_schema.columns')
        ->where('table_schema', $schema)
        ->where('table_name', 'orders')
        ->where('column_name', 'total_base')
        ->first(['numeric_precision as precision', 'numeric_scale as scale', 'data_type as type']);

    expect($floatColumns)->toBe(0)
        ->and($column->type)->toBe('decimal')
        ->and((int) $column->precision)->toBe(20)
        ->and((int) $column->scale)->toBe(6);
});

it('keeps order status and totals out of mass assignment', function (): void {
    $order = new Order;

    foreach (['status', 'user_id', 'total_base', 'payment_total', 'primary_payment_id', 'paid_at'] as $attribute) {
        expect($order->isFillable($attribute))->toBeFalse();
    }
});

it('never serializes digital code ciphertext', function (): void {
    expect(DigitalCode::factory()->create()->toArray())
        ->not->toHaveKeys(['code_ciphertext', 'pin_ciphertext', 'redemption_url_ciphertext', 'code_hash']);
});
