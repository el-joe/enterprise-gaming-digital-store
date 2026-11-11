<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminTablePage;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Exchange rates (M15, spec Phase 9.7): the current rates table with the spike guard's held
 * rates, manual overrides, "fetch now" and the override form (`currency.manage`).
 */
class PricingPageController extends Controller
{
    public function exchangeRates(Request $request, AdminTablePage $tables, ExchangeRateRegistry $rates): View
    {
        return view('admin.pricing.exchange-rates', [
            'table' => $tables->make('exchange-rates', $request->user()),
            'base' => $rates->baseCurrency(),
            'currencies' => Options::currencies(),
        ]);
    }
}
