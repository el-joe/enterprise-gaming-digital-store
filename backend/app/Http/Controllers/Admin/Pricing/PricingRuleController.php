<?php

namespace App\Http\Controllers\Admin\Pricing;

use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Pricing\Services\PricingRuleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Pricing\SavePricingRuleRequest;
use App\Http\Resources\Admin\Pricing\PricingRuleResource;
use Illuminate\Http\JsonResponse;

class PricingRuleController extends Controller
{
    public function __construct(private PricingRuleService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), PricingRuleResource::class);
    }

    public function store(SavePricingRuleRequest $request): JsonResponse
    {
        return $this->success(PricingRuleResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(PricingRule $pricingRule): JsonResponse
    {
        return $this->success(PricingRuleResource::make($pricingRule)->resolve());
    }

    public function update(SavePricingRuleRequest $request, PricingRule $pricingRule): JsonResponse
    {
        return $this->success(PricingRuleResource::make($this->service->update($pricingRule, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(PricingRule $pricingRule): JsonResponse
    {
        $this->service->delete($pricingRule);

        return $this->success(message: 'Deleted.');
    }
}
