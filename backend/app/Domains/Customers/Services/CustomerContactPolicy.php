<?php

namespace App\Domains\Customers\Services;

use App\Domains\Customers\Enums\CustomerConsentChannel;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Whether a customer may be contacted on a secondary channel (SMS), decided server-side for every
 * customer-facing automation.
 *
 * - Every message: an active customer account (not staff) with a phone number.
 * - Order and support updates: an explicit, enabled notification preference for that category on
 *   that channel (opt-in; no row means no message).
 * - Marketing: the customer's latest consent record for the channel's marketing consent is a
 *   grant, and no disabled marketing preference exists for the channel.
 *
 * Guests have no account to hold a preference and are never contacted this way.
 */
class CustomerContactPolicy
{
    /**
     * Null when the customer may be contacted, otherwise the reason they may not.
     */
    public function refusal(?User $user, NotificationPreferenceCategory $category, NotificationPreferenceChannel $channel): ?string
    {
        if ($user === null) {
            return 'guest_has_no_preferences';
        }

        $eligible = $this->eligible(User::query()->whereKey($user->id), $category, $channel)->exists();

        if ($eligible) {
            return null;
        }

        return match (true) {
            $user->type !== UserType::Customer || $user->status !== UserStatus::Active => 'account_not_active',
            trim((string) $user->phone) === '' => 'no_phone_number',
            $category === NotificationPreferenceCategory::Marketing => 'no_marketing_consent',
            default => 'channel_not_enabled_by_customer',
        };
    }

    /**
     * Restrict a query over `users` (under the given table alias) to customers who may be
     * contacted. The alias is a fixed identifier chosen by the caller, never input.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $users
     * @return Builder<TModel>
     */
    public function eligible(Builder $users, NotificationPreferenceCategory $category, NotificationPreferenceChannel $channel, string $alias = 'users'): Builder
    {
        $users->where("{$alias}.type", UserType::Customer->value)
            ->where("{$alias}.status", UserStatus::Active->value)
            ->whereNull("{$alias}.deleted_at")
            ->whereNotNull("{$alias}.phone")
            ->where("{$alias}.phone", '!=', '');

        if ($category !== NotificationPreferenceCategory::Marketing) {
            return $users->whereExists(static fn ($preference) => $preference->selectRaw('1')
                ->from('notification_preferences as np')
                ->whereColumn('np.user_id', "{$alias}.id")
                ->where('np.category', $category->value)
                ->where('np.channel', $channel->value)
                ->where('np.enabled', true));
        }

        return $users
            ->whereRaw(
                "(SELECT cc.granted FROM customer_consents cc WHERE cc.user_id = {$alias}.id AND cc.channel = ? ORDER BY cc.created_at DESC, cc.id DESC LIMIT 1) = 1",
                [$this->marketingConsent($channel)->value],
            )
            ->whereNotExists(static fn ($preference) => $preference->selectRaw('1')
                ->from('notification_preferences as np')
                ->whereColumn('np.user_id', "{$alias}.id")
                ->where('np.category', NotificationPreferenceCategory::Marketing->value)
                ->where('np.channel', $channel->value)
                ->where('np.enabled', false));
    }

    private function marketingConsent(NotificationPreferenceChannel $channel): CustomerConsentChannel
    {
        return match ($channel) {
            NotificationPreferenceChannel::Sms => CustomerConsentChannel::SmsMarketing,
            default => CustomerConsentChannel::EmailMarketing,
        };
    }
}
