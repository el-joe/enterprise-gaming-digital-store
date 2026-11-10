<?php

/**
 * Customer automation (D workflows): consent and communication preferences are enforced by
 * Laravel for every customer-facing message; n8n only delivers what Laravel prepared and logs it.
 */

use App\Domains\Automation\Enums\CustomerMessageStatus;
use App\Domains\Automation\Models\AutomationCustomerMessage;
use App\Domains\Automation\Services\AutomationOutboxWriter;
use App\Domains\Catalog\Models\Product;
use App\Domains\Customers\Enums\CustomerConsentChannel;
use App\Domains\Customers\Models\CustomerConsent;
use App\Domains\Customers\Models\CustomerProfile;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use App\Domains\Orders\Models\Order;

beforeEach(function (): void {
    enableAutomationWorkflows();
    config(['automation.outbox.enabled' => true, 'automation.store_name' => 'PLAYVAULT', 'app.url' => 'https://shop.example.test']);
    ['token' => $this->token] = automationCredential();
});

function smsCustomer(array $attributes = []): User
{
    return User::factory()->create(['phone' => '+201000000001', ...$attributes]);
}

function smsPreference(User $user, NotificationPreferenceCategory $category, bool $enabled = true): void
{
    NotificationPreference::factory()->create(['user_id' => $user->id, 'category' => $category, 'channel' => NotificationPreferenceChannel::Sms, 'enabled' => $enabled]);
}

function smsConsent(User $user, bool $granted): void
{
    CustomerConsent::factory()->create(['user_id' => $user->id, 'channel' => CustomerConsentChannel::SmsMarketing, 'granted' => $granted]);
}

function abandonedCartOf(User $user, array $attributes = []): Cart
{
    $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active, 'last_activity_at' => now()->subHours(3), ...$attributes]);
    CartItem::factory()->create(['cart_id' => $cart->id]);

    return $cart;
}

function orderEvent(Order $order, string $type): string
{
    return app(AutomationOutboxWriter::class)->recordOrderStatus($order, $type === AutomationOutboxWriter::ORDER_CREATED ? null : OrderStatus::Processing, match ($type) {
        AutomationOutboxWriter::ORDER_CREATED => OrderStatus::PendingPayment,
        AutomationOutboxWriter::ORDER_DELIVERED => OrderStatus::Completed,
        default => OrderStatus::Failed,
    })->event_id;
}

it('prepares an order message only for a customer who opted in to SMS order updates', function (): void {
    $customer = smsCustomer();
    $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Completed]);
    $eventId = orderEvent($order, AutomationOutboxWriter::ORDER_DELIVERED);
    $options = ['workflow' => 'D03', 'key' => "D03:{$eventId}"];

    automationPost($this->token, 'customers/messages/prepare', ['event_id' => $eventId], $options)
        ->assertOk()
        ->assertJsonPath('data.eligible', false)
        ->assertJsonPath('data.reason', 'channel_not_enabled_by_customer');

    smsPreference($customer, NotificationPreferenceCategory::Orders);

    $response = automationPost($this->token, 'customers/messages/prepare', ['event_id' => $eventId], $options)
        ->assertOk()
        ->assertJsonPath('data.eligible', true)
        ->assertJsonPath('data.channel', 'sms')
        ->assertJsonPath('data.dedupe_key', "D03:{$eventId}")
        ->assertJsonPath('data.recipient.customer_id', $customer->public_id)
        ->assertJsonPath('data.recipient.phone', '+201000000001');

    expect($response->json('data.message.text'))->toContain($order->order_number)->toContain("https://shop.example.test/account/orders/{$order->public_id}");
});

