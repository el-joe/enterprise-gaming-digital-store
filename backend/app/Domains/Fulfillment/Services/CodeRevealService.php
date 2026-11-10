<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Fulfillment\DTOs\RevealContext;
use App\Domains\Fulfillment\DTOs\RevealedCode;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType as Actor;
use App\Domains\Fulfillment\Exceptions\CodeRevealDenied;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Models\DigitalCodeAccess;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use Illuminate\Support\Str;

/**
 * The audited reveal (architecture section 10). Every attempt, granted or denied, is written to
 * audit_logs, and to digital_code_accesses when a code exists. Only the owning customer, the
 * verified guest of the order, or staff holding `digital_codes.reveal` with a written reason may
 * see a code, and only for a paid order whose unit is delivered.
 */
class CodeRevealService
{
    public function __construct(private DigitalCodeVault $vault, private AuditLogger $audit) {}

    /**
     * @throws CodeRevealDenied
     */
    public function revealUnit(Order $order, OrderItemUnit $unit, RevealContext $context): RevealedCode
    {
        $code = $unit->order_id === $order->id ? DigitalCode::query()->where('order_item_unit_id', $unit->id)->first() : null;

        $denial = match (true) {
            $unit->order_id !== $order->id => 'not_found',
            $context->actorType === Actor::Customer && ($context->user === null || $order->user_id !== $context->user->id) => 'not_owner',
            $context->actorType === Actor::Customer && $context->user->email_verified_at === null => 'email_unverified',
            $context->actorType === Actor::Customer && ! $context->sessionVerified => 'recent_auth_required',
            $context->actorType === Actor::Guest && ($order->user_id !== null || $order->guest_email_verified_at === null || ! $context->sessionVerified) => 'guest_not_verified',
            $context->actorType === Actor::Staff => 'forbidden',
            ! self::orderIsPaid($order) => 'unpaid',
            ! self::unitIsDelivered($unit, $code !== null) => 'not_delivered',
            default => null,
        };

        if ($denial !== null) {
            $this->deny($code, $context, $denial, ['order_id' => $order->id, 'unit_id' => $unit->id]);
        }

        return $this->grant($code, $unit, $context);
    }

    /**
     * Whether a reveal of this unit would pass the order and unit checks of `revealUnit` (paid
     * order, delivered unit holding a code). The viewer checks (owner or guest proofs, verified
     * email, recent authentication) are not part of it: this is the `revealable` hint of the
     * order detail (M-A5), and `revealUnit` stays the only gate.
     */
    public static function isRevealable(Order $order, OrderItemUnit $unit, bool $hasCode): bool
    {
        return self::orderIsPaid($order) && self::unitIsDelivered($unit, $hasCode);
    }

    /**
     * @throws CodeRevealDenied
     */
    public function revealForStaff(DigitalCode $code, RevealContext $context): RevealedCode
    {
        $denial = match (true) {
            $context->user === null || ! $context->user->isStaff() || ! $context->user->can(PermissionName::DigitalCodesReveal->value) => 'forbidden',
            trim((string) $context->reason) === '' => 'reason_required',
            default => null,
        };

        if ($denial !== null) {
            $this->deny($code, $context, $denial, ['digital_code_id' => $code->id]);
        }

        $unit = $code->order_item_unit_id === null ? null : OrderItemUnit::query()->find($code->order_item_unit_id);

        return $this->grant($code, $unit, $context);
    }

    /**
     * Product redemption instructions to show beside the code.
     */
    public function instructionsFor(OrderItemUnit $unit): ?string
    {
        return OrderItemUnit::query()->whereKey($unit->id)->with('orderItem.product:id,instructions')->first()?->orderItem?->product?->instructions;
    }

    private static function orderIsPaid(Order $order): bool
    {
        return $order->primary_payment_id !== null && $order->paid_at !== null;
    }

    private static function unitIsDelivered(OrderItemUnit $unit, bool $hasCode): bool
    {
        return $unit->status === OrderItemUnitStatus::Delivered && $hasCode;
    }

    /**
     * @param  array<string, mixed>  $subject
     *
     * @throws CodeRevealDenied
     */
    private function deny(?DigitalCode $code, RevealContext $context, string $reason, array $subject): never
    {
        $this->write($code, $context, false, $reason, $subject);

        [$message, $status] = match ($reason) {
            'not_found', 'not_owner' => ['The requested resource was not found.', 404],
            'recent_auth_required' => ['Confirm your password to reveal codes.', 423],
            'reason_required' => ['A reason is required.', 422],
            default => ['This code cannot be revealed.', 403],
        };

        throw new CodeRevealDenied($reason, $message, $status);
    }

    private function grant(DigitalCode $code, ?OrderItemUnit $unit, RevealContext $context): RevealedCode
    {
        $revealed = $this->vault->decrypt($code);

        $this->write($code, $context, true, null, ['digital_code_id' => $code->id]);

        if ($unit !== null && $unit->first_revealed_at === null && $context->actorType !== Actor::Staff) {
            OrderItemUnit::query()->whereKey($unit->id)->whereNull('first_revealed_at')->update(['first_revealed_at' => now()]);
        }

        return $revealed;
    }

    /**
     * @param  array<string, mixed>  $subject
     */
    private function write(?DigitalCode $code, RevealContext $context, bool $granted, ?string $reason, array $subject): void
    {
        if ($code !== null) {
            (new DigitalCodeAccess)->forceFill([
                'digital_code_id' => $code->id,
                'actor_type' => $context->actorType,
                'actor_id' => $context->user?->id,
                'purpose' => $context->purpose,
                'reason' => $context->reason === null ? null : Str::limit($context->reason, 497),
                'granted' => $granted,
                'denial_reason' => $reason,
                'ip' => $context->ip,
                'user_agent' => $context->userAgent === null ? null : Str::limit($context->userAgent, 252),
            ])->save();
        }

        $this->audit->record(new AuditEntry(
            action: $granted ? 'digital_codes.revealed' : 'digital_codes.reveal_denied',
            subject: $code,
            after: [...$subject, 'actor' => $context->actorType->value, 'purpose' => $context->purpose->value, 'denial_reason' => $reason],
            reason: $context->reason,
        ));
    }
}
