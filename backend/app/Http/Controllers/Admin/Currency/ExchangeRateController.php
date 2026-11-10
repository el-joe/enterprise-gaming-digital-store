<?php

namespace App\Http\Controllers\Admin\Currency;

use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Services\ExchangeRateAdminService;
use App\Domains\Currency\Services\ExchangeRateUpdater;
use App\Domains\Shared\ValueObjects\Rate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Currency\RateDecisionRequest;
use App\Http\Requests\Admin\Currency\SetRateOverrideRequest;
use App\Http\Resources\Admin\Currency\ExchangeRateHistoryResource;
use App\Http\Resources\Admin\Currency\ExchangeRateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class ExchangeRateController extends Controller
{
    public function __construct(private ExchangeRateAdminService $service) {}

    public function index(AdminListRequest $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->toListQuery()), ExchangeRateResource::class);
    }

    public function history(AdminListRequest $request, Currency $currency): JsonResponse
    {
        return $this->paginated($this->service->historyFor($currency, $request->toListQuery()), ExchangeRateHistoryResource::class);
    }

    public function fetchNow(ExchangeRateUpdater $updater): JsonResponse
    {
        return $this->success($updater->refresh()->summary(), 'Rates fetched.');
    }

    public function override(SetRateOverrideRequest $request, Currency $currency): JsonResponse
    {
        $expiresAt = $request->validated('expires_at');

        $rate = $this->service->override(
            $currency,
            Rate::of($request->validated('rate')),
            $expiresAt === null ? null : Date::parse($expiresAt),
            $request->validated('reason'),
            $request->user(),
        );

        return $this->success(ExchangeRateResource::make($rate)->resolve(), 'Override applied.');
    }

    public function clearOverride(RateDecisionRequest $request, Currency $currency): JsonResponse
    {
        $this->service->clearOverride($currency, $request->validated('reason'), $request->user());

        return $this->success(message: 'Override removed.');
    }

    public function approvePending(RateDecisionRequest $request, Currency $currency): JsonResponse
    {
        return $this->success(ExchangeRateResource::make($this->service->approvePending($currency, $request->validated('reason'), $request->user()))->resolve(), 'Rate approved.');
    }

    public function rejectPending(RateDecisionRequest $request, Currency $currency): JsonResponse
    {
        return $this->success(ExchangeRateResource::make($this->service->rejectPending($currency, $request->validated('reason')))->resolve(), 'Rate rejected.');
    }
}
