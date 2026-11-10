<?php

namespace App\Http\Controllers\Admin\Pricing;

use App\Domains\Pricing\Services\PriceSimulator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Pricing\SimulatePriceRequest;
use Illuminate\Http\JsonResponse;

class PriceSimulationController extends Controller
{
    /**
     * Show what the engine would charge, without changing anything.
     */
    public function store(SimulatePriceRequest $request, PriceSimulator $simulator): JsonResponse
    {
        return $this->success($simulator->simulate(
            $request->validated('variant'),
            $request->validated('country_code'),
            $request->validated('currency'),
        )->breakdown());
    }
}
