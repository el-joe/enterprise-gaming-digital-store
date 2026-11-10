<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Shared\Services\CountryDirectory;
use App\Http\Controllers\Controller;
use App\Http\Resources\Storefront\CountryResource;
use Collator;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    /**
     * The countries checkout accepts, sorted by their name in the request locale.
     */
    public function index(CountryDirectory $countries): JsonResponse
    {
        $rows = CountryResource::collection($countries->active())->resolve();
        $collator = class_exists(Collator::class) ? new Collator(app()->getLocale()) : null;
        usort($rows, static fn (array $a, array $b): int => $collator?->compare($a['name'], $b['name']) ?: strcmp($a['name'], $b['name']));

        return $this->success(['countries' => $rows]);
    }
}