it('never prepares a message for a guest, a disabled preference, a missing phone or a closed account', function (Closure $setup, string $reason): void {
    $order = $setup();
    $eventId = orderEvent($order, AutomationOutboxWriter::ORDER_CREATED);

    automationPost($this->token, 'customers/messages/prepare', ['event_id' => $eventId], ['workflow' => 'D01', 'key' => "D01:{$eventId}"])
        ->assertOk()
        ->assertJsonPath('data.eligible', false)
        ->assertJsonPath('data.reason', $reason)
        ->assertJsonMissingPath('data.recipient');
})->with([
    'guest' => [fn (): Order => Order::factory()->create(), 'guest_has_no_preferences'],
    'preference disabled' => [function (): Order {
        $customer = smsCustomer();
        smsPreference($customer, NotificationPreferenceCategory::Orders, enabled: false);

        return Order::factory()->create(['user_id' => $customer->id]);
    }, 'channel_not_enabled_by_customer'],
    'only a marketing consent' => [function (): Order {
        $customer = smsCustomer();
        smsConsent($customer, true);

        return Order::factory()->create(['user_id' => $customer->id]);
    }, 'channel_not_enabled_by_customer'],
    'no phone' => [function (): Order {
        $customer = smsCustomer(['phone' => null]);
        smsPreference($customer, NotificationPreferenceCategory::Orders);

        return Order::factory()->create(['user_id' => $customer->id]);
    }, 'no_phone_number'],
    'suspended account' => [function (): Order {
        $customer = smsCustomer(['status' => UserStatus::Suspended]);
        smsPreference($customer, NotificationPreferenceCategory::Orders);

        return Order::factory()->create(['user_id' => $customer->id]);
    }, 'account_not_active'],
]);

it('refuses an event of another type and reports one already sent', function (): void {
    $customer = smsCustomer();
    smsPreference($customer, NotificationPreferenceCategory::Orders);
    $eventId = orderEvent(Order::factory()->create(['user_id' => $customer->id]), AutomationOutboxWriter::ORDER_FAILED);

    automationPost($this->token, 'customers/messages/prepare', ['event_id' => $eventId], ['workflow' => 'D02', 'key' => "D02:{$eventId}"])
        ->assertUnprocessable()
        ->assertJson(['code' => 'event_type_mismatch']);

    automationPost($this->token, 'customers/messages/log', ['dedupe_key' => "D04:{$eventId}", 'customer_id' => $customer->public_id, 'status' => 'sent', 'provider_message_id' => 'SM123'], ['workflow' => 'D04', 'key' => "D04:{$eventId}"])
        ->assertCreated();

    automationPost($this->token, 'customers/messages/prepare', ['event_id' => $eventId], ['workflow' => 'D04', 'key' => "D04:{$eventId}"])
        ->assertJsonPath('data.eligible', false)
        ->assertJsonPath('data.reason', 'already_sent');
});

it('includes only customers whose latest SMS marketing consent is a grant', function (): void {
    $granted = smsCustomer();
    smsConsent($granted, true);
    $revoked = smsCustomer();
    smsConsent($revoked, true);
    smsConsent($revoked, false);
    $never = smsCustomer();
    $emailOnly = smsCustomer();
    CustomerConsent::factory()->create(['user_id' => $emailOnly->id, 'channel' => CustomerConsentChannel::EmailMarketing, 'granted' => true]);
    $optedOut = smsCustomer();
    smsConsent($optedOut, true);
    smsPreference($optedOut, NotificationPreferenceCategory::Marketing, enabled: false);
    $noPhone = smsCustomer(['phone' => '']);
    smsConsent($noPhone, true);
    $staff = User::factory()->staff()->create(['phone' => '+201000000009']);
    smsConsent($staff, true);

    $carts = collect([$granted, $revoked, $never, $emailOnly, $optedOut, $noPhone, $staff])->mapWithKeys(fn (User $user): array => [$user->id => abandonedCartOf($user)]);

    $response = automationGet($this->token, 'customers/segments/abandoned', ['workflow' => 'D06', 'key' => 'D06:2026-11-10T10'])->assertOk();

    expect($response->json('data.recipients'))->toHaveCount(1)
        ->and($response->json('data.recipients.0.customer_id'))->toBe($granted->public_id)
        ->and($response->json('data.recipients.0.dedupe_key'))->toBe('D06:cart:'.$carts[$granted->id]->public_id)
        ->and($response->json('data.recipients.0.message.text'))->toContain('https://shop.example.test/cart');
});

it('excludes carts outside the abandonment window, empty carts and carts already reminded', function (): void {
    $customer = smsCustomer();
    smsConsent($customer, true);
    abandonedCartOf($customer, ['last_activity_at' => now()->subMinutes(10)]);
    abandonedCartOf($customer, ['last_activity_at' => now()->subDays(5)]);
    Cart::factory()->create(['user_id' => $customer->id, 'last_activity_at' => now()->subHours(3)]);
    $remindable = abandonedCartOf($customer);

    $options = ['workflow' => 'D06', 'key' => 'D06:2026-11-10T10'];
    $first = automationGet($this->token, 'customers/segments/abandoned', $options)->assertOk();
    expect(collect($first->json('data.recipients'))->pluck('dedupe_key')->all())->toBe(["D06:cart:{$remindable->public_id}"]);

    automationPost($this->token, 'customers/messages/log', ['dedupe_key' => "D06:cart:{$remindable->public_id}", 'customer_id' => $customer->public_id, 'status' => 'sent'], $options)->assertCreated();

    expect($remindable->refresh()->reminder_sent_at)->not->toBeNull();
    automationGet($this->token, 'customers/segments/abandoned', $options)->assertOk()->assertJsonCount(0, 'data.recipients');
});

