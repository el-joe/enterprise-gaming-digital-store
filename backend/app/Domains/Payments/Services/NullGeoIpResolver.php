<?php

namespace App\Domains\Payments\Services;

use App\Domains\Payments\Contracts\GeoIpResolver;
use Illuminate\Http\Request;

/**
 * Default GeoIP binding: the IP country is unknown. Rules that require an IP country (Paymob's
 * seeded rule) therefore never match, which keeps them fail-closed until a resolver is set up.
 */
class NullGeoIpResolver implements GeoIpResolver
{
    public function countryFor(Request $request): ?string
    {
        return null;
    }
}
