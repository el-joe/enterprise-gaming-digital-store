<?php

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentDispute;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Payments\Models\Refund;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierApiLog;
use App\Domains\Suppliers\Models\SupplierCredential;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\DB;

/*
| M14: every admin table is server-side through Yajra: paging, search, filters, sort by
| allow-listed columns, only declared columns in the response, and no N+1 queries.
*/

beforeEach(function (): void {
    CurrencyFactory::ensure('USD');
});

/**
 * The query count of one table request, after a warm-up request has loaded the per-process
 * caches (permissions, currency registry).
 */
function tableQueryCount(string $key, array $overrides = []): int
{
    postTable($key, $overrides)->assertOk();
    DB::flushQueryLog();
    DB::enableQueryLog();
    postTable($key, $overrides)->assertOk();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

describe('orders table', function (): void {
    it('pages on the server and reports total and filtered counts', function (): void {
        adminWith(['orders.view']);
        Order::factory()->count(30)->create(['display_currency' => 'USD', 'payment_currency' => 'USD']);

        $response = postTable('orders', ['start' => 25, 'length' => 25, 'draw' => 3]);

        $response->assertOk()
            ->assertJsonPath('draw', 3)
            ->assertJsonPath('recordsTotal', 30)
            ->assertJsonPath('recordsFiltered', 30)
            ->assertJsonCount(5, 'data');
    });

    it('searches by order number prefix and filters by status, gateway and date', function (): void {
        adminWith(['orders.view']);
        $gateway = PaymentGateway::factory()->create();
        $match = Order::factory()->create(['order_number' => 'BX-777001', 'status' => 'manual_review', 'payment_gateway_id' => $gateway->id]);
        Order::factory()->create(['order_number' => 'BX-123456', 'status' => 'completed']);
        Order::factory()->create(['order_number' => 'BX-777002', 'status' => 'completed', 'created_at' => now()->subDays(10)]);

        postTable('orders', ['search' => ['value' => 'BX-777']])->assertJsonPath('recordsFiltered', 2)->assertJsonPath('recordsTotal', 3);
        postTable('orders', ['filters' => ['status' => 'manual_review']])->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.DT_RowId', $match->public_id);
        postTable('orders', ['filters' => ['attention' => '1']])->assertJsonPath('recordsFiltered', 1);
        postTable('orders', ['filters' => ['gateway' => (string) $gateway->id]])->assertJsonPath('recordsFiltered', 1);
        postTable('orders', ['filters' => ['from' => now()->subDay()->toDateString()]])->assertJsonPath('recordsFiltered', 2);
    });

    it('treats search text as a literal prefix, never as a pattern', function (): void {
        adminWith(['orders.view']);
        Order::factory()->create(['order_number' => 'BX-100']);

        postTable('orders', ['search' => ['value' => '%']])->assertJsonPath('recordsFiltered', 0);
        postTable('orders', ['search' => ['value' => '_X']])->assertJsonPath('recordsFiltered', 0);
    });

    it('sorts by an allow-listed column and ignores client column names', function (): void {
        adminWith(['orders.view']);
        Order::factory()->create(['order_number' => 'BX-B']);
        Order::factory()->create(['order_number' => 'BX-A']);

        $asc = postTable('orders', ['order' => [['column' => 0, 'dir' => 'asc']], 'columns' => [['data' => 'ip_address', 'name' => 'ip_address', 'searchable' => true, 'search' => ['value' => 'x']]]]);

        expect($asc->json('data.0.number'))->toContain('BX-A')
            ->and($asc->json('recordsFiltered'))->toBe(2);
        expect(postTable('orders', ['order' => [['column' => 0, 'dir' => 'desc']]])->json('data.0.number'))->toContain('BX-B');
    });

    it('returns only the declared columns, escaped, with the email masked without customers.view_pii', function (): void {
        adminWith(['orders.view']);
        Order::factory()->create(['customer_name' => '<b>Sara</b>', 'contact_email' => 'sara.ali@example.com', 'ip_address' => '203.0.113.9']);

        $row = postTable('orders')->json('data.0');

        expect(array_keys($row))->toEqualCanonicalizing(['number', 'customer', 'status', 'total', 'charged', 'gateway', 'risk', 'country', 'placed', 'DT_RowId'])
            ->and(json_encode($row))->not->toContain('203.0.113.9')
            ->and($row['customer'])->toContain('&lt;b&gt;Sara&lt;/b&gt;')->not->toContain('<b>')
            ->and($row['customer'])->not->toContain('sara.ali@example.com');
    });

    it('shows and searches the full email with customers.view_pii', function (): void {
        adminWith(['orders.view', 'customers.view_pii']);
        Order::factory()->create(['contact_email' => 'sara.ali@example.com']);

        postTable('orders', ['search' => ['value' => 'sara.ali@']])->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.customer', fn (string $html): bool => str_contains($html, 'sara.ali@example.com'));
    });

    it('rejects filter values outside the declared options in the envelope', function (): void {
        adminWith(['orders.view']);

        postTable('orders', ['filters' => ['status' => 'hacked', 'from' => '2026-13-40']])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonValidationErrors(['filters.status', 'filters.from']);
    });

    it('rejects a page length that is not offered and more than three sort instructions', function (): void {
        adminWith(['orders.view']);

        postTable('orders', ['length' => 5000])->assertUnprocessable()->assertJsonValidationErrors('length');
        postTable('orders', ['length' => -1])->assertUnprocessable()->assertJsonValidationErrors('length');
        postTable('orders', ['order' => array_fill(0, 4, ['column' => 0, 'dir' => 'asc'])])->assertUnprocessable()->assertJsonValidationErrors('order');
    });

    it('runs the same number of queries for one row and for a full page', function (): void {
        adminWith(['orders.view']);
        $gateway = PaymentGateway::factory()->create();
        Order::factory()->create(['payment_gateway_id' => $gateway->id]);
        $one = tableQueryCount('orders');

        Order::factory()->count(20)->create(['payment_gateway_id' => PaymentGateway::factory()]);

        expect(tableQueryCount('orders'))->toBe($one);
    });
});

describe('supplier tables', function (): void {
    it('lists suppliers with credentials as configured or not, never the values', function (): void {
        adminWith(['suppliers.view']);
        $configured = Supplier::factory()->create(['name' => 'Reloadly Gift Cards', 'mode' => 'sandbox', 'priority' => 1]);
        SupplierCredential::factory()->create(['supplier_id' => $configured->id, 'mode' => 'sandbox', 'credentials' => ['client_id' => 'cid', 'client_secret' => ADMIN_CREDENTIAL_CANARY]]);
        Supplier::factory()->create(['name' => 'Internal Stock', 'priority' => 2]);

        $response = postTable('suppliers');

        $response->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('data.0.DT_RowId', (string) $configured->id);
        expect($response->json('data.0.credentials'))->toContain('Configured')
            ->and($response->json('data.1.credentials'))->toContain('Not configured')
            ->and($response->getContent())->not->toContain(ADMIN_CREDENTIAL_CANARY)->not->toContain('cid');

        postTable('suppliers', ['search' => ['value' => 'Inter']])->assertJsonPath('recordsFiltered', 1);
        postTable('suppliers', ['filters' => ['health' => 'nope']])->assertUnprocessable()->assertJsonValidationErrors('filters.health');
    });

    it('scopes logs and mappings to the supplier given as context, which is required', function (string $key, string $permission): void {
        adminWith([$permission]);
        $supplier = Supplier::factory()->create();
        $other = Supplier::factory()->create();

        if ($key === 'supplier-logs') {
            SupplierApiLog::factory()->count(3)->create(['supplier_id' => $supplier->id, 'url' => 'https://giftcards-sandbox.reloadly.com/orders?token=secret']);
            SupplierApiLog::factory()->create(['supplier_id' => $other->id]);
        } else {
            SupplierProductMapping::factory()->count(3)->create(['supplier_id' => $supplier->id]);
            SupplierProductMapping::factory()->create(['supplier_id' => $other->id]);
        }

        postTable($key)->assertUnprocessable()->assertJsonValidationErrors('filters.supplier');

        $response = postTable($key, ['filters' => ['supplier' => (string) $supplier->id]]);
        $response->assertOk()->assertJsonPath('recordsTotal', 3)->assertJsonCount(3, 'data');
        expect($response->getContent())->not->toContain('token=secret')->not->toContain('request_redacted');
    })->with([
        ['supplier-logs', 'suppliers.logs'],
        ['supplier-mappings', 'suppliers.view'],
    ]);

    it('runs a constant number of queries for supplier pages', function (string $key, string $permission): void {
        adminWith([$permission]);
        $supplier = Supplier::factory()->create();
        $make = fn (int $count) => $key === 'supplier-logs'
            ? SupplierApiLog::factory()->count($count)->create(['supplier_id' => $supplier->id])
            : SupplierProductMapping::factory()->count($count)->create(['supplier_id' => $supplier->id]);

        $make(1);
        $one = tableQueryCount($key, ['filters' => ['supplier' => (string) $supplier->id]]);
        $make(10);

        expect(tableQueryCount($key, ['filters' => ['supplier' => (string) $supplier->id]]))->toBe($one);
    })->with([
        ['supplier-logs', 'suppliers.logs'],
        ['supplier-mappings', 'suppliers.view'],
        ['suppliers', 'suppliers.view'],
    ]);
});

describe('payment tables', function (): void {
    it('pages, searches and filters each payment table on the server', function (string $key, string $permission, Closure $seed, array $search, array $filter): void {
        adminWith([$permission]);
        $seed();

        $all = postTable($key);
        $all->assertOk()->assertJsonPath('recordsTotal', 3)->assertJsonCount(3, 'data');
        postTable($key, ['length' => 10, 'start' => 2])->assertJsonCount(1, 'data');

        if ($search !== []) {
            postTable($key, ['search' => ['value' => $search[0]]])->assertJsonPath('recordsFiltered', $search[1]);
        }

        postTable($key, ['filters' => $filter[0]])->assertJsonPath('recordsFiltered', $filter[1]);
    })->with([
        'transactions' => ['payments', 'payments.view', function (): void {
            Payment::factory()->create(['provider_payment_id' => 'pi_ALPHA_1', 'status' => 'failed', 'currency' => 'USD']);
            Payment::factory()->count(2)->create(['status' => 'succeeded', 'currency' => 'USD']);
        }, ['pi_ALPHA', 1], [['status' => 'failed'], 1]],
        'webhooks' => ['webhooks', 'payments.webhook_logs', function (): void {
            PaymentWebhook::factory()->create(['event_type' => 'charge.dispute.created', 'signature_valid' => false, 'status' => 'failed']);
            PaymentWebhook::factory()->count(2)->create(['event_type' => 'payment_intent.succeeded', 'signature_valid' => true, 'status' => 'processed']);
        }, ['charge.', 1], [['signature' => 'invalid'], 1]],
        'refunds' => ['refunds', 'refunds.view', function (): void {
            Refund::factory()->create(['status' => 'failed', 'reason' => 'fraud', 'currency' => 'USD']);
            Refund::factory()->count(2)->create(['status' => 'succeeded', 'reason' => 'undelivered', 'currency' => 'USD']);
        }, [], [['reason' => 'fraud'], 1]],
        'disputes' => ['disputes', 'payments.view', function (): void {
            PaymentDispute::factory()->create(['provider_dispute_id' => 'dp_FIND', 'status' => 'lost', 'currency' => 'USD']);
            PaymentDispute::factory()->count(2)->create(['status' => 'open', 'currency' => 'USD']);
        }, ['dp_FIND', 1], [['status' => 'open'], 2]],
        'reconciliation' => ['reconciliation', 'payments.view', function (): void {
            ReconciliationItem::factory()->create(['type' => 'amount_mismatch', 'status' => 'open', 'provider_reference' => 'txn_FIND']);
            ReconciliationItem::factory()->count(2)->create(['type' => 'status_mismatch', 'status' => 'resolved']);
        }, ['txn_FIND', 1], [['type' => 'amount_mismatch'], 1]],
    ]);

    it('never returns webhook payloads or headers', function (): void {
        adminWith(['payments.webhook_logs']);
        PaymentWebhook::factory()->create(['payload' => '{"secret":"'.ADMIN_CREDENTIAL_CANARY.'"}', 'headers' => ['stripe-signature' => ADMIN_CREDENTIAL_CANARY]]);

        expect(postTable('webhooks')->getContent())->not->toContain(ADMIN_CREDENTIAL_CANARY);
    });

    it('runs a constant number of queries per payment table', function (string $key, string $permission, string $model): void {
        adminWith([$permission]);
        $model::factory()->create(['currency' => 'USD']);
        $one = tableQueryCount($key);
        $model::factory()->count(10)->create();

        expect(tableQueryCount($key))->toBe($one);
    })->with([
        ['payments', 'payments.view', Payment::class],
        ['refunds', 'refunds.view', Refund::class],
        ['disputes', 'payments.view', PaymentDispute::class],
    ]);

    it('runs a constant number of queries for webhooks and reconciliation', function (string $key, string $permission, string $model): void {
        adminWith([$permission]);
        $model::factory()->create();
        $one = tableQueryCount($key);
        $model::factory()->count(10)->create();

        expect(tableQueryCount($key))->toBe($one);
    })->with([
        ['webhooks', 'payments.webhook_logs', PaymentWebhook::class],
        ['reconciliation', 'payments.view', ReconciliationItem::class],
    ]);
});
