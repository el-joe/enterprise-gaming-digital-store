<?php

namespace App\Domains\Catalog\Repositories;

use App\Domains\Catalog\Models\ContentTranslation;

/**
 * Reads of `content_translations` (ADR-015) for the storefront.
 */
class ContentTranslationRepository
{
    /**
     * Translated values of the given models in one query, keyed `type:id:field`.
     *
     * @param  array<string, list<int>>  $idsByType  morph alias => model ids
     * @param  list<string>  $fields
     * @return array<string, string>
     */
    public function valuesFor(string $locale, array $idsByType, array $fields): array
    {
        $idsByType = array_filter($idsByType);

        if ($idsByType === [] || $fields === []) {
            return [];
        }

        return ContentTranslation::query()
            ->select(['translatable_type', 'translatable_id', 'field', 'value'])
            ->where('locale', $locale)
            ->whereIn('field', $fields)
            ->where(function ($query) use ($idsByType): void {
                foreach ($idsByType as $type => $ids) {
                    $query->orWhere(fn ($match) => $match->where('translatable_type', $type)->whereIn('translatable_id', array_values(array_unique($ids))));
                }
            })
            ->get()
            ->mapWithKeys(static fn (ContentTranslation $row): array => [$row->translatable_type.':'.$row->translatable_id.':'.$row->field => $row->value])
            ->all();
    }
}
