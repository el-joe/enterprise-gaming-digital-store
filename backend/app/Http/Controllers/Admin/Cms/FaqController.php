<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Services\FaqService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\SaveFaqRequest;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Resources\Admin\Cms\FaqResource;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function __construct(private FaqService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), FaqResource::class);
    }

    public function store(SaveFaqRequest $request): JsonResponse
    {
        return $this->success(FaqResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        return $this->success(FaqResource::make($this->service->withRelations($faq))->resolve());
    }

    public function update(SaveFaqRequest $request, Faq $faq): JsonResponse
    {
        return $this->success(FaqResource::make($this->service->update($faq, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $this->service->delete($faq);

        return $this->success(message: 'Deleted.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->ids());

        return $this->success(message: 'Reordered.');
    }
}
