<?php

namespace App\Http\Controllers\Mcp;

use App\Domains\Mcp\Repositories\McpCatalogRepository;
use App\Domains\Mcp\Services\McpCatalogReader;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Http\Requests\Mcp\ListProductsRequest;
use App\Http\Requests\Mcp\LowMarginRequest;
use App\Http\Requests\Mcp\ProductPricingRequest;
use App\Http\Requests\Mcp\SearchProductsRequest;
use App\Http\Resources\Mcp\McpProductResource;
use Illuminate\Http\JsonResponse;

class ProductController extends McpController
{
    public function __construct(private McpCatalogRepository $catalog, private McpCatalogReader $reader) {}

    public function index(ListProductsRequest $request): JsonResponse
    {
        return $this->paginated($this->catalog->productsPage($request->filters(), $request->page(), $request->perPage()), McpProductResource::class);
    }

    public function search(SearchProductsRequest $request): JsonResponse
    {
        return $this->paginated($this->catalog->searchProducts((string) $request->validated('query'), $request->page(), $request->perPage()), McpProductResource::class);
    }

    public function show(string $idOrSku): JsonResponse
    {
        $product = $this->catalog->findProduct($idOrSku) ?? throw new ApplicationException('No product has this id or SKU.', 'not_found', 404);

        return $this->success(McpProductResource::make($product)->resolve());
    }

    public function pricing(ProductPricingRequest $request, string $variantSku): JsonResponse
    {
        return $this->success($this->reader->pricing($variantSku, $request->validated('country'), $request->validated('currency')));
    }

    public function availability(string $variantSku): JsonResponse
    {
        return $this->success($this->reader->availability($variantSku));
    }

    public function lowMargin(LowMarginRequest $request): JsonResponse
    {
        $result = $this->reader->lowMargin($request->thresholdPct(), $request->page(), $request->perPage());
        $page = $result['page'];

        return new JsonResponse([
            'success' => true,
            'message' => 'OK',
            'data' => $page->items(),
            'meta' => [
                'threshold_pct' => $result['threshold_pct'],
                'variants_scanned' => $result['scanned'],
                'variants_cost_unknown' => $result['cost_unknown'],
                'scan_truncated' => $result['truncated'],
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }
}
