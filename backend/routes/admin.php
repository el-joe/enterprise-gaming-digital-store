<?php

use App\Http\Controllers\Admin\Catalog\AttributeDefinitionController;
use App\Http\Controllers\Admin\Catalog\CategoryController;
use App\Http\Controllers\Admin\Catalog\DeliveryFieldDefinitionController;
use App\Http\Controllers\Admin\Catalog\GamingPlatformController;
use App\Http\Controllers\Admin\Catalog\ProductController;
use App\Http\Controllers\Admin\Catalog\ProductImageController;
use App\Http\Controllers\Admin\Catalog\ProductVariantController;
use App\Http\Controllers\Admin\Catalog\RegionController;
use App\Http\Controllers\Admin\Cms\BannerController;
use App\Http\Controllers\Admin\Cms\BlogCategoryController;
use App\Http\Controllers\Admin\Cms\BlogPostController;
use App\Http\Controllers\Admin\Cms\FaqController;
use App\Http\Controllers\Admin\Cms\FaqGroupController;
use App\Http\Controllers\Admin\Cms\HomepageSectionController;
use App\Http\Controllers\Admin\Cms\MediaController;
use App\Http\Controllers\Admin\Cms\MenuController;
use App\Http\Controllers\Admin\Cms\MenuItemController;
use App\Http\Controllers\Admin\Cms\PageController;
use App\Http\Controllers\Admin\Currency\CurrencyController;
use App\Http\Controllers\Admin\Currency\ExchangeRateController;
use App\Http\Controllers\Admin\Fulfillment\DigitalCodeController;
use App\Http\Controllers\Admin\Fulfillment\ManualTaskController;
use App\Http\Controllers\Admin\Fulfillment\OrderFulfilmentController;
use App\Http\Controllers\Admin\Pricing\PriceSimulationController;
use App\Http\Controllers\Admin\Pricing\PricingRuleController;
use App\Http\Controllers\Admin\Pricing\VariantPriceController;
use App\Http\Controllers\Admin\Seo\RedirectController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\Suppliers\SupplierController;
use App\Http\Controllers\Admin\Suppliers\SupplierMappingController;
use Illuminate\Support\Facades\Route;

Route::get('/me', [SessionController::class, 'show'])->name('me');

/**
 * Register a read and a write permission on a resource controller's routes.
 *
 * @param  array<string, string>  $parameters
 */
$crud = static function (string $uri, string $controller, string $view, string $manage, array $parameters = []): void {
    Route::apiResource($uri, $controller)->parameters($parameters)
        ->only(['index', 'show'])
        ->middleware("permission:{$view}");

    Route::apiResource($uri, $controller)->parameters($parameters)
        ->only(['store', 'update', 'destroy'])
        ->middleware("permission:{$manage}");
};

$reorder = static function (string $uri, string $controller, string $permission): void {
    Route::post("{$uri}/reorder", [$controller, 'reorder'])->middleware("permission:{$permission}")->name(str_replace('/', '.', $uri).'.reorder');
};

// Catalog
$reorder('platforms', GamingPlatformController::class, 'catalog.manage');
$crud('platforms', GamingPlatformController::class, 'catalog.view', 'catalog.manage', ['platforms' => 'gamingPlatform']);
$reorder('categories', CategoryController::class, 'catalog.manage');
$crud('categories', CategoryController::class, 'catalog.view', 'catalog.manage');
$crud('regions', RegionController::class, 'catalog.view', 'catalog.manage');
$crud('attributes', AttributeDefinitionController::class, 'catalog.view', 'catalog.manage', ['attributes' => 'attributeDefinition']);
$crud('delivery-fields', DeliveryFieldDefinitionController::class, 'catalog.view', 'catalog.manage', ['delivery-fields' => 'deliveryFieldDefinition']);

