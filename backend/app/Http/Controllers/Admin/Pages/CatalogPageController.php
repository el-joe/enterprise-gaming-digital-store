<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ContentTranslationAdminService;
use App\Domains\Catalog\Services\ProductAdminReader;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Seo\Services\SeoMetadataService;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ProductForm;
use App\Http\Support\AdminTablePage;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Catalog pages (M15, spec Phase 9.2, Figma "Admin Catalog List" and "Admin Catalog Edit"):
 * products list and edit (variants, delivery fields, images, supplier mappings, translations,
 * SEO), the supplier mapping list with bulk mapping, and the media library. Pages only read;
 * forms post to the admin API.
 */
class CatalogPageController extends Controller
{
    public function __construct(private ProductAdminReader $reader) {}

    public function products(Request $request, AdminTablePage $tables): View
    {
        return view('admin.catalog.products', [
            'table' => $tables->make('products', $request->user()),
            'search' => (string) $request->query('q', ''),
        ]);
    }

    public function createProduct(ProductForm $form): Response
    {
        return $this->productPage(null, $form, [], []);
    }

    public function product(Product $product, ProductForm $form, ContentTranslationAdminService $translations, SeoMetadataService $seo): Response
    {
        $this->reader->forEditor($product);

        return $this->productPage($product, $form, $translations->values($product), $seo->values($product));
    }

    public function mappings(Request $request, AdminTablePage $tables): View
    {
        return view('admin.catalog.mappings', [
            'table' => $tables->make('mappings', $request->user()),
            'suppliers' => Options::suppliers(),
        ]);
    }

    public function supplierCatalog(Request $request, AdminTablePage $tables): View
    {
        $user = $request->user();

        return view('admin.catalog.supplier-catalog', [
            'table' => $tables->make('supplier-catalog', $user),
            'canAdd' => $user->can('catalog.manage') && $user->can('mappings.manage'),
            'canCreateCategories' => $user->can('catalog.manage'),
            'categories' => $this->optionsOrNone(static fn (): array => Options::categories()),
            'platforms' => $this->optionsOrNone(static fn (): array => Options::platforms()),
        ]);
    }

    /**
     * Select options that cannot break the page: a missing table (an environment not yet
     * migrated) gives no options instead of an error.
     *
     * @param  \Closure(): array<int, string>  $read
     * @return array<int, string>
     */
    private function optionsOrNone(\Closure $read): array
    {
        try {
            return $read();
        } catch (QueryException $exception) {
            report($exception);

            return [];
        }
    }

    public function media(Request $request, AdminTablePage $tables): View
    {
        return view('admin.content.media', [
            'table' => $tables->make('media', $request->user()),
            'maxKilobytes' => (int) config('catalog.media.max_upload_kilobytes'),
        ]);
    }

    /**
     * @param  array<string, array<string, string>>  $translationValues
     * @param  array<string, array<string, mixed>>  $seoValues
     */
    private function productPage(?Product $product, ProductForm $form, array $translationValues, array $seoValues): Response
    {
        return response()->view('admin.catalog.product', [
            'product' => $product,
            'groups' => $form->product($product),
            'variantFields' => $form->variant(),
            'definitions' => $this->reader->deliveryFieldDefinitions(),
            'mappings' => $this->reader->mappings($product),
            'suppliers' => Options::suppliers(),
            'mediaUrls' => app(MediaUrls::class),
            'translationValues' => $translationValues,
            'seoValues' => $seoValues,
        ])->header('Cache-Control', 'no-store, private');
    }
}
