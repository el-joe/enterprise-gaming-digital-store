<?php

use App\Domains\Identity\Models\OtpChallenge;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Exceptions\GuestOtpInvalid;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Notifications\GuestOrderAccessCode;
use App\Domains\Orders\Services\GuestOrderAccessService;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

const SENT_MESSAGE = 'If the details match an order, a code has been sent.';

function guestOrder(string $email = 'buyer@example.com'): Order
{
    return Order::factory()->create(['user_id' => null, 'guest_email' => $email, 'contact_email' => $email]);
}

function sentCode(): string
{
    $code = null;

    Notification::assertSentOnDemand(GuestOrderAccessCode::class, function (GuestOrderAccessCode $notification, array $channels, AnonymousNotifiable $notifiable) use (&$code): bool {
        $code = $notification->code;

        return $channels === ['mail'];
    });

    return $code;
}

beforeEach(fn () => Notification::fake());

it('emails a six digit code to the guest email of the order and stores only its hash', function (): void {
    $order = guestOrder();

    $this->postJson('/api/v1/checkout/guest/otp/send', ['order_public_id' => $order->public_id, 'email' => ' Buyer@Example.com '])
        ->assertOk()
        ->assertJsonPath('message', SENT_MESSAGE);

    Notification::assertSentOnDemand(GuestOrderAccessCode::class, fn ($notification, $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'buyer@example.com');
    $code = sentCode();

    expect($code)->toMatch('/^\d{6}$/')
        ->and(OtpChallenge::query()->sole()->getRawOriginal('code_hash'))->not->toContain($code);
});

it('answers the same way and sends nothing when the details do not match', function (string $email, bool $registered): void {
    $order = $registered ? Order::factory()->create(['user_id' => User::factory()->create()->id, 'contact_email' => $email]) : guestOrder();

    $this->postJson('/api/v1/checkout/guest/otp/send', ['order_public_id' => $order->public_id, 'email' => $email])
        ->assertOk()
        ->assertJsonPath('message', SENT_MESSAGE);
    $this->postJson('/api/v1/checkout/guest/otp/send', ['order_public_id' => strtolower((string) Str::ulid()), 'email' => $email])
        ->assertOk()
        ->assertJsonPath('message', SENT_MESSAGE);

    Notification::assertNothingSent();
})->with([
    'wrong email' => ['someone@example.com', false],
    'registered customer order' => ['buyer@example.com', true],
]);

it('verifies the guest email with the right code and opens the order to this session', function (): void {
    $order = guestOrder();
    app(GuestOrderAccessService::class)->sendCode($order->public_id, 'buyer@example.com');

    $this->getJson("/api/v1/checkout/orders/{$order->public_id}/status")->assertNotFound();

    $this->postJson('/api/v1/checkout/guest/otp/verify', ['order_public_id' => $order->public_id, 'code' => sentCode()])
        ->assertOk()
        ->assertJsonPath('data', ['order_public_id' => $order->public_id, 'email_verified' => true]);

    expect($order->fresh()->guest_email_verified_at)->not->toBeNull()
        ->and(OrderEvent::query()->where('order_id', $order->id)->sole()->type)->toBe('guest_email_verified');

    $this->getJson("/api/v1/checkout/orders/{$order->public_id}/status")->assertOk();
});

it('refuses a wrong code and counts the attempt', function (): void {
    $order = guestOrder();
    app(GuestOrderAccessService::class)->sendCode($order->public_id, 'buyer@example.com');
    $wrong = sentCode() === '000000' ? '111111' : '000000';

    $this->postJson('/api/v1/checkout/guest/otp/verify', ['order_public_id' => $order->public_id, 'code' => $wrong])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'otp_invalid');

    expect(OtpChallenge::query()->sole()->attempts)->toBe(1)
        ->and($order->fresh()->guest_email_verified_at)->toBeNull();
});

it('locks a code after five wrong attempts', function (): void {
    $order = guestOrder();
    $service = app(GuestOrderAccessService::class);
    $service->sendCode($order->public_id, 'buyer@example.com');
    $code = sentCode();
    $wrong = $code === '000000' ? '111111' : '000000';

    for ($attempt = 0; $attempt < GuestOrderAccessService::MAX_ATTEMPTS; $attempt++) {
        expect(fn () => $service->verify($order->public_id, $wrong))->toThrow(GuestOtpInvalid::class);
    }

    expect(fn () => $service->verify($order->public_id, $code))->toThrow(GuestOtpInvalid::class);
});

it('refuses an expired code and a code replaced by a newer one', function (): void {
    $order = guestOrder();
    $service = app(GuestOrderAccessService::class);
    $service->sendCode($order->public_id, 'buyer@example.com');
    $first = sentCode();

    $this->travel(GuestOrderAccessService::CODE_TTL_MINUTES + 1)->minutes();
    expect(fn () => $service->verify($order->public_id, $first))->toThrow(GuestOtpInvalid::class);

    Notification::fake();
    $service->sendCode($order->public_id, 'buyer@example.com');
    $second = sentCode();

    if ($second !== $first) {
        expect(fn () => $service->verify($order->public_id, $first))->toThrow(GuestOtpInvalid::class);
    }

    expect($service->verify($order->public_id, $second)->id)->toBe($order->id);
});

it('caps how many codes one order can send per hour', function (): void {
    $order = guestOrder();
    $service = app(GuestOrderAccessService::class);

    for ($send = 0; $send < GuestOrderAccessService::MAX_SENDS_PER_HOUR + 2; $send++) {
        $service->sendCode($order->public_id, 'buyer@example.com');
    }

    Notification::assertSentOnDemandTimes(GuestOrderAccessCode::class, GuestOrderAccessService::MAX_SENDS_PER_HOUR);
});

it('rate limits the code endpoints', function (): void {
    $order = guestOrder();

    for ($request = 0; $request < 5; $request++) {
        $this->postJson('/api/v1/checkout/guest/otp/verify', ['order_public_id' => $order->public_id, 'code' => '123456']);
    }

    $this->postJson('/api/v1/checkout/guest/otp/verify', ['order_public_id' => $order->public_id, 'code' => '123456'])
        ->assertTooManyRequests()
        ->assertJsonPath('code', 'rate_limited');
});
