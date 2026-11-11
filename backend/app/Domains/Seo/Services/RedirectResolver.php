<?php

namespace App\Domains\Seo\Services;

use App\Domains\Seo\DTOs\RedirectMatch;
use App\Domains\Seo\Models\Redirect;
use Illuminate\Support\Facades\Cache;

/**
 * Runtime lookup of the admin redirects (SEO_STRATEGY.md section 7, M18), after a route miss.
 * Each path lookup is cached (misses too) under a version that every change to `redirects`
 * bumps, so edits apply at once. A chain that slipped past the save-time collapse is followed
 * up to `seo.redirects.max_hops`; a loop, or a chain longer than that, is ignored (404) rather
 * than sent to the browser. The first row decides permanent (301) or temporary (302); a 410
 * anywhere in the chain makes the page gone.
 */
class RedirectResolver
{
    private const string VERSION_KEY = 'seo:redirects:version';

    public function resolve(string $path): ?RedirectMatch
    {
        $current = RedirectService::normalize($path);
        $seen = [$current => true];
        $ids = [];
        $status = null;
        $target = null;

        for ($hop = 0; $hop < (int) config('seo.redirects.max_hops', 5); $hop++) {
            $row = $this->lookup($current);

            if ($row === null) {
                return $ids === [] ? null : new RedirectMatch($status ?? 301, $target, $ids);
            }

            $ids[] = $row['id'];
            $status ??= $row['status'];

            if ($row['status'] === 410 || $row['target'] === null) {
                return new RedirectMatch(410, null, $ids);
            }

            $next = RedirectService::normalize((string) parse_url($row['target'], PHP_URL_PATH));

            if (isset($seen[$next])) {
                return null;
            }

            $seen[$next] = true;
            $current = $next;
            $target = $row['target'];

            if ($this->lookup($next) === null) {
                return new RedirectMatch($status, $target, $ids);
            }
        }

        return null;
    }

    public function countHits(RedirectMatch $match): void
    {
        Redirect::query()->whereKey($match->redirectIds)->increment('hits');
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, (int) Cache::get(self::VERSION_KEY, 0) + 1);
    }

    /**
     * @return array{id: int, target: string|null, status: int}|null
     */
    private function lookup(string $path): ?array
    {
        $key = 'seo:redirect:'.(int) Cache::get(self::VERSION_KEY, 0).':'.RedirectService::hash($path);

        $row = Cache::remember($key, (int) config('seo.redirects.cache_seconds', 3600), static function () use ($path): array {
            $redirect = Redirect::query()
                ->where('source_hash', RedirectService::hash($path))
                ->where('is_active', true)
                ->first(['id', 'target_path', 'status_code']);

            return $redirect === null ? ['missing' => true] : ['id' => $redirect->id, 'target' => $redirect->target_path, 'status' => $redirect->status_code];
        });

        return isset($row['missing']) ? null : $row;
    }
}
