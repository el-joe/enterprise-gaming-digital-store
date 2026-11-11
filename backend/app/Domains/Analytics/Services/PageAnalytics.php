<?php

namespace App\Domains\Analytics\Services;

use InvalidArgumentException;

/**
 * Browser ecommerce events of the page being rendered (request scoped, M18). The storefront
 * layout prints them in a nonced `application/json` block that `analytics.js` pushes to the
 * dataLayer, only while a tag is configured and browser events are on. `purchase` and
 * `refund` are server-only (Measurement Protocol, INTEGRATIONS.md section 6) and refused here.
 * Item ids are variant SKUs, the same ids the server-side `purchase` sends.
 */
class PageAnalytics
{
    public const array BROWSER_EVENTS = ['view_item_list', 'select_item', 'view_item', 'add_to_cart', 'remove_from_cart', 'begin_checkout', 'add_payment_info'];

    /** @var list<array{event: string, ecommerce: array<string, mixed>}> */
    private array $events = [];

    public function __construct(private AnalyticsSettings $settings) {}

    /**
     * @param  array<string, mixed>  $ecommerce
     */
    public function add(string $event, array $ecommerce): void
    {
        if (! in_array($event, self::BROWSER_EVENTS, true)) {
            throw new InvalidArgumentException("The browser never sends the [{$event}] event.");
        }

        $this->events[] = ['event' => $event, 'ecommerce' => $ecommerce];
    }

    /**
     * `view_item` from the product page values (prices as rendered, in the display currency).
     *
     * @param  array<string, mixed>  $product  ProductDetailResource
     * @param  list<array<string, mixed>>  $variants  VariantOfferResource
     */
    public function viewItem(array $product, array $variants): void
    {
        $selected = collect($variants)->first(static fn (array $variant): bool => ($variant['purchasable'] ?? false) && is_array($variant['price'] ?? null));

        if ($selected === null) {
            return;
        }

        $this->add('view_item', [
            'currency' => $selected['price']['currency'],
            'value' => (float) $selected['price']['amount'],
            'items' => [$this->item($product['name'], $selected['sku'], $selected['title'], $selected['price']['amount'], 1, $product['category']['name'] ?? null, $product['platform']['name'] ?? null)],
        ]);
    }

    /**
     * `begin_checkout` from the checkout page's cart (CartResource).
     *
     * @param  array<string, mixed>  $cart
     */
    public function beginCheckout(array $cart): void
    {
        $items = [];

        foreach ((array) $cart['items'] as $line) {
            if (! is_array($line['unit_price'] ?? null)) {
                continue;
            }

            $items[] = $this->item($line['product']['name'] ?? '', (string) $line['variant']['sku'], $line['variant']['title'] ?? null, $line['unit_price']['amount'], (int) $line['quantity']);
        }

        if ($items === []) {
            return;
        }

        $this->add('begin_checkout', [
            'currency' => $cart['currency'],
            'value' => (float) $cart['totals']['total']['amount'],
            'coupon' => $cart['coupon']['applied'] ?? false ? $cart['coupon']['code'] : null,
            'items' => $items,
        ]);
    }

    /**
     * @return list<array{event: string, ecommerce: array<string, mixed>}>
     */
    public function events(): array
    {
        return $this->settings->browserEventsEnabled() ? $this->events : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function item(?string $name, string $sku, ?string $variant, string $price, int $quantity, ?string $category = null, ?string $brand = null): array
    {
        return array_filter([
            'item_id' => $sku,
            'item_name' => (string) $name,
            'item_variant' => $variant,
            'item_category' => $category,
            'item_brand' => $brand,
            'price' => (float) $price,
            'quantity' => $quantity,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
