<?php

/**
 * M-A18 (owner decision 9, ADR-042): push notifications through Firebase Cloud Messaging.
 * Device push tokens bound to the sign-in, the `push` preference, the FCM HTTP v1 sender with a
 * test service-account key and `Http::fake()`, and what a push may carry (ids and inbox text only).
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\DTOs\PushMessage;
use App\Domains\Notifications\Enums\InboxNotificationKind;
use App\Domains\Notifications\Enums\PushDeliveryOutcome;
use App\Domains\Notifications\Exceptions\PushNotConfigured;
use App\Domains\Notifications\Models\MobilePushToken;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Notifications\Services\FcmClient;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Listeners\SendFulfilmentNotifications;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Notifications\PaymentFailed;
use App\Domains\Payments\Services\PaymentStateApplier;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Notifications\SupportTicketReplied;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

const PUSH_DEVICE_TOKEN = 'fcm-device-token-aaaaaaaaaaaa:APA91bHUNDREDSOFCHARACTERS_0123456789-abc';

const PUSH_CANARY_CODE = 'CANARY-PUSH-0000-4444';

beforeEach(function (): void {
    $this->customer = User::factory()->create(['email_verified_at' => now(), 'locale' => 'en']);
    $this->pair = mobileSignIn($this->customer);
    $this->access = $this->pair['access_token'];
    Cache::flush();
});

afterEach(function (): void {
    foreach (glob(storage_path('framework/testing/fcm-*.json')) ?: [] as $file) {
        @unlink($file);
    }
});

/**
 * Turn push on with a freshly generated service-account key (never a real one) and return the
 * private key resource for signature checks.
 */
