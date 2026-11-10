<?php

namespace App\Http\Controllers\Mobile;

use App\Domains\Settings\Services\AppConfigService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\AppConfigRequest;
use App\Http\Resources\Mobile\AppConfigResource;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    public function show(AppConfigRequest $request, AppConfigService $config): JsonResponse
    {
        $built = $config->build($request->validated('platform'), $request->validated('version'));

        return $this->success(['config' => (new AppConfigResource($built))->resolve($request)]);
    }
}
