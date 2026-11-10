<?php

/**
 * M-A5 (G4): order history and order detail for the web session and the mobile bearer token,
 * the guest order detail behind the session or `X-Guest-Order-Token`, and the reveal fed by the
 * unit ids of the detail. No code, PIN or redemption URL ever appears in these responses.
 */

use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Cms\Models\Media;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Services\DigitalCodeVault;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\GuestOrderTokenService;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Domains\Payments\Models\Payment;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use App\Http\Controllers\Account\ConfirmPasswordController;
use App\Http\Controllers\Storefront\GuestOrderCodeController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

const ORDER_CANARY_CODE = 'CANARY-ORDR-0000-7777';

const GUEST_ORDER_CANARY_CODE = 'CANARY-GST0-0000-5555';

beforeEach(function (): void {
    $this->customer = User::factory()->create(['email_verified_at' => now()]);
});

/**
 * A paid order of this customer with one delivered unit (canary code in the vault).
 */
function deliveredOrderOf(User $customer, array $attributes = []): Order
{
    $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $customer->id, ...$attributes]);
    deliveredUnitOf($order, ORDER_CANARY_CODE);

    return $order->fresh();
}

describe('order history', function (): void {
    it('lists only the customer\'s orders, newest first, with pagination and Money totals', function (string $client): void {
        $old = paidOrderFor(sellableVariant('9'), 2, orderAttributes: ['user_id' => $this->customer->id, 'created_at' => now()->subDays(3)]);
        $new = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id, 'created_at' => now()->subDay(), 'payment_total' => '20', 'payment_rate' => '1']);
        paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => User::factory()->create()->id]);
        paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com']);

        $response = accountCall($client, $this->customer, 'GET', 'orders?per_page=1')->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('data.0.public_id', $new->public_id)
            ->assertJsonPath('data.0.number', $new->order_number)
            ->assertJsonPath('data.0.status', 'paid')
            ->assertJsonPath('data.0.payment_status', 'paid')
            ->assertJsonPath('data.0.totals.display.amount', '20.00')
            ->assertJsonPath('data.0.totals.display.currency', 'USD')
            ->assertJsonPath('data.0.item_count', 1);

        expect($response->json('data.0.totals.payment.amount'))->toBe('20.00');

        accountCall($client, $this->customer, 'GET', 'orders?per_page=1&page=2')->assertOk()
            ->assertJsonPath('data.0.public_id', $old->public_id)
            ->assertJsonPath('data.0.totals.payment', null)
            ->assertJsonPath('data.0.item_count', 2);
    })->with(accountClients());

    it('filters by status and by placement date range', function (string $client): void {
        $paid = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id, 'created_at' => '2026-10-01 10:00:00']);
        $completed = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id, 'created_at' => '2026-10-15 23:30:00'], status: OrderStatus::Completed);

        accountCall($client, $this->customer, 'GET', 'orders?status=completed')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.public_id', $completed->public_id);

        accountCall($client, $this->customer, 'GET', 'orders?from=2026-10-15&to=2026-10-15')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.public_id', $completed->public_id);

        accountCall($client, $this->customer, 'GET', 'orders?to=2026-10-14')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.public_id', $paid->public_id);
    })->with(accountClients());

    it('refuses an unknown status, a malformed date and a range that ends before it starts', function (string $client, string $query, string $field): void {
        accountCall($client, $this->customer, 'GET', "orders?{$query}")->assertUnprocessable()->assertJsonValidationErrors([$field]);
    })->with(accountClients())->with([
        'status' => ['status=shipped', 'status'],
        'date' => ['from=10/01/2026', 'from'],
        'range' => ['from=2026-10-10&to=2026-10-01', 'to'],
        'page size' => ['per_page=51', 'per_page'],
    ]);

    it('returns an absolute thumbnail URL and keeps the query count fixed', function (string $client): void {
        foreach (range(1, 3) as $index) {
            $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id]);
            $productId = $order->orderItems()->value('product_id');
            ProductImage::factory()->create(['product_id' => $productId, 'media_id' => Media::factory()->create(['path' => "products/{$index}.webp"])->id, 'sort_order' => 0]);
        }

        accountCall($client, $this->customer, 'GET', 'orders')->assertOk();

        DB::enableQueryLog();
        $response = accountCall($client, $this->customer, 'GET', 'orders')->assertOk();
        $orderQueries = collect(DB::getQueryLog())->filter(static fn (array $query): bool => preg_match('/from `(orders|order_items|products|product_images|media)`/', $query['query']) === 1)->count();
        DB::disableQueryLog();

        expect($response->json('data.0.thumbnail_url'))->toStartWith('http')->toContain('products/')
            ->and($orderQueries)->toBeLessThanOrEqual(6);
    })->with(accountClients());
});

