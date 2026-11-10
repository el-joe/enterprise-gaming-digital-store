<?php

namespace App\Domains\Orders\Services;

use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Enums\OrderStatus;

/**
 * The allowed transitions of PROJECT_ARCHITECTURE.md 6.2, with the actor types each row names
 * and, for staff rows, the permission required. Anything not listed is illegal.
 */
class OrderTransitionTable
{
    private const array PROVIDER = ['webhook', 'system'];

    private const array SYSTEM = ['system'];

    /**
     * @var array<string, array<string, array{actors: list<string>, permission?: string}>>
     */
    private const array ROWS = [
        'pending_payment' => [
            'payment_processing' => ['actors' => self::PROVIDER],
            'paid' => ['actors' => self::PROVIDER],
            'failed' => ['actors' => self::SYSTEM],
            'cancelled' => ['actors' => ['customer', 'system']],
            'manual_review' => ['actors' => self::PROVIDER],
        ],
        'payment_processing' => [
            'manual_review' => ['actors' => self::PROVIDER],
            'paid' => ['actors' => self::PROVIDER],
            'pending_payment' => ['actors' => self::PROVIDER],
            'failed' => ['actors' => self::PROVIDER],
        ],
        'paid' => [
            'processing' => ['actors' => self::SYSTEM],
            'manual_review' => ['actors' => self::SYSTEM],
        ],
        'processing' => [
            'completed' => ['actors' => self::SYSTEM],
            'supplier_pending' => ['actors' => self::SYSTEM],
            'pending_verification' => ['actors' => self::SYSTEM],
            'manual_review' => ['actors' => self::SYSTEM],
            'partially_fulfilled' => ['actors' => self::SYSTEM],
            'refund_pending' => ['actors' => self::SYSTEM],
        ],
        'supplier_pending' => [
            'completed' => ['actors' => self::PROVIDER],
            'pending_verification' => ['actors' => self::PROVIDER],
            'manual_review' => ['actors' => self::PROVIDER],
            'partially_fulfilled' => ['actors' => self::PROVIDER],
            'refund_pending' => ['actors' => self::PROVIDER],
        ],
        'pending_verification' => [
            'supplier_pending' => ['actors' => self::SYSTEM],
            'completed' => ['actors' => self::SYSTEM],
            'processing' => ['actors' => ['system', 'staff'], 'permission' => 'orders.reconcile'],
            'partially_fulfilled' => ['actors' => self::SYSTEM],
            'refund_pending' => ['actors' => self::SYSTEM],
            'manual_review' => ['actors' => self::SYSTEM],
        ],
        'manual_review' => [
            'processing' => ['actors' => ['staff'], 'permission' => 'orders.review'],
            'completed' => ['actors' => ['staff'], 'permission' => 'orders.fulfill_manual'],
            'refund_pending' => ['actors' => ['staff'], 'permission' => 'refunds.create'],
            'cancelled' => ['actors' => ['staff'], 'permission' => 'orders.review'],
        ],
        'partially_fulfilled' => [
            'refund_pending' => ['actors' => self::SYSTEM],
            'processing' => ['actors' => ['staff'], 'permission' => 'orders.review'],
        ],
        'refund_pending' => [
            'refunded' => ['actors' => self::PROVIDER],
            'partially_refunded' => ['actors' => self::PROVIDER],
            'manual_review' => ['actors' => self::PROVIDER],
        ],
        'completed' => [
            'refund_pending' => ['actors' => ['staff'], 'permission' => 'refunds.create'],
        ],
        'partially_refunded' => [
            'refund_pending' => ['actors' => ['staff'], 'permission' => 'refunds.create'],
        ],
        'cancelled' => [
            'manual_review' => ['actors' => self::PROVIDER],
        ],
        'failed' => [
            'manual_review' => ['actors' => self::PROVIDER],
        ],
    ];

    /**
     * @return array{actors: list<OrderEventActorType>, permission: ?PermissionName}|null
     */
    public function rule(OrderStatus $from, OrderStatus $to): ?array
    {
        $row = self::ROWS[$from->value][$to->value] ?? null;

        if ($row === null) {
            return null;
        }

        return [
            'actors' => array_map(static fn (string $actor): OrderEventActorType => OrderEventActorType::from($actor), $row['actors']),
            'permission' => isset($row['permission']) ? PermissionName::from($row['permission']) : null,
        ];
    }

    /**
     * Every allowed pair, for tests and documentation.
     *
     * @return list<array{0: OrderStatus, 1: OrderStatus}>
     */
    public function pairs(): array
    {
        $pairs = [];

        foreach (self::ROWS as $from => $targets) {
            foreach (array_keys($targets) as $to) {
                $pairs[] = [OrderStatus::from($from), OrderStatus::from($to)];
            }
        }

        return $pairs;
    }
}
