<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Shared\Models\Country;
use App\Domains\Shared\Services\CountryDirectory;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Profile (`/account/profile`, M13): name, country, language and display currency, saved with
 * `PATCH /api/v1/account/profile`. The display currency is the explicit choice that the
 * DisplayCurrencyResolver honours (owner decision 1).
 */
class ProfileController extends Controller
{
    public function show(Request $request, CountryDirectory $countries, StorefrontCatalogService $catalog): View
    {
        return view('account.profile', [
            'profile' => (new ProfileResource($request->user()))->resolve($request),
            'countries' => $countries->active()->map(static fn (Country $country): array => ['code' => $country->code, 'name' => $countries->localizedName($country)])->sortBy('name')->values()->all(),
            'currencies' => $catalog->currencies(),
            'locales' => (array) config('app.supported_locales'),
        ]);
    }
}
