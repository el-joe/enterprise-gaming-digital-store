<?php

namespace App\Http\Requests\Web;

use App\Http\Requests\Storefront\ProductListingRequest;

/**
 * Filters of a storefront listing page (category, platform, search): the same rules as the
 * listing JSON (`GET /api/v1/storefront/catalog/products`). An invalid filter sends the shopper
 * back to the unfiltered page instead of the page that failed.
 */
class ListingPageRequest extends ProductListingRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = array_map(static fn (array $rules): array => ['nullable', ...$rules], parent::rules());

        return [...$rules, 'q' => ['sometimes', 'nullable', 'string', 'max:100']];
    }

    protected function getRedirectUrl(): string
    {
        return $this->url();
    }
}
