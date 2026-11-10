<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class Controller
{
    /**
     * Build the standard success envelope. The message is an English source string translated
     * into the request locale (lang/ar.json, M-A3).
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $meta
     */
    protected function success(array $data = [], string $message = 'OK', array $meta = [], int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => __($message),
            'data' => (object) $data,
            'meta' => (object) $meta,
        ], $status);
    }

    /**
     * Build the success envelope for a page of results.
     *
     * @param  class-string<JsonResource>  $resource
     * @param  array<string, mixed>  $meta
     */
    protected function paginated(LengthAwarePaginator $page, string $resource, array $meta = []): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => 'OK',
            'data' => $resource::collection($page->getCollection())->resolve(),
            'meta' => (object) [
                ...$meta,
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }
}
