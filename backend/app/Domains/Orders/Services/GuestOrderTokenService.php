<?php

namespace App\Domains\Orders\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Orders\DTOs\IssuedGuestOrderToken;
use App\Domains\Orders\Enums\GuestOrderTokenSource;
use App\Domains\Orders\Models\GuestOrderToken;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Repositories\GuestOrderTokenRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Guest order tokens for stateless clients (M-A3, ADR-036). A token is an opaque random value
 * returned once, stored only as a SHA-256 hash, scoped to one guest order and expiring after
 * `orders.guest_order_token_ttl_hours`. It stands in for what the browser session holds:
 *
 * - placement or OTP verification opens the order (status, verify, capture, retry), like
 *   `OrderStatusReader::SESSION_KEY`;
 * - the signed delivery link and the email one-time code are recorded on the token, and a guest
 *   reveal needs both, like the session keys of `GuestOrderCodeController`.
 *
 * A token never opens an order that belongs to a customer account. Verifying the one-time code
 * always issues a new token (carrying a link proof of the presented token, which is deleted), so a
 * token gains reveal rights only by rotation.
 */
class GuestOrderTokenService
{
    public function __construct(private GuestOrderTokenRepository $tokens, private AuditLogger $audit) {}

    /**
     * For the stateless guest that placed (or replayed the placement of) this guest order.
     */
    public function issueForPlacement(string $orderPublicId): IssuedGuestOrderToken
    {
        return $this->issue($this->guestOrder($orderPublicId), GuestOrderTokenSource::Placement);
    }

    /**
     * The guest order with this public id; not found for an unknown or a customer's order.
     *
     * @throws ModelNotFoundException
     */
    public function guestOrder(string $orderPublicId): Order
    {
        return Order::query()->where('public_id', $orderPublicId)->whereNull('user_id')->firstOrFail();
    }

    /**
     * After a verified email one-time code: a new token that opens the order and carries the OTP
     * proof, plus the link proof of the presented token for the same order, if any.
     */
    public function issueAfterOtp(Order $order, ?string $presentedToken): IssuedGuestOrderToken
    {
        return DB::transaction(function () use ($order, $presentedToken): IssuedGuestOrderToken {
            $previous = $this->live($order, $presentedToken);
            $issued = $this->issue($order, GuestOrderTokenSource::Otp, otpVerified: true, linkVerifiedAt: $previous?->link_verified_at?->toImmutable());
            $previous?->delete();

            $this->audited('orders.guest_order_token_issued', $order, ['source' => GuestOrderTokenSource::Otp->value, 'link_verified' => $previous?->link_verified_at !== null]);

            return $issued;
        });
    }

    /**
     * The signed delivery link was opened by this client: record it on the presented token, or
     * issue a link-only token (which does not open the order) when none is presented.
     */
    public function recordLinkProof(Order $order, ?string $presentedToken): ?IssuedGuestOrderToken
    {
        $token = $this->live($order, $presentedToken);

        if ($token !== null) {
            $token->forceFill(['link_verified_at' => now(), 'last_used_at' => now()])->save();
            $this->audited('orders.guest_order_link_opened', $order, ['source' => $token->source->value]);

            return null;
        }

        $issued = $this->issue($order, GuestOrderTokenSource::Link, linkVerifiedAt: now()->toImmutable());
        $this->audited('orders.guest_order_token_issued', $order, ['source' => GuestOrderTokenSource::Link->value, 'link_verified' => true]);

        return $issued;
    }

    /**
     * The live token for this guest order, or null for an unknown, expired or other order's token
     * and for an order that belongs to a customer account.
     */
    public function live(Order $order, ?string $presentedToken): ?GuestOrderToken
    {
        if ($order->user_id !== null || $presentedToken === null || $presentedToken === '' || strlen($presentedToken) > 128) {
            return null;
        }

        return $this->tokens->findLive(self::hash($presentedToken), $order->id);
    }

    /**
     * Whether the token opens this guest order for status and payment calls.
     */
    public function opensOrder(string $orderPublicId, ?string $presentedToken): bool
    {
        $order = $presentedToken === null ? null : Order::query()->where('public_id', $orderPublicId)->first();
        $token = $order === null ? null : $this->live($order, $presentedToken);

        if ($token === null || ! $token->opensOrder()) {
            return false;
        }

        $token->forceFill(['last_used_at' => now()])->save();

        return true;
    }

    /**
     * Whether the token holds both proofs a guest reveal needs for this order.
     */
    public function provesGuestIdentity(Order $order, ?string $presentedToken): bool
    {
        return $this->live($order, $presentedToken)?->provesGuestIdentity() ?? false;
    }

    public static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    private function issue(Order $order, GuestOrderTokenSource $source, bool $otpVerified = false, ?CarbonImmutable $linkVerifiedAt = null): IssuedGuestOrderToken
    {
        $plain = Str::random(64);
        $expiresAt = now()->addHours((int) config('orders.guest_order_token_ttl_hours'))->toImmutable();

        (new GuestOrderToken)->forceFill([
            'order_id' => $order->id,
            'token_hash' => self::hash($plain),
            'source' => $source,
            'link_verified_at' => $linkVerifiedAt,
            'otp_verified_at' => $otpVerified ? now() : null,
            'expires_at' => $expiresAt,
        ])->save();

        return new IssuedGuestOrderToken($plain, $order->public_id, $expiresAt);
    }

    /**
     * @param  array<string, mixed>  $after
     */
    private function audited(string $action, Order $order, array $after): void
    {
        $this->audit->record(new AuditEntry(
            action: $action,
            subject: $order,
            after: $after,
            actorType: AuditLogActorType::Customer,
        ));
    }
}
