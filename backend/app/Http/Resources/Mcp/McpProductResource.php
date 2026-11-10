<?php

namespace App\Http\Resources\Mcp;

use App\Domains\Catalog\Models\Product;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Mcp\Services\McpFormat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Catalog fields only. Delivery instructions and terms are not included.
 *
 * @mixin Product
 */
class McpProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status->value,
            'delivery_type' => $this->delivery_type->value,
            'platform' => $this->whenLoaded('gamingPlatform', fn (): ?array => $this->gamingPlatform === null ? null : ['name' => $this->gamingPlatform->name, 'slug' => $this->gamingPlatform->slug]),
            'category' => $this->whenLoaded('primaryCategory', fn (): ?array => $this->primaryCategory === null ? null : ['name' => $this->primaryCategory->name, 'slug' => $this->primaryCategory->slug]),
            'active_variants_count' => $this->whenCounted('active_variants'),
            'is_featured' => $this->is_featured,
            'sales_count' => $this->sales_count,
            'rating' => ['average' => (string) $this->rating_avg, 'count' => $this->rating_count],
            'published_at' => McpFormat::iso($this->published_at),
            'variants' => $this->whenLoaded('productVariants', function (): array {
                $base = app(ExchangeRateRegistry::class)->baseCurrency();

                return $this->productVariants->map(static fn ($variant): array => [
                    'id' => $variant->public_id,
                    'sku' => $variant->sku,
                    'title' => $variant->title,
                    'is_active' => $variant->is_active,
                    'availability' => $variant->availability?->value,
                    'is_custom_amount' => $variant->is_custom_amount,
                    'denomination' => McpFormat::money($variant->denomination_value, $variant->denomination_currency),
                    'price_base' => McpFormat::money($variant->price_base, $base),
                    'compare_at_base' => McpFormat::money($variant->compare_at_base, $base),
                ])->values()->all();
            }),
        ];
    }
}
