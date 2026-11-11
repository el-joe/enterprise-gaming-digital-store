<?php

namespace App\Http\Controllers\Admin\Currency;

use App\Domains\Currency\Services\CountryCurrencyService;
use App\Domains\Shared\Models\Country;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Currency\UpdateCountryRequest;
use Illuminate\Http\JsonResponse;

/**
 * Country display currency (M15, OD-1; `currency.manage`).
 */
class CountryController extends Controller
{
    public function update(UpdateCountryRequest $request, Country $country, CountryCurrencyService $service): JsonResponse
    {
        $country = $service->update($country, $request->validated());

        return $this->success(['code' => $country->code, 'default_currency' => $country->default_currency, 'is_active' => $country->is_active], 'Saved.');
    }
}
