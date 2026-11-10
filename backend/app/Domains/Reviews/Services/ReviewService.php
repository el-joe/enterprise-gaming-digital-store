<?php

namespace App\Domains\Reviews\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\Product;
use App\Domains\Identity\Models\User;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use App\Domains\Reviews\Repositories\ReviewRepository;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Product reviews: verified purchase only, then moderation. A customer with a verified email may
 * review an item of their own paid order once at least one of its units was delivered, once per
 * item (`uq_r_item`). Every review starts `pending` and is public only when staff approve it.
 * The product's `rating_avg` and `rating_count` count approved reviews only and are recomputed
 * under the product row lock on every moderation. Text is stored as plain text and escaped on
 * output; it is never rendered as HTML.
 */
class ReviewService
{
    public function __construct(private ReviewRepository $reviews, private AuditLogger $audit) {}

    /**
     * @param  array{order_item_id: int, rating: int, title?: ?string, body?: ?string}  $input
     */
    public function submit(User $user, array $input): Review
    {
        if ($user->email_verified_at === null) {
            throw new ApplicationException('Verify your email address before writing a review.', 'email_unverified', 403);
        }

        $item = $this->reviews->purchasedItem($user->id, (int) $input['order_item_id'])
            ?? throw new ApplicationException('Only items you bought and received can be reviewed.', 'review_not_eligible', 422, ['order_item_id' => ['Only items you bought and received can be reviewed.']]);

        try {
            $review = (new Review)->forceFill([
                'product_id' => $item->product_id,
                'user_id' => $user->id,
                'order_item_id' => $item->id,
                'rating' => (int) $input['rating'],
                'title' => self::plain($input['title'] ?? null),
                'body' => self::plain($input['body'] ?? null),
                'status' => ReviewStatus::Pending,
            ]);
            $review->save();
        } catch (UniqueConstraintViolationException) {
            throw new ApplicationException('You have already reviewed this item.', 'review_exists', 409);
        }

        return $review->load('product:id,name,slug');
    }

    public function approve(Review $review, User $staff): Review
    {
        return $this->moderate($review, ReviewStatus::Approved, $staff, null);
    }

    public function reject(Review $review, User $staff, string $reason): Review
    {
        return $this->moderate($review, ReviewStatus::Rejected, $staff, $reason);
    }

    public function feature(Review $review, bool $featured, User $staff): Review
    {
        if ($featured && $review->status !== ReviewStatus::Approved) {
            throw new ApplicationException('Only an approved review can be featured.', 'conflict', 409);
        }

        $review->forceFill(['is_featured' => $featured, 'moderated_by' => $staff->id])->save();
        $this->audit->record(new AuditEntry(action: 'reviews.review.featured', subject: $review, after: ['is_featured' => $featured]));

        return $review->load(['product:id,name,slug', 'user:id,name,email']);
    }

    /**
     * Featured approved reviews of active products, newest first (home page, M-A4).
     *
     * @return Collection<int, Review>
     */
    public function featuredForHome(int $limit): Collection
    {
        return $this->reviews->featuredApproved($limit);
    }

    public function delete(Review $review): void
    {
        DB::transaction(function () use ($review): void {
            Product::query()->whereKey($review->product_id)->lockForUpdate()->first();
            $review->delete();
            $this->refreshRating($review->product_id);
            $this->audit->record(new AuditEntry(action: 'reviews.review.deleted', subject: $review, before: ['status' => $review->status->value, 'rating' => $review->rating]));
        });
    }

    private function moderate(Review $review, ReviewStatus $status, User $staff, ?string $reason): Review
    {
        DB::transaction(function () use ($review, $status, $staff, $reason): void {
            Product::query()->whereKey($review->product_id)->lockForUpdate()->first();
            $before = $review->status;

            $review->forceFill([
                'status' => $status,
                'moderated_by' => $staff->id,
                'is_featured' => $status === ReviewStatus::Approved && $review->is_featured,
            ])->save();

            $this->refreshRating($review->product_id);

            $this->audit->record(new AuditEntry(
                action: 'reviews.review.'.$status->value,
                subject: $review,
                before: ['status' => $before->value],
                after: ['status' => $status->value],
                reason: $reason,
            ));
        });

        return $review->load(['product:id,name,slug', 'user:id,name,email']);
    }

    private function refreshRating(int $productId): void
    {
        $stats = $this->reviews->approvedStats($productId);

        Product::query()->whereKey($productId)->update(['rating_avg' => $stats['avg'], 'rating_count' => $stats['count']]);
    }

    private static function plain(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $clean = trim(strip_tags($text));

        return $clean === '' ? null : $clean;
    }
}
