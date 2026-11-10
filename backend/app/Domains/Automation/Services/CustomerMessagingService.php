<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\Enums\CustomerMessageStatus;
use App\Domains\Automation\Models\AutomationCustomerMessage;
use App\Domains\Automation\Models\AutomationOutbox;
use App\Domains\Automation\Repositories\CustomerSegmentRepository;
use App\Domains\Customers\Services\CustomerContactPolicy;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\Order;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Support\Models\SupportTicket;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Secondary-channel customer messaging for the D workflows (ADR-013). Laravel decides who may be
 * contacted (CustomerContactPolicy) and writes the message text; n8n only delivers it on the SMS
 * channel and reports the outcome. Customer transactional email stays in Laravel. Messages never
 * contain codes, PINs, payment data or anything beyond an order number and a link.
 */
class CustomerMessagingService
{
    /**
     * Event workflows and the outbox event each may act on.
     */
    private const array EVENT_WORKFLOWS = [
        'D01' => AutomationOutboxWriter::ORDER_CREATED,
        'D02' => AutomationOutboxWriter::PAYMENT_SUCCEEDED,
        'D03' => AutomationOutboxWriter::ORDER_DELIVERED,
        'D04' => AutomationOutboxWriter::ORDER_FAILED,
        'D05' => AutomationOutboxWriter::TICKET_UPDATED,
    ];

    private const array SEGMENT_WORKFLOWS = ['abandoned' => 'D06', 'review-eligible' => 'D07', 'reactivation' => 'D08', 'recommendations' => 'D09'];

    public function __construct(
        private CustomerContactPolicy $policy,
        private CustomerSegmentRepository $segments,
    ) {}

    /**
     * D01 to D05: whether the customer behind an outbox event may get an SMS, and its text.
     *
     * @return array<string, mixed>
     */
    public function prepare(AutomationCaller $caller, string $eventId): array
    {
        $expected = self::EVENT_WORKFLOWS[$caller->workflowId] ?? null;

        if ($expected === null) {
            throw new ApplicationException('Only the event workflows D01 to D05 prepare event messages.', 'forbidden', 403);
        }

        $event = AutomationOutbox::query()->where('event_id', $eventId)->first();

        if ($event === null) {
            throw new ApplicationException('No event has this id.', 'not_found', 404);
        }

        if ($event->event_type !== $expected) {
            throw new ApplicationException("Workflow {$caller->workflowId} handles {$expected} events only.", 'event_type_mismatch', 422);
        }

        $dedupeKey = "{$caller->workflowId}:{$event->event_id}";
        $channel = $this->channel();

        if ($this->alreadySent($dedupeKey)) {
            return ['eligible' => false, 'reason' => 'already_sent', 'dedupe_key' => $dedupeKey];
        }

        [$user, $category, $text] = $expected === AutomationOutboxWriter::TICKET_UPDATED
            ? $this->ticketMessage((string) ($event->payload['ticket_public_id'] ?? ''))
            : $this->orderMessage($expected, (string) ($event->payload['order_public_id'] ?? ''));

        $refusal = $this->policy->refusal($user, $category, $channel);

        if ($refusal !== null) {
            return ['eligible' => false, 'reason' => $refusal, 'dedupe_key' => $dedupeKey];
        }

        return [
            'eligible' => true,
            'dedupe_key' => $dedupeKey,
            'channel' => $channel->value,
            'category' => $category->value,
            'recipient' => ['customer_id' => $user->public_id, 'phone' => $user->phone, 'locale' => $user->locale],
            'message' => ['text' => $text],
        ];
    }

