<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Domains\Seo\Services\SeoMetadataService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Seo\SaveSeoMetadataRequest;
use Illuminate\Http\JsonResponse;

/**
 * SEO fields of products, platforms, categories, pages and blog posts (M15, `seo.manage`).
 */
class SeoMetadataController extends Controller
{
    public function __construct(private SeoMetadataService $seo) {}

    public function update(SaveSeoMetadataRequest $request, string $type, int $id): JsonResponse
    {
        $record = $this->seo->record($type, $id);
        $this->seo->save($record, (string) $request->validated('locale'), $request->safe()->except('locale'));

        return $this->success(['values' => $this->seo->values($record)], 'SEO saved.');
    }
}
