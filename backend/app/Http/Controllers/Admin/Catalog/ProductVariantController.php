<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Services\ProductVariantService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Catalog\SaveProductVariantRequest;
use App\Http\Resources\Admin\Catalog\ProductVariantResource;
use Illuminate\Http\JsonResponse;

class ProductVariantController extends Controller
{
    public function __construct(private ProductVariantService $service) {}

    public function index(Product $product, AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->listFor($product, $request->toListQuery()), ProductVariantResource::class);
    }

    public function store(Product $product, SaveProductVariantRequest $request): JsonResponse
    {
        return $this->success(ProductVariantResource::make($this->service->createFor($product, $request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Product $product, ProductVariant $productVariant): JsonResponse
    {
        return $this->success(ProductVariantResource::make($this->service->withRelations($productVariant))->resolve());
    }

    public function update(Product $product, SaveProductVariantRequest $request, ProductVariant $productVariant): JsonResponse
    {
        return $this->success(ProductVariantResource::make($this->service->update($productVariant, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Product $product, ProductVariant $productVariant): JsonResponse
    {
        $this->service->delete($productVariant);

        return $this->success(message: 'Deleted.');
    }
}
