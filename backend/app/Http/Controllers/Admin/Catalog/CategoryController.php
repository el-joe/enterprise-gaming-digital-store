<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Services\CategoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Catalog\SaveCategoryRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Resources\Admin\Catalog\CategoryResource;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), CategoryResource::class);
    }

    public function store(SaveCategoryRequest $request): JsonResponse
    {
        return $this->success(CategoryResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->success(CategoryResource::make($this->service->withRelations($category))->resolve());
    }

    public function update(SaveCategoryRequest $request, Category $category): JsonResponse
    {
        return $this->success(CategoryResource::make($this->service->update($category, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->service->delete($category);

        return $this->success(message: 'Deleted.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->ids());

        return $this->success(message: 'Reordered.');
    }
}
