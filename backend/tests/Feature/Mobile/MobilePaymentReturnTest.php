<?php

/**
 * M-A6 (G8): the `client` channel of place-order and retry-payment, the server-built return URLs
 * each provider receives, the `/app/payments/...` return links and the `/.well-known` app link
 * files. Providers mocked with Http::fake; awaiting sandbox verification.
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\DTOs\PaymentSession;
use App\Domains\Payments\Enums\PaymentClient;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Services\PaymentAttemptService;
use App\Domains\Shared\Models\Country;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Database\Seeders\PaymentGatewaySeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

const APP_FINGERPRINT = '14:6D:E9:83:C5:73:06:50:D8:EE:B9:95:2F:34:FC:64:16:A0:83:42:E6:1D:BE:A8:8A:04:96:B2:3F:CF:44:E5';

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    config(['app.url' => 'https://shop.test']);
});

function appReturnUrl(Order $order, Payment $payment, string $kind = 'return'): string
{
    return "https://shop.test/app/payments/{$kind}/{$order->public_id}?attempt={$payment->public_id}";
}

/**
 * The web return page as M10 builds it (`url()` of `payments.return_url`); unchanged by M-A6.
 */
function webReturnUrl(Order $order, string $path = 'return'): string
{
    return url("/checkout/orders/{$order->public_id}/{$path}");
}

/**
 * A fresh attempt for an order created by `orderAwaitingPayment` (its fixture attempt removed).
 *
 * @return array{0: PaymentSession, 1: Payment}
 */
function startAttempt(Order $order, PaymentClient $client): array
{
    Payment::query()->where('order_id', $order->id)->delete();
    $session = app(PaymentAttemptService::class)->start($order->public_id, client: $client);

    return [$session, Payment::query()->where('order_id', $order->id)->sole()];
}

/**
 * @param  array<string, mixed>  $overrides
 */
function mobileStripeOrder(array $overrides = [], string $key = 'mobile-pay-key-01'): TestResponse
{
    return statelessCall('POST', 'api/v1/mobile/checkout/place-order', placeOrderBody('10.00', ['email' => 'guest@example.com', 'gateway' => 'stripe', ...$overrides]), ['X-Cart-Token' => test()->cartToken, 'Idempotency-Key' => $key]);
}

describe('PayPal return and cancel URLs', function (): void {
    beforeEach(function (): void {
        [$this->order] = orderAwaitingPayment(gatewayFor('paypal'), providerPaymentId: 'PAYPAL-ORDER-1');
        fakePayPal();
    });

    it('sends the web return page as return_url and cancel_url for a web attempt', function (): void {
        startAttempt($this->order, PaymentClient::Web);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request['payment_source']['paypal']['experience_context'] === [
                'return_url' => webReturnUrl($this->order),
                'cancel_url' => webReturnUrl($this->order),
            ]);
    });

    it('sends the configured web cancel page when one is set', function (): void {
        config(['payments.cancel_url' => '/checkout/orders/{order}/cancelled']);

        startAttempt($this->order, PaymentClient::Web);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request['payment_source']['paypal']['experience_context']['cancel_url'] === webReturnUrl($this->order, 'cancelled'));
    });

    it('sends the app return and cancel links for an app attempt', function (): void {
        [, $payment] = startAttempt($this->order, PaymentClient::App);

        expect($payment->client)->toBe(PaymentClient::App);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request['payment_source']['paypal']['experience_context'] === [
                'return_url' => appReturnUrl($this->order, $payment),
                'cancel_url' => appReturnUrl($this->order, $payment, 'cancel'),
            ]);
    });

    it('builds app links on APP_URL even when the current URL root is another host', function (): void {
        url()->forceRootUrl('https://attacker.example');

        [, $payment] = startAttempt($this->order, PaymentClient::App);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request['payment_source']['paypal']['experience_context']['return_url'] === appReturnUrl($this->order, $payment));
    });

    it('refuses an app template that is not a path on the store domain and sends nothing', function (string $template): void {
        config(['payments.app_return_url' => $template]);

        expect(fn () => startAttempt($this->order, PaymentClient::App))->toThrow(GatewayNotConfigured::class);

        expect(Payment::query()->sole())->status->toBe(PaymentStatus::Failed)->failure_code->toBe('gateway_unavailable');
        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/checkout/orders'));
    })->with(['https://elsewhere.example/app/{order}', '//elsewhere.example/app/{order}', 'app/payments/return/{order}']);
});

describe('Paymob redirection_url', function (): void {
    beforeEach(function (): void {
        givenRate('EGP', '50');
        $country = Country::query()->firstOrCreate(['code' => 'EG'], ['name' => 'Egypt', 'is_active' => true]);
        [$this->order] = orderAwaitingPayment(gatewayFor('paymob', ['EGP']), '1000.00', 'EGP', 'pi_paymob_1', orderAttributes: ['country_code' => $country->code], rate: '50');
        fakePaymob();
    });

    it('sends the return link of the channel', function (PaymentClient $client): void {
        [, $payment] = startAttempt($this->order, $client);

        $expected = $client === PaymentClient::App ? appReturnUrl($this->order, $payment) : webReturnUrl($this->order);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v1/intention/') && $request['redirection_url'] === $expected);
    })->with([PaymentClient::Web, PaymentClient::App]);
});