Route::post('products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
Route::post('products/{product}/activate', [ProductController::class, 'activate'])->middleware('permission:catalog.publish')->name('products.activate');
Route::post('products/{product}/deactivate', [ProductController::class, 'deactivate'])->middleware('permission:catalog.publish')->name('products.deactivate');
$crud('products', ProductController::class, 'catalog.view', 'catalog.manage');

Route::scopeBindings()->group(static function () use ($crud): void {
    $crud('products.variants', ProductVariantController::class, 'catalog.view', 'catalog.manage', ['products' => 'product', 'variants' => 'productVariant']);
    $crud('products.images', ProductImageController::class, 'catalog.view', 'catalog.manage', ['products' => 'product', 'images' => 'productImage']);
    $crud('menus.items', MenuItemController::class, 'content.menus', 'content.menus', ['items' => 'menuItem']);
});

// Content
$crud('pages', PageController::class, 'content.pages', 'content.pages');
$crud('blog-categories', BlogCategoryController::class, 'content.blog', 'content.blog', ['blog-categories' => 'blogCategory']);
$crud('blog-posts', BlogPostController::class, 'content.blog', 'content.blog', ['blog-posts' => 'blogPost']);
$reorder('faq-groups', FaqGroupController::class, 'content.faq');
$crud('faq-groups', FaqGroupController::class, 'content.faq', 'content.faq', ['faq-groups' => 'faqGroup']);
$reorder('faqs', FaqController::class, 'content.faq');
$crud('faqs', FaqController::class, 'content.faq', 'content.faq');
$crud('menus', MenuController::class, 'content.menus', 'content.menus');
$reorder('homepage-sections', HomepageSectionController::class, 'content.homepage');
Route::put('homepage-sections/{homepageSection}/items', [HomepageSectionController::class, 'replaceItems'])->middleware('permission:content.homepage')->name('homepage-sections.items');
$crud('homepage-sections', HomepageSectionController::class, 'content.homepage', 'content.homepage', ['homepage-sections' => 'homepageSection']);
$reorder('banners', BannerController::class, 'marketing.banners');
$crud('banners', BannerController::class, 'marketing.banners', 'marketing.banners');
$crud('redirects', RedirectController::class, 'seo.redirects', 'seo.redirects');
$crud('media', MediaController::class, 'content.media', 'content.media', ['media' => 'media']);

// Currency
Route::get('currencies', [CurrencyController::class, 'index'])->middleware('permission:pricing.view|currency.manage')->name('currencies.index');
Route::get('currencies/{currency}', [CurrencyController::class, 'show'])->middleware('permission:pricing.view|currency.manage')->name('currencies.show');
Route::patch('currencies/{currency}', [CurrencyController::class, 'update'])->middleware('permission:currency.manage')->name('currencies.update');

Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->middleware('permission:pricing.view|currency.manage')->name('exchange-rates.index');
Route::get('exchange-rates/{currency}/history', [ExchangeRateController::class, 'history'])->middleware('permission:pricing.view|currency.manage')->name('exchange-rates.history');

Route::middleware('permission:currency.manage')->group(static function (): void {
    Route::post('exchange-rates/fetch-now', [ExchangeRateController::class, 'fetchNow'])->middleware('throttle:6,1')->name('exchange-rates.fetch-now');
    Route::post('exchange-rates/{currency}/override', [ExchangeRateController::class, 'override'])->name('exchange-rates.override');
    Route::delete('exchange-rates/{currency}/override', [ExchangeRateController::class, 'clearOverride'])->name('exchange-rates.override.clear');
    Route::post('exchange-rates/{currency}/approve-pending', [ExchangeRateController::class, 'approvePending'])->name('exchange-rates.approve-pending');
    Route::post('exchange-rates/{currency}/reject-pending', [ExchangeRateController::class, 'rejectPending'])->name('exchange-rates.reject-pending');
});

// Pricing
Route::post('pricing-rules/simulate', [PriceSimulationController::class, 'store'])->middleware('permission:pricing.view')->name('pricing-rules.simulate');
$crud('pricing-rules', PricingRuleController::class, 'pricing.view', 'pricing.manage', ['pricing-rules' => 'pricingRule']);
$crud('variant-prices', VariantPriceController::class, 'pricing.view', 'pricing.manage', ['variant-prices' => 'variantPrice']);

// Suppliers (M8)
Route::middleware('permission:suppliers.view')->group(static function (): void {
    Route::get('suppliers/{supplier}/capabilities', [SupplierController::class, 'capabilities'])->name('suppliers.capabilities');
    Route::get('suppliers/{supplier}/balance', [SupplierController::class, 'balance'])->name('suppliers.balance');
});
Route::get('suppliers/{supplier}/logs', [SupplierController::class, 'logs'])->middleware('permission:suppliers.logs')->name('suppliers.logs');
Route::post('suppliers/{supplier}/credentials', [SupplierController::class, 'credentials'])->middleware('permission:suppliers.credentials')->name('suppliers.credentials');
Route::post('suppliers/{supplier}/test-connection', [SupplierController::class, 'testConnection'])->middleware(['permission:suppliers.manage', 'throttle:6,1'])->name('suppliers.test-connection');
Route::middleware(['permission:suppliers.sync', 'throttle:6,1'])->group(static function (): void {
    Route::post('suppliers/{supplier}/sync-catalog', [SupplierController::class, 'syncCatalog'])->name('suppliers.sync-catalog');
    Route::post('suppliers/{supplier}/sync-pricing', [SupplierController::class, 'syncPricing'])->name('suppliers.sync-pricing');
});
$crud('suppliers', SupplierController::class, 'suppliers.view', 'suppliers.manage');
Route::get('supplier-mappings', [SupplierMappingController::class, 'index'])->middleware('permission:suppliers.view|mappings.manage')->name('supplier-mappings.index');
Route::get('supplier-mappings/{supplierMapping}', [SupplierMappingController::class, 'show'])->middleware('permission:suppliers.view|mappings.manage')->name('supplier-mappings.show');
Route::middleware('permission:mappings.manage')->group(static function (): void {
    Route::post('supplier-mappings', [SupplierMappingController::class, 'store'])->name('supplier-mappings.store');
    Route::patch('supplier-mappings/{supplierMapping}', [SupplierMappingController::class, 'update'])->name('supplier-mappings.update');
    Route::delete('supplier-mappings/{supplierMapping}', [SupplierMappingController::class, 'destroy'])->name('supplier-mappings.destroy');
});

// Fulfilment (M8, M9)
Route::get('orders/{order}/timeline', [OrderFulfilmentController::class, 'timeline'])->middleware('permission:orders.view')->name('orders.timeline');
Route::post('orders/{order}/review', [OrderFulfilmentController::class, 'review'])->middleware('permission:orders.review')->name('orders.review');
Route::post('orders/{order}/retry-verified-unit', [OrderFulfilmentController::class, 'retryVerifiedUnit'])->middleware('permission:orders.review')->name('orders.retry-verified-unit');
Route::post('orders/{order}/reconcile', [OrderFulfilmentController::class, 'reconcile'])->middleware(['permission:orders.reconcile', 'throttle:12,1'])->name('orders.reconcile');
Route::post('transactions/{transaction}/mark-not-accepted', [OrderFulfilmentController::class, 'markNotAccepted'])->middleware('permission:orders.reconcile')->name('transactions.mark-not-accepted');

Route::middleware('permission:orders.fulfill_manual')->group(static function (): void {
    Route::get('manual-tasks', [ManualTaskController::class, 'index'])->name('manual-tasks.index');
    Route::get('manual-tasks/{manualTask}', [ManualTaskController::class, 'show'])->name('manual-tasks.show');
    Route::post('manual-tasks/{manualTask}/claim', [ManualTaskController::class, 'claim'])->name('manual-tasks.claim');
    Route::post('manual-tasks/{manualTask}/complete', [ManualTaskController::class, 'complete'])->name('manual-tasks.complete');
    Route::post('manual-tasks/{manualTask}/reject', [ManualTaskController::class, 'reject'])->name('manual-tasks.reject');
});

Route::post('digital-codes/import', [DigitalCodeController::class, 'import'])->middleware('permission:digital_codes.import')->name('digital-codes.import');
Route::post('digital-codes/{digitalCode}/reveal', [DigitalCodeController::class, 'reveal'])->middleware(['permission:digital_codes.reveal', 'throttle:code-reveal'])->name('digital-codes.reveal');
