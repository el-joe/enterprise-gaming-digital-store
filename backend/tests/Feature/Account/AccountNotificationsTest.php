<?php

/**
 * M-A5 (G6, inbox part): the customer notification inbox and notification preferences, for the
 * web session and the mobile bearer token, and the `database` channel written by the real
 * customer notifications according to the preferences. Payloads never carry a code or PIN.
 * M-A18 adds the `push` cells (orders and support only).
 */

use App\Domains\Customers\Enums\CustomerConsentChannel;
use App\Domains\Customers\Models\CustomerConsent;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Listeners\SendFulfilmentNotifications;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Orders\Notifications\OrderFulfilmentFailed;
use App\Domains\Orders\Services\GuestOrderAccessService;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Notifications\SupportTicketReplied;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

const INBOX_CANARY_CODE = 'CANARY-INBX-0000-3333';

beforeEach(function (): void {
    $this->customer = User::factory()->create(['email_verified_at' => now()]);
});

/**
 * Deliver an order of this customer through the real listener (OrderStatusChanged to completed).
 */
function deliverAndNotify(User $customer, OrderStatus $to = OrderStatus::Completed): string
{
    $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $customer->id], status: $to);
    app(SendFulfilmentNotifications::class)->handle(new OrderStatusChanged($order->id, OrderStatus::Processing, $to));

    return $order->public_id;
}

function sentMail(): string
{
    return collect(app('mailer')->getSymfonyTransport()->messages())->map(static fn ($message): string => $message->toString())->implode("\n");
}

describe('database channel of the customer notifications', function (): void {
    it('writes the inbox and sends the email for delivery, partial delivery, failure and support replies', function (): void {
        $delivered = deliverAndNotify($this->customer);
        $partial = deliverAndNotify($this->customer, OrderStatus::PartiallyFulfilled);
        $failedOrder = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id]);
        $this->customer->notify(new OrderFulfilmentFailed($failedOrder->id));
        $ticket = SupportTicket::factory()->create(['user_id' => $this->customer->id]);
        $this->customer->notify(new SupportTicketReplied($ticket->id));

        $rows = DB::table('notifications')->where('notifiable_id', $this->customer->id)->orderBy('created_at')->get();

        expect($rows->pluck('type')->sort()->values()->all())->toBe(['order_delivered', 'order_fulfilment_failed', 'order_partially_delivered', 'support_ticket_replied'])
            ->and($rows->pluck('notifiable_type')->unique()->all())->toBe(['user'])
            ->and(json_decode($rows->firstWhere('type', 'order_delivered')->data, true))->toBe(['kind' => 'order_delivered', 'params' => ['number' => DB::table('orders')->where('public_id', $delivered)->value('order_number')], 'link' => ['type' => 'order', 'value' => $delivered]])
            ->and(json_decode($rows->firstWhere('type', 'support_ticket_replied')->data, true)['link'])->toBe(['type' => 'ticket', 'value' => $ticket->public_id])
            ->and(json_decode($rows->firstWhere('type', 'order_partially_delivered')->data, true)['link']['value'])->toBe($partial)
            ->and(substr_count(sentMail(), 'Subject:'))->toBe(4);
    });

    it('respects the preferences: no inbox row when the inbox is off, and the order email is always sent', function (): void {
        NotificationPreference::query()->forceCreate(['user_id' => $this->customer->id, 'category' => 'orders', 'channel' => 'database', 'enabled' => false]);
        NotificationPreference::query()->forceCreate(['user_id' => $this->customer->id, 'category' => 'support', 'channel' => 'mail', 'enabled' => false]);

        $order = deliverAndNotify($this->customer);
        $ticket = SupportTicket::factory()->create(['user_id' => $this->customer->id]);
        $this->customer->notify(new SupportTicketReplied($ticket->id));

        expect(DB::table('notifications')->pluck('type')->all())->toBe(['support_ticket_replied'])
            ->and(sentMail())->toContain(DB::table('orders')->where('public_id', $order)->value('order_number'))
            ->and(sentMail())->not->toContain($ticket->ticket_number);
    });

    it('sends guests email only: nothing is written to the inbox for an on-demand notifiable', function (): void {
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com', 'contact_email' => 'guest@example.com'], status: OrderStatus::Completed);
        app(SendFulfilmentNotifications::class)->handle(new OrderStatusChanged($order->id, OrderStatus::Processing, OrderStatus::Completed));
        app(GuestOrderAccessService::class)->sendCode($order->public_id, 'guest@example.com');

        expect(DB::table('notifications')->count())->toBe(0)
            ->and(sentMail())->toContain($order->order_number);
    });

    it('never puts a code or PIN in the inbox payloads or responses (canary)', function (string $client): void {
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id], status: OrderStatus::Completed);
        // deliveredUnitOf stores the PIN 9876 and a redemption URL on redeem.example.com.
        deliveredUnitOf($order, INBOX_CANARY_CODE);
        app(SendFulfilmentNotifications::class)->handle(new OrderStatusChanged($order->id, OrderStatus::Processing, OrderStatus::Completed));
        $this->customer->notify(new OrderDelivered($order->id));

        $sinks = [
            'notifications table' => DB::table('notifications')->get()->toJson(),
            'inbox response' => accountCall($client, $this->customer, 'GET', 'notifications')->assertOk()->getContent(),
            'mail' => sentMail(),
        ];

        expect(DB::table('notifications')->count())->toBe(2);

        foreach ($sinks as $sink => $content) {
            expect(str_contains($content, INBOX_CANARY_CODE))->toBeFalse("Canary code found in {$sink}")
                ->and(str_contains($content, '9876'))->toBeFalse("Canary PIN found in {$sink}")
                ->and(str_contains($content, 'redeem.example.com'))->toBeFalse("Redemption URL found in {$sink}");
        }
    })->with(accountClients());
});

