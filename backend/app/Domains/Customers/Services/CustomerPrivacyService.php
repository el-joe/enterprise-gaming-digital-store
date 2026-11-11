<?php

namespace App\Domains\Customers\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\WishlistItem;
use App\Domains\Customers\Models\CustomerConsent;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Customers\Models\CustomerProfile;
use App\Domains\Identity\Enums\BiometricKeyRevocationReason;
use App\Domains\Identity\Enums\TokenRevocationReason;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\AccountSessionService;
use App\Domains\Identity\Services\BiometricAuthService;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Orders\Models\Order;
use App\Domains\Reviews\Models\Review;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Wallet\Models\CustomerWallet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Personal data export and erasure (SECURITY.md section 12, M15). The export holds what the
 * store keeps about the customer: profile, consents, preferences, orders with their lines and
 * amounts, wallet movements, tickets (staff internal notes excluded) and reviews; never codes,
 * PINs, delivery values, payment provider data or staff notes. Erasure replaces identity
 * fields, signs out everywhere and removes personal extras, while financial records (orders,
 * payments, wallet ledger) are kept for the retention period. Both are audited.
 */
class CustomerPrivacyService
{
    public function __construct(
        private AuditLogger $audit,
        private AccountSessionService $sessions,
        private BiometricAuthService $biometrics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function export(User $customer): array
    {
        $this->ensureCustomer($customer);

        $data = [
            'exported_at' => now()->toIso8601String(),
            'profile' => [
                'id' => $customer->public_id,
                'name' => $customer->name,
                'email' => $customer->email,
                'email_verified_at' => $customer->email_verified_at?->toIso8601String(),
                'phone' => $customer->phone,
                'locale' => $customer->locale,
                'country' => $customer->country_code,
                'display_currency' => $customer->display_currency,
                'status' => $customer->status?->value,
                'registered_at' => $customer->created_at?->toIso8601String(),
                'last_login_at' => $customer->last_login_at?->toIso8601String(),
            ],
            'consents' => CustomerConsent::query()->where('user_id', $customer->id)->orderBy('id')->get(['channel', 'granted', 'source', 'created_at'])
                ->map(static fn (CustomerConsent $consent): array => ['channel' => $consent->channel?->value ?? $consent->channel, 'granted' => (bool) $consent->granted, 'source' => $consent->source, 'at' => $consent->created_at?->toIso8601String()])->all(),
            'notification_preferences' => NotificationPreference::query()->where('user_id', $customer->id)->get(['category', 'channel', 'enabled'])
                ->map(static fn (NotificationPreference $preference): array => ['category' => $preference->category?->value ?? $preference->category, 'channel' => $preference->channel?->value ?? $preference->channel, 'enabled' => (bool) $preference->enabled])->all(),
            'orders' => Order::query()->where('user_id', $customer->id)->with('orderItems:id,order_id,name_snapshot,sku_snapshot,quantity,line_total_base')->orderBy('id')
                ->get(['id', 'order_number', 'status', 'country_code', 'base_currency', 'total_base', 'display_currency', 'display_total', 'payment_currency', 'payment_total', 'created_at'])
                ->map(static fn (Order $order): array => [
                    'number' => $order->order_number,
                    'status' => $order->status?->value ?? $order->status,
                    'country' => $order->country_code,
                    'total' => ['amount' => (string) $order->display_total, 'currency' => $order->display_currency],
                    'charged' => $order->payment_total === null ? null : ['amount' => (string) $order->payment_total, 'currency' => $order->payment_currency],
                    'placed_at' => $order->created_at?->toIso8601String(),
                    'items' => $order->orderItems->map(static fn ($item): array => ['name' => $item->name_snapshot, 'sku' => $item->sku_snapshot, 'quantity' => (int) $item->quantity])->all(),
                ])->all(),
            'wallet' => $this->wallet($customer),
            'tickets' => SupportTicket::query()->where('user_id', $customer->id)
                ->with(['supportTicketMessages' => static fn ($query) => $query->where('is_internal', false)->orderBy('id')])
                ->orderBy('id')->get()
                ->map(static fn (SupportTicket $ticket): array => [
                    'number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status?->value,
                    'opened_at' => $ticket->created_at?->toIso8601String(),
                    'messages' => $ticket->supportTicketMessages->map(static fn ($message): array => ['from' => $message->author_type?->value ?? $message->author_type, 'body' => $message->body, 'at' => $message->created_at?->toIso8601String()])->all(),
                ])->all(),
            'reviews' => Review::query()->where('user_id', $customer->id)->orderBy('id')->get(['rating', 'title', 'body', 'created_at'])
                ->map(static fn (Review $review): array => ['rating' => (int) $review->rating, 'title' => $review->title, 'body' => $review->body, 'at' => $review->created_at?->toIso8601String()])->all(),
        ];

        $this->audit->record(new AuditEntry('customers.data_exported', $customer, after: ['orders' => count($data['orders']), 'tickets' => count($data['tickets'])]));

        return $data;
    }

    /**
     * Replace the customer's identity with placeholders. Refused while the wallet holds a
     * balance (stored value must be settled first).
     */
    public function anonymize(User $customer, string $reason): User
    {
        $this->ensureCustomer($customer);

        $wallet = CustomerWallet::query()->where('user_id', $customer->id)->first();

        if ($wallet !== null && bccomp((string) $wallet->balance, '0', 6) > 0) {
            throw new ApplicationException('Settle the wallet balance before erasing this customer.', 'wallet_not_empty', 409);
        }

        $placeholder = 'deleted+'.Str::lower((string) $customer->public_id).'@invalid.invalid';

        DB::transaction(function () use ($customer, $reason, $placeholder): void {
            $customer->forceFill([
                'name' => 'Deleted customer',
                'email' => $placeholder,
                'phone' => null,
                'locale' => null,
                'display_currency' => null,
                'country_code' => null,
                'password' => Hash::make(Str::random(64)),
                'remember_token' => null,
                'email_verified_at' => null,
                'status' => UserStatus::Closed,
                'last_login_ip' => null,
            ])->save();

            CustomerProfile::query()->where('user_id', $customer->id)->update(['risk_notes' => null]);
            CustomerNote::query()->where('user_id', $customer->id)->delete();
            NotificationPreference::query()->where('user_id', $customer->id)->delete();
            WishlistItem::query()->where('user_id', $customer->id)->delete();
            Order::query()->where('user_id', $customer->id)->update(['contact_email' => $placeholder, 'contact_phone' => null, 'customer_name' => null, 'ip_address' => null, 'user_agent' => null]);

            $this->audit->record(new AuditEntry('customers.anonymized', $customer, reason: $reason));
        });

        $this->sessions->signOutEverywhere($customer, TokenRevocationReason::AccountInactive);
        $this->biometrics->revokeAllForUser($customer, BiometricKeyRevocationReason::RevokedByStaff);

        return $customer->refresh();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function wallet(User $customer): ?array
    {
        $wallet = CustomerWallet::query()->where('user_id', $customer->id)->first();

        if ($wallet === null) {
            return null;
        }

        return [
            'balance' => ['amount' => (string) $wallet->balance, 'currency' => $wallet->currency],
            'movements' => $wallet->walletTransactions()->orderBy('id')->get(['type', 'amount', 'balance_after', 'note', 'created_at'])
                ->map(static fn ($entry): array => ['type' => $entry->type?->value, 'amount' => (string) $entry->amount, 'balance_after' => (string) $entry->balance_after, 'note' => $entry->note, 'at' => $entry->created_at?->toIso8601String()])->all(),
        ];
    }

    private function ensureCustomer(User $customer): void
    {
        if ($customer->isStaff()) {
            throw (new ModelNotFoundException)->setModel(User::class);
        }
    }
}
