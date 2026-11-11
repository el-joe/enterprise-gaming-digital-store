<?php

/**
 * M18 browser analytics (SEO_STRATEGY.md section 12, SECURITY.md section 9): the GTM or GA4
 * tag only when configured and only after consent, CSP sources only then, ecommerce events in
 * the dataLayer block, and never a browser `purchase`.
 */

use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Analytics\Services\PageAnalytics;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\SettingType;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Database\Seeders\PaymentGatewaySeeder;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    storefrontWithoutBuild();
});

it('loads no tag, no consent banner and no Google source while analytics is not configured', function (): void {
    $product = storefrontProduct();

    $response = $this->get('/products/'.$product->slug)->assertOk()
        ->assertDontSee('data-consent-banner', false)
        ->assertDontSee('id="analytics-events"', false)
        ->assertDontSee('googletagmanager');

    expect($response->headers->get('Content-Security-Policy'))->not->toContain('google');
    expect(json_decode((string) str($response->getContent())->betweenFirst('id="store-config" nonce="'.cspNonceOf($response).'">', '</script>'), true)['analytics'])->toBeNull();
});

it('hands the public GTM id to the page, behind the consent banner, with the exact Google sources in the CSP', function (): void {
    analyticsSetting('gtm_container_id', 'GTM-ABC1234');
    configureMeasurementProtocol();
    $product = storefrontProduct();

    $response = $this->get('/products/'.$product->slug)->assertOk()
        ->assertSee('data-consent-banner hidden', false)
        ->assertSee('data-consent-choice="granted"', false)
        ->assertSee('data-consent-open', false)
        ->assertDontSee(GA4_API_SECRET_CANARY);

    $policy = (string) $response->headers->get('Content-Security-Policy');
    expect($policy)->toContain("script-src 'self' 'nonce-")
        ->toContain('https://www.googletagmanager.com')
        ->toContain('connect-src')->toContain('https://*.google-analytics.com')->toContain('https://*.analytics.google.com')
        ->not->toContain('unsafe-inline')->not->toContain('strict-dynamic');

    $config = json_decode((string) str($response->getContent())->betweenFirst('id="store-config" nonce="'.cspNonceOf($response).'">', '</script>'), true);
    expect($config['analytics'])->toBe(['gtm' => 'GTM-ABC1234', 'ga4' => null, 'events' => true]);

    // No tag script is in the HTML: lib/analytics.js adds it only after consent.
    expect($response->getContent())->not->toMatch('~<script[^>]+googletagmanager~');
    assertCspSafeHtml($response);
});

it('never adds Google sources to the admin CSP', function (): void {
    analyticsSetting('gtm_container_id', 'GTM-ABC1234');
    adminWith(['settings.view']);

    expect((string) $this->get('/admin/settings/analytics')->assertOk()->headers->get('Content-Security-Policy'))->not->toContain('google');
});

it('pushes view_item with the rendered price on the product page and begin_checkout on checkout', function (): void {
    Queue::fake([FulfilOrder::class]);
    analyticsSetting('ga4_measurement_id', 'G-TEST1234');
    $product = storefrontProduct('Steam Wallet $20', '20');
    $variant = storefrontVariant($product);

    $events = analyticsEvents($this->get('/products/'.$product->slug)->assertOk());
    expect($events)->toBe([[
        'event' => 'view_item',
        'ecommerce' => [
            'currency' => 'USD',
            'value' => 20,
            'items' => [['item_id' => $variant->sku, 'item_name' => 'Steam Wallet $20', 'item_variant' => '$20', 'item_category' => 'Gift cards', 'item_brand' => 'Steam', 'price' => 20, 'quantity' => 1]],
        ],
    ]]);

    $this->actingAs(User::factory()->create());
    $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 2])->assertCreated();

    $checkout = analyticsEvents($this->get('/checkout')->assertOk());
    expect($checkout[0]['event'])->toBe('begin_checkout')
        ->and($checkout[0]['ecommerce']['value'])->toEqual(40)
        ->and($checkout[0]['ecommerce']['items'][0])->toMatchArray(['item_id' => $variant->sku, 'quantity' => 2, 'price' => 20]);
});

it('sends no browser events while they are switched off, and the add_to_cart data stays on the buttons', function (): void {
    analyticsSetting('ga4_measurement_id', 'G-TEST1234');
    analyticsSetting('browser_events', false, SettingType::Bool);
    $product = storefrontProduct('Steam Wallet $20', '20');

    $response = $this->get('/products/'.$product->slug)->assertOk()
        ->assertSee('data-amount="20.00"', false)
        ->assertSee('data-currency="USD"', false);

    expect(analyticsEvents($response))->toBe([]);
});

it('refuses purchase and refund as browser events on the server', function (string $event): void {
    expect(fn () => app(PageAnalytics::class)->add($event, []))->toThrow(InvalidArgumentException::class);
})->with(['purchase', 'refund']);