describe('inbox', function (): void {
    it('lists newest first with localized text, a structured link and pagination, and filters unread', function (string $client): void {
        $first = deliverAndNotify($this->customer);
        $this->travel(1)->minute();
        $ticket = SupportTicket::factory()->create(['user_id' => $this->customer->id]);
        $this->customer->notify(new SupportTicketReplied($ticket->id));
        $number = DB::table('orders')->where('public_id', $first)->value('order_number');

        accountCall($client, $this->customer, 'GET', 'notifications?per_page=1')->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.unread_count', 2)
            ->assertJsonPath('data.0.type', 'support_ticket_replied')
            ->assertJsonPath('data.0.category', 'support')
            ->assertJsonPath('data.0.title', "Update on your support request {$ticket->ticket_number}")
            ->assertJsonPath('data.0.link.type', 'ticket')
            ->assertJsonPath('data.0.link.value', $ticket->public_id)
            ->assertJsonPath('data.0.read_at', null);

        $order = accountCall($client, $this->customer, 'GET', 'notifications?page=2&per_page=1', headers: ['Accept-Language' => 'ar'])->assertOk();

        expect($order->json('data.0.link'))->toBe(['type' => 'order', 'value' => $first, 'url' => rtrim((string) config('app.url'), '/')."/account/orders/{$first}"]);

        if ($client === 'mobile') {
            expect($order->json('data.0.title'))->toBe("الطلب {$number} جاهز");
        }

        $id = $order->json('data.0.id');
        accountCall($client, $this->customer, 'POST', "notifications/{$id}/read")->assertOk()->assertJsonPath('data.notification.id', $id);

        accountCall($client, $this->customer, 'GET', 'notifications?unread=1')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.type', 'support_ticket_replied')->assertJsonPath('meta.unread_count', 1);
        accountCall($client, $this->customer, 'GET', 'notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 1);
    })->with(accountClients());

    it('marks everything read', function (string $client): void {
        deliverAndNotify($this->customer);
        deliverAndNotify($this->customer);

        accountCall($client, $this->customer, 'POST', 'notifications/read-all')->assertOk()->assertJsonPath('data.marked_read', 2);
        accountCall($client, $this->customer, 'GET', 'notifications/unread-count')->assertOk()->assertJsonPath('data.unread_count', 0);
    })->with(accountClients());

    it('answers 404 when reading another customer\'s notification or an unknown id', function (string $client): void {
        $other = User::factory()->create();
        deliverAndNotify($other);
        $foreign = DB::table('notifications')->value('id');

        accountCall($client, $this->customer, 'POST', "notifications/{$foreign}/read")->assertNotFound();
        accountCall($client, $this->customer, 'POST', 'notifications/'.Str::uuid().'/read')->assertNotFound();

        expect(DB::table('notifications')->where('id', $foreign)->value('read_at'))->toBeNull();
        accountCall($client, $this->customer, 'GET', 'notifications')->assertOk()->assertJsonPath('meta.total', 0);
    })->with(accountClients());
});

describe('notification preferences', function (): void {
    it('shows the category x channel matrix with defaults and mandatory cells', function (string $client): void {
        CustomerConsent::query()->forceCreate(['user_id' => $this->customer->id, 'channel' => CustomerConsentChannel::EmailMarketing, 'granted' => true, 'source' => 'checkout']);

        $cells = collect(accountCall($client, $this->customer, 'GET', 'notification-preferences')->assertOk()->json('data.preferences'))
            ->mapWithKeys(static fn (array $cell): array => ["{$cell['category']}.{$cell['channel']}" => [$cell['enabled'], $cell['mandatory']]]);

        expect($cells->all())->toBe([
            'orders.mail' => [true, true], 'orders.database' => [true, false], 'orders.sms' => [false, false], 'orders.push' => [true, false],
            'support.mail' => [true, false], 'support.database' => [true, false], 'support.sms' => [false, false], 'support.push' => [true, false],
            'marketing.mail' => [true, false], 'marketing.database' => [true, false], 'marketing.sms' => [false, false],
            'security.mail' => [true, true], 'security.database' => [true, false], 'security.sms' => [false, false],
        ]);
    })->with(accountClients());

    it('saves changes, audits them and reads them back', function (string $client): void {
        accountCall($client, $this->customer, 'PATCH', 'notification-preferences', ['preferences' => [
            ['category' => 'orders', 'channel' => 'sms', 'enabled' => true],
            ['category' => 'support', 'channel' => 'mail', 'enabled' => false],
            ['category' => 'orders', 'channel' => 'mail', 'enabled' => true],
        ]])->assertOk();

        $cells = collect(accountCall($client, $this->customer, 'GET', 'notification-preferences')->json('data.preferences'))
            ->mapWithKeys(static fn (array $cell): array => ["{$cell['category']}.{$cell['channel']}" => $cell['enabled']]);

        expect($cells['orders.sms'])->toBeTrue()->and($cells['support.mail'])->toBeFalse()
            ->and(NotificationPreference::query()->where('user_id', $this->customer->id)->count())->toBe(3)
            ->and(DB::table('audit_logs')->where('action', 'account.notification_preferences_updated')->exists())->toBeTrue();
    })->with(accountClients());

    it('refuses to switch off a mandatory email and saves nothing of that request', function (string $client, string $category): void {
        accountCall($client, $this->customer, 'PATCH', 'notification-preferences', ['preferences' => [
            ['category' => 'support', 'channel' => 'mail', 'enabled' => false],
            ['category' => $category, 'channel' => 'mail', 'enabled' => false],
        ]])->assertUnprocessable()
            ->assertJsonPath('code', 'notification_preference_mandatory')
            ->assertJsonValidationErrors(['preferences.1.enabled']);

        expect(NotificationPreference::query()->count())->toBe(0);
    })->with(accountClients())->with(['orders', 'security']);

    it('has push for orders and support only, and refuses push for marketing or security (M-A18)', function (string $client, string $category): void {
        accountCall($client, $this->customer, 'PATCH', 'notification-preferences', ['preferences' => [
            ['category' => 'orders', 'channel' => 'push', 'enabled' => false],
            ['category' => $category, 'channel' => 'push', 'enabled' => true],
        ]])->assertUnprocessable()
            ->assertJsonPath('code', 'notification_preference_unavailable')
            ->assertJsonValidationErrors(['preferences.1.channel']);

        expect(NotificationPreference::query()->count())->toBe(0);

        accountCall($client, $this->customer, 'PATCH', 'notification-preferences', ['preferences' => [['category' => 'support', 'channel' => 'push', 'enabled' => false]]])
            ->assertOk()
            ->assertJsonFragment(['category' => 'support', 'channel' => 'push', 'enabled' => false, 'mandatory' => false]);
    })->with(accountClients())->with(['marketing', 'security']);

    it('validates the cells', function (string $client, array $body): void {
        accountCall($client, $this->customer, 'PATCH', 'notification-preferences', $body)->assertUnprocessable()->assertJsonPath('code', 'validation_failed');
    })->with(accountClients())->with([
        'empty' => [['preferences' => []]],
        'unknown category' => [['preferences' => [['category' => 'news', 'channel' => 'mail', 'enabled' => true]]]],
        'unknown channel' => [['preferences' => [['category' => 'orders', 'channel' => 'fax', 'enabled' => true]]]],
        'missing enabled' => [['preferences' => [['category' => 'orders', 'channel' => 'sms']]]],
    ]);
});
