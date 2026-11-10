<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Build the standard success envelope.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $meta
     */
    protected function success(array $data = [], string $message = 'OK', array $meta = [], int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => (object) $data,
            'meta' => (object) $meta,
        ], $status);
    }
}
