<?php

namespace App\Http\Controllers\Admin\Currency;

use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Services\CurrencyAdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Currency\UpdateCurrencyRequest;
use App\Http\Resources\Admin\Currency\CurrencyResource;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyAdminService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), CurrencyResource::class);
    }

    public function show(Currency $currency): JsonResponse
    {
        return $this->success(CurrencyResource::make($currency)->resolve());
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency): JsonResponse
    {
        return $this->success(CurrencyResource::make($this->service->update($currency, $request->validated()))->resolve(), 'Saved.');
    }
}
