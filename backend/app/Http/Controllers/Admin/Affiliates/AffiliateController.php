<?php

namespace App\Http\Controllers\Admin\Affiliates;

use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Affiliates\Services\AffiliateCommissionService;
use App\Domains\Affiliates\Services\AffiliateService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Affiliates\SaveAffiliateRequest;
use App\Http\Resources\Admin\Affiliates\AffiliateCommissionResource;
use App\Http\Resources\Admin\Affiliates\AffiliateResource;
use Illuminate\Http\JsonResponse;

class AffiliateController extends Controller
{
    public function __construct(private AffiliateService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), AffiliateResource::class);
    }

    public function store(SaveAffiliateRequest $request): JsonResponse
    {
        return $this->success(AffiliateResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Affiliate $affiliate): JsonResponse
    {
        return $this->success(AffiliateResource::make($this->service->withRelations($affiliate))->resolve());
    }

    public function update(SaveAffiliateRequest $request, Affiliate $affiliate): JsonResponse
    {
        return $this->success(AffiliateResource::make($this->service->update($affiliate, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Affiliate $affiliate): JsonResponse
    {
        $this->service->delete($affiliate);

        return $this->success(message: 'Deleted.');
    }

    public function commissions(AdminListRequest $request, Affiliate $affiliate): JsonResponse
    {
        $list = $request->toListQuery();

        return $this->paginated($this->service->commissions($affiliate, $list->filters['status'] ?? null, $list->page, $list->perPage), AffiliateCommissionResource::class);
    }

    public function approveCommission(AffiliateCommission $commission, AffiliateCommissionService $commissions): JsonResponse
    {
        return $this->success(AffiliateCommissionResource::make($commissions->approve($commission))->resolve(), 'Approved.');
    }
}