    /**
     * D06 to D09: eligible recipients, each with a dedupe key and the message text.
     *
     * @return array<string, mixed>
     */
    public function segment(AutomationCaller $caller, string $segment): array
    {
        if ((self::SEGMENT_WORKFLOWS[$segment] ?? null) !== $caller->workflowId) {
            throw new ApplicationException("Workflow {$caller->workflowId} may not read the {$segment} segment.", 'forbidden', 403);
        }

        $now = CarbonImmutable::now();
        $channel = $this->channel();
        $limit = (int) config('automation.segment_page_size');
        $store = (string) config('automation.store_name');
        $url = rtrim((string) config('app.url'), '/');

        $recipients = match ($segment) {
            'abandoned' => $this->segments->abandonedCarts(
                $now->subMinutes((int) config('automation.abandoned_cart_after_minutes')),
                $now->subHours((int) config('automation.abandoned_cart_max_age_hours')),
                $channel,
                $limit,
            )->map(static fn (array $row): array => self::recipient($row, "D06:cart:{$row['cart_id']}", "{$store}: you left items in your cart. Complete your order at {$url}/cart . Manage messages in your account settings."))->all(),
            'review-eligible' => $this->segments->reviewEligible(
                $now->subDays((int) config('automation.review_request_until_days')),
                $now->subDays((int) config('automation.review_request_after_days')),
                $channel,
                $limit,
            )->map(static fn (array $row): array => self::recipient($row, "D07:review:{$row['order_item_id']}", "{$store}: how was {$row['product_name']}? Leave a review at {$url}/account/orders/{$row['order_id']} . Manage messages in your account settings."))->all(),
            'reactivation' => $this->segments->reactivation(
                $now->subDays((int) config('automation.reactivation_until_days')),
                $now->subDays((int) config('automation.reactivation_after_days')),
                $now->format('Y-m'),
                $channel,
                $limit,
            )->map(fn (array $row): array => self::recipient($row, "D08:reactivation:{$row['customer_id']}:{$now->format('Y-m')}", "{$store}: it has been a while. See what is new at {$url} . Manage messages in your account settings."))->all(),
            'recommendations' => $this->recommendations($now, $channel, $limit, $store, $url),
            default => throw new ApplicationException('Unknown segment.', 'not_found', 404),
        };

        return [
            'segment' => $segment,
            'channel' => $channel->value,
            'category' => NotificationPreferenceCategory::Marketing->value,
            'consent_rule' => 'Latest marketing consent for the channel is a grant, no disabled marketing preference, active account with a phone number; customers already sent this message are excluded.',
            'page_size' => $limit,
            'recipients' => array_values($recipients),
        ];
    }

