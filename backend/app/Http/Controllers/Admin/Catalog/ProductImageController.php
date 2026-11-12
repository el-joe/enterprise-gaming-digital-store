<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Services\ProductImageService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Catalog\SaveProductImageRequest;
use App\Http\Resources\Admin\Catalog\ProductImageResource;
use Illuminate\Http\JsonResponse;

class ProductImageController extends Controller
{
    public function __construct(private ProductImageService $service) {}

    public function index(Product $product, AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->listFor($product, $request->toListQuery()), ProductImageResource::class);
    }

    public function store(Product $product, SaveProductImageRequest $request): JsonResponse
    {
        return $this->success(ProductImageResource::make($this->service->withRelations($this->service->addByStaff($product, $request->validated())))->resolve(), 'Created.', status: 201);
    }

    public function primary(Product $product, ProductImage $productImage): JsonResponse
    {
        $this->service->makePrimary($product, $productImage);

        return $this->success(message: 'Saved.');
    }

    public function show(Product $product, ProductImage $productImage): JsonResponse
    {
        return $this->success(ProductImageResource::make($this->service->withRelations($productImage))->resolve());
    }

    public function update(Product $product, SaveProductImageRequest $request, ProductImage $productImage): JsonResponse
    {
        return $this->success(ProductImageResource::make($this->service->update($productImage, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Product $product, ProductImage $productImage): JsonResponse
    {
        $this->service->delete($productImage);

        return $this->success(message: 'Deleted.');
    }
}
