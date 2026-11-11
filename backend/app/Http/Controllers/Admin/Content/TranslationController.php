<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domains\Catalog\Services\ContentTranslationAdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\SaveTranslationsRequest;
use Illuminate\Http\JsonResponse;

/**
 * Translated content of catalog and CMS records (M15). One route per type, each behind the
 * permission that edits that type (`config('admin.translations')`).
 */
class TranslationController extends Controller
{
    public function __construct(private ContentTranslationAdminService $translations) {}

    public function update(SaveTranslationsRequest $request, int $id): JsonResponse
    {
        $record = $this->translations->record($request->type(), $id);
        $this->translations->save($record, (string) $request->validated('locale'), (array) $request->validated('values'));

        return $this->success(['values' => $this->translations->values($record)], 'Translation saved.');
    }
}