function enablePushWithTestKey(): OpenSSLAsymmetricKey
{
    $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
    openssl_pkey_export($key, $pem);
    $directory = storage_path('framework/testing');

    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $path = $directory.'/fcm-'.Str::random(12).'.json';
    file_put_contents($path, json_encode([
        'type' => 'service_account',
        'project_id' => 'boxcards-test',
        'client_email' => 'push-sender@boxcards-test.iam.gserviceaccount.com',
        'private_key' => $pem,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

    config(['push.enabled' => true, 'push.fcm.project_id' => 'boxcards-test', 'push.fcm.credentials_path' => $path]);

    return $key;
}

/**
 * Fake Google's OAuth endpoint and FCM. `$fcm` is the FCM answer for every send.
 */
function fakeFcm(?PromiseInterface $fcm = null): void
{
    Http::preventStrayRequests();
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.test-access-token', 'expires_in' => 3599, 'token_type' => 'Bearer']),
        'fcm.googleapis.com/*' => $fcm ?? Http::response(['name' => 'projects/boxcards-test/messages/0:1']),
    ]);
}

function registerPushToken(string $access, string $token = PUSH_DEVICE_TOKEN, string $platform = 'android'): TestResponse
{
    return mobileCall('POST', 'push-token', ['token' => $token, 'platform' => $platform], $access);
}

function familyOf(User $user, string $deviceName = 'Pixel 9'): string
{
    return (string) MobileRefreshToken::query()->where('user_id', $user->id)->where('device_name', $deviceName)->value('family_id');
}

/**
 * @return list<array<string, mixed>>
 */
function fcmMessagesSent(): array
{
    return Http::recorded()
        ->filter(static fn (array $pair): bool => str_contains($pair[0]->url(), 'fcm.googleapis.com'))
        ->map(static fn (array $pair): array => $pair[0]->data())
        ->values()
        ->all();
}

describe('device registration', function (): void {
    it('registers this device\'s token for the sign-in, audits it without the token and never echoes it', function (): void {
        $response = registerPushToken($this->access)->assertCreated()
            ->assertJsonPath('data.push_token.platform', 'android')
            ->assertJsonPath('message', 'Push notifications are on for this device.');

        $device = MobilePushToken::query()->sole();

        expect($device->family_id)->toBe(familyOf($this->customer))
            ->and($device->user_id)->toBe($this->customer->id)
            ->and($device->token)->toBe(PUSH_DEVICE_TOKEN)
            ->and(DB::table('mobile_push_tokens')->value('token'))->not->toBe(PUSH_DEVICE_TOKEN)
            ->and($response->getContent())->not->toContain(PUSH_DEVICE_TOKEN);

        $audit = AuditLog::query()->where('action', 'account.push_token_registered')->sole();
        expect(json_encode($audit->toArray()))->not->toContain(PUSH_DEVICE_TOKEN)
            ->and($audit->after['platform'] ?? null)->toBe('android');
    });

    it('keeps one token per device: the same token again is 200, a refreshed token replaces it', function (): void {
        registerPushToken($this->access)->assertCreated();
        registerPushToken($this->access)->assertOk();
        registerPushToken($this->access, PUSH_DEVICE_TOKEN.'-refreshed', 'ios')->assertCreated()->assertJsonPath('data.push_token.platform', 'ios');

        $device = MobilePushToken::query()->sole();
        expect($device->token)->toBe(PUSH_DEVICE_TOKEN.'-refreshed')
            ->and($device->platform->value)->toBe('ios');
    });

    it('gives each signed-in device its own token', function (): void {
        $tablet = mobileSignIn($this->customer, 'Tablet')['access_token'];

        registerPushToken($this->access)->assertCreated();
        registerPushToken($tablet, PUSH_DEVICE_TOKEN.'-tablet')->assertCreated();

        expect(MobilePushToken::query()->pluck('family_id')->sort()->values()->all())
            ->toBe(collect([familyOf($this->customer), familyOf($this->customer, 'Tablet')])->sort()->values()->all());
    });

    it('moves a token to the account now signed in on the device', function (): void {
        registerPushToken($this->access)->assertCreated();
        $other = User::factory()->create();

        registerPushToken(mobileSignIn($other)['access_token'])->assertCreated();

        expect(MobilePushToken::query()->sole()->user_id)->toBe($other->id);
    });

    it('validates the token and the platform', function (array $body, string $field): void {
        mobileCall('POST', 'push-token', $body, $this->access)->assertUnprocessable()->assertJsonValidationErrors([$field]);

        expect(MobilePushToken::query()->exists())->toBeFalse();
    })->with([
        'missing token' => [['platform' => 'android'], 'token'],
        'too short' => [['token' => 'short', 'platform' => 'android'], 'token'],
        'bad characters' => [['token' => str_repeat('a', 30).' <script>', 'platform' => 'android'], 'token'],
        'unknown platform' => [['token' => PUSH_DEVICE_TOKEN, 'platform' => 'windows'], 'platform'],
        'missing platform' => [['token' => PUSH_DEVICE_TOKEN], 'platform'],
    ]);

    it('needs a signed-in customer device', function (): void {
        mobileCall('POST', 'push-token', ['token' => PUSH_DEVICE_TOKEN, 'platform' => 'android'])->assertUnauthorized();

        // A customer token that is not part of an app sign-in (no refresh-token family).
        $loose = $this->customer->createToken('script', ['customer'], now()->addMinutes(15))->plainTextToken;
        registerPushToken($loose)->assertForbidden()->assertJsonPath('code', 'push_not_available');
    });

    it('rate limits registration per customer', function (): void {
        foreach (range(1, (int) config('push.register_per_minute')) as $attempt) {
            registerPushToken($this->access, PUSH_DEVICE_TOKEN.'-'.$attempt)->assertSuccessful();
        }

        registerPushToken($this->access)->assertTooManyRequests();
    });

    it('unregisters this device, idempotently', function (): void {
        registerPushToken($this->access)->assertCreated();

        mobileCall('DELETE', 'push-token', token: $this->access)->assertOk()->assertJsonPath('message', 'Push notifications are off for this device.');
        mobileCall('DELETE', 'push-token', token: $this->access)->assertOk();

        expect(MobilePushToken::query()->exists())->toBeFalse()
            ->and(AuditLog::query()->where('action', 'account.push_token_removed')->count())->toBe(1);
    });
});

describe('cleanup with the sign-in', function (): void {
    beforeEach(function (): void {
        $this->tablet = mobileSignIn($this->customer, 'Tablet')['access_token'];
        registerPushToken($this->access)->assertCreated();
        registerPushToken($this->tablet, PUSH_DEVICE_TOKEN.'-tablet')->assertCreated();
    });

    it('deletes the device\'s token on logout', function (): void {
        mobileCall('POST', 'auth/logout', token: $this->access)->assertOk();

        expect(MobilePushToken::query()->pluck('family_id')->all())->toBe([familyOf($this->customer, 'Tablet')]);
    });

    it('deletes every token on sign-out everywhere', function (): void {
        mobileCall('POST', 'auth/logout-all', token: $this->access)->assertOk();

        expect(MobilePushToken::query()->exists())->toBeFalse();
    });

    it('deletes the revoked device\'s token only', function (): void {
        mobileCall('DELETE', 'account/devices/'.familyOf($this->customer, 'Tablet'), token: $this->access)->assertOk();

        expect(MobilePushToken::query()->pluck('family_id')->all())->toBe([familyOf($this->customer)]);
    });

    it('keeps only the caller\'s token after a password change', function (): void {
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);

        mobileCall('PUT', 'account/password', ['current_password' => 'password', 'password' => 'a-much-longer-passphrase', 'password_confirmation' => 'a-much-longer-passphrase'], $this->access)->assertOk();

        expect(MobilePushToken::query()->pluck('family_id')->all())->toBe([familyOf($this->customer)]);
    });

    it('deletes the family\'s token when a used refresh token is replayed', function (): void {
        mobileCall('POST', 'auth/refresh', ['refresh_token' => $this->pair['refresh_token']])->assertOk();
        mobileCall('POST', 'auth/refresh', ['refresh_token' => $this->pair['refresh_token']])->assertUnauthorized();

        expect(MobilePushToken::query()->pluck('family_id')->all())->toBe([familyOf($this->customer, 'Tablet')]);
    });

    it('keeps the token across a normal refresh, and prunes tokens of expired sign-ins', function (): void {
        mobileCall('POST', 'auth/refresh', ['refresh_token' => $this->pair['refresh_token']])->assertOk();
        expect(MobilePushToken::query()->count())->toBe(2);

        MobileRefreshToken::query()->where('family_id', familyOf($this->customer, 'Tablet'))->update(['expires_at' => now()->subMinute()]);
        $this->artisan('auth:prune-mobile-tokens')->assertSuccessful();

        expect(MobilePushToken::query()->pluck('family_id')->all())->toBe([familyOf($this->customer)]);
    });

    it('never sends to a device whose sign-in is no longer live', function (): void {
        enablePushWithTestKey();
        fakeFcm();
        MobileRefreshToken::query()->where('family_id', familyOf($this->customer, 'Tablet'))->update(['revoked_at' => now()]);

        $this->customer->notify(new SupportTicketReplied(SupportTicket::factory()->create(['user_id' => $this->customer->id])->id));

        expect(fcmMessagesSent())->toHaveCount(1)
            ->and(fcmMessagesSent()[0]['message']['token'])->toBe(PUSH_DEVICE_TOKEN);
    });
});

