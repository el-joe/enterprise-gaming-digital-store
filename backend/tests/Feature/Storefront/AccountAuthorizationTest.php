<?php

/**
 * M13 object-level authorization: another customer's order, code, ticket, review, device,
 * notification or wishlist never shows on an account page, and every page and action on it is
 * refused (404, so its existence is not revealed). The owner is allowed, as the control.
 */

use App\Domains\Catalog\Models\WishlistItem;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Reviews\Models\Review;
use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Models\SupportTicketMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    storefrontWithoutBuild();
    Queue::fake([FulfilOrder::class]);
    $this->owner = User::factory()->create(['email_verified_at' => now()]);
    $this->intruder = User::factory()->create(['email_verified_at' => now()]);
    [$this->order, $this->unit] = accountPageDeliveredOrder($this->owner);
    $this->ticket = accountPageTicket($this->owner);
});

describe('pages', function (): void {
    it('is 404 for another customer\'s order detail, in both languages', function (string $prefix): void {
        $this->actingAs($this->owner)->get($prefix.'/account/orders/'.$this->order->public_id)->assertOk();
        $this->actingAs($this->intruder)->get($prefix.'/account/orders/'.$this->order->public_id)->assertNotFound()->assertDontSee($this->order->order_number);
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('is 404 for a guest order in the account, even for the guest email\'s customer', function (): void {
        $guestOrder = paidOrderFor(storefrontVariant(storefrontProduct()), orderAttributes: ['user_id' => null, 'guest_email' => $this->owner->email]);

        $this->actingAs($this->owner)->get('/account/orders/'.$guestOrder->public_id)->assertNotFound();
    });

    it('is 404 for another customer\'s ticket, in both languages, without its messages', function (string $prefix): void {
        $this->actingAs($this->owner)->get($prefix.'/account/tickets/'.$this->ticket->public_id)->assertOk();
        $this->actingAs($this->intruder)->get($prefix.'/account/tickets/'.$this->ticket->public_id)->assertNotFound()->assertDontSee('My code says invalid.');
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('never lists another customer\'s orders, codes, tickets, reviews, wishlist, wallet, notifications or devices', function (): void {
        $item = $this->order->orderItems()->sole();
        $review = Review::factory()->create(['user_id' => $this->owner->id, 'product_id' => $item->product_id, 'order_item_id' => $item->id, 'title' => 'OWNER-REVIEW-TITLE']);
        WishlistItem::query()->forceCreate(['user_id' => $this->owner->id, 'product_id' => $item->product_id]);
        fundWallet($this->owner, '77.70');
        $this->owner->notify(new OrderDelivered($this->order->id));
        config(['session.driver' => 'database']);
        DB::table('sessions')->insert(['id' => str_repeat('o', 40), 'user_id' => $this->owner->id, 'ip_address' => '203.0.113.7', 'user_agent' => 'OWNER-BROWSER-AGENT', 'payload' => '', 'last_activity' => now()->getTimestamp()]);

        $pages = [
            '/account' => [$this->order->order_number],
            '/account/orders' => [$this->order->order_number],
            '/account/codes' => [$this->order->order_number, $this->unit->public_id],
            '/account/tickets' => [$this->ticket->subject],
            '/account/reviews' => [$review->title, 'value="'.$item->id.'"'],
            '/account/wishlist' => ['Steam Wallet $20'],
            '/account/wallet' => [storefrontPrice('77.70')],
            '/account/notifications' => [$this->order->public_id],
            '/account/security' => ['OWNER-BROWSER-AGENT', 'Steam Wallet $20'],
        ];

        foreach ($pages as $page => $secrets) {
            $response = $this->actingAs($this->intruder)->get($page)->assertOk();

            foreach ($secrets as $secret) {
                $response->assertDontSee($secret, false);
            }
        }

        $this->actingAs($this->owner)->get('/account/reviews')->assertSee('OWNER-REVIEW-TITLE');
    });
});

describe('actions', function (): void {
    it('refuses to reveal another customer\'s code, even after the intruder confirmed their password', function (): void {
        $url = "/api/v1/account/orders/{$this->order->public_id}/units/{$this->unit->public_id}/reveal";

        $this->actingAs($this->intruder)->postJson('/api/v1/account/confirm-password', ['password' => 'password'])->assertOk();
        $this->actingAs($this->intruder)->postJson($url)->assertNotFound()->assertDontSee(ACCOUNT_PAGE_CANARY_CODE);

        $this->unit->refresh();
        expect($this->unit->first_revealed_at)->toBeNull();
    });

    it('refuses to reply to another customer\'s ticket', function (): void {
        $before = SupportTicketMessage::query()->count();

        $this->actingAs($this->intruder)->postJson("/api/v1/account/tickets/{$this->ticket->public_id}/messages", ['body' => 'Hello'])->assertNotFound();

        expect(SupportTicketMessage::query()->count())->toBe($before);
    });

    it('refuses to edit another customer\'s review', function (): void {
        $item = $this->order->orderItems()->sole();
        $review = Review::factory()->create(['user_id' => $this->owner->id, 'product_id' => $item->product_id, 'order_item_id' => $item->id, 'rating' => 5, 'title' => 'Great']);

        $this->actingAs($this->intruder)->patchJson("/api/v1/account/reviews/{$review->id}", ['rating' => 1, 'title' => 'Hacked'])->assertNotFound();

        expect($review->fresh()->only(['rating', 'title']))->toBe(['rating' => 5, 'title' => 'Great']);
    });

    it('refuses to sign out another customer\'s device', function (): void {
        $family = mobileSignIn($this->owner)['family_id'] ?? MobileRefreshToken::query()->where('user_id', $this->owner->id)->value('family_id');

        $this->actingAs($this->intruder)->deleteJson("/api/v1/account/devices/{$family}")->assertNotFound();

        expect(MobileRefreshToken::query()->where('family_id', $family)->whereNull('revoked_at')->exists())->toBeTrue();
    });

    it('refuses to mark another customer\'s notification read', function (): void {
        $this->owner->notify(new OrderDelivered($this->order->id));
        $notification = $this->owner->notifications()->sole();

        $this->actingAs($this->intruder)->postJson("/api/v1/account/notifications/{$notification->id}/read")->assertNotFound();

        expect($notification->fresh()->read_at)->toBeNull();
    });

    it('refuses a payment retry on another customer\'s order', function (): void {
        $unpaid = paidOrderFor(storefrontVariant(storefrontProduct('Unpaid Card')), orderAttributes: ['user_id' => $this->owner->id, 'paid_at' => null, 'primary_payment_id' => null], status: OrderStatus::PendingPayment);

        $this->actingAs($this->intruder)->postJson("/api/v1/checkout/orders/{$unpaid->public_id}/retry-payment", ['client' => 'web'])->assertNotFound();
    });

    it('refuses refund requests and tickets about another customer\'s order without creating anything', function (): void {
        $this->actingAs($this->intruder)->postJson('/api/v1/account/refund-requests', ['order_public_id' => $this->order->public_id, 'reason' => 'Not mine but trying anyway.'])
            ->assertUnprocessable()->assertJsonPath('errors.order_public_id.0', 'This order was not found.');
        $this->actingAs($this->intruder)->postJson('/api/v1/account/tickets', ['category' => 'order', 'subject' => 'About this', 'body' => 'Hi', 'order_public_id' => $this->order->public_id])
            ->assertUnprocessable()->assertJsonPath('errors.order_public_id.0', 'This order was not found.');

        expect(RefundRequest::query()->count())->toBe(0);
    });

    it('only ever changes the caller\'s own wishlist', function (): void {
        $item = $this->order->orderItems()->sole();
        WishlistItem::query()->forceCreate(['user_id' => $this->owner->id, 'product_id' => $item->product_id]);
        $slug = $item->product->slug;

        $this->actingAs($this->intruder)->deleteJson("/api/v1/account/wishlist/{$slug}")->assertOk();
        $this->actingAs($this->intruder)->getJson('/api/v1/account/wishlist')->assertOk()->assertJsonCount(0, 'data.items');

        expect(WishlistItem::query()->where('user_id', $this->owner->id)->count())->toBe(1);
    });
});
