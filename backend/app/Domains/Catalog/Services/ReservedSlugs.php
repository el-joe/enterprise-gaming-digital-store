<?php

namespace App\Domains\Catalog\Services;

use Illuminate\Routing\Router;

/**
 * The slugs a category may not take because /{slug} would shadow another route.
 */
class ReservedSlugs
{
    public function __construct(private Router $router) {}

    public function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), $this->all(), true);
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        $fromRoutes = [];

        foreach ($this->router->getRoutes()->getRoutes() as $route) {
            $segment = strtolower(explode('/', ltrim($route->uri(), '/'))[0]);

            if ($segment !== '' && ! str_contains($segment, '{')) {
                $fromRoutes[] = $segment;
            }
        }

        return array_values(array_unique([...config('catalog.reserved_slugs'), ...$fromRoutes]));
    }
}