it('applies the same consent rule to review, reactivation and recommendation segments', function (string $segment, string $workflow): void {
    $customer = smsCustomer();
    $order = paidOrderFor(sellableVariant('3'), orderAttributes: ['user_id' => $customer->id, 'completed_at' => now()->subDays(5)], status: OrderStatus::Completed);
    CustomerProfile::factory()->create(['user_id' => $customer->id, 'last_order_at' => now()->subDays(90)]);
    $other = smsCustomer();
    $popular = sellableVariant('3');
    $popular->product->forceFill(['gaming_platform_id' => Product::query()->whereKey($order->orderItems()->value('product_id'))->value('gaming_platform_id')])->save();
    paidOrderFor($popular, orderAttributes: ['user_id' => $other->id, 'paid_at' => now()->subDays(2)], status: OrderStatus::Completed);
    $options = ['workflow' => $workflow, 'key' => "{$workflow}:2026-11-10"];

    automationGet($this->token, "customers/segments/{$segment}", $options)->assertOk()->assertJsonCount(0, 'data.recipients');

    smsConsent($customer, true);

    $recipients = automationGet($this->token, "customers/segments/{$segment}", $options)->assertOk()->json('data.recipients');
    expect(collect($recipients)->pluck('customer_id')->unique()->all())->toBe([$customer->public_id])
        ->and($recipients[0]['dedupe_key'])->toStartWith("{$workflow}:");
})->with([
    'review requests' => ['review-eligible', 'D07'],
    'reactivation' => ['reactivation', 'D08'],
    'recommendations' => ['recommendations', 'D09'],
]);

it('lets each workflow read only its own segment', function (): void {
    automationGet($this->token, 'customers/segments/abandoned', ['workflow' => 'D07'])->assertForbidden();
    automationGet($this->token, 'customers/segments/review-eligible', ['workflow' => 'A01'])->assertForbidden();
});

it('logs a message idempotently and never overwrites a sent one', function (): void {
    $customer = smsCustomer();
    $options = ['workflow' => 'D08', 'key' => 'D08:2026-11-10'];
    $key = "D08:reactivation:{$customer->public_id}:2026-11";

    automationPost($this->token, 'customers/messages/log', ['dedupe_key' => $key, 'customer_id' => $customer->public_id, 'status' => 'failed', 'error' => 'provider timeout'], $options)->assertCreated();
    automationPost($this->token, 'customers/messages/log', ['dedupe_key' => $key, 'customer_id' => $customer->public_id, 'status' => 'sent', 'provider_message_id' => 'SM1'], $options)->assertCreated();
    automationPost($this->token, 'customers/messages/log', ['dedupe_key' => $key, 'customer_id' => $customer->public_id, 'status' => 'failed'], $options)
        ->assertOk()
        ->assertJsonPath('data.repeated', true)
        ->assertJsonPath('data.status', 'sent');

    $message = AutomationCustomerMessage::query()->sole();
    expect($message->status)->toBe(CustomerMessageStatus::Sent)
        ->and($message->provider_message_id)->toBe('SM1')
        ->and($message->category)->toBe(NotificationPreferenceCategory::Marketing)
        ->and($message->channel)->toBe('sms');
});

it('refuses a log for another workflow, an unknown customer or a bad status', function (array $body, string $field): void {
    $customer = smsCustomer();

    automationPost($this->token, 'customers/messages/log', [
        'dedupe_key' => 'D08:reactivation:x',
        'customer_id' => $customer->public_id,
        'status' => 'sent',
        ...$body,
    ], ['workflow' => 'D08'])->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'key of another workflow' => [['dedupe_key' => 'D06:cart:x'], 'dedupe_key'],
    'unknown customer' => [['customer_id' => '01JAAAAAAAAAAAAAAAAAAAAAAA'], 'customer_id'],
    'unknown status' => [['status' => 'queued'], 'status'],
]);
