<?php

namespace App\Domains\Seo\Sitemaps;

use App\Http\Support\LocalizedRoutes;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use XMLWriter;

/**
 * Writes the sitemaps (SEO_STRATEGY.md sections 5 and 6, M18): one child per type and language,
 * split every `seo.sitemaps.chunk_size` URLs (`products-en-1.xml`, `products-ar-1.xml`, ...),
 * each URL with its hreflang alternates (every language it is indexable in, plus `x-default`)
 * and `lastmod`, then the index `sitemap.xml`. Files are written to a fresh run directory first
 * and swapped in, so a reader never sees a half-written set. Run by `seo:generate-sitemaps`
 * (hourly) and on the first request when nothing was generated yet.
 */
class SitemapGenerator
{
    public const string INDEX = 'sitemap.xml';

    public function __construct(private SitemapSource $source) {}

    /**
     * @return list<string> the child file names written
     */
    public function generate(): array
    {
        return Cache::lock('seo:sitemaps:generate', 600)->block(30, function (): array {
            $disk = $this->disk();
            $run = $this->directory().'/run-'.now()->format('YmdHisv');
            $children = [];

            foreach (SitemapSource::TYPES as $type) {
                foreach ((array) config('app.supported_locales') as $locale) {
                    foreach ($this->writeType($disk, $run, $type, $locale) as $file => $lastModified) {
                        $children[$file] = $lastModified;
                    }
                }
            }

            $disk->put($run.'/'.self::INDEX, $this->index($children));
            $this->swap($disk, $run);

            return array_keys($children);
        });
    }

    /**
     * Contents of a published sitemap file, or null when it does not exist.
     */
    public function read(string $file): ?string
    {
        $path = $this->directory().'/current/'.$file;

        return $this->disk()->exists($path) ? $this->disk()->get($path) : null;
    }

    public function exists(): bool
    {
        return $this->disk()->exists($this->directory().'/current/'.self::INDEX);
    }

    /**
     * @return array<string, CarbonInterface|null> file name => latest lastmod in the file
     */
    private function writeType(Filesystem $disk, string $run, string $type, string $locale): array
    {
        $chunkSize = max(1, (int) config('seo.sitemaps.chunk_size', 50000));
        $files = [];
        $writer = null;
        $count = 0;
        $chunk = 0;
        $latest = null;

        $close = function () use (&$writer, &$files, &$chunk, &$latest, $disk, $run, $type, $locale): void {
            if ($writer === null) {
                return;
            }

            $writer->endElement();
            $writer->endDocument();
            $name = "{$type}-{$locale}-{$chunk}.xml";
            $disk->put($run.'/'.$name, $writer->outputMemory());
            $files[$name] = $latest;
            $writer = null;
            $latest = null;
        };

        foreach ($this->source->entries($type) as $entry) {
            if (in_array($locale, $entry->noindexLocales, true)) {
                continue;
            }

            if ($writer === null || $count >= $chunkSize) {
                $close();
                $chunk++;
                $count = 0;
                $writer = $this->openUrlSet();
            }

            $this->writeUrl($writer, $entry, $locale);
            $count++;

            if ($entry->lastModified !== null && ($latest === null || $entry->lastModified->greaterThan($latest))) {
                $latest = $entry->lastModified;
            }
        }

        $close();

        return $files;
    }

    private function openUrlSet(): XMLWriter
    {
        $writer = new XMLWriter;
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $writer->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        return $writer;
    }

    private function writeUrl(XMLWriter $writer, SitemapEntry $entry, string $locale): void
    {
        $alternates = [];

        foreach ((array) config('app.supported_locales') as $code) {
            if (! in_array($code, $entry->noindexLocales, true)) {
                $alternates[$code] = LocalizedRoutes::url($entry->route, $entry->parameters, $code);
            }
        }

        $writer->startElement('url');
        $writer->writeElement('loc', $alternates[$locale]);

        if ($entry->lastModified !== null) {
            $writer->writeElement('lastmod', $entry->lastModified->toAtomString());
        }

        if (count($alternates) > 1) {
            foreach ($alternates as $code => $href) {
                $this->writeAlternate($writer, $code, $href);
            }

            if (isset($alternates[LocalizedRoutes::defaultLocale()])) {
                $this->writeAlternate($writer, 'x-default', $alternates[LocalizedRoutes::defaultLocale()]);
            }
        }

        $writer->endElement();
    }

    private function writeAlternate(XMLWriter $writer, string $hreflang, string $href): void
    {
        $writer->startElement('xhtml:link');
        $writer->writeAttribute('rel', 'alternate');
        $writer->writeAttribute('hreflang', $hreflang);
        $writer->writeAttribute('href', $href);
        $writer->endElement();
    }

    /**
     * @param  array<string, CarbonInterface|null>  $children
     */
    private function index(array $children): string
    {
        $writer = new XMLWriter;
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('sitemapindex');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($children as $file => $lastModified) {
            $writer->startElement('sitemap');
            $writer->writeElement('loc', url('/sitemaps/'.$file));

            if ($lastModified !== null) {
                $writer->writeElement('lastmod', $lastModified->toAtomString());
            }

            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    /**
     * Replace `current` with the run directory and remove older runs.
     */
    private function swap(Filesystem $disk, string $run): void
    {
        $current = $this->directory().'/current';

        $disk->deleteDirectory($current.'-old');

        if ($disk->exists($current)) {
            $disk->move($current, $current.'-old');
        }

        $disk->move($run, $current);
        $disk->deleteDirectory($current.'-old');

        foreach ($disk->directories($this->directory()) as $directory) {
            if (str_starts_with(basename($directory), 'run-')) {
                $disk->deleteDirectory($directory);
            }
        }
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('seo.sitemaps.disk', 'local'));
    }

    private function directory(): string
    {
        return trim((string) config('seo.sitemaps.directory', 'sitemaps'), '/');
    }
}
