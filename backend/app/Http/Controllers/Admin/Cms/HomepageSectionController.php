<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Services\HomepageSectionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\ReplaceHomepageSectionItemsRequest;
use App\Http\Requests\Admin\Cms\SaveHomepageSectionRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Resources\Admin\Cms\HomepageSectionResource;
use Illuminate\Http\JsonResponse;

class HomepageSectionController extends Controller
{
    public function __construct(private HomepageSectionService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), HomepageSectionResource::class);
    }

    public function store(SaveHomepageSectionRequest $request): JsonResponse
    {
        return $this->success(HomepageSectionResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(HomepageSection $homepageSection): JsonResponse
    {
        return $this->success(HomepageSectionResource::make($this->service->withRelations($homepageSection))->resolve());
    }

    public function update(SaveHomepageSectionRequest $request, HomepageSection $homepageSection): JsonResponse
    {
        return $this->success(HomepageSectionResource::make($this->service->update($homepageSection, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(HomepageSection $homepageSection): JsonResponse
    {
        $this->service->delete($homepageSection);

        return $this->success(message: 'Deleted.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->ids());

        return $this->success(message: 'Reordered.');
    }

    public function replaceItems(ReplaceHomepageSectionItemsRequest $request, HomepageSection $homepageSection): JsonResponse
    {
        return $this->success(HomepageSectionResource::make($this->service->replaceItems($homepageSection, $request->validated('items')))->resolve(), 'Saved.');
    }
}
