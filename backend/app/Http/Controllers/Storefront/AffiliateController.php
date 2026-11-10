<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Affiliates\Services\AffiliateTracker;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AffiliateClickRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    /**
     * Record an affiliate link visit. The browser keeps a random token in an encrypted, HTTP-only
     * cookie for the attribution window. The response is the same whether or not the code exists.
     */
    public function click(AffiliateClickRequest $request, AffiliateTracker $tracker): JsonResponse
    {
        $name = (string) config('affiliates.cookie');
        $token = $request->cookie($name);
        $token = is_string($token) && strlen($token) === 40 ? $token : Str::random(40);

        $tracker->recordClick($request->validated('code'), $token, $request->validated('landing_url'));

        return $this->success(message: 'Recorded.', status: 202)
            ->withCookie(cookie($name, $token, 60 * 24 * (int) config('affiliates.attribution_days'), httpOnly: true, sameSite: 'lax'));
    }
}
