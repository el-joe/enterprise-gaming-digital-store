<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Reviews\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A public review: the reviewer's first name and initial only, never an email or id.
 *
 * @mixin Review
 */
class ProductReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'title' => $this->title,
            'body' => $this->body,
            'reviewer' => self::displayName($this->user?->name),
            'verified_purchase' => $this->order_item_id !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public static function displayName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = $parts[0] ?? '';

        if ($first === '') {
            return 'Customer';
        }

        $last = count($parts) > 1 ? ' '.mb_strtoupper(mb_substr((string) end($parts), 0, 1)).'.' : '';

        return $first.$last;
    }
}
