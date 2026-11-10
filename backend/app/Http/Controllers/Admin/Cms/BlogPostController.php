<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Services\BlogPostService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\SaveBlogPostRequest;
use App\Http\Resources\Admin\Cms\BlogPostResource;
use Illuminate\Http\JsonResponse;

class BlogPostController extends Controller
{
    public function __construct(private BlogPostService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), BlogPostResource::class);
    }

    public function store(SaveBlogPostRequest $request): JsonResponse
    {
        return $this->success(BlogPostResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(BlogPost $blogPost): JsonResponse
    {
        return $this->success(BlogPostResource::make($this->service->withRelations($blogPost))->resolve());
    }

    public function update(SaveBlogPostRequest $request, BlogPost $blogPost): JsonResponse
    {
        return $this->success(BlogPostResource::make($this->service->update($blogPost, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(BlogPost $blogPost): JsonResponse
    {
        $this->service->delete($blogPost);

        return $this->success(message: 'Deleted.');
    }
}
