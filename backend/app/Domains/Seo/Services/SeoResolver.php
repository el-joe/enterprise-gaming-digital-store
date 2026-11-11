<?php

namespace App\Domains\Seo\Services;

use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Seo\DTOs\ResolvedSeo;
use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Models\SeoMetadata;
use App\Domains\Seo\Models\SeoTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * `SeoResolver` of SEO_STRATEGY.md section 3. Precedence per field: the record's `seo_metadata`
 * row for the current language, then the `seo_templates` row of the page type and language
 * (tokens `{name}`, `{platform}`, `{category}`, `{region}`, `{min_price}`, `{site}`), then the
 * generated default. Robots, canonical override and Open Graph image only come from the row.
 */
class SeoResolver
{
    /** @var array<string, SeoTemplate|null> */
    private array $templates = [];

    public function __construct(private MediaUrls $media) {}

    /**
     * @param  array<string, string|null>  $tokens
     */
    public function resolve(SeoTemplatePageType $type, ?Model $record, array $tokens, string $defaultTitle, ?string $defaultDescription): ResolvedSeo
    {
        $row = $record === null ? null : $this->metadata($record);
        $template = $this->template($type);
        $tokens = ['site' => (string) config('brand.name'), ...$tokens];

        $title = $this->filled($row?->title) ?? $this->render($template?->title_template, $tokens) ?? $defaultTitle;
        $description = $this->filled($row?->description) ?? $this->render($template?->description_template, $tokens) ?? $defaultDescription;

        return new ResolvedSeo(
            title: $title,
            description: $description === null ? null : Str::limit(trim(strip_tags($description)), 160, ''),
            robots: $this->filled($row?->robots),
            canonical: $this->filled($row?->canonical_override),
            image: $row?->ogImageMedia === null ? null : $this->media->image($row->ogImageMedia)['url'],
        );
    }

    private function metadata(Model $record): ?SeoMetadata
    {
        return SeoMetadata::query()
            ->with('ogImageMedia')
            ->where('seoable_type', $record->getMorphClass())
            ->where('seoable_id', $record->getKey())
            ->where('locale', app()->getLocale())
            ->first();
    }

    private function template(SeoTemplatePageType $type): ?SeoTemplate
    {
        $key = $type->value.':'.app()->getLocale();

        if (! array_key_exists($key, $this->templates)) {
            $this->templates[$key] = SeoTemplate::query()->where('page_type', $type)->where('locale', app()->getLocale())->first();
        }

        return $this->templates[$key];
    }

    /**
     * A template with its tokens replaced; null when a used token has no value, so the default
     * applies instead of a title with a hole in it.
     *
     * @param  array<string, string|null>  $tokens
     */
    private function render(?string $template, array $tokens): ?string
    {
        if ($this->filled($template) === null) {
            return null;
        }

        $missing = false;
        $text = preg_replace_callback('/\{([a-z_]+)\}/', static function (array $match) use ($tokens, &$missing): string {
            $value = $tokens[$match[1]] ?? null;

            if ($value === null || $value === '') {
                $missing = true;

                return '';
            }

            return $value;
        }, (string) $template);

        return $missing ? null : $this->filled($text);
    }

    private function filled(?string $value): ?string
    {
        return $value === null || trim($value) === '' ? null : trim($value);
    }
}
