<?php

namespace App\Domains\Orders\Services;

use App\Domains\Identity\Enums\OtpChallengePurpose;
use App\Domains\Identity\Models\OtpChallenge;
use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Exceptions\GuestOtpInvalid;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Notifications\GuestOrderAccessCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Guest email verification by one-time code (architecture 13). Sending answers the same way
 * whether or not the order and email match, so it reveals nothing. Codes are stored as HMACs,
 * expire after ten minutes, allow five attempts, and are never logged or queued.
 */
class GuestOrderAccessService
{
    public const int CODE_TTL_MINUTES = 10;

    public const int MAX_ATTEMPTS = 5;

    public const int MAX_SENDS_PER_HOUR = 5;

    public function sendCode(string $orderPublicId, string $email): void
    {
        $order = Order::query()->where('public_id', $orderPublicId)->whereNull('user_id')->first();
        $email = mb_strtolower(trim($email));

        if ($order === null || $order->guest_email === null || ! hash_equals($order->guest_email, $email)) {
            return;
        }

        if (RateLimiter::tooManyAttempts($this->sendKey($order), self::MAX_SENDS_PER_HOUR)) {
            return;
        }

        RateLimiter::hit($this->sendKey($order), 3600);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $identifier = $this->identifier($order);

        DB::transaction(function () use ($identifier, $code): void {
            OtpChallenge::query()
                ->where('purpose', OtpChallengePurpose::GuestOrderAccess)
                ->where('identifier_hash', $identifier)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            (new OtpChallenge)->forceFill([
                'purpose' => OtpChallengePurpose::GuestOrderAccess,
                'identifier_hash' => $identifier,
                'code_hash' => $this->codeHash($identifier, $code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            ])->save();
        });

        Notification::route('mail', $order->guest_email)->notify(new GuestOrderAccessCode($code, $order->order_number, self::CODE_TTL_MINUTES));
    }

    /**
     * @throws GuestOtpInvalid
     */
    public function verify(string $orderPublicId, string $code): Order
    {
        $order = Order::query()->where('public_id', $orderPublicId)->whereNull('user_id')->first() ?? throw new GuestOtpInvalid;
        $identifier = $this->identifier($order);

        $verified = DB::transaction(function () use ($order, $identifier, $code): bool {
            $challenge = OtpChallenge::query()
                ->where('purpose', OtpChallengePurpose::GuestOrderAccess)
                ->where('identifier_hash', $identifier)
                ->whereNull('consumed_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($challenge === null || $challenge->expires_at->isPast() || $challenge->attempts >= self::MAX_ATTEMPTS) {
                return false;
            }

            if (! hash_equals($challenge->code_hash, $this->codeHash($identifier, $code))) {
                $challenge->forceFill(['attempts' => $challenge->attempts + 1])->save();

                return false;
            }

            $challenge->forceFill(['consumed_at' => now()])->save();

            if ($order->guest_email_verified_at === null) {
                $order->forceFill(['guest_email_verified_at' => now()])->save();

                (new OrderEvent)->forceFill([
                    'order_id' => $order->id,
                    'type' => 'guest_email_verified',
                    'actor_type' => OrderEventActorType::Customer,
                    'message' => 'Guest email verified by one-time code.',
                ])->save();
            }

            return true;
        });

        if (! $verified) {
            throw new GuestOtpInvalid;
        }

        return $order;
    }

    private function identifier(Order $order): string
    {
        return hash_hmac('sha256', 'guest_order:'.$order->public_id, (string) config('app.key'));
    }

    private function codeHash(string $identifier, string $code): string
    {
        return hash_hmac('sha256', $identifier.'|'.$code, (string) config('app.key'));
    }

    private function sendKey(Order $order): string
    {
        return 'guest-order-otp:'.$order->public_id;
    }
}
