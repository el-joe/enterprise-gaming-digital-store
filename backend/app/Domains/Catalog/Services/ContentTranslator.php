<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Repositories\ContentTranslationRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Localized storefront text (M-A3, gap G3, ADR-015): base columns are English, other locales
 * come from `content_translations`, with the base column as the fallback for a missing or empty
 * translation. Scoped to the request; callers preload a page of models in one query, so
 * resources never query per row. The base locale costs no query at all.
 */
class ContentTranslator
{
    /** @var array<string, string> */
    private array $values = [];

    /** @var array<string, true> */
    private array $loaded = [];

    public function __construct(private ContentTranslationRepository $translations) {}

    /**
     * Load the translations of these models (nulls are skipped) for the current locale.
     *
     * @param  iterable<Model|null>  $models
     * @param  list<string>  $fields
     */
    public function preload(iterable $models, array $fields): void
    {
        if ($this->isBaseLocale()) {
            return;
        }

        $idsByType = [];

        foreach ($models as $model) {
            $loadKey = $model === null ? null : $this->modelKey($model).'|'.implode(',', $fields);

            if ($loadKey !== null && ! isset($this->loaded[$loadKey])) {
                $idsByType[$model->getMorphClass()][] = (int) $model->getKey();
                $this->loaded[$loadKey] = true;
            }
        }

        foreach ($this->translations->valuesFor(app()->getLocale(), $idsByType, $fields) as $key => $value) {
            $this->values[app()->getLocale().':'.$key] = $value;
        }
    }

    /**
     * The translated value of a field, or the base column when there is none.
     */
    public function text(?Model $model, string $field): ?string
    {
        if ($model === null) {
            return null;
        }

        $base = $model->getAttribute($field);

        if ($this->isBaseLocale()) {
            return $base;
        }

        $translated = $this->values[$this->modelKey($model).':'.$field] ?? null;

        return $translated === null || trim($translated) === '' ? $base : $translated;
    }

    private function isBaseLocale(): bool
    {
        return app()->getLocale() === config('app.fallback_locale');
    }

    /**
     * Locale-qualified, so a locale change inside one container never reads stale values.
     */
    private function modelKey(Model $model): string
    {
        return app()->getLocale().':'.$model->getMorphClass().':'.$model->getKey();
    }
}
