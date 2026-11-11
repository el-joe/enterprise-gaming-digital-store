<?php

namespace App\Domains\Seo\Services;

use App\Domains\Seo\Models\Redirect;
use App\Http\Support\LocalizedRoutes;
use Illuminate\Support\Facades\DB;

/**
 * "A slug change automatically creates a 301" (SEO_STRATEGY.md section 7, M18): when a product,
 * platform, category, CMS page or blog post changes its slug, the old URL of every language
 * redirects to the new one. Existing redirects that pointed at the old URL are collapsed onto
 * the new one (no chains), and a redirect away from the new URL (a slug changed back) is removed
 * (no loops).
 */
class SlugRedirectRecorder
{
    public function record(string $route, string $oldSlug, string $newSlug): void
    {
        if ($oldSlug === '' || $newSlug === '' || $oldSlug === $newSlug) {
            return;
        }

        DB::transaction(function () use ($route, $oldSlug, $newSlug): void {
            foreach ((array) config('app.supported_locales') as $locale) {
                $source = $this->path(LocalizedRoutes::url($route, ['slug' => $oldSlug], $locale));
                $target = $this->path(LocalizedRoutes::url($route, ['slug' => $newSlug], $locale));

                Redirect::query()->where('source_hash', RedirectService::hash($target))->get()->each->delete();
                Redirect::query()->where('target_path', $source)->get()->each(static fn (Redirect $redirect) => $redirect->update(['target_path' => $target]));

                $redirect = Redirect::query()->firstOrNew(['source_hash' => RedirectService::hash($source)]);
                $redirect->fill(['source_path' => $source, 'target_path' => $target, 'status_code' => 301, 'is_active' => true])->save();
            }
        });
    }

    private function path(string $url): string
    {
        return RedirectService::normalize((string) parse_url($url, PHP_URL_PATH));
    }
}
