<?php

namespace App\Providers;

use App\Domains\Analytics\Services\PageAnalytics;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Models\Page;
use App\Domains\Seo\Services\SeoResolver;
use App\Domains\Seo\Services\SlugRedirectRecorder;
use App\Domains\Seo\StructuredData\PageSchema;
use App\Http\Support\LocalizedRoutes;
use App\View\Composers\AccountLayoutComposer;
use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\StorefrontLayoutComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Storefront pages (M12) and account pages (M13): the layout view composers and `url()->localized()` for links that
 * stay in the page's language.
 */
class StorefrontServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // M18: the JSON-LD, browser analytics events and SEO templates of the page being rendered.
        $this->app->scoped(PageSchema::class);
        $this->app->scoped(PageAnalytics::class);
        $this->app->scoped(SeoResolver::class);
    }

    /**
     * Page-scoped SEO and analytics state never outlives its request (long-lived workers, tests
     * sending several requests through one application).
     */
    private function forgetPageState(): void
    {
        foreach ([PageSchema::class, PageAnalytics::class, SeoResolver::class] as $abstract) {
            $this->app->forgetInstance($abstract);
        }
    }

    public function boot(): void
    {
        Event::listen(RequestHandled::class, fn () => $this->forgetPageState());

        View::composer('layouts.storefront', StorefrontLayoutComposer::class);
        View::composer('layouts.account', AccountLayoutComposer::class);
        View::composer(['layouts.admin', 'layouts.admin-auth', 'admin.*'], AdminLayoutComposer::class);

        // M18: a changed slug redirects the old URL of every language (SEO_STRATEGY.md section 7).
        $slugRoutes = [
            Product::class => 'storefront.products.show',
            GamingPlatform::class => 'storefront.platforms.show',
            Category::class => 'storefront.category',
            Page::class => 'storefront.pages.show',
            BlogPost::class => 'storefront.blog.show',
        ];

        foreach ($slugRoutes as $model => $route) {
            $model::updated(static function (Model $record) use ($route): void {
                if ($record->wasChanged('slug')) {
                    app(SlugRedirectRecorder::class)->record($route, (string) $record->getOriginal('slug'), (string) $record->getAttribute('slug'));
                }
            });
        }

        // Registered on the class, so booting never resolves the URL generator (and its request).
        UrlGenerator::macro('localized', function (string $name, array $parameters = []): string {
            /** @var UrlGenerator $this */
            return LocalizedRoutes::url($name, $parameters);
        });
    }
}