it('has no browser code path that can emit purchase', function (): void {
    $sources = collect(File::allFiles(resource_path('js')))->merge(File::allFiles(resource_path('views')));

    foreach ($sources as $file) {
        $content = $file->getContents();

        expect(preg_match('~[\'"`]purchase[\'"`]~', $content))->toBe(0, "{$file->getRelativePathname()} names the purchase event")
            ->and(preg_match('~event\s*:\s*[\'"`]refund~', $content))->toBe(0);
    }

    $library = File::get(resource_path('js/lib/analytics.js'));
    expect($library)->toContain("const ALLOWED = ['view_item_list', 'select_item', 'view_item', 'add_to_cart', 'remove_from_cart', 'begin_checkout', 'add_payment_info'];")
        ->toContain('!ALLOWED.includes(event)');
});

it('never renders purchase on any page of a paid order, and the server sends it exactly once', function (): void {
    Queue::fake([FulfilOrder::class]);
    foreach (['US', 'EG'] as $country) {
        CountryFactory::ensure($country);
    }
    CurrencyFactory::ensure('USD');
    CurrencyFactory::ensure('EGP');
    analyticsSetting('gtm_container_id', 'GTM-ABC1234');
    configureMeasurementProtocol();
    $this->seed(PaymentGatewaySeeder::class);
    gatewayFor('stripe', ['USD'], keepRules: true);
    fakeStripe(overrides: [
        'api.stripe.com/v1/payment_intents' => Http::response(stripeIntent('pi_web', 'requires_payment_method', 2000)),
        'www.google-analytics.com/*' => Http::response('', 204),
    ]);
    $product = storefrontProduct('Steam Wallet $20', '20');
    $this->actingAs(User::factory()->create());

    $pages = [$this->get('/products/'.$product->slug)];
    $this->postJson('/api/v1/storefront/cart/items', ['variant_id' => storefrontVariant($product)->public_id, 'quantity' => 1])->assertCreated();
    $pages[] = $this->get('/cart');
    $pages[] = $this->get('/checkout');

    $option = collect($this->getJson('/api/v1/checkout/payment-options?country=US')->json('data.options'))->firstWhere('gateway', 'stripe');
    $orderId = $this->postJson('/api/v1/checkout/place-order', [
        'country' => 'US', 'gateway' => 'stripe', 'currency' => $option['display_currency'], 'expected_total' => $option['display_amount']['amount'],
        'consents' => ['terms' => true], 'client' => 'web', 'ga_client_id' => '123456789.1700000000',
    ], ['Idempotency-Key' => 'web-ga-0000001'])->assertCreated()->json('data.order.public_id');

    $pages[] = $this->get("/checkout/orders/{$orderId}/return");

    resetHttpFakes();
    fakeStripe(Http::response(stripeIntent('pi_web', 'succeeded', 2000)), ['www.google-analytics.com/*' => Http::response('', 204)]);
    $this->postJson("/api/v1/checkout/orders/{$orderId}/verify-payment")->assertOk()->assertJsonPath('data.payment_status', 'paid');
    $this->postJson("/api/v1/checkout/orders/{$orderId}/verify-payment")->assertOk();
    $pages[] = $this->get("/orders/{$orderId}")->assertOk();

    foreach ($pages as $page) {
        $page->assertOk();
        expect(collect(analyticsEvents($page) ?? [])->pluck('event')->all())->not->toContain('purchase', 'refund')
            ->and($page->getContent())->not->toContain('"event":"purchase"');
    }

    $sent = Http::recorded(fn (HttpRequest $request): bool => str_contains($request->url(), 'google-analytics.com/mp/collect'));
    expect($sent)->toHaveCount(1);

    [$request] = $sent->values()[0];
    expect($request['client_id'])->toBe('123456789.1700000000')
        ->and($request['events'][0]['name'])->toBe('purchase')
        ->and($request['events'][0]['params']['transaction_id'])->toBe(AnalyticsEventDispatch::query()->sole()->order->order_number)
        ->and(AnalyticsEventDispatch::query()->sole()->status->value)->toBe('sent');
});

it('saves and validates the analytics settings, and prints the Search Console verification tag', function (): void {
    adminWith(['settings.view', 'settings.manage']);

    $this->putJson('/admin/api/settings/analytics', [
        'ga4_measurement_id' => 'G-TEST1234', 'gtm_container_id' => null, 'browser_events' => true, 'server_purchase' => true,
        'mp_region' => 'mars', 'search_console_property' => 'boxcards.shop', 'search_console_client_id' => 'nope',
        'search_console_verification' => '<script>', 'faq_schema' => false,
    ])->assertUnprocessable()->assertJsonValidationErrors(['mp_region', 'search_console_property', 'search_console_client_id', 'search_console_verification'], 'errors');

    $this->putJson('/admin/api/settings/analytics', [
        'ga4_measurement_id' => 'G-TEST1234', 'gtm_container_id' => null, 'browser_events' => true, 'server_purchase' => true,
        'mp_region' => 'eu', 'search_console_property' => 'sc-domain:boxcards.shop', 'search_console_client_id' => '123-abc.apps.googleusercontent.com',
        'search_console_verification' => 'AbCdEf0123456789xyz', 'faq_schema' => false,
    ])->assertOk();

    $this->get('/gaming')->assertOk()->assertSee('<meta name="google-site-verification" content="AbCdEf0123456789xyz">', false);
});

/**
 * The CSP nonce of a response.
 */
function cspNonceOf(TestResponse $response): string
{
    preg_match("/'nonce-([^']+)'/", (string) $response->headers->get('Content-Security-Policy'), $nonce);

    return $nonce[1] ?? '';
}
