<?php

namespace App\Http\Controllers\Mobile;

use App\Domains\Settings\Services\AppLinkAssociationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * `/.well-known/assetlinks.json` and `/.well-known/apple-app-site-association` (M-A6, ADR-039).
 * Plain JSON (not the API envelope), `application/json`, 200 without redirects, publicly
 * cacheable (`cache.public` on the routes). No session or cookie is involved.
 */
class AppLinkAssociationController extends Controller
{
    public function __construct(private AppLinkAssociationService $associations) {}

    public function android(): JsonResponse
    {
        return $this->file($this->associations->androidStatements());
    }

    public function apple(): JsonResponse
    {
        return $this->file($this->associations->appleAssociation());
    }

    /**
     * @param  array<mixed>  $body
     */
    private function file(array $body): JsonResponse
    {
        return new JsonResponse($body, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }
}
