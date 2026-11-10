<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\BlogCategory;
use App\Domains\Cms\Services\BlogCategoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\SaveBlogCategoryRequest;
use App\Http\Resources\Admin\Cms\BlogCategoryResource;
use Illuminate\Http\JsonResponse;

class BlogCategoryController extends Controller
{
    public function __construct(private BlogCategoryService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), BlogCategoryResource::class);
    }

    public function store(SaveBlogCategoryRequest $request): JsonResponse
    {
        return $this->success(BlogCategoryResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(BlogCategory $blogCategory): JsonResponse
    {
        return $this->success(BlogCategoryResource::make($this->service->withRelations($blogCategory))->resolve());
    }

    public function update(SaveBlogCategoryRequest $request, BlogCategory $blogCategory): JsonResponse
    {
        return $this->success(BlogCategoryResource::make($this->service->update($blogCategory, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(BlogCategory $blogCategory): JsonResponse
    {
        $this->service->delete($blogCategory);

        return $this->success(message: 'Deleted.');
    }
}
