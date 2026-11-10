<?php

namespace App\Domains\Mcp\Actions;

use App\Domains\Fulfillment\Services\FulfilmentAdminService;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Contracts\McpWriteAction;
use App\Domains\Mcp\DTOs\PreparedAction;
use App\Domains\Mcp\Enums\McpWriteTool;
use App\Domains\Mcp\Repositories\McpOrderRepository;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use Illuminate\Support\Facades\DB;

/**
 * Retries a unit only when the supplier engine has verified that its latest attempt was not
 * accepted (`rejected` or `not_accepted_verified`). A unit with no attempt, or whose latest attempt
 * is unresolved (`submitting`, `accepted`, `pending_verification`) or succeeded, is never retried
 * through MCP. Always tier 2: it runs only after a staff member with `orders.review` approves,
 * as that staff member, through FulfilmentAdminService and the order state machine.
 */
class RetryVerifiedFailedOrderAction implements McpWriteAction
{
    private const array VERIFIED_NOT_ACCEPTED = [SupplierTransactionStatus::Rejected, SupplierTransactionStatus::NotAcceptedVerified];

    public function __construct(private McpOrderRepository $orders, private FulfilmentAdminService $fulfilment) {}

    public function tool(): McpWriteTool
    {
        return McpWriteTool::RetryVerifiedFailedOrder;
    }

    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:24'],
            'unit_public_id' => ['required', 'string', 'ulid'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function prepare(array $validated): PreparedAction
    {
        [$order, $unit] = $this->target((string) $validated['order_number'], (string) $validated['unit_public_id']);
        $this->assertVerifiedNotAccepted($unit);

        return new PreparedAction(
            ['order_number' => $order->order_number, 'unit_public_id' => $unit->public_id, 'reason' => (string) $validated['reason']],
            2,
            "Retry unit {$unit->public_id} of order {$order->order_number}; its latest supplier attempt is verified as not accepted.",
        );
    }

    public function execute(array $arguments, ?User $approver, int $serviceAccountId): array
    {
        if ($approver === null) {
            throw new ApplicationException('A supplier retry needs approval by staff in the admin panel.', 'confirmation_required', 403);
        }

        [$order, $unit] = $this->target((string) $arguments['order_number'], (string) $arguments['unit_public_id']);

        $order = DB::transaction(function () use ($order, $unit, $approver, $arguments): Order {
            $this->orders->lockOrder($order);
            $this->orders->lockUnit($unit);
            $this->assertVerifiedNotAccepted($unit);

            return $this->fulfilment->retryVerifiedUnit($order, $unit->public_id, $approver, (string) $arguments['reason']);
        }, 3);

        return ['order_number' => $order->order_number, 'unit_public_id' => $unit->public_id, 'order_status' => $order->status->value];
    }

    /**
     * @return array{0: Order, 1: OrderItemUnit}
     */
    private function target(string $orderNumber, string $unitPublicId): array
    {
        $order = $this->orders->findStatusByNumber($orderNumber);
        $unit = $order === null ? null : $this->orders->findUnit($order, $unitPublicId);

        if ($order === null || $unit === null) {
            throw new ApplicationException('No such unit on this order.', 'not_found', 404);
        }

        return [$order, $unit];
    }

    private function assertVerifiedNotAccepted(OrderItemUnit $unit): void
    {
        $latest = $this->orders->latestSupplierTransaction($unit);

        if ($latest === null || ! in_array($latest->status, self::VERIFIED_NOT_ACCEPTED, true)) {
            throw new ApplicationException(
                'Only a unit whose latest supplier attempt is verified as not accepted (rejected or not_accepted_verified) can be retried. '
                .'Attempts in pending_verification are never retried through MCP.',
                'retry_not_allowed',
                409,
                ['latest_attempt_status' => [$latest?->status->value ?? 'none']],
            );
        }
    }
}
