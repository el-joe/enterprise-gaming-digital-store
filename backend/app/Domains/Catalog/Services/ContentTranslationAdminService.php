<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Shared\Services\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Admin writes of `content_translations` (M15, ADR-015; the missing write endpoint noted at M5
 * and M-A3). Only the types and fields of `config('admin.translations')` are written, only for a
 * supported locale other than the base (English) one; an empty value removes the translation,
 * so the storefront falls back to the base column. HTML fields are sanitized like the base
 * columns. Every save is audited (field names, never the text) and refreshes the search index
 * of catalog records, whose Arabic titles come from here.
 */
class ContentTranslationAdminService
{
    public function __construct(private AuditLogger $audit, private HtmlSanitizer $sanitizer, private SearchIndexer $search) {}

    /**
     * The record of a translatable type, or 404.
     */
    public function record(string $type, int $id): Model
    {
        $class = Relation::getMorphedModel($type);

        if ($class === null || ! array_key_exists($type, (array) config('admin.translations'))) {
            throw new NotFoundHttpException;
        }

        return $class::query()->findOrFail($id);
    }

    /**
     * Current translations of a record, keyed by locale then field.
     *
     * @return array<string, array<string, string>>
     */
    public function values(Model $record): array
    {
        $values = [];

        ContentTranslation::query()
            ->where('translatable_type', $record->getMorphClass())
            ->where('translatable_id', $record->getKey())
            ->get(['locale', 'field', 'value'])
            ->each(static function (ContentTranslation $row) use (&$values): void {
                $values[$row->locale][$row->field] = (string) $row->value;
            });

        return $values;
    }

    /**
     * @param  array<string, string|null>  $values  field => text (null or empty removes it)
     */
    public function save(Model $record, string $locale, array $values): void
    {
        $type = $record->getMorphClass();
        $fields = (array) config("admin.translations.{$type}.fields", []);

        DB::transaction(function () use ($record, $type, $locale, $values, $fields): void {
            $changed = [];

            foreach ($values as $field => $value) {
                if (! array_key_exists($field, $fields)) {
                    continue;
                }

                $text = $fields[$field] === 'html' ? $this->sanitizer->sanitize($value) : $value;
                $text = $text === null ? '' : trim($text);
                $match = ['translatable_type' => $type, 'translatable_id' => $record->getKey(), 'locale' => $locale, 'field' => $field];

                if ($text === '') {
                    if (ContentTranslation::query()->where($match)->delete() > 0) {
                        $changed[] = $field;
                    }

                    continue;
                }

                $row = ContentTranslation::query()->firstOrNew($match);

                if ($row->value !== $text) {
                    $row->value = $text;
                    $row->save();
                    $changed[] = $field;
                }
            }

            $this->audit->record(new AuditEntry(
                action: 'content.translation.updated',
                subject: $record,
                after: ['locale' => $locale, 'fields' => $changed],
            ));
        });

        match (true) {
            $record instanceof Product => $this->search->indexProduct($record),
            $record instanceof GamingPlatform => $this->search->indexPlatform($record),
            $record instanceof Category => $this->search->indexCategory($record),
            default => null,
        };
    }
}
