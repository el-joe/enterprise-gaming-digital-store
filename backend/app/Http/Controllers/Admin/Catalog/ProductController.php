<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ProductPublicationService;
use App\Domains\Catalog\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Catalog\BulkProductRequest;
use App\Http\Requests\Admin\Catalog\SaveProductRequest;
use App\Http\Resources\Admin\Catalog\ProductResource;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(private ProductService $service, private ProductPublicationService $publication) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), ProductResource::class);
    }

    public function store(SaveProductRequest $request): JsonResponse
    {
        return $this->success(ProductResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->success(ProductResource::make($this->service->withRelations($product))->resolve());
    }

    public function update(SaveProductRequest $request, Product $product): JsonResponse
    {
        return $this->success(ProductResource::make($this->service->update($product, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->service->delete($product);

        return $this->success(message: 'Deleted.');
    }

    public function activate(Product $product): JsonResponse
    {
        return $this->success(ProductResource::make($this->publication->activate($product))->resolve(), 'Published.');
    }

    public function deactivate(Product $product): JsonResponse
    {
        return $this->success(ProductResource::make($this->publication->deactivate($product))->resolve(), 'Unpublished.');
    }

    public function bulk(BulkProductRequest $request): JsonResponse
    {
        return $this->success($this->publication->bulk($request->validated('action'), $request->validated('ids')), 'Done.');
    }
}
