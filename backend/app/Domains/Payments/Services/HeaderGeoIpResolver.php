<?php

namespace App\Domains\Payments\Services;

use App\Domains\Payments\Contracts\GeoIpResolver;
use Illuminate\Http\Request;

/**
 * Reads the country an edge proxy put on the request (Cloudflare's `CF-IPCountry` by default).
 * Only safe when the origin accepts traffic from that proxy alone: otherwise a client can send
 * the header itself. Cloudflare's `XX` (unknown) and `T1` (Tor) give null.
 */
class HeaderGeoIpResolver implements GeoIpResolver
{
    public function __construct(private string $header) {}

    public function countryFor(Request $request): ?string
    {
        $value = strtoupper(trim((string) $request->header($this->header)));

        if (preg_match('/^[A-Z]{2}$/', $value) !== 1 || in_array($value, ['XX', 'T1'], true)) {
            return null;
        }

        return $value;
    }
}
