<?php

/**
 * JSON routes added for the M13 account pages, for the web session and the mobile bearer token:
 * edit a review (back to moderation), sign out every other device, and the wishlist.
 */

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\WishlistItem;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    $this->customer = User::factory()->create(['email_verified_at' => now()]);
});

describe('edit a review', function (): void {
    it('updates the customer\'s review and sends it back to moderation, taking it out of the rating', function (string $client): void {
        [$order] = accountPageDeliveredOrder($this->customer);
        $item = $order->orderItems()->sole();
        $review = Review::factory()->create(['user_id' => $this->customer->id, 'product_id' => $item->product_id, 'order_item_id' => $item->id, 'rating' => 5, 'title' => 'Great', 'status' => ReviewStatus::Approved, 'is_featured' => true]);
        $item->product->forceFill(['rating_avg' => 5, 'rating_count' => 1])->save();

        accountCall($client, $this->customer, 'PATCH', "reviews/{$review->id}", ['rating' => 3, 'title' => '<b>Okay</b>', 'body' => 'Took a while.'])->assertOk()
            ->assertJsonPath('data.review.rating', 3)
            ->assertJsonPath('data.review.title', 'Okay')
            ->assertJsonPath('data.review.status', 'pending');

        expect($review->fresh()->is_featured)->toBeFalse()
            ->and((int) $item->product->fresh()->rating_count)->toBe(0)
            ->and(DB::table('audit_logs')->where('action', 'reviews.review.revised')->exists())->toBeTrue();
    })->with(accountClients());

    it('validates the rating', function (): void {
        [$order] = accountPageDeliveredOrder($this->customer);
        $item = $order->orderItems()->sole();
        $review = Review::factory()->create(['user_id' => $this->customer->id, 'product_id' => $item->product_id, 'order_item_id' => $item->id]);

        $this->actingAs($this->customer)->patchJson("/api/v1/account/reviews/{$review->id}", ['rating' => 9])->assertUnprocessable()->assertJsonValidationErrors('rating');
    });

    it('is 404 for an unknown review and needs a session', function (): void {
        $this->actingAs($this->customer)->patchJson('/api/v1/account/reviews/999999', ['rating' => 4])->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->patchJson('/api/v1/account/reviews/1', ['rating' => 4])->assertUnauthorized();
    });
});

describe('sign out everywhere else', function (): void {
    it('needs the current password', function (): void {
        $this->actingAs($this->customer)->postJson('/api/v1/account/devices/sign-out-others', ['current_password' => 'nope'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
    });

    it('revokes every other app sign-in and browser session, keeps the caller, and rotates remember-me', function (): void {
        config(['session.driver' => 'database']);
        mobileSignIn($this->customer, 'Pixel 9');
        $stranger = User::factory()->create();
        mobileSignIn($stranger, 'Other phone');
        DB::table('sessions')->insert([
            ['id' => str_repeat('d', 40), 'user_id' => $this->customer->id, 'ip_address' => '203.0.113.1', 'user_agent' => 'Safari', 'payload' => '', 'last_activity' => now()->getTimestamp()],
            ['id' => str_repeat('e', 40), 'user_id' => $stranger->id, 'ip_address' => '203.0.113.2', 'user_agent' => 'Chrome', 'payload' => '', 'last_activity' => now()->getTimestamp()],
        ]);
        $remember = $this->customer->fresh()->remember_token;

        $this->actingAs($this->customer)->postJson('/api/v1/account/devices/sign-out-others', ['current_password' => 'password'])->assertOk()
            ->assertJsonPath('data.signed_out.mobile_families', 1)
            ->assertJsonPath('data.signed_out.web_sessions', 1);

        expect(MobileRefreshToken::query()->where('user_id', $this->customer->id)->whereNull('revoked_at')->exists())->toBeFalse()
            ->and(MobileRefreshToken::query()->where('user_id', $stranger->id)->whereNull('revoked_at')->exists())->toBeTrue()
            ->and(DB::table('sessions')->where('id', str_repeat('d', 40))->exists())->toBeFalse()
            ->and(DB::table('sessions')->where('id', str_repeat('e', 40))->exists())->toBeTrue()
            ->and($this->customer->fresh()->remember_token)->not->toBe($remember)
            ->and(DB::table('audit_logs')->where('action', 'account.signed_out_others')->exists())->toBeTrue();

        $this->actingAs($this->customer)->getJson('/api/v1/account/profile')->assertOk();
    });

    it('keeps the calling app sign-in', function (): void {
        $token = accountMobileToken($this->customer, 'Pixel 9');
        mobileSignIn($this->customer, 'iPad');

        $this->withToken($token)->postJson('/api/v1/mobile/account/devices/sign-out-others', ['current_password' => 'password'])->assertOk()
            ->assertJsonPath('data.signed_out.mobile_families', 1);

        $this->withToken($token)->getJson('/api/v1/mobile/account/profile')->assertOk();
    });
});

describe('wishlist', function (): void {
    it('adds, lists as priced cards and removes, idempotently', function (string $client): void {
        $product = storefrontProduct('Roblox 800 Robux', '9.60');

        accountCall($client, $this->customer, 'POST', 'wishlist', ['product_slug' => $product->slug])->assertCreated()->assertJsonPath('data.saved', true);
        accountCall($client, $this->customer, 'POST', 'wishlist', ['product_slug' => $product->slug])->assertCreated();

        accountCall($client, $this->customer, 'GET', 'wishlist')->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.slug', $product->slug)
            ->assertJsonPath('data.items.0.price_from.formatted', storefrontPrice('9.60'));

        accountCall($client, $this->customer, 'DELETE', "wishlist/{$product->slug}")->assertOk()->assertJsonPath('data.saved', false);
        accountCall($client, $this->customer, 'DELETE', "wishlist/{$product->slug}")->assertOk();

        expect(WishlistItem::query()->count())->toBe(0);
    })->with(accountClients());

    it('refuses unknown or unlisted products and hides a saved product once it is unlisted', function (): void {
        $draft = storefrontProduct('Draft Card', '5', ['status' => ProductStatus::Draft]);
        $listed = storefrontProduct('Listed Card', '5');

        $this->actingAs($this->customer)->postJson('/api/v1/account/wishlist', ['product_slug' => 'no-such-product'])->assertNotFound();
        $this->actingAs($this->customer)->postJson('/api/v1/account/wishlist', ['product_slug' => $draft->slug])->assertNotFound();
        $this->actingAs($this->customer)->postJson('/api/v1/account/wishlist', ['product_slug' => 'Bad Slug!'])->assertUnprocessable()->assertJsonValidationErrors('product_slug');

        $this->actingAs($this->customer)->postJson('/api/v1/account/wishlist', ['product_slug' => $listed->slug])->assertCreated();
        $listed->forceFill(['status' => ProductStatus::Draft])->save();

        $this->actingAs($this->customer)->getJson('/api/v1/account/wishlist')->assertOk()->assertJsonCount(0, 'data.items');
        expect(WishlistItem::query()->where('user_id', $this->customer->id)->count())->toBe(1);
    });

    it('needs a signed-in customer', function (): void {
        $this->postJson('/api/v1/account/wishlist', ['product_slug' => 'anything'])->assertUnauthorized();
    });
});
