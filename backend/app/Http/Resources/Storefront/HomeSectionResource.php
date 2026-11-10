<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\DTOs\HomeItem;
use App\Domains\Cms\DTOs\HomeSection;
use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Services\LinkTargetResolver;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Reviews\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A homepage section (M-A4) with its items as `{ type, data }`. Products are ProductCardResource
 * objects plus `link`; platforms and categories carry `link` too, so a client never parses a
 * web path for them.
 *
 * @property-read HomeSection $resource
 */
class HomeSectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $section = $this->resource->section;
        $translator = app(ContentTranslator::class);

        return [
            'key' => $section->key,
            'title' => $translator->text($section, 'title'),
            'subtitle' => $translator->text($section, 'subtitle'),
            'source' => $section->source->value,
            'rule' => $this->resource->rule?->value,
            'items' => array_map(fn (HomeItem $item): array => ['type' => $item->type, 'data' => $this->item($item->payload, $request)], $this->resource->items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function item(ProductCard|GamingPlatform|Category|Banner|Faq|Review $payload, Request $request): array
    {
        $translator = app(ContentTranslator::class);
        $links = app(LinkTargetResolver::class);
        $media = app(MediaUrls::class);

        return match (true) {
            $payload instanceof ProductCard => [
                ...(new ProductCardResource($payload))->toArray($request),
                'link' => $links->forEntity(LinkTargetType::Product, $payload->product->slug)->toArray(),
            ],
            $payload instanceof GamingPlatform => [
                'name' => $translator->text($payload, 'name'),
                'slug' => $payload->slug,
                'logo' => $payload->logoMedia === null ? null : $media->image($payload->logoMedia),
                'banner' => $payload->bannerMedia === null ? null : $media->image($payload->bannerMedia),
                'link' => $links->forEntity(LinkTargetType::Platform, $payload->slug)->toArray(),
            ],
            $payload instanceof Category => [
                'name' => $translator->text($payload, 'name'),
                'slug' => $payload->slug,
                'banner' => $payload->bannerMedia === null ? null : $media->image($payload->bannerMedia),
                'link' => $links->forEntity(LinkTargetType::Category, $payload->slug)->toArray(),
            ],
            $payload instanceof Banner => (new BannerResource($payload))->toArray($request),
            $payload instanceof Faq => [
                'id' => $payload->id,
                'question' => $translator->text($payload, 'question'),
                'answer' => $translator->text($payload, 'answer'),
            ],
            default => [
                ...(new ProductReviewResource($payload))->toArray($request),
                'product' => $payload->product === null ? null : [
                    'name' => $translator->text($payload->product, 'name'),
                    'slug' => $payload->product->slug,
                    'link' => $links->forEntity(LinkTargetType::Product, $payload->product->slug)->toArray(),
                ],
            ],
        };
    }
}
