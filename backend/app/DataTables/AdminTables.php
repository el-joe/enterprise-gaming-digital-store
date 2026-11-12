<?php

namespace App\DataTables;

use App\DataTables\Automation\AiSuggestionsDataTable;
use App\DataTables\Automation\AutomationCredentialsDataTable;
use App\DataTables\Automation\AutomationWorkflowsDataTable;
use App\DataTables\Automation\McpActionsDataTable;
use App\DataTables\Automation\McpTokensDataTable;
use App\DataTables\Catalog\AttributesDataTable;
use App\DataTables\Catalog\CategoriesDataTable;
use App\DataTables\Catalog\DeliveryFieldsDataTable;
use App\DataTables\Catalog\MappingsDataTable;
use App\DataTables\Catalog\PlatformsDataTable;
use App\DataTables\Catalog\ProductsDataTable;
use App\DataTables\Catalog\RegionsDataTable;
use App\DataTables\Catalog\SupplierCatalogDataTable;
use App\DataTables\Content\BlogCategoriesDataTable;
use App\DataTables\Content\BlogPostsDataTable;
use App\DataTables\Content\FaqGroupsDataTable;
use App\DataTables\Content\FaqsDataTable;
use App\DataTables\Content\MediaDataTable;
use App\DataTables\Content\MenusDataTable;
use App\DataTables\Content\PagesDataTable;
use App\DataTables\Content\RedirectsDataTable;
use App\DataTables\Customers\CustomersDataTable;
use App\DataTables\Customers\CustomerWalletDataTable;
use App\DataTables\Marketing\AffiliateCommissionsDataTable;
use App\DataTables\Marketing\AffiliatesDataTable;
use App\DataTables\Marketing\BannersDataTable;
use App\DataTables\Marketing\CouponsDataTable;
use App\DataTables\Marketing\HomepageSectionsDataTable;
use App\DataTables\Marketing\PromotionsDataTable;
use App\DataTables\Orders\OrdersDataTable;
use App\DataTables\Payments\DisputesDataTable;
use App\DataTables\Payments\PaymentsDataTable;
use App\DataTables\Payments\ReconciliationItemsDataTable;
use App\DataTables\Payments\RefundsDataTable;
use App\DataTables\Payments\WebhooksDataTable;
use App\DataTables\Pricing\CountriesDataTable;
use App\DataTables\Pricing\CurrenciesDataTable;
use App\DataTables\Pricing\ExchangeRatesDataTable;
use App\DataTables\Pricing\PricingRulesDataTable;
use App\DataTables\Pricing\VariantPricesDataTable;
use App\DataTables\Suppliers\SupplierApiLogsDataTable;
use App\DataTables\Suppliers\SupplierMappingsDataTable;
use App\DataTables\Suppliers\SuppliersDataTable;
use App\DataTables\Support\RefundRequestsDataTable;
use App\DataTables\Support\ReviewsDataTable;
use App\DataTables\Support\TicketsDataTable;
use App\DataTables\Users\StaffDataTable;
use App\Domains\Identity\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Every admin table by key (M14, M15). routes/admin.php registers the data, export, state and saved
 * filter routes of each table with the table's own permission.
 */
final class AdminTables
{
    /** @var list<class-string<AdminDataTable>> */
    private const array TABLES = [
        OrdersDataTable::class,
        SuppliersDataTable::class,
        SupplierApiLogsDataTable::class,
        SupplierMappingsDataTable::class,
        PaymentsDataTable::class,
        WebhooksDataTable::class,
        RefundsDataTable::class,
        DisputesDataTable::class,
        ReconciliationItemsDataTable::class,
        // M15
        AiSuggestionsDataTable::class,
        McpActionsDataTable::class,
        McpTokensDataTable::class,
        AutomationCredentialsDataTable::class,
        AutomationWorkflowsDataTable::class,
        StaffDataTable::class,
        TicketsDataTable::class,
        RefundRequestsDataTable::class,
        ReviewsDataTable::class,
        CustomersDataTable::class,
        CustomerWalletDataTable::class,
        ProductsDataTable::class,
        MappingsDataTable::class,
        SupplierCatalogDataTable::class,
        MediaDataTable::class,
        PlatformsDataTable::class,
        CategoriesDataTable::class,
        RegionsDataTable::class,
        AttributesDataTable::class,
        DeliveryFieldsDataTable::class,
        PricingRulesDataTable::class,
        VariantPricesDataTable::class,
        CurrenciesDataTable::class,
        CountriesDataTable::class,
        ExchangeRatesDataTable::class,
        CouponsDataTable::class,
        PromotionsDataTable::class,
        AffiliatesDataTable::class,
        AffiliateCommissionsDataTable::class,
        BannersDataTable::class,
        HomepageSectionsDataTable::class,
        PagesDataTable::class,
        BlogPostsDataTable::class,
        BlogCategoriesDataTable::class,
        FaqsDataTable::class,
        FaqGroupsDataTable::class,
        MenusDataTable::class,
        RedirectsDataTable::class,
    ];

    /**
     * @return array<string, class-string<AdminDataTable>>
     */
    public static function all(): array
    {
        $tables = [];

        foreach (self::TABLES as $class) {
            $tables[$class::key()] = $class;
        }

        return $tables;
    }

    public static function make(string $key, User $viewer): AdminDataTable
    {
        $class = self::all()[$key] ?? throw new NotFoundHttpException;

        return new $class($viewer);
    }

    /**
     * The table key of the current admin table route (`admin.api.tables.{key}.*`).
     */
    public static function keyOfRoute(?string $routeName): string
    {
        $key = explode('.', (string) $routeName)[3] ?? '';

        return array_key_exists($key, self::all()) ? $key : throw new NotFoundHttpException;
    }
}
