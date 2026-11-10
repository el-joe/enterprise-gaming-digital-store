<?php

namespace App\Domains\Cms\Services;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Cms\DTOs\HomeItem;
use App\Domains\Cms\DTOs\HomePage;
use App\Domains\Reviews\Models\Review;

/**
 * One translation query for everything on the home page (none in English), shared by the home
 * JSON (M-A4) and the home Blade page (M12).
 */
class HomeTranslations
{
    public function __construct(private ContentTranslator $translator) {}

    public function preload(HomePage $page): void
    {
        $models = [];

        foreach ($page->sections as $section) {
            $models[] = $section->section;

            foreach ($section->items as $item) {
                /** @var HomeItem $item */
                $payload = $item->payload;

                if ($payload instanceof ProductCard) {
                    array_push($models, $payload->product, $payload->product->gamingPlatform, $payload->product->primaryCategory);
                } elseif ($payload instanceof Review) {
                    $models[] = $payload->product;
                } else {
                    $models[] = $payload;
                }
            }
        }

        $banners = array_merge(...array_values($page->banners));

        $this->translator->preload([...$models, ...$banners], ['name', 'short_description', 'title', 'subtitle', 'cta_label', 'question', 'answer']);
    }
}