describe('sending', function (): void {
    beforeEach(function (): void {
        registerPushToken($this->access)->assertCreated();
    });

    it('is a no-op that logs at debug level while push is disabled', function (): void {
        config(['push.enabled' => false]);
        Http::preventStrayRequests();
        Http::fake();
        Log::spy();

        $this->customer->notify(new SupportTicketReplied(SupportTicket::factory()->create(['user_id' => $this->customer->id])->id));

        Http::assertNothingSent();
        Log::shouldHaveReceived('debug')->withArgs(static fn (string $message): bool => $message === 'push.disabled')->atLeast()->once();
    });

    it('pushes a delivered order with the inbox text and ids only, never a code (canary)', function (): void {
        enablePushWithTestKey();
        fakeFcm();
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id], status: OrderStatus::Completed);
        deliveredUnitOf($order, PUSH_CANARY_CODE);

        app(SendFulfilmentNotifications::class)->handle(new OrderStatusChanged($order->id, OrderStatus::Processing, OrderStatus::Completed));

        $messages = fcmMessagesSent();
        expect($messages)->toHaveCount(1);
        $message = $messages[0]['message'];

        expect($message['token'])->toBe(PUSH_DEVICE_TOKEN)
            ->and($message['notification'])->toBe(['title' => "Order {$order->order_number} is ready", 'body' => 'Your order has been delivered. Open it to view your codes.'])
            ->and($message['data'])->toBe(['type' => 'order_delivered', 'category' => 'orders', 'link_type' => 'order', 'link_value' => $order->public_id, 'order_id' => $order->public_id])
            ->and($message['android']['notification']['channel_id'])->toBe('updates');

        $wire = json_encode($messages, JSON_THROW_ON_ERROR);
        expect($wire)->not->toContain(PUSH_CANARY_CODE)
            ->and($wire)->not->toContain('9876')
            ->and($wire)->not->toContain('redeem.example.com')
            ->and(array_keys($message))->toBe(['token', 'notification', 'data', 'android', 'apns']);

        Http::assertSent(static fn (Request $request): bool => $request->url() === 'https://fcm.googleapis.com/v1/projects/boxcards-test/messages:send'
            && $request->hasHeader('Authorization', 'Bearer ya29.test-access-token'));
        expect(MobilePushToken::query()->sole()->last_sent_at)->not->toBeNull();
    });

    it('pushes a support reply with the ticket number and id, and no message body', function (): void {
        enablePushWithTestKey();
        fakeFcm();
        $ticket = SupportTicket::factory()->create(['user_id' => $this->customer->id]);

        $this->customer->notify(new SupportTicketReplied($ticket->id));

        $message = fcmMessagesSent()[0]['message'];
        expect($message['notification'])->toBe(['title' => "Update on your support request {$ticket->ticket_number}", 'body' => 'Our support team has replied to your request.'])
            ->and($message['data'])->toBe(['type' => 'support_ticket_replied', 'category' => 'support', 'link_type' => 'ticket', 'link_value' => $ticket->public_id, 'ticket_id' => $ticket->public_id]);
    });

    it('writes the text in the customer\'s language', function (): void {
        enablePushWithTestKey();
        fakeFcm();
        $this->customer->forceFill(['locale' => 'ar'])->save();
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id], status: OrderStatus::Completed);

        $this->customer->notify(new OrderDelivered($order->id));

        expect(fcmMessagesSent()[0]['message']['notification']['title'])->toBe("الطلب {$order->order_number} جاهز");
    });

    it('pushes and files a verified payment failure, without the amount or provider message', function (): void {
        enablePushWithTestKey();
        fakeFcm();
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id], status: OrderStatus::PaymentProcessing);
        Payment::query()->where('order_id', $order->id)->update(['status' => PaymentStatus::Processing, 'failure_code' => null]);
        DB::table('orders')->where('id', $order->id)->update(['primary_payment_id' => null]);
        $payment = Payment::query()->where('order_id', $order->id)->sole();

        app(PaymentStateApplier::class)->apply($payment->id, new VerifiedPaymentState(status: ProviderPaymentStatus::Failed, failureCode: 'card_declined', failureMessage: 'Insufficient funds on card 4242'), OrderActor::system());

        $message = fcmMessagesSent()[0]['message'];
        expect($message['data'])->toBe(['type' => 'payment_failed', 'category' => 'orders', 'link_type' => 'order', 'link_value' => $order->public_id, 'order_id' => $order->public_id])
            ->and($message['notification']['title'])->toBe("Payment for order {$order->order_number} did not go through")
            ->and(json_encode($message))->not->toContain('card_declined')
            ->and(json_encode($message))->not->toContain('Insufficient')
            ->and(DB::table('notifications')->where('notifiable_id', $this->customer->id)->value('type'))->toBe('payment_failed');
    });

    it('respects the push preference: off means no push, the inbox still gets the entry', function (): void {
        enablePushWithTestKey();
        fakeFcm();
        NotificationPreference::query()->forceCreate(['user_id' => $this->customer->id, 'category' => 'orders', 'channel' => 'push', 'enabled' => false]);
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id], status: OrderStatus::Completed);

        $this->customer->notify(new OrderDelivered($order->id));
        $this->customer->notify(new PaymentFailed($order->id));

        expect(fcmMessagesSent())->toBe([])
            ->and(DB::table('notifications')->where('notifiable_id', $this->customer->id)->count())->toBe(2);
    });

    it('lists push in the channels only when the preference is on', function (): void {
        Notification::fake();
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id], status: OrderStatus::Completed);

        $this->customer->notify(new OrderDelivered($order->id));
        Notification::assertSentTo($this->customer, OrderDelivered::class, static fn (OrderDelivered $notification, array $channels): bool => $channels === ['mail', 'database', 'push']);

        NotificationPreference::query()->forceCreate(['user_id' => $this->customer->id, 'category' => 'support', 'channel' => 'push', 'enabled' => false]);
        $this->customer->notify(new SupportTicketReplied(SupportTicket::factory()->create(['user_id' => $this->customer->id])->id));
        Notification::assertSentTo($this->customer, SupportTicketReplied::class, static fn (SupportTicketReplied $notification, array $channels): bool => $channels === ['mail', 'database']);
    });

    it('deletes a token FCM reports as unregistered, and does not retry', function (): void {
        enablePushWithTestKey();
        fakeFcm(Http::response(['error' => ['code' => 404, 'message' => 'Requested entity was not found.', 'status' => 'NOT_FOUND', 'details' => [
            ['@type' => 'type.googleapis.com/google.firebase.fcm.v1.FcmError', 'errorCode' => 'UNREGISTERED'],
        ]]], 404));

        $this->customer->notify(new SupportTicketReplied(SupportTicket::factory()->create(['user_id' => $this->customer->id])->id));

        expect(MobilePushToken::query()->exists())->toBeFalse()
            ->and(fcmMessagesSent())->toHaveCount(1);
    });

    it('deletes a token FCM calls an invalid registration token or another project\'s', function (array $error, int $status): void {
        enablePushWithTestKey();
        fakeFcm(Http::response(['error' => $error], $status));

        $this->customer->notify(new SupportTicketReplied(SupportTicket::factory()->create(['user_id' => $this->customer->id])->id));

        expect(MobilePushToken::query()->exists())->toBeFalse();
    })->with([
        'invalid argument about the token' => [['code' => 400, 'message' => 'The registration token is not a valid FCM registration token', 'status' => 'INVALID_ARGUMENT'], 400],
        'sender id mismatch' => [['code' => 403, 'message' => 'SenderId mismatch', 'status' => 'PERMISSION_DENIED', 'details' => [['errorCode' => 'SENDER_ID_MISMATCH']]], 403],
    ]);

    it('keeps the token on other refusals and on a bare 404 (a wrong project id)', function (array $error, int $status): void {
        enablePushWithTestKey();
        fakeFcm(Http::response(['error' => $error], $status));

        $result = app(FcmClient::class)->send(PUSH_DEVICE_TOKEN, PushMessage::forInbox(InboxNotificationKind::SupportTicketReplied, ['number' => 'T-1'], LinkTargetType::Ticket, (string) Str::ulid(), 'en'));

        expect($result->outcome)->toBe(PushDeliveryOutcome::Failed)
            ->and(MobilePushToken::query()->count())->toBe(1);
    })->with([
        'bare 404' => [['code' => 404, 'message' => 'Not found', 'status' => 'NOT_FOUND'], 404],
        'invalid message' => [['code' => 400, 'message' => 'Invalid JSON payload received.', 'status' => 'INVALID_ARGUMENT'], 400],
    ]);

    it('asks for a retry on quota, server errors and an expired access token, honouring Retry-After', function (int $status, ?string $retryAfter): void {
        enablePushWithTestKey();
        fakeFcm(Http::response(['error' => ['code' => $status, 'status' => 'UNAVAILABLE']], $status, $retryAfter === null ? [] : ['Retry-After' => $retryAfter]));

        $result = app(FcmClient::class)->send(PUSH_DEVICE_TOKEN, PushMessage::forInbox(InboxNotificationKind::OrderDelivered, ['number' => 'BX-1'], LinkTargetType::Order, (string) Str::ulid(), 'en'));

        expect($result->outcome)->toBe(PushDeliveryOutcome::Retry)
            ->and($result->retryAfterSeconds)->toBe($retryAfter === null ? null : (int) $retryAfter);
    })->with([
        'quota' => [429, '30'],
        'unavailable' => [503, null],
        'expired access token' => [401, null],
    ]);

    it('caches the OAuth access token between sends', function (): void {
        enablePushWithTestKey();
        fakeFcm();
        $message = PushMessage::forInbox(InboxNotificationKind::OrderDelivered, ['number' => 'BX-1'], LinkTargetType::Order, (string) Str::ulid(), 'en');

        app(FcmClient::class)->send(PUSH_DEVICE_TOKEN, $message);
        app(FcmClient::class)->send(PUSH_DEVICE_TOKEN, $message);

        Http::assertSentCount(3);
        Http::assertSent(static fn (Request $request): bool => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer'
            && substr_count((string) $request['assertion'], '.') === 2);
    });

    it('fails without retrying and without sending when the credentials file is missing', function (): void {
        config(['push.enabled' => true, 'push.fcm.project_id' => 'boxcards-test', 'push.fcm.credentials_path' => storage_path('framework/testing/fcm-missing.json')]);
        Http::preventStrayRequests();
        Http::fake();

        $this->customer->notify(new SupportTicketReplied(SupportTicket::factory()->create(['user_id' => $this->customer->id])->id));

        Http::assertNothingSent();
        expect(MobilePushToken::query()->count())->toBe(1);
    });
});

