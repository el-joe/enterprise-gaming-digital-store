<?php

namespace App\Http\Controllers\Admin\Suppliers;

use App\Domains\Suppliers\Services\SupplierCatalogImporter;
use App\Domains\Suppliers\Services\SupplierCategoryResolver;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Suppliers\AddSupplierProductsToStoreRequest;
use Illuminate\Http\JsonResponse;

/**
 * "Add to store" from the supplier catalog: draft store products, variants and primary mappings.
 */
class SupplierCatalogController extends Controller
{
    public function __construct(private SupplierCatalogImporter $importer) {}

    public function addToStore(AddSupplierProductsToStoreRequest $request): JsonResponse
    {
        $platform = $request->validated('gaming_platform_id');
        $category = $request->validated('primary_category_id');
        $result = $this->importer->addToStore(
            array_map('intval', $request->validated('ids')),
            $category === null ? null : (int) $category,
            $platform === null ? null : (int) $platform,
            'admin:'.$request->user()->id,
        );

        $message = __('admin.supplier_catalog.result', ['added' => $result['added'], 'already' => $result['already'], 'skipped' => count($result['skipped'])]);

        if ($result['skipped'] !== []) {
            $message .= ' '.collect($result['skipped'])->map(static fn (array $item): string => $item['name'].': '.$item['reason'])->take(3)->implode('; ');
        }

        if ($result['inactive'] !== []) {
            $message .= ' '.__('admin.supplier_catalog.result_inactive', ['count' => count($result['inactive'])]);
        }

        return $this->success($result, $message, status: $result['added'] > 0 ? 201 : 200);
    }

    /**
     * Create the missing store categories from the supplier category names, without products.
     */
    public function createCategories(SupplierCategoryResolver $categories): JsonResponse
    {
        $counts = $categories->createAll();

        return $this->success($counts, __('admin.supplier_catalog.categories_result', $counts));
    }
}