describe('order detail', function (): void {
    it('shows items, units with reveal eligibility, payments, refunds and the timeline, never the code', function (string $client): void {
        $order = paidOrderFor(sellableVariant('9'), 2, orderAttributes: ['user_id' => $this->customer->id]);
        [$delivered, $pending] = OrderItemUnit::query()->where('order_id', $order->id)->orderBy('id')->get()->all();
        $delivered->forceFill(['status' => OrderItemUnitStatus::Delivered])->save();
        app(DigitalCodeVault::class)->store(new DeliverableItem(ORDER_CANARY_CODE, '4455', 'https://redeem.example.com/'.ORDER_CANARY_CODE), DigitalCodeSource::Supplier, ['order_item_unit_id' => $delivered->id]);
        Payment::query()->whereKey($order->primary_payment_id)->update(['provider_payment_id' => 'pi_secret_provider_ref', 'provider_reference' => 'ref_provider_secret']);
        OrderEvent::factory()->create(['order_id' => $order->id, 'from_status' => OrderStatus::PendingPayment, 'to_status' => OrderStatus::Paid, 'message' => 'Internal staff note']);
        OrderEvent::factory()->create(['order_id' => $order->id, 'message' => 'Not a status change']);

        $response = accountCall($client, $this->customer, 'GET', "orders/{$order->public_id}")->assertOk()
            ->assertJsonPath('data.order.public_id', $order->public_id)
            ->assertJsonPath('data.order.items.0.quantity', 2)
            ->assertJsonPath('data.order.items.0.delivery_type', 'instant_code')
            ->assertJsonPath('data.order.items.0.units.0.public_id', $delivered->public_id)
            ->assertJsonPath('data.order.items.0.units.0.status', 'delivered')
            ->assertJsonPath('data.order.items.0.units.0.revealable', true)
            ->assertJsonPath('data.order.items.0.units.0.delivery_type', 'instant_code')
            ->assertJsonPath('data.order.items.0.units.0.first_revealed_at', null)
            ->assertJsonPath('data.order.items.0.units.1.public_id', $pending->public_id)
            ->assertJsonPath('data.order.items.0.units.1.revealable', false)
            ->assertJsonPath('data.order.items.0.units.1.masked_code', null)
            ->assertJsonPath('data.order.payments.0.status', 'succeeded')
            ->assertJsonPath('data.order.payments.0.amount.currency', 'USD')
            ->assertJsonPath('data.order.refunds', [])
            ->assertJsonPath('data.order.refund_state', 'none')
            ->assertJsonCount(1, 'data.order.timeline')
            ->assertJsonPath('data.order.timeline.0.status', 'paid');

        $json = $response->getContent();

        expect($response->json('data.order.items.0.units.0.masked_code'))->toBe(DigitalCodeVault::mask(ORDER_CANARY_CODE))
            ->and($json)->not->toContain(ORDER_CANARY_CODE)
            ->and($json)->not->toContain('4455')
            ->and($json)->not->toContain('redeem.example.com')
            ->and($json)->not->toContain('pi_secret_provider_ref')
            ->and($json)->not->toContain('ref_provider_secret')
            ->and($json)->not->toContain('Internal staff note')
            ->and($json)->not->toContain('pricing_breakdown')
            ->and($json)->not->toContain('estimated_unit_cost');
    })->with(accountClients());

    it('answers 404 for another customer\'s order, a guest order and an unknown id', function (string $client): void {
        $foreign = deliveredOrderOf(User::factory()->create());
        $guest = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com']);

        foreach ([$foreign->public_id, $guest->public_id, (string) Str::ulid()] as $publicId) {
            accountCall($client, $this->customer, 'GET', "orders/{$publicId}")->assertNotFound()->assertJsonPath('code', 'not_found');
        }
    })->with(accountClients());

    it('feeds the reveal: the unit id from the detail reveals after recent authentication', function (string $client): void {
        $order = deliveredOrderOf($this->customer);
        $unitId = accountCall($client, $this->customer, 'GET', "orders/{$order->public_id}")->assertOk()->json('data.order.items.0.units.0.public_id');

        if ($client === 'web') {
            $this->withSession([ConfirmPasswordController::SESSION_KEY => now()->getTimestamp()]);
        } else {
            accountCall($client, $this->customer, 'POST', 'confirm-password', ['password' => 'password'])->assertOk();
        }

        accountCall($client, $this->customer, 'POST', "orders/{$order->public_id}/units/{$unitId}/reveal")->assertOk()
            ->assertJsonPath('data.code', ORDER_CANARY_CODE);

        expect(accountCall($client, $this->customer, 'GET', "orders/{$order->public_id}")->json('data.order.items.0.units.0.first_revealed_at'))->not->toBeNull();
    })->with(accountClients());
});

