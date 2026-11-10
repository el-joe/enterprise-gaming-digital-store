<?php

namespace App\Http\Controllers\Admin\Promotions;

use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Services\CouponService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Promotions\SaveCouponRequest;
use App\Http\Resources\Admin\Promotions\CouponResource;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    public function __construct(private CouponService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), CouponResource::class);
    }

    public function store(SaveCouponRequest $request): JsonResponse
    {
        return $this->success(CouponResource::make($this->service->create($request->validated()))->resolve(), 'Created.', status: 201);
    }

    public function show(Coupon $coupon): JsonResponse
    {
        return $this->success(CouponResource::make($coupon)->resolve());
    }

    public function update(SaveCouponRequest $request, Coupon $coupon): JsonResponse
    {
        return $this->success(CouponResource::make($this->service->update($coupon, $request->validated()))->resolve(), 'Saved.');
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $this->service->delete($coupon);

        return $this->success(message: 'Deleted.');
    }
}
