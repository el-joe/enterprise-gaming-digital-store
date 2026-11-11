<?php

namespace App\Domains\Seo\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Seo\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * SEO fields per record and locale (M15, `seo_metadata`): title, description, canonical
 * override, robots and the Open Graph image. Only the types of `config('admin.seo')`. A row with
 * every field empty is removed, so templates apply. Audited.
 */
class SeoMetadataService
{
    private const array FIELDS = ['title', 'description', 'canonical_override', 'robots', 'og_image_media_id'];

    public function __construct(private AuditLogger $audit) {}

    public function record(string $type, int $id): Model
    {
        $class = Relation::getMorphedModel($type);

        if ($class === null || ! array_key_exists($type, (array) config('admin.seo'))) {
            throw new NotFoundHttpException;
        }

        return $class::query()->findOrFail($id);
    }

    /**
     * @return array<string, array<string, mixed>> locale => fields
     */
    public function values(Model $record): array
    {
        return SeoMetadata::query()
            ->where('seoable_type', $record->getMorphClass())
            ->where('seoable_id', $record->getKey())
            ->get()
            ->mapWithKeys(static fn (SeoMetadata $row): array => [$row->locale => Arr::only($row->attributesToArray(), self::FIELDS)])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public function save(Model $record, string $locale, array $fields): void
    {
        $data = Arr::only($fields, self::FIELDS);
        $empty = array_filter($data, static fn (mixed $value): bool => $value !== null && $value !== '') === [];
        $match = ['seoable_type' => $record->getMorphClass(), 'seoable_id' => $record->getKey(), 'locale' => $locale];

        DB::transaction(function () use ($record, $locale, $data, $empty, $match): void {
            $row = SeoMetadata::query()->where($match)->lockForUpdate()->first();
            $before = $row === null ? null : Arr::only($row->attributesToArray(), self::FIELDS);

            if ($empty) {
                $row?->delete();
            } else {
                ($row ?? new SeoMetadata($match))->fill($data)->save();
            }

            $this->audit->record(new AuditEntry(
                action: 'seo.metadata.updated',
                subject: $record,
                before: $before,
                after: $empty ? null : ['locale' => $locale, ...$data],
            ));
        });
    }
}