describe('guest order detail', function (): void {
    beforeEach(function (): void {
        [$this->guestOrder] = deliveredGuestUnit(GUEST_ORDER_CANARY_CODE);
        $this->tokens = app(GuestOrderTokenService::class);
    });

    it('opens with the placement token and reports the reveal proofs it holds', function (): void {
        $token = $this->tokens->issueForPlacement($this->guestOrder->public_id)->token;

        $response = statelessCall('GET', "api/v1/mobile/checkout/guest/orders/{$this->guestOrder->public_id}", headers: ['X-Guest-Order-Token' => $token])->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.order.public_id', $this->guestOrder->public_id)
            ->assertJsonPath('data.order.items.0.units.0.revealable', true)
            ->assertJsonPath('meta.guest_access', ['link_verified' => false, 'otp_verified' => false]);

        expect($response->getContent())->not->toContain(GUEST_ORDER_CANARY_CODE);
    });

    it('reports both proofs for the token issued after the link and the one-time code', function (): void {
        $link = $this->tokens->recordLinkProof($this->guestOrder, null)->token;
        $otp = $this->tokens->issueAfterOtp($this->guestOrder, $link)->token;

        statelessCall('GET', "api/v1/mobile/checkout/guest/orders/{$this->guestOrder->public_id}", headers: ['X-Guest-Order-Token' => $otp])->assertOk()
            ->assertJsonPath('meta.guest_access', ['link_verified' => true, 'otp_verified' => true]);
    });

    it('answers 404 to no token, a link-only token, another order\'s token and an expired token', function (): void {
        $other = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => null, 'guest_email' => 'other@example.com']);
        $path = "api/v1/mobile/checkout/guest/orders/{$this->guestOrder->public_id}";

        statelessCall('GET', $path)->assertNotFound();
        statelessCall('GET', $path, headers: ['X-Guest-Order-Token' => $this->tokens->recordLinkProof($this->guestOrder, null)->token])->assertNotFound();
        statelessCall('GET', $path, headers: ['X-Guest-Order-Token' => $this->tokens->issueForPlacement($other->public_id)->token])->assertNotFound();

        $expiring = $this->tokens->issueForPlacement($this->guestOrder->public_id)->token;
        $this->travel((int) config('orders.guest_order_token_ttl_hours') + 1)->hours();
        statelessCall('GET', $path, headers: ['X-Guest-Order-Token' => $expiring])->assertNotFound();
    });

    it('never shows a customer\'s order on the guest route, even to its owner', function (): void {
        $order = deliveredOrderOf($this->customer);

        statelessCall('GET', "api/v1/mobile/checkout/guest/orders/{$order->public_id}", token: accountMobileToken($this->customer))->assertNotFound();
        $this->actingAs($this->customer)->withSession([OrderStatusReader::SESSION_KEY => [$order->public_id]])
            ->getJson("/api/v1/checkout/guest/orders/{$order->public_id}")->assertNotFound();
    });

    it('opens for the browser session that placed or verified the order, and for no other', function (): void {
        $path = "/api/v1/checkout/guest/orders/{$this->guestOrder->public_id}";

        $this->getJson($path)->assertNotFound();

        $this->withSession([
            OrderStatusReader::SESSION_KEY => [$this->guestOrder->public_id],
            GuestOrderCodeController::LINK_SESSION_KEY => [$this->guestOrder->public_id],
        ])->getJson($path)->assertOk()
            ->assertJsonPath('data.order.public_id', $this->guestOrder->public_id)
            ->assertJsonPath('meta.guest_access', ['link_verified' => true, 'otp_verified' => false]);
    });
});
