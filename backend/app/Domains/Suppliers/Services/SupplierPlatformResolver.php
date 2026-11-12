<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Services\GamingPlatformService;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Suppliers\Models\SupplierProduct;
use Illuminate\Support\Str;

/**
 * Store platforms from the supplier's brand (owner decision, mirrors SupplierCategoryResolver).
 * The brand is `brand_name`, else `raw.brand.brandName`. An existing platform whose name matches
 * ignoring case and spaces (or a known alias, or its slug) is reused; otherwise one is created
 * through GamingPlatformService (audited `catalog.platform.created`): the brand name as given,
 * active, a free slug, no logo (BD8: the supplier's logo URL is never read or stored). The unique
 * slug refuses a concurrent duplicate, after which the winner's row is read.
 */
class SupplierPlatformResolver
{
    /** Normalised brand => normalised platform name. */
    private const array ALIASES = [
        'psn' => 'playstation',
        'playstationnetwork' => 'playstation',
        'playstationstore' => 'playstation',
        'xboxlive' => 'xbox',
        'xboxgamepass' => 'xbox',
        'nintendoeshop' => 'nintendo',
        'steamwallet' => 'steam',
    ];

    public function __construct(private GamingPlatformService $platforms) {}

    public static function brandOf(SupplierProduct $product): ?string
    {
        $brand = $product->brand_name ?? data_get(is_array($product->raw) ? $product->raw : [], 'brand.brandName');

        return is_string($brand) && trim($brand) !== '' ? trim($brand) : null;
    }

    public function resolve(string $brand): ?GamingPlatform
    {
        $brand = trim(Str::limit(trim($brand), 120, ''));

        if ($brand === '') {
            return null;
        }

        $existing = $this->find($brand);

        if ($existing !== null) {
            return $existing;
        }

        $slug = $this->freeSlug($brand);

        try {
            return $this->platforms->create(['name' => $brand, 'slug' => $slug, 'is_active' => true]);
        } catch (ApplicationException) {
            return $this->find($brand, lock: true) ?? GamingPlatform::query()->where('slug', $slug)->lockForUpdate()->firstOrFail();
        }
    }

    private function find(string $brand, bool $lock = false): ?GamingPlatform
    {
        $key = self::normalise($brand);
        $names = array_values(array_unique([$key, self::ALIASES[$key] ?? $key]));

        return GamingPlatform::query()
            ->where(static function ($query) use ($names, $brand): void {
                $query->whereRaw("REPLACE(LOWER(name), ' ', '') IN (".implode(',', array_fill(0, count($names), '?')).')', $names)
                    ->orWhere('slug', Str::slug($brand));
            })
            ->when($lock, static fn ($query) => $query->lockForUpdate())
            ->orderByRaw('slug = ? desc', [Str::slug($brand)])->orderBy('id')->first();
    }

    private static function normalise(string $value): string
    {
        return (string) preg_replace('/\s+/u', '', Str::lower($value));
    }

    private function freeSlug(string $brand): string
    {
        $base = Str::limit(Str::slug($brand), 130, '') ?: 'platform';

        for ($suffix = 1; $suffix < 50; $suffix++) {
            $slug = $suffix === 1 ? $base : $base.'-'.$suffix;

            if (! GamingPlatform::withTrashed()->where('slug', $slug)->exists()) {
                return $slug;
            }
        }

        return $base.'-'.Str::lower(Str::random(6));
    }
}
