<?php

namespace App\Domains\Payments\Contracts;

use Illuminate\Http\Request;

/**
 * IP country for eligibility and risk (ADR-016). Returns an ISO 3166-1 alpha-2 code, or null
 * when the country is unknown. Null never satisfies a rule that names IP countries.
 */
interface GeoIpResolver
{
    public function countryFor(Request $request): ?string;

    /**
     * The request header the country is read from, or null when it does not come from a header.
     * A publicly cached response priced by country (OD-1) must `Vary` on it.
     */
    public function sourceHeader(): ?string;
}