describe('Stripe next.return_url', function (): void {
    beforeEach(function (): void {
        [$this->order] = orderAwaitingPayment(gatewayFor('stripe'));
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent(status: 'requires_payment_method'))]);
    });

    it('publishes the app return link for an app attempt and nothing for a web attempt', function (): void {
        [$web] = startAttempt($this->order, PaymentClient::Web);
        expect($web->next)->not->toHaveKey('return_url');

        [$app, $payment] = startAttempt($this->order, PaymentClient::App);
        expect($app->next['return_url'])->toBe(appReturnUrl($this->order, $payment));
    });

    it('publishes the configured app scheme when one is set', function (): void {
        config(['payments.stripe_app_return_url' => 'boxcards://stripe-redirect']);

        [$session] = startAttempt($this->order, PaymentClient::App);

        expect($session->next['return_url'])->toBe('boxcards://stripe-redirect');
    });

    it('does not change the PaymentIntent create request', function (): void {
        startAttempt($this->order, PaymentClient::App);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v1/payment_intents') && ! str_contains($request->body(), 'return_url'));
    });
});

describe('the client channel at place-order and retry-payment', function (): void {
    beforeEach(function (): void {
        // OD-1 (owner decision 4): guest checkout is off by default on the app route too; these
        // tests cover the guest flow, which stays available behind `storefront.guest_checkout`.
        config(['storefront.guest_checkout' => true]);
        CountryFactory::ensure('US');
        CountryFactory::ensure('EG');
        CurrencyFactory::ensure('USD');
        CurrencyFactory::ensure('EGP');
        $this->seed(PaymentGatewaySeeder::class);
        gatewayFor('stripe', ['USD'], keepRules: true);
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent(status: 'requires_payment_method'))]);
        $this->cartToken = mobileGuestCart(sellableVariant('10'));
    });

    it('defaults to app on the mobile group and returns the server return URL', function (): void {
        $response = mobileStripeOrder()->assertCreated();

        $order = Order::query()->sole();
        $payment = Payment::query()->sole();
        expect($payment->client)->toBe(PaymentClient::App);
        $response->assertJsonPath('data.next.return_url', appReturnUrl($order, $payment));
    });

    it('defaults to web on the session group, with no next.return_url', function (): void {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => sellableVariant('10')->public_id, 'quantity' => 1])->assertCreated();

        $this->actingAs($user)->postJson('/api/v1/checkout/place-order', placeOrderBody('10.00', ['gateway' => 'stripe']), ['Idempotency-Key' => 'web-pay-key-0001'])
            ->assertCreated()->assertJsonMissingPath('data.next.return_url');

        expect(Payment::query()->sole()->client)->toBe(PaymentClient::Web);
    });

    it('honours an explicit client', function (): void {
        mobileStripeOrder(['client' => 'web'])->assertCreated()->assertJsonMissingPath('data.next.return_url');

        expect(Payment::query()->sole()->client)->toBe(PaymentClient::Web);
    });

    it('ignores a return URL sent by the client', function (): void {
        $response = mobileStripeOrder(['return_url' => 'https://evil.example/steal', 'redirect_url' => 'https://evil.example/steal', 'redirection_url' => 'https://evil.example/steal'])->assertCreated();

        expect($response->json('data.next.return_url'))->toStartWith('https://shop.test/app/payments/return/')
            ->and(json_encode($response->json()))->not->toContain('evil.example');
        Http::assertNotSent(fn (Request $request): bool => str_contains($request->body(), 'evil.example'));
    });

    it('rejects a client that is not a known channel', function (mixed $client): void {
        mobileStripeOrder(['client' => $client])->assertUnprocessable()->assertJsonValidationErrors('client');

        expect(Order::query()->exists())->toBeFalse();
    })->with(['https://evil.example/return', 'ios', 'APP']);

    it('keeps the app channel on a retry from the app, and lets the retry name another channel', function (): void {
        resetHttpFakes();
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(['error' => ['type' => 'api_error', 'message' => 'down']], 500)]);
        $placed = mobileStripeOrder()->assertCreated()->assertJsonPath('data.next', null);
        $order = Order::query()->sole();
        $token = ['X-Guest-Order-Token' => $placed->json('data.guest_order_token.token')];

        resetHttpFakes();
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent('pi_retry', 'requires_payment_method'))]);

        $retry = statelessCall('POST', "api/v1/mobile/checkout/orders/{$order->public_id}/retry-payment", headers: $token)->assertCreated();
        $second = Payment::query()->where('attempt_no', 2)->sole();
        expect($second->client)->toBe(PaymentClient::App);
        $retry->assertJsonPath('data.next.return_url', appReturnUrl($order, $second));

        resetHttpFakes();
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::response(stripeIntent('pi_retry_web', 'requires_payment_method'))]);
        statelessCall('POST', "api/v1/mobile/checkout/orders/{$order->public_id}/retry-payment", ['client' => 'web'], $token)
            ->assertCreated()->assertJsonMissingPath('data.next.return_url');
        expect(Payment::query()->where('attempt_no', 3)->sole()->client)->toBe(PaymentClient::Web);
    });

    it('rejects an unknown client on retry-payment', function (): void {
        $user = User::factory()->create();
        [$order] = orderAwaitingPayment(gatewayFor('stripe', ['USD'], keepRules: true), '10.00', orderAttributes: ['user_id' => $user->id]);

        statelessCall('POST', "api/v1/mobile/checkout/orders/{$order->public_id}/retry-payment", ['client' => 'https://evil.example'], token: mobileSignIn($user)['access_token'])
            ->assertUnprocessable()->assertJsonValidationErrors('client');
    });
});

