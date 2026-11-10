<?php

namespace App\Http\Controllers\Admin\Pricing;

use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Pricing\Services\VariantPriceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Pricing\SaveVariantPriceRequest;
use App\Http\Resources\Admin\Pricing\VariantPriceResource;
use Illuminate\Http\JsonResponse;

class VariantPriceController extends Controller
{
    public function __construct(private VariantPriceService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), VariantPriceResource::class);
    }

    public function store(SaveVariantPriceRequest $request): JsonResponse
    {
        return $this->success(VariantPriceResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(VariantPrice $variantPrice): JsonResponse
    {
        return $this->success(VariantPriceResource::make($variantPrice)->resolve());
    }

    public function update(SaveVariantPriceRequest $request, VariantPrice $variantPrice): JsonResponse
    {
        return $this->success(VariantPriceResource::make($this->service->update($variantPrice, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(VariantPrice $variantPrice): JsonResponse
    {
        $this->service->delete($variantPrice);

        return $this->success(message: 'Deleted.');
    }
}