describe('service-account JWT', function (): void {
    it('signs an RS256 assertion for the FCM scope that the public key verifies', function (): void {
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        openssl_pkey_export($key, $pem);
        $issuedAt = 1_800_000_000;

        $jwt = app(FcmClient::class)->assertion([
            'client_email' => 'push-sender@boxcards-test.iam.gserviceaccount.com',
            'private_key' => $pem,
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ], $issuedAt);

        [$header, $claims, $signature] = explode('.', $jwt);
        $decode = static fn (string $part): string => (string) base64_decode(strtr($part, '-_', '+/').str_repeat('=', (4 - strlen($part) % 4) % 4), true);

        expect(json_decode($decode($header), true))->toBe(['alg' => 'RS256', 'typ' => 'JWT'])
            ->and(json_decode($decode($claims), true))->toBe([
                'iss' => 'push-sender@boxcards-test.iam.gserviceaccount.com',
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $issuedAt,
                'exp' => $issuedAt + 3600,
            ])
            ->and(openssl_verify($header.'.'.$claims, $decode($signature), openssl_pkey_get_details($key)['key'], OPENSSL_ALGO_SHA256))->toBe(1)
            ->and($jwt)->not->toContain('=');
    });

    it('refuses a key that cannot sign', function (): void {
        app(FcmClient::class)->assertion(['client_email' => 'x@example.com', 'private_key' => 'not a key'], 1_800_000_000);
    })->throws(PushNotConfigured::class);
});
