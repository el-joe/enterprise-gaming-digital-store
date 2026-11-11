<?php

namespace App\Http\Support\AdminScreens;

use App\Domains\Identity\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Every declarative admin screen by key (M15). routes/admin_pages.php registers the list,
 * create and edit routes of each with the screen's own permissions.
 */
final class ResourceScreens
{
    /** @var list<class-string<ResourceScreen>> */
    private const array SCREENS = [
        Screens\PlatformScreen::class,
        Screens\CategoryScreen::class,
        Screens\RegionScreen::class,
        Screens\AttributeScreen::class,
        Screens\DeliveryFieldScreen::class,
        Screens\PricingRuleScreen::class,
        Screens\VariantPriceScreen::class,
        Screens\CurrencyScreen::class,
        Screens\CountryScreen::class,
        Screens\CouponScreen::class,
        Screens\PromotionScreen::class,
        Screens\AffiliateScreen::class,
        Screens\BannerScreen::class,
        Screens\HomepageSectionScreen::class,
        Screens\PageScreen::class,
        Screens\BlogPostScreen::class,
        Screens\BlogCategoryScreen::class,
        Screens\FaqScreen::class,
        Screens\FaqGroupScreen::class,
        Screens\MenuScreen::class,
        Screens\RedirectScreen::class,
    ];

    /**
     * @return array<string, class-string<ResourceScreen>>
     */
    public static function all(): array
    {
        $screens = [];

        foreach (self::SCREENS as $class) {
            $screens[$class::key()] = $class;
        }

        return $screens;
    }

    public static function make(string $key, User $viewer): ResourceScreen
    {
        $class = self::all()[$key] ?? throw new NotFoundHttpException;

        return new $class($viewer);
    }
}
