<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Domains\Seo\Models\Redirect;
use App\Domains\Seo\Services\RedirectService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Seo\SaveRedirectRequest;
use App\Http\Resources\Admin\Seo\RedirectResource;
use Illuminate\Http\JsonResponse;

class RedirectController extends Controller
{
    public function __construct(private RedirectService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), RedirectResource::class);
    }

    public function store(SaveRedirectRequest $request): JsonResponse
    {
        return $this->success(RedirectResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Redirect $redirect): JsonResponse
    {
        return $this->success(RedirectResource::make($this->service->withRelations($redirect))->resolve());
    }

    public function update(SaveRedirectRequest $request, Redirect $redirect): JsonResponse
    {
        return $this->success(RedirectResource::make($this->service->update($redirect, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Redirect $redirect): JsonResponse
    {
        $this->service->delete($redirect);

        return $this->success(message: 'Deleted.');
    }
}
