<?php

namespace App\Http\Resources\Storefront;

use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Services\LinkTargetResolver;
use App\Domains\Cms\Services\MediaUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A live storefront banner (M-A4): localized text, absolute desktop and mobile images, and the
 * call to action as a structured link (`link`, null without `cta_url`).
 *
 * @property-read Banner $resource
 */
class BannerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $banner = $this->resource;
        $translator = app(ContentTranslator::class);
        $media = app(MediaUrls::class);

        return [
            'id' => $banner->id,
            'placement' => $banner->placement->value,
            'title' => $translator->text($banner, 'title'),
            'subtitle' => $translator->text($banner, 'subtitle'),
            'cta_label' => $translator->text($banner, 'cta_label'),
            'link' => app(LinkTargetResolver::class)->fromUrl($banner->cta_url)?->toArray(),
            'image' => [
                'desktop' => $banner->media === null ? null : $media->image($banner->media),
                'mobile' => $banner->mobileMedia === null ? null : $media->image($banner->mobileMedia),
            ],
            'starts_at' => $banner->starts_at?->toIso8601String(),
            'ends_at' => $banner->ends_at?->toIso8601String(),
        ];
    }
}
