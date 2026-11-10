<?php

/**
 * Reviews: verified purchase only, moderation before publication, product rating from approved
 * reviews only.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    $this->buyer = User::factory()->create(['name' => 'Mona Ahmed Salem']);
    $this->variant = sellableVariant('10');
    $this->order = paidOrderFor($this->variant, 2, orderAttributes: ['user_id' => $this->buyer->id]);
    $this->item = OrderItem::query()->where('order_id', $this->order->id)->sole();
    setUnitStatuses($this->order, [Unit::Delivered, Unit::Failed]);
});

function postReview(User $user, int $itemId, array $extra = []): TestResponse
{
    return test()->actingAs($user)->postJson('/api/v1/account/reviews', ['order_item_id' => $itemId, 'rating' => 5, 'title' => 'Great', 'body' => 'Code worked <b>instantly</b>.', ...$extra]);
}

describe('verified purchase', function (): void {
    it('accepts a review of a delivered item from the buyer, pending moderation, as plain text', function (): void {
        $this->actingAs($this->buyer)->getJson('/api/v1/account/reviews/eligible')->assertOk()
            ->assertJsonPath('data.items.0.order_item_id', $this->item->id)
            ->assertJsonPath('data.items.0.order.public_id', $this->order->public_id);

        postReview($this->buyer, $this->item->id)->assertCreated()
            ->assertJsonPath('data.review.status', 'pending')
            ->assertJsonPath('data.review.body', 'Code worked instantly.');

        $review = Review::query()->sole();
        expect($review->product_id)->toBe($this->variant->product_id)->and($review->order_item_id)->toBe($this->item->id);
        $this->actingAs($this->buyer)->getJson('/api/v1/account/reviews/eligible')->assertJsonCount(0, 'data.items');
        $this->actingAs($this->buyer)->getJson('/api/v1/account/reviews')->assertJsonCount(1, 'data');
    });

    it('refuses an item the customer did not buy, did not receive, or has not paid for', function (Closure $arrange): void {
        [$user, $itemId] = $arrange($this);

        postReview($user, $itemId)->assertUnprocessable()->assertJsonPath('code', 'review_not_eligible');
        expect(Review::query()->count())->toBe(0);
    })->with([
        'someone else\'s order' => [fn ($test) => [User::factory()->create(), $test->item->id]],
        'nothing delivered' => [function ($test) {
            setUnitStatuses($test->order, [Unit::Failed, Unit::Refunded]);

            return [$test->buyer, $test->item->id];
        }],
        'unpaid order' => [function ($test) {
            Order::query()->whereKey($test->order->id)->update(['paid_at' => null]);

            return [$test->buyer, $test->item->id];
        }],
        'unknown item' => [fn ($test) => [$test->buyer, 999999]],
    ]);

    it('refuses a second review of the same item', function (): void {
        postReview($this->buyer, $this->item->id)->assertCreated();
        postReview($this->buyer, $this->item->id)->assertStatus(409)->assertJsonPath('code', 'review_exists');
    });

    it('requires a verified email and a signed-in customer', function (): void {
        $this->buyer->forceFill(['email_verified_at' => null])->save();

        postReview($this->buyer->fresh(), $this->item->id)->assertForbidden()->assertJsonPath('code', 'email_unverified');
        auth()->logout();
        $this->postJson('/api/v1/account/reviews', ['order_item_id' => $this->item->id, 'rating' => 5])->assertUnauthorized();
    });

    it('validates the rating', function (mixed $rating): void {
        postReview($this->buyer, $this->item->id, ['rating' => $rating])->assertUnprocessable()->assertJsonValidationErrors('rating');
    })->with([0, 6, 'five']);
});

describe('moderation and publication', function (): void {
    beforeEach(function (): void {
        postReview($this->buyer, $this->item->id)->assertCreated();
        $this->review = Review::query()->sole();
        $this->product = $this->variant->product;
        $this->product->forceFill(['status' => ProductStatus::Active])->save();
    });

    it('publishes only approved reviews and keeps the product rating to approved ones', function (): void {
        $url = "/api/v1/storefront/catalog/products/{$this->product->slug}/reviews";
        $this->getJson($url)->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.rating_count', 0);

        actingAsStaffWith(['catalog.manage']);
        $this->postJson("/admin/api/reviews/{$this->review->id}/approve")->assertOk()->assertJsonPath('data.status', 'approved');

        $other = Review::factory()->create(['product_id' => $this->product->id, 'rating' => 2, 'status' => ReviewStatus::Pending]);
        $this->postJson("/admin/api/reviews/{$other->id}/reject", ['reason' => 'Off topic'])->assertOk();

        auth()->logout();
        $response = $this->getJson($url)->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reviewer', 'Mona S.')
            ->assertJsonPath('data.0.verified_purchase', true)
            ->assertJsonPath('meta.rating_count', 1)
            ->assertJsonPath('meta.rating_avg', '5.00');

        expect(json_encode($response->json()))->not->toContain($this->buyer->email)
            ->and(AuditLog::query()->where('action', 'reviews.review.rejected')->sole()->reason)->toBe('Off topic');
    });

    it('recomputes the rating when an approved review is rejected or deleted', function (): void {
        actingAsStaffWith(['catalog.manage']);
        $this->postJson("/admin/api/reviews/{$this->review->id}/approve")->assertOk();
        expect($this->product->fresh()->rating_count)->toBe(1);

        $this->postJson("/admin/api/reviews/{$this->review->id}/reject", ['reason' => 'Fake'])->assertOk();
        expect($this->product->fresh()->rating_count)->toBe(0)->and((string) $this->product->fresh()->rating_avg)->toBe('0.00');

        $this->deleteJson("/admin/api/reviews/{$this->review->id}")->assertOk();
        expect(Review::withTrashed()->sole()->trashed())->toBeTrue();
    });

    it('features approved reviews only and requires a reason to reject', function (): void {
        actingAsStaffWith(['catalog.manage']);

        $this->patchJson("/admin/api/reviews/{$this->review->id}/feature", ['is_featured' => true])->assertStatus(409);
        $this->postJson("/admin/api/reviews/{$this->review->id}/reject")->assertUnprocessable()->assertJsonValidationErrors('reason');

        $this->postJson("/admin/api/reviews/{$this->review->id}/approve")->assertOk();
        $this->patchJson("/admin/api/reviews/{$this->review->id}/feature", ['is_featured' => true])->assertOk()->assertJsonPath('data.is_featured', true);
        $this->getJson('/admin/api/reviews?filter[status]=approved')->assertOk()->assertJsonCount(1, 'data');
    });

    it('needs catalog.manage to moderate', function (): void {
        actingAsStaffWith(['support.view']);

        $this->getJson('/admin/api/reviews')->assertForbidden();
        $this->postJson("/admin/api/reviews/{$this->review->id}/approve")->assertForbidden();
    });

    it('hides the reviews of a product that is not active', function (): void {
        $this->product->forceFill(['status' => ProductStatus::Draft])->save();

        $this->getJson("/api/v1/storefront/catalog/products/{$this->product->slug}/reviews")->assertNotFound();
    });
});