describe('the app return links in a browser', function (): void {
    beforeEach(function (): void {
        [$this->order, $this->payment] = orderAwaitingPayment(gatewayFor('stripe'));
        Http::fake();
    });

    it('redirects to the web return page, dropping every query parameter', function (): void {
        $response = $this->get("/app/payments/return/{$this->order->public_id}?attempt={$this->payment->public_id}&success=true&txn_response_code=APPROVED")
            ->assertRedirect(webReturnUrl($this->order))
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Referrer-Policy', 'no-referrer');

        expect($response->headers->getCookies())->toBe([]);
    });

    it('redirects the cancel link to the web cancel page', function (): void {
        config(['payments.cancel_url' => '/checkout/orders/{order}/cancelled']);

        $this->get("/app/payments/cancel/{$this->order->public_id}")->assertRedirect(webReturnUrl($this->order, 'cancelled'));
    });

    it('never changes the order or the payment and calls no provider, even with success=true', function (): void {
        $this->get("/app/payments/return/{$this->order->public_id}?success=true&pending=false")->assertRedirect();
        $this->get("/app/payments/cancel/{$this->order->public_id}?success=true")->assertRedirect();

        expect($this->order->fresh())->status->toBe(OrderStatus::PendingPayment)->paid_at->toBeNull()
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::RequiresAction);
        Http::assertNothingSent();
    });

    it('is not found for an unknown order or a malformed id', function (string $path): void {
        $this->get($path)->assertNotFound();
    })->with(['/app/payments/return/01JAAAAAAAAAAAAAAAAAAAAAAA', '/app/payments/cancel/01JAAAAAAAAAAAAAAAAAAAAAAA', '/app/payments/return/not-an-order']);
});

describe('app link association files', function (): void {
    it('serves an empty assetlinks.json when no fingerprint is configured', function (): void {
        config(['mobile.app_links.android_sha256_fingerprints' => '']);

        $response = $this->get('/.well-known/assetlinks.json')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Cache-Control', 'max-age=3600, public');

        expect($response->getContent())->toBe('[]')->and($response->headers->getCookies())->toBe([]);
    });

    it('declares every configured package with every valid fingerprint', function (): void {
        config(['mobile.app_links' => [
            ...config('mobile.app_links'),
            'android_packages' => 'shop.boxcards.app, shop.boxcards.app.staging,shop.boxcards.app.dev,not a package',
            'android_sha256_fingerprints' => strtolower(APP_FINGERPRINT).',AB:CD,'.APP_FINGERPRINT,
        ]]);

        $statements = $this->get('/.well-known/assetlinks.json')->assertOk()->json();

        expect($statements)->toHaveCount(3)
            ->and(array_column(array_column($statements, 'target'), 'package_name'))->toBe(['shop.boxcards.app', 'shop.boxcards.app.staging', 'shop.boxcards.app.dev'])
            ->and($statements[0])->toBe([
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => ['namespace' => 'android_app', 'package_name' => 'shop.boxcards.app', 'sha256_cert_fingerprints' => [APP_FINGERPRINT]],
            ]);
    });

    it('serves an apple-app-site-association with no apps when unconfigured', function (): void {
        config(['mobile.app_links.ios_app_ids' => '']);

        $response = $this->get('/.well-known/apple-app-site-association')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');

        expect($response->json())->toBe(['applinks' => ['details' => []]])->and($response->headers->getCookies())->toBe([]);
    });

    it('lists the configured app ids for the /app/ paths', function (): void {
        config(['mobile.app_links.ios_app_ids' => 'ABCDE12345.shop.boxcards.app,bad.id']);

        $response = $this->get('/.well-known/apple-app-site-association')->assertOk();

        expect($response->json())->toBe(['applinks' => ['details' => [['appIDs' => ['ABCDE12345.shop.boxcards.app'], 'components' => [['/' => '/app/*']]]]]])
            ->and($response->getContent())->toContain('"/":"/app/*"');
    });
});
