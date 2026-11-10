<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Services\MediaService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Cms\UpdateMediaRequest;
use App\Http\Requests\Admin\Cms\UploadMediaRequest;
use App\Http\Resources\Admin\Cms\MediaResource;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    public function __construct(private MediaService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), MediaResource::class);
    }

    public function store(UploadMediaRequest $request): JsonResponse
    {
        $media = $this->service->upload($request->file('file'), $request->safe()->except('file'));

        return $this->success(MediaResource::make($media)->resolve(), 'Uploaded.', status: 201);
    }

    public function show(Media $media): JsonResponse
    {
        return $this->success(MediaResource::make($media)->resolve());
    }

    public function update(UpdateMediaRequest $request, Media $media): JsonResponse
    {
        return $this->success(MediaResource::make($this->service->update($media, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->service->delete($media);

        return $this->success(message: 'Deleted.');
    }
}
