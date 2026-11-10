<?php

namespace App\Domains\Mcp\Actions;

use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Contracts\McpWriteAction;
use App\Domains\Mcp\DTOs\PreparedAction;
use App\Domains\Mcp\Enums\McpWriteTool;
use App\Domains\Promotions\Enums\CouponCreatedByType;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Services\CouponService;
use App\Domains\Promotions\Services\DatabaseCheckoutCoupons;
use App\Domains\Shared\Rules\DecimalString;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Creates a coupon through CouponService with `created_by_type = mcp`. A fixed coupon is in the
 * base currency. The coupon never allows pricing below the margin floor, has a mandatory usage
 * limit and a one-use-per-buyer limit. Above the configured tier 1 caps it needs admin approval.
 */
class CreateCouponAction implements McpWriteAction
{
    public function __construct(private CouponService $coupons, private ExchangeRateRegistry $rates) {}

    public function tool(): McpWriteTool
    {
        return McpWriteTool::CreateCoupon;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:3', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('coupons', 'code')],
            'type' => ['required', Rule::enum(CouponType::class)],
            'value' => ['required', DecimalString::positiveMoney()],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'usage_limit' => ['required', 'integer', 'min:1', 'max:100000'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function prepare(array $validated): PreparedAction
    {
        $type = CouponType::from((string) $validated['type']);
        $value = bcadd((string) $validated['value'], '0', 6);
        $base = $this->rates->baseCurrency();

        if ($type === CouponType::Percent && bccomp($value, '100', 6) === 1) {
            throw ValidationException::withMessages(['value' => ['A percentage coupon cannot exceed 100.']]);
        }

        $cap = $type === CouponType::Percent ? (string) config('mcp.coupon_tier1_max_percent') : (string) config('mcp.coupon_tier1_max_fixed_base');

        $arguments = [
            'code' => DatabaseCheckoutCoupons::normalize((string) $validated['code']),
            'type' => $type->value,
            'value' => $value,
            'starts_at' => CarbonImmutable::parse((string) $validated['starts_at'])->utc()->toIso8601String(),
            'ends_at' => CarbonImmutable::parse((string) $validated['ends_at'])->utc()->toIso8601String(),
            'usage_limit' => (int) $validated['usage_limit'],
            'reason' => (string) $validated['reason'],
        ];

        $display = $type === CouponType::Percent ? bcadd($value, '0', 2).'%' : bcadd($value, '0', 2).' '.$base;

        return new PreparedAction(
            $arguments,
            bccomp($value, $cap, 6) === 1 ? 2 : 1,
            "Create coupon {$arguments['code']}: {$display} off, {$arguments['usage_limit']} uses, from {$arguments['starts_at']} to {$arguments['ends_at']}.",
        );
    }

    public function execute(array $arguments, ?User $approver, int $serviceAccountId): array
    {
        $type = CouponType::from((string) $arguments['type']);

        $coupon = $this->coupons->create([
            'code' => $arguments['code'],
            'type' => $type,
            'value' => $arguments['value'],
            'currency' => $type === CouponType::Fixed ? $this->rates->baseCurrency() : null,
            'usage_limit' => $arguments['usage_limit'],
            'usage_limit_per_user' => 1,
            'allow_below_margin' => false,
            'starts_at' => CarbonImmutable::parse((string) $arguments['starts_at']),
            'ends_at' => CarbonImmutable::parse((string) $arguments['ends_at']),
            'is_active' => true,
            'created_by_type' => CouponCreatedByType::Mcp,
        ]);

        return [
            'code' => $coupon->code,
            'type' => $coupon->type->value,
            'value' => bcadd((string) $coupon->value, '0', 2),
            'currency' => $coupon->currency,
            'usage_limit' => $coupon->usage_limit,
            'starts_at' => $coupon->starts_at?->toIso8601String(),
            'ends_at' => $coupon->ends_at?->toIso8601String(),
            'created_by' => $coupon->created_by_type->value,
        ];
    }
}