    /**
     * Record that n8n sent (or failed to send) a message. Idempotent per dedupe key: a message
     * already logged as sent is never changed; a failed one may be logged again.
     *
     * @param  array{dedupe_key: string, customer_id: string, status: string, provider_message_id?: string|null, error?: string|null}  $input
     * @return array{dedupe_key: string, status: string, repeated: bool}
     */
    public function log(AutomationCaller $caller, array $input): array
    {
        if (! str_starts_with($input['dedupe_key'], $caller->workflowId.':')) {
            throw new ApplicationException('The dedupe key belongs to another workflow.', 'validation_failed', 422, ['dedupe_key' => ['The dedupe key must start with the workflow id.']]);
        }

        $category = $this->categoryFor($caller->workflowId);
        $user = User::query()->where('public_id', $input['customer_id'])->first();

        if ($user === null) {
            throw new ApplicationException('No customer has this id.', 'validation_failed', 422, ['customer_id' => ['No customer has this id.']]);
        }

        $status = CustomerMessageStatus::from($input['status']);
        $eventId = isset(self::EVENT_WORKFLOWS[$caller->workflowId]) ? substr($input['dedupe_key'], 4) : null;

        try {
            return DB::transaction(function () use ($caller, $input, $category, $user, $status, $eventId): array {
                $message = AutomationCustomerMessage::query()->where('dedupe_key', $input['dedupe_key'])->lockForUpdate()->first();

                if ($message !== null && $message->status === CustomerMessageStatus::Sent) {
                    return ['dedupe_key' => $input['dedupe_key'], 'status' => $message->status->value, 'repeated' => true];
                }

                $message ??= new AutomationCustomerMessage;
                $message->forceFill([
                    'workflow_id' => $caller->workflowId,
                    'user_id' => $user->id,
                    'channel' => $this->channel()->value,
                    'category' => $category,
                    'dedupe_key' => $input['dedupe_key'],
                    'event_id' => $eventId !== null && preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $eventId) === 1 ? $eventId : null,
                    'status' => $status,
                    'provider_message_id' => $input['provider_message_id'] ?? null,
                    'error' => isset($input['error']) ? mb_substr((string) $input['error'], 0, 500) : null,
                ])->save();

                if ($status === CustomerMessageStatus::Sent && $caller->workflowId === 'D06') {
                    Cart::query()->where('public_id', substr($input['dedupe_key'], strlen('D06:cart:')))->where('status', CartStatus::Active->value)->update(['reminder_sent_at' => now()]);
                }

                return ['dedupe_key' => $input['dedupe_key'], 'status' => $status->value, 'repeated' => false];
            });
        } catch (UniqueConstraintViolationException) {
            return ['dedupe_key' => $input['dedupe_key'], 'status' => CustomerMessageStatus::Sent->value, 'repeated' => true];
        }
    }

    private function channel(): NotificationPreferenceChannel
    {
        return NotificationPreferenceChannel::from((string) config('automation.customer_channel'));
    }

    private function categoryFor(string $workflowId): NotificationPreferenceCategory
    {
        return match ($workflowId) {
            'D01', 'D02', 'D03', 'D04' => NotificationPreferenceCategory::Orders,
            'D05' => NotificationPreferenceCategory::Support,
            'D06', 'D07', 'D08', 'D09' => NotificationPreferenceCategory::Marketing,
            default => throw new ApplicationException('Only customer workflows log customer messages.', 'forbidden', 403),
        };
    }

    private function alreadySent(string $dedupeKey): bool
    {
        return AutomationCustomerMessage::query()->where('dedupe_key', $dedupeKey)->where('status', CustomerMessageStatus::Sent)->exists();
    }

    /**
     * @return array{0: ?User, 1: NotificationPreferenceCategory, 2: string}
     */
    private function orderMessage(string $eventType, string $orderPublicId): array
    {
        $order = Order::query()->with('user')->where('public_id', $orderPublicId)->first();

        if ($order === null) {
            throw new ApplicationException('The order of this event no longer exists.', 'not_found', 404);
        }

        $store = (string) config('automation.store_name');
        $link = rtrim((string) config('app.url'), '/').'/account/orders/'.$order->public_id;

        $text = match ($eventType) {
            AutomationOutboxWriter::ORDER_CREATED => "{$store}: we received order {$order->order_number}. We will let you know when payment is confirmed.",
            AutomationOutboxWriter::PAYMENT_SUCCEEDED => "{$store}: payment for order {$order->order_number} is confirmed. We are preparing it now.",
            AutomationOutboxWriter::ORDER_DELIVERED => "{$store}: order {$order->order_number} is ready. Sign in to view it: {$link}",
            default => "{$store}: order {$order->order_number} could not be completed. Check your email or contact support: {$link}",
        };

        return [$order->user, NotificationPreferenceCategory::Orders, $text];
    }

    /**
     * @return array{0: ?User, 1: NotificationPreferenceCategory, 2: string}
     */
    private function ticketMessage(string $ticketPublicId): array
    {
        $ticket = SupportTicket::query()->with('user')->where('public_id', $ticketPublicId)->first();

        if ($ticket === null) {
            throw new ApplicationException('The ticket of this event no longer exists.', 'not_found', 404);
        }

        $store = (string) config('automation.store_name');

        return [$ticket->user, NotificationPreferenceCategory::Support, "{$store}: there is an update on your support ticket. Sign in to read it: ".rtrim((string) config('app.url'), '/').'/account/tickets/'.$ticket->public_id];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recommendations(CarbonImmutable $now, NotificationPreferenceChannel $channel, int $limit, string $store, string $url): array
    {
        $period = $now->format('o-\WW');
        $recipients = $this->segments->recommendationRecipients($period, $channel, $limit);
        $purchases = $this->segments->purchasedProducts($recipients->pluck('id')->map(static fn ($id): int => (int) $id)->all())->groupBy('user_id');

        $platformByUser = $purchases->map(static fn ($rows): int => (int) $rows->first()['gaming_platform_id']);
        $bestSellers = $this->segments->bestSellersByPlatform($platformByUser->unique()->values()->all(), $now->subDays(30), 3)->groupBy('gaming_platform_id');

        $result = [];

        foreach ($recipients as $row) {
            $platformId = $platformByUser->get($row['id']);
            $owned = $purchases->get($row['id'], collect())->pluck('product_id')->map(static fn ($id): int => (int) $id)->all();
            $picks = $bestSellers->get($platformId, collect())->reject(static fn (array $product): bool => in_array((int) $product['product_id'], $owned, true))->take(3)->values();

            if ($picks->isEmpty()) {
                continue;
            }

            $result[] = [
                ...self::recipient($row, "D09:reco:{$row['customer_id']}:{$period}", "{$store}: picks for you on {$picks->first()['platform']}: ".$picks->pluck('name')->implode(', ').". See them at {$url} . Manage messages in your account settings."),
                'products' => $picks->map(static fn (array $product): array => ['name' => $product['name'], 'slug' => $product['slug']])->all(),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private static function recipient(array $row, string $dedupeKey, string $text): array
    {
        return [
            'dedupe_key' => $dedupeKey,
            'customer_id' => $row['customer_id'],
            'phone' => $row['phone'],
            'locale' => $row['locale'],
            'message' => ['text' => $text],
        ];
    }
}
